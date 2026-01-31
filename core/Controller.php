<?php
class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    protected function view($view, $data = []) {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            // Extract data to make variables available in view
            // Use EXTR_OVERWRITE to ensure controller data takes precedence
            extract($data, EXTR_OVERWRITE);
            require $viewFile;
        } else {
            die("View not found: {$view}");
        }
    }
    
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Set success message and redirect
     */
    protected function redirectWithSuccess($url, $message) {
        Message::success($message);
        $this->redirect($url);
    }
    
    /**
     * Set error message and redirect
     */
    protected function redirectWithError($url, $message) {
        Message::error($message);
        $this->redirect($url);
    }
    
    /**
     * Resize and compress image (especially for camera photos)
     * @param string $filePath Path to the image file
     * @param string $originalFilename Original filename to check if from camera
     * @param bool $forceProcess Force processing even if not from camera
     * @return array ['success' => bool, 'new_size' => int, 'message' => string]
     */
    protected function resizeAndCompressImage($filePath, $originalFilename = '', $forceProcess = false) {
        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => 'File tidak ditemukan'];
        }
        
        // Check if file is an image
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extension, $imageTypes)) {
            return ['success' => false, 'message' => 'File bukan gambar'];
        }
        
        // Check if GD library is available
        if (!function_exists('imagecreatefromjpeg') && !function_exists('imagecreatefrompng')) {
            return ['success' => false, 'message' => 'GD library tidak tersedia'];
        }
        
        // Check if file is from camera (filename starts with "camera_")
        // Also check the actual file path in case originalFilename is empty or changed
        $isFromCamera = false;
        if (!empty($originalFilename)) {
            $isFromCamera = (stripos($originalFilename, 'camera_') === 0);
        }
        
        // Also check filename from path as fallback
        if (!$isFromCamera) {
            $pathFilename = basename($filePath);
            $isFromCamera = (stripos($pathFilename, 'camera_') === 0);
        }
        
        // Image processing
        
        // Only process camera photos unless forced
        if (!$isFromCamera && !$forceProcess) {
            return ['success' => false, 'message' => 'Bukan foto dari kamera (nama file tidak dimulai dengan "camera_")'];
        }
        
        $originalSize = filesize($filePath);
        if ($originalSize === false || $originalSize === 0) {
            return ['success' => false, 'message' => 'Tidak dapat membaca ukuran file'];
        }
        
        // Starting image compression
        
        $maxWidth = 1920;
        $maxHeight = 1920;
        $quality = 85; // JPEG quality (0-100)
        
        try {
            // Create image resource based on type
            $image = false;
            $lastError = error_get_last();
            
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $image = @imagecreatefromjpeg($filePath);
                    if (!$image) {
                        $error = error_get_last();
                        return ['success' => false, 'message' => 'Gagal membaca file JPEG: ' . ($error['message'] ?? 'Format tidak valid')];
                    }
                    break;
                case 'png':
                    $image = @imagecreatefrompng($filePath);
                    if (!$image) {
                        $error = error_get_last();
                        return ['success' => false, 'message' => 'Gagal membaca file PNG: ' . ($error['message'] ?? 'Format tidak valid')];
                    }
                    break;
                case 'gif':
                    $image = @imagecreatefromgif($filePath);
                    if (!$image) {
                        $error = error_get_last();
                        return ['success' => false, 'message' => 'Gagal membaca file GIF: ' . ($error['message'] ?? 'Format tidak valid')];
                    }
                    break;
                default:
                    return ['success' => false, 'message' => 'Format gambar tidak didukung'];
            }
            
            // Support both resource (PHP <8) and GdImage object (PHP 8+)
            $isGdResource = is_resource($image) || (class_exists('GdImage') && $image instanceof GdImage);
            if (!$image || !$isGdResource) {
                return ['success' => false, 'message' => 'Gagal membuat resource gambar'];
            }
            
            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);
            
            // Calculate new dimensions if image is too large
            $needsResize = false;
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
            
            if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
                $needsResize = true;
                $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
                $newWidth = (int)($originalWidth * $ratio);
                $newHeight = (int)($originalHeight * $ratio);
            }
            
            // Create new image
            if ($needsResize) {
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Preserve transparency for PNG and GIF
                if ($extension === 'png' || $extension === 'gif') {
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                    $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                    imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
                }
                
                // Resize image
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                imagedestroy($image);
                $image = $newImage;
            }
            
            // Save compressed image (always save as JPEG for camera photos to reduce size)
            $outputPath = $filePath;
            $filenameChanged = false;
            
            if ($extension === 'png' || $extension === 'gif') {
                // Convert PNG/GIF to JPEG for better compression
                $outputPath = preg_replace('/\.(png|gif)$/i', '.jpg', $filePath);
                $filenameChanged = true;
            }
            
            // Get final dimensions (use newWidth/newHeight which are set correctly)
            $finalWidth = $newWidth;
            $finalHeight = $newHeight;
            
            // Save image
            if ($extension === 'png') {
                // Convert PNG to JPEG
                $jpegImage = imagecreatetruecolor($finalWidth, $finalHeight);
                if (!$jpegImage) {
                    imagedestroy($image);
                    return ['success' => false, 'message' => 'Gagal membuat image resource untuk konversi PNG'];
                }
                $white = imagecolorallocate($jpegImage, 255, 255, 255);
                imagefilledrectangle($jpegImage, 0, 0, $finalWidth, $finalHeight, $white);
                imagecopy($jpegImage, $image, 0, 0, 0, 0, $finalWidth, $finalHeight);
                $saveResult = @imagejpeg($jpegImage, $outputPath, $quality);
                imagedestroy($jpegImage);
                imagedestroy($image);
                
                if (!$saveResult) {
                    $error = error_get_last();
                    return ['success' => false, 'message' => 'Gagal menyimpan file JPEG dari PNG: ' . ($error['message'] ?? 'Unknown error')];
                }
                
                // Delete original PNG if converted
                if ($filenameChanged && file_exists($filePath)) {
                    unlink($filePath);
                }
            } elseif ($extension === 'gif') {
                // Convert GIF to JPEG
                $jpegImage = imagecreatetruecolor($finalWidth, $finalHeight);
                if (!$jpegImage) {
                    imagedestroy($image);
                    return ['success' => false, 'message' => 'Gagal membuat image resource untuk konversi GIF'];
                }
                $white = imagecolorallocate($jpegImage, 255, 255, 255);
                imagefilledrectangle($jpegImage, 0, 0, $finalWidth, $finalHeight, $white);
                imagecopy($jpegImage, $image, 0, 0, 0, 0, $finalWidth, $finalHeight);
                $saveResult = @imagejpeg($jpegImage, $outputPath, $quality);
                imagedestroy($jpegImage);
                imagedestroy($image);
                
                if (!$saveResult) {
                    $error = error_get_last();
                    return ['success' => false, 'message' => 'Gagal menyimpan file JPEG dari GIF: ' . ($error['message'] ?? 'Unknown error')];
                }
                
                // Delete original GIF if converted
                if ($filenameChanged && file_exists($filePath)) {
                    unlink($filePath);
                }
            } else {
                // JPEG - compress (always compress even if no resize needed)
                $saveResult = @imagejpeg($image, $outputPath, $quality);
                imagedestroy($image);
                
                if (!$saveResult) {
                    $error = error_get_last();
                    return ['success' => false, 'message' => 'Gagal menyimpan file JPEG: ' . ($error['message'] ?? 'Unknown error')];
                }
            }
            
            // Verify file was saved
            if (!file_exists($outputPath)) {
                return ['success' => false, 'message' => 'File tidak tersimpan setelah kompresi'];
            }
            
            $newSize = filesize($outputPath);
            if ($newSize === false) {
                return ['success' => false, 'message' => 'Tidak dapat membaca ukuran file setelah kompresi'];
            }
            
            $savedBytes = $originalSize - $newSize;
            $savedPercent = $originalSize > 0 ? round(($savedBytes / $originalSize) * 100, 2) : 0;
            
            // Image compression completed
            
            return [
                'success' => true,
                'new_size' => $newSize,
                'original_size' => $originalSize,
                'saved_bytes' => $savedBytes,
                'saved_percent' => $savedPercent,
                'new_filename' => basename($outputPath),
                'message' => "Gambar berhasil di-resize dan di-kompres. Ukuran berkurang {$savedPercent}%"
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        } catch (Error $e) {
            return ['success' => false, 'message' => 'Fatal Error: ' . $e->getMessage()];
        }
    }
}


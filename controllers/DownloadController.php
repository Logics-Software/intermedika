<?php
/**
 * DownloadController - Handles file downloads with proper error handling
 */
class DownloadController extends Controller {
    
    /**
     * Download file with error handling
     */
    public function file() {
        Auth::requireAuth();
        
        $filePath = $_GET['path'] ?? '';
        $filename = $_GET['name'] ?? '';
        
        if (empty($filePath)) {
            $this->json([
                'success' => false,
                'error' => 'Path file tidak ditemukan'
            ], 400);
        }
        
        // Security: Prevent directory traversal
        $filePath = $this->sanitizePath($filePath);
        $fullPath = $this->getFullPath($filePath);
        
        // Check if file exists
        if (!file_exists($fullPath)) {
            $this->json([
                'success' => false,
                'error' => 'File tidak ditemukan',
                'details' => 'File mungkin telah dihapus atau dipindahkan'
            ], 404);
        }
        
        // Check if file is readable
        if (!is_readable($fullPath)) {
            $this->json([
                'success' => false,
                'error' => 'File tidak dapat dibaca',
                'details' => 'Tidak memiliki izin untuk mengakses file ini'
            ], 403);
        }
        
        // Get file info
        $fileInfo = $this->getFileInfo($fullPath);
        
        // Check file size (prevent memory issues)
        if ($fileInfo['size'] > 100 * 1024 * 1024) { // 100MB limit
            $this->json([
                'success' => false,
                'error' => 'File terlalu besar',
                'details' => 'File melebihi batas maksimal download (100MB)'
            ], 413);
        }
        
        try {
            // Set appropriate headers
            $this->setDownloadHeaders($fileInfo, $filename);
            
            // Stream file to browser
            $this->streamFile($fullPath);
            
        } catch (Exception $e) {
            error_log("Download error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'error' => 'Gagal mengunduh file',
                'details' => 'Terjadi kesalahan saat memproses file'
            ], 500);
        }
    }
    
    /**
     * Check file status (HEAD request)
     */
    public function check() {
        Auth::requireAuth();
        
        $filePath = $_GET['path'] ?? '';
        
        if (empty($filePath)) {
            $this->json([
                'success' => false,
                'error' => 'Path file tidak ditemukan'
            ], 400);
        }
        
        // Security: Prevent directory traversal
        $filePath = $this->sanitizePath($filePath);
        $fullPath = $this->getFullPath($filePath);
        
        // Check file status
        $status = $this->checkFileStatus($fullPath);
        
        $this->json($status, $status['success'] ? 200 : 404);
    }
    
    /**
     * Sanitize file path to prevent directory traversal
     */
    private function sanitizePath($path) {
        // Remove any directory traversal attempts
        $path = str_replace(['../', '..\\', '../', '..\\'], '', $path);
        
        // Remove leading slashes
        $path = ltrim($path, '/\\');
        
        // Normalize path separators
        $path = str_replace('\\', '/', $path);
        
        return $path;
    }
    
    /**
     * Get full file path
     */
    private function getFullPath($relativePath) {
        $config = require __DIR__ . '/../config/app.php';
        $uploadPath = $config['upload_path'];
        
        return $uploadPath . $relativePath;
    }
    
    /**
     * Check file status
     */
    private function checkFileStatus($fullPath) {
        if (!file_exists($fullPath)) {
            return [
                'success' => false,
                'error' => 'File tidak ditemukan',
                'details' => 'File mungkin telah dihapus atau dipindahkan',
                'code' => 404
            ];
        }
        
        if (!is_readable($fullPath)) {
            return [
                'success' => false,
                'error' => 'File tidak dapat diakses',
                'details' => 'Tidak memiliki izin untuk mengakses file ini',
                'code' => 403
            ];
        }
        
        $fileInfo = $this->getFileInfo($fullPath);
        
        if ($fileInfo['size'] === 0) {
            return [
                'success' => false,
                'error' => 'File kosong',
                'details' => 'File tidak memiliki konten atau rusak',
                'code' => 422
            ];
        }
        
        return [
            'success' => true,
            'file_info' => $fileInfo
        ];
    }
    
    /**
     * Get file information
     */
    private function getFileInfo($fullPath) {
        $pathInfo = pathinfo($fullPath);
        
        return [
            'name' => $pathInfo['basename'],
            'extension' => $pathInfo['extension'] ?? '',
            'size' => filesize($fullPath),
            'mime_type' => $this->getMimeType($fullPath),
            'modified' => filemtime($fullPath)
        ];
    }
    
    /**
     * Get MIME type of file
     */
    private function getMimeType($fullPath) {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fullPath);
            finfo_close($finfo);
            return $mimeType;
        }
        
        if (function_exists('mime_content_type')) {
            return mime_content_type($fullPath);
        }
        
        // Fallback based on extension
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
            'zip' => 'application/zip'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
    
    /**
     * Set download headers
     */
    private function setDownloadHeaders($fileInfo, $customFilename = '') {
        // Clear any previous output
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set headers
        header('Content-Type: ' . $fileInfo['mime_type']);
        header('Content-Length: ' . $fileInfo['size']);
        header('Content-Disposition: attachment; filename="' . ($customFilename ?: $fileInfo['name']) . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
    }
    
    /**
     * Stream file to browser
     */
    private function streamFile($fullPath) {
        $handle = fopen($fullPath, 'rb');
        
        if ($handle === false) {
            throw new Exception('Cannot open file for reading');
        }
        
        // Stream file in chunks to prevent memory issues
        $chunkSize = 8192; // 8KB chunks
        
        while (!feof($handle)) {
            $chunk = fread($handle, $chunkSize);
            if ($chunk === false) {
                break;
            }
            echo $chunk;
            
            // Flush output to browser
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        }
        
        fclose($handle);
    }
}

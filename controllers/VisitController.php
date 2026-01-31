<?php
class VisitController extends Controller {
    private $visitModel;
    private $visitActivityModel;
    private $customerModel;
    private $visitFileModel;

    public function __construct() {
        $this->visitModel = new Visit();
        $this->visitActivityModel = new VisitActivity();
        $this->customerModel = new Mastercustomer();
        $this->visitFileModel = new VisitFile();
    }

    public function index() {
        Auth::requireAuth();

        $currentUser = Auth::user();
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $perPage = in_array($perPage, [10, 20, 50, 100, 200, 500, 1000]) ? $perPage : 10;
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';
        $periode = $_GET['periode'] ?? 'today';
        $kodecustomer = $_GET['kodecustomer'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $result = $this->visitModel->listByUser($currentUser['id'], $page, $perPage, $status, $search, $periode, $kodecustomer, $startDate, $endDate);
        $totalPages = $perPage > 0 ? (int)ceil($result['total'] / $perPage) : 1;

        $activeVisit = $this->visitModel->findActiveByUser($currentUser['id']);

        // Get customers for filter dropdown
        $customerModel = new Mastercustomer();
        $customers = $customerModel->getAllForSelection();

        $data = [
            'visits' => $result['data'],
            'page' => $page,
            'perPage' => $perPage,
            'total' => $result['total'],
            'totalPages' => $totalPages,
            'statusFilter' => $status,
            'search' => $search,
            'periode' => $periode,
            'kodecustomer' => $kodecustomer,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'customers' => $customers,
            'activeVisit' => $activeVisit
        ];

        $this->view('visits/index', $data);
    }

    public function checkin() {
        Auth::requireRole(['sales']);
        $currentUser = Auth::user();
        if (empty($currentUser['kodesales'])) {
            Session::flash('error', 'Akun sales Anda belum memiliki kode sales yang terdaftar. Hubungi admin.');
            $this->redirect('/visits');
        }

        $activeVisit = $this->visitModel->findActiveByUser($currentUser['id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($activeVisit) {
                Session::flash('error', 'Anda masih memiliki kunjungan yang berjalan. Selesaikan terlebih dahulu sebelum check-in baru.');
                $this->redirect('/visits');
            }

            $customerId = (int)($_POST['customer_id'] ?? 0);
            $checkInLatInput = $_POST['check_in_lat'] ?? '';
            $checkInLongInput = $_POST['check_in_long'] ?? '';
            $checkInLat = $checkInLatInput === '' ? null : (float)$checkInLatInput;
            $checkInLong = $checkInLongInput === '' ? null : (float)$checkInLongInput;
            $catatan = $_POST['catatan'] ?? null;

            if (!$customerId || $checkInLat === null || $checkInLong === null) {
                Session::flash('error', 'Pilih customer dan pastikan lokasi Anda terdeteksi.');
                $this->redirect('/visits/check-in');
            }

            $customer = $this->customerModel->findById($customerId);
            if (!$customer) {
                Session::flash('error', 'Customer tidak ditemukan.');
                $this->redirect('/visits/check-in');
            }

            if (empty($customer['latitude']) || empty($customer['longitude']) || (float)$customer['latitude'] == 0.0 || (float)$customer['longitude'] == 0.0) {
                Session::flash('error', 'Koordinat customer belum ditentukan. Silakan tetapkan lokasi customer terlebih dahulu.');
                $this->redirect('/visits/check-in');
            }

            $visitData = [
                'user_id' => $currentUser['id'],
                'kodesales' => $currentUser['kodesales'] ?? '',
                'customer_id' => $customer['id'],
                'kodecustomer' => $customer['kodecustomer'],
                'check_in_time' => date('Y-m-d H:i:s'),
                'check_in_lat' => $checkInLat,
                'check_in_long' => $checkInLong,
                'status_kunjungan' => 'Sedang Berjalan',
                'catatan' => $catatan,
                'jarak_dari_kantor' => $this->calculateDistanceKm($customer['latitude'], $customer['longitude'], $checkInLat, $checkInLong)
            ];

            $visitId = $this->visitModel->create($visitData);

            Session::flash('success', 'Check-in berhasil disimpan.');
            $this->redirect('/visits');
        }

        $appConfig = require __DIR__ . '/../config/app.php';
        $mapboxToken = getenv('MAPBOX_ACCESS_TOKEN') ?: ($appConfig['mapbox_access_token'] ?? null);

        $data = [
            'activeVisit' => $activeVisit,
            'mapboxToken' => $mapboxToken
        ];
        $this->view('visits/checkin', $data);
    }

    public function checkout($visitId) {
        Auth::requireRole(['sales']);
        $currentUser = Auth::user();

        $visit = $this->visitModel->findById($visitId);
        if (!$visit || $visit['user_id'] != $currentUser['id']) {
            Session::flash('error', 'Data kunjungan tidak ditemukan.');
            $this->redirect('/visits');
        }

        if ($visit['status_kunjungan'] !== 'Sedang Berjalan') {
            Session::flash('error', 'Kunjungan ini sudah selesai atau dibatalkan.');
            $this->redirect('/visits');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $checkOutLatInput = $_POST['check_out_lat'] ?? '';
            $checkOutLongInput = $_POST['check_out_long'] ?? '';
            $checkOutLat = $checkOutLatInput === '' ? null : (float)$checkOutLatInput;
            $checkOutLong = $checkOutLongInput === '' ? null : (float)$checkOutLongInput;
            $catatan = $_POST['catatan'] ?? null;

            if ($checkOutLat === null || $checkOutLong === null) {
                Session::flash('error', 'Lokasi check-out belum ditentukan.');
                $this->redirect('/visits');
            }

            // Handle file uploads
            if (isset($_FILES['visit_files']) && !empty($_FILES['visit_files']['name'])) {
                // Check if it's a single file or multiple files
                if (is_array($_FILES['visit_files']['name'])) {
                    // Multiple files
                    $hasFiles = false;
                    foreach ($_FILES['visit_files']['name'] as $name) {
                        if (!empty($name)) {
                            $hasFiles = true;
                            break;
                        }
                    }
                    
                    if ($hasFiles) {
                        $uploadErrors = $this->handleFileUploads($visitId, $_FILES['visit_files']);
                        if (!empty($uploadErrors)) {
                            Session::flash('error', 'Beberapa file gagal diupload: ' . implode(', ', $uploadErrors));
                        } else {
                            $fileCount = count(array_filter($_FILES['visit_files']['name'], function($name) { return !empty($name); }));
                            if ($fileCount > 0) {
                                Session::flash('success', "Check-out berhasil disimpan. {$fileCount} file berhasil diupload.");
                            }
                        }
                    }
                } else {
                    // Single file
                    if (!empty($_FILES['visit_files']['name'])) {
                        // Convert single file to array format for handleFileUploads
                        $filesArray = [
                            'name' => [$_FILES['visit_files']['name']],
                            'type' => [$_FILES['visit_files']['type']],
                            'tmp_name' => [$_FILES['visit_files']['tmp_name']],
                            'error' => [$_FILES['visit_files']['error']],
                            'size' => [$_FILES['visit_files']['size']]
                        ];
                        $uploadErrors = $this->handleFileUploads($visitId, $filesArray);
                        if (!empty($uploadErrors)) {
                            Session::flash('error', 'File gagal diupload: ' . implode(', ', $uploadErrors));
                        } else {
                            Session::flash('success', 'Check-out berhasil disimpan. File berhasil diupload.');
                        }
                    }
                }
            }

            $updateData = [
                'check_out_time' => date('Y-m-d H:i:s'),
                'check_out_lat' => $checkOutLat,
                'check_out_long' => $checkOutLong,
                'status_kunjungan' => 'Selesai',
                'catatan' => $catatan,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->visitModel->update($visitId, $updateData);

            // Only show generic success if no file upload message was set
            if (!Session::has('success') && !Session::has('error')) {
                Session::flash('success', 'Check-out berhasil disimpan.');
            }
            $this->redirect('/visits');
        }

        $activities = $this->visitActivityModel->listByVisit($visitId);
        $activityOptions = $this->getActiveTabelAktivitasOptions();
        $visitFiles = $this->visitFileModel->listByVisit($visitId);
        $appConfig = require __DIR__ . '/../config/app.php';
        $mapboxToken = getenv('MAPBOX_ACCESS_TOKEN') ?: ($appConfig['mapbox_access_token'] ?? null);

        $data = [
            'visit' => $visit,
            'activities' => $activities,
            'visitFiles' => $visitFiles,
            'mapboxToken' => $mapboxToken,
            'activityOptions' => $activityOptions
        ];

        $this->view('visits/checkout', $data);
    }

    public function createActivity($visitId) {
        Auth::requireRole(['sales']);
        $currentUser = Auth::user();

        $visit = $this->visitModel->findById($visitId);
        if (!$visit || $visit['user_id'] != $currentUser['id']) {
            Session::flash('error', 'Kunjungan tidak ditemukan.');
            $this->redirect('/visits');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $activityType = trim($_POST['activity_type'] ?? '');
            $deskripsi = $_POST['deskripsi'] ?? null;
            if (empty($activityType)) {
                Session::flash('error', 'Jenis aktivitas harus diisi.');
                $this->redirect('/visits/checkout/' . $visitId);
            }

            $this->visitActivityModel->create([
                'visit_id' => $visitId,
                'activity_type' => $activityType,
                'deskripsi' => $deskripsi
            ]);

            Session::flash('success', 'Aktivitas kunjungan ditambahkan.');
        }

        $this->redirect('/visits/checkout/' . $visitId);
    }

    public function nearestCustomers() {
        Auth::requireRole(['sales']);

        $latitude = isset($_GET['lat']) && $_GET['lat'] !== '' ? (float)$_GET['lat'] : null;
        $longitude = isset($_GET['lng']) && $_GET['lng'] !== '' ? (float)$_GET['lng'] : null;
        $search = $_GET['q'] ?? '';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

        if ($latitude === null || $longitude === null) {
            if (!empty($search)) {
                $customers = $this->customerModel->findNearest(null, null, $limit, $search);
            } else {
                $customers = [];
            }
        } else {
            $customers = $this->customerModel->findNearest($latitude, $longitude, $limit, $search);
        }

        header('Content-Type: application/json');
        echo json_encode(['data' => $customers]);
    }

    public function updateCustomerCoordinates($customerId) {
        Auth::requireRole(['sales']);
        $currentUser = Auth::user();

        $customer = $this->customerModel->findById($customerId);
        if (!$customer) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Customer tidak ditemukan']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $latitude = isset($input['latitude']) ? (float)$input['latitude'] : null;
        $longitude = isset($input['longitude']) ? (float)$input['longitude'] : null;

        if ($latitude === null || $longitude === null) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Latitude dan longitude harus diisi']);
            return;
        }

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Koordinat tidak valid']);
            return;
        }

        $this->customerModel->updateCoordinates($customerId, $latitude, $longitude);

        // Update status dan penanda pengguna bila relevan
        $updateData = [
            'status' => 'updated'
        ];
        if (!empty($currentUser['kodesales'])) {
            $updateData['userid'] = $currentUser['kodesales'];
        }
        $this->customerModel->update($customerId, $updateData);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    private function calculateDistanceKm($customerLat, $customerLong, $userLat, $userLong) {
        if ($customerLat === null || $customerLong === null || $userLat === null || $userLong === null) {
            return null;
        }

        $earthRadius = 6371; // km
        $latFrom = deg2rad((float)$customerLat);
        $lonFrom = deg2rad((float)$customerLong);
        $latTo = deg2rad((float)$userLat);
        $lonTo = deg2rad((float)$userLong);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($earthRadius * $angle, 2);
    }

    private function getActiveTabelAktivitasOptions() {
        $model = new Tabelaktivitas();
        $records = $model->getAll(1, 500, '', 'aktivitas', 'ASC');
        $options = [];
        foreach ($records as $row) {
            if (($row['status'] ?? '') === 'aktif' && !empty($row['aktivitas'])) {
                $options[] = $row['aktivitas'];
            }
        }
        return $options;
    }

    public function getVisitDetail($visitId) {
        Auth::requireRole(['sales']);
        $currentUser = Auth::user();

        $visit = $this->visitModel->findById($visitId);
        if (!$visit || $visit['user_id'] != $currentUser['id']) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Kunjungan tidak ditemukan']);
            return;
        }

        // Prepare response data
        $data = [
            'visit_id' => $visit['visit_id'],
            'namacustomer' => $visit['namacustomer'] ?? '-',
            'kodecustomer' => !empty($visit['master_kodecustomer']) ? $visit['master_kodecustomer'] : ($visit['kodecustomer'] ?? '-'),
            'status_kunjungan' => $visit['status_kunjungan'] ?? '-',
            'check_in_time' => $visit['check_in_time'] ?? null,
            'check_out_time' => $visit['check_out_time'] ?? null,
            'check_in_lat' => $visit['check_in_lat'] ?? null,
            'check_in_long' => $visit['check_in_long'] ?? null,
            'check_out_lat' => $visit['check_out_lat'] ?? null,
            'check_out_long' => $visit['check_out_long'] ?? null,
            'catatan' => $visit['catatan'] ?? '',
            'jarak_dari_kantor' => $visit['jarak_dari_kantor'] ?? null
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getVisitFiles($visitId) {
        Auth::requireRole(['sales']);
        $currentUser = Auth::user();

        $visit = $this->visitModel->findById($visitId);
        if (!$visit || $visit['user_id'] != $currentUser['id']) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Kunjungan tidak ditemukan']);
            return;
        }

        $files = $this->visitFileModel->listByVisit($visitId);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['files' => $files], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Convert PHP ini size string to bytes (e.g., "7.5M" -> 7864320)
     */
    private function convertToBytes($size) {
        if (empty($size)) {
            return 0;
        }
        
        $size = trim($size);
        $unit = strtolower(substr($size, -1));
        $value = (float) substr($size, 0, -1);
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
                // fall through
            case 'm':
                $value *= 1024;
                // fall through
            case 'k':
                $value *= 1024;
        }
        
        return (int) $value;
    }

    private function handleFileUploads($visitId, $files) {
        $appConfig = require __DIR__ . '/../config/app.php';
        $uploadPath = $appConfig['upload_path'];
        $allowedTypes = $appConfig['allowed_visit_file_types'] ?? ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $maxFileSize = $appConfig['max_visit_file_size'] ?? 5242880; // 5MB
        
        // Check PHP upload settings
        $phpUploadMaxSize = ini_get('upload_max_filesize');
        $phpPostMaxSize = ini_get('post_max_size');
        $phpMaxFileSize = $this->convertToBytes($phpUploadMaxSize);
        $phpPostMaxSizeBytes = $this->convertToBytes($phpPostMaxSize);
        
        // Use the smaller limit between our config and PHP settings
        if ($phpMaxFileSize > 0 && $phpMaxFileSize < $maxFileSize) {
            error_log("PHP upload_max_filesize ({$phpUploadMaxSize}) is smaller than configured max ({$maxFileSize} bytes). Using PHP limit.");
            $maxFileSize = $phpMaxFileSize;
        }
        
        if ($phpPostMaxSizeBytes > 0 && $phpPostMaxSizeBytes < $maxFileSize) {
            error_log("PHP post_max_size ({$phpPostMaxSize}) is smaller than configured max. This may cause upload failures.");
        }

        // Ensure upload directory exists and is writable
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                return ['Gagal membuat folder upload. Pastikan folder uploads/ dapat ditulis.'];
            }
        }

        if (!is_writable($uploadPath)) {
            return ['Folder upload tidak dapat ditulis. Pastikan folder uploads/ memiliki permission yang benar.'];
        }

        $errors = [];
        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            // Skip empty file names
            if (empty($files['name'][$i])) {
                continue;
            }

            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $errorMsg = 'Error upload';
                switch ($files['error'][$i]) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorMsg = 'File terlalu besar';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errorMsg = 'File hanya terupload sebagian';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errorMsg = 'Tidak ada file yang diupload';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $errorMsg = 'Folder temporary tidak ditemukan';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $errorMsg = 'Gagal menulis file ke disk';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $errorMsg = 'Upload dihentikan oleh extension PHP';
                        break;
                }
                $errors[] = $files['name'][$i] . ' - ' . $errorMsg;
                continue;
            }

            $originalName = $files['name'][$i];
            $tmpName = $files['tmp_name'][$i];
            $fileSize = $files['size'][$i];
            $fileType = $files['type'][$i] ?? '';
            
            // Get extension - handle files without extension or with multiple dots
            $pathInfo = pathinfo($originalName);
            $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';
            
            // If no extension, try to detect from MIME type for images
            if (empty($extension) && !empty($fileType)) {
                $mimeToExt = [
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp'
                ];
                if (isset($mimeToExt[$fileType])) {
                    $extension = $mimeToExt[$fileType];
                }
            }

            // Validate file type
            if (empty($extension) || !in_array($extension, $allowedTypes)) {
                $errors[] = $originalName . ' - Format file tidak diizinkan (hanya: ' . implode(', ', $allowedTypes) . '). Extension: ' . ($extension ?: 'tidak terdeteksi');
                error_log("File type validation failed: originalName={$originalName}, extension={$extension}, fileType={$fileType}");
                continue;
            }

            // Validate file size
            if ($fileSize <= 0) {
                $errors[] = $originalName . ' - Ukuran file tidak valid (0 bytes)';
                error_log("File size validation failed: originalName={$originalName}, fileSize={$fileSize}");
                continue;
            }
            
            if ($fileSize > $maxFileSize) {
                $maxSizeMB = round($maxFileSize / 1024 / 1024, 1);
                $fileSizeMB = round($fileSize / 1024 / 1024, 2);
                $errors[] = $originalName . " - Ukuran file terlalu besar ({$fileSizeMB}MB, maksimal {$maxSizeMB}MB)";
                error_log("File size exceeded: originalName={$originalName}, fileSize={$fileSize} bytes, maxSize={$maxFileSize} bytes");
                continue;
            }

            // Generate unique filename
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $targetPath = $uploadPath . $filename;

            // Validate tmp file exists and is readable
            if (!file_exists($tmpName) || !is_uploaded_file($tmpName)) {
                $errors[] = $originalName . ' - File temporary tidak ditemukan atau tidak valid';
                error_log("Temporary file validation failed: originalName={$originalName}, tmpName={$tmpName}, exists=" . (file_exists($tmpName) ? 'yes' : 'no'));
                continue;
            }
            
            // Move uploaded file
            if (move_uploaded_file($tmpName, $targetPath)) {
                // Resize and compress if it's a camera photo
                $finalPath = $targetPath;
                $finalFilename = $filename;
                $finalSize = $fileSize;
                $finalExtension = $extension;
                
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    // Try to compress image (especially camera photos)
                    error_log("Attempting to compress image: originalName={$originalName}, targetPath={$targetPath}, extension={$extension}");
                    $compressResult = $this->resizeAndCompressImage($targetPath, $originalName);
                    
                    // Log compression result
                    if ($compressResult['success']) {
                        error_log("Image compression successful: {$originalName} - Saved {$compressResult['saved_percent']}%");
                        
                       	// Verify compressed file exists
                        $newPath = dirname($targetPath) . '/' . $compressResult['new_filename'];
                        if (!file_exists($newPath)) {
                            error_log("WARNING: Compressed file not found: {$newPath}. Using original file.");
                            $compressResult['success'] = false;
                        }
                    } else {
                        error_log("Image compression failed: {$originalName} - " . ($compressResult['message'] ?? 'Unknown error'));
                    }
                    
                    if ($compressResult['success']) {
                        $finalFilename = $compressResult['new_filename'];
                        $finalPath = dirname($targetPath) . '/' . $finalFilename;
                        $finalSize = $compressResult['new_size'];
                        
                        // Update extension if file was converted (PNG/GIF to JPEG)
                        if (preg_match('/\.jpg$/i', $finalFilename)) {
                            $finalExtension = 'jpg';
                        }
                        
                        // Update target path for cleanup if needed
                        $targetPath = $finalPath;
                    } else {
                        // Compression failed, but file is already saved, so continue with original
                        error_log("Using original file (compression failed): {$originalName}");
                    }
                }
                
                // Save file info to database
                try {
                    $this->visitFileModel->create([
                        'visit_id' => $visitId,
                        'filename' => $finalFilename,
                        'original_filename' => $originalName,
                        'file_size' => $finalSize,
                        'file_type' => $finalExtension
                    ]);
                } catch (Exception $e) {
                    // If database save fails, delete the uploaded file
                    if (file_exists($targetPath)) {
                        unlink($targetPath);
                    }
                    $errors[] = $originalName . ' - Gagal menyimpan ke database: ' . $e->getMessage();
                }
            } else {
                $errors[] = $originalName . ' - Gagal menyimpan file ke server';
            }
        }

        return $errors;
    }
}


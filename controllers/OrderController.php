<?php
class OrderController extends Controller {
	private $headerModel;
	private $detailModel;
	private $customerModel;
	private $barangModel;
	private $orderFileModel;
	private $salesModel;

	public function __construct() {
		parent::__construct();
		$this->headerModel = new Headerorder();
		$this->detailModel = new Detailorder();
		$this->customerModel = new Mastercustomer();
		$this->barangModel = new Masterbarang();
		$this->orderFileModel = new OrderFile();
		$this->salesModel = new Mastersales();
	}

	public function index() {
		Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

		$user = Auth::user();
		$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
		$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
		$perPage = in_array($perPage, [10, 25, 50, 100, 200, 500, 1000]) ? $perPage : 10;
		$search = trim($_GET['search'] ?? '');
		$status = trim($_GET['status'] ?? '');
		$dateFilter = $_GET['periode'] ?? ($_GET['date_filter'] ?? 'today');
		$startDate = $_GET['start_date'] ?? '';
		$endDate = $_GET['end_date'] ?? '';
		$kodesalesFilter = $_GET['kodesales'] ?? '';

		[$computedStartDate, $computedEndDate] = $this->computeDateRange($dateFilter, $startDate, $endDate);

		$sortBy = $_GET['sort_by'] ?? 'tanggalorder';
		$sortOrder = strtoupper($_GET['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
		
		$options = [
			'page' => $page,
			'per_page' => $perPage,
			'search' => $search,
			'status' => $status,
			'start_date' => $computedStartDate,
			'end_date' => $computedEndDate,
			'sort_by' => $sortBy,
			'sort_order' => $sortOrder
		];

		// Prepare sales list for filter (only for non-sales users)
		$salesList = [];
		if (($user['role'] ?? '') !== 'sales') {
			$salesList = $this->salesModel->getAllActive();
			// Use selected filter if set
			if (!empty($kodesalesFilter)) {
				$options['kodesales'] = $kodesalesFilter;
			}
		} else {
			// Force filter for sales user
			$options['kodesales'] = $user['kodesales'] ?? null;
		}

		$orders = $this->headerModel->getAll($options);
		$total = $this->headerModel->count($options);
		$totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

		$data = [
			'orders' => $orders,
			'page' => $page,
			'perPage' => $perPage,
			'total' => $total,
			'totalPages' => $totalPages,
			'search' => $search,
			'status' => $status,
			'dateFilter' => $dateFilter,
			'startDate' => $computedStartDate,
			'endDate' => $computedEndDate,
			'rawStartDate' => $startDate,
			'rawEndDate' => $endDate,
			'sortBy' => $sortBy,
			'sortOrder' => $sortOrder,
			'salesList' => $salesList,
			'kodesalesFilter' => $kodesalesFilter
		];

		$this->view('orders/index', $data);
	}

	public function create() {
		Auth::requireRole(['sales']);

		$user = Auth::user();
		if (empty($user['kodesales'])) {
			Session::flash('error', 'Sales tidak memiliki kode sales. Silakan hubungi administrator.');
			$this->redirect('/orders');
		}

		$customers = $this->customerModel->getAllForSelection();
		$customersByStatus = [
			'pkp' => array_values(array_filter($customers, static fn($c) => strtolower($c['statuspkp'] ?? 'pkp') === 'pkp')),
			'nonpkp' => array_values(array_filter($customers, static fn($c) => strtolower($c['statuspkp'] ?? 'pkp') === 'nonpkp')),
		];
		$barangs = $this->barangModel->getAllForSelection();
		$noorder = $this->generateNoorder();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->processFormData($noorder, $user, true);
			if ($result['success']) {
				$message = 'Order berhasil dibuat';
				if (isset($result['warning'])) {
					Session::flash('warning', $result['warning']);
				} else {
					Session::flash('success', $message);
				}
				$this->redirect('/orders');
			} else {
				Session::flash('error', $result['message']);
			}
		}

		$data = [
			'noorder' => $noorder,
			'customers' => $customers,
			'customersByStatus' => $customersByStatus,
			'barangs' => $barangs,
			'selectedCustomer' => $_POST['kodecustomer'] ?? '',
			'statuspkp' => $_POST['statuspkp'] ?? 'pkp',
			'tanggalorder' => date('Y-m-d'),
			'keterangan' => $_POST['keterangan'] ?? '',
			'status' => 'order',
			'detailItems' => $this->getPostedDetails(),
			'barangsJson' => json_encode($barangs),
			'customersByStatusJson' => json_encode($customersByStatus),
			'backUrl' => $_GET['back'] ?? '/orders' // Custom back URL from query parameter or default
		];

		$this->view('orders/create', $data);
	}

	public function edit($noorder) {
		Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

		$order = $this->headerModel->findByNoorder($noorder);
		if (!$order) {
			Session::flash('error', 'Order tidak ditemukan');
			$this->redirect('/orders');
		}

		$user = Auth::user();
		if (($user['role'] ?? '') === 'sales' && ($user['kodesales'] ?? '') !== $order['kodesales']) {
			Session::flash('error', 'Anda tidak memiliki akses ke order ini');
			$this->redirect('/orders');
		}

		if ($order['status'] !== 'order') {
			Session::flash('error', 'Order sudah menjadi Faktur dan tidak dapat diubah');
			$this->redirect('/orders');
		}

		$customers = $this->customerModel->getAllForSelection();
		$customersByStatus = [
			'pkp' => array_values(array_filter($customers, static fn($c) => strtolower($c['statuspkp'] ?? 'pkp') === 'pkp')),
			'nonpkp' => array_values(array_filter($customers, static fn($c) => strtolower($c['statuspkp'] ?? 'pkp') === 'nonpkp')),
		];
		$barangs = $this->barangModel->getAllForSelection();
		$detailItems = $this->detailModel->getByNoorder($noorder);
		$orderFiles = $this->orderFileModel->listByOrder($noorder);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->processFormData($noorder, $user, false, $order);
			if ($result['success']) {
				$message = 'Order berhasil diperbarui';
				if (isset($result['warning'])) {
					Session::flash('warning', $result['warning']);
				} else {
					Session::flash('success', $message);
				}
				$this->redirect('/orders');
			} else {
				Session::flash('error', $result['message']);
			}

			$detailItems = $this->getPostedDetails();
			$order = array_merge($order, [
				'kodecustomer' => $_POST['kodecustomer'] ?? $order['kodecustomer'],
				'keterangan' => $_POST['keterangan'] ?? $order['keterangan'],
				'status' => 'order'
			]);
		}

		$data = [
			'order' => $order,
			'detailItems' => $detailItems,
			'customers' => $customers,
			'customersByStatus' => $customersByStatus,
			'barangs' => $barangs,
			'orderFiles' => $orderFiles,
			'statuspkp' => $_POST['statuspkp'] ?? ($order['statuspkp'] ?? 'pkp'),
			'barangsJson' => json_encode($barangs),
			'customersByStatusJson' => json_encode($customersByStatus),
			'backUrl' => $_GET['back'] ?? '/orders' // Custom back URL from query parameter or default
		];

		$this->view('orders/edit', $data);
	}

	public function show($noorder) {
		Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

		$order = $this->headerModel->findByNoorder($noorder);
		if (!$order) {
			Session::flash('error', 'Order tidak ditemukan');
			$this->redirect('/orders');
		}

		$user = Auth::user();
		if (($user['role'] ?? '') === 'sales' && ($user['kodesales'] ?? '') !== $order['kodesales']) {
			Session::flash('error', 'Anda tidak memiliki akses ke order ini');
			$this->redirect('/orders');
		}

		$details = $this->detailModel->getByNoorder($noorder);
		$orderFiles = $this->orderFileModel->listByOrder($noorder);

		$data = [
			'order' => $order,
			'details' => $details,
			'orderFiles' => $orderFiles,
			'backUrl' => $_GET['back'] ?? '/orders' // Custom back URL from query parameter or default
		];

		$this->view('orders/show', $data);
	}

	public function delete($noorder) {
		Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

		$order = $this->headerModel->findByNoorder($noorder);
		if (!$order) {
			Session::flash('error', 'Order tidak ditemukan');
			$this->redirect('/orders');
		}

		$user = Auth::user();
		if (($user['role'] ?? '') === 'sales' && ($user['kodesales'] ?? '') !== $order['kodesales']) {
			Session::flash('error', 'Anda tidak memiliki akses ke order ini');
			$this->redirect('/orders');
		}

		if (($order['status'] ?? '') !== 'order') {
			Session::flash('error', 'Order dengan status Faktur tidak dapat dihapus');
			$this->redirect('/orders');
		}

		try {
			// Delete associated files first
			$this->orderFileModel->deleteByOrder($noorder);
			// Then delete the order
			$this->headerModel->delete($noorder);
			Session::flash('success', 'Order berhasil dihapus');
		} catch (Exception $e) {
			Session::flash('error', 'Gagal menghapus order: ' . $e->getMessage());
		}

		$this->redirect('/orders');
	}

	private function computeDateRange($filter, $start, $end) {
		switch ($filter) {
			case 'week':
				$startDate = date('Y-m-d', strtotime('monday this week'));
				$endDate = date('Y-m-d', strtotime('sunday this week'));
				break;
			case 'month':
				$startDate = date('Y-m-01');
				$endDate = date('Y-m-t');
				break;
			case 'year':
				$startDate = date('Y-01-01');
				$endDate = date('Y-12-31');
				break;
			case 'custom':
				$startDate = !empty($start) ? $start : date('Y-m-d');
				$endDate = !empty($end) ? $end : $startDate;
				break;
			case 'today':
			default:
				$startDate = date('Y-m-d');
				$endDate = date('Y-m-d');
				break;
		}

		return [$startDate, $endDate];
	}

	private function processFormData($noorder, $user, $isCreate = true, $existingOrder = null) {
		$tanggalorder = date('Y-m-d');
		$kodecustomer = trim($_POST['kodecustomer'] ?? '');
		$keterangan = trim($_POST['keterangan'] ?? '');
		$status = 'order';
		$nopenjualan = $_POST['nopenjualan'] ?? null;
		$statusPkpInput = $_POST['statuspkp'] ?? ($existingOrder['statuspkp'] ?? 'pkp');
		$statusPkpNormalized = strtolower(trim($statusPkpInput)) === 'nonpkp' ? 'nonpkp' : 'pkp';

		if (empty($kodecustomer)) {
			return ['success' => false, 'message' => 'Customer harus dipilih'];
		}

		$customerInfo = $this->customerModel->findByKodecustomer($kodecustomer);
		if (!$customerInfo) {
			return ['success' => false, 'message' => 'Customer tidak ditemukan'];
		}

		$customerStatusPkp = strtolower($customerInfo['statuspkp'] ?? 'pkp');
		if ($customerStatusPkp !== $statusPkpNormalized) {
			return ['success' => false, 'message' => 'Customer yang dipilih tidak sesuai dengan status PKP order'];
		}

		$detailData = $this->sanitizeDetailInput();
		if (empty($detailData)) {
			return ['success' => false, 'message' => 'Minimal satu detail order harus diisi'];
		}

		$nilaiOrder = array_sum(array_column($detailData, 'totalharga'));

		$headerData = [
			'noorder' => $noorder,
			'tanggalorder' => $tanggalorder,
			'kodesales' => $user['kodesales'] ?? $existingOrder['kodesales'] ?? null,
			'statuspkp' => $statusPkpNormalized,
			'kodecustomer' => $kodecustomer,
			'keterangan' => $keterangan,
			'nilaiorder' => $nilaiOrder,
			'nopenjualan' => $nopenjualan,
			'status' => $status
		];

		if (empty($headerData['kodesales'])) {
			return ['success' => false, 'message' => 'Kode sales tidak tersedia'];
		}

		try {
			if ($isCreate) {
				if ($this->headerModel->findByNoorder($noorder)) {
					return ['success' => false, 'message' => 'Nomor order sudah digunakan'];
				}
				$this->headerModel->create($headerData, $detailData);
			} else {
				$this->headerModel->update($noorder, $headerData, $detailData);
			}

			// Handle file uploads
			if (isset($_FILES['order_files']) && !empty($_FILES['order_files']['name'])) {
				$uploadErrors = $this->handleFileUploads($noorder, $_FILES['order_files'], $user);
				if (!empty($uploadErrors)) {
					// File upload errors, but order is already saved
					// Return success with warning message
					return ['success' => true, 'warning' => 'Order berhasil disimpan, namun beberapa file gagal diupload: ' . implode(', ', $uploadErrors)];
				}
			}
		} catch (Exception $e) {
			return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
		}

		return ['success' => true];
	}

	private function sanitizeDetailInput() {
		$kodebarang = $_POST['detail_kodebarang'] ?? [];
		$jumlah = $_POST['detail_jumlah'] ?? [];
		$harga = $_POST['detail_harga'] ?? [];
		$discount = $_POST['detail_discount'] ?? [];
		$satuan = $_POST['detail_satuan'] ?? [];

		$details = [];
		$count = count($kodebarang);

		for ($i = 0; $i < $count; $i++) {
			$kb = trim($kodebarang[$i] ?? '');
			$qty = isset($jumlah[$i]) ? (int)$jumlah[$i] : 0;
			$price = isset($harga[$i]) ? (float)str_replace(',', '', $harga[$i]) : 0;
			$disc = isset($discount[$i]) ? (float)str_replace(',', '', $discount[$i]) : 0;

			if ($kb === '' || $qty <= 0) {
				continue;
			}

			// Treat discount as percentage (frontend sends discount in percent, e.g., 5.00 means 5%)
			$discountValue = 0;
			if ($disc > 0) {
				$discountValue = (($qty * $price) * ($disc / 100));
			}

			$lineTotal = max(($qty * $price) - $discountValue, 0);

			$details[] = [
				'kodebarang' => $kb,
				'jumlah' => $qty,
				'hargajual' => $price,
				'discount' => $disc,
				'totalharga' => $lineTotal,
				'satuan' => trim($satuan[$i] ?? '')
			];
		}

		return $details;
	}

	private function getPostedDetails() {
		$kodebarang = $_POST['detail_kodebarang'] ?? [];
		$jumlah = $_POST['detail_jumlah'] ?? [];
		$harga = $_POST['detail_harga'] ?? [];
		$discount = $_POST['detail_discount'] ?? [];
		$satuan = $_POST['detail_satuan'] ?? [];

		$rows = [];
		$count = max(count($kodebarang), count($jumlah));

		for ($i = 0; $i < $count; $i++) {
			$kb = $kodebarang[$i] ?? '';
			$qty = $jumlah[$i] ?? '';
			$price = $harga[$i] ?? '';
			$disc = $discount[$i] ?? '';
			$total = '';

			if ($kb !== '' && $qty !== '' && $price !== '') {
				// Interpret discount as percentage (frontend uses percent)
				$discFloat = (float)str_replace(',', '', $disc);
				$discountValue = ($discFloat > 0) ? (((float)$qty * (float)$price) * ($discFloat / 100)) : 0;
				$calcTotal = max(((float)$qty * (float)$price) - $discountValue, 0);
				$total = number_format($calcTotal, 2, '.', '');
			}

			$rows[] = [
				'kodebarang' => $kb,
				'jumlah' => $qty,
				'hargajual' => $price,
				'discount' => $disc,
				'totalharga' => $total,
				'satuan' => $satuan[$i] ?? ''
			];
		}

		if (empty($rows)) {
			$rows[] = [
				'kodebarang' => '',
				'jumlah' => '',
				'hargajual' => '',
				'discount' => '',
				'totalharga' => '',
				'satuan' => ''
			];
		}

		return $rows;
	}

	private function generateNoorder() {
		$prefix = 'OJ' . date('ym');
		$last = $this->headerModel->getLastNoorderWithPrefix($prefix);

		if ($last && isset($last['noorder'])) {
			$lastNumber = (int)substr($last['noorder'], -4);
			$nextNumber = $lastNumber + 1;
		} else {
			$nextNumber = 1;
		}

		return sprintf('%s%05d', $prefix, $nextNumber);
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

	private function handleFileUploads($noorder, $files, $user) {
		$appConfig = require __DIR__ . '/../config/app.php';
		$uploadPath = $appConfig['upload_path'] . 'orders/';
		$allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'];
		$maxFileSize = 5242880; // 5MB
		$maxFiles = 5;
		
		// Check PHP upload settings
		$phpUploadMaxSize = ini_get('upload_max_filesize');
		$phpPostMaxSize = ini_get('post_max_size');
		$phpMaxFileSize = $this->convertToBytes($phpUploadMaxSize);
		$phpPostMaxSizeBytes = $this->convertToBytes($phpPostMaxSize);
		
		// Log PHP settings for debugging
		error_log("PHP Upload Settings: upload_max_filesize={$phpUploadMaxSize} ({$phpMaxFileSize} bytes), post_max_size={$phpPostMaxSize} ({$phpPostMaxSizeBytes} bytes), configured max={$maxFileSize} bytes (5MB)");
		
		// Use the smaller limit between our config and PHP settings
		if ($phpMaxFileSize > 0 && $phpMaxFileSize < $maxFileSize) {
			error_log("WARNING: PHP upload_max_filesize ({$phpUploadMaxSize} = {$phpMaxFileSize} bytes) is smaller than configured max ({$maxFileSize} bytes). Using PHP limit.");
			$maxFileSize = $phpMaxFileSize;
		}
		
		if ($phpPostMaxSizeBytes > 0 && $phpPostMaxSizeBytes < $maxFileSize) {
			error_log("WARNING: PHP post_max_size ({$phpPostMaxSize} = {$phpPostMaxSizeBytes} bytes) is smaller than configured max ({$maxFileSize} bytes). This may cause upload failures.");
		}

		// Ensure upload directory exists and is writable
		if (!is_dir($uploadPath)) {
			if (!mkdir($uploadPath, 0755, true)) {
				return ['Gagal membuat folder upload. Pastikan folder uploads/orders/ dapat ditulis.'];
			}
		}

		if (!is_writable($uploadPath)) {
			return ['Folder upload tidak dapat ditulis. Pastikan folder uploads/orders/ memiliki permission yang benar.'];
		}

		$errors = [];
		$fileCount = 0;

		// Count non-empty files
		foreach ($files['name'] as $name) {
			if (!empty($name)) {
				$fileCount++;
			}
		}

		// Check max files limit
		if ($fileCount > $maxFiles) {
			return ['Maksimal ' . $maxFiles . ' file yang dapat diupload'];
		}

		// Get existing files count
		$existingFiles = $this->orderFileModel->listByOrder($noorder);
		$existingCount = count($existingFiles);
		if (($existingCount + $fileCount) > $maxFiles) {
			return ['Total file tidak boleh melebihi ' . $maxFiles . ' file (sudah ada ' . $existingCount . ' file)'];
		}

		$uploadedCount = 0;
		$totalFiles = count($files['name']);

		for ($i = 0; $i < $totalFiles; $i++) {
			// Skip empty file names
			if (empty($files['name'][$i])) {
				continue;
			}

			if ($files['error'][$i] !== UPLOAD_ERR_OK) {
				$errorMsg = 'Error upload';
				$phpUploadMaxSize = ini_get('upload_max_filesize');
				$phpPostMaxSize = ini_get('post_max_size');
				
				switch ($files['error'][$i]) {
					case UPLOAD_ERR_INI_SIZE:
						$errorMsg = "File terlalu besar. PHP upload_max_filesize saat ini: {$phpUploadMaxSize} (diperlukan minimal 6M). Silakan hubungi administrator untuk mengubah setting PHP.";
						error_log("UPLOAD_ERR_INI_SIZE: File {$files['name'][$i]} exceeds PHP upload_max_filesize ({$phpUploadMaxSize}). Configured max in app: 5MB");
						break;
					case UPLOAD_ERR_FORM_SIZE:
						$errorMsg = "File terlalu besar. PHP post_max_size saat ini: {$phpPostMaxSize} (diperlukan minimal 6M). Silakan hubungi administrator untuk mengubah setting PHP.";
						error_log("UPLOAD_ERR_FORM_SIZE: File {$files['name'][$i]} exceeds PHP post_max_size ({$phpPostMaxSize}). Configured max in app: 5MB");
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
			$fileType = $files['type'][$i];
			
			// Log file info for debugging
			error_log("Processing file upload: name={$originalName}, size={$fileSize} bytes (" . round($fileSize / 1024 / 1024, 2) . "MB), maxAllowed={$maxFileSize} bytes (" . round($maxFileSize / 1024 / 1024, 2) . "MB)");
			
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
				$maxSizeMB = round($maxFileSize / 1024 / 1024, 2);
				$fileSizeMB = round($fileSize / 1024 / 1024, 2);
				$errors[] = $originalName . " - Ukuran file terlalu besar ({$fileSizeMB}MB, maksimal {$maxSizeMB}MB)";
				error_log("File size exceeded: originalName={$originalName}, fileSize={$fileSize} bytes ({$fileSizeMB}MB), maxSize={$maxFileSize} bytes ({$maxSizeMB}MB)");
				continue;
			}

			// Generate unique filename
			$filename = uniqid() . '_' . time() . '.' . $extension;
			$targetPath = $uploadPath . $filename;
			$relativePath = 'uploads/orders/' . $filename;

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
						$relativePath = 'uploads/orders/' . $finalFilename;
						
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
					$this->orderFileModel->create([
						'noorder' => $noorder,
						'filename' => $finalFilename,
						'original_filename' => $originalName,
						'file_path' => $relativePath,
						'file_type' => $fileType,
						'file_size' => $finalSize,
						'uploaded_by' => $user['id'] ?? null
					]);
					$uploadedCount++;
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

	public function deleteFile() {
		Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->json(['success' => false, 'message' => 'Method not allowed'], 405);
		}

		$fileId = $_POST['file_id'] ?? null;
		if (empty($fileId)) {
			$this->json(['success' => false, 'message' => 'File ID tidak ditemukan'], 400);
		}

		$file = $this->orderFileModel->findById($fileId);
		if (!$file) {
			$this->json(['success' => false, 'message' => 'File tidak ditemukan'], 404);
		}

		// Check if user has access to this order
		$order = $this->headerModel->findByNoorder($file['noorder']);
		if (!$order) {
			$this->json(['success' => false, 'message' => 'Order tidak ditemukan'], 404);
		}

		$user = Auth::user();
		if (($user['role'] ?? '') === 'sales' && ($user['kodesales'] ?? '') !== $order['kodesales']) {
			$this->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke file ini'], 403);
		}

		try {
			$this->orderFileModel->delete($fileId);
			$this->json(['success' => true, 'message' => 'File berhasil dihapus']);
		} catch (Exception $e) {
			error_log("Error deleting file: " . $e->getMessage());
			$this->json(['success' => false, 'message' => 'Gagal menghapus file: ' . $e->getMessage()], 500);
		}
	}
}



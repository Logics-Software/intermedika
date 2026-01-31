<?php
class PenerimaanController extends Controller {
	private $headerModel;
	private $detailModel;
	private $penjualanModel;

	public function __construct() {
		parent::__construct();
		$this->headerModel = new Headerpenerimaan();
		$this->detailModel = new Detailpenerimaan();
		$this->penjualanModel = new Headerpenjualan();
	}

	public function index() {
		Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

		$currentUser = Auth::user();
		$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
		$perPageOptions = [10, 25, 50, 100, 200, 500, 1000];
		$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
		if (!in_array($perPage, $perPageOptions, true)) {
			$perPage = 10;
		}

		$search = trim($_GET['search'] ?? '');
		$status = $_GET['status'] ?? 'belumproses'; // Default filter: status = 'belumproses'
		$dateFilter = $_GET['date_filter'] ?? 'today';
		$startDate = $_GET['start_date'] ?? '';
		$endDate = $_GET['end_date'] ?? '';

		[$computedStartDate, $computedEndDate] = $this->computeDateRange($dateFilter, $startDate, $endDate);

		$options = [
			'page' => $page,
			'per_page' => $perPage,
			'search' => $search,
			'status' => $status,
			'start_date' => $computedStartDate,
			'end_date' => $computedEndDate,
		];

		// Jika role adalah sales, filter hanya data penerimaan dari sales tersebut
		if (Auth::isSales() && !empty($currentUser['kodesales'])) {
			$options['kodesales'] = $currentUser['kodesales'];
		}

		$penerimaan = $this->headerModel->getAll($options);
		$total = $this->headerModel->count($options);
		$totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

		$data = [
			'penerimaan' => $penerimaan,
			'page' => $page,
			'perPage' => $perPage,
			'perPageOptions' => $perPageOptions,
			'totalPages' => $totalPages,
			'total' => $total,
			'search' => $search,
			'status' => $status,
			'dateFilter' => $dateFilter,
			'startDate' => $computedStartDate,
			'endDate' => $computedEndDate,
			'rawStartDate' => $startDate,
			'rawEndDate' => $endDate,
		];

		$this->view('penerimaan/index', $data);
	}

	public function create() {
		// Hanya sales yang bisa create/edit penerimaan
		Auth::requireRole(['sales']);

		$currentUser = Auth::user();
		$nopenerimaan = $this->generateNopenerimaan();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$headerData = [
				'nopenerimaan' => $_POST['nopenerimaan'] ?? '',
				'tanggalpenerimaan' => date('Y-m-d'), // Otomatis menggunakan tanggal sistem saat create
				'statuspkp' => $_POST['statuspkp'] ?? null,
				'jenispenerimaan' => $_POST['jenispenerimaan'] ?? '',
				'kodesales' => $currentUser['kodesales'] ?? null, // Otomatis dari session sales
				'kodecustomer' => $_POST['kodecustomer'] ?? null,
				'totalpiutang' => (float)($_POST['totalpiutang'] ?? 0),
				'totalpotongan' => (float)($_POST['totalpotongan'] ?? 0),
				'totallainlain' => (float)($_POST['totallainlain'] ?? 0),
				'totalnetto' => (float)($_POST['totalnetto'] ?? 0),
				'status' => 'belumproses',
				'noinkaso' => null, // Akan diupdate via API dari VB6
				'userid' => $currentUser['username'] ?? null
			];

			$details = [];
			if (isset($_POST['details']) && is_array($_POST['details'])) {
				foreach ($_POST['details'] as $detail) {
					$tanggalcair = $detail['tanggalcair'] ?? null;
					// Convert empty string to null for database compatibility
					if ($tanggalcair === '' || $tanggalcair === null) {
						$tanggalcair = null;
					}
					
					$nogiro = $detail['nogiro'] ?? null;
					// Convert empty string to null for database compatibility
					if ($nogiro === '') {
						$nogiro = null;
					}
					
					$details[] = [
						'nopenjualan' => $detail['nopenjualan'] ?? '',
						'nogiro' => $nogiro,
						'tanggalcair' => $tanggalcair,
						'piutang' => (float)($detail['piutang'] ?? 0),
						'potongan' => (float)($detail['potongan'] ?? 0),
						'lainlain' => (float)($detail['lainlain'] ?? 0),
						'netto' => (float)($detail['netto'] ?? 0),
					];
				}
			}

			try {
				$this->headerModel->create($headerData, $details);
				Session::flash('success', 'Penerimaan piutang berhasil dibuat');
				$this->redirect('/penerimaan');
			} catch (Exception $e) {
				Session::flash('error', 'Gagal membuat penerimaan piutang: ' . $e->getMessage());
			}
		}

		$customerModel = new Mastercustomer();
		$customers = $customerModel->getAllForSelection();
		$customersByStatus = [
			'pkp' => array_values(array_filter($customers, static fn($c) => strtolower($c['statuspkp'] ?? 'pkp') === 'pkp')),
			'nonpkp' => array_values(array_filter($customers, static fn($c) => strtolower($c['statuspkp'] ?? 'pkp') === 'nonpkp')),
		];

		$data = [
			'nopenerimaan' => $nopenerimaan,
			'customers' => $customers,
			'customersByStatus' => $customersByStatus,
			'selectedCustomer' => $_POST['kodecustomer'] ?? '',
			'statuspkp' => $_POST['statuspkp'] ?? 'pkp',
			'availablePenjualan' => $this->detailModel->getAvailablePenjualan()
		];

		$this->view('penerimaan/create', $data);
	}

	private function generateNopenerimaan() {
		$year = date('y');
		$month = date('m');
		$prefix = 'PP' . $year . $month;
		
		$last = $this->headerModel->getLastNopenerimaanWithPrefix($prefix);
		
		if ($last && !empty($last['nopenerimaan'])) {
			$lastNumber = (int)substr($last['nopenerimaan'], -5);
			$nextNumber = $lastNumber + 1;
		} else {
			$nextNumber = 1;
		}

		return sprintf('%s%05d', $prefix, $nextNumber);
	}

	public function edit($nopenerimaan) {
		// Hanya sales yang bisa create/edit penerimaan
		Auth::requireRole(['sales']);

		$currentUser = Auth::user();
		$header = $this->headerModel->findByNopenerimaan($nopenerimaan);
		if (!$header) {
			Session::flash('error', 'Data penerimaan tidak ditemukan');
			$this->redirect('/penerimaan');
		}

		// Pastikan sales hanya bisa edit penerimaan mereka sendiri
		if (!empty($currentUser['kodesales']) && $header['kodesales'] !== $currentUser['kodesales']) {
			Session::flash('error', 'Anda tidak memiliki akses untuk mengedit penerimaan ini');
			$this->redirect('/penerimaan');
		}

		if (!$this->headerModel->canEditOrDelete($nopenerimaan)) {
			Session::flash('error', 'Data penerimaan tidak dapat diedit karena status sudah "proses"');
			$this->redirect('/penerimaan');
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$headerData = [
				'tanggalpenerimaan' => $_POST['tanggalpenerimaan'] ?? '',
				'statuspkp' => $_POST['statuspkp'] ?? null,
				'jenispenerimaan' => $_POST['jenispenerimaan'] ?? '',
				'kodesales' => $header['kodesales'] ?? null, // Tetap menggunakan kodesales dari data yang sudah ada, tidak bisa diubah
				'kodecustomer' => $_POST['kodecustomer'] ?? null,
				'totalpiutang' => (float)($_POST['totalpiutang'] ?? 0),
				'totalpotongan' => (float)($_POST['totalpotongan'] ?? 0),
				'totallainlain' => (float)($_POST['totallainlain'] ?? 0),
				'totalnetto' => (float)($_POST['totalnetto'] ?? 0),
				// noinkaso tidak diupdate dari form, akan diupdate via API dari VB6
			];

			$details = [];
			if (isset($_POST['details']) && is_array($_POST['details'])) {
				foreach ($_POST['details'] as $detail) {
					$tanggalcair = $detail['tanggalcair'] ?? null;
					// Convert empty string to null for database compatibility
					if ($tanggalcair === '' || $tanggalcair === null) {
						$tanggalcair = null;
					}
					
					$nogiro = $detail['nogiro'] ?? null;
					// Convert empty string to null for database compatibility
					if ($nogiro === '') {
						$nogiro = null;
					}
					
					$details[] = [
						'nopenjualan' => $detail['nopenjualan'] ?? '',
						'nogiro' => $nogiro,
						'tanggalcair' => $tanggalcair,
						'piutang' => (float)($detail['piutang'] ?? 0),
						'potongan' => (float)($detail['potongan'] ?? 0),
						'lainlain' => (float)($detail['lainlain'] ?? 0),
						'netto' => (float)($detail['netto'] ?? 0),
					];
				}
			}

			try {
				$this->headerModel->update($nopenerimaan, $headerData, $details);
				Session::flash('success', 'Penerimaan piutang berhasil diperbarui');
				$this->redirect('/penerimaan/view/' . urlencode($nopenerimaan));
			} catch (Exception $e) {
				Session::flash('error', 'Gagal memperbarui penerimaan piutang: ' . $e->getMessage());
			}
		}

		$details = $this->detailModel->getByNopenerimaan($nopenerimaan);
		$kodecustomer = $header['kodecustomer'] ?? null;
		$availablePenjualan = $this->detailModel->getAvailablePenjualan($kodecustomer);

		$customerModel = new Mastercustomer();
		$customers = $customerModel->getAllForSelection();
		$customersByStatus = [
			'pkp' => array_values(array_filter($customers, static fn($c) => strtolower($c['statuspkp'] ?? 'pkp') === 'pkp')),
			'nonpkp' => array_values(array_filter($customers, static fn($c) => strtolower($c['statuspkp'] ?? 'pkp') === 'nonpkp')),
		];

		$data = [
			'penerimaan' => $header,
			'details' => $details,
			'customers' => $customers,
			'customersByStatus' => $customersByStatus,
			'selectedCustomer' => $kodecustomer ?? '',
			'statuspkp' => $header['statuspkp'] ?? 'pkp',
			'availablePenjualan' => $availablePenjualan
		];

		$this->view('penerimaan/edit', $data);
	}

	public function show($nopenerimaan) {
		Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

		$currentUser = Auth::user();
		$header = $this->headerModel->findByNopenerimaan($nopenerimaan);
		if (!$header) {
			Session::flash('error', 'Data penerimaan tidak ditemukan');
			$this->redirect('/penerimaan');
		}

		// Jika role adalah sales, pastikan hanya bisa melihat penerimaan mereka sendiri
		if (Auth::isSales() && !empty($currentUser['kodesales'])) {
			if ($header['kodesales'] !== $currentUser['kodesales']) {
				Session::flash('error', 'Anda tidak memiliki akses untuk melihat data penerimaan ini');
				$this->redirect('/penerimaan');
			}
		}

		$details = $this->detailModel->getByNopenerimaan($nopenerimaan);

		$data = [
			'penerimaan' => $header,
			'details' => $details
		];

		$this->view('penerimaan/view', $data);
	}

	public function delete($nopenerimaan) {
		Auth::requireRole(['admin', 'manajemen', 'operator']);

		$header = $this->headerModel->findByNopenerimaan($nopenerimaan);
		if (!$header) {
			Session::flash('error', 'Data penerimaan tidak ditemukan');
			$this->redirect('/penerimaan');
		}

		if (!$this->headerModel->canEditOrDelete($nopenerimaan)) {
			Session::flash('error', 'Data penerimaan tidak dapat dihapus karena status sudah "proses"');
			$this->redirect('/penerimaan');
		}

		try {
			$this->headerModel->delete($nopenerimaan);
			Session::flash('success', 'Penerimaan piutang berhasil dihapus');
		} catch (Exception $e) {
			Session::flash('error', 'Gagal menghapus penerimaan piutang: ' . $e->getMessage());
		}

		$this->redirect('/penerimaan');
	}

	public function getAvailablePenjualan() {
		Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

		$kodecustomer = $_GET['kodecustomer'] ?? null;
		$penjualan = $this->detailModel->getAvailablePenjualan($kodecustomer);

		header('Content-Type: application/json');
		echo json_encode(['success' => true, 'data' => $penjualan]);
		exit;
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
}



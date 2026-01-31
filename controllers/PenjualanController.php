<?php
class PenjualanController extends Controller {
	private $headerModel;
	private $detailModel;

	public function __construct() {
		$this->headerModel = new Headerpenjualan();
		$this->detailModel = new Detailpenjualan();
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
		$periode = $_GET['periode'] ?? 'today';
		$startDate = $_GET['start_date'] ?? null;
		$endDate = $_GET['end_date'] ?? null;
		$statuspkp = $_GET['statuspkp'] ?? null;
		$sortBy = $_GET['sort_by'] ?? 'tanggalpenjualan';
		$sortOrder = strtoupper($_GET['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

		$options = [
			'page' => $page,
			'per_page' => $perPage,
			'search' => $search,
			'periode' => $periode,
			'start_date' => $startDate,
			'end_date' => $endDate,
			'statuspkp' => $statuspkp,
			'sort_by' => $sortBy,
			'sort_order' => $sortOrder
		];

		// Jika role adalah sales, filter hanya data penjualan dari sales tersebut
		if (Auth::isSales() && !empty($currentUser['kodesales'])) {
			$options['kodesales'] = $currentUser['kodesales'];
		}

		$penjualan = $this->headerModel->getAll($options);
		$total = $this->headerModel->count($options);
		$totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

		$data = [
			'penjualan' => $penjualan,
			'page' => $page,
			'perPage' => $perPage,
			'perPageOptions' => $perPageOptions,
			'totalPages' => $totalPages,
			'total' => $total,
			'search' => $search,
			'periode' => $periode,
			'startDate' => $startDate,
			'endDate' => $endDate,
			'statuspkp' => $statuspkp,
			'sortBy' => $sortBy,
			'sortOrder' => $sortOrder
		];

		$this->view('penjualan/index', $data);
	}

	public function show($nopenjualan) {
		Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

		// Validasi parameter
		if (empty($nopenjualan) || trim($nopenjualan) === '') {
			Session::flash('error', 'Nomor penjualan tidak valid');
			$this->redirect('/penjualan');
			return;
		}

		$currentUser = Auth::user();
		$header = $this->headerModel->findByNopenjualan($nopenjualan);
		if (!$header) {
			// Cek apakah ini format detail penjualan (dimulai dengan titik)
			if (strpos($nopenjualan, '.') === 0) {
				Session::flash('error', 'Nomor detail penjualan "' . htmlspecialchars($nopenjualan) . '" tidak ditemukan. Pastikan nomor penjualan yang dimasukkan benar.');
			} else {
				Session::flash('error', 'Nomor penjualan "' . htmlspecialchars($nopenjualan) . '" tidak ditemukan. Pastikan nomor penjualan yang dimasukkan benar.');
			}
			$this->redirect('/penjualan');
			return;
		}

		// Jika role adalah sales, pastikan hanya bisa melihat penjualan mereka sendiri
		if (Auth::isSales() && !empty($currentUser['kodesales'])) {
			if ($header['kodesales'] !== $currentUser['kodesales']) {
				Session::flash('error', 'Anda tidak memiliki akses untuk melihat data penjualan ini');
				$this->redirect('/penjualan');
			}
		}

		$details = $this->detailModel->getByNopenjualan($nopenjualan);

		// Jika detail penjualan kosong, tampilkan peringatan
		if (empty($details)) {
			Session::flash('warning', 'Data detail penjualan untuk nomor penjualan "' . htmlspecialchars($nopenjualan) . '" tidak ditemukan. Data header penjualan tersedia, namun detail barang tidak ditemukan dalam sistem.');
		}

		$data = [
			'penjualan' => $header,
			'details' => $details
		];

		$this->view('penjualan/view', $data);
	}
}




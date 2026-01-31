<?php
class PembelianController extends Controller {
    private $pembelianModel;
    private $barangModel;
    private $supplierModel;

    public function __construct() {
        parent::__construct();
        $this->pembelianModel = new Pembelianbarang();
        $this->barangModel = new Masterbarang();
        $this->supplierModel = new Mastersupplier();
    }

    public function index() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $perPage = in_array($perPage, [10, 25, 50, 100, 200, 500, 1000]) ? $perPage : 10;
        $search = trim($_GET['search'] ?? '');
        $nopembelian = trim($_GET['nopembelian'] ?? '');
        $kodebarang = trim($_GET['kodebarang'] ?? '');
        $dateFilter = $_GET['periode'] ?? ($_GET['date_filter'] ?? 'today');
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'tanggalpembelian';
        $sortOrder = strtoupper($_GET['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        [$computedStartDate, $computedEndDate] = $this->computeDateRange($dateFilter, $startDate, $endDate);

        $options = [
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
            'nopembelian' => $nopembelian,
            'kodebarang' => $kodebarang,
            'start_date' => $computedStartDate,
            'end_date' => $computedEndDate,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ];

        $items = $this->pembelianModel->getAll($options);
        $total = $this->pembelianModel->count($options);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        $perPageOptions = [10, 25, 50, 100, 200, 500, 1000];

        $data = [
            'items' => $items,
            'page' => $page,
            'perPage' => $perPage,
            'perPageOptions' => $perPageOptions,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'nopembelian' => $nopembelian,
            'kodebarang' => $kodebarang,
            'dateFilter' => $dateFilter,
            'startDate' => $computedStartDate,
            'endDate' => $computedEndDate,
            'rawStartDate' => $startDate,
            'rawEndDate' => $endDate,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];

        $this->view('pembelian/index', $data);
    }

    public function create() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = Auth::user();
            $isSales = ($user['role'] ?? '') === 'sales';
            
            $data = [
                'nopembelian' => trim($_POST['nopembelian'] ?? ''),
                'tanggalpembelian' => $_POST['tanggalpembelian'] ?? date('Y-m-d'),
                'namasupplier' => trim($_POST['namasupplier'] ?? ''),
                'kodebarang' => trim($_POST['kodebarang'] ?? ''),
                'jumlah' => (float)($_POST['jumlah'] ?? 0),
                'harga' => (float)($_POST['harga'] ?? 0),
                'discount' => (float)($_POST['discount'] ?? 0),
                'totalharga' => (float)($_POST['totalharga'] ?? 0)
            ];

            // Validate required fields
            if (empty($data['nopembelian']) || empty($data['tanggalpembelian']) || 
                empty($data['namasupplier']) || empty($data['kodebarang'])) {
                Session::flash('error', 'Semua field wajib diisi');
                $this->redirect('/pembelian/create');
            }
            
            // For sales role, set price fields to 0 if not provided
            if ($isSales) {
                $data['harga'] = 0;
                $data['discount'] = 0;
                $data['totalharga'] = 0;
            }

            try {
                $this->pembelianModel->create($data);
                Session::flash('success', 'Data pembelian berhasil ditambahkan');
                $this->redirect('/pembelian');
            } catch (Exception $e) {
                Session::flash('error', 'Gagal menambahkan data: ' . $e->getMessage());
                $this->redirect('/pembelian/create');
            }
        }

        $barangs = $this->barangModel->getAllForSelection();
        $suppliers = $this->supplierModel->getAllActive();

        $data = [
            'barangs' => $barangs,
            'suppliers' => $suppliers
        ];

        $this->view('pembelian/create', $data);
    }

    public function edit($id) {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $item = $this->pembelianModel->findById($id);

        if (!$item) {
            Session::flash('error', 'Data pembelian tidak ditemukan');
            $this->redirect('/pembelian');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = Auth::user();
            $isSales = ($user['role'] ?? '') === 'sales';
            
            $data = [
                'nopembelian' => trim($_POST['nopembelian'] ?? ''),
                'tanggalpembelian' => $_POST['tanggalpembelian'] ?? date('Y-m-d'),
                'namasupplier' => trim($_POST['namasupplier'] ?? ''),
                'kodebarang' => trim($_POST['kodebarang'] ?? ''),
                'jumlah' => (float)($_POST['jumlah'] ?? 0),
                'harga' => (float)($_POST['harga'] ?? 0),
                'discount' => (float)($_POST['discount'] ?? 0),
                'totalharga' => (float)($_POST['totalharga'] ?? 0)
            ];

            // Validate required fields
            if (empty($data['nopembelian']) || empty($data['tanggalpembelian']) || 
                empty($data['namasupplier']) || empty($data['kodebarang'])) {
                Session::flash('error', 'Semua field wajib diisi');
                $this->redirect('/pembelian/edit/' . $id);
            }
            
            // For sales role, preserve existing price values (don't allow modification)
            if ($isSales) {
                $data['harga'] = (float)($item['harga'] ?? 0);
                $data['discount'] = (float)($item['discount'] ?? 0);
                $data['totalharga'] = (float)($item['totalharga'] ?? 0);
            }

            try {
                $this->pembelianModel->update($id, $data);
                Session::flash('success', 'Data pembelian berhasil diperbarui');
                $this->redirect('/pembelian');
            } catch (Exception $e) {
                Session::flash('error', 'Gagal memperbarui data: ' . $e->getMessage());
                $this->redirect('/pembelian/edit/' . $id);
            }
        }

        $barangs = $this->barangModel->getAllForSelection();
        $suppliers = $this->supplierModel->getAllActive();

        $data = [
            'item' => $item,
            'barangs' => $barangs,
            'suppliers' => $suppliers
        ];

        $this->view('pembelian/edit', $data);
    }

    public function show($id) {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $item = $this->pembelianModel->findById($id);

        if (!$item) {
            Session::flash('error', 'Data pembelian tidak ditemukan');
            $this->redirect('/pembelian');
        }

        $this->view('pembelian/view', ['item' => $item]);
    }

    public function delete($id) {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $item = $this->pembelianModel->findById($id);

        if (!$item) {
            Session::flash('error', 'Data pembelian tidak ditemukan');
            $this->redirect('/pembelian');
        }

        try {
            $this->pembelianModel->delete($id);
            Session::flash('success', 'Data pembelian berhasil dihapus');
        } catch (Exception $e) {
            Session::flash('error', 'Gagal menghapus data: ' . $e->getMessage());
        }

        $this->redirect('/pembelian');
    }

    private function computeDateRange($dateFilter, $startDate, $endDate) {
        $today = date('Y-m-d');
        
        switch ($dateFilter) {
            case 'today':
                return [$today, $today];
            case 'week':
                $start = date('Y-m-d', strtotime('monday this week'));
                $end = date('Y-m-d', strtotime('sunday this week'));
                return [$start, $end];
            case 'month':
                $start = date('Y-m-01');
                $end = date('Y-m-t');
                return [$start, $end];
            case 'year':
                $start = date('Y-01-01');
                $end = date('Y-12-31');
                return [$start, $end];
            case 'custom':
                if (!empty($startDate) && !empty($endDate)) {
                    return [$startDate, $endDate];
                }
                return [null, null];
            default:
                return [$today, $today];
        }
    }
}


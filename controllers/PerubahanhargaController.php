<?php
class PerubahanhargaController extends Controller {
    private $perubahanhargaModel;
    private $barangModel;

    public function __construct() {
        parent::__construct();
        $this->perubahanhargaModel = new Perubahanharga();
        $this->barangModel = new Masterbarang();
    }

    public function index() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $perPage = in_array($perPage, [10, 25, 50, 100, 200, 500, 1000]) ? $perPage : 10;
        $search = trim($_GET['search'] ?? '');
        $dateFilter = $_GET['periode'] ?? ($_GET['date_filter'] ?? 'today');
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'tanggalperubahan';
        $sortOrder = strtoupper($_GET['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        [$computedStartDate, $computedEndDate] = $this->computeDateRange($dateFilter, $startDate, $endDate);

        $options = [
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
            'start_date' => $computedStartDate,
            'end_date' => $computedEndDate,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ];

        $items = $this->perubahanhargaModel->getAll($options);
        $total = $this->perubahanhargaModel->count($options);
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
            'dateFilter' => $dateFilter,
            'startDate' => $computedStartDate,
            'endDate' => $computedEndDate,
            'rawStartDate' => $startDate,
            'rawEndDate' => $endDate,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];

        $this->view('perubahanharga/index', $data);
    }

    public function create() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'noperubahan' => trim($_POST['noperubahan'] ?? ''),
                'tanggalperubahan' => $_POST['tanggalperubahan'] ?? date('Y-m-d'),
                'keterangan' => trim($_POST['keterangan'] ?? ''),
                'kodebarang' => trim($_POST['kodebarang'] ?? ''),
                'hargalama' => (float)($_POST['hargalama'] ?? 0),
                'discountlama' => (float)($_POST['discountlama'] ?? 0),
                'hargabaru' => (float)($_POST['hargabaru'] ?? 0),
                'discountbaru' => (float)($_POST['discountbaru'] ?? 0)
            ];

            // Validate required fields
            if (empty($data['noperubahan']) || empty($data['tanggalperubahan']) || 
                empty($data['keterangan']) || empty($data['kodebarang'])) {
                Session::flash('error', 'Semua field wajib diisi');
                $this->redirect('/perubahanharga/create');
            }

            try {
                $this->perubahanhargaModel->create($data);
                Session::flash('success', 'Data perubahan harga berhasil ditambahkan');
                $this->redirect('/perubahanharga');
            } catch (Exception $e) {
                Session::flash('error', 'Gagal menambahkan data: ' . $e->getMessage());
                $this->redirect('/perubahanharga/create');
            }
        }

        $barangs = $this->barangModel->getAllForSelection();

        $data = [
            'barangs' => $barangs
        ];

        $this->view('perubahanharga/create', $data);
    }

    public function edit($id) {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $item = $this->perubahanhargaModel->findById($id);

        if (!$item) {
            Session::flash('error', 'Data perubahan harga tidak ditemukan');
            $this->redirect('/perubahanharga');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'noperubahan' => trim($_POST['noperubahan'] ?? ''),
                'tanggalperubahan' => $_POST['tanggalperubahan'] ?? date('Y-m-d'),
                'keterangan' => trim($_POST['keterangan'] ?? ''),
                'kodebarang' => trim($_POST['kodebarang'] ?? ''),
                'hargalama' => (float)($_POST['hargalama'] ?? 0),
                'discountlama' => (float)($_POST['discountlama'] ?? 0),
                'hargabaru' => (float)($_POST['hargabaru'] ?? 0),
                'discountbaru' => (float)($_POST['discountbaru'] ?? 0)
            ];

            // Validate required fields
            if (empty($data['noperubahan']) || empty($data['tanggalperubahan']) || 
                empty($data['keterangan']) || empty($data['kodebarang'])) {
                Session::flash('error', 'Semua field wajib diisi');
                $this->redirect('/perubahanharga/edit/' . $id);
            }

            try {
                $this->perubahanhargaModel->update($id, $data);
                Session::flash('success', 'Data perubahan harga berhasil diperbarui');
                $this->redirect('/perubahanharga');
            } catch (Exception $e) {
                Session::flash('error', 'Gagal memperbarui data: ' . $e->getMessage());
                $this->redirect('/perubahanharga/edit/' . $id);
            }
        }

        $barangs = $this->barangModel->getAllForSelection();

        $data = [
            'item' => $item,
            'barangs' => $barangs
        ];

        $this->view('perubahanharga/edit', $data);
    }

    public function show($id) {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $item = $this->perubahanhargaModel->findById($id);

        if (!$item) {
            Session::flash('error', 'Data perubahan harga tidak ditemukan');
            $this->redirect('/perubahanharga');
        }

        $this->view('perubahanharga/view', ['item' => $item]);
    }

    public function delete($id) {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $item = $this->perubahanhargaModel->findById($id);

        if (!$item) {
            Session::flash('error', 'Data perubahan harga tidak ditemukan');
            $this->redirect('/perubahanharga');
        }

        try {
            $this->perubahanhargaModel->delete($id);
            Session::flash('success', 'Data perubahan harga berhasil dihapus');
        } catch (Exception $e) {
            Session::flash('error', 'Gagal menghapus data: ' . $e->getMessage());
        }

        $this->redirect('/perubahanharga');
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


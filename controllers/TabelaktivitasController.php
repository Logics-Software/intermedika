<?php
class TabelaktivitasController extends Controller {
    private $allowedStatuses = ['aktif', 'non aktif'];

    private function requireNonSalesRole() {
        Auth::requireRole(['admin', 'manajemen', 'operator']);
    }

    public function index() {
        $this->requireNonSalesRole();

        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = trim($_GET['search'] ?? '');
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';

        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (!in_array($perPage, $validPerPage, true)) {
            $perPage = 10;
        }

        $model = new Tabelaktivitas();
        $records = $model->getAll($page, $perPage, $search, $sortBy, $sortOrder);
        $total = $model->count($search);
        $totalPages = max((int)ceil($total / $perPage), 1);

        $data = [
            'records' => $records,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'validPerPage' => $validPerPage
        ];

        $this->view('tabelaktivitas/index', $data);
    }

    public function create() {
        $this->requireNonSalesRole();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $aktivitas = trim($_POST['aktivitas'] ?? '');
            $status = $_POST['status'] ?? 'aktif';

            if ($aktivitas === '') {
                Session::flash('error', 'Nama aktivitas wajib diisi.');
                $this->redirect('/tabelaktivitas/create');
            }

            if (!in_array($status, $this->allowedStatuses, true)) {
                Session::flash('error', 'Status aktivitas tidak valid.');
                $this->redirect('/tabelaktivitas/create');
            }

            $model = new Tabelaktivitas();
            $model->create([
                'aktivitas' => $aktivitas,
                'status' => $status
            ]);

            Session::flash('success', 'Aktivitas berhasil ditambahkan.');
            $this->redirect('/tabelaktivitas');
        }

        $this->view('tabelaktivitas/create', [
            'allowedStatuses' => $this->allowedStatuses
        ]);
    }

    public function edit($id) {
        $this->requireNonSalesRole();

        $model = new Tabelaktivitas();
        $record = $model->findById($id);

        if (!$record) {
            Session::flash('error', 'Data aktivitas tidak ditemukan.');
            $this->redirect('/tabelaktivitas');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $aktivitas = trim($_POST['aktivitas'] ?? '');
            $status = $_POST['status'] ?? 'aktif';

            if ($aktivitas === '') {
                Session::flash('error', 'Nama aktivitas wajib diisi.');
                $this->redirect("/tabelaktivitas/edit/{$id}");
            }

            if (!in_array($status, $this->allowedStatuses, true)) {
                Session::flash('error', 'Status aktivitas tidak valid.');
                $this->redirect("/tabelaktivitas/edit/{$id}");
            }

            $model->update($id, [
                'aktivitas' => $aktivitas,
                'status' => $status
            ]);

            Session::flash('success', 'Aktivitas berhasil diperbarui.');
            $this->redirect('/tabelaktivitas');
        }

        $this->view('tabelaktivitas/edit', [
            'record' => $record,
            'allowedStatuses' => $this->allowedStatuses
        ]);
    }

    public function delete($id) {
        $this->requireNonSalesRole();

        $model = new Tabelaktivitas();
        $record = $model->findById($id);

        if (!$record) {
            Session::flash('error', 'Data aktivitas tidak ditemukan.');
            $this->redirect('/tabelaktivitas');
        }

        $model->delete($id);
        Session::flash('success', 'Aktivitas berhasil dihapus.');
        $this->redirect('/tabelaktivitas');
    }
}


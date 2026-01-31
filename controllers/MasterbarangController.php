<?php
class MasterbarangController extends Controller {
    public function index() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        $filterPabrik = $_GET['kodepabrik'] ?? '';
        $filterGolongan = $_GET['kodegolongan'] ?? '';
        $filterSupplier = $_GET['kodesupplier'] ?? '';
        $filterStatus = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : '';

        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }

        $masterbarangModel = new Masterbarang();
        $items = $masterbarangModel->getAll(
            $page,
            $perPage,
            $search,
            $sortBy,
            $sortOrder,
            $filterPabrik,
            $filterGolongan,
            $filterSupplier,
            $filterStatus
        );
        $total = $masterbarangModel->count($search, $filterPabrik, $filterGolongan, $filterSupplier, $filterStatus);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        $tabelpabrikModel = new Tabelpabrik();
        $pabriks = $tabelpabrikModel->getAllActive();

        $tabelgolonganModel = new Tabelgolongan();
        $golongans = $tabelgolonganModel->getAllActive();

        $mastersupplierModel = new Mastersupplier();
        $suppliers = $mastersupplierModel->getAllActive();

        $data = [
            'items' => $items,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'pabriks' => $pabriks,
            'golongans' => $golongans,
            'suppliers' => $suppliers,
            'filterPabrik' => $filterPabrik,
            'filterGolongan' => $filterGolongan,
            'filterSupplier' => $filterSupplier,
            'filterStatus' => $filterStatus
        ];

        $this->view('masterbarang/index', $data);
    }

    public function show($id) {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $masterbarangModel = new Masterbarang();
        $item = $masterbarangModel->findById($id);

        if (!$item) {
            Session::flash('error', 'Barang tidak ditemukan');
            $this->redirect('/masterbarang');
        }

        $this->view('masterbarang/view', ['item' => $item]);
    }

    public function edit($id) {
        Auth::requireRole(['admin', 'manajemen', 'operator']);

        $masterbarangModel = new Masterbarang();
        $item = $masterbarangModel->findById($id);

        if (!$item) {
            Session::flash('error', 'Barang tidak ditemukan');
            $this->redirect('/masterbarang');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'namabarang' => $_POST['namabarang'] ?? $item['namabarang'],
                'satuan' => $_POST['satuan'] ?? null,
                'kodepabrik' => $_POST['kodepabrik'] ?? null,
                'kodegolongan' => $_POST['kodegolongan'] ?? null,
                'kodesupplier' => $_POST['kodesupplier'] ?? null,
                'kandungan' => $_POST['kandungan'] ?? null,
                'oot' => $_POST['oot'] ?? 'tidak',
                'prekursor' => $_POST['prekursor'] ?? 'tidak',
                'nie' => $_POST['nie'] ?? null,
                'kondisi' => $_POST['kondisi'] ?? null,
                'ed' => $_POST['ed'] ?? null,
                'hpp' => $_POST['hpp'] ?? null,
                'hargabeli' => $_POST['hargabeli'] ?? null,
                'discountbeli' => $_POST['discountbeli'] ?? null,
                'hargajual' => $_POST['hargajual'] ?? null,
                'discountjual' => $_POST['discountjual'] ?? null,
                'stokakhir' => $_POST['stokakhir'] ?? null,
                'status' => $_POST['status'] ?? $item['status'] ?? 'aktif'
            ];

            try {
                $masterbarangModel->update($id, $data);
                Session::flash('success', 'Data barang berhasil diperbarui');
                $this->redirect('/masterbarang');
            } catch (Exception $e) {
                Session::flash('error', 'Gagal memperbarui data: ' . $e->getMessage());
                $this->redirect('/masterbarang/edit/' . $id);
            }
        }

        $tabelpabrikModel = new Tabelpabrik();
        $pabriks = $tabelpabrikModel->getAllActive();

        $tabelgolonganModel = new Tabelgolongan();
        $golongans = $tabelgolonganModel->getAllActive();

        $mastersupplierModel = new Mastersupplier();
        $suppliers = $mastersupplierModel->getAllActive();

        $this->view('masterbarang/edit', [
            'item' => $item,
            'pabriks' => $pabriks,
            'golongans' => $golongans,
            'suppliers' => $suppliers
        ]);
    }
}


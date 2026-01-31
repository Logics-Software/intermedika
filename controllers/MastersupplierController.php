<?php
class MastersupplierController extends Controller {
    public function index() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        $status = $_GET['status'] ?? '';

        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }

        $validStatus = ['', 'aktif', 'nonaktif'];
        if (!in_array($status, $validStatus)) {
            $status = '';
        }

        $mastersupplierModel = new Mastersupplier();
        $suppliers = $mastersupplierModel->getAll($page, $perPage, $search, $sortBy, $sortOrder, $status);
        $total = $mastersupplierModel->count($search, $status);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        $data = [
            'suppliers' => $suppliers,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'status' => $status
        ];

        $this->view('mastersupplier/index', $data);
    }
}


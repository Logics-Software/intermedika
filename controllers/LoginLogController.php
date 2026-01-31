<?php
class LoginLogController extends Controller {
    public function index() {
        Auth::requireRole(['admin', 'manajemen']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'login_at';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        
        $loginLogModel = new LoginLog();
        $result = $loginLogModel->getAll($page, $perPage, $search, $status, $dateFrom, $dateTo, $sortBy, $sortOrder);
        
        $data = [
            'logs' => $result['data'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('loginlog/index', $data);
    }
}


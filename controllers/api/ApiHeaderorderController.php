<?php
class ApiHeaderorderController extends Controller {
    public function index() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        switch ($method) {
            case 'GET':
                $this->getHeaderorder();
                break;
            case 'POST':
            case 'PUT':
            case 'PATCH':
                $this->updateHeaderorder();
                break;
            default:
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    private function updateHeaderorder() {
        $input = $this->getInputData();
        if (!$input) {
            $this->json(['success' => false, 'message' => 'Invalid payload'], 400);
            return;
        }

        $noorder = $input['noorder'] ?? $_GET['noorder'] ?? null;
        if (!$noorder) {
            $this->json(['success' => false, 'message' => 'noorder is required'], 400);
            return;
        }

        $fields = [];
        if (isset($input['nopenjualan'])) {
            $fields['nopenjualan'] = trim((string)$input['nopenjualan']);
        }
        if (isset($input['status'])) {
            $fields['status'] = trim((string)$input['status']);
        }

        // Remove empty strings
        $fields = array_filter($fields, function ($value) {
            return $value !== '' && $value !== null;
        });

        if (empty($fields)) {
            $this->json(['success' => false, 'message' => 'No fields to update'], 400);
            return;
        }

        $headerModel = new Headerorder();
        $order = $headerModel->findByNoorder($noorder);
        if (!$order) {
            $this->json(['success' => false, 'message' => 'Order not found'], 404);
            return;
        }

        try {
            $headerModel->updateFields($noorder, $fields);
            $updated = $headerModel->findByNoorder($noorder);
            $this->json(['success' => true, 'message' => 'Headerorder updated', 'data' => $updated]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Failed to update headerorder',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getInputData() {
        $raw = file_get_contents('php://input');
        if ($raw) {
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
            parse_str($raw, $parsed);
            if (!empty($parsed)) {
                return $parsed;
            }
        }

        if (!empty($_POST)) {
            return $_POST;
        }

        return null;
    }

    private function getHeaderorder() {
        $noorder = $_GET['noorder'] ?? null;
        $headerModel = new Headerorder();
        $detailModel = new Detailorder();

        if ($noorder) {
            $order = $headerModel->findByNoorder($noorder);
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Order not found'], 404);
                return;
            }

            $details = $detailModel->getByNoorder($noorder);
            $order['details'] = $details;
            $this->json(['success' => true, 'data' => $order]);
            return;
        }

        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPage = isset($_GET['per_page']) ? max((int)$_GET['per_page'], 1) : 20;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? 'order';
        $kodesales = $_GET['kodesales'] ?? null;
        $kodecustomer = $_GET['kodecustomer'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        $options = [
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
            'status' => $status,
            'kodesales' => $kodesales,
            'kodecustomer' => $kodecustomer,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $data = $headerModel->getAll($options);
        $total = $headerModel->count($options);

        $this->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $perPage > 0 ? (int)ceil($total / $perPage) : 1,
            ],
        ]);
    }
}


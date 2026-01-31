<?php
class ApiTabelpabrikController extends Controller {
    public function index() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Handle method override (Router already handles this, but keep for compatibility)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        switch ($method) {
            case 'GET':
                $this->getTabelpabrik();
                break;
            case 'POST':
                $this->createTabelpabrik();
                break;
            case 'PUT':
            case 'PATCH':
                $this->updateTabelpabrik();
                break;
            case 'DELETE':
                $this->deleteTabelpabrik();
                break;
            default:
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    /**
     * Helper method to get input data from request
     * Uses stored raw input from Router if available
     */
    private function getInputData() {
        // Use stored raw input if available (from Router), otherwise read from php://input
        $rawInput = $GLOBALS['_RAW_INPUT'] ?? file_get_contents('php://input');
        
        if ($rawInput) {
            $json = json_decode($rawInput, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
            parse_str($rawInput, $parsed);
            if (!empty($parsed)) {
                return $parsed;
            }
        }

        if (!empty($_POST)) {
            return $_POST;
        }

        return null;
    }

    private function getTabelpabrik() {
        $id = $_GET['id'] ?? null;
        $kodepabrik = $_GET['kodepabrik'] ?? null;
        $tabelpabrikModel = new Tabelpabrik();

        if ($id) {
            $pabrik = $tabelpabrikModel->findById($id);
            if ($pabrik) {
                $this->json(['success' => true, 'data' => $pabrik]);
            } else {
                $this->json(['success' => false, 'message' => 'Tabelpabrik not found'], 404);
            }
            return;
        }

        if ($kodepabrik) {
            $pabrik = $tabelpabrikModel->findByKodepabrik($kodepabrik);
            if ($pabrik) {
                $this->json(['success' => true, 'data' => $pabrik]);
            } else {
                $this->json(['success' => false, 'message' => 'Tabelpabrik not found'], 404);
            }
            return;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 100;
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        $status = $_GET['status'] ?? '';

        $pabriks = $tabelpabrikModel->getAll($page, $perPage, $search, $sortBy, $sortOrder, $status);
        $total = $tabelpabrikModel->count($search, $status);

        $this->json([
            'success' => true,
            'data' => $pabriks,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $perPage > 0 ? (int)ceil($total / $perPage) : 1
            ]
        ]);
    }

    private function createTabelpabrik() {
        $input = $this->getInputData();
        
        if (!$input) {
            $input = $_POST;
        }

        $required = ['kodepabrik', 'namapabrik'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(['success' => false, 'message' => "Field {$field} is required"], 400);
                return;
            }
        }

        $tabelpabrikModel = new Tabelpabrik();

        if ($tabelpabrikModel->findByKodepabrik($input['kodepabrik'])) {
            $this->json(['success' => false, 'message' => 'Kode pabrik already exists'], 400);
            return;
        }

        $data = [
            'kodepabrik' => $input['kodepabrik'],
            'namapabrik' => $input['namapabrik'],
            'status' => $input['status'] ?? 'aktif'
        ];

        $id = $tabelpabrikModel->create($data);
        $pabrik = $tabelpabrikModel->findById($id);

        $this->json(['success' => true, 'message' => 'Tabelpabrik created', 'data' => $pabrik], 201);
    }

    private function updateTabelpabrik() {
        $input = $this->getInputData();
        
        if (!$input) {
            $input = $_POST;
        }

        $id = $input['id'] ?? $_GET['id'] ?? null;
        $kodepabrik = $input['kodepabrik'] ?? $_GET['kodepabrik'] ?? null;

        if (!$id && !$kodepabrik) {
            $this->json(['success' => false, 'message' => 'ID or kodepabrik is required'], 400);
            return;
        }

        $tabelpabrikModel = new Tabelpabrik();

        if ($id) {
            $pabrik = $tabelpabrikModel->findById($id);
        } else {
            $pabrik = $tabelpabrikModel->findByKodepabrik($kodepabrik);
            if ($pabrik) {
                $id = $pabrik['id'];
            }
        }

        if (!$pabrik) {
            $this->json(['success' => false, 'message' => 'Tabelpabrik not found'], 404);
            return;
        }

        $data = [];
        $allowedFields = ['kodepabrik', 'namapabrik', 'status'];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (isset($data['kodepabrik']) && $data['kodepabrik'] !== $pabrik['kodepabrik']) {
            $existing = $tabelpabrikModel->findByKodepabrik($data['kodepabrik']);
            if ($existing && $existing['id'] != $id) {
                $this->json(['success' => false, 'message' => 'Kode pabrik already exists'], 400);
                return;
            }
        }

        if (empty($data)) {
            $this->json(['success' => false, 'message' => 'No data to update'], 400);
            return;
        }

        $tabelpabrikModel->update($id, $data);
        $updated = $tabelpabrikModel->findById($id);

        $this->json(['success' => true, 'message' => 'Tabelpabrik updated', 'data' => $updated]);
    }

    private function deleteTabelpabrik() {
        $input = $this->getInputData();
        
        if (!$input) {
            $input = $_GET;
        }

        $id = $input['id'] ?? $_GET['id'] ?? null;
        $kodepabrik = $input['kodepabrik'] ?? $_GET['kodepabrik'] ?? null;

        if (!$id && !$kodepabrik) {
            $this->json(['success' => false, 'message' => 'ID or kodepabrik is required'], 400);
            return;
        }

        $tabelpabrikModel = new Tabelpabrik();

        if ($id) {
            $pabrik = $tabelpabrikModel->findById($id);
        } else {
            $pabrik = $tabelpabrikModel->findByKodepabrik($kodepabrik);
            if ($pabrik) {
                $id = $pabrik['id'];
            }
        }

        if (!$pabrik) {
            $this->json(['success' => false, 'message' => 'Tabelpabrik not found'], 404);
            return;
        }

        $tabelpabrikModel->delete($id);
        $this->json(['success' => true, 'message' => 'Tabelpabrik deleted']);
    }
}



<?php
class ApiTabelgolonganController extends Controller {
    public function index() {
        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        switch ($method) {
            case 'GET':
                $this->getTabelgolongan();
                break;
            case 'POST':
                $this->createTabelgolongan();
                break;
            case 'PUT':
            case 'PATCH':
                $this->updateTabelgolongan();
                break;
            case 'DELETE':
                $this->deleteTabelgolongan();
                break;
            default:
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    private function getTabelgolongan() {
        $id = $_GET['id'] ?? null;
        $kodegolongan = $_GET['kodegolongan'] ?? null;
        $tabelgolonganModel = new Tabelgolongan();

        if ($id) {
            $golongan = $tabelgolonganModel->findById($id);
            if ($golongan) {
                $this->json(['success' => true, 'data' => $golongan]);
            } else {
                $this->json(['success' => false, 'message' => 'Tabelgolongan not found'], 404);
            }
            return;
        }

        if ($kodegolongan) {
            $golongan = $tabelgolonganModel->findByKodegolongan($kodegolongan);
            if ($golongan) {
                $this->json(['success' => true, 'data' => $golongan]);
            } else {
                $this->json(['success' => false, 'message' => 'Tabelgolongan not found'], 404);
            }
            return;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 100;
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        $status = $_GET['status'] ?? '';

        $golongans = $tabelgolonganModel->getAll($page, $perPage, $search, $sortBy, $sortOrder, $status);
        $total = $tabelgolonganModel->count($search, $status);

        $this->json([
            'success' => true,
            'data' => $golongans,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $perPage > 0 ? (int)ceil($total / $perPage) : 1
            ]
        ]);
    }

    private function createTabelgolongan() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $input = $_POST;
        }

        $required = ['kodegolongan', 'namagolongan'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(['success' => false, 'message' => "Field {$field} is required"], 400);
                return;
            }
        }

        $tabelgolonganModel = new Tabelgolongan();

        if ($tabelgolonganModel->findByKodegolongan($input['kodegolongan'])) {
            $this->json(['success' => false, 'message' => 'Kode golongan already exists'], 400);
            return;
        }

        $data = [
            'kodegolongan' => $input['kodegolongan'],
            'namagolongan' => $input['namagolongan'],
            'status' => $input['status'] ?? 'aktif'
        ];

        $id = $tabelgolonganModel->create($data);
        $golongan = $tabelgolonganModel->findById($id);

        $this->json(['success' => true, 'message' => 'Tabelgolongan created', 'data' => $golongan], 201);
    }

    private function updateTabelgolongan() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            parse_str(file_get_contents('php://input'), $parsedData);
            $input = $parsedData ?: $_POST;
        }

        $id = $input['id'] ?? $_GET['id'] ?? null;
        $kodegolongan = $input['kodegolongan'] ?? $_GET['kodegolongan'] ?? null;

        if (!$id && !$kodegolongan) {
            $this->json(['success' => false, 'message' => 'ID or kodegolongan is required'], 400);
            return;
        }

        $tabelgolonganModel = new Tabelgolongan();

        if ($id) {
            $golongan = $tabelgolonganModel->findById($id);
        } else {
            $golongan = $tabelgolonganModel->findByKodegolongan($kodegolongan);
            if ($golongan) {
                $id = $golongan['id'];
            }
        }

        if (!$golongan) {
            $this->json(['success' => false, 'message' => 'Tabelgolongan not found'], 404);
            return;
        }

        $data = [];
        $allowedFields = ['kodegolongan', 'namagolongan', 'status'];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (isset($data['kodegolongan']) && $data['kodegolongan'] !== $golongan['kodegolongan']) {
            $existing = $tabelgolonganModel->findByKodegolongan($data['kodegolongan']);
            if ($existing && $existing['id'] != $id) {
                $this->json(['success' => false, 'message' => 'Kode golongan already exists'], 400);
                return;
            }
        }

        if (empty($data)) {
            $this->json(['success' => false, 'message' => 'No data to update'], 400);
            return;
        }

        $tabelgolonganModel->update($id, $data);
        $updated = $tabelgolonganModel->findById($id);

        $this->json(['success' => true, 'message' => 'Tabelgolongan updated', 'data' => $updated]);
    }

    private function deleteTabelgolongan() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            parse_str(file_get_contents('php://input'), $parsedData);
            $input = $parsedData ?: $_GET;
        }

        $id = $input['id'] ?? $_GET['id'] ?? null;
        $kodegolongan = $input['kodegolongan'] ?? $_GET['kodegolongan'] ?? null;

        if (!$id && !$kodegolongan) {
            $this->json(['success' => false, 'message' => 'ID or kodegolongan is required'], 400);
            return;
        }

        $tabelgolonganModel = new Tabelgolongan();

        if ($id) {
            $golongan = $tabelgolonganModel->findById($id);
        } else {
            $golongan = $tabelgolonganModel->findByKodegolongan($kodegolongan);
            if ($golongan) {
                $id = $golongan['id'];
            }
        }

        if (!$golongan) {
            $this->json(['success' => false, 'message' => 'Tabelgolongan not found'], 404);
            return;
        }

        $tabelgolonganModel->delete($id);
        $this->json(['success' => true, 'message' => 'Tabelgolongan deleted']);
    }
}



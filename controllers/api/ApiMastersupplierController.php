<?php
class ApiMastersupplierController extends Controller {
    public function index() {
        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        switch ($method) {
            case 'GET':
                $this->getMastersupplier();
                break;
            case 'POST':
                $this->createMastersupplier();
                break;
            case 'PUT':
            case 'PATCH':
                $this->updateMastersupplier();
                break;
            case 'DELETE':
                $this->deleteMastersupplier();
                break;
            default:
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    private function getMastersupplier() {
        $id = $_GET['id'] ?? null;
        $kodesupplier = $_GET['kodesupplier'] ?? null;
        $mastersupplierModel = new Mastersupplier();

        if ($id) {
            $supplier = $mastersupplierModel->findById($id);
            if ($supplier) {
                $this->json(['success' => true, 'data' => $supplier]);
            } else {
                $this->json(['success' => false, 'message' => 'Mastersupplier not found'], 404);
            }
            return;
        }

        if ($kodesupplier) {
            $supplier = $mastersupplierModel->findByKodesupplier($kodesupplier);
            if ($supplier) {
                $this->json(['success' => true, 'data' => $supplier]);
            } else {
                $this->json(['success' => false, 'message' => 'Mastersupplier not found'], 404);
            }
            return;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 100;
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        $status = $_GET['status'] ?? '';

        $suppliers = $mastersupplierModel->getAll($page, $perPage, $search, $sortBy, $sortOrder, $status);
        $total = $mastersupplierModel->count($search, $status);

        $this->json([
            'success' => true,
            'data' => $suppliers,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $perPage > 0 ? (int)ceil($total / $perPage) : 1
            ]
        ]);
    }

    private function createMastersupplier() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $input = $_POST;
        }

        $required = ['kodesupplier', 'namasupplier'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(['success' => false, 'message' => "Field {$field} is required"], 400);
                return;
            }
        }

        $mastersupplierModel = new Mastersupplier();

        if ($mastersupplierModel->findByKodesupplier($input['kodesupplier'])) {
            $this->json(['success' => false, 'message' => 'Kode supplier already exists'], 400);
            return;
        }

        $data = [
            'kodesupplier' => $input['kodesupplier'],
            'namasupplier' => $input['namasupplier'],
            'alamatsupplier' => $input['alamatsupplier'] ?? null,
            'notelepon' => $input['notelepon'] ?? null,
            'kontakperson' => $input['kontakperson'] ?? null,
            'status' => $input['status'] ?? 'aktif'
        ];

        $id = $mastersupplierModel->create($data);
        $supplier = $mastersupplierModel->findById($id);

        $this->json(['success' => true, 'message' => 'Mastersupplier created', 'data' => $supplier], 201);
    }

    private function updateMastersupplier() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            parse_str(file_get_contents('php://input'), $parsedData);
            $input = $parsedData ?: $_POST;
        }

        $id = $input['id'] ?? $_GET['id'] ?? null;
        $kodesupplier = $input['kodesupplier'] ?? $_GET['kodesupplier'] ?? null;

        if (!$id && !$kodesupplier) {
            $this->json(['success' => false, 'message' => 'ID or kodesupplier is required'], 400);
            return;
        }

        $mastersupplierModel = new Mastersupplier();

        if ($id) {
            $supplier = $mastersupplierModel->findById($id);
        } else {
            $supplier = $mastersupplierModel->findByKodesupplier($kodesupplier);
            if ($supplier) {
                $id = $supplier['id'];
            }
        }

        if (!$supplier) {
            $this->json(['success' => false, 'message' => 'Mastersupplier not found'], 404);
            return;
        }

        $data = [];
        $allowedFields = ['kodesupplier', 'namasupplier', 'alamatsupplier', 'notelepon', 'kontakperson', 'status'];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (isset($data['kodesupplier']) && $data['kodesupplier'] !== $supplier['kodesupplier']) {
            $existing = $mastersupplierModel->findByKodesupplier($data['kodesupplier']);
            if ($existing && $existing['id'] != $id) {
                $this->json(['success' => false, 'message' => 'Kode supplier already exists'], 400);
                return;
            }
        }

        if (empty($data)) {
            $this->json(['success' => false, 'message' => 'No data to update'], 400);
            return;
        }

        $mastersupplierModel->update($id, $data);
        $updated = $mastersupplierModel->findById($id);

        $this->json(['success' => true, 'message' => 'Mastersupplier updated', 'data' => $updated]);
    }

    private function deleteMastersupplier() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            parse_str(file_get_contents('php://input'), $parsedData);
            $input = $parsedData ?: $_GET;
        }

        $id = $input['id'] ?? $_GET['id'] ?? null;
        $kodesupplier = $input['kodesupplier'] ?? $_GET['kodesupplier'] ?? null;

        if (!$id && !$kodesupplier) {
            $this->json(['success' => false, 'message' => 'ID or kodesupplier is required'], 400);
            return;
        }

        $mastersupplierModel = new Mastersupplier();

        if ($id) {
            $supplier = $mastersupplierModel->findById($id);
        } else {
            $supplier = $mastersupplierModel->findByKodesupplier($kodesupplier);
            if ($supplier) {
                $id = $supplier['id'];
            }
        }

        if (!$supplier) {
            $this->json(['success' => false, 'message' => 'Mastersupplier not found'], 404);
            return;
        }

        $mastersupplierModel->delete($id);
        $this->json(['success' => true, 'message' => 'Mastersupplier deleted']);
    }
}



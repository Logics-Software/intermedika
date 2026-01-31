<?php
class ApiPerubahanhargaController extends Controller {
    public function index() {
        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        switch ($method) {
            case 'GET':
                $this->getPerubahanharga();
                break;
            case 'POST':
                $this->createPerubahanharga();
                break;
            case 'PUT':
            case 'PATCH':
                $this->updatePerubahanharga();
                break;
            case 'DELETE':
                $this->deletePerubahanharga();
                break;
            default:
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    private function getPerubahanharga() {
        $id = $_GET['id'] ?? null;
        $noperubahan = $_GET['noperubahan'] ?? null;
        $kodebarang = $_GET['kodebarang'] ?? null;

        $perubahanhargaModel = new Perubahanharga();

        // Find by ID
        if ($id) {
            $item = $perubahanhargaModel->findById($id);
            if ($item) {
                $this->json(['success' => true, 'data' => $item]);
            } else {
                $this->json(['success' => false, 'message' => 'Perubahanharga not found'], 404);
            }
            return;
        }

        // Find by noperubahan and kodebarang
        if ($noperubahan && $kodebarang) {
            $item = $perubahanhargaModel->findByNoperubahanAndKodebarang($noperubahan, $kodebarang);
            if ($item) {
                $this->json(['success' => true, 'data' => $item]);
            } else {
                $this->json(['success' => false, 'message' => 'Perubahanharga not found'], 404);
            }
            return;
        }

        // Find by noperubahan only
        if ($noperubahan) {
            $items = $perubahanhargaModel->findByNoperubahan($noperubahan);
            if ($items) {
                $this->json(['success' => true, 'data' => $items]);
            } else {
                $this->json(['success' => false, 'message' => 'Perubahanharga not found'], 404);
            }
            return;
        }

        // Find all with pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 100;
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'tanggalperubahan';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        $options = [
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
            'noperubahan' => $_GET['noperubahan'] ?? '',
            'kodebarang' => $_GET['kodebarang'] ?? '',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ];

        $items = $perubahanhargaModel->getAll($options);
        $total = $perubahanhargaModel->count($options);

        $this->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $perPage > 0 ? (int)ceil($total / $perPage) : 1
            ]
        ]);
    }

    private function createPerubahanharga() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $input = $_POST;
        }

        $required = ['noperubahan', 'tanggalperubahan', 'keterangan', 'kodebarang'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(['success' => false, 'message' => "Field {$field} is required"], 400);
                return;
            }
        }

        $perubahanhargaModel = new Perubahanharga();

        $data = [
            'noperubahan' => trim($input['noperubahan']),
            'tanggalperubahan' => $input['tanggalperubahan'],
            'keterangan' => trim($input['keterangan']),
            'kodebarang' => trim($input['kodebarang']),
            'hargalama' => isset($input['hargalama']) ? (float)$input['hargalama'] : 0,
            'discountlama' => isset($input['discountlama']) ? (float)$input['discountlama'] : 0,
            'hargabaru' => isset($input['hargabaru']) ? (float)$input['hargabaru'] : 0,
            'discountbaru' => isset($input['discountbaru']) ? (float)$input['discountbaru'] : 0
        ];

        try {
            $id = $perubahanhargaModel->create($data);
            $item = $perubahanhargaModel->findById($id);

            $this->json(['success' => true, 'message' => 'Perubahanharga created', 'data' => $item], 201);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to create: ' . $e->getMessage()], 500);
        }
    }

    private function updatePerubahanharga() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            parse_str(file_get_contents('php://input'), $parsedData);
            $input = $parsedData ?: $_POST;
        }

        $id = $input['id'] ?? $_GET['id'] ?? null;
        $noperubahan = $input['noperubahan'] ?? $_GET['noperubahan'] ?? null;
        $kodebarang = $input['kodebarang'] ?? $_GET['kodebarang'] ?? null;

        $perubahanhargaModel = new Perubahanharga();

        if ($id) {
            $item = $perubahanhargaModel->findById($id);
        } elseif ($noperubahan && $kodebarang) {
            $item = $perubahanhargaModel->findByNoperubahanAndKodebarang($noperubahan, $kodebarang);
            if ($item) {
                $id = $item['id'];
            }
        } else {
            $this->json(['success' => false, 'message' => 'ID or (noperubahan and kodebarang) is required'], 400);
            return;
        }

        if (!$item) {
            $this->json(['success' => false, 'message' => 'Perubahanharga not found'], 404);
            return;
        }

        $data = [];
        $allowedFields = [
            'noperubahan',
            'tanggalperubahan',
            'keterangan',
            'kodebarang',
            'hargalama',
            'discountlama',
            'hargabaru',
            'discountbaru'
        ];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                if (in_array($field, ['hargalama', 'discountlama', 'hargabaru', 'discountbaru'])) {
                    $data[$field] = (float)$input[$field];
                } else {
                    $data[$field] = trim($input[$field]);
                }
            }
        }

        if (empty($data)) {
            $this->json(['success' => false, 'message' => 'No data to update'], 400);
            return;
        }

        try {
            $perubahanhargaModel->update($id, $data);
            $updated = $perubahanhargaModel->findById($id);

            $this->json(['success' => true, 'message' => 'Perubahanharga updated', 'data' => $updated]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 500);
        }
    }

    private function deletePerubahanharga() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            parse_str(file_get_contents('php://input'), $parsedData);
            $input = $parsedData ?: $_GET;
        }

        $id = $input['id'] ?? $_GET['id'] ?? null;
        $noperubahan = $input['noperubahan'] ?? $_GET['noperubahan'] ?? null;

        $perubahanhargaModel = new Perubahanharga();

        // Delete by noperubahan (delete all records with that noperubahan)
        if ($noperubahan && !$id) {
            try {
                $perubahanhargaModel->deleteByNoperubahan($noperubahan);
                $this->json(['success' => true, 'message' => 'All perubahanharga with noperubahan ' . $noperubahan . ' deleted']);
            } catch (Exception $e) {
                $this->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
            }
            return;
        }

        // Delete by ID
        if ($id) {
            $item = $perubahanhargaModel->findById($id);
            if (!$item) {
                $this->json(['success' => false, 'message' => 'Perubahanharga not found'], 404);
                return;
            }

            try {
                $perubahanhargaModel->delete($id);
                $this->json(['success' => true, 'message' => 'Perubahanharga deleted']);
            } catch (Exception $e) {
                $this->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
            }
            return;
        }

        $this->json(['success' => false, 'message' => 'ID or noperubahan is required'], 400);
    }
}


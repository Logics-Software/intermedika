<?php
class ApiPembelianbarangController extends Controller {
    public function index() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Method override sudah ditangani di Router, tapi kita juga perlu handle JSON body
        // (untuk kasus khusus jika Router belum menangani)
        if ($method === 'POST') {
            // Check form data first
            if (isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
            }
            // Also check JSON body for method override
            elseif (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                // Use stored raw input if available (from Router), otherwise read from php://input
                $rawInput = $GLOBALS['_RAW_INPUT'] ?? file_get_contents('php://input');
                $jsonInput = json_decode($rawInput, true);
                if (isset($jsonInput['_method'])) {
                    $method = strtoupper($jsonInput['_method']);
                }
            }
        }

        switch ($method) {
            case 'GET':
                $this->getPembelianbarang();
                break;
            case 'POST':
                $this->createPembelianbarang();
                break;
            case 'PUT':
            case 'PATCH':
                $this->updatePembelianbarang();
                break;
            case 'DELETE':
                $this->deletePembelianbarang();
                break;
            default:
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    private function getPembelianbarang() {
        $id = $_GET['id'] ?? null;
        $nopembelian = $_GET['nopembelian'] ?? null;
        $kodebarang = $_GET['kodebarang'] ?? null;

        $pembelianModel = new Pembelianbarang();

        // Find by ID
        if ($id) {
            $item = $pembelianModel->findById($id);
            if ($item) {
                $this->json(['success' => true, 'data' => $item]);
            } else {
                $this->json(['success' => false, 'message' => 'Pembelianbarang not found'], 404);
            }
            return;
        }

        // Find by nopembelian and kodebarang
        if ($nopembelian && $kodebarang) {
            $item = $pembelianModel->findByNopembelianAndKodebarang($nopembelian, $kodebarang);
            if ($item) {
                $this->json(['success' => true, 'data' => $item]);
            } else {
                $this->json(['success' => false, 'message' => 'Pembelianbarang not found'], 404);
            }
            return;
        }

        // Find by nopembelian only
        if ($nopembelian) {
            $items = $pembelianModel->findByNopembelian($nopembelian);
            if ($items) {
                $this->json(['success' => true, 'data' => $items]);
            } else {
                $this->json(['success' => false, 'message' => 'Pembelianbarang not found'], 404);
            }
            return;
        }

        // Find all with pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 100;
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'tanggalpembelian';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        $options = [
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
            'nopembelian' => $_GET['nopembelian'] ?? '',
            'kodebarang' => $_GET['kodebarang'] ?? '',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ];

        $items = $pembelianModel->getAll($options);
        $total = $pembelianModel->count($options);

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

    private function createPembelianbarang() {
        // Use stored raw input if available (from Router), otherwise read from php://input
        $rawInput = $GLOBALS['_RAW_INPUT'] ?? file_get_contents('php://input');
        $input = json_decode($rawInput, true);

        if (!$input) {
            $input = $_POST;
        }

        $required = ['nopembelian', 'tanggalpembelian', 'namasupplier', 'kodebarang'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(['success' => false, 'message' => "Field {$field} is required"], 400);
                return;
            }
        }

        $pembelianModel = new Pembelianbarang();

        $data = [
            'nopembelian' => trim($input['nopembelian']),
            'tanggalpembelian' => $input['tanggalpembelian'],
            'namasupplier' => trim($input['namasupplier']),
            'kodebarang' => trim($input['kodebarang']),
            'jumlah' => isset($input['jumlah']) ? (float)$input['jumlah'] : 0,
            'harga' => isset($input['harga']) ? (float)$input['harga'] : 0,
            'discount' => isset($input['discount']) ? (float)$input['discount'] : 0,
            'totalharga' => isset($input['totalharga']) ? (float)$input['totalharga'] : 0
        ];

        try {
            $id = $pembelianModel->create($data);
            $item = $pembelianModel->findById($id);

            $this->json(['success' => true, 'message' => 'Pembelianbarang created', 'data' => $item], 201);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to create: ' . $e->getMessage()], 500);
        }
    }

    private function updatePembelianbarang() {
        // Use stored raw input if available (from Router), otherwise read from php://input
        $rawInput = $GLOBALS['_RAW_INPUT'] ?? file_get_contents('php://input');
        $input = json_decode($rawInput, true);

        if (!$input) {
            parse_str($rawInput, $parsedData);
            $input = $parsedData ?: $_POST;
        }

        $id = $input['id'] ?? $_GET['id'] ?? null;
        $nopembelian = $input['nopembelian'] ?? $_GET['nopembelian'] ?? null;
        $kodebarang = $input['kodebarang'] ?? $_GET['kodebarang'] ?? null;

        $pembelianModel = new Pembelianbarang();

        if ($id) {
            $item = $pembelianModel->findById($id);
        } elseif ($nopembelian && $kodebarang) {
            $item = $pembelianModel->findByNopembelianAndKodebarang($nopembelian, $kodebarang);
            if ($item) {
                $id = $item['id'];
            }
        } else {
            $this->json(['success' => false, 'message' => 'ID or (nopembelian and kodebarang) is required'], 400);
            return;
        }

        if (!$item) {
            $this->json(['success' => false, 'message' => 'Pembelianbarang not found'], 404);
            return;
        }

        $data = [];
        $allowedFields = [
            'nopembelian',
            'tanggalpembelian',
            'namasupplier',
            'kodebarang',
            'jumlah',
            'harga',
            'discount',
            'totalharga'
        ];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                if (in_array($field, ['jumlah', 'harga', 'discount', 'totalharga'])) {
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
            $pembelianModel->update($id, $data);
            $updated = $pembelianModel->findById($id);

            $this->json(['success' => true, 'message' => 'Pembelianbarang updated', 'data' => $updated]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 500);
        }
    }

    private function deletePembelianbarang() {
        // For DELETE, check query string first (common for DELETE requests)
        // Then check JSON body if available
        $id = $_GET['id'] ?? null;
        $nopembelian = $_GET['nopembelian'] ?? null;
        
        // If not in query string, try to read from request body (JSON or form data)
        if (!$id && !$nopembelian) {
            $rawInput = $GLOBALS['_RAW_INPUT'] ?? file_get_contents('php://input');
            if (!empty($rawInput)) {
                $input = json_decode($rawInput, true);
                if (!$input) {
                    parse_str($rawInput, $input);
                }
                if ($input) {
                    $id = $input['id'] ?? null;
                    $nopembelian = $input['nopembelian'] ?? null;
                }
            }
        }

        $pembelianModel = new Pembelianbarang();

        // Delete by nopembelian (delete all records with that nopembelian)
        if ($nopembelian && !$id) {
            try {
                $pembelianModel->deleteByNopembelian($nopembelian);
                $this->json(['success' => true, 'message' => 'All pembelianbarang with nopembelian ' . $nopembelian . ' deleted']);
            } catch (Exception $e) {
                $this->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
            }
            return;
        }

        // Delete by ID
        if ($id) {
            $item = $pembelianModel->findById($id);
            if (!$item) {
                $this->json(['success' => false, 'message' => 'Pembelianbarang not found'], 404);
                return;
            }

            try {
                $pembelianModel->delete($id);
                $this->json(['success' => true, 'message' => 'Pembelianbarang deleted']);
            } catch (Exception $e) {
                $this->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
            }
            return;
        }

        $this->json(['success' => false, 'message' => 'ID or nopembelian is required'], 400);
    }
}


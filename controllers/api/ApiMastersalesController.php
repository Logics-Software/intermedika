<?php
class ApiMastersalesController extends Controller {
    public function index() {
        // Simple API without token authentication
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Handle method override for PUT/DELETE/PATCH (VB6 compatibility)
        if (isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        if ($method === 'GET') {
            $this->getMastersales();
        } elseif ($method === 'POST') {
            $this->createMastersales();
        } elseif ($method === 'PUT' || $method === 'PATCH') {
            $this->updateMastersales();
        } elseif ($method === 'DELETE') {
            $this->deleteMastersales();
        } else {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }
    
    private function getMastersales() {
        $id = $_GET['id'] ?? null;
        $kodesales = $_GET['kodesales'] ?? null;
        $mastersalesModel = new Mastersales();
        
        if ($id) {
            $mastersales = $mastersalesModel->findById($id);
            if ($mastersales) {
                $this->json(['success' => true, 'data' => $mastersales]);
            } else {
                $this->json(['success' => false, 'message' => 'Mastersales not found'], 404);
            }
        } elseif ($kodesales) {
            $mastersales = $mastersalesModel->findByKodesales($kodesales);
            if ($mastersales) {
                $this->json(['success' => true, 'data' => $mastersales]);
            } else {
                $this->json(['success' => false, 'message' => 'Mastersales not found'], 404);
            }
        } else {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 100;
            $search = $_GET['search'] ?? '';
            $sortBy = $_GET['sort_by'] ?? 'id';
            $sortOrder = $_GET['sort_order'] ?? 'ASC';
            $status = $_GET['status'] ?? '';
            
            $allMastersales = $mastersalesModel->getAll($page, $perPage, $search, $sortBy, $sortOrder);
            $total = $mastersalesModel->count($search);
            
            // Filter by status if provided
            if (!empty($status)) {
                $allMastersales = array_filter($allMastersales, function($item) use ($status) {
                    return $item['status'] === $status;
                });
                $allMastersales = array_values($allMastersales);
            }
            
            $this->json([
                'success' => true,
                'data' => $allMastersales,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ]);
        }
    }
    
    private function createMastersales() {
        // Support both JSON and form-urlencoded (for VB6)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $required = ['kodesales', 'namasales'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(['success' => false, 'message' => "Field {$field} is required"], 400);
                return;
            }
        }
        
        $mastersalesModel = new Mastersales();
        
        // Check if kodesales already exists
        if ($mastersalesModel->findByKodesales($input['kodesales'])) {
            $this->json(['success' => false, 'message' => 'Kode sales already exists'], 400);
            return;
        }
        
        $data = [
            'kodesales' => $input['kodesales'],
            'namasales' => $input['namasales'],
            'alamatsales' => $input['alamatsales'] ?? null,
            'notelepon' => $input['notelepon'] ?? null,
            'status' => $input['status'] ?? 'aktif'
        ];
        
        $id = $mastersalesModel->create($data);
        $mastersales = $mastersalesModel->findById($id);
        
        $this->json(['success' => true, 'message' => 'Mastersales created', 'data' => $mastersales], 201);
    }
    
    private function updateMastersales() {
        // Support both JSON and form-urlencoded (for VB6)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            // Try parse_str for form-urlencoded
            parse_str(file_get_contents('php://input'), $putData);
            $input = $putData ?: $_POST;
        }
        
        $id = $input['id'] ?? $_GET['id'] ?? null;
        $kodesales = $input['kodesales'] ?? $_GET['kodesales'] ?? null;
        
        if (!$id && !$kodesales) {
            $this->json(['success' => false, 'message' => 'ID or kodesales is required'], 400);
            return;
        }
        
        $mastersalesModel = new Mastersales();
        
        if ($id) {
            $mastersales = $mastersalesModel->findById($id);
        } else {
            $mastersales = $mastersalesModel->findByKodesales($kodesales);
            if ($mastersales) {
                $id = $mastersales['id'];
            }
        }
        
        if (!$mastersales) {
            $this->json(['success' => false, 'message' => 'Mastersales not found'], 404);
            return;
        }
        
        $data = [];
        $allowedFields = ['kodesales', 'namasales', 'alamatsales', 'notelepon', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }
        
        // Check kodesales uniqueness if being updated
        if (isset($data['kodesales']) && $data['kodesales'] !== $mastersales['kodesales']) {
            $existing = $mastersalesModel->findByKodesales($data['kodesales']);
            if ($existing && $existing['id'] != $id) {
                $this->json(['success' => false, 'message' => 'Kode sales already exists'], 400);
                return;
            }
        }
        
        if (empty($data)) {
            $this->json(['success' => false, 'message' => 'No data to update'], 400);
            return;
        }
        
        $mastersalesModel->update($id, $data);
        $updatedMastersales = $mastersalesModel->findById($id);
        
        $this->json(['success' => true, 'message' => 'Mastersales updated', 'data' => $updatedMastersales]);
    }
    
    private function deleteMastersales() {
        // Support both JSON and form-urlencoded (for VB6)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            parse_str(file_get_contents('php://input'), $deleteData);
            $input = $deleteData ?: $_GET;
        }
        
        $id = $input['id'] ?? $_GET['id'] ?? null;
        $kodesales = $input['kodesales'] ?? $_GET['kodesales'] ?? null;
        
        if (!$id && !$kodesales) {
            $this->json(['success' => false, 'message' => 'ID or kodesales is required'], 400);
            return;
        }
        
        $mastersalesModel = new Mastersales();
        
        if ($id) {
            $mastersales = $mastersalesModel->findById($id);
        } else {
            $mastersales = $mastersalesModel->findByKodesales($kodesales);
            if ($mastersales) {
                $id = $mastersales['id'];
            }
        }
        
        if (!$mastersales) {
            $this->json(['success' => false, 'message' => 'Mastersales not found'], 404);
            return;
        }
        
        $mastersalesModel->delete($id);
        $this->json(['success' => true, 'message' => 'Mastersales deleted']);
    }
}


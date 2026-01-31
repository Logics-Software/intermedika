<?php
class ApiMastercustomerController extends Controller {
    public function index() {
        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $action = $_POST['action'] ?? $_GET['action'] ?? null;
        if ($method === 'POST' && $action === 'update_status') {
            $this->updateStatusByKodecustomer();
            return;
        }

        switch ($method) {
            case 'GET':
                $this->getMastercustomer();
                break;
            case 'POST':
                $this->createMastercustomer();
                break;
            case 'PUT':
            case 'PATCH':
                $this->updateMastercustomer($method);
                break;
            case 'DELETE':
                $this->deleteMastercustomer();
                break;
            default:
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    private function getMastercustomer() {
        $id = $_GET['id'] ?? null;
        $kodecustomer = $_GET['kodecustomer'] ?? null;
        $mastercustomerModel = new Mastercustomer();

        if ($id) {
            $customer = $mastercustomerModel->findById($id);
            if ($customer) {
                $this->json(['success' => true, 'data' => $customer]);
            } else {
                $this->json(['success' => false, 'message' => 'Mastercustomer not found'], 404);
            }
            return;
        }

        if ($kodecustomer) {
            $customer = $mastercustomerModel->findByKodecustomer($kodecustomer);
            if ($customer) {
                $this->json(['success' => true, 'data' => $customer]);
            } else {
                $this->json(['success' => false, 'message' => 'Mastercustomer not found'], 404);
            }
            return;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 100;
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        $status = $_GET['status'] ?? '';

        $customers = $mastercustomerModel->getAll($page, $perPage, $search, $sortBy, $sortOrder, $status);
        $total = $mastercustomerModel->count($search, $status);

        $this->json([
            'success' => true,
            'data' => $customers,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $perPage > 0 ? (int)ceil($total / $perPage) : 1
            ]
        ]);
    }

    private function createMastercustomer() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $input = $_POST;
        }

        $required = ['kodecustomer', 'namacustomer'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(['success' => false, 'message' => "Field {$field} is required"], 400);
                return;
            }
        }

        $mastercustomerModel = new Mastercustomer();

        if ($mastercustomerModel->findByKodecustomer($input['kodecustomer'])) {
            $this->json(['success' => false, 'message' => 'Kode customer already exists'], 400);
            return;
        }

        // Normalize date fields - convert empty strings to null
        $normalizeDate = function($value) {
            return ($value === '' || $value === null) ? null : $value;
        };

        $data = [
            'kodecustomer' => $input['kodecustomer'],
            'namacustomer' => $input['namacustomer'],
            'namabadanusaha' => $input['namabadanusaha'] ?? null,
            'alamatcustomer' => $input['alamatcustomer'] ?? null,
            'kotacustomer' => $input['kotacustomer'] ?? null,
            'notelepon' => $input['notelepon'] ?? null,
            'kontakperson' => $input['kontakperson'] ?? null,
            'statuspkp' => $input['statuspkp'] ?? 'nonpkp',
            'npwp' => $input['npwp'] ?? null,
            'namawp' => $input['namawp'] ?? null,
            'alamatwp' => $input['alamatwp'] ?? null,
            'namaapoteker' => $input['namaapoteker'] ?? null,
            'nosipa' => $input['nosipa'] ?? null,
            'tanggaledsipa' => $normalizeDate($input['tanggaledsipa'] ?? null),
            'noijinusaha' => $input['noijinusaha'] ?? null,
            'tanggaledijinusaha' => $normalizeDate($input['tanggaledijinusaha'] ?? null),
            'nocdob' => $input['nocdob'] ?? null,
            'tanggaledcdob' => $normalizeDate($input['tanggaledcdob'] ?? null),
            'latitude' => $input['latitude'] ?? null,
            'longitude' => $input['longitude'] ?? null,
            'userid' => $input['userid'] ?? null,
            'status' => $input['status'] ?? 'baru'
        ];
        
        // Normalize status - ensure it's one of the allowed values and not too long
        $statusValue = !empty($data['status']) ? trim($data['status']) : 'baru';
        $statusValue = strtolower($statusValue);
        
        // Limit length to prevent truncation (max 10 characters)
        if (strlen($statusValue) > 10) {
            $statusValue = substr($statusValue, 0, 10);
        }
        
        $allowedStatus = ['baru', 'updated', 'aktif', 'nonaktif'];
        if (!in_array($statusValue, $allowedStatus, true)) {
            $data['status'] = 'baru';
        } else {
            $data['status'] = $statusValue;
        }

        $id = $mastercustomerModel->create($data);
        $customer = $mastercustomerModel->findById($id);

        $this->json(['success' => true, 'message' => 'Mastercustomer created', 'data' => $customer], 201);
    }

    private function updateMastercustomer($methodOverride = 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            parse_str(file_get_contents('php://input'), $parsedData);
            $input = $parsedData ?: $_POST;
        }

        $id = $input['id'] ?? $_GET['id'] ?? null;
        $kodecustomer = $input['kodecustomer'] ?? $_GET['kodecustomer'] ?? null;

        if (!$id && !$kodecustomer) {
            $this->json(['success' => false, 'message' => 'ID or kodecustomer is required'], 400);
            return;
        }

        $mastercustomerModel = new Mastercustomer();

        if ($id) {
            $customer = $mastercustomerModel->findById($id);
        } else {
            $customer = $mastercustomerModel->findByKodecustomer($kodecustomer);
            if ($customer) {
                $id = $customer['id'];
            }
        }

        if (!$customer) {
            $this->json(['success' => false, 'message' => 'Mastercustomer not found'], 404);
            return;
        }

        $data = $input;

        if (isset($data['kodecustomer']) && $data['kodecustomer'] !== $customer['kodecustomer']) {
            $existing = $mastercustomerModel->findByKodecustomer($data['kodecustomer']);
            if ($existing && $existing['id'] != $id) {
                $this->json(['success' => false, 'message' => 'Kode customer already exists'], 400);
                return;
            }
        }

        unset($data['id']);

        if (empty(array_diff_key($data, array_flip(['_method'])))) {
            $this->json(['success' => false, 'message' => 'No data to update'], 400);
            return;
        }

        unset($data['_method']);

        // Normalize date fields - convert empty strings to null
        $dateFields = ['tanggaledsipa', 'tanggaledijinusaha', 'tanggaledcdob'];
        foreach ($dateFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $mastercustomerModel->update($id, $data);
        $updated = $mastercustomerModel->findById($id);

        $this->json(['success' => true, 'message' => 'Mastercustomer updated', 'data' => $updated]);
    }

    private function updateStatusByKodecustomer() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            parse_str(file_get_contents('php://input'), $parsedData);
            $input = $parsedData ?: $_POST;
        }

        $kodecustomer = $input['kodecustomer'] ?? null;
        $status = $input['status'] ?? null;

        if (empty($kodecustomer) || empty($status)) {
            $this->json(['success' => false, 'message' => 'kodecustomer and status are required'], 400);
            return;
        }

        $mastercustomerModel = new Mastercustomer();
        $result = $mastercustomerModel->updateStatusByKodecustomer($kodecustomer, $status);

        if ($result) {
            $updated = $mastercustomerModel->findByKodecustomer($kodecustomer);
            $this->json(['success' => true, 'message' => 'Status updated', 'data' => $updated]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update status'], 400);
        }
    }

    private function deleteMastercustomer() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            parse_str(file_get_contents('php://input'), $parsedData);
            $input = $parsedData ?: $_GET;
        }

        $id = $input['id'] ?? $_GET['id'] ?? null;
        $kodecustomer = $input['kodecustomer'] ?? $_GET['kodecustomer'] ?? null;

        if (!$id && !$kodecustomer) {
            $this->json(['success' => false, 'message' => 'ID or kodecustomer is required'], 400);
            return;
        }

        $mastercustomerModel = new Mastercustomer();

        if ($id) {
            $customer = $mastercustomerModel->findById($id);
        } else {
            $customer = $mastercustomerModel->findByKodecustomer($kodecustomer);
            if ($customer) {
                $id = $customer['id'];
            }
        }

        if (!$customer) {
            $this->json(['success' => false, 'message' => 'Mastercustomer not found'], 404);
            return;
        }

        $mastercustomerModel->delete($id);
        $this->json(['success' => true, 'message' => 'Mastercustomer deleted']);
    }
}


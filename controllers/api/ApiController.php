<?php
class ApiController extends Controller {
    public function users() {
        // Simple API without token authentication
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Handle method override for PUT/DELETE
        if (isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        if ($method === 'GET') {
            $this->getUsers();
        } elseif ($method === 'POST') {
            $this->createUser();
        } elseif ($method === 'PUT') {
            $this->updateUser();
        } elseif ($method === 'DELETE') {
            $this->deleteUser();
        } else {
            $this->json(['error' => 'Method not allowed'], 405);
        }
    }
    
    private function getUsers() {
        $id = $_GET['id'] ?? null;
        $userModel = new User();
        
        if ($id) {
            $user = $userModel->findById($id);
            if ($user) {
                unset($user['password']);
                $this->json(['success' => true, 'data' => $user]);
            } else {
                $this->json(['success' => false, 'message' => 'User not found'], 404);
            }
        } else {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
            $search = $_GET['search'] ?? '';
            $sortBy = $_GET['sort_by'] ?? 'id';
            $sortOrder = $_GET['sort_order'] ?? 'ASC';
            
            $users = $userModel->getAll($page, $perPage, $search, $sortBy, $sortOrder);
            $total = $userModel->count($search);
            
            // Remove passwords from response
            foreach ($users as &$user) {
                unset($user['password']);
            }
            
            $this->json([
                'success' => true,
                'data' => $users,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ]);
        }
    }
    
    private function createUser() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $required = ['username', 'namalengkap', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(['success' => false, 'message' => "Field {$field} is required"], 400);
                return;
            }
        }
        
        $userModel = new User();
        
        if ($userModel->findByUsername($input['username'])) {
            $this->json(['success' => false, 'message' => 'Username already exists'], 400);
            return;
        }
        
        if ($userModel->findByEmail($input['email'])) {
            $this->json(['success' => false, 'message' => 'Email already exists'], 400);
            return;
        }
        
        $data = [
            'username' => $input['username'],
            'namalengkap' => $input['namalengkap'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => $input['role'] ?? 'sales',
            'kodesales' => $input['kodesales'] ?? null,
            'status' => $input['status'] ?? 'aktif'
        ];
        
        $id = $userModel->create($data);
        $user = $userModel->findById($id);
        unset($user['password']);
        
        $this->json(['success' => true, 'message' => 'User created', 'data' => $user], 201);
    }
    
    private function updateUser() {
        // Parse input from PUT request
        parse_str(file_get_contents('php://input'), $putData);
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $putData ?: $_POST;
        }
        
        $id = $input['id'] ?? $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'User ID is required'], 400);
            return;
        }
        
        $userModel = new User();
        $user = $userModel->findById($id);
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }
        
        $data = [];
        $allowedFields = ['username', 'namalengkap', 'email', 'role', 'kodesales', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }
        
        if (isset($input['password'])) {
            $data['password'] = $input['password'];
        }
        
        if (empty($data)) {
            $this->json(['success' => false, 'message' => 'No data to update'], 400);
            return;
        }
        
        $userModel->update($id, $data);
        $updatedUser = $userModel->findById($id);
        unset($updatedUser['password']);
        
        $this->json(['success' => true, 'message' => 'User updated', 'data' => $updatedUser]);
    }
    
    private function deleteUser() {
        // Parse input from DELETE request
        parse_str(file_get_contents('php://input'), $deleteData);
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $deleteData ?: $_GET;
        }
        
        $id = $input['id'] ?? $_GET['id'] ?? null;
        if (!$id) {
            $this->json(['success' => false, 'message' => 'User ID is required'], 400);
            return;
        }
        
        $userModel = new User();
        $user = $userModel->findById($id);
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }
        
        $userModel->delete($id);
        $this->json(['success' => true, 'message' => 'User deleted']);
    }
}


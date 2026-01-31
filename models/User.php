<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ? AND status = 'aktif'";
        return $this->db->fetchOne($sql, [$username]);
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        return $this->db->fetchOne($sql, [$email]);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'id', $sortOrder = 'ASC') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (username LIKE ? OR namalengkap LIKE ? OR email LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        $validSortColumns = ['id', 'username', 'namalengkap', 'email', 'role', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT * FROM users WHERE {$where} ORDER BY {$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function count($search = '') {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (username LIKE ? OR namalengkap LIKE ? OR email LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        $sql = "SELECT COUNT(*) as total FROM users WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    public function create($data) {
        $sql = "INSERT INTO users (username, namalengkap, email, password, role, kodesales, picture, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['username'],
            $data['namalengkap'],
            $data['email'],
            $this->hashPassword($data['password']),
            $data['role'],
            $data['kodesales'] ?? null,
            $data['picture'] ?? null,
            $data['status'] ?? 'aktif'
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['username', 'namalengkap', 'email', 'role', 'kodesales', 'picture', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (isset($data['password'])) {
            $fields[] = "password = ?";
            $params[] = $this->hashPassword($data['password']);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}


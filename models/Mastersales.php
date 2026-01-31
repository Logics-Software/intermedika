<?php
class Mastersales {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM mastersales WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function findByKodesales($kodesales) {
        $sql = "SELECT * FROM mastersales WHERE kodesales = ?";
        return $this->db->fetchOne($sql, [$kodesales]);
    }
    
    public function getAll($page = 1, $perPage = 100, $search = '', $sortBy = 'id', $sortOrder = 'ASC') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (kodesales LIKE ? OR namasales LIKE ? OR alamatsales LIKE ? OR notelepon LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        $validSortColumns = ['id', 'kodesales', 'namasales', 'alamatsales', 'notelepon', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT * FROM mastersales WHERE {$where} ORDER BY {$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function count($search = '') {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (kodesales LIKE ? OR namasales LIKE ? OR alamatsales LIKE ? OR notelepon LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        $sql = "SELECT COUNT(*) as total FROM mastersales WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    public function create($data) {
        $sql = "INSERT INTO mastersales (kodesales, namasales, alamatsales, notelepon, status) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $data['kodesales'],
            $data['namasales'],
            $data['alamatsales'] ?? null,
            $data['notelepon'] ?? null,
            $data['status'] ?? 'aktif'
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['kodesales', 'namasales', 'alamatsales', 'notelepon', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE mastersales SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM mastersales WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getAllActive() {
        $sql = "SELECT * FROM mastersales WHERE status = 'aktif' ORDER BY kodesales ASC";
        return $this->db->fetchAll($sql, []);
    }
}


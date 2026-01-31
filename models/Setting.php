<?php
class Setting {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all settings
     */
    public function getAll() {
        $sql = "SELECT * FROM setting";
        return $this->db->fetchOne($sql);
    }
    
    /**
     * Get setting by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM setting WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Get first/main setting (usually there's only one record)
     */
    public function getMainSetting() {
        $sql = "SELECT * FROM setting LIMIT 1";
        return $this->db->fetchOne($sql);
    }
    
    /**
     * Create new setting record
     */
    public function create($data) {
        $sql = "INSERT INTO setting (order_online, inkaso_online) VALUES (?, ?)";
        $params = [
            $data['order_online'] ?? 'nonaktif',
            $data['inkaso_online'] ?? 'nonaktif'
        ];
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update setting
     */
    public function update($id, $data) {
        $allowedFields = ['order_online', 'inkaso_online'];
        $fields = [];
        $params = [];
        
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
        $sql = "UPDATE setting SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $this->db->query($sql, $params);
        return true;
    }
    
    /**
     * Save or create setting (insert if not exists, update if exists)
     */
    public function saveOrCreate($data) {
        $existing = $this->getMainSetting();
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->create($data);
        }
    }
}

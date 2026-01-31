<?php
class VisitActivity {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO visit_activities (visit_id, activity_type, deskripsi, timestamp) VALUES (?, ?, ?, ?)";
        $params = [
            $data['visit_id'],
            $data['activity_type'],
            $data['deskripsi'] ?? null,
            $data['timestamp'] ?? date('Y-m-d H:i:s')
        ];
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function listByVisit($visitId) {
        $sql = "SELECT * FROM visit_activities WHERE visit_id = ? ORDER BY timestamp ASC";
        return $this->db->fetchAll($sql, [$visitId]);
    }
}


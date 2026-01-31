<?php
class VisitFile {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO visit_files (visit_id, filename, original_filename, file_size, file_type, uploaded_at) VALUES (?, ?, ?, ?, ?, ?)";
        $params = [
            $data['visit_id'],
            $data['filename'],
            $data['original_filename'],
            $data['file_size'],
            $data['file_type'],
            $data['uploaded_at'] ?? date('Y-m-d H:i:s')
        ];
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function listByVisit($visitId) {
        $sql = "SELECT * FROM visit_files WHERE visit_id = ? ORDER BY uploaded_at ASC";
        return $this->db->fetchAll($sql, [$visitId]);
    }

    public function findById($fileId) {
        $sql = "SELECT * FROM visit_files WHERE file_id = ?";
        return $this->db->fetchOne($sql, [$fileId]);
    }

    public function delete($fileId) {
        $file = $this->findById($fileId);
        if ($file) {
            $sql = "DELETE FROM visit_files WHERE file_id = ?";
            $this->db->query($sql, [$fileId]);
            return $file;
        }
        return null;
    }
}


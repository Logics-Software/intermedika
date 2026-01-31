<?php
class Tabelaktivitas {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'id', $sortOrder = 'ASC') {
        $offset = ($page - 1) * $perPage;

        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            $where .= " AND aktivitas LIKE ?";
            $params[] = "%{$search}%";
        }

        $validSortColumns = ['id', 'aktivitas', 'status'];
        $sortBy = in_array($sortBy, $validSortColumns, true) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT id, aktivitas, status FROM tabelaktivitas WHERE {$where} ORDER BY {$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function count($search = '') {
        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            $where .= " AND aktivitas LIKE ?";
            $params[] = "%{$search}%";
        }

        $sql = "SELECT COUNT(*) as total FROM tabelaktivitas WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return (int)($result['total'] ?? 0);
    }

    public function findById($id) {
        $sql = "SELECT id, aktivitas, status FROM tabelaktivitas WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    public function create($data) {
        $sql = "INSERT INTO tabelaktivitas (aktivitas, status) VALUES (?, ?)";
        $this->db->query($sql, [
            $data['aktivitas'],
            $data['status']
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];

        if (isset($data['aktivitas'])) {
            $fields[] = "aktivitas = ?";
            $params[] = $data['aktivitas'];
        }

        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE tabelaktivitas SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->query($sql, $params);
    }

    public function delete($id) {
        $sql = "DELETE FROM tabelaktivitas WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}


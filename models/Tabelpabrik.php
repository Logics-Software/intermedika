<?php
class Tabelpabrik {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        $sql = "SELECT * FROM tabelpabrik WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    public function findByKodepabrik($kodepabrik) {
        $sql = "SELECT * FROM tabelpabrik WHERE kodepabrik = ?";
        return $this->db->fetchOne($sql, [$kodepabrik]);
    }

    public function getAll($page = 1, $perPage = 100, $search = '', $sortBy = 'id', $sortOrder = 'ASC', $status = '') {
        $offset = ($page - 1) * $perPage;

        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (kodepabrik LIKE ? OR namapabrik LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($status)) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $validSortColumns = ['id', 'kodepabrik', 'namapabrik', 'status', 'created_at', 'updated_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM tabelpabrik WHERE {$where} ORDER BY {$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function count($search = '', $status = '') {
        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (kodepabrik LIKE ? OR namapabrik LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($status)) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $sql = "SELECT COUNT(*) as total FROM tabelpabrik WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function create($data) {
        $sql = "INSERT INTO tabelpabrik (kodepabrik, namapabrik, status) VALUES (?, ?, ?)";
        $params = [
            $data['kodepabrik'],
            $data['namapabrik'],
            $data['status'] ?? 'aktif'
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];
        $allowedFields = ['kodepabrik', 'namapabrik', 'status'];

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
        $sql = "UPDATE tabelpabrik SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->query($sql, $params);
    }

    public function delete($id) {
        $sql = "DELETE FROM tabelpabrik WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getAllActive() {
        $sql = "SELECT * FROM tabelpabrik WHERE status = 'aktif' ORDER BY kodepabrik ASC";
        return $this->db->fetchAll($sql, []);
    }
}


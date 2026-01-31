<?php
class Tabelgolongan {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        $sql = "SELECT * FROM tabelgolongan WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    public function findByKodegolongan($kodegolongan) {
        $sql = "SELECT * FROM tabelgolongan WHERE kodegolongan = ?";
        return $this->db->fetchOne($sql, [$kodegolongan]);
    }

    public function getAll($page = 1, $perPage = 100, $search = '', $sortBy = 'id', $sortOrder = 'ASC', $status = '') {
        $offset = ($page - 1) * $perPage;

        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (kodegolongan LIKE ? OR namagolongan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($status)) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $validSortColumns = ['id', 'kodegolongan', 'namagolongan', 'status', 'created_at', 'updated_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM tabelgolongan WHERE {$where} ORDER BY {$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function count($search = '', $status = '') {
        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (kodegolongan LIKE ? OR namagolongan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($status)) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $sql = "SELECT COUNT(*) as total FROM tabelgolongan WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function create($data) {
        $sql = "INSERT INTO tabelgolongan (kodegolongan, namagolongan, status) VALUES (?, ?, ?)";
        $params = [
            $data['kodegolongan'],
            $data['namagolongan'],
            $data['status'] ?? 'aktif'
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];
        $allowedFields = ['kodegolongan', 'namagolongan', 'status'];

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
        $sql = "UPDATE tabelgolongan SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->query($sql, $params);
    }

    public function delete($id) {
        $sql = "DELETE FROM tabelgolongan WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getAllActive() {
        $sql = "SELECT * FROM tabelgolongan WHERE status = 'aktif' ORDER BY kodegolongan ASC";
        return $this->db->fetchAll($sql, []);
    }
}


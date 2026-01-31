<?php
class Mastersupplier {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        $sql = "SELECT * FROM mastersupplier WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    public function findByKodesupplier($kodesupplier) {
        $sql = "SELECT * FROM mastersupplier WHERE kodesupplier = ?";
        return $this->db->fetchOne($sql, [$kodesupplier]);
    }

    public function getAll($page = 1, $perPage = 100, $search = '', $sortBy = 'id', $sortOrder = 'ASC', $status = '') {
        $offset = ($page - 1) * $perPage;

        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (kodesupplier LIKE ? OR namasupplier LIKE ? OR alamatsupplier LIKE ? OR kontakperson LIKE ? OR notelepon LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_fill(0, 5, $searchParam);
        }

        if (!empty($status)) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $validSortColumns = [
            'id',
            'kodesupplier',
            'namasupplier',
            'alamatsupplier',
            'kontakperson',
            'notelepon',
            'status',
            'created_at',
            'updated_at'
        ];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM mastersupplier WHERE {$where} ORDER BY {$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function count($search = '', $status = '') {
        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (kodesupplier LIKE ? OR namasupplier LIKE ? OR alamatsupplier LIKE ? OR kontakperson LIKE ? OR notelepon LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_fill(0, 5, $searchParam);
        }

        if (!empty($status)) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $sql = "SELECT COUNT(*) as total FROM mastersupplier WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function create($data) {
        $sql = "INSERT INTO mastersupplier (kodesupplier, namasupplier, alamatsupplier, notelepon, kontakperson, status) VALUES (?, ?, ?, ?, ?, ?)";
        $params = [
            $data['kodesupplier'],
            $data['namasupplier'],
            $data['alamatsupplier'] ?? null,
            $data['notelepon'] ?? null,
            $data['kontakperson'] ?? null,
            $data['status'] ?? 'aktif'
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];
        $allowedFields = ['kodesupplier', 'namasupplier', 'alamatsupplier', 'notelepon', 'kontakperson', 'status'];

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
        $sql = "UPDATE mastersupplier SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->query($sql, $params);
    }

    public function delete($id) {
        $sql = "DELETE FROM mastersupplier WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getAllActive() {
        $sql = "SELECT * FROM mastersupplier WHERE status = 'aktif' ORDER BY kodesupplier ASC";
        return $this->db->fetchAll($sql, []);
    }
}


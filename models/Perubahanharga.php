<?php
class Perubahanharga {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        $sql = "SELECT ph.*, mb.namabarang, mb.satuan
                FROM perubahanharga ph
                LEFT JOIN masterbarang mb ON ph.kodebarang = mb.kodebarang
                WHERE ph.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    public function findByNoperubahan($noperubahan) {
        $sql = "SELECT ph.*, mb.namabarang, mb.satuan
                FROM perubahanharga ph
                LEFT JOIN masterbarang mb ON ph.kodebarang = mb.kodebarang
                WHERE ph.noperubahan = ?
                ORDER BY ph.id ASC";
        return $this->db->fetchAll($sql, [$noperubahan]);
    }

    public function findByNoperubahanAndKodebarang($noperubahan, $kodebarang) {
        $sql = "SELECT ph.*, mb.namabarang, mb.satuan
                FROM perubahanharga ph
                LEFT JOIN masterbarang mb ON ph.kodebarang = mb.kodebarang
                WHERE ph.noperubahan = ? AND ph.kodebarang = ?";
        return $this->db->fetchOne($sql, [$noperubahan, $kodebarang]);
    }

    public function getAll($options = []) {
        $page = $options['page'] ?? 1;
        $perPage = $options['per_page'] ?? 10;
        $search = $options['search'] ?? '';
        $noperubahan = $options['noperubahan'] ?? '';
        $kodebarang = $options['kodebarang'] ?? '';
        $startDate = $options['start_date'] ?? null;
        $endDate = $options['end_date'] ?? null;
        $sortBy = $options['sort_by'] ?? 'tanggalperubahan';
        $sortOrder = strtoupper($options['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $offset = ($page - 1) * $perPage;

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(ph.noperubahan LIKE ? OR ph.keterangan LIKE ? OR mb.namabarang LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($noperubahan)) {
            $where[] = "ph.noperubahan = ?";
            $params[] = $noperubahan;
        }

        if (!empty($kodebarang)) {
            $where[] = "ph.kodebarang = ?";
            $params[] = $kodebarang;
        }

        if (!empty($startDate) && !empty($endDate)) {
            $where[] = "ph.tanggalperubahan BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $validSortColumns = [
            'id',
            'noperubahan',
            'tanggalperubahan',
            'keterangan',
            'kodebarang',
            'hargalama',
            'discountlama',
            'hargabaru',
            'discountbaru',
            'namabarang'
        ];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'tanggalperubahan';
        
        // Handle sorting for joined columns
        if ($sortBy === 'namabarang') {
            $sortColumn = 'mb.namabarang';
        } else {
            $sortColumn = 'ph.' . $sortBy;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT ph.*, mb.namabarang, mb.satuan
                FROM perubahanharga ph
                LEFT JOIN masterbarang mb ON ph.kodebarang = mb.kodebarang
                WHERE {$whereClause}
                ORDER BY {$sortColumn} {$sortOrder}
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function count($options = []) {
        $search = $options['search'] ?? '';
        $noperubahan = $options['noperubahan'] ?? '';
        $kodebarang = $options['kodebarang'] ?? '';
        $startDate = $options['start_date'] ?? null;
        $endDate = $options['end_date'] ?? null;

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(ph.noperubahan LIKE ? OR ph.keterangan LIKE ? OR mb.namabarang LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($noperubahan)) {
            $where[] = "ph.noperubahan = ?";
            $params[] = $noperubahan;
        }

        if (!empty($kodebarang)) {
            $where[] = "ph.kodebarang = ?";
            $params[] = $kodebarang;
        }

        if (!empty($startDate) && !empty($endDate)) {
            $where[] = "ph.tanggalperubahan BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT COUNT(*) as total
                FROM perubahanharga ph
                LEFT JOIN masterbarang mb ON ph.kodebarang = mb.kodebarang
                WHERE {$whereClause}";

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function create($data) {
        $sql = "INSERT INTO perubahanharga (
                    noperubahan,
                    tanggalperubahan,
                    keterangan,
                    kodebarang,
                    hargalama,
                    discountlama,
                    hargabaru,
                    discountbaru
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['noperubahan'],
            $data['tanggalperubahan'],
            $data['keterangan'],
            $data['kodebarang'],
            $data['hargalama'] ?? 0,
            $data['discountlama'] ?? 0,
            $data['hargabaru'] ?? 0,
            $data['discountbaru'] ?? 0
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];

        $allowedFields = [
            'noperubahan',
            'tanggalperubahan',
            'keterangan',
            'kodebarang',
            'hargalama',
            'discountlama',
            'hargabaru',
            'discountbaru'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE perubahanharga SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->query($sql, $params);
    }

    public function delete($id) {
        $sql = "DELETE FROM perubahanharga WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function deleteByNoperubahan($noperubahan) {
        $sql = "DELETE FROM perubahanharga WHERE noperubahan = ?";
        return $this->db->query($sql, [$noperubahan]);
    }

    public function findAll() {
        $sql = "SELECT ph.*, mb.namabarang, mb.satuan
                FROM perubahanharga ph
                LEFT JOIN masterbarang mb ON ph.kodebarang = mb.kodebarang
                ORDER BY ph.tanggalperubahan DESC, ph.noperubahan ASC";
        return $this->db->fetchAll($sql);
    }
}


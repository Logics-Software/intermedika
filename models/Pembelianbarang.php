<?php
class Pembelianbarang {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        $sql = "SELECT pb.*, mb.namabarang, mb.satuan
                FROM pembelianbarang pb
                LEFT JOIN masterbarang mb ON pb.kodebarang = mb.kodebarang
                WHERE pb.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    public function findByNopembelian($nopembelian) {
        $sql = "SELECT pb.*, mb.namabarang, mb.satuan
                FROM pembelianbarang pb
                LEFT JOIN masterbarang mb ON pb.kodebarang = mb.kodebarang
                WHERE pb.nopembelian = ?
                ORDER BY pb.id ASC";
        return $this->db->fetchAll($sql, [$nopembelian]);
    }

    public function findByNopembelianAndKodebarang($nopembelian, $kodebarang) {
        $sql = "SELECT pb.*, mb.namabarang, mb.satuan
                FROM pembelianbarang pb
                LEFT JOIN masterbarang mb ON pb.kodebarang = mb.kodebarang
                WHERE pb.nopembelian = ? AND pb.kodebarang = ?";
        return $this->db->fetchOne($sql, [$nopembelian, $kodebarang]);
    }

    public function getAll($options = []) {
        $page = $options['page'] ?? 1;
        $perPage = $options['per_page'] ?? 10;
        $search = $options['search'] ?? '';
        $nopembelian = $options['nopembelian'] ?? '';
        $kodebarang = $options['kodebarang'] ?? '';
        $startDate = $options['start_date'] ?? null;
        $endDate = $options['end_date'] ?? null;
        $sortBy = $options['sort_by'] ?? 'tanggalpembelian';
        $sortOrder = strtoupper($options['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $offset = ($page - 1) * $perPage;

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(pb.nopembelian LIKE ? OR pb.namasupplier LIKE ? OR mb.namabarang LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($nopembelian)) {
            $where[] = "pb.nopembelian = ?";
            $params[] = $nopembelian;
        }

        if (!empty($kodebarang)) {
            $where[] = "pb.kodebarang = ?";
            $params[] = $kodebarang;
        }

        if (!empty($startDate) && !empty($endDate)) {
            $where[] = "pb.tanggalpembelian BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $validSortColumns = [
            'id',
            'nopembelian',
            'tanggalpembelian',
            'namasupplier',
            'kodebarang',
            'jumlah',
            'harga',
            'discount',
            'totalharga',
            'namabarang'
        ];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'tanggalpembelian';
        
        // Handle sorting for joined columns
        if ($sortBy === 'namabarang') {
            $sortColumn = 'mb.namabarang';
        } else {
            $sortColumn = 'pb.' . $sortBy;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT pb.*, mb.namabarang, mb.satuan
                FROM pembelianbarang pb
                LEFT JOIN masterbarang mb ON pb.kodebarang = mb.kodebarang
                WHERE {$whereClause}
                ORDER BY {$sortColumn} {$sortOrder}
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function count($options = []) {
        $search = $options['search'] ?? '';
        $nopembelian = $options['nopembelian'] ?? '';
        $kodebarang = $options['kodebarang'] ?? '';
        $startDate = $options['start_date'] ?? null;
        $endDate = $options['end_date'] ?? null;

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(pb.nopembelian LIKE ? OR pb.namasupplier LIKE ? OR mb.namabarang LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($nopembelian)) {
            $where[] = "pb.nopembelian = ?";
            $params[] = $nopembelian;
        }

        if (!empty($kodebarang)) {
            $where[] = "pb.kodebarang = ?";
            $params[] = $kodebarang;
        }

        if (!empty($startDate) && !empty($endDate)) {
            $where[] = "pb.tanggalpembelian BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT COUNT(*) as total
                FROM pembelianbarang pb
                LEFT JOIN masterbarang mb ON pb.kodebarang = mb.kodebarang
                WHERE {$whereClause}";

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function create($data) {
        $sql = "INSERT INTO pembelianbarang (
                    nopembelian,
                    tanggalpembelian,
                    namasupplier,
                    kodebarang,
                    jumlah,
                    harga,
                    discount,
                    totalharga
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['nopembelian'],
            $data['tanggalpembelian'],
            $data['namasupplier'],
            $data['kodebarang'],
            $data['jumlah'] ?? 0,
            $data['harga'] ?? 0,
            $data['discount'] ?? 0,
            $data['totalharga'] ?? 0
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];

        $allowedFields = [
            'nopembelian',
            'tanggalpembelian',
            'namasupplier',
            'kodebarang',
            'jumlah',
            'harga',
            'discount',
            'totalharga'
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
        $sql = "UPDATE pembelianbarang SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->query($sql, $params);
    }

    public function delete($id) {
        $sql = "DELETE FROM pembelianbarang WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function deleteByNopembelian($nopembelian) {
        $sql = "DELETE FROM pembelianbarang WHERE nopembelian = ?";
        return $this->db->query($sql, [$nopembelian]);
    }

    public function findAll() {
        $sql = "SELECT pb.*, mb.namabarang, mb.satuan
                FROM pembelianbarang pb
                LEFT JOIN masterbarang mb ON pb.kodebarang = mb.kodebarang
                ORDER BY pb.tanggalpembelian DESC, pb.nopembelian ASC";
        return $this->db->fetchAll($sql);
    }
}


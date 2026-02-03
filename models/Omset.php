<?php
class Omset {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($tahun = null, $bulan = null, $page = 1, $perPage = 20, $kodesales = null) {
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        if ($tahun !== null && $tahun !== '') {
            $where[] = 'tahun = ?';
            $params[] = $tahun;
        }

        if ($bulan !== null && $bulan !== '') {
            $where[] = 'bulan = ?';
            $params[] = $bulan;
        }

        if ($kodesales !== null && $kodesales !== '') {
            $where[] = 'kodesales = ?';
            $params[] = $kodesales;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT * FROM omset {$whereClause} ORDER BY tahun DESC, bulan DESC, kodesales ASC LIMIT ? OFFSET ?";
        
        $paramsWithLimit = array_merge($params, [$perPage, $offset]);
        return $this->db->fetchAll($sql, $paramsWithLimit);
    }

    public function count($tahun = null, $bulan = null, $kodesales = null) {
        $where = [];
        $params = [];

        if ($tahun !== null && $tahun !== '') {
            $where[] = 'tahun = ?';
            $params[] = $tahun;
        }

        if ($bulan !== null && $bulan !== '') {
            $where[] = 'bulan = ?';
            $params[] = $bulan;
        }

        if ($kodesales !== null && $kodesales !== '') {
            $where[] = 'kodesales = ?';
            $params[] = $kodesales;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT COUNT(*) as total FROM omset {$whereClause}";
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function findByKey($tahun, $bulan, $kodesales) {
        $sql = "SELECT * FROM omset WHERE tahun = ? AND bulan = ? AND kodesales = ?";
        return $this->db->fetchOne($sql, [$tahun, $bulan, $kodesales]);
    }

    public function create($data) {
        $sql = "INSERT INTO omset (
            tahun, bulan, kodesales, namasales, jumlahfaktur, penjualan, returpenjualan,
            penjualanbersih, targetpenjualan, prosenpenjualan, penerimaantunai, cnpenjualan,
            pencairangiro, penerimaanbersih, targetpenerimaan, prosenpenerimaan
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['tahun'] ?? null,
            $data['bulan'] ?? null,
            $data['kodesales'] ?? null,
            $data['namasales'] ?? null,
            $data['jumlahfaktur'] ?? 0,
            $data['penjualan'] ?? 0,
            $data['returpenjualan'] ?? 0,
            $data['penjualanbersih'] ?? 0,
            $data['targetpenjualan'] ?? 0,
            $data['prosenpenjualan'] ?? 0,
            $data['penerimaantunai'] ?? 0,
            $data['cnpenjualan'] ?? 0,
            $data['pencairangiro'] ?? 0,
            $data['penerimaanbersih'] ?? 0,
            $data['targetpenerimaan'] ?? 0,
            $data['prosenpenerimaan'] ?? 0
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function deleteByTahunBulan($tahun, $bulan) {
        $sql = "DELETE FROM omset WHERE tahun = ? AND bulan = ?";
        $stmt = $this->db->query($sql, [$tahun, $bulan]);
        return $stmt->rowCount();
    }

    public function getDistinctYears() {
        $sql = "SELECT DISTINCT tahun FROM omset ORDER BY tahun DESC";
        $results = $this->db->fetchAll($sql);
        return array_column($results, 'tahun');
    }

    public function update($tahun, $bulan, $kodesales, $data) {
        $fields = [];
        $params = [];

        $allowedFields = [
            'namasales',
            'jumlahfaktur',
            'penjualan',
            'returpenjualan',
            'penjualanbersih',
            'targetpenjualan',
            'prosenpenjualan',
            'penerimaantunai',
            'cnpenjualan',
            'pencairangiro',
            'penerimaanbersih',
            'targetpenerimaan',
            'prosenpenerimaan'
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

        $params[] = $tahun;
        $params[] = $bulan;
        $params[] = $kodesales;

        $sql = "UPDATE omset SET " . implode(', ', $fields) . " WHERE tahun = ? AND bulan = ? AND kodesales = ?";
        
        return $this->db->query($sql, $params);
    }
}


<?php
class OmsetHarian {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($startDate = null, $endDate = null, $page = 1, $perPage = 20, $kodesales = null) {
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        if ($startDate !== null && $startDate !== '' && $endDate !== null && $endDate !== '') {
            $where[] = 'tanggal BETWEEN ? AND ?';
            $params[] = $startDate;
            $params[] = $endDate;
        } elseif ($startDate !== null && $startDate !== '') {
            $where[] = 'tanggal = ?';
            $params[] = $startDate;
        }

        if ($kodesales !== null && $kodesales !== '') {
            $where[] = 'kodesales = ?';
            $params[] = $kodesales;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT * FROM omset_harian {$whereClause} ORDER BY tanggal DESC, kodesales ASC LIMIT ? OFFSET ?";
        
        $paramsWithLimit = array_merge($params, [$perPage, $offset]);
        // Handle PDO param types for limit/offset if necessary or just rely on driver
        // Usually fetchAll handles params as strings, but LIMIT/OFFSET sometimes need int.
        // Assuming Database class handles this correctly or user doesn't strictly need int binding.
        return $this->db->fetchAll($sql, $paramsWithLimit);
    }

    public function count($startDate = null, $endDate = null, $kodesales = null) {
        $where = [];
        $params = [];

        if ($startDate !== null && $startDate !== '' && $endDate !== null && $endDate !== '') {
            $where[] = 'tanggal BETWEEN ? AND ?';
            $params[] = $startDate;
            $params[] = $endDate;
        } elseif ($startDate !== null && $startDate !== '') {
            $where[] = 'tanggal = ?';
            $params[] = $startDate;
        }

        if ($kodesales !== null && $kodesales !== '') {
            $where[] = 'kodesales = ?';
            $params[] = $kodesales;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT COUNT(*) as total FROM omset_harian {$whereClause}";
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function findByKey($tanggal, $kodesales) {
        $sql = "SELECT * FROM omset_harian WHERE tanggal = ? AND kodesales = ?";
        return $this->db->fetchOne($sql, [$tanggal, $kodesales]);
    }

    public function create($data) {
        $sql = "INSERT INTO omset_harian (
            tanggal, kodesales, namasales, jumlahfaktur, penjualan, returpenjualan,
            penjualanbersih, targetpenjualan, prosenpenjualan, penerimaantunai, cnpenjualan,
            pencairangiro, penerimaanbersih, targetpenerimaan, prosenpenerimaan
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['tanggal'] ?? null,
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

    public function update($tanggal, $kodesales, $data) {
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

        $params[] = $tanggal;
        $params[] = $kodesales;

        $sql = "UPDATE omset_harian SET " . implode(', ', $fields) . " WHERE tanggal = ? AND kodesales = ?";
        
        return $this->db->query($sql, $params);
    }

    public function deleteByTanggal($tanggal) {
        $sql = "DELETE FROM omset_harian WHERE tanggal = ?";
        $stmt = $this->db->query($sql, [$tanggal]);
        return $stmt->rowCount();
    }

    public function getDistinctDates() {
        $sql = "SELECT DISTINCT tanggal FROM omset_harian ORDER BY tanggal DESC";
        $results = $this->db->fetchAll($sql);
        return array_column($results, 'tanggal');
    }

    public function getSummaryBySales($startDate = null, $endDate = null, $kodesales = null) {
        $where = [];
        $params = [];

        if ($startDate !== null && $startDate !== '' && $endDate !== null && $endDate !== '') {
            $where[] = 'tanggal BETWEEN ? AND ?';
            $params[] = $startDate;
            $params[] = $endDate;
        } elseif ($startDate !== null && $startDate !== '') {
            $where[] = 'tanggal = ?';
            $params[] = $startDate;
        }

        if ($kodesales !== null && $kodesales !== '') {
            $where[] = 'kodesales = ?';
            $params[] = $kodesales;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT 
                    kodesales, 
                    namasales,
                    SUM(jumlahfaktur) as total_jumlahfaktur,
                    SUM(penjualan) as total_penjualan,
                    SUM(returpenjualan) as total_returpenjualan,
                    SUM(penjualanbersih) as total_penjualanbersih,
                    SUM(targetpenjualan) as total_targetpenjualan,
                    SUM(penerimaantunai) as total_penerimaantunai,
                    SUM(cnpenjualan) as total_cnpenjualan,
                    SUM(pencairangiro) as total_pencairangiro,
                    SUM(penerimaanbersih) as total_penerimaanbersih,
                    SUM(targetpenerimaan) as total_targetpenerimaan
                FROM omset_harian 
                {$whereClause} 
                GROUP BY kodesales, namasales 
                ORDER BY namasales ASC";

        return $this->db->fetchAll($sql, $params);
    }
}

<?php
class Visit {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO visits (user_id, kodesales, customer_id, kodecustomer, check_in_time, check_out_time, check_in_lat, check_in_long, check_out_lat, check_out_long, status_kunjungan, catatan, jarak_dari_kantor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['user_id'],
            $data['kodesales'],
            $data['customer_id'],
            $data['kodecustomer'],
            $data['check_in_time'],
            $data['check_out_time'] ?? null,
            $data['check_in_lat'],
            $data['check_in_long'],
            $data['check_out_lat'] ?? null,
            $data['check_out_long'] ?? null,
            $data['status_kunjungan'] ?? 'Sedang Berjalan',
            $data['catatan'] ?? null,
            $data['jarak_dari_kantor'] ?? null
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function update($visitId, $data) {
        $fields = [];
        $params = [];

        foreach (['check_out_time', 'check_out_lat', 'check_out_long', 'status_kunjungan', 'catatan', 'updated_at', 'jarak_dari_kantor'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $visitId;
        $sql = "UPDATE visits SET " . implode(', ', $fields) . " WHERE visit_id = ?";
        $this->db->query($sql, $params);
        return true;
    }

    public function findById($visitId) {
        $sql = "SELECT v.*, mc.namacustomer, mc.kodecustomer as master_kodecustomer, mc.alamatcustomer, mc.kotacustomer, mc.latitude, mc.longitude
                FROM visits v
                LEFT JOIN mastercustomer mc ON v.customer_id = mc.id
                WHERE v.visit_id = ?";
        return $this->db->fetchOne($sql, [$visitId]);
    }

    public function findActiveByUser($userId) {
        $sql = "SELECT v.*, mc.namacustomer
                FROM visits v
                LEFT JOIN mastercustomer mc ON v.customer_id = mc.id
                WHERE v.user_id = ? AND v.status_kunjungan = 'Sedang Berjalan'
                ORDER BY v.check_in_time DESC
                LIMIT 1";
        return $this->db->fetchOne($sql, [$userId]);
    }

    public function listByUser($userId, $page = 1, $perPage = 20, $status = '', $search = '', $periode = 'today', $kodecustomer = '', $startDate = '', $endDate = '') {
        $offset = ($page - 1) * $perPage;
        $params = [$userId];
        $where = ['v.user_id = ?'];

        if (!empty($status) && in_array($status, ['Direncanakan', 'Sedang Berjalan', 'Selesai', 'Dibatalkan'])) {
            $where[] = 'v.status_kunjungan = ?';
            $params[] = $status;
        }

        if (!empty($search)) {
            $where[] = '(mc.namacustomer LIKE ? OR mc.kodecustomer LIKE ? OR mc.kotacustomer LIKE ?)';
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }

        // Filter periode
        if (!empty($periode)) {
            $filterStartDate = null;
            $filterEndDate = null;
            
            if ($periode === 'custom' && !empty($startDate) && !empty($endDate)) {
                // Custom date range
                $filterStartDate = $startDate;
                $filterEndDate = $endDate;
            } else {
                switch ($periode) {
                    case 'week':
                        $filterStartDate = date('Y-m-d', strtotime('monday this week'));
                        $filterEndDate = date('Y-m-d', strtotime('sunday this week'));
                        break;
                    case 'month':
                        $filterStartDate = date('Y-m-01');
                        $filterEndDate = date('Y-m-t');
                        break;
                    case 'year':
                        $filterStartDate = date('Y-01-01');
                        $filterEndDate = date('Y-12-31');
                        break;
                    case 'today':
                    default:
                        $filterStartDate = date('Y-m-d');
                        $filterEndDate = date('Y-m-d');
                        break;
                }
            }
            
            if ($filterStartDate && $filterEndDate) {
                $where[] = 'DATE(v.check_in_time) BETWEEN ? AND ?';
                $params[] = $filterStartDate;
                $params[] = $filterEndDate;
            }
        }

        // Filter customer
        if (!empty($kodecustomer)) {
            $where[] = 'mc.kodecustomer = ?';
            $params[] = $kodecustomer;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT v.*, mc.namacustomer, mc.kodecustomer as master_kodecustomer, mc.kotacustomer
                FROM visits v
                LEFT JOIN mastercustomer mc ON v.customer_id = mc.id
                {$whereSql}
                ORDER BY v.check_in_time DESC
                LIMIT ? OFFSET ?";

        $paramsWithLimit = array_merge($params, [$perPage, $offset]);
        $rows = $this->db->fetchAll($sql, $paramsWithLimit);

        $countSql = "SELECT COUNT(*) as total FROM visits v LEFT JOIN mastercustomer mc ON v.customer_id = mc.id {$whereSql}";
        $totalRow = $this->db->fetchOne($countSql, $params);
        $total = $totalRow ? (int)$totalRow['total'] : 0;

        return [
            'data' => $rows,
            'total' => $total
        ];
    }
}


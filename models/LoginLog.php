<?php
class LoginLog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $this->purgeOldLogs();

        $sql = "INSERT INTO login_log (user_id, session_token, ip_address, user_agent, login_at, logout_at, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $data['user_id'] ?? null,
            $data['session_token'] ?? null,
            $data['ip_address'] ?? null,
            $data['user_agent'] ?? null,
            $data['login_at'] ?? date('Y-m-d H:i:s'),
            $data['logout_at'] ?? null,
            $data['status'] ?? 'success'
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function markLogout($sessionToken) {
        if (empty($sessionToken)) {
            return false;
        }

        $sql = "UPDATE login_log SET logout_at = ?, status = 'logout', updated_at = NOW() WHERE session_token = ? AND logout_at IS NULL";
        $params = [date('Y-m-d H:i:s'), $sessionToken];
        $this->db->query($sql, $params);
        return true;
    }

    public function purgeOldLogs() {
        $sql = "DELETE FROM login_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        $this->db->query($sql);
    }

    public function getAll($page = 1, $perPage = 20, $search = '', $status = '', $dateFrom = '', $dateTo = '', $sortBy = 'login_at', $sortOrder = 'DESC') {
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(u.username LIKE ? OR u.namalengkap LIKE ? OR ll.ip_address LIKE ? OR ll.user_agent LIKE ? OR ll.session_token LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, array_fill(0, 5, $searchParam));
        }

        if (!empty($status) && in_array($status, ['success', 'failed', 'logout'])) {
            $where[] = "ll.status = ?";
            $params[] = $status;
        }

        if (!empty($dateFrom)) {
            $where[] = "DATE(ll.login_at) >= ?";
            $params[] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $where[] = "DATE(ll.login_at) <= ?";
            $params[] = $dateTo;
        }

        $validSortColumns = ['login_at', 'logout_at', 'status', 'ip_address', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'login_at';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql = "SELECT ll.*, u.username, u.namalengkap, u.role
                FROM login_log ll
                LEFT JOIN users u ON ll.user_id = u.id
                {$whereSql}
                ORDER BY ll.{$sortBy} {$sortOrder}
                LIMIT ? OFFSET ?";

        $paramsWithLimit = $params;
        $paramsWithLimit[] = $perPage;
        $paramsWithLimit[] = $offset;

        $rows = $this->db->fetchAll($sql, $paramsWithLimit);

        $countSql = "SELECT COUNT(*) as total FROM login_log ll LEFT JOIN users u ON ll.user_id = u.id {$whereSql}";
        $totalRow = $this->db->fetchOne($countSql, $params);
        $total = $totalRow ? (int)$totalRow['total'] : 0;

        return [
            'data' => $rows,
            'total' => $total
        ];
    }

    public function countToday() {
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as total FROM login_log WHERE DATE(login_at) = ?";
        $result = $this->db->fetchOne($sql, [$today]);
        return $result ? (int)$result['total'] : 0;
    }
}


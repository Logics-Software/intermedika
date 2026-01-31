<?php
class Masterbarang {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        $sql = "SELECT mb.*, tp.namapabrik, tg.namagolongan, ms.namasupplier
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                LEFT JOIN tabelgolongan tg ON mb.kodegolongan = tg.kodegolongan
                LEFT JOIN mastersupplier ms ON mb.kodesupplier = ms.kodesupplier
                WHERE mb.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    public function findByKodebarang($kodebarang) {
        $sql = "SELECT mb.*, tp.namapabrik, tg.namagolongan, ms.namasupplier
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                LEFT JOIN tabelgolongan tg ON mb.kodegolongan = tg.kodegolongan
                LEFT JOIN mastersupplier ms ON mb.kodesupplier = ms.kodesupplier
                WHERE mb.kodebarang = ?";
        return $this->db->fetchOne($sql, [$kodebarang]);
    }

    public function getAll(
        $page = 1,
        $perPage = 100,
        $search = '',
        $sortBy = 'id',
        $sortOrder = 'ASC',
        $kodepabrik = '',
        $kodegolongan = '',
        $kodesupplier = '',
        $status = ''
    ) {
        $offset = ($page - 1) * $perPage;

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if (!empty($kodesupplier)) {
            $where[] = "mb.kodesupplier = ?";
            $params[] = $kodesupplier;
        }

        if (!empty($status)) {
            $normalizedStatus = $this->normalizeStatus($status, null);
            if ($normalizedStatus !== null) {
                $where[] = "mb.status = ?";
                $params[] = $normalizedStatus;
            }
        }

        $validSortColumns = [
            'id',
            'kodebarang',
            'namabarang',
            'kodepabrik',
            'kodegolongan',
            'namapabrik',
            'namagolongan',
            'kodesupplier',
            'hpp',
            'hargajual',
            'stokakhir',
            'status',
            'created_at',
            'updated_at'
        ];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        // Map sort column to actual database column (for joined tables)
        $sortColumnMap = [
            'namapabrik' => 'tp.namapabrik',
            'namagolongan' => 'tg.namagolongan',
            'kodepabrik' => 'mb.kodepabrik',
            'kodegolongan' => 'mb.kodegolongan',
            'kodesupplier' => 'mb.kodesupplier',
            'hpp' => 'mb.hpp',
            'hargajual' => 'mb.hargajual',
            'stokakhir' => 'mb.stokakhir',
            'status' => 'mb.status',
            'id' => 'mb.id',
            'kodebarang' => 'mb.kodebarang',
            'namabarang' => 'mb.namabarang',
            'created_at' => 'mb.created_at',
            'updated_at' => 'mb.updated_at'
        ];
        $orderByColumn = $sortColumnMap[$sortBy] ?? 'mb.id';

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT mb.*, tp.namapabrik, tg.namagolongan, ms.namasupplier
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                LEFT JOIN tabelgolongan tg ON mb.kodegolongan = tg.kodegolongan
                LEFT JOIN mastersupplier ms ON mb.kodesupplier = ms.kodesupplier
                WHERE {$whereClause}
                ORDER BY {$orderByColumn} {$sortOrder}
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function count($search = '', $kodepabrik = '', $kodegolongan = '', $kodesupplier = '', $status = '') {
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if (!empty($kodesupplier)) {
            $where[] = "mb.kodesupplier = ?";
            $params[] = $kodesupplier;
        }

        if (!empty($status)) {
            $normalizedStatus = $this->normalizeStatus($status, null);
            if ($normalizedStatus !== null) {
                $where[] = "mb.status = ?";
                $params[] = $normalizedStatus;
            }
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as total FROM masterbarang mb WHERE {$whereClause}";

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function create($data) {
        $sql = "INSERT INTO masterbarang (
                    kodebarang,
                    namabarang,
                    satuan,
                    kodepabrik,
                    kodegolongan,
                    kodesupplier,
                    kandungan,
                    oot,
                    prekursor,
                    nie,
                    kondisi,
                    ed,
                    hpp,
                    hargabeli,
                    discountbeli,
                    hargajual,
                    discountjual,
                    stokakhir,
                    status
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $params = [
            $data['kodebarang'],
            $data['namabarang'],
            $data['satuan'] ?? null,
            $data['kodepabrik'] ?? null,
            $data['kodegolongan'] ?? null,
            $data['kodesupplier'] ?? null,
            $data['kandungan'] ?? null,
            $data['oot'] ?? 'tidak',
            $data['prekursor'] ?? 'tidak',
            $data['nie'] ?? null,
            $data['kondisi'] ?? null,
            $data['ed'] ?? null,
            $data['hpp'] ?? null,
            $data['hargabeli'] ?? null,
            $data['discountbeli'] ?? null,
            $data['hargajual'] ?? null,
            $data['discountjual'] ?? null,
            $data['stokakhir'] ?? null,
            $this->normalizeStatus($data['status'] ?? 'aktif')
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];

        $allowedFields = [
            'kodebarang',
            'namabarang',
            'satuan',
            'kodepabrik',
            'kodegolongan',
            'kodesupplier',
            'kandungan',
            'oot',
            'prekursor',
            'nie',
            'kondisi',
            'ed',
            'hpp',
            'hargabeli',
            'discountbeli',
            'hargajual',
            'discountjual',
            'stokakhir',
            'status'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $value = $field === 'status'
                    ? $this->normalizeStatus($data[$field])
                    : $data[$field];
                $fields[] = "{$field} = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE masterbarang SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->query($sql, $params);
    }

    public function delete($id) {
        $sql = "DELETE FROM masterbarang WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getAllActive() {
        $sql = "SELECT * FROM masterbarang WHERE status = 'aktif' ORDER BY kodebarang ASC";
        return $this->db->fetchAll($sql, []);
    }

    public function getAllForSelection() {
        $sql = "SELECT mb.kodebarang,
                       mb.namabarang,
                       mb.satuan,
                       mb.hargajual,
                       mb.discountjual,
                       mb.stokakhir,
                       mb.kodepabrik,
                       tp.namapabrik,
                       mb.kodegolongan,
                       tg.namagolongan,
                       mb.kandungan,
                       mb.oot,
                       mb.prekursor,
                       mb.nie,
                       mb.kondisi,
                       mb.ed
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                LEFT JOIN tabelgolongan tg ON mb.kodegolongan = tg.kodegolongan
                WHERE mb.status = 'aktif'
                ORDER BY mb.namabarang ASC";
        return $this->db->fetchAll($sql);
    }

    private function normalizeStatus($status, $default = 'aktif') {
        if ($status === null || $status === '') {
            return $default;
        }

        if (!is_string($status)) {
            return $default;
        }

        $value = strtolower(trim($status));
        $allowed = ['aktif', 'nonaktif'];

        if (in_array($value, $allowed, true)) {
            return $value;
        }

        return $default;
    }
}


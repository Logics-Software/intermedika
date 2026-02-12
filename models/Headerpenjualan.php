<?php
class Headerpenjualan {
	private $db;

	public function __construct() {
		$this->db = Database::getInstance();
	}

	public function getAll($options = []) {
		$page = max((int)($options['page'] ?? 1), 1);
		$perPage = max((int)($options['per_page'] ?? 10), 1);
		$search = trim($options['search'] ?? '');
		$kodesales = $options['kodesales'] ?? null;
		$periode = $options['periode'] ?? 'today';
		$startDate = $options['start_date'] ?? null;
		$endDate = $options['end_date'] ?? null;
		$statuspkp = $options['statuspkp'] ?? null;

		[$filterStart, $filterEnd] = $this->resolveDateRange($periode, $startDate, $endDate);

		$offset = ($page - 1) * $perPage;
		$params = [];
		$where = ["1=1"];

		if ($filterStart && $filterEnd) {
			$where[] = "hp.tanggalpenjualan BETWEEN ? AND ?";
			$params[] = $filterStart;
			$params[] = $filterEnd;
		}

		if (!empty($search)) {
			$where[] = "(hp.nopenjualan LIKE ? OR mc.namacustomer LIKE ? OR u.namasales LIKE ?)";
			$keyword = '%' . $search . '%';
			$params[] = $keyword;
			$params[] = $keyword;
			$params[] = $keyword;
		}

		if (!empty($kodesales)) {
			$where[] = "hp.kodesales = ?";
			$params[] = $kodesales;
		}

		if (!empty($statuspkp) && in_array($statuspkp, ['pkp', 'nonpkp'], true)) {
			$where[] = "hp.statuspkp = ?";
			$params[] = $statuspkp;
		}

		$sortBy = $options['sort_by'] ?? 'tanggalpenjualan';
		$sortOrder = strtoupper($options['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
		
		$validSortColumns = ['tanggalpenjualan', 'nopenjualan', 'namacustomer', 'namasales', 'noorder'];
		$sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'tanggalpenjualan';
		
		// Handle sorting for joined columns
		if ($sortBy === 'namacustomer') {
			$sortColumn = 'mc.namacustomer';
		} elseif ($sortBy === 'namasales') {
			$sortColumn = 'u.namasales';
		} else {
			$sortColumn = 'hp.' . $sortBy;
		}

		$whereClause = implode(' AND ', $where);

		$sql = "SELECT hp.*, mc.namacustomer, u.namasales
				FROM headerpenjualan hp
				LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
				LEFT JOIN mastersales u ON hp.kodesales = u.kodesales
				WHERE {$whereClause}
				ORDER BY {$sortColumn} {$sortOrder}
				LIMIT ? OFFSET ?";
		$params[] = $perPage;
		$params[] = $offset;

		return $this->db->fetchAll($sql, $params);
	}

	public function count($options = []) {
		$search = trim($options['search'] ?? '');
		$kodesales = $options['kodesales'] ?? null;
		$periode = $options['periode'] ?? 'today';
		$startDate = $options['start_date'] ?? null;
		$endDate = $options['end_date'] ?? null;
		$statuspkp = $options['statuspkp'] ?? null;

		[$filterStart, $filterEnd] = $this->resolveDateRange($periode, $startDate, $endDate);

		$params = [];
		$where = ["1=1"];

		if ($filterStart && $filterEnd) {
			$where[] = "hp.tanggalpenjualan BETWEEN ? AND ?";
			$params[] = $filterStart;
			$params[] = $filterEnd;
		}

		if (!empty($search)) {
			$where[] = "(hp.nopenjualan LIKE ? OR mc.namacustomer LIKE ? OR u.namasales LIKE ?)";
			$keyword = '%' . $search . '%';
			$params[] = $keyword;
			$params[] = $keyword;
			$params[] = $keyword;
		}

		if (!empty($kodesales)) {
			$where[] = "hp.kodesales = ?";
			$params[] = $kodesales;
		}

		if (!empty($statuspkp) && in_array($statuspkp, ['pkp', 'nonpkp'], true)) {
			$where[] = "hp.statuspkp = ?";
			$params[] = $statuspkp;
		}

		$whereClause = implode(' AND ', $where);

		$sql = "SELECT COUNT(*) AS total
				FROM headerpenjualan hp
				LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
				LEFT JOIN mastersales u ON hp.kodesales = u.kodesales
				WHERE {$whereClause}";

		$result = $this->db->fetchOne($sql, $params);
		return (int)($result['total'] ?? 0);
	}

	public function countDistinctCustomers($options = []) {
		$search = trim($options['search'] ?? '');
		$kodesales = $options['kodesales'] ?? null;
		$periode = $options['periode'] ?? 'today';
		$startDate = $options['start_date'] ?? null;
		$endDate = $options['end_date'] ?? null;
		$statuspkp = $options['statuspkp'] ?? null;

		[$filterStart, $filterEnd] = $this->resolveDateRange($periode, $startDate, $endDate);

		$params = [];
		$where = ["1=1"];

		if ($filterStart && $filterEnd) {
			$where[] = "hp.tanggalpenjualan BETWEEN ? AND ?";
			$params[] = $filterStart;
			$params[] = $filterEnd;
		}

		if (!empty($kodesales)) {
			$where[] = "hp.kodesales = ?";
			$params[] = $kodesales;
		}

		$whereClause = implode(' AND ', $where);

		$sql = "SELECT COUNT(DISTINCT hp.kodecustomer) AS total
				FROM headerpenjualan hp
				WHERE {$whereClause}";

		$result = $this->db->fetchOne($sql, $params);
		return (int)($result['total'] ?? 0);
	}

	public function findByNopenjualan($nopenjualan) {
		$sql = "SELECT hp.*, mc.namacustomer, mc.alamatcustomer, mc.kotacustomer, u.namasales
				FROM headerpenjualan hp
				LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
				LEFT JOIN mastersales u ON hp.kodesales = u.kodesales
				WHERE hp.nopenjualan = ?";
		return $this->db->fetchOne($sql, [$nopenjualan]);
	}

	public function create($headerData, $details) {
		$conn = $this->db->getConnection();

		$conn->beginTransaction();
		try {
			$headerSql = "INSERT INTO headerpenjualan (nopenjualan, tanggalpenjualan, statuspkp, kodeformulir, noorder, tanggalorder, tanggaljatuhtempo, keterangan, kodecustomer, kodesales, pengirim, dpp, ppn, nilaipenjualan, saldopenjualan, userid)
						  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$this->db->query($headerSql, [
				$headerData['nopenjualan'],
				$headerData['tanggalpenjualan'],
				$headerData['statuspkp'] ?? null,
				$headerData['kodeformulir'],
				$headerData['noorder'],
				$headerData['tanggalorder'],
				$headerData['tanggaljatuhtempo'],
				$headerData['keterangan'],
				$headerData['kodecustomer'],
				$headerData['kodesales'],
				$headerData['pengirim'],
				$headerData['dpp'],
				$headerData['ppn'],
				$headerData['nilaipenjualan'],
				$headerData['saldopenjualan'],
				$headerData['userid']
			]);

			$detailSql = "INSERT INTO detailpenjualan (nopenjualan, kodebarang, nopembelian, nomorbatch, expireddate, jumlah, hargasatuan, discount, jumlahharga, nourut)
						  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

			$seq = 1;
			foreach ($details as $detail) {
				$this->db->query($detailSql, [
					$headerData['nopenjualan'],
					$detail['kodebarang'],
					$detail['nopembelian'],
					$detail['nomorbatch'],
					$detail['expireddate'],
					$detail['jumlah'],
					$detail['hargasatuan'],
					$detail['discount'],
					$detail['jumlahharga'],
					isset($detail['nourut']) ? (int)$detail['nourut'] : $seq++
				]);
			}

			$conn->commit();
		} catch (Exception $e) {
			$conn->rollBack();
			throw $e;
		}
	}

	public function update($nopenjualan, $headerData, $details = null) {
		$conn = $this->db->getConnection();

		$conn->beginTransaction();
		try {
			if (!empty($headerData)) {
				$fields = [];
				$params = [];

				foreach ($headerData as $key => $value) {
					$fields[] = "{$key} = ?";
					$params[] = $value;
				}
				$params[] = $nopenjualan;

				$sql = "UPDATE headerpenjualan SET " . implode(', ', $fields) . " WHERE nopenjualan = ?";
				$this->db->query($sql, $params);
			}

			if (is_array($details)) {
				$this->db->query("DELETE FROM detailpenjualan WHERE nopenjualan = ?", [$nopenjualan]);

				$detailSql = "INSERT INTO detailpenjualan (nopenjualan, kodebarang, nopembelian, nomorbatch, expireddate, jumlah, hargasatuan, discount, jumlahharga, nourut)
							  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

				$seq = 1;
				foreach ($details as $detail) {
					$this->db->query($detailSql, [
						$nopenjualan,
						$detail['kodebarang'],
						$detail['nopembelian'],
						$detail['nomorbatch'],
						$detail['expireddate'],
						$detail['jumlah'],
						$detail['hargasatuan'],
						$detail['discount'],
						$detail['jumlahharga'],
						isset($detail['nourut']) ? (int)$detail['nourut'] : $seq++
					]);
				}
			}

			$conn->commit();
		} catch (Exception $e) {
			$conn->rollBack();
			throw $e;
		}
	}

	public function patch($nopenjualan, $data) {
		if (empty($data)) {
			return;
		}

		$fields = [];
		$params = [];
		foreach ($data as $key => $value) {
			$fields[] = "{$key} = ?";
			$params[] = $value;
		}
		$params[] = $nopenjualan;

		$sql = "UPDATE headerpenjualan SET " . implode(', ', $fields) . " WHERE nopenjualan = ?";
		$this->db->query($sql, $params);
	}

	public function updateSaldo($nopenjualan, $saldo) {
		$sql = "UPDATE headerpenjualan SET saldopenjualan = ? WHERE nopenjualan = ?";
		$this->db->query($sql, [$saldo, $nopenjualan]);
	}

	public function updateOrderInfo($nopenjualan, $noorder, $tanggalorder) {
		$sql = "UPDATE headerpenjualan SET noorder = ?, tanggalorder = ? WHERE nopenjualan = ?";
		$this->db->query($sql, [$noorder, $tanggalorder, $nopenjualan]);
	}

	public function delete($nopenjualan) {
		$conn = $this->db->getConnection();
		$conn->beginTransaction();
		try {
			$this->db->query("DELETE FROM detailpenjualan WHERE nopenjualan = ?", [$nopenjualan]);
			$this->db->query("DELETE FROM headerpenjualan WHERE nopenjualan = ?", [$nopenjualan]);
			$conn->commit();
		} catch (Exception $e) {
			$conn->rollBack();
			throw $e;
		}
	}

	private function resolveDateRange($periode, $startDate, $endDate) {
		$today = date('Y-m-d');
		switch ($periode) {
			case 'week':
				$start = date('Y-m-d', strtotime('monday this week'));
				$end = date('Y-m-d', strtotime('sunday this week'));
				break;
			case 'month':
				$start = date('Y-m-01');
				$end = date('Y-m-t');
				break;
			case 'year':
				$start = date('Y-01-01');
				$end = date('Y-12-31');
				break;
			case 'custom':
				$start = $startDate ?: $today;
				$end = $endDate ?: $start;
				break;
			case 'today':
			default:
				$start = $today;
				$end = $today;
				break;
		}
		return [$start, $end];
	}

	public function getTotals($options = []) {
		$search = trim($options['search'] ?? '');
		$kodesales = $options['kodesales'] ?? null;
		$periode = $options['periode'] ?? 'today';
		$startDate = $options['start_date'] ?? null;
		$endDate = $options['end_date'] ?? null;
		$statuspkp = $options['statuspkp'] ?? null;

		[$filterStart, $filterEnd] = $this->resolveDateRange($periode, $startDate, $endDate);

		$params = [];
		$where = ["1=1"];

		if ($filterStart && $filterEnd) {
			$where[] = "hp.tanggalpenjualan BETWEEN ? AND ?";
			$params[] = $filterStart;
			$params[] = $filterEnd;
		}

		if (!empty($search)) {
			$where[] = "(hp.nopenjualan LIKE ? OR mc.namacustomer LIKE ? OR u.namasales LIKE ?)";
			$keyword = '%' . $search . '%';
			$params[] = $keyword;
			$params[] = $keyword;
			$params[] = $keyword;
		}

		if (!empty($kodesales)) {
			$where[] = "hp.kodesales = ?";
			$params[] = $kodesales;
		}

		if (!empty($statuspkp) && in_array($statuspkp, ['pkp', 'nonpkp'], true)) {
			$where[] = "hp.statuspkp = ?";
			$params[] = $statuspkp;
		}

		$whereClause = implode(' AND ', $where);

		$sql = "SELECT 
					COALESCE(SUM(hp.nilaipenjualan), 0) AS total_penjualan,
					COALESCE(SUM(hp.saldopenjualan), 0) AS total_saldo
				FROM headerpenjualan hp
				LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
				LEFT JOIN mastersales u ON hp.kodesales = u.kodesales
				WHERE {$whereClause}";

		$result = $this->db->fetchOne($sql, $params);
		return [
			'nilaipenjualan' => (float)($result['total_penjualan'] ?? 0),
			'saldopenjualan' => (float)($result['total_saldo'] ?? 0)
		];
	}
}




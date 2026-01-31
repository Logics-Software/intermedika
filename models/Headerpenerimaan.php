<?php
class Headerpenerimaan {
	private $db;

	public function __construct() {
		$this->db = Database::getInstance();
	}

	public function getAll($options = []) {
		$page = max((int)($options['page'] ?? 1), 1);
		$perPage = max((int)($options['per_page'] ?? 10), 1);
		$search = trim($options['search'] ?? '');
		$status = $options['status'] ?? null;
		$kodecustomer = $options['kodecustomer'] ?? null;
		$kodesales = $options['kodesales'] ?? null;
		$startDate = $options['start_date'] ?? null;
		$endDate = $options['end_date'] ?? null;

		$offset = ($page - 1) * $perPage;
		$params = [];
		$where = ["1=1"];

		if ($startDate && $endDate) {
			$where[] = "hp.tanggalpenerimaan BETWEEN ? AND ?";
			$params[] = $startDate;
			$params[] = $endDate;
		}

		if (!empty($search)) {
			$where[] = "(hp.nopenerimaan LIKE ? OR mc.namacustomer LIKE ? OR u.namasales LIKE ? OR hp.noinkaso LIKE ?)";
			$keyword = '%' . $search . '%';
			$params[] = $keyword;
			$params[] = $keyword;
			$params[] = $keyword;
			$params[] = $keyword;
		}

		if (!empty($status) && in_array($status, ['belumproses', 'proses'], true)) {
			$where[] = "hp.status = ?";
			$params[] = $status;
		}

		if (!empty($kodecustomer)) {
			$where[] = "hp.kodecustomer = ?";
			$params[] = $kodecustomer;
		}

		if (!empty($kodesales)) {
			$where[] = "hp.kodesales = ?";
			$params[] = $kodesales;
		}

		$whereClause = implode(' AND ', $where);

		$sql = "SELECT hp.*, mc.namacustomer, u.namasales
				FROM headerpenerimaan hp
				LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
				LEFT JOIN mastersales u ON hp.kodesales = u.kodesales
				WHERE {$whereClause}
				ORDER BY hp.tanggalpenerimaan DESC, hp.nopenerimaan DESC
				LIMIT ? OFFSET ?";
		$params[] = $perPage;
		$params[] = $offset;

		return $this->db->fetchAll($sql, $params);
	}

	public function count($options = []) {
		$search = trim($options['search'] ?? '');
		$status = $options['status'] ?? null;
		$kodecustomer = $options['kodecustomer'] ?? null;
		$kodesales = $options['kodesales'] ?? null;
		$startDate = $options['start_date'] ?? null;
		$endDate = $options['end_date'] ?? null;

		$params = [];
		$where = ["1=1"];

		if ($startDate && $endDate) {
			$where[] = "hp.tanggalpenerimaan BETWEEN ? AND ?";
			$params[] = $startDate;
			$params[] = $endDate;
		}

		if (!empty($search)) {
			$where[] = "(hp.nopenerimaan LIKE ? OR mc.namacustomer LIKE ? OR u.namasales LIKE ? OR hp.noinkaso LIKE ?)";
			$keyword = '%' . $search . '%';
			$params[] = $keyword;
			$params[] = $keyword;
			$params[] = $keyword;
			$params[] = $keyword;
		}

		if (!empty($status) && in_array($status, ['belumproses', 'proses'], true)) {
			$where[] = "hp.status = ?";
			$params[] = $status;
		}

		if (!empty($kodecustomer)) {
			$where[] = "hp.kodecustomer = ?";
			$params[] = $kodecustomer;
		}

		if (!empty($kodesales)) {
			$where[] = "hp.kodesales = ?";
			$params[] = $kodesales;
		}

		$whereClause = implode(' AND ', $where);

		$sql = "SELECT COUNT(*) AS total
				FROM headerpenerimaan hp
				LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
				LEFT JOIN mastersales u ON hp.kodesales = u.kodesales
				WHERE {$whereClause}";

		$result = $this->db->fetchOne($sql, $params);
		return (int)($result['total'] ?? 0);
	}

	public function findByNopenerimaan($nopenerimaan) {
		$sql = "SELECT hp.*, mc.namacustomer, mc.alamatcustomer, mc.kotacustomer, u.namasales
				FROM headerpenerimaan hp
				LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
				LEFT JOIN mastersales u ON hp.kodesales = u.kodesales
				WHERE hp.nopenerimaan = ?";
		return $this->db->fetchOne($sql, [$nopenerimaan]);
	}

	public function create($headerData, $details) {
		$conn = $this->db->getConnection();

		$conn->beginTransaction();
		try {
			// Kurangi saldopenjualan sebelum insert
			$penjualanModel = new Headerpenjualan();
			foreach ($details as $detail) {
				$nopenjualan = $detail['nopenjualan'] ?? '';
				$piutang = (float)($detail['piutang'] ?? 0);
				
				if (!empty($nopenjualan) && $piutang > 0) {
					$penjualan = $penjualanModel->findByNopenjualan($nopenjualan);
					if ($penjualan) {
						$saldopenjualan = (float)($penjualan['saldopenjualan'] ?? 0);
						
						if ($saldopenjualan < $piutang) {
							throw new Exception("Saldo penjualan untuk {$nopenjualan} tidak mencukupi. Saldo tersedia: {$saldopenjualan}, dibutuhkan: {$piutang}");
						}
						
						// Kurangi saldopenjualan
						$saldoBaru = $saldopenjualan - $piutang;
						$penjualanModel->updateSaldo($nopenjualan, $saldoBaru);
					} else {
						throw new Exception("Penjualan dengan nomor {$nopenjualan} tidak ditemukan");
					}
				}
			}

			$headerSql = "INSERT INTO headerpenerimaan (nopenerimaan, tanggalpenerimaan, statuspkp, jenispenerimaan, kodesales, kodecustomer, totalpiutang, totalpotongan, totallainlain, totalnetto, status, noinkaso, userid)
						  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$this->db->query($headerSql, [
				$headerData['nopenerimaan'],
				$headerData['tanggalpenerimaan'],
				$headerData['statuspkp'] ?? null,
				$headerData['jenispenerimaan'],
				$headerData['kodesales'] ?? null,
				$headerData['kodecustomer'] ?? null,
				$headerData['totalpiutang'] ?? 0,
				$headerData['totalpotongan'] ?? 0,
				$headerData['totallainlain'] ?? 0,
				$headerData['totalnetto'] ?? 0,
				$headerData['status'] ?? 'belumproses',
				$headerData['noinkaso'] ?? null,
				$headerData['userid'] ?? null
			]);

			$detailSql = "INSERT INTO detailpenerimaan (nopenerimaan, nopenjualan, nogiro, tanggalcair, piutang, potongan, lainlain, netto, nourut)
						  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

			$seq = 1;
			foreach ($details as $detail) {
				$nogiro = $detail['nogiro'] ?? null;
				$tanggalcair = $detail['tanggalcair'] ?? null;
				
				// Ensure empty strings are converted to null for database compatibility
				if ($nogiro === '') {
					$nogiro = null;
				}
				if ($tanggalcair === '') {
					$tanggalcair = null;
				}
				
				$this->db->query($detailSql, [
					$headerData['nopenerimaan'],
					$detail['nopenjualan'],
					$nogiro,
					$tanggalcair,
					$detail['piutang'] ?? 0,
					$detail['potongan'] ?? 0,
					$detail['lainlain'] ?? 0,
					$detail['netto'] ?? 0,
					isset($detail['nourut']) ? (int)$detail['nourut'] : $seq++
				]);
			}

			$conn->commit();
		} catch (Exception $e) {
			$conn->rollBack();
			throw $e;
		}
	}

	public function update($nopenerimaan, $headerData, $details = null) {
		$conn = $this->db->getConnection();

		$conn->beginTransaction();
		try {
			$penjualanModel = new Headerpenjualan();
			$detailModel = new Detailpenerimaan();

			// Jika ada perubahan detail, sesuaikan saldopenjualan
			if (is_array($details)) {
				// Ambil detail penerimaan yang lama
				$oldDetails = $detailModel->getByNopenerimaan($nopenerimaan);

				// Kembalikan saldopenjualan dari detail lama
				foreach ($oldDetails as $oldDetail) {
					$nopenjualan = $oldDetail['nopenjualan'] ?? '';
					$piutangLama = (float)($oldDetail['piutang'] ?? 0);
					
					if (!empty($nopenjualan) && $piutangLama > 0) {
						$penjualan = $penjualanModel->findByNopenjualan($nopenjualan);
						if ($penjualan) {
							$saldopenjualan = (float)($penjualan['saldopenjualan'] ?? 0);
							$saldoBaru = $saldopenjualan + $piutangLama;
							$penjualanModel->updateSaldo($nopenjualan, $saldoBaru);
						}
					}
				}

				// Validasi dan kurangi saldopenjualan untuk detail baru
				foreach ($details as $detail) {
					$nopenjualan = $detail['nopenjualan'] ?? '';
					$piutang = (float)($detail['piutang'] ?? 0);
					
					if (!empty($nopenjualan) && $piutang > 0) {
						$penjualan = $penjualanModel->findByNopenjualan($nopenjualan);
						if ($penjualan) {
							$saldopenjualan = (float)($penjualan['saldopenjualan'] ?? 0);
							
							if ($saldopenjualan < $piutang) {
								throw new Exception("Saldo penjualan untuk {$nopenjualan} tidak mencukupi. Saldo tersedia: {$saldopenjualan}, dibutuhkan: {$piutang}");
							}
							
							// Kurangi saldopenjualan
							$saldoBaru = $saldopenjualan - $piutang;
							$penjualanModel->updateSaldo($nopenjualan, $saldoBaru);
						} else {
							throw new Exception("Penjualan dengan nomor {$nopenjualan} tidak ditemukan");
						}
					}
				}
			}

			if (!empty($headerData)) {
				$fields = [];
				$params = [];

				foreach ($headerData as $key => $value) {
					$fields[] = "{$key} = ?";
					$params[] = $value;
				}
				$params[] = $nopenerimaan;

				$sql = "UPDATE headerpenerimaan SET " . implode(', ', $fields) . " WHERE nopenerimaan = ?";
				$this->db->query($sql, $params);
			}

			if (is_array($details)) {
				$this->db->query("DELETE FROM detailpenerimaan WHERE nopenerimaan = ?", [$nopenerimaan]);

				$detailSql = "INSERT INTO detailpenerimaan (nopenerimaan, nopenjualan, nogiro, tanggalcair, piutang, potongan, lainlain, netto, nourut)
							  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

				$seq = 1;
				foreach ($details as $detail) {
					$nogiro = $detail['nogiro'] ?? null;
					$tanggalcair = $detail['tanggalcair'] ?? null;
					
					// Ensure empty strings are converted to null for database compatibility
					if ($nogiro === '') {
						$nogiro = null;
					}
					if ($tanggalcair === '') {
						$tanggalcair = null;
					}
					
					$this->db->query($detailSql, [
						$nopenerimaan,
						$detail['nopenjualan'],
						$nogiro,
						$tanggalcair,
						$detail['piutang'] ?? 0,
						$detail['potongan'] ?? 0,
						$detail['lainlain'] ?? 0,
						$detail['netto'] ?? 0,
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

	public function patch($nopenerimaan, $data) {
		if (empty($data)) {
			return;
		}

		$fields = [];
		$params = [];
		foreach ($data as $key => $value) {
			$fields[] = "{$key} = ?";
			$params[] = $value;
		}
		$params[] = $nopenerimaan;

		$sql = "UPDATE headerpenerimaan SET " . implode(', ', $fields) . " WHERE nopenerimaan = ?";
		$this->db->query($sql, $params);
	}

	public function delete($nopenerimaan) {
		$conn = $this->db->getConnection();
		$conn->beginTransaction();
		try {
			// Ambil detail penerimaan untuk mengembalikan saldopenjualan
			$detailModel = new Detailpenerimaan();
			$details = $detailModel->getByNopenerimaan($nopenerimaan);

			// Kembalikan saldopenjualan
			$penjualanModel = new Headerpenjualan();
			foreach ($details as $detail) {
				$nopenjualan = $detail['nopenjualan'] ?? '';
				$piutang = (float)($detail['piutang'] ?? 0);
				
				if (!empty($nopenjualan) && $piutang > 0) {
					$penjualan = $penjualanModel->findByNopenjualan($nopenjualan);
					if ($penjualan) {
						$saldopenjualan = (float)($penjualan['saldopenjualan'] ?? 0);
						$saldoBaru = $saldopenjualan + $piutang;
						$penjualanModel->updateSaldo($nopenjualan, $saldoBaru);
					}
				}
			}

			$this->db->query("DELETE FROM detailpenerimaan WHERE nopenerimaan = ?", [$nopenerimaan]);
			$this->db->query("DELETE FROM headerpenerimaan WHERE nopenerimaan = ?", [$nopenerimaan]);
			$conn->commit();
		} catch (Exception $e) {
			$conn->rollBack();
			throw $e;
		}
	}

	public function updateStatusAndNoinkaso($nopenerimaan, $status, $noinkaso = null) {
		$fields = ["status = ?"];
		$params = [$status];
		
		// Always update noinkaso if provided (even if empty string, convert to null)
		if ($noinkaso !== null) {
			$fields[] = "noinkaso = ?";
			// Convert empty string to null for database consistency
			$params[] = ($noinkaso === '') ? null : $noinkaso;
		}
		
		$params[] = $nopenerimaan;
		$sql = "UPDATE headerpenerimaan SET " . implode(', ', $fields) . " WHERE nopenerimaan = ?";
		$this->db->query($sql, $params);
	}

	public function canEditOrDelete($nopenerimaan) {
		$sql = "SELECT status FROM headerpenerimaan WHERE nopenerimaan = ?";
		$result = $this->db->fetchOne($sql, [$nopenerimaan]);
		return $result && $result['status'] === 'belumproses';
	}

	public function getLastNopenerimaanWithPrefix($prefix) {
		$sql = "SELECT nopenerimaan FROM headerpenerimaan 
				WHERE nopenerimaan LIKE ? 
				ORDER BY nopenerimaan DESC 
				LIMIT 1";
		return $this->db->fetchOne($sql, [$prefix . '%']);
	}

	public function sumTotal($options = []) {
		$kodesales = $options['kodesales'] ?? null;
		$startDate = $options['start_date'] ?? null;
		$endDate = $options['end_date'] ?? null;
		$status = $options['status'] ?? null;

		$params = [];
		$where = ["1=1"];

		if ($startDate && $endDate) {
			$where[] = "hp.tanggalpenerimaan BETWEEN ? AND ?";
			$params[] = $startDate;
			$params[] = $endDate;
		}

		if (!empty($status) && in_array($status, ['belumproses', 'proses'], true)) {
			$where[] = "hp.status = ?";
			$params[] = $status;
		}

		if (!empty($kodesales)) {
			$where[] = "hp.kodesales = ?";
			$params[] = $kodesales;
		}

		$whereClause = implode(' AND ', $where);

		$sql = "SELECT COALESCE(SUM(hp.totalnetto), 0) AS total
				FROM headerpenerimaan hp
				WHERE {$whereClause}";

		$result = $this->db->fetchOne($sql, $params);
		return (float)($result['total'] ?? 0);
	}
}




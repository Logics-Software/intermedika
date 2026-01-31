<?php
class Headerorder {
	private $db;

	public function __construct() {
		$this->db = Database::getInstance();
	}

	public function findByNoorder($noorder) {
		$sql = "SELECT ho.*, mc.namacustomer, mc.alamatcustomer, mc.kotacustomer AS kota, mc.namabadanusaha, u.namalengkap AS namasales
				FROM headerorder ho
				LEFT JOIN mastercustomer mc ON ho.kodecustomer = mc.kodecustomer
				LEFT JOIN users u ON ho.kodesales = u.kodesales
				WHERE ho.noorder = ?";
		return $this->db->fetchOne($sql, [$noorder]);
	}

	public function getAll($options = []) {
		$page = $options['page'] ?? 1;
		$perPage = $options['per_page'] ?? 10;
		$search = $options['search'] ?? '';
		$status = $options['status'] ?? '';
		$kodesales = $options['kodesales'] ?? null;
		$startDate = $options['start_date'] ?? null;
		$endDate = $options['end_date'] ?? null;
		$sortBy = $options['sort_by'] ?? 'tanggalorder';
		$sortOrder = strtoupper($options['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

		$offset = ($page - 1) * $perPage;

		$where = ["1=1"];
		$params = [];

		if (!empty($kodesales)) {
			$where[] = "ho.kodesales = ?";
			$params[] = $kodesales;
		}

		if (!empty($status)) {
			$where[] = "ho.status = ?";
			$params[] = $status;
		}

		if (!empty($startDate) && !empty($endDate)) {
			$where[] = "ho.tanggalorder BETWEEN ? AND ?";
			$params[] = $startDate;
			$params[] = $endDate;
		}

		if (!empty($search)) {
			$where[] = "mc.namacustomer LIKE ?";
			$params[] = "%{$search}%";
		}

		$validSortColumns = ['tanggalorder', 'noorder', 'nilaiorder', 'status', 'namacustomer', 'nopenjualan'];
		$sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'tanggalorder';
		
		// Handle sorting for joined columns
		if ($sortBy === 'namacustomer') {
			$sortColumn = 'mc.namacustomer';
		} elseif ($sortBy === 'nopenjualan') {
			$sortColumn = 'ho.nopenjualan';
		} else {
			$sortColumn = 'ho.' . $sortBy;
		}

		$whereClause = implode(' AND ', $where);

		$sql = "SELECT ho.*, mc.namacustomer, mc.alamatcustomer, mc.kotacustomer AS kota, mc.namabadanusaha, ms.namasales
				FROM headerorder ho
				LEFT JOIN mastercustomer mc ON ho.kodecustomer = mc.kodecustomer
				LEFT JOIN mastersales ms ON ho.kodesales = ms.kodesales
				WHERE {$whereClause}
				ORDER BY {$sortColumn} {$sortOrder}
				LIMIT ? OFFSET ?";
		$params[] = $perPage;
		$params[] = $offset;

		return $this->db->fetchAll($sql, $params);
	}

	public function count($options = []) {
		$search = $options['search'] ?? '';
		$status = $options['status'] ?? '';
		$kodesales = $options['kodesales'] ?? null;
		$startDate = $options['start_date'] ?? null;
		$endDate = $options['end_date'] ?? null;

		$where = ["1=1"];
		$params = [];

		if (!empty($kodesales)) {
			$where[] = "ho.kodesales = ?";
			$params[] = $kodesales;
		}

		if (!empty($status)) {
			$where[] = "ho.status = ?";
			$params[] = $status;
		}

		if (!empty($startDate) && !empty($endDate)) {
			$where[] = "ho.tanggalorder BETWEEN ? AND ?";
			$params[] = $startDate;
			$params[] = $endDate;
		}

		if (!empty($search)) {
			$where[] = "mc.namacustomer LIKE ?";
			$params[] = "%{$search}%";
		}

		$whereClause = implode(' AND ', $where);

		$sql = "SELECT COUNT(*) AS total
				FROM headerorder ho
				LEFT JOIN mastercustomer mc ON ho.kodecustomer = mc.kodecustomer
				WHERE {$whereClause}";

		$result = $this->db->fetchOne($sql, $params);
		return $result['total'] ?? 0;
	}

	public function create($headerData, $details) {
		$conn = $this->db->getConnection();

		try {
			$conn->beginTransaction();

			// Validasi dan kurangi stok sebelum insert
			$barangModel = new Masterbarang();
			foreach ($details as $detail) {
				$barang = $barangModel->findByKodebarang($detail['kodebarang']);
				if (!$barang) {
					throw new Exception("Barang dengan kode {$detail['kodebarang']} tidak ditemukan");
				}

				$stokAkhir = (float)($barang['stokakhir'] ?? 0);
				$jumlah = (int)($detail['jumlah'] ?? 0);

				if ($stokAkhir < $jumlah) {
					throw new Exception("Stok barang {$barang['namabarang']} ({$detail['kodebarang']}) tidak mencukupi. Stok tersedia: {$stokAkhir}, dibutuhkan: {$jumlah}");
				}

				// Kurangi stok
				$stokBaru = $stokAkhir - $jumlah;
				$barangModel->update($barang['id'], ['stokakhir' => $stokBaru]);
			}

			$sqlHeader = "INSERT INTO headerorder (noorder, tanggalorder, kodesales, statuspkp, kodecustomer, keterangan, nilaiorder, nopenjualan, status)
						  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$this->db->query($sqlHeader, [
				$headerData['noorder'],
				$headerData['tanggalorder'],
				$headerData['kodesales'],
				$headerData['statuspkp'],
				$headerData['kodecustomer'],
				$headerData['keterangan'],
				$headerData['nilaiorder'],
				$headerData['nopenjualan'],
				$headerData['status']
			]);

			$sqlDetail = "INSERT INTO detailorder (noorder, kodebarang, jumlah, hargajual, discount, totalharga)
						  VALUES (?, ?, ?, ?, ?, ?)";

			foreach ($details as $detail) {
				$this->db->query($sqlDetail, [
					$headerData['noorder'],
					$detail['kodebarang'],
					$detail['jumlah'],
					$detail['hargajual'],
					$detail['discount'],
					$detail['totalharga']
				]);
			}

			$conn->commit();
		} catch (Exception $e) {
			$conn->rollBack();
			throw $e;
		}
	}

	public function update($noorder, $headerData, $details = null) {
		$conn = $this->db->getConnection();

		try {
			$conn->beginTransaction();

			$barangModel = new Masterbarang();
			$detailModel = new Detailorder();

			// Jika ada perubahan detail, sesuaikan stok
			if ($details !== null) {
				// Ambil detail order yang lama
				$oldDetails = $detailModel->getByNoorder($noorder);

				// Kembalikan stok dari detail lama
				foreach ($oldDetails as $oldDetail) {
					$barang = $barangModel->findByKodebarang($oldDetail['kodebarang']);
					if ($barang) {
						$stokAkhir = (float)($barang['stokakhir'] ?? 0);
						$jumlahLama = (int)($oldDetail['jumlah'] ?? 0);
						$stokBaru = $stokAkhir + $jumlahLama;
						$barangModel->update($barang['id'], ['stokakhir' => $stokBaru]);
					}
				}

				// Validasi dan kurangi stok untuk detail baru
				foreach ($details as $detail) {
					$barang = $barangModel->findByKodebarang($detail['kodebarang']);
					if (!$barang) {
						throw new Exception("Barang dengan kode {$detail['kodebarang']} tidak ditemukan");
					}

					$stokAkhir = (float)($barang['stokakhir'] ?? 0);
					$jumlah = (int)($detail['jumlah'] ?? 0);

					if ($stokAkhir < $jumlah) {
						throw new Exception("Stok barang {$barang['namabarang']} ({$detail['kodebarang']}) tidak mencukupi. Stok tersedia: {$stokAkhir}, dibutuhkan: {$jumlah}");
					}

					// Kurangi stok
					$stokBaru = $stokAkhir - $jumlah;
					$barangModel->update($barang['id'], ['stokakhir' => $stokBaru]);
				}
			}

			$sqlHeader = "UPDATE headerorder
						  SET tanggalorder = ?, kodesales = ?, statuspkp = ?, kodecustomer = ?, keterangan = ?, nilaiorder = ?, nopenjualan = ?, status = ?
						  WHERE noorder = ?";
			$this->db->query($sqlHeader, [
				$headerData['tanggalorder'],
				$headerData['kodesales'],
				$headerData['statuspkp'],
				$headerData['kodecustomer'],
				$headerData['keterangan'],
				$headerData['nilaiorder'],
				$headerData['nopenjualan'],
				$headerData['status'],
				$noorder
			]);

			if ($details !== null) {
				$this->db->query("DELETE FROM detailorder WHERE noorder = ?", [$noorder]);

				$sqlDetail = "INSERT INTO detailorder (noorder, kodebarang, jumlah, hargajual, discount, totalharga)
							  VALUES (?, ?, ?, ?, ?, ?)";

				foreach ($details as $detail) {
					$this->db->query($sqlDetail, [
						$noorder,
						$detail['kodebarang'],
						$detail['jumlah'],
						$detail['hargajual'],
						$detail['discount'],
						$detail['totalharga']
					]);
				}
			}

			$conn->commit();
		} catch (Exception $e) {
			$conn->rollBack();
			throw $e;
		}
	}

	public function delete($noorder) {
		$conn = $this->db->getConnection();

		try {
			$conn->beginTransaction();

			// Ambil detail order untuk mengembalikan stok
			$detailModel = new Detailorder();
			$details = $detailModel->getByNoorder($noorder);

			// Kembalikan stok barang
			$barangModel = new Masterbarang();
			foreach ($details as $detail) {
				$barang = $barangModel->findByKodebarang($detail['kodebarang']);
				if ($barang) {
					$stokAkhir = (float)($barang['stokakhir'] ?? 0);
					$jumlah = (int)($detail['jumlah'] ?? 0);
					$stokBaru = $stokAkhir + $jumlah;
					$barangModel->update($barang['id'], ['stokakhir' => $stokBaru]);
				}
			}

			$this->db->query("DELETE FROM detailorder WHERE noorder = ?", [$noorder]);
			$this->db->query("DELETE FROM headerorder WHERE noorder = ?", [$noorder]);

			$conn->commit();
		} catch (Exception $e) {
			$conn->rollBack();
			throw $e;
		}
	}

	public function getLastNoorderWithPrefix($prefix) {
		$sql = "SELECT noorder FROM headerorder WHERE noorder LIKE ? ORDER BY noorder DESC LIMIT 1";
		return $this->db->fetchOne($sql, [$prefix . '%']);
	}

	public function sumTotal($options = []) {
		$kodesales = $options['kodesales'] ?? null;
		$startDate = $options['start_date'] ?? null;
		$endDate = $options['end_date'] ?? null;
		$status = $options['status'] ?? '';

		$where = ["1=1"];
		$params = [];

		if (!empty($kodesales)) {
			$where[] = "ho.kodesales = ?";
			$params[] = $kodesales;
		}

		if (!empty($status)) {
			$where[] = "ho.status = ?";
			$params[] = $status;
		}

		if (!empty($startDate) && !empty($endDate)) {
			$where[] = "ho.tanggalorder BETWEEN ? AND ?";
			$params[] = $startDate;
			$params[] = $endDate;
		}

		$whereClause = implode(' AND ', $where);

		$sql = "SELECT COALESCE(SUM(ho.nilaiorder), 0) AS total
				FROM headerorder ho
				WHERE {$whereClause}";

		$result = $this->db->fetchOne($sql, $params);
		return (float)($result['total'] ?? 0);
	}

	public function updateFields($noorder, $data = []) {
		if (empty($noorder) || empty($data)) {
			return false;
		}

		$allowed = ['nopenjualan', 'status'];
		$fields = [];
		$params = [];

		foreach ($allowed as $field) {
			if (array_key_exists($field, $data)) {
				$value = $data[$field];
				if ($value === '' || $value === null) {
					continue;
				}
				$fields[] = "{$field} = ?";
				$params[] = $value;
			}
		}

		if (empty($fields)) {
			return false;
		}

		$fields[] = "updated_at = NOW()";
		$params[] = $noorder;

		$sql = "UPDATE headerorder SET " . implode(', ', $fields) . " WHERE noorder = ?";
		$this->db->query($sql, $params);
		return true;
	}
}



<?php
class Detailpenerimaan {
	private $db;

	public function __construct() {
		$this->db = Database::getInstance();
	}

	public function getByNopenerimaan($nopenerimaan) {
		$sql = "SELECT dp.*, hpj.nopenjualan, hpj.tanggalpenjualan, hpj.saldopenjualan, mc.namacustomer, mc.namabadanusaha, u.namasales
				FROM detailpenerimaan dp
				LEFT JOIN headerpenjualan hpj ON dp.nopenjualan = hpj.nopenjualan
				LEFT JOIN mastercustomer mc ON hpj.kodecustomer = mc.kodecustomer
				LEFT JOIN mastersales u ON hpj.kodesales = u.kodesales
				WHERE dp.nopenerimaan = ?
				ORDER BY dp.nourut ASC, dp.nopenjualan ASC";
		return $this->db->fetchAll($sql, [$nopenerimaan]);
	}

	public function getAvailablePenjualan($kodecustomer = null) {
		$where = "hp.saldopenjualan > 0";
		$params = [];

		if (!empty($kodecustomer)) {
			$where .= " AND hp.kodecustomer = ?";
			$params[] = $kodecustomer;
		}

		$sql = "SELECT hp.nopenjualan, hp.tanggalpenjualan, hp.saldopenjualan, mc.namacustomer, mc.namabadanusaha, u.namasales
				FROM headerpenjualan hp
				LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
				LEFT JOIN mastersales u ON hp.kodesales = u.kodesales
				WHERE {$where}
				ORDER BY hp.tanggalpenjualan DESC, hp.nopenjualan DESC";
		
		return $this->db->fetchAll($sql, $params);
	}

	public function deleteByNopenerimaan($nopenerimaan) {
		$sql = "DELETE FROM detailpenerimaan WHERE nopenerimaan = ?";
		$this->db->query($sql, [$nopenerimaan]);
	}
}



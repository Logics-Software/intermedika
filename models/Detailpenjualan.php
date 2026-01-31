<?php
class Detailpenjualan {
	private $db;

	public function __construct() {
		$this->db = Database::getInstance();
	}

	public function getByNopenjualan($nopenjualan) {
		$sql = "SELECT dp.*, mb.namabarang, mb.satuan
				FROM detailpenjualan dp
				LEFT JOIN masterbarang mb ON dp.kodebarang = mb.kodebarang
				WHERE dp.nopenjualan = ?
				ORDER BY dp.nourut ASC, dp.kodebarang ASC";
		return $this->db->fetchAll($sql, [$nopenjualan]);
	}

	public function deleteByNopenjualan($nopenjualan) {
		$sql = "DELETE FROM detailpenjualan WHERE nopenjualan = ?";
		$this->db->query($sql, [$nopenjualan]);
	}
}




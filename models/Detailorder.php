<?php
class Detailorder {
	private $db;

	public function __construct() {
		$this->db = Database::getInstance();
	}

	public function getByNoorder($noorder) {
		$sql = "SELECT do.*, mb.namabarang, mb.satuan
				FROM detailorder do
				LEFT JOIN masterbarang mb ON do.kodebarang = mb.kodebarang
				WHERE do.noorder = ?
				ORDER BY do.id ASC";
		return $this->db->fetchAll($sql, [$noorder]);
	}

	public function deleteByNoorder($noorder) {
		$sql = "DELETE FROM detailorder WHERE noorder = ?";
		$this->db->query($sql, [$noorder]);
	}
}



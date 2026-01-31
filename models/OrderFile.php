<?php
class OrderFile {
	private $db;

	public function __construct() {
		$this->db = Database::getInstance();
	}

	public function create($data) {
		$sql = "INSERT INTO order_files (noorder, filename, original_filename, file_path, file_type, file_size, uploaded_by)
				VALUES (?, ?, ?, ?, ?, ?, ?)";
		$params = [
			$data['noorder'],
			$data['filename'],
			$data['original_filename'],
			$data['file_path'],
			$data['file_type'] ?? null,
			$data['file_size'] ?? null,
			$data['uploaded_by'] ?? null
		];
		$this->db->query($sql, $params);
		return $this->db->lastInsertId();
	}

	public function listByOrder($noorder) {
		$sql = "SELECT order_files.*, u.namalengkap AS uploaded_by_name
				FROM order_files
				LEFT JOIN users u ON order_files.uploaded_by = u.id
				WHERE order_files.noorder = ?
				ORDER BY order_files.created_at DESC";
		return $this->db->fetchAll($sql, [$noorder]);
	}

	public function findById($id) {
		$sql = "SELECT * FROM order_files WHERE id = ?";
		return $this->db->fetchOne($sql, [$id]);
	}

	public function delete($id) {
		$file = $this->findById($id);
		if ($file) {
			$filePath = __DIR__ . '/../' . $file['file_path'];
			if (file_exists($filePath)) {
				unlink($filePath);
			}
		}
		$sql = "DELETE FROM order_files WHERE id = ?";
		$this->db->query($sql, [$id]);
	}

	public function deleteByOrder($noorder) {
		$files = $this->listByOrder($noorder);
		foreach ($files as $file) {
			$filePath = __DIR__ . '/../' . $file['file_path'];
			if (file_exists($filePath)) {
				unlink($filePath);
			}
		}
		$sql = "DELETE FROM order_files WHERE noorder = ?";
		$this->db->query($sql, [$noorder]);
	}
}


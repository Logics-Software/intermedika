<?php
class Message {
	private $db;

	public function __construct() {
		$this->db = Database::getInstance();
	}

	/**
	 * Create a new message with recipients
	 */
	public function createMessage($data, $recipientIds = []) {
		try {
			$this->db->getConnection()->beginTransaction();

			// Create the message
			$sql = "INSERT INTO messages (sender_id, subject, content, message_type, status, created_at) 
					VALUES (?, ?, ?, ?, ?, NOW())";
			$this->db->query($sql, [
				$data['sender_id'],
				$data['subject'],
				$data['content'],
				$data['message_type'] ?? 'direct',
				$data['status'] ?? 'sent'
			]);

			$messageId = $this->db->lastInsertId();

			if (!$messageId) {
				throw new Exception('Failed to create message');
			}

			// Add recipients
			if (!empty($recipientIds)) {
				foreach ($recipientIds as $recipientId) {
					$this->db->query(
						"INSERT INTO message_recipients (message_id, recipient_id, created_at) VALUES (?, ?, NOW())",
						[$messageId, $recipientId]
					);
				}
			}

			$this->db->getConnection()->commit();
			return $messageId;
		} catch (Exception $e) {
			$this->db->getConnection()->rollBack();
			throw $e;
		}
	}

	/**
	 * Get a specific message with recipients
	 */
	public function getMessageWithRecipients($messageId, $userId) {
		// Check if user has access to this message
		$accessCheck = $this->db->fetchOne(
			"SELECT COUNT(*) as count FROM messages m 
			 LEFT JOIN message_recipients mr ON m.id = mr.message_id 
			 WHERE m.id = ? AND (m.sender_id = ? OR mr.recipient_id = ?)",
			[$messageId, $userId, $userId]
		);

		if ($accessCheck['count'] == 0) {
			return null;
		}

		// Get message details
		$message = $this->db->fetchOne(
			"SELECT m.*, u.namalengkap as sender_name, u.email as sender_email, u.picture as sender_picture 
			 FROM messages m 
			 INNER JOIN users u ON m.sender_id = u.id 
			 WHERE m.id = ?",
			[$messageId]
		);

		if (!$message) {
			return null;
		}

		// Get recipients
		$recipients = $this->db->fetchAll(
			"SELECT mr.*, u.namalengkap as recipient_name, u.email as recipient_email, u.picture as recipient_picture
			 FROM message_recipients mr
			 INNER JOIN users u ON mr.recipient_id = u.id
			 WHERE mr.message_id = ?",
			[$messageId]
		);

		$message['recipients'] = $recipients;
		return $message;
	}

	/**
	 * Mark message as read
	 */
	public function markAsRead($messageId, $userId) {
		$sql = "
			UPDATE message_recipients 
			SET is_read = 1, read_at = NOW() 
			WHERE message_id = ? AND recipient_id = ?
		";
		
		return $this->db->query($sql, [$messageId, $userId]);
	}

	/**
	 * Mark all messages as read for user
	 */
	public function markAllAsRead($userId) {
		$sql = "
			UPDATE message_recipients 
			SET is_read = 1, read_at = NOW() 
			WHERE recipient_id = ? AND is_read = 0
		";
		
		return $this->db->query($sql, [$userId]);
	}

	/**
	 * Get unread message count for user
	 */
	public function getUnreadCount($userId) {
		$result = $this->db->fetchOne(
			"SELECT COUNT(*) as count 
			 FROM message_recipients 
			 WHERE recipient_id = ? AND is_read = 0",
			[$userId]
		);
		
		return (int)($result['count'] ?? 0);
	}

	/**
	 * Get unread messages for user (limited to recent messages)
	 */
	public function getUnreadMessages($userId, $limit = 10) {
		$sql = "
			SELECT 
				m.*,
				u.namalengkap as sender_name,
				u.email as sender_email,
				u.picture as sender_picture,
				mr.is_read,
				mr.read_at
			FROM messages m
			INNER JOIN message_recipients mr ON m.id = mr.message_id
			INNER JOIN users u ON m.sender_id = u.id
			WHERE mr.recipient_id = ? AND mr.is_read = 0
			ORDER BY m.created_at DESC
			LIMIT ?
		";
		
		return $this->db->fetchAll($sql, [$userId, $limit]);
	}

	/**
	 * Get all users for recipient selection
	 */
	public function getAllUsers($excludeUserId = null) {
		$sql = "SELECT id, namalengkap as name, email FROM users WHERE status = 'aktif'";
		$params = [];
		
		if ($excludeUserId) {
			$sql .= " AND id != ?";
			$params[] = $excludeUserId;
		}
		
		$sql .= " ORDER BY namalengkap ASC";
		
		return $this->db->fetchAll($sql, $params);
	}

	/**
	 * Delete message (soft delete for sender, hard delete for recipients)
	 */
	public function deleteMessage($messageId, $userId) {
		try {
			$this->db->getConnection()->beginTransaction();

			// Check if user is sender
			$message = $this->db->fetchOne(
				"SELECT sender_id FROM messages WHERE id = ?",
				[$messageId]
			);

			if (!$message) {
				throw new Exception('Message not found');
			}

			if ($message['sender_id'] == $userId) {
				// User is sender - delete the entire message
				$this->db->query("DELETE FROM message_attachments WHERE message_id = ?", [$messageId]);
				$this->db->query("DELETE FROM message_recipients WHERE message_id = ?", [$messageId]);
				$this->db->query("DELETE FROM messages WHERE id = ?", [$messageId]);
			} else {
				// User is recipient - remove from recipients only
				$this->db->query(
					"DELETE FROM message_recipients WHERE message_id = ? AND recipient_id = ?",
					[$messageId, $userId]
				);
			}

			$this->db->getConnection()->commit();
			return true;
		} catch (Exception $e) {
			$this->db->getConnection()->rollBack();
			throw $e;
		}
	}

	public function searchUsers($search = '', $role = '', $excludeUserId = null) {
		$sql = "SELECT id, namalengkap, username, email, role, picture, status FROM users WHERE status = 'aktif'";
		$params = [];
		
		// Exclude current user if provided
		if ($excludeUserId) {
			$sql .= " AND id != ?";
			$params[] = $excludeUserId;
		}
		
		if (!empty($search)) {
			$sql .= " AND (namalengkap LIKE ? OR username LIKE ? OR email LIKE ?)";
			$searchPattern = "%{$search}%";
			$params[] = $searchPattern;
			$params[] = $searchPattern;
			$params[] = $searchPattern;
		}
		
		if (!empty($role)) {
			$sql .= " AND role = ?";
			$params[] = $role;
		}
		
		$sql .= " ORDER BY namalengkap ASC LIMIT 50";
		
		return $this->db->fetchAll($sql, $params);
	}

	public function saveAttachment($messageId, $filename, $filepath, $mimetype, $filesize) {
		$sql = "INSERT INTO message_attachments (message_id, filename, original_name, file_path, file_size, mime_type, created_at) 
				VALUES (?, ?, ?, ?, ?, ?, NOW())";
		return $this->db->query($sql, [$messageId, $filename, $filename, $filepath, $filesize, $mimetype]);
	}

	public function getAttachments($messageId) {
		$sql = "SELECT * FROM message_attachments WHERE message_id = ? ORDER BY created_at ASC";
		return $this->db->fetchAll($sql, [$messageId]);
	}

	public function getMessageForReply($messageId, $userId) {
		$message = $this->db->fetchOne(
			"SELECT m.*, u.namalengkap as sender_name, u.email as sender_email 
			FROM messages m 
			JOIN users u ON m.sender_id = u.id 
			WHERE m.id = ? AND (m.sender_id = ? OR EXISTS (
				SELECT 1 FROM message_recipients mr 
				WHERE mr.message_id = m.id AND mr.recipient_id = ?
			))",
			[$messageId, $userId, $userId]
		);
		
		if ($message) {
			// Get sender info for reply
			$message['reply_sender'] = [
				'id' => $message['sender_id'],
				'name' => $message['sender_name'],
				'email' => $message['sender_email']
			];
		}
		
		return $message;
	}

	public function getMessageForForward($messageId, $userId) {
		$message = $this->db->fetchOne(
			"SELECT m.*, u.namalengkap as sender_name, u.email as sender_email 
			FROM messages m 
			JOIN users u ON m.sender_id = u.id 
			WHERE m.id = ? AND (m.sender_id = ? OR EXISTS (
				SELECT 1 FROM message_recipients mr 
				WHERE mr.message_id = m.id AND mr.recipient_id = ?
			))",
			[$messageId, $userId, $userId]
		);
		
		if ($message) {
			// Get original sender info for forward
			$message['forward_sender'] = [
				'id' => $message['sender_id'],
				'name' => $message['sender_name'],
				'email' => $message['sender_email']
			];
			
			// Get attachments for forward
			$message['attachments'] = $this->getAttachments($messageId);
		}
		
		return $message;
	}
	
	/**
	 * Get paginated inbox messages
	 */
	public function getPaginatedInboxMessages($userId, $page = 1, $perPage = 20, $search = '') {
		$offset = ($page - 1) * $perPage;
		
		// Build WHERE clause for search
		$whereClause = 'WHERE mr.recipient_id = ?';
		$params = [$userId];
		
		if (!empty($search)) {
			$whereClause .= ' AND (m.subject LIKE ? OR m.content LIKE ? OR u.namalengkap LIKE ?)';
			$searchTerm = "%{$search}%";
			$params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
		}
		
		// Get total count
		$countSql = "
			SELECT COUNT(*) as total 
			FROM messages m
			INNER JOIN message_recipients mr ON m.id = mr.message_id
			INNER JOIN users u ON m.sender_id = u.id
			{$whereClause}
		";
		$totalResult = $this->db->fetchOne($countSql, $params);
		$total = (int)($totalResult['total'] ?? 0);
		
		
		// Get paginated data
		$sql = "
			SELECT 
				m.*,
				u.namalengkap as sender_name,
				u.email as sender_email,
				u.picture as sender_picture,
				mr.is_read,
				mr.read_at
			FROM messages m
			INNER JOIN message_recipients mr ON m.id = mr.message_id
			INNER JOIN users u ON m.sender_id = u.id
			{$whereClause}
			ORDER BY m.created_at DESC
			LIMIT {$perPage} OFFSET {$offset}
		";
		$data = $this->db->fetchAll($sql, $params);
		
		$totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
		$hasNext = $page < $totalPages;
		$hasPrev = $page > 1;
		
		
		return [
			'data' => $data,
			'total' => $total,
			'page' => $page,
			'per_page' => $perPage,
			'total_pages' => $totalPages,
			'has_next' => $hasNext,
			'has_prev' => $hasPrev
		];
	}
	
	/**
	 * Get paginated sent messages
	 */
	public function getPaginatedSentMessages($userId, $page = 1, $perPage = 20, $search = '') {
		$offset = ($page - 1) * $perPage;
		
		// Build WHERE clause for search
		$whereClause = 'WHERE m.sender_id = ?';
		$params = [$userId];
		
		if (!empty($search)) {
			$whereClause .= ' AND (m.subject LIKE ? OR m.content LIKE ?)';
			$searchTerm = "%{$search}%";
			$params = array_merge($params, [$searchTerm, $searchTerm]);
		}
		
		// Get total count
		$countSql = "SELECT COUNT(*) as total FROM messages m {$whereClause}";
		$totalResult = $this->db->fetchOne($countSql, $params);
		$total = (int)($totalResult['total'] ?? 0);
		
		// Get paginated data
		$sql = "
			SELECT 
				m.*,
				(SELECT COUNT(*) FROM message_recipients mr WHERE mr.message_id = m.id) as recipient_count,
				(SELECT GROUP_CONCAT(u.namalengkap SEPARATOR ', ') 
				 FROM message_recipients mr 
				 LEFT JOIN users u ON mr.recipient_id = u.id 
				 WHERE mr.message_id = m.id) as recipient_names
			FROM messages m
			{$whereClause}
			ORDER BY m.created_at DESC
			LIMIT {$perPage} OFFSET {$offset}
		";
		$data = $this->db->fetchAll($sql, $params);
		
		$totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
		
		return [
			'data' => $data,
			'total' => $total,
			'page' => $page,
			'per_page' => $perPage,
			'total_pages' => $totalPages,
			'has_next' => $page < $totalPages,
			'has_prev' => $page > 1
		];
	}
}


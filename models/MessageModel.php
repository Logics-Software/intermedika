<?php
class MessageModel {
	private $db;

	public function __construct() {
		$this->db = Database::getInstance();
	}

	/**
	 * Create a new message with recipients
	 * Note: This method does not manage transactions - caller should handle transactions
	 */
	public function createMessage($data, $recipientIds = []) {
		$conn = $this->db->getConnection();
		$transactionStartedHere = false;
		
		try {
			// Only start transaction if one is not already active
			if (!$conn->inTransaction()) {
				$conn->beginTransaction();
				$transactionStartedHere = true;
			}

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

			// Only commit if we started the transaction here
			if ($transactionStartedHere) {
				$conn->commit();
			}
			return $messageId;
		} catch (Exception $e) {
			// Only rollback if we started the transaction here
			if ($transactionStartedHere && $conn->inTransaction()) {
				$conn->rollBack();
			}
			throw $e;
		}
	}

	/**
	 * Find message by ID - simple direct query
	 */
	public function findById($messageId) {
		$messageId = (int)$messageId;
		error_log("MessageModel::findById() - Searching for message ID: {$messageId}");
		
		$sql = "SELECT m.*,
				COALESCE(u.namalengkap, 'Unknown') as sender_name, 
				COALESCE(u.email, '-') as sender_email, 
				u.picture as sender_picture 
			 FROM messages m 
			 LEFT JOIN users u ON m.sender_id = u.id 
			 WHERE m.id = ?";
		
		$result = $this->db->fetchOne($sql, [$messageId]);
		error_log("MessageModel::findById() - Query result: " . var_export($result ? 'FOUND' : 'NOT FOUND', true));
		
		if ($result) {
			error_log("MessageModel::findById() - Message ID in result: " . ($result['id'] ?? 'NO ID'));
		}
		
		return $result ? $result : null;
	}

	/**
	 * Get recipients for a message
	 */
	public function getRecipients($messageId) {
		$sql = "SELECT mr.*, 
				COALESCE(u.namalengkap, 'Unknown') as recipient_name, 
				COALESCE(u.email, '-') as recipient_email, 
				u.picture as recipient_picture
			 FROM message_recipients mr
			 LEFT JOIN users u ON mr.recipient_id = u.id
			 WHERE mr.message_id = ?";
		return $this->db->fetchAll($sql, [$messageId]);
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
		try {
			$sql = "SELECT id, namalengkap, username, email, role, picture, status FROM users WHERE status = 'aktif'";
			$params = [];
			
			// Exclude current user if provided
			if ($excludeUserId) {
				$sql .= " AND id != ?";
				$params[] = (int)$excludeUserId;
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
			
			$result = $this->db->fetchAll($sql, $params);
			
			// Ensure result is always an array
			if (!is_array($result)) {
				error_log('MessageModel::searchUsers() - fetchAll returned non-array: ' . gettype($result));
				return [];
			}
			
			return $result;
		} catch (Exception $e) {
			error_log('MessageModel::searchUsers() Error: ' . $e->getMessage());
			error_log('Stack trace: ' . $e->getTraceAsString());
			return [];
		} catch (Error $e) {
			error_log('MessageModel::searchUsers() Fatal Error: ' . $e->getMessage());
			error_log('Stack trace: ' . $e->getTraceAsString());
			return [];
		}
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
		
		// Ensure userId is integer
		$userId = (int)$userId;
		
		// Build WHERE clause
		$whereClause = 'WHERE mr.recipient_id = ?';
		$params = [$userId];
		
		if (!empty($search)) {
			$whereClause .= ' AND (m.subject LIKE ? OR m.content LIKE ?)';
			$searchTerm = "%{$search}%";
			$params[] = $searchTerm;
			$params[] = $searchTerm;
		}
		
		// Get total count - simple query
		$countSql = "
			SELECT COUNT(*) as total 
			FROM message_recipients mr
			INNER JOIN messages m ON m.id = mr.message_id
			{$whereClause}
		";
		$totalResult = $this->db->fetchOne($countSql, $params);
		$total = (int)($totalResult['total'] ?? 0);
		
		// Get paginated data - simple direct query
		$sql = "
			SELECT 
				m.*,
				COALESCE(u.namalengkap, 'Unknown') as sender_name,
				COALESCE(u.email, '-') as sender_email,
				u.picture as sender_picture,
				mr.is_read,
				mr.read_at
			FROM message_recipients mr
			INNER JOIN messages m ON m.id = mr.message_id
			LEFT JOIN users u ON m.sender_id = u.id
			{$whereClause}
			ORDER BY m.created_at DESC
			LIMIT ? OFFSET ?
		";
		$params[] = $perPage;
		$params[] = $offset;
		$data = $this->db->fetchAll($sql, $params);
		
		// Ensure data is always an array
		if (!is_array($data)) {
			$data = [];
		}
		
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
		
		// Ensure userId is integer
		$userId = (int)$userId;
		
		// Build WHERE clause for search
		$whereClause = 'WHERE m.sender_id = ?';
		$params = [$userId];
		
		if (!empty($search)) {
			$whereClause .= ' AND (m.subject LIKE ? OR m.content LIKE ?)';
			$searchTerm = "%{$search}%";
			$params = array_merge($params, [$searchTerm, $searchTerm]);
		}
		
		// Get total count - simple query
		$countSql = "SELECT COUNT(*) as total FROM messages m {$whereClause}";
		$totalResult = $this->db->fetchOne($countSql, $params);
		$total = (int)($totalResult['total'] ?? 0);
		
		// Get paginated data - simple direct query
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
			LIMIT ? OFFSET ?
		";
		$params[] = $perPage;
		$params[] = $offset;
		$data = $this->db->fetchAll($sql, $params);
		
		// Ensure data is always an array
		if (!is_array($data)) {
			$data = [];
		}
		
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

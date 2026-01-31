<?php
class MessageController extends Controller {
	private $messageModel;
	private $userModel;

	public function __construct() {
		parent::__construct();
		$this->messageModel = new MessageModel();
		$this->userModel = new User();
	}

	/**
	 * Display inbox messages
	 */
	public function index() {
		Auth::requireAuth();

		$userId = Auth::user()['id'];
		$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
		$search = trim($_GET['search'] ?? '');
		$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
		$perPage = in_array($perPage, [10, 25, 50, 100, 200, 500, 1000]) ? $perPage : 10;

		// Get paginated messages
		$result = $this->messageModel->getPaginatedInboxMessages($userId, $page, $perPage, $search);
		$unreadCount = $this->messageModel->getUnreadCount($userId);

		// Ensure messages is always an array
		$messages = is_array($result['data'] ?? null) ? $result['data'] : [];

		$this->view('messages/index', [
			'title' => 'Pesan Masuk',
			'messages' => $messages,
			'unread_count' => $unreadCount,
			'search' => $search,
			'pagination' => [
				'current_page' => $result['page'] ?? 1,
				'total_pages' => $result['total_pages'] ?? 1,
				'total_items' => $result['total'] ?? 0,
				'per_page' => $result['per_page'] ?? $perPage,
				'has_next' => $result['has_next'] ?? false,
				'has_prev' => $result['has_prev'] ?? false
			]
		]);
	}

	/**
	 * Display sent messages
	 */
	public function sent() {
		Auth::requireAuth();

		$userId = Auth::user()['id'];
		$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
		$search = trim($_GET['search'] ?? '');
		$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
		$perPage = in_array($perPage, [10, 25, 50, 100, 200, 500, 1000]) ? $perPage : 10;

		// Get paginated sent messages
		$result = $this->messageModel->getPaginatedSentMessages($userId, $page, $perPage, $search);

		// Ensure messages is always an array
		$messages = is_array($result['data'] ?? null) ? $result['data'] : [];

		$this->view('messages/sent', [
			'title' => 'Pesan Terkirim',
			'messages' => $messages,
			'search' => $search,
			'pagination' => [
				'current_page' => $result['page'] ?? 1,
				'total_pages' => $result['total_pages'] ?? 1,
				'total_items' => $result['total'] ?? 0,
				'per_page' => $result['per_page'] ?? $perPage,
				'has_next' => $result['has_next'] ?? false,
				'has_prev' => $result['has_prev'] ?? false
			]
		]);
	}

	/**
	 * Show compose message form
	 */
	public function create() {
		Auth::requireAuth();

		$userId = Auth::user()['id'];

		// Get all users for recipient selection
		$users = $this->messageModel->getAllUsers($userId);
		
		// Initialize reply and forward data
		$replyData = null;
		$forwardData = null;
		
		// Check if this is a reply to a message
		$replyId = $_GET['reply'] ?? null;
		if ($replyId) {
			$replyData = $this->messageModel->getMessageForReply($replyId, $userId);
		}
		
		// Check if this is a forward of a message
		$forwardId = $_GET['forward'] ?? null;
		if ($forwardId) {
			$forwardData = $this->messageModel->getMessageForForward($forwardId, $userId);
		}

		$this->view('messages/create', [
			'title' => 'Tulis Pesan',
			'users' => $users,
			'reply_data' => $replyData,
			'forward_data' => $forwardData
		]);
	}

	/**
	 * Store new message
	 */
	public function store() {
		Auth::requireAuth();

		$userId = Auth::user()['id'];

		// Validation
		if (empty($_POST['subject']) || empty($_POST['content']) || empty($_POST['recipients'])) {
			Session::flash('error', 'Subjek, isi pesan, dan penerima wajib diisi');
			$this->redirect('/messages/create');
			return;
		}

		$conn = $this->db->getConnection();
		$transactionStarted = false;

		try {
			$conn->beginTransaction();
			$transactionStarted = true;
			
			// Prepare message data
			$messageData = [
				'sender_id' => $userId,
				'subject' => trim($_POST['subject']),
				'content' => $_POST['content'],
				'message_type' => $_POST['message_type'] ?? 'direct',
				'status' => 'sent'
			];

			// Parse recipients (can be comma-separated or array)
			$recipientIds = [];
			if (is_string($_POST['recipients'])) {
				$recipientIds = array_filter(array_map('trim', explode(',', $_POST['recipients'])));
			} elseif (is_array($_POST['recipients'])) {
				$recipientIds = array_filter($_POST['recipients']);
			}

			if (empty($recipientIds)) {
				if ($transactionStarted && $conn->inTransaction()) {
					$conn->rollBack();
				}
				Session::flash('error', 'Pilih minimal satu penerima');
				$this->redirect('/messages/create');
				return;
			}

			// Create message
			$messageId = $this->messageModel->createMessage($messageData, $recipientIds);

			if ($messageId) {
				// Handle attachments
				if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
					$this->handleAttachments($messageId, $_FILES['attachments']);
				}
				
				if ($transactionStarted && $conn->inTransaction()) {
					$conn->commit();
				}
				Session::flash('success', 'Pesan berhasil dikirim');
				$this->redirect('/messages?sent=true');
			} else {
				if ($transactionStarted && $conn->inTransaction()) {
					$conn->rollBack();
				}
				Session::flash('error', 'Gagal mengirim pesan');
				$this->redirect('/messages/create');
			}
		} catch (Exception $e) {
			if ($transactionStarted && $conn->inTransaction()) {
				try {
					$conn->rollBack();
				} catch (PDOException $rollbackException) {
					// Ignore rollback errors
				}
			}
			Session::flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
			$this->redirect('/messages/create');
		}
	}

	/**
	 * Show specific message
	 */
	public function show($id) {
		if (empty($id)) {
			error_log("MessageController::show called with empty ID. REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
			Session::flash('error', 'ID pesan tidak valid');
			$this->redirect('/messages');
			return;
		}

		Auth::requireAuth();

		$messageId = (int)$id;
		$userId = Auth::user()['id'];
		
		// Get message by ID
		$message = $this->messageModel->findById($messageId);

		// If not found, try direct query as fallback
		if (!$message) {
			$message = $this->db->fetchOne("SELECT * FROM messages WHERE id = ?", [$messageId]);
			if ($message) {
				// Get sender info
				$sender = $this->db->fetchOne("SELECT namalengkap, email, picture FROM users WHERE id = ?", [$message['sender_id'] ?? 0]);
				$message['sender_name'] = $sender['namalengkap'] ?? 'Unknown';
				$message['sender_email'] = $sender['email'] ?? '-';
				$message['sender_picture'] = $sender['picture'] ?? null;
			}
		}

		if (!$message) {
			Session::flash('error', 'Pesan tidak ditemukan');
			$this->redirect('/messages');
			return;
		}

		// Ensure message has required fields
		if (!isset($message['id'])) {
			$message['id'] = $messageId;
		}
		if (!isset($message['content'])) {
			$message['content'] = '';
		}
		if (!isset($message['subject'])) {
			$message['subject'] = '(No Subject)';
		}

		// Get recipients
		$recipients = $this->messageModel->getRecipients($messageId);
		$message['recipients'] = is_array($recipients) ? $recipients : [];

		// Get attachments
		$attachments = $this->messageModel->getAttachments($messageId);
		$message['attachments'] = is_array($attachments) ? $attachments : [];

		// Check if user is recipient and mark as read if needed
		$isRecipient = false;
		foreach ($message['recipients'] as $recipient) {
			if (($recipient['recipient_id'] ?? 0) == $userId) {
				$isRecipient = true;
				if (!($recipient['is_read'] ?? 0)) {
					$this->messageModel->markAsRead($messageId, $userId);
				}
				break;
			}
		}

		// Verify data before passing
		if (empty($message) || !is_array($message)) {
			error_log("MessageController::show - message empty for ID: {$messageId}. Data: " . print_r($message, true));
			Session::flash('error', 'Pesan tidak ditemukan');
			$this->redirect('/messages');
			return;
		}

		// Pass data to view
		// Use $messageData to avoid conflict with $message in alerts.php
		$this->view('messages/show', [
			'title' => 'Detail Pesan',
			'messageData' => $message,
			'message' => $message, // Keep for backward compatibility
			'is_recipient' => $isRecipient
		]);
	}

	/**
	 * Delete message
	 */
	public function delete($id = null) {
		Auth::requireAuth();

		$messageId = $id ?? ($_GET['id'] ?? null);
		if (!$messageId) {
			$this->json(['success' => false, 'message' => 'ID pesan tidak valid']);
			return;
		}
		$userId = Auth::user()['id'];

		try {
			$result = $this->messageModel->deleteMessage($messageId, $userId);

			if ($result) {
				$this->json(['success' => true, 'message' => 'Pesan berhasil dihapus']);
			} else {
				$this->json(['success' => false, 'message' => 'Gagal menghapus pesan']);
			}
		} catch (Exception $e) {
			$this->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
		}
	}

	/**
	 * Search messages
	 */
	public function search() {
		Auth::requireAuth();

		$userId = Auth::user()['id'];
		$searchTerm = trim($_GET['q'] ?? '');
		$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
		$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
		$perPage = in_array($perPage, [10, 25, 50, 100, 200, 500, 1000]) ? $perPage : 10;

		if (empty($searchTerm)) {
			$this->redirect('/messages');
			return;
		}

		// Search messages with pagination
		$result = $this->messageModel->getPaginatedInboxMessages($userId, $page, $perPage, $searchTerm);

		$this->view('messages/search', [
			'title' => 'Hasil Pencarian',
			'messages' => $result['data'],
			'search_term' => $searchTerm,
			'pagination' => [
				'current_page' => $result['page'],
				'total_pages' => $result['total_pages'],
				'total_items' => $result['total'],
				'per_page' => $result['per_page'],
				'has_next' => $result['has_next'],
				'has_prev' => $result['has_prev']
			]
		]);
	}

	/**
	 * Get unread count (AJAX)
	 */
	public function getUnreadCount() {
		Auth::requireAuth();

		$userId = Auth::user()['id'];
		$unreadCount = $this->messageModel->getUnreadCount($userId);

		$this->json(['success' => true, 'unread_count' => $unreadCount]);
	}

	/**
	 * Mark all messages as read (AJAX)
	 */
	public function markAllAsRead() {
		Auth::requireAuth();

		$userId = Auth::user()['id'];

		try {
			$result = $this->messageModel->markAllAsRead($userId);
			
			if ($result) {
				$this->json(['success' => true, 'message' => 'All messages marked as read']);
			} else {
				$this->json(['success' => false, 'message' => 'Failed to mark messages as read']);
			}
		} catch (Exception $e) {
			$this->json(['success' => false, 'message' => 'Failed to mark messages as read']);
		}
	}

	/**
	 * Mark message as read (AJAX)
	 */
	public function markAsRead() {
		Auth::requireAuth();

		$messageId = $_POST['message_id'] ?? $_GET['message_id'] ?? null;
		$userId = Auth::user()['id'];

		if (!$messageId) {
			$this->json(['success' => false, 'message' => 'Message ID required']);
			return;
		}

		try {
			$result = $this->messageModel->markAsRead($messageId, $userId);
			
			if ($result) {
				$this->json(['success' => true, 'message' => 'Message marked as read']);
			} else {
				$this->json(['success' => false, 'message' => 'Failed to mark as read']);
			}
		} catch (Exception $e) {
			$this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
		}
	}

	/**
	 * Search users for recipient selection (AJAX)
	 */
	public function searchUsers() {
		try {
			Auth::requireAuth();

			$user = Auth::user();
			if (!$user || !isset($user['id'])) {
				$this->json(['success' => false, 'message' => 'User tidak ditemukan'], 401);
				return;
			}

			$search = trim($_GET['search'] ?? '');
			$role = trim($_GET['role'] ?? '');
			$currentUserId = (int)$user['id'];
			
			$users = $this->messageModel->searchUsers($search, $role, $currentUserId);
			
			// Ensure users is always an array
			if (!is_array($users)) {
				$users = [];
			}
			
			$this->json(['success' => true, 'users' => $users]);
		} catch (Exception $e) {
			error_log('MessageController::searchUsers() Error: ' . $e->getMessage());
			error_log('Stack trace: ' . $e->getTraceAsString());
			$this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
		} catch (Error $e) {
			error_log('MessageController::searchUsers() Fatal Error: ' . $e->getMessage());
			error_log('Stack trace: ' . $e->getTraceAsString());
			$this->json(['success' => false, 'message' => 'Fatal Error: ' . $e->getMessage()], 500);
		}
	}

	private function handleAttachments($messageId, $files) {
		$uploadDir = __DIR__ . '/../assets/uploads/attachments/';
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0755, true);
		}

		$allowedTypes = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif'];
		$maxSize = 5 * 1024 * 1024; // 5MB

		for ($i = 0; $i < count($files['name']); $i++) {
			if ($files['error'][$i] === UPLOAD_ERR_OK) {
				$fileName = $files['name'][$i];
				$fileSize = $files['size'][$i];
				$fileTmp = $files['tmp_name'][$i];
				$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

				// Validate file type
				if (!in_array($fileExt, $allowedTypes)) {
					continue; // Skip invalid files
				}

				// Validate file size
				if ($fileSize > $maxSize) {
					continue; // Skip oversized files
				}

				// Generate unique filename
				$newFileName = 'msg_' . $messageId . '_' . time() . '_' . $i . '.' . $fileExt;
				$filePath = $uploadDir . $newFileName;

				if (move_uploaded_file($fileTmp, $filePath)) {
					// Save attachment info to database
					$relativePath = 'assets/uploads/attachments/' . $newFileName;
					$this->messageModel->saveAttachment($messageId, $fileName, $relativePath, $files['type'][$i], $fileSize);
				}
			}
		}
	}
}

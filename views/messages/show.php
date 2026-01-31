<?php
// Debug: Check where this view is called from
$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
$calledFrom = '';
foreach ($backtrace as $trace) {
	if (isset($trace['file'])) {
		$calledFrom .= basename($trace['file']) . ':' . ($trace['line'] ?? '?') . ' -> ';
	}
}

$title = 'Detail Pesan';
require __DIR__ . '/../layouts/header.php';

// Use messageData if available (to avoid conflict with alerts.php), otherwise use message
$message = $messageData ?? $message ?? null;

// Check if message exists
if (!isset($message) || empty($message)) {
	echo '<div class="container mt-3">';
	echo '<div class="alert alert-danger">';
	echo '<strong>Error:</strong> Message data tidak ditemukan.<br>';
	echo 'Message variable exists: ' . (isset($message) ? 'Yes' : 'No') . '<br>';
	echo 'MessageData variable exists: ' . (isset($messageData) ? 'Yes' : 'No') . '<br>';
	echo 'Called from: ' . htmlspecialchars($calledFrom) . '<br>';
	echo 'REQUEST_URI: ' . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') . '<br>';
	if (isset($message)) {
		echo 'Message value: ' . var_export($message, true) . '<br>';
		echo 'Message type: ' . gettype($message) . '<br>';
		if (is_array($message)) {
			echo 'Message keys: ' . implode(', ', array_keys($message)) . '<br>';
		}
	}
	echo '</div>';
	echo '<a href="/messages" class="btn btn-secondary">Kembali</a>';
	echo '</div>';
	require __DIR__ . '/../layouts/footer.php';
	exit;
}
?>

<div class="container">
	<div class="breadcrumb-item">
		<div class="col-12">
			<nav aria-label="breadcrumb" data-breadcrumb-parent="/messages">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
					<li class="breadcrumb-item"><a href="/messages">Pesan Masuk</a></li>
					<li class="breadcrumb-item active">Detail Pesan</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="card" style="overflow: visible;">
		<div class="card-header">
			<div class="d-flex align-items-center">
				<h4 class="mb-0"><?= htmlspecialchars($message['subject'] ?? '(No Subject)') ?></h4>
			</div>
		</div>

		<div class="card-body" style="overflow: visible;">
			<div class="row">
				<div class="col-md-8">
					<!-- Message Content -->
					<div class="mb-4">
						<div class="d-flex align-items-center mb-3">
							<?php 
							$config = require __DIR__ . '/../../config/app.php';
							$avatarInitial = strtoupper(substr($message['sender_name'] ?? 'U', 0, 1));
							?>
							<?php if (!empty($message['sender_picture'])): ?>
								<img src="<?= BASE_URL . $config['upload_url'] . htmlspecialchars($message['sender_picture']) ?>" 
										alt="<?= htmlspecialchars($message['sender_name']) ?>" 
										class="rounded-circle me-3"
										style="width: 48px; height: 48px; object-fit: cover;"
										onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
							<?php else: ?>
								<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
									<?= $avatarInitial ?>
								</div>
							<?php endif; ?>
							<div>
								<h6 class="mb-1"><?= htmlspecialchars($message['sender_name'] ?? 'Unknown') ?></h6>
								<small class="text-muted">
									<?= date('d F Y, H:i', strtotime($message['created_at'] ?? 'now')) ?>
									<?php if (($message['status'] ?? '') === 'read'): ?>
										<span class="badge bg-success ms-2">Sudah dibaca</span>
									<?php endif; ?>
								</small>
							</div>
						</div>
					</div>
					
					<div class="message-body border rounded p-3 mb-3" style="min-height: 200px;">
						<?= $message['content'] ?? '<p class="text-muted"><em>Tidak ada konten pesan</em></p>' ?>
					</div>
					
					<?php if (!empty($message['attachments'])): ?>
					<div class="mt-4">
						<h6 class="mb-3">
							<?= icon('file-pdf', 'me-1 mb-1', 18) ?> Lampiran
						</h6>
						<div class="row">
							<?php foreach ($message['attachments'] as $attachment): ?>
							<div class="col-md-6 mb-2">
								<div class="card">
									<div class="card-body p-2">
										<div class="d-flex align-items-center">
											<?= icon('file-pdf', 'me-2 text-primary', 20) ?>
											<div class="flex-grow-1">
												<div class="fw-bold"><?= htmlspecialchars($attachment['original_name']) ?></div>
												<small class="text-muted">
													<?= number_format($attachment['file_size'] / 1024, 1) ?> KB
												</small>
											</div>
											<a href="<?= BASE_URL ?>/download/file?path=<?= urlencode($attachment['file_path']) ?>&name=<?= urlencode($attachment['original_name']) ?>" 
												class="btn btn-sm btn-outline-primary download-link" 
												download="<?= htmlspecialchars($attachment['original_name']) ?>"
												data-filename="<?= htmlspecialchars($attachment['original_name']) ?>"
												data-filesize="<?= $attachment['file_size'] ?>">
												<?= icon('arrow-down', 'mb-0', 16) ?>
											</a>
										</div>
									</div>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>
				</div>
				
				<div class="col-md-4">
					<!-- Recipients - Only show for sent messages -->
					<?php if (!empty($message['recipients']) && !$is_recipient): ?>
						<div class="card mt-3">
							<div class="card-header">
								<h6 class="mb-0">
									<?= icon('users', 'me-1 mb-1', 18) ?> Penerima
								</h6>
							</div>
							<div class="card-body">
								<?php foreach ($message['recipients'] as $recipient): ?>
									<div class="d-flex align-items-center mb-2">
										<?php 
										$recipientAvatarInitial = strtoupper(substr($recipient['recipient_name'] ?? 'U', 0, 1));
										?>
										<?php if (!empty($recipient['recipient_picture'])): ?>
											<img src="<?= BASE_URL . $config['upload_url'] . htmlspecialchars($recipient['recipient_picture']) ?>" 
													alt="<?= htmlspecialchars($recipient['recipient_name']) ?>" 
													class="rounded-circle me-2"
													style="width: 32px; height: 32px; object-fit: cover;"
													onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
											<div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; display: none;">
												<?= $recipientAvatarInitial ?>
											</div>
										<?php else: ?>
											<div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
												<?= $recipientAvatarInitial ?>
											</div>
										<?php endif; ?>
										<div class="flex-grow-1">
											<div class="fw-bold"><?= htmlspecialchars($recipient['recipient_name']) ?></div>
											<small class="text-muted"><?= htmlspecialchars($recipient['recipient_email']) ?></small>
										</div>
										<div>
											<?php if ($recipient['is_read']): ?>
												<span class="badge bg-success">Dibaca</span>
											<?php else: ?>
												<span class="badge bg-warning">Belum</span>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2" style="overflow: visible; position: relative; z-index: 1;">
			<a href="/messages" class="btn btn-secondary">
				<?= icon('circle-arrow-left', 'me-1 mb-1', 18) ?> Kembali
			</a>
			<!-- Desktop: Show all buttons -->
			<div class="d-none d-md-flex gap-2">
				<a href="/messages/create?reply=<?= $message['id'] ?>" class="btn btn-primary">
					<?= icon('circle-arrow-right', 'me-1 mb-1', 18) ?> Balas
				</a>
				<a href="/messages/create?forward=<?= $message['id'] ?>" class="btn btn-primary">
					<?= icon('share-from-square', 'me-1 mb-1', 18) ?> Teruskan
				</a>
				<button type="button" class="btn btn-primary" onclick="window.print()">
					<?= icon('print', 'me-1 mb-1', 18) ?> Cetak
				</button>
				<a href="/messages/delete/<?= $message['id'] ?>" class="btn btn-danger" onclick="event.preventDefault(); confirmDelete('Apakah Anda yakin ingin menghapus pesan ini?', this.href); return false;">
					<?= icon('trash-can', 'me-1 mb-1', 18) ?> Hapus
				</a>
			</div>
			<!-- Mobile: Dropdown menu -->
			<div class="d-md-none" style="position: relative; z-index: 1000;">
				<div class="dropdown">
					<button class="btn btn-primary dropdown-toggle" type="button" id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-boundary="viewport">
						<span class="text-dark"><?= icon('ellipsis-vertical', 'me-1 mb-1', 18) ?></span> Aksi
					</button>
					<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionDropdown" style="z-index: 9999;">
						<li>
							<a class="dropdown-item" href="/messages/create?reply=<?= $message['id'] ?>">
								<span style="filter: brightness(0);"><?= icon('circle-arrow-right', 'me-2 mb-1', 16) ?></span> Balas
							</a>
						</li>
						<li>
							<a class="dropdown-item" href="/messages/create?forward=<?= $message['id'] ?>">
								<span style="filter: brightness(0);"><?= icon('share-from-square', 'me-2 mb-1', 16) ?></span> Teruskan
							</a>
						</li>
						<li>
							<a class="dropdown-item" href="#" onclick="window.print(); return false;">
								<span style="filter: brightness(0);"><?= icon('print', 'me-2 mb-1', 16) ?></span> Cetak
							</a>
						</li>
						<li><hr class="dropdown-divider"></li>
						<li>
							<a class="dropdown-item text-danger" href="/messages/delete/<?= $message['id'] ?>" onclick="event.preventDefault(); confirmDelete('Apakah Anda yakin ingin menghapus pesan ini?', this.href); return false;">
								<span style="filter: brightness(0);"><?= icon('trash-can', 'me-2 mb-1', 16) ?></span> Hapus
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>


<?php require __DIR__ . '/../layouts/footer.php'; ?>

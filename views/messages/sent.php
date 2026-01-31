<?php
$title = 'Pesan Terkirim';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
	<div class="breadcrumb-item">
		<div class="col-12">
			<nav aria-label="breadcrumb" data-breadcrumb-parent="/messages">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
					<li class="breadcrumb-item"><a href="/messages">Pesan Masuk</a></li>
					<li class="breadcrumb-item active">Pesan Terkirim</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="card">
		<div class="card-header">
			<div class="d-flex align-items-center">
				<h4 class="mb-0 me-auto">Pesan Terkirim</h4>
				<div class="d-flex gap-2">
					<a href="/messages/create" class="btn btn-primary btn-sm"><?= icon('square-plus', 'me-1 mb-1', 18) ?> Tulis Pesan</a>
					<a href="/messages" class="btn btn-secondary btn-sm"><?= icon('inbox', 'me-1 mb-1', 18) ?> Pesan Masuk</a>
				</div>
			</div>
		</div>

		<div class="card-body">
			<!-- Search Form with Action Buttons -->
			<div class="d-flex flex-row gap-2 mb-3">
				<div class="flex-grow-1">
					<form method="GET" action="/messages/sent" class="d-flex" id="searchForm">
						<div class="input-group">
							<input type="text" name="search" class="form-control" placeholder="Cari pesan terkirim..." value="<?= htmlspecialchars($search ?? '') ?>" id="searchInput">
							<button type="button" class="btn btn-secondary" id="searchToggleBtn" title="Search">
								<span id="searchIcon"><?= icon('magnifying-glass', 'me-0 mb-1', 16) ?></span>
							</button>
						</div>
					</form>
				</div>
				<div style="min-width: 100px;">
					<select class="form-select" id="per_page" name="per_page" onchange="window.location.href='/messages/sent?' + new URLSearchParams({...new URLSearchParams(window.location.search), per_page: this.value}).toString()">
						<?php foreach ([10, 25, 50, 100, 200, 500, 1000] as $pp): ?>
						<option value="<?= $pp ?>" <?= ($pagination['per_page'] ?? 10) == $pp ? 'selected' : '' ?>><?= $pp ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<?php if (empty($messages)): ?>
				<div class="text-center py-5">
					<?= icon('paper-plane-dark', 'mb-3', 48) ?>
					<?php if (!empty($search)): ?>
						<h5 class="text-muted">Tidak ada hasil pencarian</h5>
						<p class="text-muted">Tidak ada pesan terkirim yang sesuai dengan pencarian "<strong><?= htmlspecialchars($search) ?></strong>"</p>
						<a href="/messages/sent" class="btn btn-secondary">
							<?= icon('list-check', 'me-1 mb-1', 18) ?> Lihat Semua Pesan Terkirim
						</a>
					<?php else: ?>
						<h5 class="text-muted">Belum ada pesan terkirim</h5>
						<p class="text-muted">Anda belum mengirim pesan apapun.</p>
						<a href="/messages/create" class="btn btn-primary">
							<?= icon('square-plus', 'me-1 mb-1', 18) ?> Tulis Pesan Pertama
						</a>
					<?php endif; ?>
				</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-striped align-middle">
						<thead>
							<tr>
								<th width="45%" style="min-width: 200px;">Subjek</th>
								<th width="30%" style="min-width: 150px;">Penerima</th>
								<th width="15%" style="min-width: 100px;">Tanggal</th>
								<th width="10%" style="min-width: 80px;">Aksi</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($messages as $message): ?>
								<tr>
									<td style="min-width: 200px;">
										<div class="fw-bold"><?= htmlspecialchars($message['subject'] ?? '') ?></div>
										<small class="text-muted">
											<?= htmlspecialchars(substr(strip_tags($message['content'] ?? ''), 0, 100)) ?>
											<?php if (strlen(strip_tags($message['content'] ?? '')) > 100): ?>...<?php endif; ?>
										</small>
									</td>
									<td style="min-width: 150px;">
										<small class="text-muted">
											<?php 
											$recipients = $message['recipient_names'] ?? '';
											$recipientCount = $message['recipient_count'] ?? 0;
											
											if (empty($recipients) || $recipientCount == 0) {
												echo '<span class="text-muted">Tidak ada penerima</span>';
											} else {
												$displayRecipients = $recipients;
												if (strlen($recipients) > 50) {
													$displayRecipients = substr($recipients, 0, 50) . '...';
												}
												echo htmlspecialchars($displayRecipients);
												if ($recipientCount > 1) {
													echo ' <span class="badge bg-success">' . $recipientCount . '</span>';
												}
											}
											?>
										</small>
									</td>
									<td style="min-width: 100px;">
										<small class="text-muted">
											<?= date('d/m/Y H:i', strtotime($message['created_at'])) ?>
										</small>
									</td>
									<td style="min-width: 80px;">
										<div class="d-flex gap-1">
											<a href="/messages/show/<?= $message['id'] ?>" class="btn btn-info btn-sm" 
											data-bs-toggle="tooltip" data-bs-title="Lihat Pesan">
												<?= icon('show', 'mb-0', 16) ?>
											</a>
											<a href="/messages/delete/<?= $message['id'] ?>" class="btn btn-danger btn-sm" 
											onclick="event.preventDefault(); confirmDelete('Apakah Anda yakin ingin menghapus pesan ini?', this.href); return false;" 
											data-bs-toggle="tooltip" data-bs-title="Hapus Pesan">
												<?= icon('trash-can', 'mb-0', 16) ?>
											</a>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<!-- Pagination -->
				<?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
				<div class="row mt-3">
					<div class="col-12">
						<nav aria-label="Sent Messages pagination">
							<ul class="pagination justify-content-center">
								<?php
								$queryParams = [];
								if (!empty($search)) $queryParams['search'] = $search;
								if (!empty($pagination['per_page'])) $queryParams['per_page'] = $pagination['per_page'];
								$queryString = http_build_query($queryParams);
								?>
								
								<?php if ($pagination['has_prev']): ?>
									<li class="page-item">
										<a class="page-link" href="/messages/sent?page=<?= $pagination['current_page'] - 1 ?><?= !empty($queryString) ? '&' . $queryString : '' ?>">Previous</a>
									</li>
								<?php endif; ?>

								<?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
									<?php $activeClass = $i == $pagination['current_page'] ? ' active' : ''; ?>
									<li class="page-item<?= $activeClass ?>">
										<a class="page-link" href="/messages/sent?page=<?= $i ?><?= !empty($queryString) ? '&' . $queryString : '' ?>"><?= $i ?></a>
									</li>
								<?php endfor; ?>

								<?php if ($pagination['has_next']): ?>
									<li class="page-item">
										<a class="page-link" href="/messages/sent?page=<?= $pagination['current_page'] + 1 ?><?= !empty($queryString) ? '&' . $queryString : '' ?>">Next</a>
									</li>
								<?php endif; ?>
							</ul>
						</nav>

						<div class="text-center text-muted mt-2">
							Menampilkan <?= (((int)$pagination['current_page'] - 1) * (int)$pagination['per_page']) + 1 ?> sampai 
							<?= min((int)$pagination['current_page'] * (int)$pagination['per_page'], (int)$pagination['total_items']) ?> 
							dari <?= $pagination['total_items'] ?> pesan terkirim
						</div>
					</div>
				</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
	// Search/Reset Toggle
	const searchForm = document.getElementById('searchForm');
	const searchInput = document.getElementById('searchInput');
	const searchToggleBtn = document.getElementById('searchToggleBtn');
	
	if (searchForm && searchInput && searchToggleBtn) {
		let isSearchMode = true;
		
		if (searchInput.value.trim() !== '') {
			isSearchMode = false;
			updateButtonState();
		}
		
		function updateButtonState() {
			const searchIcon = document.getElementById('searchIcon');
			const baseUrl = <?= json_encode($baseUrl ?? '/', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
			if (isSearchMode) {
				searchToggleBtn.title = 'Search';
				if (searchIcon) {
					searchIcon.innerHTML = '<img src="' + baseUrl + '/assets/icons/magnifying-glass.svg" alt="search" width="16" height="16" class="icon-inline me-0 mb-1">';
				}
				searchToggleBtn.onclick = function() {
					searchForm.submit();
				};
			} else {
				searchToggleBtn.title = 'Reset';
				if (searchIcon) {
					searchIcon.innerHTML = '<img src="' + baseUrl + '/assets/icons/cancel.svg" alt="reset" width="16" height="16" class="icon-inline me-0 mb-1">';
				}
				searchToggleBtn.onclick = function() {
					searchInput.value = '';
					searchForm.submit();
				};
			}
		}
		
		searchInput.addEventListener('input', function() {
			const hasValue = this.value.trim() !== '';
			if (hasValue && isSearchMode) {
				isSearchMode = false;
				updateButtonState();
			} else if (!hasValue && !isSearchMode) {
				isSearchMode = true;
				updateButtonState();
			}
		});
		
		updateButtonState();
	}
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


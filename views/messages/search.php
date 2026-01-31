<?php
$title = 'Hasil Pencarian';
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
					<li class="breadcrumb-item active">Hasil Pencarian</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h4 class="mb-0">
				Hasil Pencarian
				<small class="text-muted">untuk: "<?= htmlspecialchars($search_term) ?>"</small>
			</h4>
			<a href="/messages" class="btn btn-outline-secondary btn-sm">
				<?= icon('arrow-left', 'me-1 mb-1', 18) ?> Kembali ke Inbox
			</a>
		</div>

		<div class="card-body">
			<!-- Search Form -->
			<div class="row g-2 mb-3">
				<div class="col-12 col-md-6">
					<form method="GET" action="/messages/search" class="d-flex" id="searchForm">
						<div class="input-group">
							<input type="text" name="q" class="form-control" placeholder="Cari pesan..." value="<?= htmlspecialchars($search_term) ?>" id="searchInput">
							<button type="button" class="btn btn-secondary" id="searchToggleBtn" title="Search">
								<span id="searchIcon"><?= icon('magnifying-glass', 'me-0 mb-1', 16) ?></span>
							</button>
						</div>
					</form>
				</div>
				<div class="col-12 col-md-2">
					<select class="form-select" id="per_page" name="per_page" onchange="window.location.href='/messages/search?' + new URLSearchParams({...new URLSearchParams(window.location.search), per_page: this.value}).toString()">
						<?php foreach ([10, 25, 50, 100, 200, 500, 1000] as $pp): ?>
						<option value="<?= $pp ?>" <?= ($pagination['per_page'] ?? 10) == $pp ? 'selected' : '' ?>><?= $pp ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-12 col-md-4">
					<div class="d-flex gap-2 justify-content-end">
						<a href="/messages" class="btn btn-secondary btn-sm">
							<?= icon('table-list', 'me-1 mb-1', 18) ?> Masuk
						</a>
						<a href="/messages/sent" class="btn btn-outline-secondary btn-sm">
							<?= icon('paper-plane', 'me-1 mb-1', 18) ?> Pesan Terkirim
						</a>
					</div>
				</div>
			</div>

			<?php if (empty($messages)): ?>
				<div class="text-center py-5">
					<?= icon('table-list', 'mb-3', 48) ?>
					<h5 class="text-muted">Tidak ada hasil ditemukan</h5>
					<p class="text-muted">Tidak ada pesan yang sesuai dengan pencarian "<?= htmlspecialchars($search_term) ?>"</p>
					<a href="/messages" class="btn btn-primary">
						<?= icon('table-list', 'me-1 mb-1', 18) ?> Lihat Semua Pesan
					</a>
				</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-striped align-middle">
						<thead>
							<tr>
								<th width="5%"></th>
								<th width="25%">Pengirim</th>
								<th width="40%">Subjek</th>
								<th width="15%">Tanggal</th>
								<th width="15%">Aksi</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($messages as $message): ?>
								<tr class="<?= !$message['is_read'] ? 'table-warning' : '' ?>">
									<td>
										<?php if (!$message['is_read']): ?>
											<span class="badge bg-primary">Baru</span>
										<?php else: ?>
											<span class="badge bg-success">Dibaca</span>
										<?php endif; ?>
									</td>
									<td>
										<div class="d-flex align-items-center">
											<?php 
											$config = require __DIR__ . '/../../config/app.php';
											$avatarInitial = strtoupper(substr($message['sender_name'] ?? 'U', 0, 1));
											?>
											<?php if (!empty($message['sender_picture'])): ?>
												<img src="<?= BASE_URL . $config['upload_url'] . htmlspecialchars($message['sender_picture']) ?>" 
														alt="<?= htmlspecialchars($message['sender_name']) ?>" 
														class="rounded-circle me-2"
														style="width: 32px; height: 32px; object-fit: cover;"
														onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
												<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; display: none;">
													<?= $avatarInitial ?>
												</div>
											<?php else: ?>
												<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
													<?= $avatarInitial ?>
												</div>
											<?php endif; ?>
											<div>
												<div class="fw-bold"><?= htmlspecialchars($message['sender_name'] ?? 'Unknown') ?></div>
												<small class="text-muted"><?= htmlspecialchars($message['sender_email'] ?? '-') ?></small>
											</div>
										</div>
									</td>
									<td>
										<div class="fw-bold"><?= htmlspecialchars($message['subject'] ?? '(No Subject)') ?></div>
										<small class="text-muted">
											<?php 
											$content = strip_tags($message['content'] ?? '');
											$highlighted = str_ireplace($search_term ?? '', '<mark>' . htmlspecialchars($search_term ?? '') . '</mark>', htmlspecialchars($content));
											echo substr($highlighted, 0, 100);
											?>
											<?php if (strlen($content) > 100): ?>...<?php endif; ?>
										</small>
									</td>
									<td>
										<small class="text-muted">
											<?= date('d/m/Y H:i', strtotime($message['created_at'])) ?>
										</small>
									</td>
									<td>
										<div class="d-flex gap-1">
											<a href="/messages/show/<?= $message['id'] ?>" class="btn btn-info btn-sm">
												<?= icon('eye', 'mb-0', 16) ?>
											</a>
											<a href="/messages/delete/<?= $message['id'] ?>" class="btn btn-danger btn-sm" 
											onclick="event.preventDefault(); confirmDelete('Apakah Anda yakin ingin menghapus pesan ini?', this.href); return false;">
												<?= icon('trash', 'mb-0', 16) ?>
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
						<nav aria-label="Search Results pagination">
							<ul class="pagination justify-content-center">
								<?php
								$queryParams = [];
								if (!empty($search_term)) $queryParams['q'] = $search_term;
								if (!empty($pagination['per_page'])) $queryParams['per_page'] = $pagination['per_page'];
								$queryString = http_build_query($queryParams);
								?>
								
								<?php if ($pagination['has_prev']): ?>
									<li class="page-item">
										<a class="page-link" href="/messages/search?page=<?= $pagination['current_page'] - 1 ?><?= !empty($queryString) ? '&' . $queryString : '' ?>">Previous</a>
									</li>
								<?php endif; ?>

								<?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
									<?php $activeClass = $i == $pagination['current_page'] ? ' active' : ''; ?>
									<li class="page-item<?= $activeClass ?>">
										<a class="page-link" href="/messages/search?page=<?= $i ?><?= !empty($queryString) ? '&' . $queryString : '' ?>"><?= $i ?></a>
									</li>
								<?php endfor; ?>

								<?php if ($pagination['has_next']): ?>
									<li class="page-item">
										<a class="page-link" href="/messages/search?page=<?= $pagination['current_page'] + 1 ?><?= !empty($queryString) ? '&' . $queryString : '' ?>">Next</a>
									</li>
								<?php endif; ?>
							</ul>
						</nav>
						
						<div class="text-center text-muted mt-2">
							Menampilkan <?= (((int)$pagination['current_page'] - 1) * (int)$pagination['per_page']) + 1 ?> sampai 
							<?= min((int)$pagination['current_page'] * (int)$pagination['per_page'], (int)$pagination['total_items']) ?> 
							dari <?= $pagination['total_items'] ?> hasil pencarian
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


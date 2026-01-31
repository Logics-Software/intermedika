<?php
$title = 'Transaksi Order';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Helper function to generate sort URL
if (!function_exists('getSortUrl')) {
    function getSortUrl($column, $currentSortBy, $currentSortOrder, $search, $status, $dateFilter, $rawStartDate, $rawEndDate, $perPage) {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = [
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'status' => $status,
            'periode' => $dateFilter,
            'sort_by' => $column,
            'sort_order' => $newSortOrder
        ];
        if ($dateFilter === 'custom' && !empty($rawStartDate)) {
            $params['start_date'] = $rawStartDate;
        }
        if ($dateFilter === 'custom' && !empty($rawEndDate)) {
            $params['end_date'] = $rawEndDate;
        }
        return '/orders?' . http_build_query(array_filter($params));
    }
}

require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
		<div class="col-12">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
					<li class="breadcrumb-item active">Transaksi Order</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h4 class="mb-0 me-auto">Daftar Order</h4>
			<?php if (Auth::isSales()): ?>
			<a href="/orders/create" class="btn btn-primary btn-sm"><?= icon('square-plus', 'me-1 mb-1', 18) ?> Buat Order</a>
			<?php endif; ?>
		</div>

		<div class="card-body">
			<form method="GET" action="/orders" class="mb-3" id="filterForm">
				<div class="row g-2 align-items-end search-filter-card">
					<div class="col-12 col-lg-3">
						<input type="text" class="form-control" name="search" placeholder="Cari customer..." value="<?= htmlspecialchars($search ?? '') ?>">
					</div>
					<div class="col-6 col-lg-2">
						<select name="status" class="form-select" onchange="this.form.submit()">
							<option value="">Semua Status</option>
							<option value="order" <?= ($status ?? '') === 'order' ? 'selected' : '' ?>>Order</option>
							<option value="faktur" <?= ($status ?? '') === 'faktur' ? 'selected' : '' ?>>Faktur</option>
						</select>
					</div>
					<div class="col-6 col-lg-2">
						<select name="periode" id="dateFilter" class="form-select" onchange="handleDateFilterChange(true)">
							<option value="today" <?= ($dateFilter ?? 'today') === 'today' ? 'selected' : '' ?>>Hari ini</option>
							<option value="week" <?= ($dateFilter ?? '') === 'week' ? 'selected' : '' ?>>Minggu ini</option>
							<option value="month" <?= ($dateFilter ?? '') === 'month' ? 'selected' : '' ?>>Bulan ini</option>
							<option value="year" <?= ($dateFilter ?? '') === 'year' ? 'selected' : '' ?>>Tahun ini</option>
							<option value="custom" <?= ($dateFilter ?? 'today') === 'custom' ? 'selected' : '' ?>>Custom</option>
						</select>
					</div>
					<div class="col-6 col-lg-2" id="startDateWrapper" style="display: <?= ($dateFilter ?? 'today') === 'custom' ? 'block' : 'none' ?>;">
						<input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($rawStartDate ?? '') ?>" placeholder="Dari">
					</div>
					<div class="col-6 col-lg-2" id="endDateWrapper" style="display: <?= ($dateFilter ?? 'today') === 'custom' ? 'block' : 'none' ?>;">
						<input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($rawEndDate ?? '') ?>" placeholder="Sampai">
					</div>
					<div class="col-6 col-lg-1">
						<select name="per_page" class="form-select" onchange="this.form.submit()">
							<?php foreach ([10, 25, 50, 100, 200, 500, 1000] as $pp): ?>
							<option value="<?= $pp ?>" <?= ($perPage ?? 10) == $pp ? 'selected' : '' ?>><?= $pp ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-12 col-lg-4 d-lg-flex justify-content-lg-end">
						<div class="row g-2 w-100">
							<div class="col-6 col-lg-6">
								<button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
							</div>
							<div class="col-6 col-lg-6">
								<a href="/orders" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
							</div>
						</div>
					</div>
				</div>
				<input type="hidden" name="page" value="1">
				<input type="hidden" name="sort_by" value="<?= htmlspecialchars($sortBy ?? 'tanggalorder') ?>">
				<input type="hidden" name="sort_order" value="<?= htmlspecialchars($sortOrder ?? 'DESC') ?>">
			</form>

			<div class="table-responsive">
				<table class="table table-striped align-middle">
					<thead>
						<tr>
							<th class="th-sortable <?= ($sortBy ?? 'tanggalorder') === 'noorder' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
								<a href="<?= getSortUrl('noorder', $sortBy ?? 'tanggalorder', $sortOrder ?? 'DESC', $search ?? '', $status ?? '', $dateFilter ?? 'today', $rawStartDate ?? '', $rawEndDate ?? '', $perPage ?? 10) ?>" class="text-decoration-none text-dark">
									No Order
								</a>
							</th>
							<th class="th-sortable <?= ($sortBy ?? 'tanggalorder') === 'tanggalorder' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
								<a href="<?= getSortUrl('tanggalorder', $sortBy ?? 'tanggalorder', $sortOrder ?? 'DESC', $search ?? '', $status ?? '', $dateFilter ?? 'today', $rawStartDate ?? '', $rawEndDate ?? '', $perPage ?? 10) ?>" class="text-decoration-none text-dark">
									Tanggal
								</a>
							</th>
							<th class="th-sortable <?= ($sortBy ?? 'tanggalorder') === 'namacustomer' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
								<a href="<?= getSortUrl('namacustomer', $sortBy ?? 'tanggalorder', $sortOrder ?? 'DESC', $search ?? '', $status ?? '', $dateFilter ?? 'today', $rawStartDate ?? '', $rawEndDate ?? '', $perPage ?? 10) ?>" class="text-decoration-none text-dark">
									Customer
								</a>
							</th>
							<th>Alamat</th>
							<th class="text-end">Nilai</th>
							<th>Status</th>
							<th class="th-sortable <?= ($sortBy ?? 'tanggalorder') === 'nopenjualan' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
								<a href="<?= getSortUrl('nopenjualan', $sortBy ?? 'tanggalorder', $sortOrder ?? 'DESC', $search ?? '', $status ?? '', $dateFilter ?? 'today', $rawStartDate ?? '', $rawEndDate ?? '', $perPage ?? 10) ?>" class="text-decoration-none text-dark">
									No.Faktur
								</a>
							</th>
							<th>Aksi</th>
						</tr>
					</thead>
					<tbody>
					<?php if (empty($orders)): ?>
						<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
					<?php else: foreach ($orders as $row): ?>
						<tr>
							<td class="fw-semibold"><?= htmlspecialchars($row['noorder']) ?></td>
							<td><?= htmlspecialchars(date('d/m/Y', strtotime($row['tanggalorder']))) ?></td>
							<td><?= htmlspecialchars(($row['namacustomer'] ?? '') . (!empty($row['namabadanusaha']) ? ', ' . $row['namabadanusaha'] : '')) ?></td>
							<td><?= htmlspecialchars(($row['alamatcustomer'] ?? '') . (!empty($row['kota']) ? ', ' . $row['kota'] : '')) ?></td>
							<td class="text-end"><?= number_format((float)($row['nilaiorder'] ?? 0), 0, ',', '.') ?></td>
							<td align="center"><span class="badge bg-<?= ($row['status'] ?? '') === 'faktur' ? 'success' : 'warning' ?>"><?= htmlspecialchars(ucfirst($row['status'] ?? '')) ?></span></td>
							<td><?= htmlspecialchars(($row['nopenjualan'] ?? '-')) ?></td>
							<td>
								<div class="d-flex gap-1">
									<a href="/orders/view/<?= urlencode($row['noorder']) ?>" class="btn btn-sm btn-info text-white"><?= icon('show', 'mb-0', 16) ?></a>
									<?php if (($row['status'] ?? '') === 'order'): ?>
									<a href="/orders/edit/<?= urlencode($row['noorder']) ?>" class="btn btn-sm btn-warning"><?= icon('pen-to-square', 'mb-0', 16) ?></a>
									<a href="/orders/delete/<?= urlencode($row['noorder']) ?>" class="btn btn-sm btn-danger" onclick="event.preventDefault(); confirmDelete('Apakah Anda yakin ingin menghapus order <strong><?= htmlspecialchars($row['noorder']) ?></strong>?', this.href); return false;"><?= icon('trash-can', 'mb-0', 16) ?></a>
									<?php endif; ?>
								</div>
							</td>
						</tr>
					<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>

			<?php if ($totalPages > 1): ?>
			<?php
			// Get current page from URL (always use $_GET to ensure it's current)
			$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : (isset($page) ? (int)$page : 1);
			// Ensure currentPage is at least 1
			if ($currentPage < 1) {
				$currentPage = 1;
			}
			// Use currentPage for all calculations
			$page = $currentPage;
			$totalPages = (int)$totalPages;
			$perPage = (int)$perPage;
			
			// Build link function for pagination
			$buildLink = function ($p) use ($perPage, $search, $status, $dateFilter, $rawStartDate, $rawEndDate, $sortBy, $sortOrder) {
				$params = [
					'page' => $p,
					'per_page' => $perPage,
					'search' => $search,
					'status' => $status,
					'periode' => $dateFilter,
					'sort_by' => $sortBy,
					'sort_order' => $sortOrder
				];
				if ($dateFilter === 'custom' && !empty($rawStartDate)) {
					$params['start_date'] = $rawStartDate;
				}
				if ($dateFilter === 'custom' && !empty($rawEndDate)) {
					$params['end_date'] = $rawEndDate;
				}
				return '/orders?' . http_build_query(array_filter($params));
			};
			$maxLinks = 3;
			$half = (int)floor($maxLinks / 2);
			$start = max(1, $page - $half);
			$end = min($totalPages, $start + $maxLinks - 1);
			if ($end - $start + 1 < $maxLinks) {
				$start = max(1, $end - $maxLinks + 1);
			}
			?>
			<nav>
				<ul class="pagination justify-content-center">
					<li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
						<?php
						// Calculate previous page, ensuring it's an integer
						$prevPage = (int)max(1, (int)$page - 1);
						// Ensure prevPage is at least 1
						if ($prevPage < 1) $prevPage = 1;
						?>
						<a class="page-link" href="<?php echo $buildLink($prevPage); ?>">Previous</a>
					</li>
					<?php
					if ($start > 1) {
						echo '<li class="page-item"><a class="page-link" href="' . $buildLink(1) . '">1</a></li>';
						if ($start > 2) {
							echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
						}
					}
					for ($i = $start; $i <= $end; $i++) {
						echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="' . $buildLink($i) . '">' . $i . '</a></li>';
					}
					if ($end < $totalPages) {
						if ($end < $totalPages - 1) {
							echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
						}
						echo '<li class="page-item"><a class="page-link" href="' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
					}
					?>
					<li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
						<?php
						// Calculate next page: current page + 1 (increment)
						// $page is already cast to int and validated above
						// Simply increment current page
						$nextPage = $page + 1;
						
						// Only cap at totalPages if it exceeds (for disabled state)
						if ($nextPage > $totalPages) {
							$nextPage = $totalPages;
						}
						
						// Ensure it's an integer
						$nextPage = (int)$nextPage;
						?>
						<a class="page-link" href="<?php echo $buildLink($nextPage); ?>">Next</a>
					</li>
				</ul>
			</nav>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
function handleDateFilterChange(triggerSubmit = false) {
    const filter = document.getElementById('dateFilter').value;
    const startWrapper = document.getElementById('startDateWrapper');
    const endWrapper = document.getElementById('endDateWrapper');
    const isCustom = filter === 'custom';
    
    if (startWrapper && endWrapper) {
        startWrapper.style.display = isCustom ? 'block' : 'none';
        endWrapper.style.display = isCustom ? 'block' : 'none';
    }
    
    if (!isCustom && triggerSubmit) {
        const startInput = document.querySelector('input[name="start_date"]');
        const endInput = document.querySelector('input[name="end_date"]');
        if (startInput) startInput.value = '';
        if (endInput) endInput.value = '';
        const form = document.getElementById('filterForm');
        if (form) form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    handleDateFilterChange(false);
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>



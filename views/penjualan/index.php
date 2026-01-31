<?php
$title = 'Data Penjualan';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Helper function to generate sort URL
if (!function_exists('getSortUrlPenjualan')) {
    function getSortUrlPenjualan($column, $currentSortBy, $currentSortOrder, $search, $periode, $startDate, $endDate, $statuspkp, $perPage) {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = [
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'periode' => $periode,
            'sort_by' => $column,
            'sort_order' => $newSortOrder
        ];
        if ($periode === 'custom' && !empty($startDate)) {
            $params['start_date'] = $startDate;
        }
        if ($periode === 'custom' && !empty($endDate)) {
            $params['end_date'] = $endDate;
        }
        if (!empty($statuspkp)) {
            $params['statuspkp'] = $statuspkp;
        }
        return '/penjualan?' . http_build_query(array_filter($params));
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
                    <li class="breadcrumb-item active">Data Penjualan</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar Penjualan</h4>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="/penjualan" class="mb-3" id="penjualanFilterForm">
                        <div class="row g-2 align-items-end search-filter-card">
                            <div class="col-12 col-lg-3">
                                <input type="text" class="form-control" name="search" placeholder="Cari no penjualan, customer, sales" value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-6 col-lg-2">
                                <select name="statuspkp" class="form-select" onchange="this.form.submit()">
                                    <option value="">Semua</option>
                                    <option value="pkp" <?= $statuspkp === 'pkp' ? 'selected' : '' ?>>PKP</option>
                                    <option value="nonpkp" <?= $statuspkp === 'nonpkp' ? 'selected' : '' ?>>Non PKP</option>
                                </select>
                            </div>
                            <div class="col-6 col-lg-2">
                                <select name="periode" id="dateFilter" class="form-select" onchange="handleDateFilterChange(true)">
                                    <option value="today" <?= ($periode ?? 'today') === 'today' ? 'selected' : '' ?>>Hari ini</option>
                                    <option value="week" <?= ($periode ?? '') === 'week' ? 'selected' : '' ?>>Minggu ini</option>
                                    <option value="month" <?= ($periode ?? '') === 'month' ? 'selected' : '' ?>>Bulan ini</option>
                                    <option value="year" <?= ($periode ?? '') === 'year' ? 'selected' : '' ?>>Tahun ini</option>
                                    <option value="custom" <?= ($periode ?? 'today') === 'custom' ? 'selected' : '' ?>>Custom</option>
                                </select>
                            </div>
                            <div class="col-6 col-lg-2" id="startDateWrapper" style="display: <?= ($periode ?? 'today') === 'custom' ? 'block' : 'none' ?>;">
                                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate ?? '') ?>" placeholder="Dari">
                            </div>
                            <div class="col-6 col-lg-2" id="endDateWrapper" style="display: <?= ($periode ?? 'today') === 'custom' ? 'block' : 'none' ?>;">
                                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate ?? '') ?>" placeholder="Sampai">
                            </div>
                            <div class="col-6 col-lg-1">
                                <select name="per_page" class="form-select" onchange="this.form.submit()">
                                    <?php foreach ($perPageOptions as $option): ?>
                                    <option value="<?= $option ?>" <?= $perPage == $option ? 'selected' : '' ?>><?= $option ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-lg-4 d-lg-flex justify-content-lg-end">
                                <div class="row g-2 w-100">
                                    <div class="col-6 col-lg-6">
                                        <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                                    </div>
                                    <div class="col-6 col-lg-6">
                                        <a href="/penjualan" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sortBy ?? 'tanggalpenjualan') ?>">
                        <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sortOrder ?? 'DESC') ?>">
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="th-sortable <?= ($sortBy ?? 'tanggalpenjualan') === 'nopenjualan' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
                                        <a href="<?= getSortUrlPenjualan('nopenjualan', $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC', $search ?? '', $periode ?? 'today', $startDate ?? '', $endDate ?? '', $statuspkp ?? '', $perPage ?? 10) ?>" class="text-decoration-none text-dark">
                                            No.Faktur
                                        </a>
                                    </th>
                                    <th class="th-sortable <?= ($sortBy ?? 'tanggalpenjualan') === 'tanggalpenjualan' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
                                        <a href="<?= getSortUrlPenjualan('tanggalpenjualan', $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC', $search ?? '', $periode ?? 'today', $startDate ?? '', $endDate ?? '', $statuspkp ?? '', $perPage ?? 10) ?>" class="text-decoration-none text-dark">
                                            Tanggal
                                        </a>
                                    </th>
                                    <th class="th-sortable <?= ($sortBy ?? 'tanggalpenjualan') === 'tanggaljatuhtempo' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
                                        <a href="<?= getSortUrlPenjualan('tanggaljatuhtempo', $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC', $search ?? '', $periode ?? 'today', $startDate ?? '', $endDate ?? '', $statuspkp ?? '', $perPage ?? 10) ?>" class="text-decoration-none text-dark">
                                            J.T.Tempo
                                        </a>
                                    </th>
                                    <th class="th-sortable <?= ($sortBy ?? 'tanggalpenjualan') === 'namacustomer' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
                                        <a href="<?= getSortUrlPenjualan('namacustomer', $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC', $search ?? '', $periode ?? 'today', $startDate ?? '', $endDate ?? '', $statuspkp ?? '', $perPage ?? 10) ?>" class="text-decoration-none text-dark">
                                            Customer
                                        </a>
                                    </th>
                                    <th class="th-sortable <?= ($sortBy ?? 'tanggalpenjualan') === 'namasales' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
                                        <a href="<?= getSortUrlPenjualan('namasales', $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC', $search ?? '', $periode ?? 'today', $startDate ?? '', $endDate ?? '', $statuspkp ?? '', $perPage ?? 10) ?>" class="text-decoration-none text-dark">
                                            Sales
                                        </a>
                                    </th>
                                    <th class="text-end">Nilai Penjualan</th>
                                    <th class="text-end">Saldo</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($penjualan)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Tidak ada data penjualan</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($penjualan as $row): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nopenjualan']) ?></td>
                                    <td><?= $row['tanggalpenjualan'] ? date('d/m/Y', strtotime($row['tanggalpenjualan'])) : '-' ?></td>
                                    <td><?= $row['tanggaljatuhtempo'] ? date('d/m/Y', strtotime($row['tanggaljatuhtempo'])) : '-' ?></td>
                                    <td><?= htmlspecialchars($row['namacustomer'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['namasales'] ?? '-') ?></td>
                                    <td class="text-end"><?= number_format((float)$row['nilaipenjualan'], 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format((float)$row['saldopenjualan'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <a href="/penjualan/view/<?= urlencode($row['nopenjualan']) ?>" class="btn btn-sm btn-info text-white"><?= icon('show', 'me-0 mb-1', 14) ?></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
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
                                <a class="page-link" href="<?= buildPenjualanQuery($prevPage, $perPage, $search, $periode, $startDate, $endDate, $statuspkp, $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC') ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="' . buildPenjualanQuery(1, $perPage, $search, $periode, $startDate, $endDate, $statuspkp, $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC') . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                $active = $page == $i ? 'active' : '';
                                echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . buildPenjualanQuery($i, $perPage, $search, $periode, $startDate, $endDate, $statuspkp, $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC') . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="' . buildPenjualanQuery($totalPages, $perPage, $search, $periode, $startDate, $endDate, $statuspkp, $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC') . '">' . $totalPages . '</a></li>';
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
                                <a class="page-link" href="<?= buildPenjualanQuery($nextPage, $perPage, $search, $periode, $startDate, $endDate, $statuspkp, $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC') ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function buildPenjualanQuery($page, $perPage, $search, $periode, $startDate, $endDate, $statuspkp = null, $sortBy = null, $sortOrder = null) {
    $params = [
        'page' => max($page, 1),
        'per_page' => $perPage,
        'search' => $search,
        'periode' => $periode,
        'start_date' => $startDate,
        'end_date' => $endDate
    ];
    if (!empty($statuspkp)) {
        $params['statuspkp'] = $statuspkp;
    }
    if (!empty($sortBy)) {
        $params['sort_by'] = $sortBy;
    }
    if (!empty($sortOrder)) {
        $params['sort_order'] = $sortOrder;
    }
    return '/penjualan?' . http_build_query($params);
}
?>

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
        const form = document.getElementById('penjualanFilterForm');
        if (form) form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    handleDateFilterChange(false);
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>



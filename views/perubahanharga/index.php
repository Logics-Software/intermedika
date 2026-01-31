<?php
$title = 'Data Perubahan Harga';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Load sticky column CSS dan JS
$additionalStyles = $additionalStyles ?? [];
$additionalStyles[] = $baseUrl . '/assets/css/sticky-column.css';
$additionalScripts = $additionalScripts ?? [];
$additionalScripts[] = $baseUrl . '/assets/js/sticky-column.js';

// Helper function to generate sort URL
if (!function_exists('getSortUrlPerubahanharga')) {
    function getSortUrlPerubahanharga($column, $currentSortBy, $currentSortOrder, $search, $dateFilter, $rawStartDate, $rawEndDate, $perPage) {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = [
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
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
        return '/perubahanharga?' . http_build_query(array_filter($params));
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
                    <li class="breadcrumb-item active">Perubahan Harga</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 me-auto">Daftar Perubahan Harga</h4>
                    <!-- <a href="/perubahanharga/create" class="btn btn-primary btn-sm"><?= icon('square-plus', 'me-1 mb-1', 18) ?> Tambah Data</a> -->
                </div>
                <div class="card-body">
                    <form method="GET" action="/perubahanharga" class="mb-3" id="filterForm">
                        <div class="row g-2 align-items-end search-filter-card">
                            <div class="col-12 col-lg-3">
                                <input type="text" class="form-control" name="search" placeholder="Cari no perubahan, keterangan, barang..." value="<?= htmlspecialchars($search ?? '') ?>">
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
                                        <a href="/perubahanharga" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="page" value="1">
                        <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sortBy ?? 'tanggalperubahan') ?>">
                        <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sortOrder ?? 'DESC') ?>">
                    </form>

                    <div class="table-responsive table-sticky-column">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="sticky-col th-sortable <?= ($sortBy ?? 'tanggalperubahan') === 'namabarang' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>" style="min-width: 150px;">
                                        <a href="<?= getSortUrlPerubahanharga('namabarang', $sortBy ?? 'tanggalperubahan', $sortOrder ?? 'DESC', $search ?? '', $dateFilter ?? 'today', $rawStartDate ?? '', $rawEndDate ?? '', $perPage ?? 10) ?>" class="text-decoration-none text-dark">
                                            Nama Barang
                                        </a>
                                    </th>
                                    <th>No. Perubahan</th>
                                    <th class="th-sortable <?= ($sortBy ?? 'tanggalperubahan') === 'tanggalperubahan' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
                                        <a href="<?= getSortUrlPerubahanharga('tanggalperubahan', $sortBy ?? 'tanggalperubahan', $sortOrder ?? 'DESC', $search ?? '', $dateFilter ?? 'today', $rawStartDate ?? '', $rawEndDate ?? '', $perPage ?? 10) ?>" class="text-decoration-none text-dark">
                                            Tanggal
                                        </a>
                                    </th>
                                    <th>Keterangan</th>
                                    <th>Harga Lama</th>
                                    <th>Disc</th>
                                    <th>Harga Baru</th>
                                    <th>Disc</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">Tidak ada data perubahan harga</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($items as $row): ?>
                                <tr>
                                    <td class="sticky-col"><?= htmlspecialchars($row['namabarang'] ?? '-') ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['noperubahan']) ?></td>
                                    <td><?= $row['tanggalperubahan'] ? date('d/m/Y', strtotime($row['tanggalperubahan'])) : '-' ?></td>
                                    <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
                                    <td class="text-end"><?= number_format((float)$row['hargalama'], 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format((float)$row['discountlama'], 2, ',', '.') ?></td>
                                    <td class="text-end fw-semibold text-primary"><?= number_format((float)$row['hargabaru'], 0, ',', '.') ?></td>
                                    <td class="text-end fw-semibold text-primary"><?= number_format((float)$row['discountbaru'], 2, ',', '.') ?></td>
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
                                <a class="page-link" href="<?= buildPerubahanhargaQuery($prevPage, $perPage, $search, $dateFilter, $rawStartDate, $rawEndDate, $sortBy ?? 'tanggalperubahan', $sortOrder ?? 'DESC') ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="' . buildPerubahanhargaQuery(1, $perPage, $search, $dateFilter, $rawStartDate, $rawEndDate, $sortBy ?? 'tanggalperubahan', $sortOrder ?? 'DESC') . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                $active = $page == $i ? 'active' : '';
                                echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . buildPerubahanhargaQuery($i, $perPage, $search, $dateFilter, $rawStartDate, $rawEndDate, $sortBy ?? 'tanggalperubahan', $sortOrder ?? 'DESC') . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="' . buildPerubahanhargaQuery($totalPages, $perPage, $search, $dateFilter, $rawStartDate, $rawEndDate, $sortBy ?? 'tanggalperubahan', $sortOrder ?? 'DESC') . '">' . $totalPages . '</a></li>';
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
                                <a class="page-link" href="<?= buildPerubahanhargaQuery($nextPage, $perPage, $search, $dateFilter, $rawStartDate, $rawEndDate, $sortBy ?? 'tanggalperubahan', $sortOrder ?? 'DESC') ?>">Next</a>
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
function buildPerubahanhargaQuery($page, $perPage, $search, $dateFilter, $rawStartDate, $rawEndDate, $sortBy = null, $sortOrder = null) {
    $params = [
        'page' => max($page, 1),
        'per_page' => $perPage,
        'search' => $search,
        'periode' => $dateFilter,
        'start_date' => $rawStartDate,
        'end_date' => $rawEndDate
    ];
    if (!empty($sortBy)) {
        $params['sort_by'] = $sortBy;
    }
    if (!empty($sortOrder)) {
        $params['sort_order'] = $sortOrder;
    }
    return '/perubahanharga?' . http_build_query($params);
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
        const form = document.getElementById('filterForm');
        if (form) form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    handleDateFilterChange(false);
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


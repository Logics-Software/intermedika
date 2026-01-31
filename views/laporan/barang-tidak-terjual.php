<?php
$title = 'Laporan Barang Tidak Terjual';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Helper function to generate sort URL
if (!function_exists('getSortUrlBarangTidakTerjual')) {
    function getSortUrlBarangTidakTerjual($column, $currentSortBy, $currentSortOrder, $search, $perPage, $periode, $startDate, $endDate) {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = http_build_query([
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'periode' => $periode,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sort_by' => $column,
            'sort_order' => $newSortOrder
        ]);
        return '/laporan/barang-tidak-terjual?' . $params;
    }
}

// Helper function to get sort icon
if (!function_exists('getSortIconBarangTidakTerjual')) {
    function getSortIconBarangTidakTerjual($column, $currentSortBy, $currentSortOrder) {
        $config = require __DIR__ . '/../../config/app.php';
        $baseUrl = rtrim($config['base_url'], '/');
        if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
            $baseUrl = '/';
        }
        
        if ($currentSortBy != $column) {
            $iconPath = $baseUrl . '/assets/icons/arrows-up-down.svg';
            return '<img src="' . htmlspecialchars($iconPath) . '" alt="sort" class="sort-icon icon-inline" width="14" height="14">';
        }
        
        if ($currentSortOrder == 'ASC') {
            $iconPath = $baseUrl . '/assets/icons/arrow-up.svg';
            return '<img src="' . htmlspecialchars($iconPath) . '" alt="sort-up" class="sort-icon icon-inline" width="14" height="14">';
        } else {
            $iconPath = $baseUrl . '/assets/icons/arrow-down.svg';
            return '<img src="' . htmlspecialchars($iconPath) . '" alt="sort-down" class="sort-icon icon-inline" width="14" height="14">';
        }
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
                    <li class="breadcrumb-item active">Barang Tidak Terjual</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 me-auto">Laporan Barang Tidak Terjual</h4>
                        <div class="d-flex gap-2">
                            <?php
                            $exportParams = [];
                            if (!empty($search)) $exportParams['search'] = $search;
                            if (!empty($periode)) $exportParams['periode'] = $periode;
                            if ($periode === 'custom') {
                                if (!empty($startDate)) $exportParams['start_date'] = $startDate;
                                if (!empty($endDate)) $exportParams['end_date'] = $endDate;
                            }
                            if (!empty($sortBy)) $exportParams['sort_by'] = $sortBy;
                            if (!empty($sortOrder)) $exportParams['sort_order'] = $sortOrder;
                            $exportQuery = http_build_query($exportParams);
                            ?>
                            <a href="/laporan/barang-tidak-terjual?export=excel<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-success btn-sm">
                                <?= icon('file-excel', 'mb-1 me-2', 16) ?>
                                <span class="d-none d-md-inline">Export Excel</span>
                                <span class="d-inline d-md-none">Excel</span>
                            </a>
                            <a href="/laporan/barang-tidak-terjual?export=pdf<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-danger btn-sm">
                                <?= icon('file-pdf', 'mb-1 me-2', 16) ?>
                                <span class="d-none d-md-inline">Download PDF</span>
                                <span class="d-inline d-md-none">PDF</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="/laporan/barang-tidak-terjual" class="mb-3">
                        <div class="row g-2 search-filter-card">
                            <div class="col-12 col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Cari Nama Barang..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-6 col-md-2">
                                <select name="periode" class="form-select" onchange="window.toggleCustomDateBarang(this.value)">
                                    <option value="today" <?= $periode === 'today' ? 'selected' : '' ?>>Hari Ini</option>
                                    <option value="this_month" <?= $periode === 'this_month' ? 'selected' : '' ?>>Bulan Ini</option>
                                    <option value="this_year" <?= $periode === 'this_year' ? 'selected' : '' ?>>Tahun Ini</option>
                                    <option value="custom" <?= $periode === 'custom' ? 'selected' : '' ?>>Custom</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 custom-date-range-barang" style="<?= $periode !== 'custom' ? 'display:none;' : '' ?>">
                                <div class="input-group">
                                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                                    <span class="input-group-text">-</span>
                                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                                </div>
                            </div>
                            <div class="col-6 col-md-1">
                                <select name="per_page" class="form-select" onchange="this.form.submit()">
                                    <?php foreach ($perPageOptions as $option): ?>
                                    <option value="<?= $option ?>" <?= $perPage == $option ? 'selected' : '' ?>><?= $option ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-filter btn-primary w-100">Filter</button>
                            </div>
                            <div class="col-6 col-md-1 d-flex align-items-end">
                                <a href="/laporan/barang-tidak-terjual" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                            </div>
                            <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sortBy) ?>">
                            <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sortOrder) ?>">
                        </div>
                    </form>

                    <script>
                    // Define function immediately to ensure it's available
                    window.toggleCustomDateBarang = function(value) {
                        const customDateRange = document.querySelector('.custom-date-range-barang');
                        const form = document.querySelector('form');
                        
                        if (value === 'custom') {
                            if (customDateRange) customDateRange.style.display = 'block';
                        } else {
                            if (customDateRange) customDateRange.style.display = 'none';
                            // Reset date inputs when not custom
                            const startInput = document.querySelector('input[name="start_date"]');
                            const endInput = document.querySelector('input[name="end_date"]');
                            if (startInput) startInput.value = '';
                            if (endInput) endInput.value = '';
                            
                            // Auto submit if not custom
                            if (form) form.submit();
                        }
                    }
                    </script>

                    <?php
                    // Get current page from URL (always use $_GET to ensure it's current)
                    // This is critical for pagination to work correctly
                    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : (isset($page) ? (int)$page : 1);
                    if ($currentPage < 1) {
                        $currentPage = 1;
                    }
                    // Use currentPage for all calculations
                    $page = $currentPage;
                    $perPage = isset($perPage) && is_numeric($perPage) ? max(1, (int)$perPage) : 50;
                    $totalPages = isset($totalPages) && is_numeric($totalPages) ? max(1, (int)$totalPages) : 1;
                    ?>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th class="th-sortable">
                                        <a href="<?= getSortUrlBarangTidakTerjual('namabarang', $sortBy, $sortOrder, $search, $perPage, $periode, $startDate, $endDate) ?>">
                                            Nama Barang
                                        </a>
                                    </th>
                                    <th class="text-center">Satuan</th>
                                    <th class="th-sortable">
                                        <a href="<?= getSortUrlBarangTidakTerjual('namapabrik', $sortBy, $sortOrder, $search, $perPage, $periode, $startDate, $endDate) ?>">
                                            Pabrik
                                        </a>
                                    </th>
                                    <th class="text-end">Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reportData)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Tidak ada data barang tidak terjual</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $no = ($page - 1) * $perPage + 1;
                                foreach ($reportData as $row): 
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['namabarang'] ?? '-') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['satuan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['namapabrik'] ?? '-') ?></td>
                                    <td class="text-end"><?= number_format((float)($row['stokakhir'] ?? 0), 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <?php
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
                                $prevPage = (int)max(1, (int)$page - 1);
                                if ($prevPage < 1) $prevPage = 1;
                                ?>
                                <a class="page-link" href="<?= buildBarangTidakTerjualQuery($prevPage, $perPage, $search, $periode, $startDate, $endDate, $sortBy, $sortOrder) ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="' . buildBarangTidakTerjualQuery(1, $perPage, $search, $periode, $startDate, $endDate, $sortBy, $sortOrder) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                $active = $page == $i ? 'active' : '';
                                echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . buildBarangTidakTerjualQuery($i, $perPage, $search, $periode, $startDate, $endDate, $sortBy, $sortOrder) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="' . buildBarangTidakTerjualQuery($totalPages, $perPage, $search, $periode, $startDate, $endDate, $sortBy, $sortOrder) . '">' . $totalPages . '</a></li>';
                            }
                            ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <?php
                                $nextPage = $page + 1;
                                if ($nextPage > $totalPages) {
                                    $nextPage = $totalPages;
                                }
                                $nextPage = (int)$nextPage;
                                ?>
                                <a class="page-link" href="<?= buildBarangTidakTerjualQuery($nextPage, $perPage, $search, $periode, $startDate, $endDate, $sortBy, $sortOrder) ?>">Next</a>
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
function buildBarangTidakTerjualQuery($page, $perPage, $search, $periode, $startDate, $endDate, $sortBy = 'namabarang', $sortOrder = 'ASC') {
    $params = [
        'page' => max($page, 1),
        'per_page' => $perPage,
        'search' => $search,
        'periode' => $periode,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'sort_by' => $sortBy,
        'sort_order' => $sortOrder
    ];
    return '/laporan/barang-tidak-terjual?' . http_build_query($params);
}
?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

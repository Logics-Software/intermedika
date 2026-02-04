<?php
$title = 'Laporan Customer Non Aktif';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

require __DIR__ . '/../layouts/header.php';

// Helper function to get sort icon
if (!function_exists('getSortIconCustomerNonAktif')) {
    function getSortIconCustomerNonAktif($column, $currentSortBy, $currentSortOrder) {
        $config = require __DIR__ . '/../../config/app.php';
        $baseUrl = rtrim($config['base_url'], '/');
        
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
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Customer Non Aktif</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 me-auto">Laporan Customer Non Aktif</h4>
                        <div class="d-flex gap-2">
                            <?php
                            $exportParams = [];
                            if (!empty($search)) $exportParams['search'] = $search;
                            if (!empty($kodesales)) $exportParams['kodesales'] = $kodesales;
                            if (!empty($month)) $exportParams['month'] = $month;
                            if (!empty($year)) $exportParams['year'] = $year;
                            $exportQuery = http_build_query($exportParams);
                            ?>
                            <a href="/laporan/customer-non-aktif?export=excel<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-success btn-sm">
                                <?= icon('file-excel', 'mb-1 me-2', 16) ?>
                                <span class="d-none d-md-inline">Export Excel</span>
                                <span class="d-inline d-md-none">Excel</span>
                            </a>
                            <a href="/laporan/customer-non-aktif?export=pdf<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-danger btn-sm">
                                <?= icon('file-pdf', 'mb-1 me-2', 16) ?>
                                <span class="d-none d-md-inline">Download PDF</span>
                                <span class="d-inline d-md-none">PDF</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="/laporan/customer-non-aktif" class="mb-3">
                        <div class="row g-2 search-filter-card">
                            <div class="col-12 col-md-3">
                                <input type="text" class="form-control" name="search" placeholder="Cari Nama Customer/Sales..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                            
                            <div class="col-6 col-md-2">
                                <select name="kodesales" class="form-select" <?= (isset($isSales) && $isSales) ? 'disabled' : '' ?>>
                                    <?php if (!isset($isSales) || !$isSales): ?>
                                    <option value="">Semua Sales</option>
                                    <?php endif; ?>
                                    <?php foreach ($salesList as $sales): ?>
                                    <option value="<?= htmlspecialchars($sales['kodesales']) ?>" <?= $kodesales == $sales['kodesales'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sales['namasales']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($isSales) && $isSales): ?>
                                <input type="hidden" name="kodesales" value="<?= htmlspecialchars($kodesales) ?>">
                                <?php endif; ?>
                            </div>

                            <div class="col-6 col-md-2">
                                <select name="month" class="form-select">
                                    <?php 
                                    $months = [
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                    ];
                                    foreach ($months as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= $month == $k ? 'selected' : '' ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-6 col-md-2">
                                <select name="year" class="form-select">
                                    <?php 
                                    $currentYear = date('Y');
                                    for ($y = $currentYear; $y >= $currentYear - 5; $y--): ?>
                                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-6 col-md-1">
                                <select name="per_page" class="form-select" onchange="this.form.submit()">
                                    <?php 
                                    if (!isset($perPageOptions)) $perPageOptions = [10, 25, 50, 100, 200, 500, 1000];
                                    foreach ($perPageOptions as $option): 
                                    ?>
                                    <option value="<?= $option ?>" <?= $perPage == $option ? 'selected' : '' ?>><?= $option ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-filter btn-primary w-100">Filter</button>
                            </div>
                            <div class="col-6 col-md-1 d-flex align-items-end">
                                <a href="/laporan/customer-non-aktif" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle customer-report-table">
                            <thead class="table-light">
                                <tr>
                                    <th rowspan="2" class="align-middle text-center">Nama Customer</th>
                                    <?php foreach ($monthHeaders as $mHeader): ?>
                                    <th class="text-center"><?= $mHeader['name'] ?></th>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <?php foreach ($monthHeaders as $mHeader): ?>
                                    <th class="text-center"><?= $mHeader['year'] ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reportData)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Tidak ada data customer non aktif</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($reportData as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['namacustomer'] ?? '-') ?></td>
                                        <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <td class="text-end"><?= number_format((float)($row['month' . $i] ?? 0), 0, ',', '.') ?></td>
                                        <?php endfor; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <!-- Total Row -->
                                    <tr class="fw-bold table-active">
                                        <td class="text-end">TOTAL</td>
                                        <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <td class="text-end"><?= number_format((float)($totals['month' . $i] ?? 0), 0, ',', '.') ?></td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <?php
                    // Ensure variables are set
                    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : (isset($page) ? (int)$page : 1);
                    if ($currentPage < 1) $currentPage = 1;
                    $page = $currentPage;
                    
                    $totalRecords = isset($total) ? (int)$total : 0;
                    $perPage = isset($perPage) ? (int)$perPage : 50;
                    if ($perPage < 1) $perPage = 50;
                    
                    $totalPages = ceil($totalRecords / $perPage);
                    if ($totalPages < 1) $totalPages = 1;

                    $maxLinks = 3;
                    $half = (int)floor($maxLinks / 2);
                    $start = max(1, $page - $half);
                    $end = min($totalPages, $start + $maxLinks - 1);
                    if ($end - $start + 1 < $maxLinks) {
                        $start = max(1, $end - $maxLinks + 1);
                    }
                    ?>
                    <nav aria-label="Page navigation" class="mt-3">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <?php
                                $prevPage = (int)max(1, (int)$page - 1);
                                if ($prevPage < 1) $prevPage = 1;
                                ?>
                                <a class="page-link" href="<?= buildCustomerNonAktifQuery($prevPage, $perPage, $search, $kodesales, $month, $year) ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="' . buildCustomerNonAktifQuery(1, $perPage, $search, $kodesales, $month, $year) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                $active = $page == $i ? 'active' : '';
                                echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . buildCustomerNonAktifQuery($i, $perPage, $search, $kodesales, $month, $year) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="' . buildCustomerNonAktifQuery($totalPages, $perPage, $search, $kodesales, $month, $year) . '">' . $totalPages . '</a></li>';
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
                                <a class="page-link" href="<?= buildCustomerNonAktifQuery($nextPage, $perPage, $search, $kodesales, $month, $year) ?>">Next</a>
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
function buildCustomerNonAktifQuery($page, $perPage, $search, $kodesales, $month, $year) {
    $params = [
        'page' => max($page, 1),
        'per_page' => $perPage,
        'search' => $search,
        'kodesales' => $kodesales,
        'month' => $month,
        'year' => $year
    ];
    return '/laporan/customer-non-aktif?' . http_build_query($params);
}
?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>

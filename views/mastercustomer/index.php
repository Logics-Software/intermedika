<?php
$title = 'Master Customer';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Helper function to generate sort URL
if (!function_exists('getSortUrl')) {
    function getSortUrl($column, $currentSortBy, $currentSortOrder, $search, $perPage, $status, $statuspkp) {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = http_build_query([
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'sort_by' => $column,
            'sort_order' => $newSortOrder,
            'status' => $status,
            'statuspkp' => $statuspkp
        ]);
        return '/mastercustomer?' . $params;
    }
}

// Helper function to get sort icon
if (!function_exists('getSortIcon')) {
    function getSortIcon($column, $currentSortBy, $currentSortOrder) {
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
                    <li class="breadcrumb-item active">Customer</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar Customer</h4>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <form method="GET" action="/mastercustomer" id="searchForm">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-4 col-lg-4">
                                <input type="text" class="form-control" name="search" placeholder="Cari nama customer, alamat, kota, atau nama WP..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-6 col-md-2 col-lg-2">
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <?php $normalizedStatus = strtolower($status ?? ''); ?>
                                    <option value="" <?= $normalizedStatus === '' ? 'selected' : '' ?>>Semua Status</option>
                                    <option value="baru" <?= $normalizedStatus === 'baru' ? 'selected' : '' ?>>Baru</option>
                                    <option value="updated" <?= $normalizedStatus === 'updated' ? 'selected' : '' ?>>Updated</option>
                                    <option value="aktif" <?= $normalizedStatus === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= $normalizedStatus === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2 col-lg-2">
                                <select name="statuspkp" class="form-select" onchange="this.form.submit()">
                                    <?php $normalizedStatusPkp = strtolower($statuspkp ?? ''); ?>
                                    <option value="" <?= $normalizedStatusPkp === '' ? 'selected' : '' ?>>Semua PKP</option>
                                    <option value="pkp" <?= $normalizedStatusPkp === 'pkp' ? 'selected' : '' ?>>PKP</option>
                                    <option value="nonpkp" <?= $normalizedStatusPkp === 'nonpkp' ? 'selected' : '' ?>>Non PKP</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2 col-lg-2">
                                <select name="per_page" class="form-select" onchange="this.form.submit()">
                                    <?php foreach ([10, 25, 50, 100, 200, 500, 1000] as $pp): ?>
                                    <option value="<?= $pp ?>" <?= $perPage == $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-1 col-lg-1">
                                <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                            </div>
                            <div class="col-6 col-md-1 col-lg-1">
                                <a href="/mastercustomer?page=1&per_page=10&sort_by=<?= htmlspecialchars($sortBy) ?>&sort_order=<?= htmlspecialchars($sortOrder) ?>&status=&statuspkp=" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                            </div>
                        </div>
                            <input type="hidden" name="page" value="1">
                            <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sortBy) ?>">
                            <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sortOrder) ?>">
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="th-sortable">
                                        <a href="<?= getSortUrl('kodecustomer', $sortBy, $sortOrder, $search, $perPage, $status, $statuspkp) ?>">
                                            Kode
                                        </a>
                                    </th>
                                    <th class="th-sortable">
                                        <a href="<?= getSortUrl('namacustomer', $sortBy, $sortOrder, $search, $perPage, $status, $statuspkp) ?>">
                                            Nama Customer
                                        </a>
                                    </th>
                                    <th>Alamat Customer</th>
                                    <th class="th-sortable">
                                        <a href="<?= getSortUrl('kotacustomer', $sortBy, $sortOrder, $search, $perPage, $status, $statuspkp) ?>">
                                            Kota
                                        </a>
                                    </th>
                                    <th>No Telepon</th>
                                    <th>PKP</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($customers)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($customers as $customer): ?>
                                <?php
                                    $statusValue = strtolower($customer['status'] ?? 'baru');
                                    switch ($statusValue) {
                                        case 'updated':
                                            $statusBadgeClass = 'bg-info text-dark';
                                            break;
                                        case 'aktif':
                                            $statusBadgeClass = 'bg-success';
                                            break;
                                        case 'nonaktif':
                                            $statusBadgeClass = 'bg-secondary';
                                            break;
                                        case 'baru':
                                        default:
                                            $statusBadgeClass = 'bg-primary';
                                            break;
                                    }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($customer['kodecustomer']) ?></td>
                                    <td><?= htmlspecialchars($customer['namacustomer'].', '.$customer['namabadanusaha']) ?></td>
                                    <td><?= htmlspecialchars($customer['alamatcustomer'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($customer['kotacustomer'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($customer['notelepon'] ?? '-') ?></td>
                                    <td align="center">
                                        <?php $statusPkpValue = strtolower($customer['statuspkp'] ?? 'pkp'); ?>
                                        <span class="badge <?= $statusPkpValue === 'pkp' ? 'bg-primary' : 'bg-secondary' ?>">
                                            <?= $statusPkpValue === 'pkp' ? 'PKP' : 'Non PKP' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/mastercustomer/edit/<?= $customer['id'] ?>" class="btn btn-sm btn-warning"><?= icon('update', 'me-0 mb-1', 14) ?></a>
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
                    
                    // Build link function for pagination
                    $buildLink = function ($p) use ($perPage, $search, $status, $statuspkp, $sortBy, $sortOrder) {
                        return '?page=' . $p
                            . '&per_page=' . $perPage
                            . '&search=' . urlencode($search)
                            . '&status=' . urlencode($status)
                            . '&statuspkp=' . urlencode($statuspkp)
                            . '&sort_by=' . $sortBy
                            . '&sort_order=' . $sortOrder;
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
                                <a class="page-link" href="/mastercustomer<?php echo $buildLink($prevPage); ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="/mastercustomer' . $buildLink(1) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/mastercustomer' . $buildLink($i) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="/mastercustomer' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
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
                                <a class="page-link" href="/mastercustomer<?php echo $buildLink($nextPage); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


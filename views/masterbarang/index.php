<?php
$title = 'Master Barang';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

$currentUser = Auth::user();

if (!function_exists('getSortUrlMasterbarang')) {
    function getSortUrlMasterbarang($column, $currentSortBy, $currentSortOrder, $search, $perPage, $filterPabrik, $filterGolongan, $filterSupplier, $filterStatus) {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = http_build_query([
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'kodepabrik' => $filterPabrik,
            'kodegolongan' => $filterGolongan,
            'kodesupplier' => $filterSupplier,
            'status' => $filterStatus,
            'sort_by' => $column,
            'sort_order' => $newSortOrder
        ]);
        return '/masterbarang?' . $params;
    }
}

if (!function_exists('getSortIconMasterbarang')) {
    function getSortIconMasterbarang($column, $currentSortBy, $currentSortOrder) {
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
        }

        $iconPath = $baseUrl . '/assets/icons/arrow-down.svg';
        return '<img src="' . htmlspecialchars($iconPath) . '" alt="sort-down" class="sort-icon icon-inline" width="14" height="14">';
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
                    <li class="breadcrumb-item active">Master Barang</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar Barang</h4>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <form method="GET" action="/masterbarang" id="searchForm">
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-2">
                                    <input type="text" class="form-control" name="search" placeholder="Cari nama barang atau kandungan..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-5 col-md-3 col-lg-1">
                                    <select name="status" class="form-select" onchange="this.form.submit()">
                                        <option value="" <?= $filterStatus === '' ? 'selected' : '' ?>>Semua</option>
                                        <option value="aktif" <?= $filterStatus === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="nonaktif" <?= $filterStatus === 'nonaktif' ? 'selected' : '' ?>>Non Aktif</option>
                                    </select>
                                </div>
                                <div class="col-7 col-md-5 col-lg-3">
                                    <select name="kodepabrik" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Pabrik</option>
                                        <?php foreach ($pabriks as $pabrik): ?>
                                        <option value="<?= htmlspecialchars($pabrik['kodepabrik']) ?>" <?= $filterPabrik === $pabrik['kodepabrik'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($pabrik['namapabrik']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-7 col-md-5 col-lg-3">
                                    <select name="kodegolongan" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Golongan</option>
                                        <?php foreach ($golongans as $golongan): ?>
                                        <option value="<?= htmlspecialchars($golongan['kodegolongan']) ?>" <?= $filterGolongan === $golongan['kodegolongan'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($golongan['namagolongan']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-5 col-md-3 col-lg-1">
                                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ([10, 25, 50, 100, 200, 500, 1000] as $pp): ?>
                                        <option value="<?= $pp ?>" <?= $perPage == $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6 col-md-1">
                                    <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                                </div>
                                <div class="col-6 col-md-1">
                                    <a href="/masterbarang?page=1&per_page=<?= $perPage ?>&search=<?= urlencode($search) ?>&kodepabrik=<?= urlencode($filterPabrik) ?>&kodegolongan=<?= urlencode($filterGolongan) ?>&kodesupplier=<?= urlencode($filterSupplier) ?>&status=<?= urlencode($filterStatus) ?>&sort_by=<?= $sortBy ?>&sort_order=<?= $sortOrder ?>" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
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
                                        <a href="<?= getSortUrlMasterbarang('kodebarang', $sortBy, $sortOrder, $search, $perPage, $filterPabrik, $filterGolongan, $filterSupplier, $filterStatus) ?>">
                                            Kode
                                        </a>
                                    </th>
                                    <th class="th-sortable">
                                        <a href="<?= getSortUrlMasterbarang('namabarang', $sortBy, $sortOrder, $search, $perPage, $filterPabrik, $filterGolongan, $filterSupplier, $filterStatus) ?>">
                                            Nama Barang
                                        </a>
                                    </th>
                                    <th>Satuan</th>
                                    <th class="th-sortable">
                                        <a href="<?= getSortUrlMasterbarang('namapabrik', $sortBy, $sortOrder, $search, $perPage, $filterPabrik, $filterGolongan, $filterSupplier, $filterStatus) ?>">
                                            Pabrik
                                        </a>
                                    </th>
                                    <th class="th-sortable">
                                        <a href="<?= getSortUrlMasterbarang('namagolongan', $sortBy, $sortOrder, $search, $perPage, $filterPabrik, $filterGolongan, $filterSupplier, $filterStatus) ?>">
                                            Golongan
                                        </a>
                                    </th>
                                    <th>Harga Jual</th>
                                    <th>Stok</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="12" class="text-center">Tidak ada data</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['kodebarang']) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($item['namabarang']) ?></td>
                                    <td><?= htmlspecialchars($item['satuan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($item['namapabrik'] ?? $item['kodepabrik'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($item['namagolongan'] ?? $item['kodegolongan'] ?? '-') ?></td>
                                    <td align="right"><?= is_null($item['hargajual']) ? '-' : number_format((float)$item['hargajual'], 0, ',', '.') ?></td>
                                    <td align="right"><?= is_null($item['stokakhir']) ? '-' : number_format((float)$item['stokakhir'], 0, ',', '.') ?></td>
                                    <td align="center">
                                        <span class="badge bg-<?= ($item['status'] ?? 'aktif') === 'aktif' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($item['status'] ?? 'aktif') ?>
                                        </span>
                                    </td>
                                    <td align="center">
                                        <a href="/masterbarang/view/<?= $item['id'] ?>" class="btn btn-sm btn-info text-white"><?= icon('show', 'me-0 mb-1', 14) ?></a>
                                        <!-- <?php if ($currentUser && in_array($currentUser['role'], ['admin', 'manajemen', 'operator'])): ?>
                                        <a href="/masterbarang/edit/<?= $item['id'] ?>" class="btn btn-sm btn-warning"><?= icon('update', 'me-0 mb-1', 14) ?></a>
                                        <?php endif; ?> -->
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
                    $buildLink = function ($p) use ($perPage, $search, $filterPabrik, $filterGolongan, $filterSupplier, $filterStatus, $sortBy, $sortOrder) {
                        return '?page=' . $p
                            . '&per_page=' . $perPage
                            . '&search=' . urlencode($search)
                            . '&kodepabrik=' . urlencode($filterPabrik)
                            . '&kodegolongan=' . urlencode($filterGolongan)
                            . '&kodesupplier=' . urlencode($filterSupplier)
                            . '&status=' . urlencode($filterStatus)
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
                                <a class="page-link" href="/masterbarang<?php echo $buildLink($prevPage); ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="/masterbarang' . $buildLink(1) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/masterbarang' . $buildLink($i) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="/masterbarang' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
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
                                <a class="page-link" href="/masterbarang<?php echo $buildLink($nextPage); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>



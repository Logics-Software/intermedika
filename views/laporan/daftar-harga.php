<?php
$title = 'Laporan Daftar Harga';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Load sticky column CSS dan JS
$additionalStyles = [
    $baseUrl . '/assets/css/sticky-column.css'
];
$additionalScripts = [
    $baseUrl . '/assets/js/sticky-column.js'
];

if (!function_exists('getSortUrlLaporanHarga')) {
    function getSortUrlLaporanHarga($column, $currentSortBy, $currentSortOrder, $search, $perPage, $kodepabrik, $kodegolongan, $kondisiStok) {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = http_build_query([
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'kodepabrik' => $kodepabrik,
            'kodegolongan' => $kodegolongan,
            'kondisi_stok' => $kondisiStok,
            'sort_by' => $column,
            'sort_order' => $newSortOrder
        ]);
        return '/laporan/daftar-harga?' . $params;
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
                    <li class="breadcrumb-item active">Daftar Harga</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 me-auto">Daftar Harga</h4>
                        <div class="d-flex gap-2">
                            <?php
                            $exportParams = [];
                            if (!empty($search)) $exportParams['search'] = $search;
                            if (!empty($kodepabrik)) $exportParams['kodepabrik'] = $kodepabrik;
                            if (!empty($kodegolongan)) $exportParams['kodegolongan'] = $kodegolongan;
                            if (!empty($kondisiStok) && $kondisiStok !== 'semua') $exportParams['kondisi_stok'] = $kondisiStok;
                            if (!empty($sortBy)) $exportParams['sort_by'] = $sortBy;
                            if (!empty($sortOrder)) $exportParams['sort_order'] = $sortOrder;
                            $exportQuery = http_build_query($exportParams);
                            ?>
                            <a href="/laporan/daftar-harga?export=excel<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-success btn-sm">
                                <?= icon('file-excel', 'mb-1 me-2', 16) ?>
                                <span class="d-none d-md-inline">Export Excel</span>
                                <span class="d-inline d-md-none">Excel</span>
                            </a>
                            <a href="/laporan/daftar-harga?export=pdf<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-danger btn-sm">
                                <?= icon('file-pdf', 'mb-1 me-2', 16) ?>
                                <span class="d-none d-md-inline">Download PDF</span>
                                <span class="d-inline d-md-none">PDF</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="/laporan/daftar-harga" class="mb-3">
                        <div class="row g-2 align-items-end search-filter-card">
                            <div class="col-12 col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Cari nama barang atau kandungan..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-6 col-md-2">
                                <select name="kodepabrik" class="form-select" onchange="this.form.submit()">
                                    <option value="">Pabrik</option>
                                    <?php foreach ($pabriks as $pabrik): ?>
                                    <option value="<?= htmlspecialchars($pabrik['kodepabrik']) ?>" <?= $kodepabrik === $pabrik['kodepabrik'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($pabrik['namapabrik']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <select name="kodegolongan" class="form-select" onchange="this.form.submit()">
                                    <option value="">Golongan</option>
                                    <?php foreach ($golongans as $golongan): ?>
                                    <option value="<?= htmlspecialchars($golongan['kodegolongan']) ?>" <?= $kodegolongan === $golongan['kodegolongan'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($golongan['namagolongan']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <select name="kondisi_stok" class="form-select" onchange="this.form.submit()">
                                    <option value="semua" <?= ($kondisiStok ?? 'semua') === 'semua' ? 'selected' : '' ?>>Stok</option>
                                    <option value="ada" <?= ($kondisiStok ?? '') === 'ada' ? 'selected' : '' ?>>Stok > 0</option>
                                    <option value="kosong" <?= ($kondisiStok ?? '') === 'kosong' ? 'selected' : '' ?>>Stok = 0</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-1">
                                <button type="submit" class="btn btn-filter btn-primary w-100">Cari</button>
                            </div>
                            <div class="col-6 col-md-1">
                                <a href="/laporan/daftar-harga" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <small class="text-muted">Total: <strong><?= number_format($total) ?></strong> barang</small>
                        </div>
                        <div>
                            <?php
                            $queryParams = [];
                            if (!empty($search)) $queryParams['search'] = $search;
                            if (!empty($kodepabrik)) $queryParams['kodepabrik'] = $kodepabrik;
                            if (!empty($kodegolongan)) $queryParams['kodegolongan'] = $kodegolongan;
                            if (!empty($kondisiStok) && $kondisiStok !== 'semua') $queryParams['kondisi_stok'] = $kondisiStok;
                            if (!empty($sortBy)) $queryParams['sort_by'] = $sortBy;
                            if (!empty($sortOrder)) $queryParams['sort_order'] = $sortOrder;
                            $baseQueryForPerPage = http_build_query($queryParams);
                            ?>
                            <select name="per_page" class="form-select form-select-sm d-inline-block" style="width: 100px;" onchange="window.location.href='?per_page=' + this.value + '<?= !empty($baseQueryForPerPage) ? '&' . $baseQueryForPerPage : '' ?>'">
                                <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                                <option value="200" <?= $perPage == 200 ? 'selected' : '' ?>>200</option>
                                <option value="500" <?= $perPage == 500 ? 'selected' : '' ?>>500</option>
                                <option value="1000" <?= $perPage == 1000 ? 'selected' : '' ?>>1000</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive table-sticky-column hide-first-col" id="tableDaftarHarga">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th class="th-sortable sticky-col <?= ($sortBy ?? 'namabarang') === 'namabarang' ? (($sortOrder ?? 'ASC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
                                    <a href="<?= getSortUrlLaporanHarga('namabarang', $sortBy ?? 'namabarang', $sortOrder ?? 'ASC', $search ?? '', $perPage ?? 10, $kodepabrik ?? '', $kodegolongan ?? '', $kondisiStok ?? 'semua') ?>" class="text-decoration-none text-dark">
                                        Nama Barang
                                    </a>
                                </th>
                                <th>
                                    Satuan
                                </th>
                                <th class="th-sortable <?= ($sortBy ?? 'namabarang') === 'pabrik' ? (($sortOrder ?? 'ASC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
                                    <a href="<?= getSortUrlLaporanHarga('pabrik', $sortBy ?? 'namabarang', $sortOrder ?? 'ASC', $search ?? '', $perPage ?? 10, $kodepabrik ?? '', $kodegolongan ?? '', $kondisiStok ?? 'semua') ?>" class="text-decoration-none text-dark">
                                        Pabrik
                                    </a>
                                </th>
                                <th>
                                    Kondisi
                                </th>
                                <th>
                                    ED
                                </th>
                                    <th>
                                        Harga Jual
                                    </th>
                                    <th>
                                        Disc
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($barangs)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Tidak ada data barang</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $pageNum = isset($page) ? max((int)$page, 1) : 1;
                                $perPageNum = isset($perPage) ? max((int)$perPage, 1) : 10;
                                $no = ($pageNum - 1) * $perPageNum + 1;
                                foreach ($barangs as $barang): 
                                ?>
                                <tr>
                                    <td align="center"><?= $no++ ?></td>
                                    <td class="sticky-col"><?= htmlspecialchars($barang['namabarang'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($barang['satuan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($barang['pabrik'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($barang['kondisi'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($barang['ed'] ?? '-') ?></td>
                                    <td class="text-end"><?= number_format((float)($barang['hargajual'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format((float)($barang['discountjual'] ?? 0), 2, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                    <?php
                    // Ensure page is an integer from $_GET
                    $currentPage = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
                    if ($currentPage < 1) {
                        $currentPage = 1;
                    }
                    $page = $currentPage;
                    $totalPages = (int)$totalPages;
                    $perPage = (int)$perPage;
                    
                    // Build link function for pagination
                    $buildLink = function ($p) use ($perPage, $search, $kodepabrik, $kodegolongan, $kondisiStok, $sortBy, $sortOrder) {
                        return '?page=' . $p
                            . '&per_page=' . $perPage
                            . '&search=' . urlencode($search)
                            . '&kodepabrik=' . urlencode($kodepabrik)
                            . '&kodegolongan=' . urlencode($kodegolongan)
                            . '&kondisi_stok=' . urlencode($kondisiStok)
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
                                $prevPage = (int)max(1, $page - 1);
                                if ($prevPage < 1) $prevPage = 1;
                                ?>
                                <a class="page-link" href="/laporan/daftar-harga<?php echo $buildLink($prevPage); ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="/laporan/daftar-harga' . $buildLink(1) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/laporan/daftar-harga' . $buildLink($i) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="/laporan/daftar-harga' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
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
                                <a class="page-link" href="/laporan/daftar-harga<?php echo $buildLink($nextPage); ?>">Next</a>
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



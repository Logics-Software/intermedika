<?php
$title = 'Laporan Daftar Tagihan';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Load Choices.js for searchable dropdown and sticky column
$additionalStyles = [
    $baseUrl . '/assets/css/choices.min.css',
    $baseUrl . '/assets/css/sticky-column.css'
];
$additionalScripts = [
    $baseUrl . '/assets/js/choices.min.js',
    $baseUrl . '/assets/js/sticky-column.js'
];

if (!function_exists('getSortUrlTagihan')) {
    function getSortUrlTagihan($column, $currentSortBy, $currentSortOrder, $search, $perPage, $kodecustomer, $statusJatuhTempo) {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = http_build_query([
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'kodecustomer' => $kodecustomer,
            'status_jatuh_tempo' => $statusJatuhTempo,
            'sort_by' => $column,
            'sort_order' => $newSortOrder
        ]);
        return '/laporan/daftar-tagihan?' . $params;
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
                    <li class="breadcrumb-item active">Daftar Tagihan</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 me-auto">Daftar Tagihan</h4>
                        <div class="d-flex gap-2">
                            <?php
                            $exportParams = [];
                            if (!empty($search)) $exportParams['search'] = $search;
                            if (!empty($kodecustomer)) $exportParams['kodecustomer'] = $kodecustomer;
                            if (!empty($statusJatuhTempo) && $statusJatuhTempo !== 'semua') $exportParams['status_jatuh_tempo'] = $statusJatuhTempo;
                            if (!empty($sortBy)) $exportParams['sort_by'] = $sortBy;
                            if (!empty($sortOrder)) $exportParams['sort_order'] = $sortOrder;
                            $exportQuery = http_build_query($exportParams);
                            ?>
                            <a href="/laporan/daftar-tagihan?export=excel<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-success btn-sm">
                                <?= icon('file-excel', 'mb-1 me-2', 16) ?>
                                <span class="d-none d-md-inline">Export Excel</span>
                                <span class="d-inline d-md-none">Excel</span>
                            </a>
                            <a href="/laporan/daftar-tagihan?export=pdf<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-danger btn-sm">
                                <?= icon('file-pdf', 'mb-1 me-2', 16) ?>
                                <span class="d-none d-md-inline">Download PDF</span>
                                <span class="d-inline d-md-none">PDF</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="/laporan/daftar-tagihan" class="mb-3">
                        <div class="row g-2 align-items-end search-filter-card">
                            <div class="col-12 col-md-3">
                                <input type="text" class="form-control" name="search" placeholder="Cari nama customer..." value="<?= htmlspecialchars($search ?? '') ?>">
                            </div>
                            <div class="col-12 col-md-4">
                                <select name="kodecustomer" id="kodecustomerSelect" class="form-select js-choice-customer">
                                    <option value="">Semua Customer</option>
                                    <?php foreach ($customers as $customer): ?>
                                    <option value="<?= htmlspecialchars($customer['kodecustomer']) ?>" <?= ($kodecustomer ?? '') === $customer['kodecustomer'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($customer['namacustomer'] ?? '') ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-2">
                                <select name="status_jatuh_tempo" class="form-select" onchange="this.form.submit()">
                                    <option value="semua" <?= ($statusJatuhTempo ?? 'semua') === 'semua' ? 'selected' : '' ?>>Semua Status</option>
                                    <option value="sudah" <?= ($statusJatuhTempo ?? '') === 'sudah' ? 'selected' : '' ?>>Sudah Jatuh Tempo</option>
                                    <option value="belum" <?= ($statusJatuhTempo ?? '') === 'belum' ? 'selected' : '' ?>>Belum Jatuh Tempo</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-1">
                                <button type="submit" class="btn btn-filter btn-primary w-100">Cari</button>
                            </div>
                            <div class="col-6 col-md-1">
                                <a href="/laporan/daftar-tagihan" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                            </div>
                            <div class="col-6 col-md-1">
                                <!-- <div>
                                    <small class="text-muted">Total: <strong><?= number_format($total ?? 0) ?></strong> tagihan</small>
                                </div> -->
                                <div>
                                    <?php
                                    $queryParams = [];
                                    if (!empty($search)) $queryParams['search'] = $search;
                                    if (!empty($kodecustomer)) $queryParams['kodecustomer'] = $kodecustomer;
                                    if (!empty($statusJatuhTempo) && $statusJatuhTempo !== 'semua') $queryParams['status_jatuh_tempo'] = $statusJatuhTempo;
                                    if (!empty($sortBy)) $queryParams['sort_by'] = $sortBy;
                                    if (!empty($sortOrder)) $queryParams['sort_order'] = $sortOrder;
                                    $baseQueryForPerPage = http_build_query($queryParams);
                                    ?>
                                    <select name="per_page" class="form-select form-select-sm d-inline-block" style="width: 100px;" onchange="window.location.href='?per_page=' + this.value + '<?= !empty($baseQueryForPerPage) ? '&' . $baseQueryForPerPage : '' ?>'">
                                        <option value="10" <?= ($perPage ?? 10) == 10 ? 'selected' : '' ?>>10</option>
                                        <option value="25" <?= ($perPage ?? 10) == 25 ? 'selected' : '' ?>>25</option>
                                        <option value="50" <?= ($perPage ?? 10) == 50 ? 'selected' : '' ?>>50</option>
                                        <option value="100" <?= ($perPage ?? 10) == 100 ? 'selected' : '' ?>>100</option>
                                        <option value="200" <?= ($perPage ?? 10) == 200 ? 'selected' : '' ?>>200</option>
                                        <option value="500" <?= ($perPage ?? 10) == 500 ? 'selected' : '' ?>>500</option>
                                        <option value="1000" <?= ($perPage ?? 10) == 1000 ? 'selected' : '' ?>>1000</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>


                    <div class="table-responsive table-sticky-column hide-first-col">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No.</th>
                                    <th class="th-sortable sticky-col sticky-col-faktur <?= ($sortBy ?? 'tanggalpenjualan') === 'nopenjualan' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
                                        <a href="<?= getSortUrlTagihan('nopenjualan', $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC', $search ?? '', $perPage ?? 10, $kodecustomer ?? '', $statusJatuhTempo ?? 'semua') ?>" class="text-decoration-none text-dark">
                                            No.Faktur
                                        </a>
                                    </th>
                                    <th class="th-sortable text-center <?= ($sortBy ?? 'tanggalpenjualan') === 'tanggalpenjualan' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
                                        <a href="<?= getSortUrlTagihan('tanggalpenjualan', $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC', $search ?? '', $perPage ?? 10, $kodecustomer ?? '', $statusJatuhTempo ?? 'semua') ?>" class="text-decoration-none text-dark">
                                            Tanggal
                                        </a>
                                    </th>
                                    <th>Jatuh Tempo</th>
                                    <th class="th-sortable text-center <?= ($sortBy ?? 'tanggalpenjualan') === 'umur' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
                                        <a href="<?= getSortUrlTagihan('umur', $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC', $search ?? '', $perPage ?? 10, $kodecustomer ?? '', $statusJatuhTempo ?? 'semua') ?>" class="text-decoration-none text-dark">
                                            Umur
                                        </a>
                                    </th>
                                    <th>Nilai Penjualan</th>
                                    <th>Saldo Tagihan</th>
                                    <th class="th-sortable <?= ($sortBy ?? 'tanggalpenjualan') === 'namacustomer' ? (($sortOrder ?? 'DESC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>" style="min-width: 250px;">
                                        <a href="<?= getSortUrlTagihan('namacustomer', $sortBy ?? 'tanggalpenjualan', $sortOrder ?? 'DESC', $search ?? '', $perPage ?? 10, $kodecustomer ?? '', $statusJatuhTempo ?? 'semua') ?>" class="text-decoration-none text-dark">
                                            Customer
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tagihans)): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">Tidak ada data tagihan</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $pageNum = isset($page) ? max((int)$page, 1) : 1;
                                $perPageNum = isset($perPage) ? max((int)$perPage, 1) : 10;
                                $no = ($pageNum - 1) * $perPageNum + 1;
                                $tanggalSistem = new DateTime();
                                foreach ($tagihans as $tagihan): 
                                    // Hitung umur (hari)
                                    $umur = '-';
                                    if (!empty($tagihan['tanggalpenjualan'])) {
                                        try {
                                            $tanggalPenjualan = new DateTime($tagihan['tanggalpenjualan']);
                                            $diff = $tanggalSistem->diff($tanggalPenjualan);
                                            $umur = $diff->days;
                                        } catch (Exception $e) {
                                            $umur = '-';
                                        }
                                    }
                                    
                                    // Format customer dengan namabadanusaha
                                    $customerDisplay = $tagihan['namacustomer'] ?? '';
                                    if ($customerDisplay && !empty($tagihan['namabadanusaha'])) {
                                        $customerDisplay .= ', ' . $tagihan['namabadanusaha'];
                                    }
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="fw-semibold sticky-col"><?= htmlspecialchars($tagihan['nopenjualan'] ?? '-') ?></td>
                                    <td align="center"><?= $tagihan['tanggalpenjualan'] ? date('d/m/Y', strtotime($tagihan['tanggalpenjualan'])) : '-' ?></td>
                                    <td align="center"><?= $tagihan['tanggaljatuhtempo'] ? date('d/m/Y', strtotime($tagihan['tanggaljatuhtempo'])) : '-' ?></td>
                                    <td class="text-center"><?= $umur ?></td>
                                    <td class="text-end"><?= number_format((float)($tagihan['nilaipenjualan'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format((float)($tagihan['saldopenjualan'] ?? 0), 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($customerDisplay ?: '-') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (!empty($tagihans)): ?>
                                <tr class="table-info fw-bold">
                                    <td class="text-center"></td>
                                    <td class="text-center sticky-col">TOTAL</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-end"><?= number_format($totals['nilaipenjualan'] ?? 0, 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format($totals['saldopenjualan'] ?? 0, 0, ',', '.') ?></td>
                                    <td></td>
                                </tr>
                                <?php endif; ?>
                                <?php endif; ?>
                            </tbody>
                            <?php 
                            $currentPage = (int)($page ?? 1);
                            $lastPage = (int)($totalPages ?? 1);
                            if ($currentPage == $lastPage && !empty($tagihans) && isset($grandTotals)): 
                            ?>
                            <tfoot>
                                <tr class="table-warning fw-bold">
                                    <td class="text-center"></td>
                                    <td class="text-center sticky-col">GRAND TOTAL</td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                    <td class="hide-alamat-mobile text-center"></td>
                                    <td class="text-end"><?= number_format($grandTotals['nilaipenjualan'] ?? 0, 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format($grandTotals['saldopenjualan'] ?? 0, 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <?php if (($totalPages ?? 1) > 1): ?>
                    <?php
                    // Ensure page is an integer from $_GET
                    $currentPage = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
                    if ($currentPage < 1) {
                        $currentPage = 1;
                    }
                    $page = $currentPage;
                    $totalPages = (int)($totalPages ?? 1);
                    $perPage = (int)($perPage ?? 10);
                    
                    // Build link function for pagination
                    $buildLink = function ($p) use ($perPage, $search, $kodecustomer, $statusJatuhTempo, $sortBy, $sortOrder) {
                        return '?page=' . $p
                            . '&per_page=' . $perPage
                            . '&search=' . urlencode($search)
                            . '&kodecustomer=' . urlencode($kodecustomer)
                            . '&status_jatuh_tempo=' . urlencode($statusJatuhTempo)
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
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <?php
                                $prevPage = (int)max(1, $page - 1);
                                if ($prevPage < 1) $prevPage = 1;
                                ?>
                                <a class="page-link" href="/laporan/daftar-tagihan<?php echo $buildLink($prevPage); ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="/laporan/daftar-tagihan' . $buildLink(1) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/laporan/daftar-tagihan' . $buildLink($i) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="/laporan/daftar-tagihan' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
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
                                <a class="page-link" href="/laporan/daftar-tagihan<?php echo $buildLink($nextPage); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Choices.js for customer dropdown
    const customerSelect = document.getElementById('kodecustomerSelect');
    if (customerSelect && typeof Choices !== 'undefined') {
        new Choices(customerSelect, {
            searchEnabled: true,
            searchResultLimit: 100,
            searchPlaceholderValue: 'Ketik untuk mencari customer...',
            shouldSort: false,
            itemSelectText: '',
            noResultsText: 'Customer tidak ditemukan'
        });
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


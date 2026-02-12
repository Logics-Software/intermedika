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
                                    <th style="width: 5%;">No</th>
                                    <th class="th-sortable <?= ($sortBy ?? 'namabarang') === 'namabarang' ? (($sortOrder ?? 'ASC') === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>">
                                        <a href="<?= getSortUrlLaporanHarga('namabarang', $sortBy ?? 'namabarang', $sortOrder ?? 'ASC', $search ?? '', $perPage ?? 10, $kodepabrik ?? '', $kodegolongan ?? '', $kondisiStok ?? 'semua') ?>" class="text-decoration-none text-dark">
                                            Nama Barang
                                        </a>
                                    </th>
                                    <th class="text-end" style="width: 15%;">
                                        Stok
                                    </th>
                                    <th class="th-sortable text-end" style="width: 20%;">
                                        <a href="<?= getSortUrlLaporanHarga('hargajual', $sortBy ?? 'namabarang', $sortOrder ?? 'ASC', $search ?? '', $perPage ?? 10, $kodepabrik ?? '', $kodegolongan ?? '', $kondisiStok ?? 'semua') ?>" class="text-decoration-none text-dark">
                                            Harga
                                        </a>
                                    </th>
                                    <th class="text-center" style="width: 15%;">
                                        Satuan
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($barangs)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Tidak ada data barang</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $pageNum = isset($page) ? max((int)$page, 1) : 1;
                                $perPageNum = isset($perPage) ? max((int)$perPage, 1) : 10;
                                $no = ($pageNum - 1) * $perPageNum + 1;
                                foreach ($barangs as $barang): 
                                ?>
                                <tr role="button" data-bs-toggle="modal" data-bs-target="#detailModal"
                                    data-namabarang="<?= htmlspecialchars($barang['namabarang'] ?? '-') ?>"
                                    data-stok="<?= number_format((float)($barang['stok'] ?? 0), 0, ',', '.') ?>"
                                    data-hargajual="<?= number_format((float)($barang['hargajual'] ?? 0), 0, ',', '.') ?>"
                                    data-satuan="<?= htmlspecialchars($barang['satuan'] ?? '-') ?>"
                                    data-pabrik="<?= htmlspecialchars($barang['pabrik'] ?? '-') ?>"
                                    data-kondisi="<?= htmlspecialchars($barang['kondisi'] ?? '-') ?>"
                                    data-ed="<?= htmlspecialchars($barang['ed'] ?? '-') ?>"
                                    data-discount="<?= number_format((float)($barang['discountjual'] ?? 0), 2, ',', '.') ?>">
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($barang['namabarang'] ?? '-') ?></td>
                                    <td class="text-end"><?= number_format((float)($barang['stok'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format((float)($barang['hargajual'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($barang['satuan'] ?? '-') ?></td>
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

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="small text-muted d-block">Nama Barang</label>
                    <div class="fw-bold" id="modalNamaBarang"></div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="small text-muted d-block">Stok</label>
                        <div class="fw-bold" id="modalStok"></div>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted d-block">Satuan</label>
                        <div class="fw-bold" id="modalSatuan"></div>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted d-block">Harga Jual</label>
                        <div class="fw-bold" id="modalHarga"></div>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted d-block">Pabrik</label>
                        <div class="fw-bold" id="modalPabrik"></div>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted d-block">Kondisi</label>
                        <div class="fw-bold" id="modalKondisi"></div>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted d-block">ED</label>
                        <div class="fw-bold" id="modalEd"></div>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted d-block">Discount</label>
                        <div class="fw-bold" id="modalDiscount"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var detailModal = document.getElementById('detailModal');
        detailModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            
            var namabarang = button.getAttribute('data-namabarang');
            var stok = button.getAttribute('data-stok');
            var hargajual = button.getAttribute('data-hargajual');
            var satuan = button.getAttribute('data-satuan');
            var pabrik = button.getAttribute('data-pabrik');
            var kondisi = button.getAttribute('data-kondisi');
            var ed = button.getAttribute('data-ed');
            var discount = button.getAttribute('data-discount');
            
            detailModal.querySelector('#modalNamaBarang').textContent = namabarang;
            detailModal.querySelector('#modalStok').textContent = stok;
            detailModal.querySelector('#modalSatuan').textContent = satuan;
            detailModal.querySelector('#modalHarga').textContent = hargajual;
            detailModal.querySelector('#modalPabrik').textContent = pabrik;
            detailModal.querySelector('#modalKondisi').textContent = kondisi;
            detailModal.querySelector('#modalEd').textContent = ed;
            detailModal.querySelector('#modalDiscount').textContent = discount + '%';
        });
    });
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

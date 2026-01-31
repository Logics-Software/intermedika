<?php
$title = 'Laporan Omset';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Load sticky column CSS dan JS (only for non-sales roles)
// $userRole will be set by controller, but we need to check here for CSS/JS loading
$currentUser = Auth::user();
$currentUserRole = $currentUser['role'] ?? '';
if ($currentUserRole !== 'sales') {
    $additionalStyles = [
        $baseUrl . '/assets/css/sticky-column.css'
    ];
    $additionalScripts = [
        $baseUrl . '/assets/js/sticky-column.js'
    ];
}

require __DIR__ . '/../layouts/header.php';

$bulanNama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Laporan Omset</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 me-auto">Laporan Omset</h4>
                        <div class="d-flex gap-2">
                            <?php
                            $exportParams = [];
                            if (!empty($tahun)) $exportParams['tahun'] = $tahun;
                            if (!empty($bulan)) $exportParams['bulan'] = $bulan;
                            $exportQuery = http_build_query($exportParams);
                            ?>
                            <a href="/laporan/omset?export=excel<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-success btn-sm">
                                <?= icon('file-excel', 'mb-1 me-2', 16) ?>
                                <span class="d-none d-md-inline">Export Excel</span>
                                <span class="d-inline d-md-none">Excel</span>
                            </a>
                            <a href="/laporan/omset?export=pdf<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-danger btn-sm">
                                <?= icon('file-pdf', 'mb-1 me-2', 16) ?>
                                <span class="d-none d-md-inline">Download PDF</span>
                                <span class="d-inline d-md-none">PDF</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (($userRole ?? '') === 'sales'): ?>
                        <!-- Form View for Sales Role -->
                        <?php if (!empty($omsetData)): ?>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Data Omset</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="GET" action="/laporan/omset" class="mb-3">
                                            <div class="row g-2 align-items-end search-filter-card">
                                                <div class="col-6 col-md-3">
                                                    <select name="tahun" class="form-select" onchange="this.form.submit()">
                                                        <?php foreach ($years as $year): ?>
                                                        <option value="<?= htmlspecialchars($year) ?>" <?= $tahun == $year ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($year) ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <select name="bulan" class="form-select" onchange="this.form.submit()">
                                                        <option value="">Semua Bulan</option>
                                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                                        <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= $bulan == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                                                            <?= $bulanNama[$i] ?>
                                                        </option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                        
                                        <div class="row g-3">
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Tahun</div>
                                                <div class="fw-bold"><?= htmlspecialchars($omsetData['tahun'] ?? '-') ?></div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Bulan</div>
                                                <div class="fw-bold"><?= !empty($omsetData['bulan']) ? $bulanNama[(int)$omsetData['bulan']] : '-' ?></div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Kode Sales</div>
                                                <div class="fw-bold"><?= htmlspecialchars($omsetData['kodesales'] ?? '-') ?></div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Nama Sales</div>
                                                <div class="fw-bold"><?= htmlspecialchars($omsetData['namasales'] ?? '-') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0">Penjualan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Jumlah Faktur</div>
                                                <div class="fw-bold"><?= number_format((float)($omsetData['jumlahfaktur'] ?? 0), 0, ',', '.') ?></div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Penjualan</div>
                                                <div class="fw-bold"><?= number_format((float)($omsetData['penjualan'] ?? 0), 0, ',', '.') ?></div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Retur Penjualan</div>
                                                <div class="fw-bold"><?= number_format((float)($omsetData['returpenjualan'] ?? 0), 0, ',', '.') ?></div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Penjualan Bersih</div>
                                                <div class="fw-bold"><?= number_format((float)($omsetData['penjualanbersih'] ?? 0), 0, ',', '.') ?></div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Target Penjualan</div>
                                                <div class="fw-bold"><?= number_format((float)($omsetData['targetpenjualan'] ?? 0), 0, ',', '.') ?></div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Prosen Penjualan</div>
                                                <div class="fw-bold"><?= number_format((float)($omsetData['prosenpenjualan'] ?? 0), 2, ',', '.') ?>%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0">Penerimaan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Penerimaan Tunai</div>
                                                <div class="fw-bold"><?= number_format((float)($omsetData['penerimaantunai'] ?? 0), 0, ',', '.') ?></div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">CN Penjualan</div>
                                                <div class="fw-bold"><?= number_format((float)($omsetData['cnpenjualan'] ?? 0), 0, ',', '.') ?></div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Pencairan Giro</div>
                                                <div class="fw-bold"><?= number_format((float)($omsetData['pencairangiro'] ?? 0), 0, ',', '.') ?></div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Penerimaan Bersih</div>
                                                <div class="fw-bold"><?= number_format((float)($omsetData['penerimaanbersih'] ?? 0), 0, ',', '.') ?></div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Target Penerimaan</div>
                                                <div class="fw-bold"><?= number_format((float)($omsetData['targetpenerimaan'] ?? 0), 0, ',', '.') ?></div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Prosen Penerimaan</div>
                                                <div class="fw-bold"><?= number_format((float)($omsetData['prosenpenerimaan'] ?? 0), 2, ',', '.') ?>%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <p class="mb-0">Tidak ada data omset untuk periode yang dipilih.</p>
                        </div>
                        <form method="GET" action="/laporan/omset" class="mb-3">
                            <div class="row g-2 align-items-end search-filter-card">
                                <div class="col-6 col-md-3">
                                    <select name="tahun" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ($years as $year): ?>
                                        <option value="<?= htmlspecialchars($year) ?>" <?= $tahun == $year ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($year) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <select name="bulan" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Bulan</option>
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= $bulan == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                                            <?= $bulanNama[$i] ?>
                                        </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Table View for Other Roles -->
                        <form method="GET" action="/laporan/omset" class="mb-3">
                            <div class="row g-2 align-items-end search-filter-card">
                                <div class="col-6 col-md-3">
                                    <select name="tahun" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ($years as $year): ?>
                                        <option value="<?= htmlspecialchars($year) ?>" <?= $tahun == $year ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($year) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <select name="bulan" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Bulan</option>
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= $bulan == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                                            <?= $bulanNama[$i] ?>
                                        </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="row g-2">
                                        <div class="col-4 col-md-3">
                                            <select name="per_page" class="form-select" onchange="this.form.submit()">
                                                <?php foreach ([10, 25, 50, 100, 200, 500, 1000] as $option): ?>
                                                <option value="<?= $option ?>" <?= $perPage == $option ? 'selected' : '' ?>><?= $option ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-4 col-md-3">
                                            <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                                        </div>
                                        <div class="col-4 col-md-3">
                                            <a href="/laporan/omset" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive table-sticky-column hide-first-col">
                            <table class="table table-striped table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 3%;">No</th>
                                        <th style="width: 10%;" class="sticky-col">Nama Sales</th>
                                        <th style="width: 5%;" class="text-end">Jml Faktur</th>
                                        <th style="width: 7%;" class="text-end">Penjualan</th>
                                        <th style="width: 6%;" class="text-end">Retur</th>
                                        <th style="width: 7%;" class="text-end">Penj. Bersih</th>
                                        <th style="width: 7%;" class="text-end">Target</th>
                                        <th style="width: 5%;" class="text-end">%</th>
                                        <th style="width: 7%;" class="text-end">Tunai</th>
                                        <th style="width: 6%;" class="text-end">CN</th>
                                        <th style="width: 7%;" class="text-end">Giro</th>
                                        <th style="width: 7%;" class="text-end">Terima Bersih</th>
                                        <th style="width: 7%;" class="text-end">Target</th>
                                        <th style="width: 5%;" class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($omset)): ?>
                                    <tr>
                                        <td colspan="14" class="text-center text-muted">Tidak ada data omset.</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php 
                                    $pageNum = isset($page) ? max((int)$page, 1) : 1;
                                    $perPageNum = isset($perPage) ? max((int)$perPage, 1) : 10;
                                    $no = ($pageNum - 1) * $perPageNum + 1;
                                    foreach ($omset as $row): 
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="sticky-col"><?= htmlspecialchars($row['namasales'] ?? '-') ?></td>
                                        <td class="text-end"><?= number_format((float)($row['jumlahfaktur'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format((float)($row['penjualan'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format((float)($row['returpenjualan'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format((float)($row['penjualanbersih'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format((float)($row['targetpenjualan'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format((float)($row['prosenpenjualan'] ?? 0), 2, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format((float)($row['penerimaantunai'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format((float)($row['cnpenjualan'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format((float)($row['pencairangiro'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format((float)($row['penerimaanbersih'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format((float)($row['targetpenerimaan'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format((float)($row['prosenpenerimaan'] ?? 0), 2, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (!empty($omset)): ?>
                                    <tr class="table-info fw-bold">
                                        <td class="text-center"></td>
                                        <td class="text-center sticky-col">TOTAL</td>
                                        <td class="text-end"><?= number_format($totals['jumlahfaktur'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($totals['penjualan'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($totals['returpenjualan'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($totals['penjualanbersih'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($totals['targetpenjualan'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($totals['prosenpenjualan'], 2, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($totals['penerimaantunai'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($totals['cnpenjualan'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($totals['pencairangiro'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($totals['penerimaanbersih'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($totals['targetpenerimaan'], 0, ',', '.') ?></td>
                                        <td class="text-end"><?= number_format($totals['prosenpenerimaan'], 2, ',', '.') ?></td>
                                    </tr>
                                    <?php endif; ?>
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
                        $buildLink = function ($p) use ($perPage, $tahun, $bulan) {
                            return '?page=' . $p
                                . '&per_page=' . $perPage
                                . '&tahun=' . urlencode($tahun)
                                . '&bulan=' . urlencode($bulan);
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
                                    <a class="page-link" href="/laporan/omset<?php echo $buildLink($prevPage); ?>">Previous</a>
                                </li>
                                <?php
                                if ($start > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="/laporan/omset' . $buildLink(1) . '">1</a></li>';
                                    if ($start > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                    }
                                }
                                for ($i = $start; $i <= $end; $i++) {
                                    echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/laporan/omset' . $buildLink($i) . '">' . $i . '</a></li>';
                                }
                                if ($end < $totalPages) {
                                    if ($end < $totalPages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="/laporan/omset' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
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
                                    <a class="page-link" href="/laporan/omset<?php echo $buildLink($nextPage); ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>



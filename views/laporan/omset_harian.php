<?php
$title = 'Laporan Omset Harian';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Load sticky column CSS dan JS (only for non-sales roles)
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

?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Laporan Omset Harian</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 me-auto">Laporan Omset Harian</h4>
                        <div class="d-flex gap-2">
                            <?php
                            $exportParams = [];
                            if (!empty($startDate)) $exportParams['start_date'] = $startDate;
                            if (!empty($endDate)) $exportParams['end_date'] = $endDate;
                            $exportQuery = http_build_query($exportParams);
                            ?>
                            <a href="/laporan/omset-harian?export=excel<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-success btn-sm">
                                <?= icon('file-excel', 'mb-1 me-2', 16) ?>
                                <span class="d-none d-md-inline">Export Excel</span>
                                <span class="d-inline d-md-none">Excel</span>
                            </a>
                            <a href="/laporan/omset-harian?export=pdf<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-danger btn-sm">
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
                                        <h5 class="mb-0">Data Omset Harian</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="GET" action="/laporan/omset-harian" class="mb-3">
                                            <div class="row g-2 align-items-end search-filter-card">
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small text-muted">Tanggal Awal</label>
                                                    <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>" required>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label class="form-label small text-muted">Tanggal Akhir</label>
                                                    <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>" required>
                                                </div>
                                                <div class="col-12 col-md-2">
                                                    <label class="form-label small text-muted">&nbsp;</label>
                                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                                </div>
                                            </div>
                                        </form>
                                        
                                        <div class="row g-3">
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Tanggal</div>
                                                <div class="fw-bold"><?= htmlspecialchars($omsetData['tanggal'] ?? '-') ?></div>
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
                                                <div class="small text-muted">Jumlah Outlet</div>
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
                                        <h5 class="mb-0">Inkaso</h5>
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
                            <p class="mb-0">Tidak ada data omset harian untuk periode yang dipilih.</p>
                        </div>
                        <form method="GET" action="/laporan/omset-harian" class="mb-3">
                            <div class="row g-2 align-items-end search-filter-card">
                                <div class="col-6 col-md-3">
                                    <label class="form-label small text-muted">Tanggal Awal</label>
                                    <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>" required>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small text-muted">Tanggal Akhir</label>
                                    <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>" required>
                                </div>
                                <div class="col-12 col-md-2">
                                    <label class="form-label small text-muted">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </div>
                        </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Table View for Other Roles -->
                        <form method="GET" action="/laporan/omset-harian" class="mb-3">
                            <div class="row g-2 align-items-end search-filter-card">
                                <div class="col-6 col-md-3">
                                    <!-- <label class="form-label small text-muted">Tanggal Awal</label> -->
                                    <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>" required>
                                </div>
                                <div class="col-6 col-md-3">
                                    <!-- <label class="form-label small text-muted">Tanggal Akhir</label> -->
                                    <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="row g-2">
                                        <div class="col-4 col-md-3">
                                            <!-- <label class="form-label small text-muted">Per Page</label> -->
                                            <select name="per_page" class="form-select" onchange="this.form.submit()">
                                                <?php foreach ([10, 25, 50, 100, 200, 500, 1000] as $option): ?>
                                                <option value="<?= $option ?>" <?= $perPage == $option ? 'selected' : '' ?>><?= $option ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-4 col-md-3">
                                            <!-- <label class="form-label small text-muted">&nbsp;</label> -->
                                            <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                                        </div>
                                        <div class="col-4 col-md-3">
                                            <!-- <label class="form-label small text-muted">&nbsp;</label> -->
                                            <a href="/laporan/omset-harian" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Summary Table -->
                         <div class="mb-4">
                            <div class="table-responsive table-sticky-column hide-first-col">
                                <table class="table table-striped table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr class="text-center align-middle">
                                            <th rowspan="2" style="width: 3%;">No</th>
                                            <th rowspan="2" style="width: 15%;">Nama Sales</th>
                                            <th colspan="6">Penjualan</th>
                                            <th colspan="6">Inkaso</th>
                                        </tr>
                                        <tr>
                                            <th>Outlet</th>
                                            <th>Penjualan</th>
                                            <th>Retur</th>
                                            <th>Netto</th>
                                            <th>Target</th>
                                            <th>%</th>
                                            <th>Tunai</th>
                                            <th>CN</th>
                                            <th>Giro</th>
                                            <th>Bersih</th>
                                            <th>Target</th>
                                            <th>%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($summaryBySales)): ?>
                                        <tr>
                                            <td colspan="14" class="text-center text-muted">Tidak ada data summary.</td>
                                        </tr>
                                        <?php else: ?>
                                        <?php 
                                        $no = 1; 
                                        $grandTotalFaktur = 0;
                                        $grandTotalPenjualan = 0;
                                        $grandTotalRetur = 0;
                                        $grandTotalPenjualanBersih = 0;
                                        $grandTotalTargetPenjualan = 0;
                                        $grandTotalPenerimaanTunai = 0;
                                        $grandTotalCN = 0;
                                        $grandTotalGiro = 0;
                                        $grandTotalPenerimaanBersih = 0;
                                        $grandTotalTargetPenerimaan = 0;

                                        foreach ($summaryBySales as $row): 
                                            $grandTotalFaktur += (float)($row['total_jumlahfaktur'] ?? 0);
                                            $grandTotalPenjualan += (float)($row['total_penjualan'] ?? 0);
                                            $grandTotalRetur += (float)($row['total_returpenjualan'] ?? 0);
                                            $grandTotalPenjualanBersih += (float)($row['total_penjualanbersih'] ?? 0);
                                            $grandTotalTargetPenjualan += (float)($row['total_targetpenjualan'] ?? 0);
                                            $grandTotalPenerimaanTunai += (float)($row['total_penerimaantunai'] ?? 0);
                                            $grandTotalCN += (float)($row['total_cnpenjualan'] ?? 0);
                                            $grandTotalGiro += (float)($row['total_pencairangiro'] ?? 0);
                                            $grandTotalPenerimaanBersih += (float)($row['total_penerimaanbersih'] ?? 0);
                                            $grandTotalTargetPenerimaan += (float)($row['total_targetpenerimaan'] ?? 0);

                                            $prosenPenjualan = $row['total_targetpenjualan'] > 0 ? ($row['total_penjualanbersih'] / $row['total_targetpenjualan']) * 100 : 0;
                                            $prosenPenerimaan = $row['total_targetpenerimaan'] > 0 ? ($row['total_penerimaanbersih'] / $row['total_targetpenerimaan']) * 100 : 0;
                                        ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['namasales'] ?? '-') ?></td>
                                            <td class="text-end"><?= number_format((float)($row['total_jumlahfaktur'] ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format((float)($row['total_penjualan'] ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format((float)($row['total_returpenjualan'] ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format((float)($row['total_penjualanbersih'] ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format((float)($row['total_targetpenjualan'] ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($prosenPenjualan, 2, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format((float)($row['total_penerimaantunai'] ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format((float)($row['total_cnpenjualan'] ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format((float)($row['total_pencairangiro'] ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format((float)($row['total_penerimaanbersih'] ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format((float)($row['total_targetpenerimaan'] ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($prosenPenerimaan, 2, ',', '.') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-info fw-bold">
                                            <td class="text-center"></td>
                                            <td class="text-center">TOTAL</td>
                                            <td class="text-end"><?= number_format($grandTotalFaktur, 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($grandTotalPenjualan, 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($grandTotalRetur, 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($grandTotalPenjualanBersih, 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($grandTotalTargetPenjualan, 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($grandTotalTargetPenjualan > 0 ? ($grandTotalPenjualanBersih / $grandTotalTargetPenjualan) * 100 : 0, 2, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($grandTotalPenerimaanTunai, 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($grandTotalCN, 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($grandTotalGiro, 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($grandTotalPenerimaanBersih, 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($grandTotalTargetPenerimaan, 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($grandTotalTargetPenerimaan > 0 ? ($grandTotalPenerimaanBersih / $grandTotalTargetPenerimaan) * 100 : 0, 2, ',', '.') ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>

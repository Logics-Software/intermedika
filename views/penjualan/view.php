<?php
$title = 'Detail Penjualan';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

require __DIR__ . '/../layouts/header.php';

?>
<div class="container">
    <div class="breadcrumb-item mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/penjualan">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/penjualan">Transaksi Penjualan</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (!empty($penjualan)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 me-auto">Penjualan</h4>
                        <a href="/penjualan" class="btn btn-secondary btn-sm"><?= icon('circle-arrow-left', 'me-2', 14) ?> Kembali</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">No Penjualan</div>
                            <span class="fw-bold"><?= htmlspecialchars($penjualan['nopenjualan']) ?></span>
                            <span><?php 
                                    $statuspkp = $penjualan['statuspkp'] ?? null;
                                    if ($statuspkp === 'pkp') {
                                        echo '<span class="badge bg-success small" style="font-size: 0.7em;">PKP</span>';
                                    } elseif ($statuspkp === 'nonpkp') {
                                        echo '<span class="badge bg-secondary small" style="font-size: 0.7em;">Non PKP</span>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                            </span>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Tanggal Penjualan</div>
                            <div class="fw-semibold"><?= $penjualan['tanggalpenjualan'] ? date('d/m/Y', strtotime($penjualan['tanggalpenjualan'])) : '-' ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">No Order</div>
                            <div class="fw-semibold"><?= htmlspecialchars($penjualan['noorder'] ?? '-') ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Tanggal Order</div>
                            <div class="fw-semibold"><?= $penjualan['tanggalorder'] ? date('d/m/Y', strtotime($penjualan['tanggalorder'])) : '-' ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Jatuh Tempo</div>
                            <div class="fw-semibold"><?= $penjualan['tanggaljatuhtempo'] ? date('d/m/Y', strtotime($penjualan['tanggaljatuhtempo'])) : '-' ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Customer</div>
                            <div class="fw-semibold"><?= htmlspecialchars($penjualan['namacustomer'] ?? '-') ?></div>
                            <div class="text-muted small"><?= htmlspecialchars(($penjualan['alamatcustomer'] ?? '') . ' ' . ($penjualan['kotacustomer'] ?? '')) ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Sales</div>
                            <div class="fw-semibold"><?= htmlspecialchars($penjualan['namasales'] ?? '-') ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Pengirim</div>
                            <div class="fw-semibold"><?= htmlspecialchars($penjualan['pengirim'] ?? '-') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Detail Barang</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($details)): ?>
                    <div class="alert alert-warning mb-0" role="alert">
                        <div class="d-flex align-items-center">
                            <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                            </svg>
                            <div>
                                <strong>Data Detail Penjualan Tidak Ditemukan</strong>
                                <p class="mb-0 mt-1">Tidak ada data detail penjualan untuk nomor penjualan <strong><?= htmlspecialchars($penjualan['nopenjualan']) ?></strong>. Data header penjualan tersedia, namun detail barang tidak ditemukan dalam sistem.</p>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th>No.batch</th>
                                    <th>ED</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Diskon</th>
                                    <th class="text-end">Jumlah Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($details as $index => $detail): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($detail['kodebarang']) ?></td>
                                    <td><?= htmlspecialchars($detail['namabarang'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($detail['satuan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($detail['nomorbatch'] ?? '-') ?></td>
                                    <td><?= $detail['expireddate'] ? date('d/m/Y', strtotime($detail['expireddate'])) : '-' ?></td>
                                    <td class="text-end"><?= number_format((float)$detail['jumlah'], 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format((float)$detail['hargasatuan'], 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format((float)$detail['discount'], 2, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format((float)$detail['jumlahharga'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="text-end">
                                <div class="small text-muted">DPP</div>
                                <div class="fw-bold"><?= number_format((float)($penjualan['dpp'] ?? 0), 0, ',', '.') ?></div>
                            </div>
                            <div class="text-end mt-2 d-md-none">
                                <div class="small text-muted">PPN</div>
                                <div class="fw-bold"><?= number_format((float)($penjualan['ppn'] ?? 0), 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 d-none d-md-block">
                            <div class="text-end">
                                <div class="small text-muted">PPN</div>
                                <div class="fw-bold"><?= number_format((float)($penjualan['ppn'] ?? 0), 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-end">
                                <div class="small text-muted">Nilai Penjualan</div>
                                <div class="fw-bold"><?= number_format((float)($penjualan['nilaipenjualan'] ?? 0), 0, ',', '.') ?></div>
                            </div>
                            <div class="text-end mt-2 d-md-none">
                                <div class="small text-muted">Saldo Penjualan</div>
                                <div class="fw-bold"><?= number_format((float)($penjualan['saldopenjualan'] ?? 0), 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 d-none d-md-block">
                            <div class="text-end">
                                <div class="small text-muted">Saldo Penjualan</div>
                                <div class="fw-bold"><?= number_format((float)($penjualan['saldopenjualan'] ?? 0), 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger" role="alert">
                <h5 class="alert-heading">Data Penjualan Tidak Ditemukan</h5>
                <p class="mb-0">Nomor penjualan yang Anda cari tidak ditemukan dalam sistem. Pastikan nomor penjualan yang dimasukkan benar.</p>
                <hr>
                <a href="/penjualan" class="btn btn-secondary btn-sm"><?= icon('circle-arrow-left', 'me-2', 14) ?> Kembali ke Daftar Penjualan</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
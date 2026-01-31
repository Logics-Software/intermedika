<?php
$title = 'Detail Penerimaan Piutang';
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
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/penerimaan">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/penerimaan">Transaksi Inkaso</a></li>
                    <li class="breadcrumb-item active">Detail Inkaso</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (!empty($penerimaan)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 me-auto">Inkaso <?= icon('caret-right', 'me-0 mb-0', 14) ?> <?= htmlspecialchars($penerimaan['nopenerimaan']) ?></h4>
                        <a href="/penerimaan" class="btn btn-secondary btn-sm"><?= icon('back', 'me-2', 14) ?> Kembali</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">No Inkaso</div>
                            <span class="fw-bold"><?= htmlspecialchars($penerimaan['nopenerimaan']) ?></span>
                            <?php 
                            $statuspkp = $penerimaan['statuspkp'] ?? null;
                            if ($statuspkp === 'pkp') {
                                echo '<span class="badge bg-success small ms-2" style="font-size: 0.7em;">PKP</span>';
                            } elseif ($statuspkp === 'nonpkp') {
                                echo '<span class="badge bg-secondary small ms-2" style="font-size: 0.7em;">Non PKP</span>';
                            }
                            ?>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Tanggal Inkaso</div>
                            <div class="fw-semibold"><?= $penerimaan['tanggalpenerimaan'] ? date('d/m/Y', strtotime($penerimaan['tanggalpenerimaan'])) : '-' ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Jenis Inkaso</div>
                            <div class="fw-semibold">
                                <?php
                                $jenisLabels = ['tunai' => 'Tunai', 'transfer' => 'Transfer', 'giro' => 'Giro'];
                                echo $jenisLabels[$penerimaan['jenispenerimaan']] ?? $penerimaan['jenispenerimaan'];
                                ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Status</div>
                            <div>
                                <?php if ($penerimaan['status'] === 'belumproses'): ?>
                                    <span class="badge bg-warning text-dark">Belum Proses</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Proses</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Customer</div>
                            <div class="fw-semibold"><?= htmlspecialchars($penerimaan['namacustomer'] ?? '-') ?></div>
                            <div class="text-muted small"><?= htmlspecialchars(($penerimaan['alamatcustomer'] ?? '') . ' ' . ($penerimaan['kotacustomer'] ?? '')) ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Sales</div>
                            <div class="fw-semibold"><?= htmlspecialchars($penerimaan['namasales'] ?? '-') ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">No Proses</div>
                            <div class="fw-semibold"><?= htmlspecialchars($penerimaan['noinkaso'] ?? '-') ?></div>
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
                    <h5 class="mb-0">Detail Inkaso</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>No.Faktur</th>
                                    <th>Tanggal</th>
                                    <th>Umur</th>
                                    <th>Customer</th>
                                    <th>No Giro</th>
                                    <th>Tgl.Cair</th>
                                    <th class="text-end">Piutang</th>
                                    <th class="text-end">Potongan</th>
                                    <th class="text-end">Lain-lain</th>
                                    <th class="text-end">Netto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($details)): ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">Tidak ada detail penerimaan</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $tanggalInkaso = $penerimaan['tanggalpenerimaan'] ? strtotime($penerimaan['tanggalpenerimaan']) : null;
                                foreach ($details as $index => $detail): 
                                    // Hitung umur (hari) dari tanggal inkaso - tanggal penjualan
                                    $umur = '-';
                                    if ($tanggalInkaso && !empty($detail['tanggalpenjualan'])) {
                                        $tanggalPenjualan = strtotime($detail['tanggalpenjualan']);
                                        if ($tanggalPenjualan) {
                                            $diff = $tanggalInkaso - $tanggalPenjualan;
                                            $umur = max(0, floor($diff / (60 * 60 * 24))); // Konversi detik ke hari
                                        }
                                    }
                                ?>
                                <tr>
                                    <td align="center"><?= $index + 1 ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($detail['nopenjualan'] ?? '-') ?></td>
                                    <td><?= $detail['tanggalpenjualan'] ? date('d/m/Y', strtotime($detail['tanggalpenjualan'])) : '-' ?></td>
                                    <td class="text-center"><?= $umur !== '-' ? $umur . ' hari' : '-' ?></td>
                                    <td><?php
                                        $namacustomer = $detail['namacustomer'] ?? '';
                                        $namabadanusaha = $detail['namabadanusaha'] ?? '';
                                        if ($namacustomer) {
                                            echo htmlspecialchars($namacustomer);
                                            if ($namabadanusaha) {
                                                echo ', ' . htmlspecialchars($namabadanusaha);
                                            }
                                        } else {
                                            echo '-';
                                        }
                                    ?></td>
                                    <td><?= htmlspecialchars($detail['nogiro'] ?? '-') ?></td>
                                    <td><?= $detail['tanggalcair'] ? date('d/m/Y', strtotime($detail['tanggalcair'])) : '-' ?></td>
                                    <td class="text-end"><?= number_format((float)($detail['piutang'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format((float)($detail['potongan'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format((float)($detail['lainlain'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-end fw-bold"><?= number_format((float)($detail['netto'] ?? 0), 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="7" class="text-end">Total:</th>
                                    <th class="text-end"><?= number_format((float)$penerimaan['totalpiutang'], 0, ',', '.') ?></th>
                                    <th class="text-end"><?= number_format((float)$penerimaan['totalpotongan'], 0, ',', '.') ?></th>
                                    <th class="text-end"><?= number_format((float)$penerimaan['totallainlain'], 0, ',', '.') ?></th>
                                    <th class="text-end"><?= number_format((float)$penerimaan['totalnetto'], 0, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php if (Auth::isSales() && $penerimaan['status'] === 'belumproses'): ?>
        <div class="row">
            <div class="col-12">
                <div class="d-flex gap-2">
                    <a href="/penerimaan/edit/<?= urlencode($penerimaan['nopenerimaan']) ?>" class="btn btn-warning"><?= icon('pen-to-square', 'me-2 mb-1', 14) ?> Edit</a>
                    <button type="button" class="btn btn-danger" onclick="confirmDeletePenerimaan('<?= htmlspecialchars($penerimaan['nopenerimaan'], ENT_QUOTES) ?>')"><?= icon('trash-can', 'me-2 mb-1', 14) ?> Hapus</button>
                </div>
            </div>
        </div>

    </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">Data penerimaan tidak ditemukan</div>
        <a href="/penerimaan" class="btn btn-secondary">Kembali</a>
    </div>
</div>
<?php endif; ?>

<script>
function confirmDeletePenerimaan(nopenerimaan) {
    if (!nopenerimaan) {
        return;
    }
    const deleteUrl = `/penerimaan/delete/${encodeURIComponent(nopenerimaan)}`;
    showConfirmModal({
        title: 'Konfirmasi Hapus',
        message: `Apakah Anda yakin ingin menghapus penerimaan <strong>${nopenerimaan}</strong>?`,
        buttonText: 'Hapus',
        buttonClass: 'btn-danger',
        onConfirm: function () {
            window.location.href = deleteUrl;
        }
    });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


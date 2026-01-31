<?php
$title = 'Detail Perubahan Harga';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/perubahanharga">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/perubahanharga">Data Perubahan Harga</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detail Perubahan Harga</h4>
                    <div class="d-flex gap-2">
                        <a href="/perubahanharga/edit/<?= $item['id'] ?>" class="btn btn-warning btn-sm">
                            <?= icon('pen-to-square', 'me-1 mb-1', 16) ?> Edit
                        </a>
                        <a href="/perubahanharga" class="btn btn-secondary btn-sm">
                            <?= icon('cancel', 'me-1 mb-1', 16) ?> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">No. Perubahan</label>
                            <div class="form-control-plaintext"><?= htmlspecialchars($item['noperubahan']) ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tanggal Perubahan</label>
                            <div class="form-control-plaintext"><?= $item['tanggalperubahan'] ? date('d/m/Y', strtotime($item['tanggalperubahan'])) : '-' ?></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Keterangan</label>
                            <div class="form-control-plaintext"><?= htmlspecialchars($item['keterangan']) ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Kode Barang</label>
                            <div class="form-control-plaintext"><?= htmlspecialchars($item['kodebarang']) ?></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Nama Barang</label>
                            <div class="form-control-plaintext"><?= htmlspecialchars($item['namabarang'] ?? '-') ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Satuan</label>
                            <div class="form-control-plaintext"><?= htmlspecialchars($item['satuan'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">Harga Lama</label>
                            <div class="form-control-plaintext"><?= number_format((float)$item['hargalama'], 0, ',', '.') ?></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">Discount Lama</label>
                            <div class="form-control-plaintext"><?= number_format((float)$item['discountlama'], 2, ',', '.') ?></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">Harga Baru</label>
                            <div class="form-control-plaintext fw-bold text-primary"><?= number_format((float)$item['hargabaru'], 0, ',', '.') ?></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">Discount Baru</label>
                            <div class="form-control-plaintext"><?= number_format((float)$item['discountbaru'], 2, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


<?php
$title = 'Detail Pembelian Barang';
$user = $user ?? Auth::user();
$role = $role ?? ($user['role'] ?? '');
$isSales = ($role === 'sales');
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/pembelian">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/pembelian">Data Pembelian Barang</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detail Pembelian Barang</h4>
                    <div class="d-flex gap-2">
                        <a href="/pembelian/edit/<?= $item['id'] ?>" class="btn btn-warning btn-sm">
                            <?= icon('pen-to-square', 'me-1 mb-1', 16) ?> Edit
                        </a>
                        <a href="/pembelian" class="btn btn-secondary btn-sm">
                            <?= icon('cancel', 'me-1 mb-1', 16) ?> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">No. Pembelian</label>
                            <div class="form-control-plaintext"><?= htmlspecialchars($item['nopembelian']) ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tanggal Pembelian</label>
                            <div class="form-control-plaintext"><?= $item['tanggalpembelian'] ? date('d/m/Y', strtotime($item['tanggalpembelian'])) : '-' ?></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Nama Supplier</label>
                            <div class="form-control-plaintext"><?= htmlspecialchars($item['namasupplier']) ?></div>
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
                        <div class="<?= $isSales ? 'col-md-12' : 'col-md-3' ?> mb-3">
                            <label class="form-label fw-semibold">Jumlah</label>
                            <div class="form-control-plaintext"><?= number_format((float)$item['jumlah'], 2, ',', '.') ?></div>
                        </div>
                        <?php if (!$isSales): ?>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">Harga</label>
                            <div class="form-control-plaintext"><?= number_format((float)$item['harga'], 0, ',', '.') ?></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">Discount</label>
                            <div class="form-control-plaintext"><?= number_format((float)$item['discount'], 2, ',', '.') ?></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">Total Harga</label>
                            <div class="form-control-plaintext fw-bold text-primary"><?= number_format((float)$item['totalharga'], 0, ',', '.') ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


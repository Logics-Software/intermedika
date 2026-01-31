<?php
$title = 'Edit Barang';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/masterbarang">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/masterbarang">Master Barang</a></li>
                    <li class="breadcrumb-item active">Edit Barang</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Edit Barang: <?= htmlspecialchars($item['kodebarang']) ?></h4>
                    <small class="text-muted">Perubahan akan langsung tersimpan ke sistem.</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kode Barang</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($item['kodebarang']) ?>" disabled>
                                <small class="text-muted">Kode barang tidak dapat diubah.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" name="namabarang" class="form-control" value="<?= htmlspecialchars($item['namabarang']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Satuan</label>
                                <input type="text" name="satuan" class="form-control" value="<?= htmlspecialchars($item['satuan'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pabrik</label>
                                <select name="kodepabrik" class="form-select">
                                    <option value="">Pilih Pabrik</option>
                                    <?php foreach ($pabriks as $pabrik): ?>
                                    <option value="<?= htmlspecialchars($pabrik['kodepabrik']) ?>" <?= ($item['kodepabrik'] ?? '') === $pabrik['kodepabrik'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($pabrik['kodepabrik'] . ' - ' . $pabrik['namapabrik']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Golongan</label>
                                <select name="kodegolongan" class="form-select">
                                    <option value="">Pilih Golongan</option>
                                    <?php foreach ($golongans as $golongan): ?>
                                    <option value="<?= htmlspecialchars($golongan['kodegolongan']) ?>" <?= ($item['kodegolongan'] ?? '') === $golongan['kodegolongan'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($golongan['kodegolongan'] . ' - ' . $golongan['namagolongan']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Supplier</label>
                                <select name="kodesupplier" class="form-select">
                                    <option value="">Pilih Supplier</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= htmlspecialchars($supplier['kodesupplier']) ?>" <?= ($item['kodesupplier'] ?? '') === $supplier['kodesupplier'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($supplier['kodesupplier'] . ' - ' . $supplier['namasupplier']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kandungan</label>
                                <input type="text" name="kandungan" class="form-control" value="<?= htmlspecialchars($item['kandungan'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">OOT</label>
                                <select name="oot" class="form-select">
                                    <option value="tidak" <?= ($item['oot'] ?? '') === 'tidak' ? 'selected' : '' ?>>Tidak</option>
                                    <option value="ya" <?= ($item['oot'] ?? '') === 'ya' ? 'selected' : '' ?>>Ya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prekursor</label>
                                <select name="prekursor" class="form-select">
                                    <option value="tidak" <?= ($item['prekursor'] ?? '') === 'tidak' ? 'selected' : '' ?>>Tidak</option>
                                    <option value="ya" <?= ($item['prekursor'] ?? '') === 'ya' ? 'selected' : '' ?>>Ya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIE</label>
                                <input type="text" name="nie" class="form-control" value="<?= htmlspecialchars($item['nie'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kondisi</label>
                                <input type="text" name="kondisi" class="form-control" value="<?= htmlspecialchars($item['kondisi'] ?? '') ?>" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ED</label>
                                <input type="text" name="ed" class="form-control" value="<?= htmlspecialchars($item['ed'] ?? '') ?>" maxlength="50">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">HPP</label>
                                <input type="number" step="0.01" name="hpp" class="form-control" value="<?= htmlspecialchars($item['hpp'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga Beli</label>
                                <input type="number" step="0.01" name="hargabeli" class="form-control" value="<?= htmlspecialchars($item['hargabeli'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Diskon Beli (%)</label>
                                <input type="number" step="0.01" name="discountbeli" class="form-control" value="<?= htmlspecialchars($item['discountbeli'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga Jual</label>
                                <input type="number" step="0.01" name="hargajual" class="form-control" value="<?= htmlspecialchars($item['hargajual'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Diskon Jual (%)</label>
                                <input type="number" step="0.01" name="discountjual" class="form-control" value="<?= htmlspecialchars($item['discountjual'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stok Akhir</label>
                                <input type="number" step="0.01" name="stokakhir" class="form-control" value="<?= htmlspecialchars($item['stokakhir'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="aktif" <?= ($item['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= ($item['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Non Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="/masterbarang" class="btn btn-outline-secondary"><?= icon('back', 'me-2', 16) ?> Kembali</a>
                            <button type="submit" class="btn btn-primary"><?= icon('save', 'me-2', 16) ?> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>



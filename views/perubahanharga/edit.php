<?php
$title = 'Edit Data Perubahan Harga';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/perubahanharga">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/perubahanharga">Data Perubahan Harga</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Data Perubahan Harga</h4>
                    </div>
                </div>

                <form method="POST" action="/perubahanharga/edit/<?= $item['id'] ?>" id="perubahanhargaForm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="noperubahan" class="form-label">No. Perubahan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="noperubahan" name="noperubahan" required placeholder="Masukkan no perubahan" maxlength="15" value="<?= htmlspecialchars($item['noperubahan']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggalperubahan" class="form-label">Tanggal Perubahan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggalperubahan" name="tanggalperubahan" required value="<?= htmlspecialchars($item['tanggalperubahan']) ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="keterangan" class="form-label">Keterangan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan" required placeholder="Masukkan keterangan" maxlength="100" value="<?= htmlspecialchars($item['keterangan']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="kodebarang" class="form-label">Kode Barang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kodebarang" name="kodebarang" required placeholder="Masukkan kode barang" maxlength="15" value="<?= htmlspecialchars($item['kodebarang']) ?>" list="barangList">
                                <datalist id="barangList">
                                    <?php foreach ($barangs as $barang): ?>
                                    <option value="<?= htmlspecialchars($barang['kodebarang']) ?>" data-nama="<?= htmlspecialchars($barang['namabarang']) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="hargalama" class="form-label">Harga Lama <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="hargalama" name="hargalama" required step="0.01" min="0" value="<?= htmlspecialchars($item['hargalama']) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="discountlama" class="form-label">Discount Lama</label>
                                <input type="number" class="form-control" id="discountlama" name="discountlama" step="0.01" min="0" value="<?= htmlspecialchars($item['discountlama']) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="hargabaru" class="form-label">Harga Baru <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="hargabaru" name="hargabaru" required step="0.01" min="0" value="<?= htmlspecialchars($item['hargabaru']) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="discountbaru" class="form-label">Discount Baru</label>
                                <input type="number" class="form-control" id="discountbaru" name="discountbaru" step="0.01" min="0" value="<?= htmlspecialchars($item['discountbaru']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="/perubahanharga" class="btn btn-secondary">
                            <?= icon('cancel', 'me-1 mb-1', 18) ?> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <?= icon('save', 'me-1 mb-1', 18) ?> Update Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


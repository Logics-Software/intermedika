<?php
$title = 'Tambah Data Pembelian Barang';
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
                    <li class="breadcrumb-item active">Tambah Data</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Tambah Data Pembelian Barang</h4>
                    </div>
                </div>

                <form method="POST" action="/pembelian/create" id="pembelianForm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nopembelian" class="form-label">No. Pembelian <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nopembelian" name="nopembelian" required placeholder="Masukkan no pembelian" maxlength="15">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggalpembelian" class="form-label">Tanggal Pembelian <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggalpembelian" name="tanggalpembelian" required value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="namasupplier" class="form-label">Nama Supplier <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="namasupplier" name="namasupplier" required placeholder="Masukkan nama supplier" maxlength="100" list="supplierList">
                                <datalist id="supplierList">
                                    <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= htmlspecialchars($supplier['namasupplier']) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="kodebarang" class="form-label">Kode Barang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kodebarang" name="kodebarang" required placeholder="Masukkan kode barang" maxlength="15" list="barangList">
                                <datalist id="barangList">
                                    <?php foreach ($barangs as $barang): ?>
                                    <option value="<?= htmlspecialchars($barang['kodebarang']) ?>" data-nama="<?= htmlspecialchars($barang['namabarang']) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>
                        <div class="row">
                            <div class="<?= $isSales ? 'col-md-12' : 'col-md-3' ?> mb-3">
                                <label for="jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="jumlah" name="jumlah" required step="0.01" min="0" value="0" <?= !$isSales ? 'onchange="calculateTotal()"' : '' ?>>
                            </div>
                            <?php if (!$isSales): ?>
                            <div class="col-md-3 mb-3">
                                <label for="harga" class="form-label">Harga <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="harga" name="harga" required step="0.01" min="0" value="0" onchange="calculateTotal()">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="discount" class="form-label">Discount</label>
                                <input type="number" class="form-control" id="discount" name="discount" step="0.01" min="0" value="0" onchange="calculateTotal()">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="totalharga" class="form-label">Total Harga</label>
                                <input type="number" class="form-control" id="totalharga" name="totalharga" step="0.01" min="0" value="0" readonly>
                            </div>
                            <?php else: ?>
                            <input type="hidden" id="harga" name="harga" value="0">
                            <input type="hidden" id="discount" name="discount" value="0">
                            <input type="hidden" id="totalharga" name="totalharga" value="0">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="/pembelian" class="btn btn-secondary">
                            <?= icon('cancel', 'me-1 mb-1', 18) ?> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <?= icon('save', 'me-1 mb-1', 18) ?> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function calculateTotal() {
    const hargaEl = document.getElementById('harga');
    const discountEl = document.getElementById('discount');
    const totalhargaEl = document.getElementById('totalharga');
    
    if (!hargaEl || !discountEl || !totalhargaEl) {
        return; // Fields hidden for sales role
    }
    
    const jumlah = parseFloat(document.getElementById('jumlah').value) || 0;
    const harga = parseFloat(hargaEl.value) || 0;
    const discount = parseFloat(discountEl.value) || 0;
    
    const subtotal = jumlah * harga;
    const total = subtotal - discount;
    
    totalhargaEl.value = Math.max(0, total).toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


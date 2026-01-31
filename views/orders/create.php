<?php
$title = 'Buat Order';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

$additionalStyles = array_merge($additionalStyles ?? [], [
    $baseUrl . '/assets/css/choices.min.css'
]);
$additionalScripts = array_merge($additionalScripts ?? [], [
    $baseUrl . '/assets/js/choices.min.js'
]);

require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/orders">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/orders">Transaksi Order</a></li>
                    <li class="breadcrumb-item active">Buat Order</li>
                </ol>
            </nav>
        </div>
	</div>

    <div class="row">
        <div class="col-12">
           <div class="card">
                <div class="card-header"<?= !empty($backUrl ?? '') ? ' data-back-url="' . htmlspecialchars($backUrl) . '"' : '' ?>>
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Order Baru <?= icon('caret-right', 'me-0 mb-1', 14)?> <?= htmlspecialchars($noorder) ?></h4>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="orderForm" enctype="multipart/form-data">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label" for="statusPkpSelect">Jenis Customer</label>
                                <select name="statuspkp" id="statusPkpSelect" class="form-select">
                                    <option value="pkp" <?= (strtolower($statuspkp ?? 'pkp') === 'pkp') ? 'selected' : '' ?>>PKP</option>
                                    <option value="nonpkp" <?= (strtolower($statuspkp ?? 'pkp') === 'nonpkp') ? 'selected' : '' ?>>Non PKP</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-8">
                                <label class="form-label">Customer</label>
                                <?php
                                $normalizedStatusPkp = strtolower($statuspkp ?? 'pkp');
                                $availableCustomers = $customersByStatus[$normalizedStatusPkp] ?? $customers;
                                if ($selectedCustomer && !array_filter($availableCustomers, static function ($item) use ($selectedCustomer) {
                                    return ($item['kodecustomer'] ?? '') === $selectedCustomer;
                                })) {
                                    foreach ($customers as $fallbackCustomer) {
                                        if (($fallbackCustomer['kodecustomer'] ?? '') === $selectedCustomer) {
                                            $availableCustomers[] = $fallbackCustomer;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <select name="kodecustomer" class="form-select js-choice-customer" data-selected="<?= htmlspecialchars($selectedCustomer ?? '') ?>">
						            <option value="">Pilih Customer</option>
                                    <?php foreach ($availableCustomers as $customer): ?>
                                        <?php
                                        $alamat = trim($customer['alamatcustomer'] ?? '');
                                        $optionLabel = $customer['namacustomer'];
                                        if ($alamat !== '') {
                                            $optionLabel .= ' - ' . $alamat;
                                        }
                                        $optionLabel .= ' (' . $customer['kodecustomer'] . ')';
                                        ?>
                                        <option value="<?= htmlspecialchars($customer['kodecustomer']) ?>" <?= ($selectedCustomer === $customer['kodecustomer']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($optionLabel) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Keterangan</label>
                                            <input type="text" name="keterangan" class="form-control" value="<?= htmlspecialchars($keterangan) ?>" maxlength="50">
                            </div>
                        </div>
                        <input type="hidden" name="tanggalorder" value="<?= htmlspecialchars($tanggalorder) ?>">
                        <input type="hidden" name="status" value="order">
                        <input type="hidden" name="noorder" value="<?= htmlspecialchars($noorder) ?>">

                        <?php
                        $detailRows = array_values(array_filter($detailItems, static function ($item) {
                            return !empty($item['kodebarang']);
                        }));
                        $barangLookup = [];
                        foreach ($barangs as $barang) {
                            $barangLookup[$barang['kodebarang']] = $barang;
                        }
                        $normalizedDetails = [];
                        foreach ($detailRows as $detail) {
                            $kode = $detail['kodebarang'];
                            $barangInfo = $barangLookup[$kode] ?? null;
                            $namaBarang = $barangInfo['namabarang'] ?? ($detail['namabarang'] ?? $kode);
                            $jumlah = isset($detail['jumlah']) ? (float)$detail['jumlah'] : 0;
                            $hargaBaris = isset($detail['hargajual']) ? (float)$detail['hargajual'] : (isset($detail['harga']) ? (float)$detail['harga'] : 0);
                            $discount = isset($detail['discount']) ? (float)$detail['discount'] : 0;
                            $total = isset($detail['totalharga']) ? (float)$detail['totalharga'] : (($jumlah * $hargaBaris) - $discount);
                            if ($total < 0) {
                                $total = 0;
                            }
                            $normalizedDetails[] = [
                                'kodebarang' => $kode,
                                'namabarang' => $namaBarang,
                                'jumlah' => $jumlah,
                                'harga' => $hargaBaris,
                                'discount' => $discount,
                                'total' => $total,
                                'satuan' => $detail['satuan'] ?? ($barangInfo['satuan'] ?? '')
                            ];
                        }
                        $detailJson = json_encode($normalizedDetails, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                        ?>

                        <div class="card">
                            <div class="card-header-table">
                                <div class="mb-1 d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0">Detail Barang</h4>
                                    <button type="button" class="btn btn-success btn-sm" id="addDetailBtn">Tambah Barang</button>
                                </div>
                            </div>
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-striped align-middle table-detail-barang mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="order-col-item">Barang</th>
                                            <th class="order-col-qty text-end">Jumlah</th>
                                            <th class="order-col-unit text-end">Satuan</th>
                                            <th class="order-col-price text-end">Harga</th>
                                            <th class="order-col-discount text-end">Diskon</th>
                                            <th class="order-col-total text-end">Total</th>
                                            <th class="order-col-action text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detailTableBody"></tbody>
                                    <tfoot>
                                        <tr class="table-light fw-bold">
                                            <td colspan="5" class="text-end">Grand Total:</td>
                                            <td class="text-end" id="grandTotalDisplay">0</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="order-items-cards d-block d-md-none" id="detailCardsContainer"></div>
                            <div class="d-block d-md-none mt-3 p-2 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Grand Total:</span>
                                    <span class="fw-bold" id="grandTotalDisplayMobile">0</span>
                                </div>
                            </div>
                            <div id="detailEmptyState" class="text-center text-muted py-3 <?= !empty($detailRows) ? 'd-none' : '' ?>">
                                Belum ada barang ditambahkan
                            </div>
                            <input type="hidden" name="nilaiorder" id="grandTotalHidden" value="0">
                        </div>

                        <div class="card mt-3">
                            <div class="card-header-table">
                                <h4 class="mb-0">Lampiran Order</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="file" name="order_files[]" class="form-control" id="orderFilesInput" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar">
                                        <!-- Mobile camera input (hidden on desktop) -->
                                        <input type="file" name="order_files[]" id="orderFilesMobile" class="form-control d-none" accept="image/*" capture="environment">
                                        <button type="button" class="btn btn-primary" id="btnOpenCamera" data-bs-toggle="modal" data-bs-target="#cameraModal">
                                            <?= icon('camera', 'mb-1 me-2', 16) ?> Kamera
                                        </button>
                                        <!-- Mobile direct camera button (hidden on desktop) -->
                                        <button type="button" class="btn btn-primary d-none" id="btnMobileCamera">
                                            <?= icon('camera', 'mb-1 me-2', 16) ?> Kamera
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">Format yang diizinkan: JPG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX, TXT, ZIP, RAR. Maksimal 5 file, setiap file maksimal 5MB.</small>
                                    <div id="fileList" class="mt-2"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div></div>
                            <div>
                                <a href="/orders" class="btn btn-secondary me-2"><?= icon('cancel', 'me-1 mb-1', 18) ?> Batal</a>
                                <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?> Simpan Order</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="detailForm">
                <div class="modal-header modal-header-muted">
                    <h5 class="modal-title" id="detailModalLabel">Tambah Barang</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Barang</label>
                        <select class="form-select js-choice-barang" id="modalBarang" required>
                            <option value="">Pilih Barang</option>
                            <?php foreach ($barangs as $barang): ?>
                            <option value="<?= htmlspecialchars($barang['kodebarang']) ?>" data-nama="<?= htmlspecialchars($barang['namabarang']) ?>" data-satuan="<?= htmlspecialchars($barang['satuan'] ?? '-') ?>" data-harga="<?= isset($barang['hargajual']) ? number_format((float)$barang['hargajual'], 2, '.', '') : '0' ?>" data-discount="<?= isset($barang['discountjual']) ? number_format((float)$barang['discountjual'], 2, '.', '') : '0' ?>" data-stok="<?= htmlspecialchars($barang['stokakhir'] ?? '0') ?>">
                                <?= htmlspecialchars(sprintf('%s (%s) - Stok: %s', $barang['namabarang'], $barang['kodebarang'], number_format((float)($barang['stokakhir'] ?? 0), 0, ',', '.'))) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3 col-5">
                            <label class="form-label">Stok tersedia</label>
                            <div class="input-group stock-display">
                                <span class="form-control" id="modalStokInfo">0</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-7">
                            <label class="form-label">Jumlah</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="modalJumlah" value="1" required>
                                <span class="input-group-text" id="modalSatuan">-</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-7">
                            <label class="form-label">Harga</label>
                            <input type="text" class="form-control" id="modalHarga" inputmode="numeric" value="0" required>
                        </div>
                        <div class="col-md-3 col-5">
                            <label class="form-label">Diskon</label>
                            <input type="text" class="form-control" id="modalDiscount" inputmode="decimal" value="0,00">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Total</label>
                        <input type="text" class="form-control fw-bold" id="modalTotal" value="0" readonly>
                    </div>
                </div>
                <div class="modal-footer modal-footer-muted justify-content-between align-items-center">
                    <div class="me-auto">
                        <button type="button" class="btn btn-success btn-lg" id="openStockPriceModal">
                            <?= icon('list-check', 'me-0 mb-1 text-white', 18) ?> Stok &amp; Harga
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="detailModalSubmitBtn">Simpan</button>
                    </div>
			</div>
		</form>
        </div>
    </div>
</div>

<div class="modal fade" id="stockPriceModal" tabindex="-1" aria-labelledby="stockPriceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header modal-header-muted">
                <h5 class="modal-title" id="stockPriceModalLabel">Informasi Stok dan Harga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-12 col-lg-4">
                        <input type="text" class="form-control" id="stockSearchInput" placeholder="Cari Nama atau Kode Barang...">
                    </div>
                    <div class="col-5 col-md-3 col-lg-1">
                        <select class="form-select" id="stockStockFilter">
                            <option value="all">Semua</option>
                            <option value="available">Stok &gt; 0</option>
                            <option value="empty">Stok = 0</option>
                        </select>
                    </div>
                    <div class="col-7 col-md-5 col-lg-3">
                        <select class="form-select" id="stockFactoryFilter">
                            <option value="">Semua Pabrik</option>
                        </select>
                    </div>
                    <div class="col-7 col-md-5 col-lg-3">
                        <select class="form-select" id="stockGroupFilter">
                            <option value="">Semua Golongan</option>
                        </select>
                    </div>
                    <div class="col-5 col-md-3 col-lg-1">
                        <select class="form-select" id="stockPageSize">
                            <option value="10">10 data</option>
                            <option value="20">20 data</option>
                            <option value="40">40 data</option>
                            <option value="60">60 data</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle stock-info-table">
                        <thead class="table-light">
                            <tr>
                                <th class="stock-col-name">Nama Barang</th>
                                <th class="stock-col-factory">Pabrik</th>
                                <th class="stock-col-group">Golongan</th>
                                <th class="stock-col-unit">Satuan</th>
                                <th class="stock-col-price text-end">Harga Jual</th>
                                <th class="stock-col-discount text-end">Diskon (%)</th>
                                <th class="stock-col-stock text-end">Stok</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="stockTableBody"></tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <nav>
                        <ul class="pagination justify-content-center mb-0" id="stockPagination"></ul>
                    </nav>
                </div>
            </div>
            <div class="modal-footer modal-footer-muted">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kembali</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Kamera -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cameraModalLabel">Ambil Foto dari Kamera</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="btnCloseCamera"></button>
            </div>
            <div class="modal-body text-center">
                <div id="cameraError" class="alert alert-danger d-none"></div>
                <video id="videoPreview" autoplay playsinline style="width: 100%; max-width: 640px; border-radius: 8px; background: #000; display: none;"></video>
                <canvas id="canvasCapture" style="display: none;"></canvas>
                <div id="cameraPlaceholder" class="p-5 bg-light rounded">
                    <p class="text-muted mb-0">Klik tombol "Mulai Kamera" untuk mengaktifkan kamera</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnCancelCamera">Batal</button>
                <button type="button" class="btn btn-primary" id="btnStartCamera">Mulai Kamera</button>
                <button type="button" class="btn btn-success" id="btnCapturePhoto" style="display: none;">📷 Ambil Foto</button>
                <button type="button" class="btn btn-primary" id="btnRetakePhoto" style="display: none;">Ulangi</button>
                <button type="button" class="btn btn-success" id="btnUsePhoto" style="display: none;">Gunakan Foto Ini</button>
            </div>
        </div>
    </div>
</div>

<?php
$customersStatusJsonSafe = json_encode($customersByStatus ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>
<script>
const orderCustomersByStatus = <?= $customersStatusJsonSafe ?? '{}' ?> || {};
const barangList = <?= $barangsJson ?? '[]' ?>;
const barangMap = {};
barangList.forEach((item) => {
    if (item && item.kodebarang) {
        barangMap[item.kodebarang] = item;
    }
});

const initialDetails = <?= $detailJson ?? '[]' ?>;

const integerFormatter = new Intl.NumberFormat('id-ID', {
    maximumFractionDigits: 0,
    minimumFractionDigits: 0
});

const currencyFormatter = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
});

function parseDiscountInput(value, formatOnBlur = false) {
    let cleaned = (value ?? '').replace(/[^0-9.,]/g, '').replace(/\./g, ',');
    if (!cleaned) {
        return {
            raw: 0,
            display: formatOnBlur ? '0,00' : ''
        };
    }

    const endsWithComma = cleaned.endsWith(',');
    const segments = cleaned.split(',');
    const rawIntPart = segments.shift() || '';
    const rawFractionPart = segments.join('').replace(/[^0-9]/g, '');

    const intDigits = rawIntPart.replace(/[^0-9]/g, '');
    const numericInt = intDigits === '' ? 0 : parseInt(intDigits, 10);
    const formattedInt = String(Number.isNaN(numericInt) ? 0 : numericInt);

    let fractionDigits = rawFractionPart.slice(0, 2);
    let raw = parseFloat(`${formattedInt}.${fractionDigits || '0'}`);
    if (Number.isNaN(raw)) raw = 0;
    raw = Math.max(raw, 0);

    let display;
    if (formatOnBlur) {
        display = raw.toFixed(2).replace('.', ',');
    } else {
        if (cleaned.includes(',')) {
            if (fractionDigits.length === 0 && endsWithComma) {
                display = `${formattedInt},`;
            } else {
                display = `${formattedInt},${fractionDigits}`;
            }
        } else {
            display = formattedInt;
        }
    }

    return { raw, display };
}

let orderDetails = Array.isArray(initialDetails) ? [...initialDetails] : [];
let detailModalInstance = null;
let barangChoiceInstance = null;
let customerChoiceInstances = [];
let currentEditIndex = null;
let rawHarga = 0;
let rawDiscount = 0;
let currentSelectedStock = null;
let modalStokInfoEl = null;

function parseNumericStock(value) {
    if (value === null || value === undefined) {
        return null;
    }
    let str = String(value).trim();
    if (!str) {
        return null;
    }
    str = str.replace(/\s+/g, '').replace(',', '.');
    const parsed = parseFloat(str);
    return Number.isNaN(parsed) ? null : parsed;
}

function getAvailableStock(option) {
    if (!option) {
        return null;
    }
    return parseNumericStock(option.dataset?.stok ?? null);
}

function updateStokInfoDisplay(stokAkhir) {
    if (!modalStokInfoEl) {
        return;
    }
    modalStokInfoEl.classList.remove('text-danger', 'text-warning', 'text-success', 'text-muted');
    if (stokAkhir === null) {
        modalStokInfoEl.textContent = '-';
        modalStokInfoEl.classList.add('text-muted');
        return;
    }

    const formattedStock = formatQty(stokAkhir);
    modalStokInfoEl.textContent = `${formattedStock}`;

    if (stokAkhir <= 0) {
        modalStokInfoEl.classList.add('text-danger');
    } else if (stokAkhir <= 5) {
        modalStokInfoEl.classList.add('text-warning');
    } else {
        modalStokInfoEl.classList.add('text-success');
    }
}

function resetDetailForm(
    detailForm,
    modalJumlahInput,
    modalHargaInput,
    modalDiscountInput,
    modalTotalInput,
    modalSatuanDisplay
) {
    detailForm?.reset();
    modalJumlahInput.value = '1';
    rawHarga = 0;
    rawDiscount = 0;
    currentSelectedStock = null;
    modalHargaInput.value = '0';
    modalDiscountInput.value = '0,00';
    modalTotalInput.value = integerFormatter.format(0);
    modalTotalInput.dataset.rawTotal = (0).toFixed(2);
    modalSatuanDisplay.textContent = '-';
    updateStokInfoDisplay(null);
    if (barangChoiceInstance) {
        barangChoiceInstance.removeActiveItems();
    } else if (detailForm?.querySelector('#modalBarang')) {
        detailForm.querySelector('#modalBarang').value = '';
    }
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    })[char]);
}

function formatQty(value) {
    const num = parseFloat(value) || 0;
    if (Number.isInteger(num)) {
        return integerFormatter.format(num);
    }
    return currencyFormatter.format(num);
}

function formatCurrency(value) {
    return currencyFormatter.format(parseFloat(value) || 0);
}

function getBarangInfo(kode) {
    return barangMap[kode] || null;
}

function updateGrandTotal(grandTotalDisplay, grandTotalHidden) {
    const total = orderDetails.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
    if (grandTotalDisplay) {
        grandTotalDisplay.innerText = integerFormatter.format(Math.round(total));
    }
    const grandTotalDisplayMobile = document.getElementById('grandTotalDisplayMobile');
    if (grandTotalDisplayMobile) {
        grandTotalDisplayMobile.innerText = integerFormatter.format(Math.round(total));
    }
    if (grandTotalHidden) {
        grandTotalHidden.value = total.toFixed(2);
    }
}

function renderDetailTable(detailTableBody, detailEmptyState, grandTotalDisplay, grandTotalHidden) {
    detailTableBody.innerHTML = '';
    const detailCardsContainer = document.getElementById('detailCardsContainer');
    if (detailCardsContainer) {
        detailCardsContainer.innerHTML = '';
    }
    
    if (!orderDetails.length) {
        detailEmptyState.classList.remove('d-none');
    } else {
        detailEmptyState.classList.add('d-none');
        orderDetails.forEach((item, index) => {
            const jumlah = parseFloat(item.jumlah) || 0;
            const harga = parseFloat(item.harga) || 0;
            const discountPercent = parseFloat(item.discount) || 0;
            const effectiveHarga = Math.max(harga * (1 - discountPercent / 100), 0);
            const total = Math.max(jumlah * effectiveHarga, 0);

            // Table row for desktop
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="fw-semibold">${escapeHtml(item.namabarang)}</div>
                    <input type="hidden" name="detail_kodebarang[]" value="${escapeHtml(item.kodebarang)}">
                    <input type="hidden" name="detail_satuan[]" value="${escapeHtml(item.satuan || '')}">
                </td>
                <td class="text-end">
                    ${formatQty(jumlah)}
                    <input type="hidden" name="detail_jumlah[]" value="${jumlah.toFixed(2)}">
                </td>
                <td>
                    ${escapeHtml(item.satuan || '')}
                </td>
                <td class="text-end">
                    ${integerFormatter.format(Math.round(harga))}
                    <input type="hidden" name="detail_harga[]" value="${harga.toFixed(2)}">
                </td>
                <td class="text-end">
                    ${discountPercent.toFixed(2).replace('.', ',')}
                    <input type="hidden" name="detail_discount[]" value="${discountPercent.toFixed(2)}">%
                </td>
                <td class="text-end">
                    <span class="fw-bold fw-xl">${integerFormatter.format(Math.round(total))}</span>
                    <input type="hidden" name="detail_totalharga[]" value="${total.toFixed(2)}">
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary py-1" data-action="edit" data-index="${index}">Edit</button>
                        <button type="button" class="btn btn-outline-danger py-1" data-action="remove" data-index="${index}">Hapus</button>
                    </div>
                </td>
            `;
            detailTableBody.appendChild(row);

            // Card for mobile (display only, no hidden inputs to avoid duplication)
            if (detailCardsContainer) {
                const satuanBarang = item.satuan || 'BOX';
                const card = document.createElement('div');
                card.className = 'order-item-card';
                card.innerHTML = `
                    <div class="order-item-card-header">
                        <h6 class="order-item-card-title">${escapeHtml(item.namabarang)}</h6>
                    </div>
                    <div class="order-item-card-body">
                        <div class="order-item-card-row">
                            <span class="order-item-card-value">${formatQty(jumlah)} ${satuanBarang} x Rp. ${integerFormatter.format(Math.round(harga))} (${discountPercent.toFixed(2).replace('.', ',')}%) = Rp. <span class="fw-bold fw-xl">${integerFormatter.format(Math.round(total))}</span></span>
                        </div>
                    </div>
                    <div class="order-item-card-actions">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-action="edit" data-index="${index}">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-action="remove" data-index="${index}">Hapus</button>
                    </div>
                `;
                detailCardsContainer.appendChild(card);
            }
        });
    }
    updateGrandTotal(grandTotalDisplay, grandTotalHidden);
}

function openDetailModal(modalElements, index = null, kodeBarangFromStock = null) {
    const { modal, detailForm, modalBarangSelect, modalJumlahInput, modalHargaInput, modalDiscountInput, modalTotalInput, modalTitle, modalSatuanDisplay } = modalElements;
    currentEditIndex = index;
    const isEdit = index !== null;
    if (modalTitle) {
        modalTitle.textContent = isEdit ? 'Ubah Barang' : 'Tambah Barang';
    }
    detailForm?.reset();

    const selectedDetail = isEdit ? orderDetails[index] : null;
    const kode = kodeBarangFromStock || (selectedDetail?.kodebarang ?? '');
    if (barangChoiceInstance) {
        barangChoiceInstance.removeActiveItems();
        if (kode) {
            // Gunakan setTimeout untuk memastikan Choices.js siap
            setTimeout(() => {
                try {
                    barangChoiceInstance.setChoiceByValue(kode);
                    // Trigger change event setelah setChoiceByValue
                    setTimeout(() => {
                        if (modalBarangSelect) {
                            const changeEvent = new Event('change', { bubbles: true });
                            modalBarangSelect.dispatchEvent(changeEvent);
                        }
                    }, 100);
                } catch (e) {
                    console.error('Error setting choice value:', e);
                    if (modalBarangSelect) {
                        modalBarangSelect.value = kode;
                        const changeEvent = new Event('change', { bubbles: true });
                        modalBarangSelect.dispatchEvent(changeEvent);
                    }
                }
            }, 100);
        }
    } else if (modalBarangSelect) {
        modalBarangSelect.value = kode;
        if (kode) {
            // Trigger change event untuk mengisi data lainnya
            setTimeout(() => {
                const changeEvent = new Event('change', { bubbles: true });
                modalBarangSelect.dispatchEvent(changeEvent);
            }, 100);
        }
    }

    modalJumlahInput.value = selectedDetail ? String(parseFloat(selectedDetail.jumlah) || 0) : '1';
    rawHarga = selectedDetail ? (parseFloat(selectedDetail.harga) || 0) : 0;
    rawDiscount = selectedDetail ? (parseFloat(selectedDetail.discount) || 0) : 0;
    const initialTotal = selectedDetail ? (parseFloat(selectedDetail.total) || 0) : 0;
    modalHargaInput.value = rawHarga ? integerFormatter.format(Math.round(rawHarga)) : '0';
    modalDiscountInput.value = parseDiscountInput(String(rawDiscount), true).display;
    modalTotalInput.value = integerFormatter.format(Math.round(initialTotal));
    modalTotalInput.dataset.rawTotal = initialTotal.toFixed(2);
    modalSatuanDisplay.textContent = selectedDetail?.satuan || '-';
    const selectedOption = modalBarangSelect && modalBarangSelect.selectedOptions ? modalBarangSelect.selectedOptions[0] : null;
    const stokAkhir = getAvailableStock(selectedOption);
    currentSelectedStock = stokAkhir;
    updateStokInfoDisplay(stokAkhir);

    modal?.show();
}

function findChoiceInstanceForSelect(selectElement) {
    if (!selectElement) {
        return null;
    }
    return customerChoiceInstances.find(function(choice) {
        return choice && choice.passedElement && choice.passedElement.element === selectElement;
    }) || null;
}

function buildCustomerOptionLabel(customer) {
    const name = (customer?.namacustomer ?? customer?.kodecustomer ?? '').toString();
    const address = (customer?.alamatcustomer ?? '').toString().trim();
    let label = name;
    if (address) {
        label += ' - ' + address;
    }
    if (customer?.kodecustomer) {
        label += ' (' + customer.kodecustomer + ')';
    }
    return label;
}

function setupCustomerStatusFilter(customerSelect, statusSelect, customersMap) {
    if (!customerSelect || !statusSelect) {
        return;
    }

    let currentSelectedCustomer = customerSelect.dataset.selected || customerSelect.value || '';

    function refreshCustomerOptions(preserveSelection) {
        const normalizedStatus = (statusSelect.value || 'pkp').toLowerCase() === 'nonpkp' ? 'nonpkp' : 'pkp';
        const list = customersMap?.[normalizedStatus] || [];

        if (!preserveSelection) {
            currentSelectedCustomer = '';
        }

        if (currentSelectedCustomer && !list.some(function(item) { return item.kodecustomer === currentSelectedCustomer; })) {
            currentSelectedCustomer = '';
        }

        const choiceInstance = findChoiceInstanceForSelect(customerSelect);
        if (choiceInstance) {
            choiceInstance.clearChoices();
            const options = [{
                value: '',
                label: 'Pilih Customer',
                selected: currentSelectedCustomer === ''
            }];
            list.forEach(function(customer) {
                options.push({
                    value: customer.kodecustomer,
                    label: buildCustomerOptionLabel(customer),
                    selected: currentSelectedCustomer !== '' && customer.kodecustomer === currentSelectedCustomer
                });
            });
            choiceInstance.setChoices(options, 'value', 'label', true);
            if (currentSelectedCustomer) {
                choiceInstance.setChoiceByValue(currentSelectedCustomer);
            } else {
                choiceInstance.removeActiveItems();
            }
        } else {
            customerSelect.innerHTML = '';
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = 'Pilih Customer';
            placeholderOption.selected = currentSelectedCustomer === '';
            customerSelect.appendChild(placeholderOption);
            list.forEach(function(customer) {
                const option = document.createElement('option');
                option.value = customer.kodecustomer;
                option.textContent = buildCustomerOptionLabel(customer);
                if (currentSelectedCustomer && customer.kodecustomer === currentSelectedCustomer) {
                    option.selected = true;
                }
                customerSelect.appendChild(option);
            });
        }

        customerSelect.value = currentSelectedCustomer;
    }

    refreshCustomerOptions(true);

    statusSelect.addEventListener('change', function() {
        refreshCustomerOptions(false);
    });

    customerSelect.addEventListener('change', function() {
        currentSelectedCustomer = customerSelect.value;
    });
}

function initOrderCreateForm() {
    document.querySelectorAll('.js-choice-customer').forEach((select) => {
        if (typeof Choices !== 'undefined' && !select.dataset.choicesInitialized) {
            const choice = new Choices(select, {
                searchEnabled: true,
                searchResultLimit: 100,
                searchPlaceholderValue: 'Ketik untuk mencari customer...',
                shouldSort: false,
                itemSelectText: '',
                noResultsText: 'Customer tidak ditemukan'
            });
            customerChoiceInstances.push(choice);
            select.dataset.choicesInitialized = '1';
        }
    });

    const orderForm = document.getElementById('orderForm');
    const customerSelect = document.querySelector('select[name="kodecustomer"]');
    const statusSelect = document.getElementById('statusPkpSelect');
    const detailModalElement = document.getElementById('detailModal');
    const modalTitle = document.getElementById('detailModalLabel');
    const detailForm = document.getElementById('detailForm');
    const modalBarangSelect = document.getElementById('modalBarang');
    const modalJumlahInput = document.getElementById('modalJumlah');
    const modalHargaInput = document.getElementById('modalHarga');
    const modalDiscountInput = document.getElementById('modalDiscount');
    const modalTotalInput = document.getElementById('modalTotal');
    const modalSatuanDisplay = document.getElementById('modalSatuan');
    modalStokInfoEl = document.getElementById('modalStokInfo');
    const openStockModalBtn = document.getElementById('openStockPriceModal');
    const stockModalElement = document.getElementById('stockPriceModal');
    const stockSearchInput = document.getElementById('stockSearchInput');
    const stockFactoryFilter = document.getElementById('stockFactoryFilter');
    const stockGroupFilter = document.getElementById('stockGroupFilter');
    const stockStockFilter = document.getElementById('stockStockFilter');
    const stockTableBody = document.getElementById('stockTableBody');
    const stockPagination = document.getElementById('stockPagination');
    const stockPageSizeSelect = document.getElementById('stockPageSize');
    let stockPriceModalInstance = null;
    const stockTableState = {
        allItems: Array.isArray(barangList) ? [...barangList] : [],
        filtered: [],
        currentPage: 1,
        pageSize: parseInt(stockPageSizeSelect?.value ?? '10', 10),
        totalPages: 1,
        filterFactory: '',
        filterGroup: '',
        filterStock: 'all',
        searchQuery: ''
    };
    stockTableState.filtered = [...stockTableState.allItems];
    const addDetailBtn = document.getElementById('addDetailBtn');
    const detailTableBody = document.getElementById('detailTableBody');
    const detailEmptyState = document.getElementById('detailEmptyState');
    const grandTotalDisplay = document.getElementById('grandTotalDisplay');
    const grandTotalHidden = document.getElementById('grandTotalHidden');

    if (detailModalElement) {
        detailModalInstance = new bootstrap.Modal(detailModalElement);
    }

    if (typeof Choices !== 'undefined' && modalBarangSelect) {
        barangChoiceInstance = new Choices(modalBarangSelect, {
            searchEnabled: true,
            searchResultLimit: 100,
            searchPlaceholderValue: 'Cari barang...',
            shouldSort: false,
            itemSelectText: '',
            noResultsText: 'Barang tidak ditemukan'
        });
    }

    setupCustomerStatusFilter(customerSelect, statusSelect, orderCustomersByStatus);

    const modalElements = {
        modal: detailModalInstance,
        detailForm,
        modalBarangSelect,
        modalJumlahInput,
        modalHargaInput,
        modalDiscountInput,
        modalTotalInput,
        modalTitle,
        modalSatuanDisplay
    };

    function normalizeFilterKey(value) {
        const trimmed = (value ?? '').toString().trim();
        return trimmed === '' ? '__EMPTY__' : trimmed;
    }

    function populateStockFilters() {
        if (stockFactoryFilter) {
            const previousValue = stockTableState.filterFactory;
            const optionsMap = new Map();
            stockTableState.allItems.forEach((item) => {
                const key = normalizeFilterKey(item.kodepabrik);
                if (!optionsMap.has(key)) {
                    let label = (item.namapabrik ?? '').toString().trim();
                    const rawCode = (item.kodepabrik ?? '').toString().trim();
                    if (!label) {
                        label = rawCode || 'Tanpa Pabrik';
                    }
                    optionsMap.set(key, label);
                }
            });
            const sortedOptions = [...optionsMap.entries()]
                .filter(([key]) => key !== '__EMPTY__')
                .sort((a, b) => a[1].localeCompare(b[1], 'id', { sensitivity: 'base' }));
            const hasEmptyOption = optionsMap.has('__EMPTY__');

            stockFactoryFilter.innerHTML = '<option value="">Semua Pabrik</option>';
            if (hasEmptyOption) {
                const option = document.createElement('option');
                option.value = '__EMPTY__';
                option.textContent = 'Tanpa Pabrik';
                stockFactoryFilter.appendChild(option);
            }
            sortedOptions.forEach(([value, label]) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = label;
                stockFactoryFilter.appendChild(option);
            });
            stockFactoryFilter.value = previousValue;
            if (stockFactoryFilter.value !== previousValue) {
                stockFactoryFilter.value = '';
                stockTableState.filterFactory = '';
            }
        }

        if (stockGroupFilter) {
            const previousValue = stockTableState.filterGroup;
            const optionsMap = new Map();
            stockTableState.allItems.forEach((item) => {
                const key = normalizeFilterKey(item.kodegolongan);
                if (!optionsMap.has(key)) {
                    let label = (item.namagolongan ?? '').toString().trim();
                    const rawCode = (item.kodegolongan ?? '').toString().trim();
                    if (!label) {
                        label = rawCode || 'Tanpa Golongan';
                    }
                    optionsMap.set(key, label);
                }
            });
            const sortedOptions = [...optionsMap.entries()]
                .filter(([key]) => key !== '__EMPTY__')
                .sort((a, b) => a[1].localeCompare(b[1], 'id', { sensitivity: 'base' }));
            const hasEmptyOption = optionsMap.has('__EMPTY__');

            stockGroupFilter.innerHTML = '<option value="">Semua Golongan</option>';
            if (hasEmptyOption) {
                const option = document.createElement('option');
                option.value = '__EMPTY__';
                option.textContent = 'Tanpa Golongan';
                stockGroupFilter.appendChild(option);
            }
            sortedOptions.forEach(([value, label]) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = label;
                stockGroupFilter.appendChild(option);
            });
            stockGroupFilter.value = previousValue;
            if (stockGroupFilter.value !== previousValue) {
                stockGroupFilter.value = '';
                stockTableState.filterGroup = '';
            }
        }

        if (stockStockFilter) {
            stockStockFilter.value = stockTableState.filterStock || 'all';
        }
    }

    function applyStockFilters() {
        const query = (stockTableState.searchQuery || '').trim().toLowerCase();
        const factoryFilter = stockTableState.filterFactory;
        const groupFilter = stockTableState.filterGroup;
        const stockFilter = stockTableState.filterStock || 'all';

        stockTableState.filtered = stockTableState.allItems.filter((item) => {
            const kode = (item.kodebarang ?? '').toString().toLowerCase();
            const nama = (item.namabarang ?? '').toString().toLowerCase();
            const satuan = (item.satuan ?? '').toString().toLowerCase();
            const pabrikName = (item.namapabrik ?? '').toString().toLowerCase();
            const pabrikCode = (item.kodepabrik ?? '').toString().toLowerCase();
            const golonganName = (item.namagolongan ?? '').toString().toLowerCase();
            const golonganCode = (item.kodegolongan ?? '').toString().toLowerCase();

            const matchesQuery =
                !query ||
                kode.includes(query) ||
                nama.includes(query) ||
                satuan.includes(query) ||
                pabrikName.includes(query) ||
                pabrikCode.includes(query) ||
                golonganName.includes(query) ||
                golonganCode.includes(query);

            if (!matchesQuery) {
                return false;
            }

            if (factoryFilter && normalizeFilterKey(item.kodepabrik) !== factoryFilter) {
                return false;
            }

            if (groupFilter && normalizeFilterKey(item.kodegolongan) !== groupFilter) {
                return false;
            }

            if (stockFilter !== 'all') {
                let stokValue = item.stokakhir;
                if (typeof stokValue === 'string') {
                    stokValue = stokValue.trim();
                }
                let stokNumber = null;
                if (stokValue !== null && stokValue !== undefined && stokValue !== '') {
                    stokNumber = parseFloat(String(stokValue).replace(',', '.'));
                    if (!Number.isFinite(stokNumber)) {
                        stokNumber = null;
                    }
                }
                if (stockFilter === 'available') {
                    if (!(stokNumber !== null && stokNumber > 0)) {
                        return false;
                    }
                } else if (stockFilter === 'empty') {
                    if (!(stokNumber !== null && stokNumber === 0)) {
                        return false;
                    }
                }
            }

            return true;
        });
    }

    function renderStockTable() {
        if (!stockTableBody) {
            return;
        }
        const pageSize = parseInt(stockPageSizeSelect?.value ?? stockTableState.pageSize ?? '10', 10);
        stockTableState.pageSize = pageSize > 0 ? pageSize : 10;
        const totalItems = Array.isArray(stockTableState.filtered) ? stockTableState.filtered.length : 0;
        const totalPages = Math.max(Math.ceil(totalItems / stockTableState.pageSize) || 1, 1);
        stockTableState.totalPages = totalPages;
        if (stockTableState.currentPage > totalPages) {
            stockTableState.currentPage = totalPages;
        }
        if (stockTableState.currentPage < 1) {
            stockTableState.currentPage = 1;
        }
        const startIndex = (stockTableState.currentPage - 1) * stockTableState.pageSize;
        const pageItems = stockTableState.filtered.slice(startIndex, startIndex + stockTableState.pageSize);
        stockTableBody.innerHTML = pageItems
            .map((item, idx) => {
                const globalIndex = startIndex + idx;
                const nama = escapeHtml(item.namabarang ?? '');
                const pabrik = escapeHtml(item.namapabrik ?? '');
                const golongan = escapeHtml(item.namagolongan ?? '');
                const satuan = escapeHtml(item.satuan ?? '-');
                const harga = parseFloat(item.hargajual ?? 0);
                const diskon = parseFloat(item.discountjual ?? 0);
                const stok = item.stokakhir !== undefined && item.stokakhir !== null ? parseFloat(item.stokakhir) : null;
                const kodeBarang = escapeHtml(item.kodebarang ?? '');
                return `
                    <tr class="stock-info-row" data-index="${globalIndex}" role="button" data-kode="${kodeBarang}">
                        <td><span class="fw-semibold">${nama}</span></td>
                        <td>${pabrik || '-'}</td>
                        <td>${golongan || '-'}</td>
                        <td>${satuan}</td>
                        <td class="text-end">${integerFormatter.format(Math.round(harga))}</td>
                        <td class="text-end">${Number.isFinite(diskon) ? diskon.toFixed(2).replace('.', ',') : '0,00'} %</td>
                        <td class="text-end">${stok !== null && Number.isFinite(stok) ? formatQty(stok) : '-'}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-primary btn-sm btn-order-item" data-kode="${kodeBarang}" data-nama="${escapeHtml(item.namabarang ?? '')}" data-harga="${harga}" data-discount="${diskon}" data-stok="${stok !== null && Number.isFinite(stok) ? stok : 0}" data-satuan="${escapeHtml(item.satuan ?? '-')}" title="Tambah ke Order">
                                Order
                            </button>
                        </td>
                    </tr>
                `;
            })
            .join('');

        if (stockPagination) {
            if (totalPages <= 1) {
                stockPagination.innerHTML = '';
            } else {
                const maxLinks = 3;
                const half = Math.floor(maxLinks / 2);
                let start = Math.max(1, stockTableState.currentPage - half);
                let end = Math.min(totalPages, start + maxLinks - 1);
                if (end - start + 1 < maxLinks) {
                    start = Math.max(1, end - maxLinks + 1);
                }
                let html = '';
                const addLink = (page, label, disabled = false) => {
                    const safePage = Math.min(Math.max(page, 1), totalPages);
                    html += `
                        <li class="page-item ${disabled ? 'disabled' : ''} ${stockTableState.currentPage === page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${safePage}">${label}</a>
                        </li>`;
                };
                addLink(stockTableState.currentPage - 1, 'Previous', stockTableState.currentPage <= 1);
                if (start > 1) {
                    addLink(1, '1');
                    if (start > 2) {
                        html += '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                    }
                }
                for (let i = start; i <= end; i += 1) {
                    addLink(i, String(i));
                }
                if (end < totalPages) {
                    if (end < totalPages - 1) {
                        html += '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                    }
                    addLink(totalPages, String(totalPages));
                }
                addLink(stockTableState.currentPage + 1, 'Next', stockTableState.currentPage >= totalPages);
                stockPagination.innerHTML = html;
            }
        }
    }

    function buildStockDetailMessage(item) {
        if (!item) {
            return '<div class="text-start"><em>Data barang tidak ditemukan.</em></div>';
        }

        const hargaValue = parseFloat(item.hargajual ?? 0);
        const discountValue = parseFloat(item.discountjual ?? 0);
        const stokValue =
            item.stokakhir !== undefined && item.stokakhir !== null
                ? formatQty(item.stokakhir)
                : '-';

        const detailPairs = [
            ['Kode Barang', item.kodebarang ?? '-'],
            ['Nama Barang', item.namabarang ?? '-'],
            ['Satuan', item.satuan ?? '-'],
            ['Pabrik', item.namapabrik ?? '-'],
            ['Golongan', item.namagolongan ?? '-'],
            ['Kandungan', item.kandungan ?? '-'],
            ['OOT', item.oot ?? '-'],
            ['Prekursor', item.prekursor ?? '-'],
            ['NIE', item.nie ?? '-'],
            ['Harga Jual', Number.isFinite(hargaValue) ? `Rp ${integerFormatter.format(Math.round(hargaValue))}` : '-'],
            [
                'Discount',
                Number.isFinite(discountValue)
                    ? `${discountValue.toFixed(2).replace('.', ',')} %`
                    : '-'
            ],
            ['Stok Akhir', stokValue]
        ];

        const detailHtml = detailPairs
            .map(([label, value]) => {
                const safeLabel = escapeHtml(label);
                const safeValue = escapeHtml(value === null || value === undefined || value === '' ? '-' : String(value));
                return `<div class="mb-1"><strong>${safeLabel}</strong>: ${safeValue}</div>`;
            })
            .join('');

        return `<div class="text-start">${detailHtml}</div>`;
    }

    function openStockPriceModal() {
        if (!stockModalElement || typeof bootstrap === 'undefined') {
            return;
        }
        if (!stockPriceModalInstance) {
            stockPriceModalInstance = new bootstrap.Modal(stockModalElement);
        }
        stockTableState.allItems = Array.isArray(barangList) ? [...barangList] : [];
        if (stockPageSizeSelect) {
            stockPageSizeSelect.value = String(stockTableState.pageSize);
        }
        if (stockSearchInput) {
            stockSearchInput.value = stockTableState.searchQuery;
        }
        populateStockFilters();
        applyStockFilters();
        stockTableState.currentPage = 1;
        renderStockTable();
        stockPriceModalInstance.show();
    }

    openStockModalBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        openStockPriceModal();
    });

    stockTableBody?.addEventListener('click', (event) => {
        // Handle tombol Order
        const orderBtn = event.target.closest('.btn-order-item');
        if (orderBtn) {
            event.preventDefault();
            event.stopPropagation();
            
            const kodeBarang = orderBtn.dataset.kode;
            if (!kodeBarang) {
                return;
            }
            
            // Tutup modal stok
            if (stockPriceModalInstance) {
                stockPriceModalInstance.hide();
            }
            
            // Buka modal tambah barang dengan kode barang dari stok
            rawHarga = 0;
            rawDiscount = 0;
            openDetailModal(modalElements, null, kodeBarang);
            
            // Fallback: Pastikan nilai ter-set setelah modal benar-benar terbuka
            const detailModalEl = document.getElementById('detailModal');
            const handleModalShown = () => {
                setTimeout(() => {
                    const modalBarangSelect = document.getElementById('modalBarang');
                    if (modalBarangSelect && modalBarangSelect.value !== kodeBarang) {
                        // Jika nilai belum ter-set, set ulang
                        if (barangChoiceInstance && typeof barangChoiceInstance.setChoiceByValue === 'function') {
                            try {
                                barangChoiceInstance.setChoiceByValue(kodeBarang);
                                setTimeout(() => {
                                    const changeEvent = new Event('change', { bubbles: true });
                                    modalBarangSelect.dispatchEvent(changeEvent);
                                }, 100);
                            } catch (e) {
                                modalBarangSelect.value = kodeBarang;
                                const changeEvent = new Event('change', { bubbles: true });
                                modalBarangSelect.dispatchEvent(changeEvent);
                            }
                        } else {
                            modalBarangSelect.value = kodeBarang;
                            const changeEvent = new Event('change', { bubbles: true });
                            modalBarangSelect.dispatchEvent(changeEvent);
                        }
                    }
                }, 300);
            };
            
            if (detailModalEl) {
                detailModalEl.addEventListener('shown.bs.modal', handleModalShown, { once: true });
            }
            
            return;
        }
        
        // Handle klik row untuk menampilkan detail (jika bukan tombol)
        const row = event.target.closest('tr[data-index]');
        if (!row || event.target.closest('button')) {
            return;
        }
        const index = parseInt(row.dataset.index, 10);
        if (Number.isNaN(index) || index < 0 || index >= stockTableState.filtered.length) {
            return;
        }
        const item = stockTableState.filtered[index];
        showAlert({
            title: 'Informasi Barang',
            message: buildStockDetailMessage(item),
            buttonText: 'Tutup',
            buttonClass: 'btn-primary'
        });
    });

    stockSearchInput?.addEventListener('input', () => {
        stockTableState.searchQuery = stockSearchInput.value || '';
        stockTableState.currentPage = 1;
        applyStockFilters();
        renderStockTable();
    });

    stockFactoryFilter?.addEventListener('change', () => {
        stockTableState.filterFactory = stockFactoryFilter.value;
        stockTableState.currentPage = 1;
        applyStockFilters();
        renderStockTable();
    });

    stockGroupFilter?.addEventListener('change', () => {
        stockTableState.filterGroup = stockGroupFilter.value;
        stockTableState.currentPage = 1;
        applyStockFilters();
        renderStockTable();
    });

    stockStockFilter?.addEventListener('change', () => {
        const value = stockStockFilter.value || 'all';
        stockTableState.filterStock = value;
        stockTableState.currentPage = 1;
        applyStockFilters();
        renderStockTable();
    });

    stockPageSizeSelect?.addEventListener('change', () => {
        const parsed = parseInt(stockPageSizeSelect.value ?? '10', 10);
        stockTableState.pageSize = Number.isFinite(parsed) && parsed > 0 ? parsed : 10;
        stockTableState.currentPage = 1;
        renderStockTable();
    });

    stockPagination?.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-page]');
        if (!link) {
            return;
        }
        event.preventDefault();
        const li = link.closest('.page-item');
        if (li && li.classList.contains('disabled')) {
            return;
        }
        const targetPage = parseInt(link.dataset.page, 10);
        if (Number.isNaN(targetPage) || targetPage === stockTableState.currentPage) {
            return;
        }
        stockTableState.currentPage = Math.min(
            Math.max(targetPage, 1),
            stockTableState.totalPages
        );
        renderStockTable();
    });

    renderDetailTable(detailTableBody, detailEmptyState, grandTotalDisplay, grandTotalHidden);

    orderForm?.addEventListener('submit', (event) => {
        if (!customerSelect?.value) {
            event.preventDefault();
            showAlert({
                title: 'Validasi Order',
                message: 'Customer harus dipilih sebelum menyimpan order.',
                buttonText: 'Mengerti'
            });
            customerSelect?.focus();
            return;
        }
        if (!orderDetails.length) {
            event.preventDefault();
            showAlert({
                title: 'Validasi Order',
                message: 'Minimal satu barang harus ditambahkan sebelum menyimpan order.',
                buttonText: 'Mengerti'
            });
            addDetailBtn?.focus();
            return;
        }
    });

    addDetailBtn?.addEventListener('click', () => {
        rawHarga = 0;
        rawDiscount = 0;
        openDetailModal(modalElements, null);
    });

    modalBarangSelect?.addEventListener('change', () => {
        const kode = modalBarangSelect.value;
        const info = getBarangInfo(kode);
        const selectedOption = modalBarangSelect.selectedOptions ? modalBarangSelect.selectedOptions[0] : null;
        const isEditingSameItem = currentEditIndex !== null && orderDetails[currentEditIndex]?.kodebarang === kode;
        if (info && info.hargajual && !isEditingSameItem) {
            rawHarga = parseFloat(String(info.hargajual).replace(/[^0-9.]/g, '')) || 0;
        }
        if (info && Object.prototype.hasOwnProperty.call(info, 'discountjual') && !isEditingSameItem) {
            rawDiscount = parseFloat(String(info.discountjual).replace(/[^0-9.]/g, '')) || 0;
        }
        modalSatuanDisplay.textContent = info?.satuan || '-';
        const qtySanitizedSelect = sanitizeIntegerInput(modalJumlahInput.value);
        const qty = qtySanitizedSelect === '' ? 0 : parseFloat(qtySanitizedSelect);
        const harga = rawHarga;
        const discountPercent = rawDiscount;
        const effectiveHarga = Math.max(harga * (1 - discountPercent / 100), 0);
        const total = Math.max(qty * effectiveHarga, 0);
        modalHargaInput.value = rawHarga ? integerFormatter.format(Math.round(rawHarga)) : '0';
        modalDiscountInput.value = discountPercent.toFixed(2).replace('.', ',');
        modalTotalInput.value = integerFormatter.format(Math.round(total));
        modalTotalInput.dataset.rawTotal = total.toFixed(2);
        const stokAkhir = getAvailableStock(selectedOption);
        currentSelectedStock = stokAkhir;
        updateStokInfoDisplay(stokAkhir);

        if (stokAkhir !== null && stokAkhir <= 0) {
            showAlert({
                title: 'Stok Kosong',
                message: `Stok untuk ${escapeHtml(selectedOption?.dataset.nama ?? kode)} kosong.`,
                buttonText: 'Mengerti'
            });
            resetDetailForm(detailForm, modalJumlahInput, modalHargaInput, modalDiscountInput, modalTotalInput, modalSatuanDisplay);
            return;
        }

        if (stokAkhir !== null && qty > stokAkhir) {
            showAlert({
                title: 'Validasi Stok',
                message: `Jumlah tidak boleh melebihi stok (${formatQty(stokAkhir)}).`,
                buttonText: 'Mengerti'
            });
            const adjustedQty = Math.max(Math.floor(stokAkhir), 0);
            modalJumlahInput.value = adjustedQty > 0 ? String(adjustedQty) : '0';
            modalJumlahInput.dispatchEvent(new Event('input'));
        }
    });

    function sanitizeIntegerInput(value) {
        const normalized = value.replace(/[^0-9]/g, '');
        if (normalized === '') {
            return '';
        }
        return normalized.replace(/^0+(?=\d)/, '');
    }

    [modalJumlahInput, modalHargaInput, modalDiscountInput].forEach((input) => {
        input?.addEventListener('input', () => {
        const qtySanitized = sanitizeIntegerInput(modalJumlahInput.value);
        let qty = qtySanitized === '' ? 0 : parseFloat(qtySanitized);
            if (input === modalJumlahInput) {
                if (modalJumlahInput === document.activeElement) {
                    modalJumlahInput.value = qtySanitized;
                }
            }
            if (input === modalHargaInput) {
                const sanitizedHarga = sanitizeIntegerInput(modalHargaInput.value);
                if (sanitizedHarga === '') {
                    rawHarga = 0;
                    if (modalHargaInput === document.activeElement) {
                        modalHargaInput.value = '';
                    }
                } else {
                    rawHarga = parseFloat(sanitizedHarga) || 0;
                    if (modalHargaInput === document.activeElement) {
                        modalHargaInput.value = sanitizedHarga;
                    }
                }
            }
            if (input === modalDiscountInput) {
                const { raw, display } = parseDiscountInput(modalDiscountInput.value, false);
                rawDiscount = raw;
                if (modalDiscountInput === document.activeElement) {
                    modalDiscountInput.value = display;
                }
            }
        const harga = rawHarga;
        const discountPercent = rawDiscount;
        if (currentSelectedStock !== null && qty > currentSelectedStock) {
            showAlert({
                title: 'Validasi Stok',
                message: `Jumlah tidak boleh melebihi stok (${formatQty(currentSelectedStock)}).`,
                buttonText: 'Mengerti'
            });
            qty = Math.max(Math.floor(currentSelectedStock), 0);
            modalJumlahInput.value = qty > 0 ? String(qty) : '0';
        }
            const effectiveHarga = Math.max(harga * (1 - discountPercent / 100), 0);
            const total = Math.max(qty * effectiveHarga, 0);
            modalHargaInput.value = rawHarga ? integerFormatter.format(Math.round(rawHarga)) : '0';
            modalTotalInput.value = integerFormatter.format(Math.round(total));
            modalTotalInput.dataset.rawTotal = total.toFixed(2);
        if (modalBarangSelect) {
            const selectedOption = modalBarangSelect.selectedOptions ? modalBarangSelect.selectedOptions[0] : null;
            const stokAkhir = getAvailableStock(selectedOption);
            currentSelectedStock = stokAkhir;
            updateStokInfoDisplay(stokAkhir);
        }
        });
    });

    modalDiscountInput?.addEventListener('blur', () => {
        const parsed = parseDiscountInput(modalDiscountInput.value, true);
        rawDiscount = parsed.raw;
        modalDiscountInput.value = parsed.display;
    });

    modalHargaInput?.addEventListener('blur', () => {
        modalHargaInput.value = rawHarga ? integerFormatter.format(Math.round(rawHarga)) : '0';
    });

    modalJumlahInput?.addEventListener('blur', () => {
        const qtySanitized = sanitizeIntegerInput(modalJumlahInput.value);
        modalJumlahInput.value = qtySanitized === '' ? '0' : qtySanitized;
    });

    modalJumlahInput?.addEventListener('focus', () => {
        modalJumlahInput.select();
    });

    modalHargaInput?.addEventListener('focus', () => {
        modalHargaInput.value = rawHarga ? String(Math.round(rawHarga)) : '';
        modalHargaInput.select();
    });

    modalDiscountInput?.addEventListener('focus', () => {
        const focusDisplay = parseDiscountInput(rawDiscount.toString().replace('.', ','), false).display;
        modalDiscountInput.value = focusDisplay;
        modalDiscountInput.select();
    });

    detailForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        const kode = modalBarangSelect.value.trim();
        if (!kode) {
            modalBarangSelect.focus();
            return;
        }

        const jumlahSanitized = sanitizeIntegerInput(modalJumlahInput.value);
        const jumlah = jumlahSanitized === '' ? 0 : parseFloat(jumlahSanitized);
        if (jumlah <= 0) {
            modalJumlahInput.focus();
            return;
        }

        const harga = rawHarga;
        const discountPercent = rawDiscount;
        const effectiveHarga = Math.max(harga * (1 - discountPercent / 100), 0);
        const total = Math.max(jumlah * effectiveHarga, 0);
        const barangInfo = getBarangInfo(kode);
        const selectedOption = modalBarangSelect.selectedOptions ? modalBarangSelect.selectedOptions[0] : null;
        const namaBarang = barangInfo?.namabarang || selectedOption?.dataset.nama || selectedOption?.textContent || kode;
        const satuanBarang = barangInfo?.satuan || selectedOption?.dataset.satuan || '';
        const stokAkhir = currentSelectedStock !== null ? currentSelectedStock : getAvailableStock(selectedOption);
        if (stokAkhir !== null && jumlah > stokAkhir) {
            showAlert({
                title: 'Jumlah Melebihi Stok',
                message: `Jumlah yang diinput (${formatQty(jumlah)}${satuanBarang ? ' ' + escapeHtml(satuanBarang) : ''}) melebihi stok tersedia (${formatQty(stokAkhir)}).`,
                buttonText: 'Mengerti'
            });
            modalJumlahInput.focus();
            return;
        }

        const rowData = {
            kodebarang: kode,
            namabarang: namaBarang,
            satuan: satuanBarang,
            jumlah,
            harga,
            discount: discountPercent,
            total
        };

        if (currentEditIndex !== null) {
            orderDetails[currentEditIndex] = {
                ...rowData
            };
        } else {
            orderDetails.push({
                ...rowData
            });
        }

        renderDetailTable(detailTableBody, detailEmptyState, grandTotalDisplay, grandTotalHidden);
        detailModalInstance?.hide();
    });

    const detailCardsContainer = document.getElementById('detailCardsContainer');
    
    function handleDetailAction(event) {
        const button = event.target.closest('button[data-action]');
        if (!button) {
            return;
        }
        const index = parseInt(button.dataset.index, 10);
        if (Number.isNaN(index)) {
            return;
        }
        const action = button.dataset.action;
        if (action === 'edit') {
            openDetailModal(modalElements, index);
        } else if (action === 'remove') {
            const item = orderDetails[index];
            const namaBarang = item?.namabarang || 'barang ini';
            showConfirmModal({
                title: 'Konfirmasi Hapus',
                message: `Apakah Anda yakin ingin menghapus <strong>${escapeHtml(namaBarang)}</strong> dari daftar barang?`,
                buttonText: 'Hapus',
                buttonClass: 'btn-danger',
                onConfirm: function() {
                    orderDetails.splice(index, 1);
                    renderDetailTable(detailTableBody, detailEmptyState, grandTotalDisplay, grandTotalHidden);
                }
            });
        }
    }

    detailTableBody?.addEventListener('click', handleDetailAction);
    detailCardsContainer?.addEventListener('click', handleDetailAction);

    detailModalElement?.addEventListener('hidden.bs.modal', () => {
        currentEditIndex = null;
        resetDetailForm(
            detailForm,
            modalJumlahInput,
            modalHargaInput,
            modalDiscountInput,
            modalTotalInput,
            modalSatuanDisplay
        );
    });
}

// File upload handling
const orderFilesInput = document.getElementById('orderFilesInput');
const fileList = document.getElementById('fileList');
const maxFiles = 5;
const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function removeFileFromList(fileIndex) {
    if (!orderFilesInput) return;
    
    const files = Array.from(orderFilesInput.files);
    if (fileIndex < 0 || fileIndex >= files.length) return;
    
    // Create new FileList without the removed file
    const dataTransfer = new DataTransfer();
    files.forEach((file, index) => {
        if (index !== fileIndex) {
            dataTransfer.items.add(file);
        }
    });
    
    // Update the input's files
    orderFilesInput.files = dataTransfer.files;
    
    // Refresh the file list display
    updateFileList();
}

function updateFileList() {
    if (!orderFilesInput || !fileList) return;
    
    const files = Array.from(orderFilesInput.files);
    fileList.innerHTML = '';
    
    if (files.length === 0) {
        return;
    }
    
    if (files.length > maxFiles) {
        fileList.innerHTML = `<div class="alert alert-danger">Maksimal ${maxFiles} file yang dapat diupload</div>`;
        orderFilesInput.value = '';
        return;
    }
    
    const ul = document.createElement('ul');
    ul.className = 'list-group list-group-flush';
    
    files.forEach((file, index) => {
        if (file.size > maxFileSize) {
            fileList.innerHTML = `<div class="alert alert-danger">File "${file.name}" terlalu besar (maksimal 5MB)</div>`;
            orderFilesInput.value = '';
            return;
        }
        
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML = `
            <div class="d-flex align-items-center flex-grow-1">
                <span class="me-2">${file.name}</span>
                <span class="badge bg-secondary">${formatFileSize(file.size)}</span>
            </div>
            <button type="button" class="btn btn-sm btn-danger ms-2 remove-file-btn" data-file-index="${index}" data-file-name="${file.name}">
                <img src="<?= htmlspecialchars($baseUrl) ?>/assets/icons/trash-can.svg" alt="trash-can" width="14" height="14" class="icon-inline me-1 mb-1"> Hapus
            </button>
        `;
        ul.appendChild(li);
    });
    
    fileList.appendChild(ul);
    
    // Add event listeners to remove buttons
    const removeButtons = fileList.querySelectorAll('.remove-file-btn');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const fileIndex = parseInt(this.getAttribute('data-file-index'));
            const fileName = this.getAttribute('data-file-name');
            
            showConfirmModal({
                title: 'Konfirmasi Hapus',
                message: `Apakah Anda yakin ingin menghapus file <strong>${fileName}</strong> dari daftar upload?`,
                buttonText: 'Hapus',
                buttonClass: 'btn-danger',
                onConfirm: function() {
                    removeFileFromList(fileIndex);
                }
            });
        });
    });
}

orderFilesInput?.addEventListener('change', updateFileList);

// Camera functionality
document.addEventListener('DOMContentLoaded', function() {
    // Detect mobile device
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                     (window.innerWidth <= 768 && 'ontouchstart' in window);
    
    const cameraModal = document.getElementById('cameraModal');
    const btnOpenCamera = document.getElementById('btnOpenCamera');
    const btnMobileCamera = document.getElementById('btnMobileCamera');
    const fileInputMobile = document.getElementById('orderFilesMobile');
    const btnStartCamera = document.getElementById('btnStartCamera');
    const btnCapturePhoto = document.getElementById('btnCapturePhoto');
    const btnRetakePhoto = document.getElementById('btnRetakePhoto');
    const btnUsePhoto = document.getElementById('btnUsePhoto');
    const btnCloseCamera = document.getElementById('btnCloseCamera');
    const btnCancelCamera = document.getElementById('btnCancelCamera');
    const videoPreview = document.getElementById('videoPreview');
    const canvasCapture = document.getElementById('canvasCapture');
    const cameraPlaceholder = document.getElementById('cameraPlaceholder');
    const cameraError = document.getElementById('cameraError');
    const fileInput = document.getElementById('orderFilesInput');
    
    let stream = null;
    let capturedImage = null;
    
    const hasGetUserMedia = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    
    // Mobile device handling
    if (isMobile) {
        if (btnMobileCamera) btnMobileCamera.classList.remove('d-none');
        if (btnOpenCamera) btnOpenCamera.style.display = 'none';
        
        if (btnMobileCamera && fileInputMobile) {
            btnMobileCamera.addEventListener('click', function() {
                fileInputMobile.click();
            });
            
            fileInputMobile.addEventListener('change', function(e) {
                if (e.target.files && e.target.files.length > 0) {
                    const dataTransfer = new DataTransfer();
                    if (fileInput.files) {
                        for (let i = 0; i < fileInput.files.length; i++) {
                            dataTransfer.items.add(fileInput.files[i]);
                        }
                    }
                    for (let i = 0; i < e.target.files.length; i++) {
                        dataTransfer.items.add(e.target.files[i]);
                    }
                    fileInput.files = dataTransfer.files;
                    const changeEvent = new Event('change', { bubbles: true });
                    fileInput.dispatchEvent(changeEvent);
                    fileInputMobile.value = '';
                }
            });
        }
    } else {
        if (btnMobileCamera) btnMobileCamera.classList.add('d-none');
        if (btnOpenCamera) btnOpenCamera.style.display = 'inline-block';
        if (!hasGetUserMedia && btnOpenCamera) {
            btnOpenCamera.disabled = true;
            btnOpenCamera.title = 'Browser tidak mendukung akses kamera';
        }
    }
    
    function resetCameraUI() {
        videoPreview.style.display = 'none';
        cameraPlaceholder.style.display = 'block';
        cameraPlaceholder.innerHTML = '<p class="text-muted mb-0">Klik tombol "Mulai Kamera" untuk mengaktifkan kamera</p>';
        btnStartCamera.style.display = hasGetUserMedia ? 'inline-block' : 'none';
        btnCapturePhoto.style.display = 'none';
        btnRetakePhoto.style.display = 'none';
        btnUsePhoto.style.display = 'none';
        cameraError.classList.add('d-none');
        capturedImage = null;
    }
    
    function showCameraError(message) {
        cameraError.textContent = message;
        cameraError.classList.remove('d-none');
    }
    
    function hideCameraError() {
        cameraError.classList.add('d-none');
    }
    
    function stopStream() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }
    
    function stopCamera() {
        stopStream();
        resetCameraUI();
    }
    
    async function startCamera() {
        try {
            hideCameraError();
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: false
            });
            videoPreview.srcObject = stream;
            videoPreview.style.display = 'block';
            cameraPlaceholder.style.display = 'none';
            btnStartCamera.style.display = 'none';
            btnCapturePhoto.style.display = 'inline-block';
            videoPreview.addEventListener('loadedmetadata', function() {
                canvasCapture.width = videoPreview.videoWidth;
                canvasCapture.height = videoPreview.videoHeight;
            }, { once: true });
        } catch (error) {
            console.error('Error accessing camera:', error);
            let errorMessage = 'Gagal mengakses kamera. ';
            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                errorMessage += 'Izin akses kamera ditolak. Silakan berikan izin di pengaturan browser.';
            } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
                errorMessage += 'Kamera tidak ditemukan.';
            } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
                errorMessage += 'Kamera sedang digunakan oleh aplikasi lain.';
            } else {
                errorMessage += error.message || 'Terjadi kesalahan.';
            }
            showCameraError(errorMessage);
            resetCameraUI();
        }
    }
    
    function capturePhoto() {
        if (!stream || !videoPreview.videoWidth) {
            showCameraError('Kamera belum siap. Tunggu sebentar.');
            return;
        }
        try {
            const ctx = canvasCapture.getContext('2d');
            ctx.drawImage(videoPreview, 0, 0, canvasCapture.width, canvasCapture.height);
            canvasCapture.toBlob(function(blob) {
                if (!blob) {
                    showCameraError('Gagal mengambil foto.');
                    return;
                }
                capturedImage = blob;
                stopStream();
                videoPreview.style.display = 'none';
                const img = document.createElement('img');
                img.src = URL.createObjectURL(blob);
                img.style.maxWidth = '100%';
                img.style.borderRadius = '8px';
                img.style.marginBottom = '10px';
                const previewContainer = document.createElement('div');
                previewContainer.innerHTML = '';
                previewContainer.appendChild(img);
                cameraPlaceholder.innerHTML = '';
                cameraPlaceholder.appendChild(previewContainer);
                cameraPlaceholder.style.display = 'block';
                btnCapturePhoto.style.display = 'none';
                btnRetakePhoto.style.display = 'inline-block';
                btnUsePhoto.style.display = 'inline-block';
            }, 'image/jpeg', 0.9);
        } catch (error) {
            console.error('Error capturing photo:', error);
            showCameraError('Gagal mengambil foto: ' + error.message);
        }
    }
    
    function usePhoto() {
        if (!capturedImage) {
            showCameraError('Tidak ada foto yang diambil.');
            return;
        }
        const timestamp = new Date().getTime();
        const filename = `camera_${timestamp}.jpg`;
        const file = new File([capturedImage], filename, { type: 'image/jpeg' });
        const dataTransfer = new DataTransfer();
        if (fileInput.files) {
            for (let i = 0; i < fileInput.files.length; i++) {
                dataTransfer.items.add(fileInput.files[i]);
            }
        }
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;
        const changeEvent = new Event('change', { bubbles: true });
        fileInput.dispatchEvent(changeEvent);
        const modal = bootstrap.Modal.getInstance(cameraModal);
        if (modal) modal.hide();
    }
    
    function retakePhoto() {
        resetCameraUI();
        startCamera();
    }
    
    if (cameraModal) {
        cameraModal.addEventListener('show.bs.modal', function() {
            resetCameraUI();
            if (!hasGetUserMedia) {
                btnStartCamera.style.display = 'none';
                showCameraError('Browser Anda tidak mendukung akses kamera. Silakan gunakan fitur upload file biasa.');
            }
        });
        cameraModal.addEventListener('hide.bs.modal', function() {
            stopCamera();
        });
    }
    
    if (btnStartCamera) btnStartCamera.addEventListener('click', startCamera);
    if (btnCapturePhoto) btnCapturePhoto.addEventListener('click', capturePhoto);
    if (btnRetakePhoto) btnRetakePhoto.addEventListener('click', retakePhoto);
    if (btnUsePhoto) btnUsePhoto.addEventListener('click', usePhoto);
    if (btnCloseCamera || btnCancelCamera) {
        const closeHandler = () => stopCamera();
        if (btnCloseCamera) btnCloseCamera.addEventListener('click', closeHandler);
        if (btnCancelCamera) btnCancelCamera.addEventListener('click', closeHandler);
    }
});

if (document.readyState === 'complete') {
    initOrderCreateForm();
} else {
    window.addEventListener('load', initOrderCreateForm);
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

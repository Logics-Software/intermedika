<?php
$title = 'Edit Penerimaan Piutang';
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
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/penerimaan">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/penerimaan">Transaksi Inkaso</a></li>
                    <li class="breadcrumb-item active">Edit Inkaso</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Inkaso <?= icon('caret-right', 'me-0 mb-1', 14)?> <?= htmlspecialchars($penerimaan['nopenerimaan']) ?></h4>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="/penerimaan/edit/<?= urlencode($penerimaan['nopenerimaan']) ?>" id="penerimaanForm">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6 col-lg-2">
                                <label class="form-label">Tanggal Inkaso <span class="text-danger">*</span></label>
                                <input type="date" name="tanggalpenerimaan" class="form-control" value="<?= htmlspecialchars($penerimaan['tanggalpenerimaan'] ?? date('Y-m-d')) ?>" required>
                            </div>
                            <div class="col-12 col-md-6 col-lg-2">
                                <label class="form-label">Status PKP <span class="text-danger">*</span></label>
                                <select name="statuspkp" class="form-select">
                                    <option value="pkp" <?= ($penerimaan['statuspkp'] ?? 'pkp') === 'pkp' ? 'selected' : '' ?>>PKP</option>
                                    <option value="nonpkp" <?= ($penerimaan['statuspkp'] ?? 'pkp') === 'nonpkp' ? 'selected' : '' ?>>Non PKP</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-2">
                                <label class="form-label">Jenis Inkaso <span class="text-danger">*</span></label>
                                <select name="jenispenerimaan" class="form-select" required>
                                    <option value="tunai" <?= ($penerimaan['jenispenerimaan'] ?? 'tunai') === 'tunai' ? 'selected' : '' ?>>Tunai</option>
                                    <option value="transfer" <?= ($penerimaan['jenispenerimaan'] ?? 'tunai') === 'transfer' ? 'selected' : '' ?>>Transfer</option>
                                    <option value="giro" <?= ($penerimaan['jenispenerimaan'] ?? 'tunai') === 'giro' ? 'selected' : '' ?>>Giro</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-6">
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
                                <select name="kodecustomer" id="kodecustomer" class="form-select js-choice-customer" data-selected="<?= htmlspecialchars($selectedCustomer ?? '') ?>">
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
                            <input type="hidden" name="kodesales" value="<?= htmlspecialchars($penerimaan['kodesales'] ?? '') ?>">
                        </div>

                        <div class="table-responsive penerimaan-detail-wrapper">
                            <table class="table table-bordered align-middle penerimaan-detail-table" id="detailTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="detail-col-penjualan">No.Faktur</th>
                                        <th class="detail-col-tanggal">Tanggal</th>
                                        <th class="detail-col-umur">Umur</th>
                                        <th class="detail-col-customer">Customer</th>
                                        <th class="detail-col-giro">No Giro</th>
                                        <th class="detail-col-tanggal">Tanggal Cair</th>
                                        <th class="detail-col-piutang text-end">Piutang</th>
                                        <th class="detail-col-potongan text-end">Potongan</th>
                                        <th class="detail-col-lain text-end">Lain-lain</th>
                                        <th class="detail-col-netto text-end">Netto</th>
                                        <th class="detail-col-action text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="detailTableBody"></tbody>
                            </table>
                            <div id="detailEmptyState" class="text-center text-muted py-3">
                                Belum ada detail penerimaan ditambahkan
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center justify-content-between mt-0 gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addDetailBtn">Tambah Detail</button>
                            <div class="text-end flex-grow-1 flex-md-grow-0">
                                <div class="row g-2">
                                    <div class="col-6 col-md-3">
                                        <small class="text-muted d-block">Total Piutang</small>
                                        <strong id="totalPiutangDisplay">0</strong>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <small class="text-muted d-block">Total Potongan</small>
                                        <strong id="totalPotonganDisplay">0</strong>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <small class="text-muted d-block">Total Lain-lain</small>
                                        <strong id="totalLainlainDisplay">0</strong>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <small class="text-muted d-block">Total Netto</small>
                                        <strong id="totalNettoDisplay">0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="totalpiutang" id="totalpiutang" value="<?= htmlspecialchars($penerimaan['totalpiutang']) ?>">
                        <input type="hidden" name="totalpotongan" id="totalpotongan" value="<?= htmlspecialchars($penerimaan['totalpotongan']) ?>">
                        <input type="hidden" name="totallainlain" id="totallainlain" value="<?= htmlspecialchars($penerimaan['totallainlain']) ?>">
                        <input type="hidden" name="totalnetto" id="totalnetto" value="<?= htmlspecialchars($penerimaan['totalnetto']) ?>">

                        <div class="mt-3 d-flex justify-content-end gap-2">
                            <a href="/penerimaan" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Penerimaan -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="detailForm">
                    <div class="modal-header modal-header-muted">
                        <h5 class="modal-title" id="detailModalLabel">Tambah Data Penjualan</h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">No Penjualan <span class="text-danger">*</span></label>
                            <select class="form-select js-choice-penjualan" id="modalPenjualan" required>
                                <option value="">Pilih Penjualan</option>
                            </select>
                        </div>
                        <div class="row g-3" id="giroFields" style="display: none;">
                            <div class="col-md-6">
                                <label class="form-label">No Giro</label>
                                <input type="text" class="form-control" id="modalNoGiro" placeholder="No Giro">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Cair</label>
                                <input type="date" class="form-control" id="modalTanggalCair">
                            </div>
                        </div>
                        <div class="row g-3 align-items-end mt-2">
                            <div class="col-md-4">
                                <label class="form-label">Piutang</label>
                                <input type="text" class="form-control" id="modalPiutang" inputmode="numeric" value="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Potongan</label>
                                <input type="text" class="form-control" id="modalPotongan" inputmode="numeric" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Lain-lain</label>
                                <input type="text" class="form-control" id="modalLainlain" inputmode="numeric" value="0">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Netto</label>
                            <input type="text" class="form-control fw-bold" id="modalNetto" value="0" readonly>
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-muted justify-content-between align-items-center">
                        <div></div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="detailModalSubmitBtn">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const integerFormatter = new Intl.NumberFormat('id-ID', {
    maximumFractionDigits: 0,
    minimumFractionDigits: 0
});

const currencyFormatter = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
});

function formatCurrency(value) {
    return integerFormatter.format(Math.round(parseFloat(value) || 0));
}

function formatCurrencyDecimal(value) {
    return currencyFormatter.format(parseFloat(value) || 0);
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

let penerimaanDetails = <?= json_encode(array_map(function($detail) {
    return [
        'nopenjualan' => $detail['nopenjualan'] ?? '',
        'tanggalpenjualan' => $detail['tanggalpenjualan'] ?? '',
        'namacustomer' => $detail['namacustomer'] ?? '',
        'namabadanusaha' => $detail['namabadanusaha'] ?? '',
        'nogiro' => $detail['nogiro'] ?? '',
        'tanggalcair' => $detail['tanggalcair'] ?? '',
        'piutang' => (float)($detail['piutang'] ?? 0),
        'potongan' => (float)($detail['potongan'] ?? 0),
        'lainlain' => (float)($detail['lainlain'] ?? 0),
        'netto' => (float)($detail['netto'] ?? 0)
    ];
}, $details ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
let availablePenjualan = [];
let detailModalInstance = null;
let penjualanChoiceInstance = null;
let customerChoiceInstance = null;
let currentEditIndex = null;
let rawPiutang = 0;
let rawPotongan = 0;
let rawLainlain = 0;

function sanitizeIntegerInput(value) {
    const normalized = value.replace(/[^0-9]/g, '');
    if (normalized === '') {
        return '';
    }
    return normalized.replace(/^0+(?=\d)/, '');
}

function initPenerimaanEditForm() {
    const penerimaanForm = document.getElementById('penerimaanForm');
    const customerSelect = document.getElementById('kodecustomer');
    const jenispenerimaanSelect = document.querySelector('select[name="jenispenerimaan"]');
    const detailModalElement = document.getElementById('detailModal');
    const modalTitle = document.getElementById('detailModalLabel');
    const detailForm = document.getElementById('detailForm');
    const modalPenjualanSelect = document.getElementById('modalPenjualan');
    const modalPiutangInput = document.getElementById('modalPiutang');
    const modalPotonganInput = document.getElementById('modalPotongan');
    const modalLainlainInput = document.getElementById('modalLainlain');
    const modalNettoInput = document.getElementById('modalNetto');
    const modalNoGiroInput = document.getElementById('modalNoGiro');
    const modalTanggalCairInput = document.getElementById('modalTanggalCair');
    const giroFields = document.getElementById('giroFields');
    const addDetailBtn = document.getElementById('addDetailBtn');
    const detailTableBody = document.getElementById('detailTableBody');
    const detailEmptyState = document.getElementById('detailEmptyState');

    if (detailModalElement) {
        detailModalInstance = new bootstrap.Modal(detailModalElement);
    }

    // Initialize Choices.js for customer
    if (typeof Choices !== 'undefined' && customerSelect) {
        customerChoiceInstance = new Choices(customerSelect, {
            searchEnabled: true,
            searchResultLimit: 100,
            searchPlaceholderValue: 'Ketik untuk mencari customer...',
            shouldSort: false,
            itemSelectText: '',
            noResultsText: 'Customer tidak ditemukan'
        });

        customerSelect.addEventListener('change', function() {
            const kodecustomer = this.value;
            loadAvailablePenjualan(kodecustomer);
        });
    }

    // Initialize Choices.js for penjualan in modal
    if (typeof Choices !== 'undefined' && modalPenjualanSelect) {
        penjualanChoiceInstance = new Choices(modalPenjualanSelect, {
            searchEnabled: true,
            searchResultLimit: 100,
            searchPlaceholderValue: 'Cari penjualan...',
            shouldSort: false,
            itemSelectText: '',
            noResultsText: 'Penjualan tidak ditemukan'
        });

        // Event listener akan di-setup di updatePenjualanSelects()
    }

    // Show/hide giro fields based on jenis penerimaan
    const currentJenisPenerimaan = jenispenerimaanSelect?.value || 'tunai';
    if (giroFields && currentJenisPenerimaan === 'giro') {
        giroFields.style.display = 'flex';
    }
    jenispenerimaanSelect?.addEventListener('change', function() {
        const isGiro = this.value === 'giro';
        if (giroFields) {
            giroFields.style.display = isGiro ? 'flex' : 'none';
        }
    });

    // Load available penjualan on page load
    loadAvailablePenjualan('<?= htmlspecialchars($penerimaan['kodecustomer'] ?? '') ?>');

    function loadAvailablePenjualan(kodecustomer = null) {
        let url = '/penerimaan/get-available-penjualan';
        if (kodecustomer) {
            url += '?kodecustomer=' + encodeURIComponent(kodecustomer);
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    availablePenjualan = data.data || [];
                    updatePenjualanSelects();
                }
            })
            .catch(error => {
                console.error('Error loading penjualan:', error);
            });
    }

    function updatePenjualanSelects() {
        if (!penjualanChoiceInstance) return;
        
        const options = [{
            value: '',
            label: 'Pilih Penjualan',
            selected: true
        }];
        
        availablePenjualan.forEach(penjualan => {
            const label = `${penjualan.nopenjualan} - ${penjualan.namacustomer || ''} (Saldo: ${formatCurrency(penjualan.saldopenjualan)})`;
            options.push({
                value: penjualan.nopenjualan,
                label: label,
                customProperties: {
                    saldo: penjualan.saldopenjualan || 0,
                    tanggalpenjualan: penjualan.tanggalpenjualan || '',
                    namacustomer: penjualan.namacustomer || '',
                    namabadanusaha: penjualan.namabadanusaha || ''
                }
            });
        });
        
        penjualanChoiceInstance.setChoices(options, 'value', 'label', true);
        
        // Setup event listener setelah choices di-update
        setupPenjualanChangeListener();
    }

    function setupPenjualanChangeListener() {
        if (!penjualanChoiceInstance || !modalPenjualanSelect) return;
        
        // Gunakan event listener pada select element yang di-wrap oleh Choices.js
        const penjualanElement = penjualanChoiceInstance.passedElement.element;
        
        // Hapus event listener lama dengan menggunakan named function untuk memudahkan removal
        if (penjualanElement._penjualanChangeHandler) {
            penjualanElement.removeEventListener('change', penjualanElement._penjualanChangeHandler);
        }
        
        // Buat handler function
        penjualanElement._penjualanChangeHandler = function() {
            const selectedValue = this.value;
            if (selectedValue) {
                // Cari data penjualan dari availablePenjualan array
                const selectedPenjualan = availablePenjualan.find(p => p.nopenjualan === selectedValue);
                if (selectedPenjualan && selectedPenjualan.saldopenjualan) {
                    const saldo = parseFloat(selectedPenjualan.saldopenjualan) || 0;
                    if (saldo > 0) {
                        rawPiutang = saldo;
                        modalPiutangInput.value = formatCurrency(rawPiutang);
                        calculateModalNetto();
                    }
                }
                // Simpan data penjualan untuk digunakan saat render tabel
                if (selectedPenjualan) {
                    modalPenjualanSelect.dataset.tanggalpenjualan = selectedPenjualan.tanggalpenjualan || '';
                    modalPenjualanSelect.dataset.namacustomer = selectedPenjualan.namacustomer || '';
                    modalPenjualanSelect.dataset.namabadanusaha = selectedPenjualan.namabadanusaha || '';
                }
            }
        };
        
        // Tambahkan event listener baru
        penjualanElement.addEventListener('change', penjualanElement._penjualanChangeHandler);
    }

    function calculateUmur(tanggalInkaso, tanggalPenjualan) {
        if (!tanggalInkaso || !tanggalPenjualan) return '-';
        try {
            const tglInkaso = new Date(tanggalInkaso);
            const tglPenjualan = new Date(tanggalPenjualan);
            const diffTime = tglInkaso - tglPenjualan;
            const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
            return diffDays >= 0 ? diffDays + ' hari' : '-';
        } catch (e) {
            return '-';
        }
    }

    function renderDetailTable() {
        detailTableBody.innerHTML = '';
        if (!penerimaanDetails.length) {
            detailEmptyState.classList.remove('d-none');
        } else {
            detailEmptyState.classList.add('d-none');
            // Ambil tanggal inkaso dari form
            const tanggalInkasoInput = document.querySelector('input[name="tanggalpenerimaan"]');
            const tanggalInkaso = tanggalInkasoInput ? tanggalInkasoInput.value : null;
            
            penerimaanDetails.forEach((item, index) => {
                const row = document.createElement('tr');
                const tanggalPenjualan = item.tanggalpenjualan || '';
                const umur = calculateUmur(tanggalInkaso, tanggalPenjualan);
                const formattedTanggalPenjualan = tanggalPenjualan ? new Date(tanggalPenjualan).toLocaleDateString('id-ID') : '-';
                
                const customerDisplay = item.namacustomer 
                    ? (item.namacustomer + (item.namabadanusaha ? ', ' + item.namabadanusaha : ''))
                    : '-';
                
                row.innerHTML = `
                    <td>${escapeHtml(item.nopenjualan)}</td>
                    <td>${formattedTanggalPenjualan}</td>
                    <td class="text-center">${umur}</td>
                    <td>${escapeHtml(customerDisplay)}</td>
                    <td>${escapeHtml(item.nogiro || '-')}</td>
                    <td>${item.tanggalcair ? new Date(item.tanggalcair).toLocaleDateString('id-ID') : '-'}</td>
                    <td class="text-end">${formatCurrency(item.piutang)}</td>
                    <td class="text-end">${formatCurrency(item.potongan)}</td>
                    <td class="text-end">${formatCurrency(item.lainlain)}</td>
                    <td class="text-end">${formatCurrency(item.netto)}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary py-1" data-action="edit" data-index="${index}">Edit</button>
                            <button type="button" class="btn btn-outline-danger py-1" data-action="remove" data-index="${index}">Hapus</button>
                        </div>
                    </td>
                `;
                detailTableBody.appendChild(row);
            });
        }
        updateTotals();
    }

    function updateTotals() {
        const totals = penerimaanDetails.reduce((acc, item) => {
            acc.piutang += parseFloat(item.piutang) || 0;
            acc.potongan += parseFloat(item.potongan) || 0;
            acc.lainlain += parseFloat(item.lainlain) || 0;
            acc.netto += parseFloat(item.netto) || 0;
            return acc;
        }, { piutang: 0, potongan: 0, lainlain: 0, netto: 0 });

        document.getElementById('totalPiutangDisplay').textContent = formatCurrency(totals.piutang);
        document.getElementById('totalPotonganDisplay').textContent = formatCurrency(totals.potongan);
        document.getElementById('totalLainlainDisplay').textContent = formatCurrency(totals.lainlain);
        document.getElementById('totalNettoDisplay').textContent = formatCurrency(totals.netto);

        document.getElementById('totalpiutang').value = totals.piutang.toFixed(2);
        document.getElementById('totalpotongan').value = totals.potongan.toFixed(2);
        document.getElementById('totallainlain').value = totals.lainlain.toFixed(2);
        document.getElementById('totalnetto').value = totals.netto.toFixed(2);
    }

    function resetDetailForm() {
        detailForm?.reset();
        rawPiutang = 0;
        rawPotongan = 0;
        rawLainlain = 0;
        modalPiutangInput.value = '0';
        modalPotonganInput.value = '0';
        modalLainlainInput.value = '0';
        modalNettoInput.value = '0';
        modalNoGiroInput.value = '';
        modalTanggalCairInput.value = '';
        if (penjualanChoiceInstance) {
            penjualanChoiceInstance.removeActiveItems();
        }
    }

    function calculateModalNetto() {
        const piutang = rawPiutang;
        const potongan = rawPotongan;
        const lainlain = rawLainlain;
        const netto = piutang - potongan + lainlain;
        modalNettoInput.value = formatCurrency(Math.max(netto, 0));
        modalNettoInput.dataset.rawNetto = Math.max(netto, 0).toFixed(2);
    }

    function openDetailModal(index = null) {
        currentEditIndex = index;
        const isEdit = index !== null;
        if (modalTitle) {
            modalTitle.textContent = isEdit ? 'Ubah Detail Inkaso' : 'Tambah Detail Inkaso';
        }
        resetDetailForm();

        const selectedDetail = isEdit ? penerimaanDetails[index] : null;
        if (selectedDetail) {
            if (penjualanChoiceInstance && selectedDetail.nopenjualan) {
                penjualanChoiceInstance.setChoiceByValue(selectedDetail.nopenjualan);
                // Set data penjualan untuk perhitungan umur
                const selectedPenjualan = availablePenjualan.find(p => p.nopenjualan === selectedDetail.nopenjualan);
                if (selectedPenjualan) {
                    modalPenjualanSelect.dataset.tanggalpenjualan = selectedPenjualan.tanggalpenjualan || '';
                    modalPenjualanSelect.dataset.namacustomer = selectedPenjualan.namacustomer || '';
                    modalPenjualanSelect.dataset.namabadanusaha = selectedPenjualan.namabadanusaha || '';
                }
            }
            rawPiutang = parseFloat(selectedDetail.piutang) || 0;
            rawPotongan = parseFloat(selectedDetail.potongan) || 0;
            rawLainlain = parseFloat(selectedDetail.lainlain) || 0;
            modalPiutangInput.value = formatCurrency(rawPiutang);
            modalPotonganInput.value = formatCurrency(rawPotongan);
            modalLainlainInput.value = formatCurrency(rawLainlain);
            modalNoGiroInput.value = selectedDetail.nogiro || '';
            modalTanggalCairInput.value = selectedDetail.tanggalcair || '';
            calculateModalNetto();
        }

        detailModalInstance?.show();
    }

    // Event listeners
    addDetailBtn?.addEventListener('click', () => {
        openDetailModal(null);
    });

    [modalPiutangInput, modalPotonganInput, modalLainlainInput].forEach((input) => {
        input?.addEventListener('input', function() {
            const sanitized = sanitizeIntegerInput(this.value);
            let value = sanitized === '' ? 0 : parseFloat(sanitized);
            
            if (this === modalPiutangInput) {
                rawPiutang = value;
                if (this === document.activeElement) {
                    this.value = sanitized || '0';
                } else {
                    this.value = formatCurrency(rawPiutang);
                }
            } else if (this === modalPotonganInput) {
                rawPotongan = value;
                if (this === document.activeElement) {
                    this.value = sanitized || '0';
                } else {
                    this.value = formatCurrency(rawPotongan);
                }
            } else if (this === modalLainlainInput) {
                rawLainlain = value;
                if (this === document.activeElement) {
                    this.value = sanitized || '0';
                } else {
                    this.value = formatCurrency(rawLainlain);
                }
            }
            calculateModalNetto();
        });

        input?.addEventListener('blur', function() {
            if (this === modalPiutangInput) {
                this.value = formatCurrency(rawPiutang);
            } else if (this === modalPotonganInput) {
                this.value = formatCurrency(rawPotongan);
            } else if (this === modalLainlainInput) {
                this.value = formatCurrency(rawLainlain);
            }
        });

        input?.addEventListener('focus', function() {
            if (this === modalPiutangInput) {
                this.value = rawPiutang ? String(Math.round(rawPiutang)) : '';
            } else if (this === modalPotonganInput) {
                this.value = rawPotongan ? String(Math.round(rawPotongan)) : '';
            } else if (this === modalLainlainInput) {
                this.value = rawLainlain ? String(Math.round(rawLainlain)) : '';
            }
            this.select();
        });
    });

    detailForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        const nopenjualan = modalPenjualanSelect.value.trim();
        if (!nopenjualan) {
            modalPenjualanSelect.focus();
            return;
        }

        const jenispenerimaan = jenispenerimaanSelect?.value || 'tunai';
        // Ambil data penjualan yang dipilih
        const selectedPenjualan = availablePenjualan.find(p => p.nopenjualan === nopenjualan);
        const rowData = {
            nopenjualan: nopenjualan,
            tanggalpenjualan: selectedPenjualan?.tanggalpenjualan || '',
            namacustomer: selectedPenjualan?.namacustomer || '',
            namabadanusaha: selectedPenjualan?.namabadanusaha || '',
            nogiro: jenispenerimaan === 'giro' ? (modalNoGiroInput.value || '') : '',
            tanggalcair: jenispenerimaan === 'giro' ? (modalTanggalCairInput.value || '') : '',
            piutang: rawPiutang,
            potongan: rawPotongan,
            lainlain: rawLainlain,
            netto: parseFloat(modalNettoInput.dataset.rawNetto || 0)
        };

        if (currentEditIndex !== null) {
            penerimaanDetails[currentEditIndex] = rowData;
        } else {
            penerimaanDetails.push(rowData);
        }

        renderDetailTable();
        detailModalInstance?.hide();
    });

    detailTableBody?.addEventListener('click', (event) => {
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
            openDetailModal(index);
        } else if (action === 'remove') {
            penerimaanDetails.splice(index, 1);
            renderDetailTable();
        }
    });

    detailModalElement?.addEventListener('hidden.bs.modal', () => {
        currentEditIndex = null;
        resetDetailForm();
    });

    penerimaanForm?.addEventListener('submit', (event) => {
        if (!penerimaanDetails.length) {
            event.preventDefault();
            showAlert({
                title: 'Validasi Penerimaan',
                message: 'Minimal satu detail penerimaan harus ditambahkan sebelum menyimpan.',
                buttonText: 'Mengerti'
            });
            addDetailBtn?.focus();
            return;
        }

        // Build form data - add hidden inputs for details
        penerimaanDetails.forEach((detail, index) => {
            Object.keys(detail).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `details[${index}][${key}]`;
                input.value = detail[key] || '';
                penerimaanForm.appendChild(input);
            });
        });
    });

    // Update tabel saat tanggal inkaso berubah
    const tanggalInkasoInput = document.querySelector('input[name="tanggalpenerimaan"]');
    if (tanggalInkasoInput) {
        tanggalInkasoInput.addEventListener('change', function() {
            renderDetailTable();
        });
    }

    renderDetailTable();
}

if (document.readyState === 'complete') {
    initPenerimaanEditForm();
} else {
    window.addEventListener('load', initPenerimaanEditForm);
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


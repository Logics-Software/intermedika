<?php
$title = 'Laporan Distribusi Penjualan';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Load Choices.js for searchable dropdown
$additionalStyles = [
    $baseUrl . '/assets/css/choices.min.css'
];
$additionalScripts = [
    $baseUrl . '/assets/js/choices.min.js'
];

// Add inline style to fix Choices.js dropdown being clipped
echo '<style>
    .card { overflow: visible !important; }
    .search-filter-card { overflow: visible !important; }
    .card-body { overflow: visible !important; }
    .choices__list--dropdown { z-index: 10000 !important; }
</style>';

require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Distribusi Penjualan</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 me-auto">Laporan Distribusi Penjualan</h4>
                        <div class="d-flex gap-2">
                            <?php
                            $exportParams = [];
                            if (!empty($search)) $exportParams['search'] = $search;
                            if (!empty($kodesales)) $exportParams['kodesales'] = $kodesales;
                            if (!empty($kodecustomer)) $exportParams['kodecustomer'] = $kodecustomer;
                            if (!empty($periode)) $exportParams['periode'] = $periode;
                            if ($periode === 'custom') {
                                if (!empty($startDate)) $exportParams['start_date'] = $startDate;
                                if (!empty($endDate)) $exportParams['end_date'] = $endDate;
                            }
                            $exportQuery = http_build_query($exportParams);
                            ?>
                            <a href="/laporan/distribusi-penjualan?export=excel<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-success btn-sm">
                                <?= icon('file-excel', 'mb-1 me-2', 16) ?>
                                <span class="d-none d-md-inline">Export Excel</span>
                                <span class="d-inline d-md-none">Excel</span>
                            </a>
                            <a href="/laporan/distribusi-penjualan?export=pdf<?= !empty($exportQuery) ? '&' . $exportQuery : '' ?>" class="btn btn-danger btn-sm">
                                <?= icon('file-pdf', 'mb-1 me-2', 16) ?>
                                <span class="d-none d-md-inline">Download PDF</span>
                                <span class="d-inline d-md-none">PDF</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="/laporan/distribusi-penjualan" class="mb-3">
                        <div class="row g-2 search-filter-card">
                            <div class="col-12 col-md-3">
                                <input type="text" class="form-control" name="search" placeholder="Cari Customer / Barang..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-6 col-md-2">
                                <?php if (Auth::isSales()): ?>
                                    <?php 
                                    $currentSalesName = '';
                                    foreach ($salesList as $sales) {
                                        if ($sales['kodesales'] === $kodesales) {
                                            $currentSalesName = $sales['namasales'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($currentSalesName) ?>" disabled>
                                    <input type="hidden" name="kodesales" value="<?= htmlspecialchars($kodesales) ?>">
                                <?php else: ?>
                                    <select name="kodesales" class="form-select">
                                        <option value="">Semua Sales</option>
                                        <?php foreach ($salesList as $sales): ?>
                                        <option value="<?= htmlspecialchars($sales['kodesales']) ?>" <?= $kodesales === $sales['kodesales'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sales['namasales']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                            <div class="col-6 col-md-3">
                                <select name="kodecustomer" id="kodecustomerSelect" class="form-select">
                                    <option value="">Semua Customer</option>
                                    <?php foreach ($customerList as $cust): ?>
                                    <option value="<?= htmlspecialchars($cust['kodecustomer']) ?>" <?= $kodecustomer === $cust['kodecustomer'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cust['namacustomer'].', '.$cust['namabadanusaha']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <select name="periode" class="form-select" onchange="toggleCustomDate(this.value)">
                                    <option value="today" <?= $periode === 'today' ? 'selected' : '' ?>>Hari Ini</option>
                                    <option value="this_month" <?= $periode === 'this_month' ? 'selected' : '' ?>>Bulan Ini</option>
                                    <option value="this_year" <?= $periode === 'this_year' ? 'selected' : '' ?>>Tahun Ini</option>
                                    <option value="custom" <?= $periode === 'custom' ? 'selected' : '' ?>>Custom</option>
                                </select>
                            </div>
                             <div class="col-12 col-md-3 custom-date-range" style="<?= $periode !== 'custom' ? 'display:none;' : '' ?>">
                                <div class="input-group">
                                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                                    <span class="input-group-text">-</span>
                                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                                </div>
                            </div>
                            <div class="col-12 col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-filter btn-primary w-100">Filter</button>
                            </div>
                             <div class="col-12 col-md-1 d-flex align-items-end">
                                <a href="/laporan/distribusi-penjualan" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                            </div>
                        </div>
                    </form>

                    <script>
                    // Define function immediately to ensure it's available
                    window.toggleCustomDate = function(value) {
                        const customDateRange = document.querySelector('.custom-date-range');
                        const form = document.querySelector('form');
                        
                        if (value === 'custom') {
                            if (customDateRange) customDateRange.style.display = 'block';
                        } else {
                            if (customDateRange) customDateRange.style.display = 'none';
                            // Reset date inputs when not custom
                            const startInput = document.querySelector('input[name="start_date"]');
                            const endInput = document.querySelector('input[name="end_date"]');
                            if (startInput) startInput.value = '';
                            if (endInput) endInput.value = '';
                            
                            // Auto submit if not custom
                            if (form) form.submit();
                        }
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        // Initialize Choices.js for customer dropdown
                        const customerSelect = document.getElementById('kodecustomerSelect');
                        if (customerSelect && typeof Choices !== 'undefined') {
                            new Choices(customerSelect, {
                                searchEnabled: true,
                                searchResultLimit: 100,
                                searchPlaceholderValue: 'Ketik untuk mencari customer...',
                                shouldSort: false,
                                itemSelectText: '',
                                noResultsText: 'Customer tidak ditemukan'
                            });
                        }
                    });
                    </script>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Barang</th>
                                    <th class="text-center">Satuan</th>
                                    <th>Pabrik</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-end">Harga Rata-rata</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reportData)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Tidak ada data penjualan</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $currentSales = null;
                                $currentCustomer = null;
                                $customerTotal = 0;
                                $salesTotal = 0;
                                $itemCount = count($reportData);
                                
                                foreach ($reportData as $index => $row): 
                                    // Check if sales is changing
                                    $isSalesChanging = ($row['kodesales'] !== $currentSales && $currentSales !== null);
                                    $isCustomerChanging = ($row['kodecustomer'] !== $currentCustomer && $currentCustomer !== null);
                                    $isLastItem = ($index === $itemCount - 1);
                                    
                                    // Print customer total if customer is changing
                                    if ($isCustomerChanging && $currentCustomer !== null) {
                                        echo '<tr class="table-light"><td colspan="5" class="text-end fw-bold py-2 pe-3">Total Penjualan:</td><td class="text-end fw-bold py-2">' . number_format($customerTotal, 0, ',', '.') . '</td></tr>';
                                        $customerTotal = 0;
                                    }
                                    
                                    // Print sales total if sales is changing
                                    if ($isSalesChanging && $currentSales !== null) {
                                        echo '<tr class="table-warning"><td colspan="5" class="text-end fw-bold py-2 pe-3">TOTAL SALES:</td><td class="text-end fw-bold py-2">' . number_format($salesTotal, 0, ',', '.') . '</td></tr>';
                                        $salesTotal = 0;
                                    }
                                    
                                    // Group Header 1: Sales
                                    if ($row['kodesales'] !== $currentSales) {
                                        echo '<tr class="table-secondary"><td colspan="6" class="fw-bold py-2"><i class="fas fa-user-tie me-2"></i> SALES: ' . htmlspecialchars($row['namasales']) . '</td></tr>';
                                        $currentSales = $row['kodesales'];
                                        $currentCustomer = null;
                                        $customerTotal = 0;
                                    }

                                    // Group Header 2: Customer
                                    if ($row['kodecustomer'] !== $currentCustomer) {
                                        $customerDisplay = htmlspecialchars($row['namacustomer']);
                                        if (!empty($row['namabadanusaha'])) {
                                            $customerDisplay .= ' (' . htmlspecialchars($row['namabadanusaha']) . ')';
                                        }
                                        if (!empty($row['alamatcustomer'])) {
                                            $customerDisplay .= ' - ' . htmlspecialchars($row['alamatcustomer']);
                                        }
                                        echo '<tr class="table-info bg-opacity-10"><td colspan="6" class="fw-bold py-2 ps-4"><i class="fas fa-building me-2"></i> ' . $customerDisplay . '</td></tr>';
                                        $currentCustomer = $row['kodecustomer'];
                                    }
                                    
                                    // Add to totals
                                    $itemTotal = (float)($row['total_nilai'] ?? 0);
                                    $customerTotal += $itemTotal;
                                    $salesTotal += $itemTotal;
                                ?>
                                <tr>
                                    <td class="ps-5"><?= htmlspecialchars($row['namabarang'] ?? '-') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['satuan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['namapabrik'] ?? '-') ?></td>
                                    <td class="text-end"><?= number_format((float)($row['total_jumlah'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format((float)($row['harga_rata_rata'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-end fw-bold"><?= number_format((float)($row['total_nilai'] ?? 0), 0, ',', '.') ?></td>
                                </tr>
                                <?php 
                                    // Print totals for last item
                                    if ($isLastItem && $currentCustomer !== null) {
                                        // Customer total
                                        echo '<tr class="table-light"><td colspan="5" class="text-end fw-bold py-2 pe-3">Total Penjualan:</td><td class="text-end fw-bold py-2">' . number_format($customerTotal, 0, ',', '.') . '</td></tr>';
                                        // Sales total
                                        echo '<tr class="table-warning"><td colspan="5" class="text-end fw-bold py-2 pe-3">TOTAL SALES:</td><td class="text-end fw-bold py-2">' . number_format($salesTotal, 0, ',', '.') . '</td></tr>';
                                    }
                                endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php require __DIR__ . '/../layouts/footer.php'; ?>

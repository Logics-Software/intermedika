<?php
$title = 'Dashboard';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

$user = $user ?? Auth::user();
$role = $role ?? ($user['role'] ?? '');
$stats = $stats ?? [];

// Load sticky column CSS and JS if needed (for price changes, barang datang, and overdue invoices tables)
if (($role === 'sales' || $role === 'manajemen' || $role === 'admin' || $role === 'operator') && (!empty($stats['price_changes']) || !empty($stats['barang_datang']) || !empty($stats['overdue_invoices']))) {
    $additionalStyles = $additionalStyles ?? [];
    $additionalStyles[] = $baseUrl . '/assets/css/sticky-column.css';
    $additionalScripts = $additionalScripts ?? [];
    $additionalScripts[] = $baseUrl . '/assets/js/sticky-column.js';
}

require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="mb-0">Dashboard</h1>
        </div>
    </div>
        
    <?php if ($role === 'manajemen' || $role === 'admin'): ?>
        <!-- Dashboard Manajemen / Admin -->
        <div class="row g-3 mb-3">
            <?php if ($role === 'admin'): ?>
            <!-- Admin: Total User Log Hari Ini -->
            <div class="col-12 col-md-4">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-blue">
                            <?= icon('clock-rotate-left', '', 24) ?>
                        </div>
                        <h4 class="card-title text-muted mb-2">Total User Log Hari Ini</h4>
                        <h3 class="mb-2"><?= number_format($stats['total_user_logs_today'] ?? 0) ?></h3>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0">Login Log</p>
                            <a href="/login-logs" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Manajemen: Total Order Hari Ini -->
            <div class="col-12 col-md-4">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-blue">
                            <?= icon('file-invoice', '', 24) ?>
                        </div>
                        <h4 class="card-title text-muted mb-2">Total Order Hari Ini</h4>
                        <h3 class="mb-2">Rp <?= number_format($stats['all_orders_total'] ?? 0, 0, ',', '.') ?></h3>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0"><?= number_format($stats['total_orders'] ?? 0) ?> Order</p>
                            <a href="/orders" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-12 col-md-4">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-green">
                            <?= icon('file-invoice-dollar', '', 24) ?>
                        </div>
                        <h5 class="card-title text-muted mb-2">Total Penjualan Hari Ini</h5>
                        <h3 class="mb-2">Rp <?= number_format($stats['all_penjualan_total'] ?? 0, 0, ',', '.') ?></h3>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0"><?= number_format($stats['total_penjualan'] ?? 0) ?> Faktur</p>
                            <a href="/penjualan" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-purple">
                            <?= icon('money-bill-transfer', '', 24) ?>
                        </div>
                        <h5 class="card-title text-muted mb-2">Total Inkaso Hari Ini</h5>
                        <h3 class="mb-2">Rp <?= number_format($stats['all_penerimaan_total'] ?? 0, 0, ',', '.') ?></h3>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0"><?= number_format($stats['total_penerimaan'] ?? 0) ?> Inkaso</p>
                            <a href="/penerimaan" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Daily Stats (Faktur & Outlet) -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-blue">
                            <?= icon('file-lines', '', 24) ?>
                        </div>
                        <h4 class="card-title text-muted mb-2">Total Faktur Hari Ini</h4>
                        <h3 class="mb-0"><?= number_format($stats['total_penjualan'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-purple">
                            <?= icon('store', '', 24) ?>
                        </div>
                        <h4 class="card-title text-muted mb-2">Total Outlet Hari Ini</h4>
                        <h3 class="mb-0"><?= number_format($stats['total_outlets_today'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
        </div>


        <!-- Chart Penjualan dan Inkaso Per Bulan YTD (All Sales) -->
        <?php if (!empty($stats['monthly_sales']) || !empty($stats['monthly_inkaso'])): ?>
        <div class="row g-3 mb-3">
            <?php if (!empty($stats['monthly_sales'])): ?>
            <div class="col-12 col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header dashboard-card-header">
                        <h5 class="mb-0">Penjualan Per Bulan</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChartManajemen" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($stats['monthly_inkaso'])): ?>
            <div class="col-12 col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header dashboard-card-header">
                        <h5 class="mb-0">Inkaso Per Bulan</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="inkasoChartManajemen" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Harga Barang Baru -->
        <?php if (!empty($stats['price_changes'])): ?>
        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header dashboard-card-header">
                        <h5 class="mb-0">Harga Barang Baru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-sticky-column">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="sticky-col" style="min-width: 150px;">Nama Barang</th>
                                        <th>Satuan</th>
                                        <th>Pabrik</th>
                                        <th>Kondisi</th>
                                        <th>ED</th>
                                        <th>Harga</th>
                                        <th>Disc</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['price_changes'] as $item): ?>
                                    <tr>
                                        <td class="sticky-col"><?= htmlspecialchars($item['namabarang'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['satuan'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['pabrik'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['kondisi'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['ed'] ?? '-') ?></td>
                                        <td align="right">Rp <?= number_format($item['harga'] ?? 0, 0, ',', '.') ?></td>
                                        <td align="right"><?= number_format($item['discount'] ?? 0, 2, ',', '.') ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="/perubahanharga" class="btn btn-secondary">
                                Lebih lanjut <?= icon('ellipsis-horizontal', 'me-2', 18) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Faktur Overdue (All Sales) -->
        <?php if (!empty($stats['overdue_invoices'])): ?>
        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header dashboard-card-header">
                        <h5 class="mb-0">Faktur Overdue</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-sticky-column">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="sticky-col sticky-col-faktur">No. Faktur</th>
                                        <th>Tanggal</th>
                                        <th>Umur</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Customer</th>
                                        <th>Alamat Customer</th>
                                        <th style="min-width: 100px;">Saldo Tagihan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['overdue_invoices'] as $invoice): ?>
                                    <tr>
                                        <td class="sticky-col sticky-col-faktur fw-bold text-lg"><?= htmlspecialchars($invoice['nopenjualan'] ?? '-') ?></td>
                                        <td align="center"><?= !empty($invoice['tanggalpenjualan']) ? date('d/m/Y', strtotime($invoice['tanggalpenjualan'])) : '-' ?></td>
                                        <td align="center"><?= !empty($invoice['umur']) ? number_format($invoice['umur']) : '-' ?></td>
                                        <td align="center"><?= !empty($invoice['tanggaljatuhtempo']) ? date('d/m/Y', strtotime($invoice['tanggaljatuhtempo'])) : '-' ?></td>
                                        <td><?= htmlspecialchars($invoice['namacustomer'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($invoice['alamatcustomer'] ?? '-') ?></td>
                                        <td align="right">Rp <?= number_format($invoice['saldopenjualan'] ?? 0, 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="/laporan/daftar-tagihan" class="btn btn-secondary">
                                Lebih lanjut <?= icon('ellipsis-horizontal', 'me-2', 18) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($role === 'operator'): ?>
        <!-- Dashboard Operator -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-blue">
                            <?= icon('file-invoice', '', 24) ?>
                        </div>
                        <h4 class="card-title text-muted mb-2">Total Order Hari Ini</h4>
                        <h3 class="mb-2">Rp <?= number_format($stats['all_orders_total'] ?? 0, 0, ',', '.') ?></h3>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0"><?= number_format($stats['total_orders'] ?? 0) ?> Order</p>
                            <a href="/orders" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-green">
                            <?= icon('file-invoice-dollar', '', 24) ?>
                        </div>
                        <h5 class="card-title text-muted mb-2">Total Penjualan Hari Ini</h5>
                        <h3 class="mb-2">Rp <?= number_format($stats['all_penjualan_total'] ?? 0, 0, ',', '.') ?></h3>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0"><?= number_format($stats['total_penjualan'] ?? 0) ?> Faktur</p>
                            <a href="/penjualan" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-purple">
                            <?= icon('money-bill-transfer', '', 24) ?>
                        </div>
                        <h5 class="card-title text-muted mb-2">Total Inkaso Hari Ini</h5>
                        <h3 class="mb-2">Rp <?= number_format($stats['all_penerimaan_total'] ?? 0, 0, ',', '.') ?></h3>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0"><?= number_format($stats['total_penerimaan'] ?? 0) ?> Inkaso</p>
                            <a href="/penerimaan" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Daily Stats (Faktur & Outlet) -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-blue">
                            <?= icon('file-lines', '', 24) ?>
                        </div>
                        <h4 class="card-title text-muted mb-2">Jumlah Faktur Hari Ini</h4>
                        <h3 class="mb-0"><?= number_format($stats['total_penjualan'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-purple">
                            <?= icon('store', '', 24) ?>
                        </div>
                        <h4 class="card-title text-muted mb-2">Jumlah Outlet Hari Ini</h4>
                        <h3 class="mb-0"><?= number_format($stats['total_outlets_today'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Harga Barang Baru -->
        <?php if (!empty($stats['price_changes'])): ?>
        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header dashboard-card-header">
                        <h5 class="mb-0">Harga Barang Baru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-sticky-column">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="sticky-col" style="min-width: 150px;">Nama Barang</th>
                                        <th>Satuan</th>
                                        <th>Pabrik</th>
                                        <th>Kondisi</th>
                                        <th>ED</th>
                                        <th>Harga</th>
                                        <th>Disc</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['price_changes'] as $item): ?>
                                    <tr>
                                        <td class="sticky-col"><?= htmlspecialchars($item['namabarang'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['satuan'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['pabrik'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['kondisi'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['ed'] ?? '-') ?></td>
                                        <td align="right">Rp <?= number_format($item['harga'] ?? 0, 0, ',', '.') ?></td>
                                        <td align="right"><?= number_format($item['discount'] ?? 0, 2, ',', '.') ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="/perubahanharga" class="btn btn-secondary">
                                Lebih lanjut <?= icon('ellipsis-horizontal', 'me-2', 18) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Faktur Overdue (All Sales) -->
        <?php if (!empty($stats['overdue_invoices'])): ?>
        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header dashboard-card-header">
                        <h5 class="mb-0">Faktur Overdue</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-sticky-column">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="sticky-col sticky-col-faktur">No. Faktur</th>
                                        <th>Tanggal</th>
                                        <th>Umur</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Customer</th>
                                        <th>Alamat Customer</th>
                                        <th style="min-width: 100px;">Saldo Tagihan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['overdue_invoices'] as $invoice): ?>
                                    <tr>
                                        <td class="sticky-col sticky-col-faktur fw-bold text-lg"><?= htmlspecialchars($invoice['nopenjualan'] ?? '-') ?></td>
                                        <td align="center"><?= !empty($invoice['tanggalpenjualan']) ? date('d/m/Y', strtotime($invoice['tanggalpenjualan'])) : '-' ?></td>
                                        <td align="center"><?= !empty($invoice['umur']) ? number_format($invoice['umur']) : '-' ?></td>
                                        <td align="center"><?= !empty($invoice['tanggaljatuhtempo']) ? date('d/m/Y', strtotime($invoice['tanggaljatuhtempo'])) : '-' ?></td>
                                        <td><?= htmlspecialchars($invoice['namacustomer'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($invoice['alamatcustomer'] ?? '-') ?></td>
                                        <td align="right">Rp <?= number_format($invoice['saldopenjualan'] ?? 0, 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="/laporan/daftar-tagihan" class="btn btn-secondary">
                                Lebih lanjut <?= icon('ellipsis-horizontal', 'me-2', 18) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    <?php elseif ($role === 'sales'): ?>
        <!-- Dashboard Sales -->
        <div class="row g-3 mb-3">
            <!-- <div class="col-12 col-md-4">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-blue">
                            <?= icon('file-invoice', '', 24) ?>
                        </div>
                        <h4 class="card-title text-muted mb-2">Order Saya</h4>
                        <h3 class="mb-2">Rp <?= number_format($stats['my_orders_total'] ?? 0, 0, ',', '.') ?></h3>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0"><?= number_format($stats['my_orders'] ?? 0) ?> Order</p>
                            <a href="/orders" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div> -->
            <div class="col-12 col-md-12">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-green">
                            <?= icon('file-invoice-dollar', '', 24) ?>
                        </div>
                        <h5 class="card-title text-muted mb-2">Penjualan Saya</h5>
                        <h3 class="mb-2">Rp <?= number_format($stats['my_penjualan_total'] ?? 0, 0, ',', '.') ?></h3>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0"><?= number_format($stats['my_penjualan'] ?? 0) ?> Faktur</p>
                            <a href="/penjualan" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="col-12 col-md-4">
                <div class="card dashboard-stats-card">
                    <div class="card-body">
                        <div class="dashboard-stats-card-icon icon-purple">
                            <?= icon('money-bill-transfer', '', 24) ?>
                        </div>
                        <h5 class="card-title text-muted mb-2">Inkaso Saya</h5>
                        <h3 class="mb-2">Rp <?= number_format($stats['my_penerimaan_total'] ?? 0, 0, ',', '.') ?></h3>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-2"><?= number_format($stats['my_penerimaan'] ?? 0) ?> Inkaso</p>
                            <a href="/penerimaan" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Quick Links/Shortcuts -->
        <div class="row g-3 mb-3 mx-0">
            <div class="col-12 col-md-4">
                <a href="/laporan/daftar-stok" class="card dashboard-stats-card text-decoration-none h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <?= icon('table-list-dark', '', 24) ?>
                        </div>
                        <span class="fw-bold text-dark">Daftar Harga & Stok Barang</span>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a href="/laporan/daftar-tagihan" class="card dashboard-stats-card text-decoration-none h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <?= icon('file-invoice-dollar', '', 24) ?>
                        </div>
                        <span class="fw-bold text-dark">Tagihan Faktur</span>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a href="/laporan/omset" class="card dashboard-stats-card text-decoration-none h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <?= icon('money-bill-transfer', '', 24) ?>
                        </div>
                        <span class="fw-bold text-dark">Omset Penjualan</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Chart Penjualan dan Inkaso Per Bulan YTD -->
        <!-- <?php if (!empty($stats['monthly_sales']) || !empty($stats['monthly_inkaso'])): ?>
        <div class="row g-3 mb-3">
            <?php if (!empty($stats['monthly_sales'])): ?>
            <div class="col-12 col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header dashboard-card-header">
                        <h5 class="mb-0">Penjualan Per Bulan</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($stats['monthly_inkaso'])): ?>
            <div class="col-12 col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header dashboard-card-header">
                        <h5 class="mb-0">Inkaso Per Bulan</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="inkasoChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?> -->

        <!-- Perubahan Harga Barang -->
        <?php if (!empty($stats['price_changes'])): ?>
        <div class="col-12 col-md-12">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header dashboard-card-header">
                        <h5 class="mb-0">Harga Barang Baru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-sticky-column">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="sticky-col" style="min-width: 150px;">Nama Barang</th>
                                        <th>Satuan</th>
                                        <th>Pabrik</th>
                                        <th>Kondisi</th>
                                        <th>ED</th>
                                        <th>Harga</th>
                                        <th>Disc</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['price_changes'] as $item): ?>
                                    <tr>
                                        <td class="sticky-col"><?= htmlspecialchars($item['namabarang'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['satuan'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['pabrik'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['kondisi'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['ed'] ?? '-') ?></td>
                                        <td align="right">Rp <?= number_format($item['harga'] ?? 0, 0, ',', '.') ?></td>
                                        <td align="right"><?= number_format($item['discount'] ?? 0, 2, ',', '.') ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="/laporan/daftar-harga" class="btn btn-secondary">
                                Lebih lanjut<?= icon('ellipsis-horizontal', 'me-2', 18) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Barang Datang -->
        <?php if ($role === 'sales' && !empty($stats['barang_datang'])): ?>
        <div class="col-12 col-md-12">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header dashboard-card-header">
                        <h5 class="mb-0">Barang Datang</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-sticky-column">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="sticky-col" style="min-width: 150px;">Nama Barang</th>
                                        <th>Satuan</th>
                                        <th>Pabrik</th>
                                        <th>Kondisi</th>
                                        <th>ED</th>
                                        <th>Tanggal</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['barang_datang'] as $item): ?>
                                    <tr>
                                        <td class="sticky-col"><?= htmlspecialchars($item['namabarang'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['satuan'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['pabrik'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['kondisi'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['ed'] ?? '-') ?></td>
                                        <td align="center"><?= !empty($item['tanggal']) ? date('d/m/Y', strtotime($item['tanggal'])) : '-' ?></td>
                                        <td align="right"><?= number_format($item['jumlah'] ?? 0, 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="/pembelian" class="btn btn-secondary">
                                Lebih lanjut <?= icon('ellipsis-horizontal', 'me-2', 18) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Faktur Overdue -->
        <?php if (!empty($stats['overdue_invoices'])): ?>
        <div class="col-12 col-md-12">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header dashboard-card-header">
                        <h5 class="mb-0">Faktur Overdue</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-sticky-column">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="sticky-col sticky-col-faktur">No. Faktur</th>
                                        <th>Tanggal</th>
                                        <th>Umur</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Customer</th>
                                        <th>Alamat Customer</th>
                                        <th style="min-width: 100px;">Saldo Tagihan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['overdue_invoices'] as $invoice): ?>
                                    <tr>
                                        <td class="sticky-col sticky-col-faktur fw-bold text-lg"><?= htmlspecialchars($invoice['nopenjualan'] ?? '-') ?></td>
                                        <td align="center"><?= !empty($invoice['tanggalpenjualan']) ? date('d/m/Y', strtotime($invoice['tanggalpenjualan'])) : '-' ?></td>
                                        <td align="center"><?= !empty($invoice['umur']) ? number_format($invoice['umur']) : '-' ?></td>
                                        <td align="center"><?= !empty($invoice['tanggaljatuhtempo']) ? date('d/m/Y', strtotime($invoice['tanggaljatuhtempo'])) : '-' ?></td>
                                        <td><?= htmlspecialchars($invoice['namacustomer'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($invoice['alamatcustomer'] ?? '-') ?></td>
                                        <td align="right">Rp <?= number_format($invoice['saldopenjualan'] ?? 0, 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="/laporan/daftar-tagihan" class="btn btn-secondary">
                                Lebih lanjut <?= icon('ellipsis-horizontal', 'me-2', 18) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    <?php endif; ?>
</div>

<?php if (($role === 'sales' || $role === 'manajemen' || $role === 'admin') && !empty($stats['monthly_sales'])): ?>
<?php
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlySales = <?= json_encode($stats['monthly_sales']) ?>;
    
    // Prepare data
    const labels = [];
    const data = [];
    
    // monthlySales is already an array, so we can iterate directly
    monthlySales.forEach(item => {
        labels.push(item.month);
        // Convert to thousands (per mil)
        data.push(item.total / 1000);
    });
    
    // Find max value for better Y-axis scaling (in thousands)
    const maxValue = Math.max(...data, 0);
    const yAxisMax = maxValue > 0 ? Math.ceil(maxValue * 1.2 / 1.5) * 1.5 : 6;
    
    // Create chart for sales role
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Penjualan',
                    data: data,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2.5,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                // Convert back to full value and format
                                const value = context.parsed.y * 1000;
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            color: '#6b7280'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: yAxisMax,
                        ticks: {
                            stepSize: 1.5,
                            font: {
                                size: 12
                            },
                            color: '#6b7280',
                            callback: function(value) {
                                // Format in thousands with 'K' suffix
                                return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(value) + 'K';
                            }
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false,
                            borderDash: [5, 5]
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
    
    // Create chart for manajemen role
    const ctxManajemen = document.getElementById('salesChartManajemen');
    if (ctxManajemen) {
        new Chart(ctxManajemen, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Penjualan',
                    data: data,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2.5,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                // Convert back to full value and format
                                const value = context.parsed.y * 1000;
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            color: '#6b7280'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: yAxisMax,
                        ticks: {
                            stepSize: 1.5,
                            font: {
                                size: 12
                            },
                            color: '#6b7280',
                            callback: function(value) {
                                // Format in thousands with 'K' suffix
                                return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(value) + 'K';
                            }
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false,
                            borderDash: [5, 5]
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
});
</script>
<?php endif; ?>

<?php if (($role === 'sales' || $role === 'manajemen' || $role === 'admin') && !empty($stats['monthly_inkaso'])): ?>
<?php
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyInkaso = <?= json_encode($stats['monthly_inkaso']) ?>;
    
    // Prepare data
    const labels = [];
    const data = [];
    
    // monthlyInkaso is already an array, so we can iterate directly
    monthlyInkaso.forEach(item => {
        labels.push(item.month);
        // Convert to thousands (per mil)
        data.push(item.total / 1000);
    });
    
    // Find max value for better Y-axis scaling (in thousands)
    const maxValue = Math.max(...data, 0);
    const yAxisMax = maxValue > 0 ? Math.ceil(maxValue * 1.2 / 1.5) * 1.5 : 6;
    
    // Create chart for sales role
    const ctx = document.getElementById('inkasoChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Inkaso',
                    data: data,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2.5,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            color: '#6b7280'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: yAxisMax,
                        ticks: {
                            stepSize: 1500,
                            font: {
                                size: 12
                            },
                            color: '#6b7280',
                            callback: function(value) {
                                return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(value) + 'K';
                            }
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false,
                            borderDash: [5, 5]
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
    
    // Create chart for manajemen role
    const ctxManajemenInkaso = document.getElementById('inkasoChartManajemen');
    if (ctxManajemenInkaso) {
        new Chart(ctxManajemenInkaso, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Inkaso',
                    data: data,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2.5,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                // Convert back to full value and format
                                const value = context.parsed.y * 1000;
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            color: '#6b7280'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: yAxisMax,
                        ticks: {
                            stepSize: 1.5,
                            font: {
                                size: 12
                            },
                            color: '#6b7280',
                            callback: function(value) {
                                // Format in thousands with 'K' suffix
                                return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(value) + 'K';
                            }
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false,
                            borderDash: [5, 5]
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

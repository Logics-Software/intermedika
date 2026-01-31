<?php
$title = 'Detail Order';
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
				<nav aria-label="breadcrumb" data-breadcrumb-parent="/orders">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
						<li class="breadcrumb-item"><a href="/orders">Transaksi Order</a></li>
						<li class="breadcrumb-item active">Detail Order</li>
					</ol>
				</nav>
			</div>
	</div>

	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center"<?= !empty($backUrl ?? '') ? ' data-back-url="' . htmlspecialchars($backUrl) . '"' : '' ?>>
			<h4 class="mb-0 me-auto">Order: <?= htmlspecialchars($order['noorder'] ?? '') ?></h4>
			<a href="<?= htmlspecialchars($backUrl ?? '/orders') ?>" class="btn btn-secondary btn-sm"><?= icon('back', 'me-2 mb-0', 18) ?> Kembali</a>
		</div>
		<div class="card-body">
			<div class="row mb-3">
				<div class="col-md-1"><strong>Tanggal</strong><br><?= htmlspecialchars(date('d/m/Y', strtotime($order['tanggalorder'] ?? date('Y-m-d')))) ?></div>
				<div class="col-md-3"><strong>Customer</strong><br><?= htmlspecialchars(($order['namacustomer'] ?? '') . (!empty($order['namabadanusaha']) ? ', ' . $order['namabadanusaha'] : '')) ?></div>
				<div class="col-md-5"><strong>Alamat</strong><br><?= htmlspecialchars(($order['alamatcustomer'] ?? '') . (!empty($order['kota']) ? ', ' . $order['kota'] : '')) ?></div>
				<div class="col-md-1"><strong>Status</strong><br><span class="badge bg-<?= ($order['status'] ?? '') === 'faktur' ? 'success' : 'warning' ?>"><?= htmlspecialchars(ucfirst($order['status'] ?? '')) ?></span></div>
				<div class="col-md-2"><strong>No Faktur</strong><br><?= htmlspecialchars($order['nopenjualan'] ?? '-') ?></div>
			</div>

			<div class="table-responsive">
				<table class="table table-striped align-middle">
					<thead>
						<tr>
							<th>Nama</th>
							<th class="text-end">Jumlah</th>
							<th class="text-end">Harga</th>
							<th class="text-end">Diskon</th>
							<th class="text-end">Total</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach (($details ?? []) as $d): ?>
						<tr>
							<td><?= htmlspecialchars($d['namabarang'] ?? '') ?></td>
							<td class="text-end"><?= number_format((float)($d['jumlah'] ?? 0), 0, ',', '.') ?></td>
							<td class="text-end"><?= number_format((float)($d['hargajual'] ?? 0), 0, ',', '.') ?></td>
							<td class="text-end"><?= number_format((float)($d['discount'] ?? 0), 2, ',', '.') ?> %</td>
							<td class="text-end"><?= number_format((float)($d['totalharga'] ?? 0), 0, ',', '.') ?></td>
						</tr>
						<?php endforeach; ?>
						<tr>
							<td class="text-center">GRAND TOTAL</td>
							<td></td>
							<td></td>
							<td></td>
							<td class="text-end"><?= number_format((float)($order['nilaiorder'] ?? 0), 0, ',', '.') ?></td>
						</tr>						
					</tbody>
				</table>
			</div>

			<?php if (!empty($orderFiles ?? [])): ?>
			<div class="mt-4">
				<h5>Lampiran Order</h5>
				<div class="table-responsive">
					<table class="table table-striped align-middle">
						<thead>
							<tr>
								<th>Nama File</th>
								<th>Ukuran</th>
								<th>Diupload Oleh</th>
								<th>Tanggal Upload</th>
								<th class="text-center">Aksi</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($orderFiles as $file): ?>
							<tr>
								<td><?= htmlspecialchars($file['original_filename']) ?></td>
								<td><?= number_format((float)($file['file_size'] ?? 0) / 1024, 2, ',', '.') ?> KB</td>
								<td><?= htmlspecialchars($file['uploaded_by_name'] ?? '-') ?></td>
								<td><?= date('d/m/Y H:i', strtotime($file['created_at'])) ?></td>
								<td class="text-center">
									<a href="<?= htmlspecialchars($baseUrl) ?><?= htmlspecialchars($file['file_path']) ?>" target="_blank" class="btn btn-sm btn-primary">
										<?= icon('download', 'me-1 mb-1', 16) ?> Download
									</a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>



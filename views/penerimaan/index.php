<?php
$title = 'Penerimaan Piutang';
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
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Transaksi Inkaso</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 me-auto">Daftar Inkaso</h4>
                        <?php if (Auth::isSales()): ?>
                        <a href="/penerimaan/create" class="btn btn-primary btn-sm"><?= icon('square-plus', 'me-2', 16) ?> Buat Inkaso</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="/penerimaan" class="mb-3" id="penerimaanFilterForm">
                        <div class="row g-2 align-items-end search-filter-card">
                            <div class="col-12 col-lg-3">
                                <input type="text" class="form-control" name="search" placeholder="Cari no penerimaan, customer, sales, no inkaso" value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-6 col-lg-2">
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">Semua Status</option>
                                    <option value="belumproses" <?= $status === 'belumproses' ? 'selected' : '' ?>>Belum Proses</option>
                                    <option value="proses" <?= $status === 'proses' ? 'selected' : '' ?>>Proses</option>
                                </select>
                            </div>
                            <div class="col-6 col-lg-2">
                                <select name="date_filter" id="dateFilter" class="form-select" onchange="handleDateFilterChange(true)">
                                    <option value="today" <?= ($dateFilter ?? 'today') === 'today' ? 'selected' : '' ?>>Hari ini</option>
                                    <option value="week" <?= ($dateFilter ?? '') === 'week' ? 'selected' : '' ?>>Minggu ini</option>
                                    <option value="month" <?= ($dateFilter ?? '') === 'month' ? 'selected' : '' ?>>Bulan ini</option>
                                    <option value="year" <?= ($dateFilter ?? '') === 'year' ? 'selected' : '' ?>>Tahun ini</option>
                                    <option value="custom" <?= ($dateFilter ?? '') === 'custom' ? 'selected' : '' ?>>Custom</option>
                                </select>
                            </div>
                            <div class="col-6 col-lg-2" id="startDateWrapper" style="display: <?= ($dateFilter ?? 'today') === 'custom' ? 'block' : 'none' ?>;">
                                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($rawStartDate ?? $startDate ?? '') ?>" placeholder="Dari">
                            </div>
                            <div class="col-6 col-lg-2" id="endDateWrapper" style="display: <?= ($dateFilter ?? 'today') === 'custom' ? 'block' : 'none' ?>;">
                                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($rawEndDate ?? $endDate ?? '') ?>" placeholder="Sampai">
                            </div>
                            <div class="col-6 col-lg-1">
                                <select name="per_page" class="form-select" onchange="this.form.submit()">
                                    <?php foreach ($perPageOptions as $option): ?>
                                    <option value="<?= $option ?>" <?= $perPage == $option ? 'selected' : '' ?>><?= $option ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-lg-4 d-lg-flex justify-content-lg-end">
                                <div class="row g-2 w-100">
                                    <div class="col-6 col-lg-6">
                                        <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                                    </div>
                                    <div class="col-6 col-lg-6">
                                        <a href="/penerimaan" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No.Inkaso</th>
                                    <th>Tanggal</th>
                                    <th>Customer</th>
                                    <th>Jenis</th>
                                    <th>Status</th>
                                    <th class="text-end">Total Netto</th>
                                    <th>No.Proses</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($penerimaan)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Tidak ada data penerimaan</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($penerimaan as $row): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nopenerimaan']) ?></td>
                                    <td><?= $row['tanggalpenerimaan'] ? date('d/m/Y', strtotime($row['tanggalpenerimaan'])) : '-' ?></td>
                                    <td><?= htmlspecialchars($row['namacustomer'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $jenisLabels = ['tunai' => 'Tunai', 'transfer' => 'Transfer', 'giro' => 'Giro'];
                                        echo $jenisLabels[$row['jenispenerimaan']] ?? $row['jenispenerimaan'];
                                        ?>
                                    </td>
                                    <td align="center">
                                        <?php if ($row['status'] === 'belumproses'): ?>
                                            <span class="badge bg-warning text-dark">Belum Proses</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Proses</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><?= number_format((float)$row['totalnetto'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($row['noinkaso'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1">
                                        <a href="/penerimaan/view/<?= urlencode($row['nopenerimaan']) ?>" class="btn btn-sm btn-info text-white"><?= icon('eye', 'me-0 mb-1', 14) ?></a>
                                        <?php if (Auth::isSales() && $row['status'] === 'belumproses'): ?>
                                        <a href="/penerimaan/edit/<?= urlencode($row['nopenerimaan']) ?>" class="btn btn-sm btn-warning text-white"><?= icon('pen-to-square', 'me-0 mb-1', 14) ?></a>
                                        <button type="button" class="btn btn-sm btn-danger text-white" onclick="confirmDeletePenerimaan('<?= htmlspecialchars($row['nopenerimaan'], ENT_QUOTES) ?>')"><?= icon('trash-can', 'me-0 mb-1', 14) ?></button>
                                        <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                    <?php
                    // Get current page from URL (always use $_GET to ensure it's current)
                    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : (isset($page) ? (int)$page : 1);
                    // Ensure currentPage is at least 1
                    if ($currentPage < 1) {
                        $currentPage = 1;
                    }
                    // Use currentPage for all calculations
                    $page = $currentPage;
                    $totalPages = (int)$totalPages;
                    $perPage = (int)$perPage;
                    
                    $maxLinks = 3;
                    $half = (int)floor($maxLinks / 2);
                    $start = max(1, $page - $half);
                    $end = min($totalPages, $start + $maxLinks - 1);
                    if ($end - $start + 1 < $maxLinks) {
                        $start = max(1, $end - $maxLinks + 1);
                    }
                    ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <?php
                                // Calculate previous page, ensuring it's an integer
                                $prevPage = (int)max(1, (int)$page - 1);
                                // Ensure prevPage is at least 1
                                if ($prevPage < 1) $prevPage = 1;
                                ?>
                                <a class="page-link" href="<?= buildPenerimaanQuery($prevPage, $perPage, $search, $status, $dateFilter ?? 'today', $rawStartDate ?? $startDate ?? '', $rawEndDate ?? $endDate ?? '') ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="' . buildPenerimaanQuery(1, $perPage, $search, $status, $dateFilter ?? 'today', $rawStartDate ?? $startDate ?? '', $rawEndDate ?? $endDate ?? '') . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                $active = $page == $i ? 'active' : '';
                                echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . buildPenerimaanQuery($i, $perPage, $search, $status, $dateFilter ?? 'today', $rawStartDate ?? $startDate ?? '', $rawEndDate ?? $endDate ?? '') . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="' . buildPenerimaanQuery($totalPages, $perPage, $search, $status, $dateFilter ?? 'today', $rawStartDate ?? $startDate ?? '', $rawEndDate ?? $endDate ?? '') . '">' . $totalPages . '</a></li>';
                            }
                            ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <?php
                                // Calculate next page: current page + 1 (increment)
                                // $page is already cast to int and validated above
                                // Simply increment current page
                                $nextPage = $page + 1;
                                
                                // Only cap at totalPages if it exceeds (for disabled state)
                                if ($nextPage > $totalPages) {
                                    $nextPage = $totalPages;
                                }
                                
                                // Ensure it's an integer
                                $nextPage = (int)$nextPage;
                                ?>
                                <a class="page-link" href="<?= buildPenerimaanQuery($nextPage, $perPage, $search, $status, $dateFilter ?? 'today', $rawStartDate ?? $startDate ?? '', $rawEndDate ?? $endDate ?? '') ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function buildPenerimaanQuery($page, $perPage, $search, $status, $dateFilter, $startDate, $endDate) {
    $params = [
        'page' => max($page, 1),
        'per_page' => $perPage,
        'search' => $search
    ];
    if (!empty($status)) {
        $params['status'] = $status;
    }
    if (!empty($dateFilter)) {
        $params['date_filter'] = $dateFilter;
    }
    if (!empty($startDate)) {
        $params['start_date'] = $startDate;
    }
    if (!empty($endDate)) {
        $params['end_date'] = $endDate;
    }
    return '/penerimaan?' . http_build_query($params);
}
?>

<script>
function handleDateFilterChange(triggerSubmit = false) {
    const filter = document.getElementById('dateFilter').value;
    const startWrapper = document.getElementById('startDateWrapper');
    const endWrapper = document.getElementById('endDateWrapper');
    const isCustom = filter === 'custom';
    
    if (startWrapper && endWrapper) {
        startWrapper.style.display = isCustom ? 'block' : 'none';
        endWrapper.style.display = isCustom ? 'block' : 'none';
    }
    
    if (!isCustom && triggerSubmit) {
        const startInput = document.querySelector('input[name="start_date"]');
        const endInput = document.querySelector('input[name="end_date"]');
        if (startInput) startInput.value = '';
        if (endInput) endInput.value = '';
        const form = document.getElementById('penerimaanFilterForm');
        if (form) form.submit();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    handleDateFilterChange(false);
});

function confirmDeletePenerimaan(nopenerimaan) {
    if (!nopenerimaan) {
        return;
    }
    const deleteUrl = `/penerimaan/delete/${encodeURIComponent(nopenerimaan)}`;
    showConfirmModal({
        title: 'Konfirmasi Hapus',
        message: `Apakah Anda yakin ingin menghapus penerimaan <strong>${nopenerimaan}</strong>?`,
        buttonText: 'Hapus',
        buttonClass: 'btn-danger',
        onConfirm: function () {
            window.location.href = deleteUrl;
        }
    });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


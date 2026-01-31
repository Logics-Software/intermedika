<?php
$title = 'Tabel Aktivitas';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Tabel Aktivitas</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
				<div class="card-header">
					<div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar Aktivitas</h4>
                        <a href="/tabelaktivitas/create" class="btn btn-primary btn-sm ms-auto">
                            Tambah Aktivitas
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-3 search-filter-card">
                        <form method="GET" action="/tabelaktivitas" id="activitySearchForm">
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-6">
                                    <input type="text" class="form-control" name="search" placeholder="Cari nama aktivitas..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-4 col-md-2">
                                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ($validPerPage as $pp): ?>
                                        <option value="<?= $pp ?>" <?= $perPage == $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4 col-md-2">
                                    <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                                </div>
                                <div class="col-4 col-md-2">
                                    <a href="/tabelaktivitas?page=1&per_page=<?= $perPage ?>&sort_by=<?= htmlspecialchars($sortBy) ?>&sort_order=<?= htmlspecialchars($sortOrder) ?>" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                                </div>
                            </div>
                            <input type="hidden" name="page" value="1">
                            <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sortBy) ?>">
                            <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sortOrder) ?>">
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="sortable">
                                        <a href="?<?= http_build_query(['page' => 1, 'per_page' => $perPage, 'search' => $search, 'sort_by' => 'aktivitas', 'sort_order' => ($sortBy === 'aktivitas' && strtoupper($sortOrder) === 'ASC') ? 'DESC' : 'ASC']) ?>" class="sort-link">
                                            Aktivitas <?= ($sortBy === 'aktivitas') ? icon(strtoupper($sortOrder) === 'ASC' ? 'arrow-up' : 'arrow-down', 'ms-1', 12) : icon('arrows-up-down', 'ms-1', 12) ?>
                                        </a>
                                    </th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data aktivitas.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($records as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['aktivitas']) ?></td>
                                    <td align="center">
                                        <span class="badge bg-<?= $row['status'] === 'aktif' ? 'success' : 'secondary' ?>">
                                            <?= htmlspecialchars(ucwords($row['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="/tabelaktivitas/edit/<?= $row['id'] ?>" class="btn btn-sm btn-warning"><?= icon('pen-to-square', 'me-0 mb-1', 14) ?></a>
                                            <a href="/tabelaktivitas/delete/<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="event.preventDefault(); confirmDelete('Hapus aktivitas <strong><?= htmlspecialchars($row['aktivitas']) ?></strong>?', this.href); return false;"><?= icon('trash-can', 'me-0 mb-1', 14) ?></a>
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
                    
                    // Build link function for pagination
                    $buildLink = function ($p) use ($perPage, $search, $sortBy, $sortOrder) {
                        return '?page=' . $p
                            . '&per_page=' . $perPage
                            . '&search=' . urlencode($search)
                            . '&sort_by=' . $sortBy
                            . '&sort_order=' . $sortOrder;
                    };
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
                                <a class="page-link" href="/tabelaktivitas<?php echo $buildLink($prevPage); ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="/tabelaktivitas' . $buildLink(1) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/tabelaktivitas' . $buildLink($i) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="/tabelaktivitas' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
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
                                <a class="page-link" href="/tabelaktivitas<?php echo $buildLink($nextPage); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>



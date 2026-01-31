<?php
$title = 'Login Log';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">User Log</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
				<div class="card-header">
					<div class="d-flex align-items-center">
						<h4 class="mb-0">Login Log</h4>
					</div>
                </div>

                <div class="card-body">
                    <form method="GET" action="/login-logs" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-2">
                                <input type="text" class="form-control" name="search" placeholder="Search..." value="<?= htmlspecialchars($search ?? '') ?>">
                            </div>
                            <div class="col-6 col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="success" <?= ($status ?? '') === 'success' ? 'selected' : '' ?>>Success</option>
                                    <option value="failed" <?= ($status ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                                    <option value="logout" <?= ($status ?? '') === 'logout' ? 'selected' : '' ?>>Logout</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($dateFrom ?? '') ?>">
                            </div>
                            <div class="col-6 col-md-2">
                                <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($dateTo ?? '') ?>">
                            </div>
                            <div class="col-6 col-md-2">
                                <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                            </div>
                            <div class="col-6 col-md-2">
                                <a href="/login-logs" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Login At</th>
                                    <th>Logout At</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No data found</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['username'] ?? 'N/A') ?> (<?= htmlspecialchars($log['namalengkap'] ?? 'N/A') ?>)</td>
                                    <td><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($log['login_at'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($log['logout_at'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $statusClass = 'secondary';
                                        if ($log['status'] === 'success') $statusClass = 'success';
                                        elseif ($log['status'] === 'failed') $statusClass = 'danger';
                                        elseif ($log['status'] === 'logout') $statusClass = 'info';
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>"><?= htmlspecialchars($log['status'] ?? 'N/A') ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (isset($total) && $total > 0): ?>
                    <div class="mt-3">
                        <p>Total: <?= $total ?> records</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>             
        </div>    
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


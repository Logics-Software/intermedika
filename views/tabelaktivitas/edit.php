<?php
$title = 'Edit Aktivitas';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/tabelaktivitas">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/tabelaktivitas">Tabel Aktivitas</a></li>
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
                        <h4 class="mb-0">Edit Data Aktivitas</h4>
                    </div>
                </div>

                <form method="POST" action="/tabelaktivitas/edit/<?= $record['id'] ?>">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="aktivitas" class="form-label">Nama Aktivitas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="aktivitas" name="aktivitas" required placeholder="Masukkan nama aktivitas" value="<?= htmlspecialchars($record['aktivitas']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <?php foreach ($allowedStatuses as $option): ?>
                                <option value="<?= htmlspecialchars($option) ?>" <?= $record['status'] === $option ? 'selected' : '' ?>><?= htmlspecialchars(ucwords($option)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="/tabelaktivitas" class="btn btn-secondary">
                            <?= icon('cancel', 'me-1 mb-1', 18) ?> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <?= icon('save', 'me-1 mb-1', 18) ?> Update Aktivitas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>



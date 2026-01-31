<?php
$title = 'Pengaturan Sistem';
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
                    <li class="breadcrumb-item active">Pengaturan Sistem</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Pengaturan Sistem</h4>
                    </div>
                </div>

                <form method="POST" action="/setting">
                    <div class="card-body">
                        <div class="mb-4">
                            <label for="order_online" class="form-label">Order Online <span class="text-danger">*</span></label>
                            <select class="form-select" id="order_online" name="order_online" required>
                                <option value="">Pilih Status</option>
                                <option value="aktif" <?= isset($setting) && $setting['order_online'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= isset($setting) && $setting['order_online'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                            <small class="text-muted d-block mt-1">Aktifkan/nonaktifkan fitur order online</small>
                        </div>

                        <div class="mb-4">
                            <label for="inkaso_online" class="form-label">Inkaso Online <span class="text-danger">*</span></label>
                            <select class="form-select" id="inkaso_online" name="inkaso_online" required>
                                <option value="">Pilih Status</option>
                                <option value="aktif" <?= isset($setting) && $setting['inkaso_online'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= isset($setting) && $setting['inkaso_online'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                            <small class="text-muted d-block mt-1">Aktifkan/nonaktifkan fitur inkaso online</small>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="/dashboard" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                        <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

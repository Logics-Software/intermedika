<?php
$title = 'Ubah Password';
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
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/profile">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/profile">Profil</a></li>
                    <li class="breadcrumb-item active">Ubah Password</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Ubah Password</h4>
                    </div>
                </div>

                <form method="POST" action="/profile/change-password">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Lama <span class="text-danger">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="Masukkan password lama">
                                <button type="button" class="password-toggle-btn" data-target="current_password" aria-label="Toggle password visibility">
                                    <?= icon('eye-slash', '', 18) ?>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6" placeholder="Minimal 6 karakter">
                                <button type="button" class="password-toggle-btn" data-target="new_password" aria-label="Toggle password visibility">
                                    <?= icon('eye-slash', '', 18) ?>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Ulangi password baru">
                                <button type="button" class="password-toggle-btn" data-target="confirm_password" aria-label="Toggle password visibility">
                                    <?= icon('eye-slash', '', 18) ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="/dashboard" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                        <button type="submit" class="btn btn-warning"><?= icon('save', 'me-1 mb-1', 18) ?>Ubah Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordToggles = document.querySelectorAll('.password-toggle-btn');
    
    passwordToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input) {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                
                const icon = this.querySelector('img');
                if (icon) {
                    const baseUrl = <?= json_encode($baseUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
                    if (type === 'password') {
                        icon.src = baseUrl + '/assets/icons/eye-slash.svg?v=' + Date.now();
                    } else {
                        icon.src = baseUrl + '/assets/icons/eye.svg?v=' + Date.now();
                    }
                }
            }
        });
    });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


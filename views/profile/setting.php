<?php
$title = 'Settings - Biometrik Login';
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
                    <li class="breadcrumb-item active">Setting</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Setting</h4>
                    </div>
                </div>

                <div class="card-body">
                    <div id="biometricSection">
                        <div id="biometricNotSupported" class="alert alert-warning mb-3" style="display: none;">
                            Browser Anda tidak mendukung WebAuthn. Pastikan menggunakan browser modern (Chrome, Firefox, Edge, Safari).
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1">Biometrik Login</h5>
                                <p class="text-muted mb-0 small">Aktifkan biometrik (fingerprint/face ID) untuk login lebih cepat dan aman.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="biometricToggle" disabled>
                                <label class="form-check-label" for="biometricToggle"></label>
                            </div>
                        </div>
                        
                        <div id="biometricCredentialsList" class="mb-3" style="display: none;"></div>
                        <div id="biometricMessage" class="alert mb-0" style="display: none;"></div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a href="/dashboard" class="btn btn-secondary"><?= icon('back', 'me-1 mb-1', 18) ?>Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/webauthn.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const biometricSection = document.getElementById('biometricSection');
    const biometricNotSupported = document.getElementById('biometricNotSupported');
    const biometricCredentialsList = document.getElementById('biometricCredentialsList');
    const biometricToggle = document.getElementById('biometricToggle');
    const biometricMessage = document.getElementById('biometricMessage');
    let currentCredentials = [];

    // Check WebAuthn support
    if (!WebAuthnHelper.isSupported()) {
        biometricNotSupported.style.display = 'block';
        biometricToggle.disabled = true;
        return;
    }

    // Load credentials
    loadCredentials();

    // Toggle switch change handler
    biometricToggle.addEventListener('change', async function() {
        if (this.checked) {
            // Turn ON - Register biometric
            await registerBiometric();
        } else {
            // Turn OFF - Delete all credentials
            await deleteAllCredentials();
        }
    });
    
    async function registerBiometric() {
        biometricToggle.disabled = true;
        hideMessage();

        try {
            const result = await WebAuthnHelper.registerBiometric();
            
            if (result.success) {
                showMessage('Biometrik berhasil diaktifkan! Anda sekarang dapat menggunakan biometrik untuk login.', 'success');
                await loadCredentials();
                biometricToggle.checked = true;
            } else {
                showMessage(result.error || 'Gagal mengaktifkan biometrik', 'danger');
                biometricToggle.checked = false;
            }
        } catch (error) {
            console.error('Biometric registration error:', error);
            
            let errorMessage = 'Gagal mengaktifkan biometrik. ';
            if (error.name === 'NotAllowedError') {
                errorMessage += 'Autentikasi dibatalkan atau tidak diizinkan. Pastikan biometrik sudah dikonfigurasi di perangkat Anda.';
            } else if (error.name === 'InvalidStateError') {
                errorMessage += 'Credential sudah terdaftar atau tidak valid.';
            } else if (error.message) {
                errorMessage += error.message;
            } else {
                errorMessage += 'Pastikan perangkat Anda mendukung biometrik dan sudah dikonfigurasi.';
            }
            
            showMessage(errorMessage, 'danger');
            biometricToggle.checked = false;
        } finally {
            biometricToggle.disabled = false;
        }
    }
    
    async function deleteAllCredentials() {
        if (currentCredentials.length === 0) {
            biometricToggle.checked = false;
            return;
        }

        if (!confirm('Yakin ingin menonaktifkan biometrik login? Semua credential biometrik akan dihapus.')) {
            biometricToggle.checked = true;
            return;
        }

        biometricToggle.disabled = true;
        hideMessage();

        try {
            let allDeleted = true;
            for (const cred of currentCredentials) {
                const success = await WebAuthnHelper.deleteCredential(cred.credential_id);
                if (!success) {
                    allDeleted = false;
                }
            }
            
            if (allDeleted) {
                showMessage('Biometrik login berhasil dinonaktifkan', 'success');
                await loadCredentials();
                biometricToggle.checked = false;
            } else {
                showMessage('Beberapa credential gagal dihapus', 'warning');
                await loadCredentials();
            }
        } catch (error) {
            console.error('Error deleting credentials:', error);
            showMessage('Gagal menonaktifkan biometrik', 'danger');
            biometricToggle.checked = true;
        } finally {
            biometricToggle.disabled = false;
        }
    }

    async function loadCredentials() {
        try {
            const credentials = await WebAuthnHelper.listCredentials();
            currentCredentials = credentials;
            
            // Update toggle state
            if (credentials.length > 0) {
                biometricToggle.checked = true;
                biometricCredentialsList.style.display = 'block';
                
                let html = '<div class="list-group mt-3">';
                credentials.forEach(cred => {
                    const createdDate = new Date(cred.created_at).toLocaleDateString('id-ID');
                    const lastUsed = cred.last_used_at ? new Date(cred.last_used_at).toLocaleDateString('id-ID') : 'Belum pernah digunakan';
                    const shortId = cred.credential_id.substring(0, 20) + '...';
                    
                    html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center d-none">
                            <div>
                                <div class="fw-semibold">Biometrik Credential</div>
                                <small class="text-muted">ID: ${shortId}</small><br>
                                <small class="text-muted">Dibuat: ${createdDate} | Terakhir digunakan: ${lastUsed}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCredential('${cred.credential_id}')">
                                Hapus
                            </button>
                        </div>
                    `;
                });
                html += '</div>';
                
                biometricCredentialsList.innerHTML = html;
            } else {
                biometricToggle.checked = false;
                biometricCredentialsList.style.display = 'none';
            }
            
            // Enable toggle after loading
            biometricToggle.disabled = false;
        } catch (error) {
            console.error('Error loading credentials:', error);
            biometricCredentialsList.innerHTML = '<p class="text-danger mb-0">Gagal memuat daftar biometrik.</p>';
            biometricToggle.checked = false;
            biometricToggle.disabled = false;
        }
    }

    window.deleteCredential = async function(credentialId) {
        if (!confirm('Yakin ingin menghapus biometrik ini?')) {
            return;
        }

        try {
            const success = await WebAuthnHelper.deleteCredential(credentialId);
            
            if (success) {
                showMessage('Biometrik berhasil dihapus', 'success');
                await loadCredentials();
                
                // If no credentials left, uncheck toggle
                if (currentCredentials.length === 0) {
                    biometricToggle.checked = false;
                }
            } else {
                showMessage('Gagal menghapus biometrik', 'danger');
            }
        } catch (error) {
            console.error('Error deleting credential:', error);
            showMessage('Gagal menghapus biometrik', 'danger');
        }
    };

    function showMessage(message, type) {
        biometricMessage.textContent = message;
        biometricMessage.className = 'alert alert-' + type + ' mt-3 mb-0';
        biometricMessage.style.display = 'block';
        setTimeout(hideMessage, 5000);
    }

    function hideMessage() {
        biometricMessage.style.display = 'none';
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


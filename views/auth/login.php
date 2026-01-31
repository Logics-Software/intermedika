<?php
$title = 'Login';
require __DIR__ . '/../layouts/header.php';
?>

<?php
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}
?>
<div class="login-container">
    <div class="login-card card">
        <div class="card-body">
            <div class="login-logo text-center mb-4">
                <img src="<?= htmlspecialchars($baseUrl) ?>/assets/images/logo.png" alt="Logo" class="login-logo-img">
            </div>
            <h3 class="card-title text-center">Login Indoprima Online</h3>
            <form method="POST" action="/login" class="login-form">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus placeholder="Masukkan username">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan password">
                        <button type="button" class="password-toggle-btn" id="passwordToggle" aria-label="Toggle password visibility">
                            <?= icon('eye-slash', '', 18) ?>
                        </button>
                    </div>
                </div>
                <div class="login-buttons-wrapper d-flex gap-2">
                    <button type="submit" class="login-btn flex-grow-1">
                        <?= icon('login', '', 20) ?> Login
                    </button>
                    <button type="button" class="btn btn-outline-primary d-md-none" id="btnMobileBiometric" style="display: none;" title="Login dengan Biometrik">
                        <?= icon('fingerprint', '', 24) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/webauthn.js"></script>
<script>
// Scroll login form to top when keyboard appears on mobile
document.addEventListener('DOMContentLoaded', function() {
    const loginCard = document.querySelector('.login-card');
    const inputs = document.querySelectorAll('.login-form input[type="text"], .login-form input[type="password"]');
    
    if (loginCard && inputs.length > 0) {
        inputs.forEach(function(input) {
            input.addEventListener('focus', function() {
                // Check if mobile device
                const isMobile = window.innerWidth <= 991.98;
                
                if (isMobile) {
                    // Delay to ensure keyboard is shown and viewport is adjusted
                    setTimeout(function() {
                        const cardRect = loginCard.getBoundingClientRect();
                        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
                        const targetScroll = currentScroll + cardRect.top - 20; // 20px offset from top
                        
                        window.scrollTo({
                            top: targetScroll,
                            behavior: 'smooth'
                        });
                    }, 300);
                }
            });
        });
    }

    // Toggle password visibility
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('passwordToggle');
    const usernameInput = document.getElementById('username');
    const biometricSection = document.getElementById('biometricSection');
    const btnBiometricLogin = document.getElementById('btnBiometricLogin');
    const btnMobileBiometric = document.getElementById('btnMobileBiometric');
    const biometricError = document.getElementById('biometricError');
    
    if (passwordInput && passwordToggle) {
        // Store base URL from PHP
        const baseUrl = <?= json_encode($baseUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const icon = passwordToggle.querySelector('img');
        
        passwordToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle input type
            const currentType = passwordInput.type;
            const newType = currentType === 'password' ? 'text' : 'password';
            
            // Change input type immediately
            passwordInput.type = newType;
            
            // Toggle icon with cache busting
            if (icon) {
                if (newType === 'password') {
                    icon.src = baseUrl + '/assets/icons/eye-slash.svg?v=' + Date.now();
                    icon.alt = 'Show password';
                    passwordToggle.setAttribute('aria-label', 'Show password');
                } else {
                    icon.src = baseUrl + '/assets/icons/eye.svg?v=' + Date.now();
                    icon.alt = 'Hide password';
                    passwordToggle.setAttribute('aria-label', 'Hide password');
                }
            }
        });
    }

    // Check WebAuthn support
    if (!WebAuthnHelper.isSupported()) {
        if (biometricSection) biometricSection.style.display = 'none';
        return;
    }

    // Check if user has credentials when username changes
    let checkCredentialsTimeout;
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            clearTimeout(checkCredentialsTimeout);
            const username = this.value.trim();
            
            if (username.length < 3) {
                if (biometricSection) biometricSection.style.display = 'none';
                return;
            }

            checkCredentialsTimeout = setTimeout(async function() {
                try {
                    const hasCredentials = await WebAuthnHelper.hasCredentials(username);
                    
                    if (hasCredentials) {
                        if (biometricSection) biometricSection.style.display = 'block';
                        if (btnBiometricLogin) btnBiometricLogin.style.display = 'flex';
                        if (biometricError) biometricError.style.display = 'none';
                    } else {
                        if (biometricSection) biometricSection.style.display = 'none';
                    }
                    
                    updateMobileBiometricButton();
                } catch (error) {
                    console.error('Error checking credentials:', error);
                    if (biometricSection) biometricSection.style.display = 'none';
                }
            }, 500);
        });
    }

    // Save username to localStorage when form is submitted successfully
    const loginForm = document.querySelector('.login-form');
    if (loginForm && usernameInput) {
        loginForm.addEventListener('submit', function(e) {
            const username = usernameInput.value.trim();
            if (username.length > 0) {
                localStorage.setItem('last_username', username);
            }
        });
    }

    // Load and fill last username in input field (mobile only)
    function loadLastUsername() {
        const isMobile = window.matchMedia('(max-width: 767.98px)').matches;
        if (!isMobile || !usernameInput) {
            return;
        }

        const lastUsername = localStorage.getItem('last_username');
        const currentUsername = usernameInput.value.trim();
        
        if (lastUsername && lastUsername.length > 0 && currentUsername.length === 0) {
            usernameInput.value = lastUsername;
            setTimeout(function() {
                usernameInput.dispatchEvent(new Event('input', { bubbles: true }));
            }, 300);
        }
    }

    loadLastUsername();

    // Function to update mobile biometric button visibility
    function updateMobileBiometricButton() {
        if (!usernameInput || !btnMobileBiometric) return;
        
        const username = usernameInput.value.trim();
        const isMobile = window.matchMedia('(max-width: 767.98px)').matches;
        
        if (username.length >= 3 && WebAuthnHelper.isSupported() && isMobile) {
            WebAuthnHelper.hasCredentials(username).then(function(hasCredentials) {
                if (hasCredentials) {
                    btnMobileBiometric.style.display = 'flex';
                    btnMobileBiometric.style.alignItems = 'center';
                    btnMobileBiometric.style.justifyContent = 'center';
                } else {
                    btnMobileBiometric.style.display = 'none';
                }
            }).catch(function(error) {
                console.error('Error checking credentials for mobile biometric button:', error);
                btnMobileBiometric.style.display = 'none';
            });
        } else {
            btnMobileBiometric.style.display = 'none';
        }
    }

    // Check mobile biometric button when username changes
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            updateMobileBiometricButton();
        });
    }

    // Check mobile biometric button on window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(updateMobileBiometricButton, 250);
    });

    // Initial check for mobile biometric button
    setTimeout(function() {
        updateMobileBiometricButton();
        if (usernameInput && usernameInput.value.trim().length >= 3) {
            WebAuthnHelper.hasCredentials(usernameInput.value.trim()).then(function(hasCredentials) {
                if (hasCredentials && biometricSection) {
                    biometricSection.style.display = 'block';
                    if (btnBiometricLogin) btnBiometricLogin.style.display = 'flex';
                }
            });
        }
    }, 500);

    // Handle mobile biometric button click
    if (btnMobileBiometric) {
        btnMobileBiometric.addEventListener('click', async function() {
            if (!usernameInput) return;
            
            const username = usernameInput.value.trim();
            
            if (!username) {
                showBiometricError('Masukkan username terlebih dahulu');
                usernameInput.focus();
                return;
            }

            btnMobileBiometric.disabled = true;
            const originalHTML = btnMobileBiometric.innerHTML;
            btnMobileBiometric.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            if (biometricError) biometricError.style.display = 'none';

            try {
                const result = await WebAuthnHelper.authenticateBiometric(username);
                
                if (result.success && result.redirect) {
                    if (username.length > 0) {
                        localStorage.setItem('last_username', username);
                    }
                    window.location.href = result.redirect;
                } else {
                    showBiometricError(result.error || 'Login biometrik gagal');
                }
            } catch (error) {
                console.error('Biometric login error:', error);
                
                let errorMessage = 'Gagal melakukan login biometrik. ';
                if (error.name === 'NotAllowedError') {
                    errorMessage += 'Autentikasi dibatalkan atau tidak diizinkan.';
                } else if (error.name === 'NotFoundError') {
                    errorMessage += 'Tidak ada credential biometrik ditemukan.';
                } else if (error.name === 'InvalidStateError') {
                    errorMessage += 'Credential sudah digunakan atau tidak valid.';
                } else if (error.message) {
                    errorMessage += error.message;
                } else {
                    errorMessage += 'Pastikan perangkat Anda mendukung biometrik dan sudah dikonfigurasi.';
                }
                
                showBiometricError(errorMessage);
            } finally {
                btnMobileBiometric.disabled = false;
                btnMobileBiometric.innerHTML = originalHTML;
            }
        });
    }

    // Handle biometric login (desktop)
    if (btnBiometricLogin) {
        btnBiometricLogin.addEventListener('click', async function() {
            if (!usernameInput) return;
            
            const username = usernameInput.value.trim();
            
            if (!username) {
                showBiometricError('Masukkan username terlebih dahulu');
                usernameInput.focus();
                return;
            }

            btnBiometricLogin.disabled = true;
            const originalHTML = btnBiometricLogin.innerHTML;
            btnBiometricLogin.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            if (biometricError) biometricError.style.display = 'none';

            try {
                const result = await WebAuthnHelper.authenticateBiometric(username);
                
                if (result.success && result.redirect) {
                    if (username.length > 0) {
                        localStorage.setItem('last_username', username);
                    }
                    window.location.href = result.redirect;
                } else {
                    showBiometricError(result.error || 'Login biometrik gagal');
                }
            } catch (error) {
                console.error('Biometric login error:', error);
                
                let errorMessage = 'Gagal melakukan login biometrik. ';
                if (error.name === 'NotAllowedError') {
                    errorMessage += 'Autentikasi dibatalkan atau tidak diizinkan.';
                } else if (error.name === 'NotFoundError') {
                    errorMessage += 'Tidak ada credential biometrik ditemukan.';
                } else if (error.name === 'InvalidStateError') {
                    errorMessage += 'Credential sudah digunakan atau tidak valid.';
                } else if (error.message) {
                    errorMessage += error.message;
                } else {
                    errorMessage += 'Pastikan perangkat Anda mendukung biometrik dan sudah dikonfigurasi.';
                }
                
                showBiometricError(errorMessage);
            } finally {
                btnBiometricLogin.disabled = false;
                btnBiometricLogin.innerHTML = originalHTML;
            }
        });
    }

    function showBiometricError(message) {
        if (biometricError) {
            biometricError.textContent = message;
            biometricError.style.display = 'block';
            setTimeout(function() {
                if (biometricError) biometricError.style.display = 'none';
            }, 5000);
        }
    }
});
</script>

<style>
.login-buttons-wrapper {
    display: flex;
    align-items: stretch;
    gap: 0.5rem;
}

#btnMobileBiometric {
    display: none;
    min-width: 50px;
    padding: 0.5rem;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 0.5rem;
    background-color: #ffffff;
    border: 1.5px solid #e5e7eb;
    color: #374151;
    transition: all 0.2s ease;
}

#btnMobileBiometric:hover {
    border-color: #0b0e1a;
    color: #0b0e1a;
    background-color: #f9fafb;
}

#btnMobileBiometric svg,
#btnMobileBiometric img {
    width: 24px;
    height: 24px;
}

#btnBiometricLogin {
    display: none;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
}

#btnBiometricLogin svg {
    flex-shrink: 0;
}

#biometricError {
    font-size: 0.875rem;
}

@media (max-width: 767.98px) {
    #btnMobileBiometric {
        display: flex;
    }
}

@media (min-width: 768px) {
    #btnMobileBiometric {
        display: none !important;
    }
}
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


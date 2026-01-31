    <?php
    $config = require __DIR__ . '/../../config/app.php';
    $baseUrl = rtrim($config['base_url'], '/');
    if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
        $baseUrl = '/';
    }
    ?>
    <?php
    // Cache busting - use file modification time as version
    $jsVersion = file_exists(__DIR__ . '/../../assets/js/bootstrap.bundle.min.js') ? filemtime(__DIR__ . '/../../assets/js/bootstrap.bundle.min.js') : time();
    ?>
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/bootstrap.bundle.min.js?v=<?= $jsVersion ?>"></script>
    
    <?php
    // Download handler script - load on pages with file downloads
    $currentPath = $_SERVER['REQUEST_URI'] ?? '';
    $downloadPages = ['/messages/', '/orders/', '/visits/'];
    $needsDownloadHandler = false;
    
    foreach ($downloadPages as $page) {
        if (strpos($currentPath, $page) !== false) {
            $needsDownloadHandler = true;
            break;
        }
    }
    
    if ($needsDownloadHandler) {
        $downloadVersion = file_exists(__DIR__ . '/../../assets/js/download-handler.js') ? filemtime(__DIR__ . '/../../assets/js/download-handler.js') : time();
        echo '<script src="' . htmlspecialchars($baseUrl) . '/assets/js/download-handler.js?v=' . $downloadVersion . '"></script>';
    }
    ?>
    
    <?php
    if (!empty($additionalScripts)) {
        $scripts = is_array($additionalScripts) ? $additionalScripts : [$additionalScripts];
        foreach ($scripts as $scriptSrc) {
            if (!empty($scriptSrc)) {
                echo '<script src="' . htmlspecialchars($scriptSrc) . '"></script>';
            }
        }
    }
    ?>

    <!-- Global Alert/Confirm Modals -->
    <div class="modal fade" id="appAlertModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white" id="appAlertHeader">
                    <h5 class="modal-title" id="appAlertTitle">Informasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="appAlertMessage">...</div>
                <div class="modal-footer border-0 bg-light" id="appAlertFooter">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="appAlertOkBtn">OK</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="appConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark" id="appConfirmHeader">
                    <h5 class="modal-title" id="appConfirmTitle">Konfirmasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="appConfirmMessage">...</div>
                    <form id="appConfirmDynamicForm" class="mt-2" novalidate style="display:none;"></form>
                </div>
                <div class="modal-footer border-0 bg-light" id="appConfirmFooter">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="appConfirmCancelBtn">Batal</button>
                    <button type="button" class="btn btn-danger" id="appConfirmOkBtn">Ya, Lanjut</button>
                </div>
            </div>
        </div>
    </div>


    <script>
    // Initialize toast messages
    document.addEventListener('DOMContentLoaded', function() {
        const toastElements = document.querySelectorAll('.toast');
        toastElements.forEach(function(toastEl) {
            // Set delay based on type: 5 seconds for success, 7 seconds for error
            const isError = toastEl.id.includes('error') || toastEl.id.includes('danger');
            const delay = isError ? 7000 : 5000;
            
            // Set custom delay
            toastEl.setAttribute('data-bs-delay', delay);
            
            // Initialize and show toast
            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: delay
            });
            toast.show();
        });
    });

    // Custom alert & confirm helpers
    (function () {
        let alertModal, confirmModal;
        let confirmResolve = null;

        function ensureModals() {
            if (!alertModal) {
                const el = document.getElementById('appAlertModal');
                if (el) alertModal = new bootstrap.Modal(el);
            }
            if (!confirmModal) {
                const el = document.getElementById('appConfirmModal');
                if (el) confirmModal = new bootstrap.Modal(el);
            }
        }

        window.showAlert = function showAlert(opts) {
            ensureModals();
            const title = (opts && opts.title) || 'Informasi';
            const message = (opts && opts.message) || '';
            const buttonText = (opts && opts.buttonText) || 'OK';
            const buttonClass = (opts && opts.buttonClass) || 'btn-primary';
            const headerClass = (opts && opts.headerClass) || 'bg-primary text-white';
            const footerClass = (opts && opts.footerClass) || 'bg-light';

            const titleEl = document.getElementById('appAlertTitle');
            const msgEl = document.getElementById('appAlertMessage');
            const okBtn = document.getElementById('appAlertOkBtn');
            const headerEl = document.getElementById('appAlertHeader');
            const footerEl = document.getElementById('appAlertFooter');

            if (titleEl) titleEl.textContent = title;
            if (msgEl) msgEl.innerHTML = message;
            if (okBtn) {
                okBtn.textContent = buttonText;
                okBtn.className = 'btn ' + buttonClass;
            }
            if (headerEl) headerEl.className = 'modal-header ' + headerClass;
            if (footerEl) footerEl.className = 'modal-footer border-0 ' + footerClass;

            if (alertModal) alertModal.show();
        };

        window.showConfirmModal = function showConfirmModal(opts) {
            ensureModals();
            const title = (opts && opts.title) || 'Konfirmasi';
            const message = (opts && opts.message) || 'Lanjutkan?';
            const okText = (opts && opts.buttonText) || 'Ya';
            const okClass = (opts && opts.buttonClass) || 'btn-danger';
            const cancelText = (opts && opts.cancelText) || 'Batal';
            const headerClass = (opts && opts.headerClass) || 'bg-warning text-dark';
            const footerClass = (opts && opts.footerClass) || 'bg-light';

            const titleEl = document.getElementById('appConfirmTitle');
            const msgEl = document.getElementById('appConfirmMessage');
            const okBtn = document.getElementById('appConfirmOkBtn');
            const cancelBtn = document.getElementById('appConfirmCancelBtn');
            const headerEl = document.getElementById('appConfirmHeader');
            const footerEl = document.getElementById('appConfirmFooter');

            if (titleEl) titleEl.textContent = title;
            if (msgEl) msgEl.innerHTML = message;
            if (okBtn) {
                okBtn.textContent = okText;
                okBtn.className = 'btn ' + okClass;
            }
            if (cancelBtn) cancelBtn.textContent = cancelText;
            if (headerEl) headerEl.className = 'modal-header ' + headerClass;
            if (footerEl) footerEl.className = 'modal-footer border-0 ' + footerClass;

            // Clean previous handlers
            const okClone = okBtn.cloneNode(true);
            okBtn.parentNode.replaceChild(okClone, okBtn);
            const cancelClone = cancelBtn.cloneNode(true);
            cancelBtn.parentNode.replaceChild(cancelClone, cancelBtn);

            return new Promise(function (resolve) {
                confirmResolve = resolve;
                okClone.addEventListener('click', function () {
                    if (confirmModal) confirmModal.hide();
                    resolve(true);
                    confirmResolve = null;
                });
                cancelClone.addEventListener('click', function () {
                    if (confirmModal) confirmModal.hide();
                    resolve(false);
                    confirmResolve = null;
                });
                if (confirmModal) confirmModal.show();
            }).then(function (result) {
                if (typeof opts?.onConfirm === 'function' && result) {
                    opts.onConfirm();
                }
                if (typeof opts?.onCancel === 'function' && !result) {
                    opts.onCancel();
                }
                return result;
            });
        };

        // Confirm with dynamic form (reuses the same confirm modal)
        window.showConfirmForm = function showConfirmForm(opts) {
            ensureModals();
            const title = (opts && opts.title) || 'Konfirmasi';
            const message = (opts && opts.message) || '';
            const submitText = (opts && opts.submitText) || 'Kirim';
            const submitClass = (opts && opts.submitClass) || 'btn-primary';
            const cancelText = (opts && opts.cancelText) || 'Batal';
            const fields = Array.isArray(opts?.fields) ? opts.fields : [];
            const headerClass = (opts && opts.headerClass) || 'bg-info text-white';
            const footerClass = (opts && opts.footerClass) || 'bg-light';

            const titleEl = document.getElementById('appConfirmTitle');
            const msgEl = document.getElementById('appConfirmMessage');
            const formEl = document.getElementById('appConfirmDynamicForm');
            const okBtn = document.getElementById('appConfirmOkBtn');
            const cancelBtn = document.getElementById('appConfirmCancelBtn');
            const headerEl = document.getElementById('appConfirmHeader');
            const footerEl = document.getElementById('appConfirmFooter');

            if (titleEl) titleEl.textContent = title;
            if (msgEl) msgEl.innerHTML = message || '';
            if (okBtn) {
                okBtn.textContent = submitText;
                okBtn.className = 'btn ' + submitClass;
            }
            if (cancelBtn) cancelBtn.textContent = cancelText;
            if (headerEl) headerEl.className = 'modal-header ' + headerClass;
            if (footerEl) footerEl.className = 'modal-footer border-0 ' + footerClass;

            // Build form controls
            if (formEl) {
                formEl.style.display = 'block';
                formEl.innerHTML = '';
                fields.forEach(function(field) {
                    const f = Object.assign({
                        type: 'text',
                        name: '',
                        label: '',
                        required: false,
                        placeholder: '',
                        value: '',
                        options: [] // for select
                    }, field || {});

                    const wrapper = document.createElement('div');
                    wrapper.className = f.type === 'hidden' ? '' : 'mb-3';

                    if (f.type !== 'hidden' && f.label) {
                        const labelEl = document.createElement('label');
                        labelEl.className = 'form-label';
                        labelEl.setAttribute('for', 'confirm-form-' + f.name);
                        labelEl.textContent = f.label + (f.required ? ' *' : '');
                        wrapper.appendChild(labelEl);
                    }

                    let inputEl;
                    if (f.type === 'textarea') {
                        inputEl = document.createElement('textarea');
                        inputEl.className = 'form-control';
                        inputEl.rows = f.rows || 3;
                        inputEl.placeholder = f.placeholder || '';
                        inputEl.value = f.value || '';
                    } else if (f.type === 'select') {
                        inputEl = document.createElement('select');
                        inputEl.className = 'form-select';
                        (Array.isArray(f.options) ? f.options : []).forEach(function(opt) {
                            const o = document.createElement('option');
                            o.value = String(opt?.value ?? '');
                            o.textContent = String(opt?.label ?? opt?.value ?? '');
                            if (String(f.value ?? '') === o.value) o.selected = true;
                            inputEl.appendChild(o);
                        });
                    } else if (f.type === 'checkbox') {
                        // For single checkbox
                        const div = document.createElement('div');
                        div.className = 'form-check';
                        inputEl = document.createElement('input');
                        inputEl.type = 'checkbox';
                        inputEl.className = 'form-check-input';
                        inputEl.checked = !!f.value;
                        const labelRight = document.createElement('label');
                        labelRight.className = 'form-check-label';
                        labelRight.textContent = f.placeholder || f.label || '';
                        labelRight.setAttribute('for', 'confirm-form-' + f.name);
                        div.appendChild(inputEl);
                        div.appendChild(labelRight);
                        // override wrapper behavior
                        wrapper.appendChild(div);
                    } else {
                        inputEl = document.createElement('input');
                        inputEl.type = f.type;
                        inputEl.className = 'form-control';
                        inputEl.placeholder = f.placeholder || '';
                        if (f.value !== undefined && f.value !== null) {
                            inputEl.value = String(f.value);
                        }
                    }

                    if (inputEl) {
                        inputEl.id = 'confirm-form-' + f.name;
                        inputEl.name = f.name;
                        if (f.required) {
                            inputEl.setAttribute('required', 'required');
                        }
                        if (f.min !== undefined) inputEl.setAttribute('min', f.min);
                        if (f.max !== undefined) inputEl.setAttribute('max', f.max);
                        if (f.pattern) inputEl.setAttribute('pattern', f.pattern);
                        if (f.type !== 'checkbox') {
                            wrapper.appendChild(inputEl);
                        }
                    }
                    formEl.appendChild(wrapper);
                });
            }

            // Reset previous handlers
            const okClone = okBtn.cloneNode(true);
            okBtn.parentNode.replaceChild(okClone, okBtn);
            const cancelClone = cancelBtn.cloneNode(true);
            cancelBtn.parentNode.replaceChild(cancelClone, cancelBtn);

            return new Promise(function (resolve) {
                const onSubmit = function () {
                    if (!formEl) {
                        resolve(false);
                        return;
                    }
                    // Simple validation
                    const invalid = Array.from(formEl.querySelectorAll('[required]')).some(function(el) {
                        if (el.type === 'checkbox') {
                            return !el.checked;
                        }
                        return !el.value;
                    });
                    if (invalid) {
                        // Basic feedback
                        formEl.classList.add('was-validated');
                        return;
                    }
                    const formData = new FormData(formEl);
                    const values = {};
                    formData.forEach(function(value, key) {
                        if (Object.prototype.hasOwnProperty.call(values, key)) {
                            if (!Array.isArray(values[key])) values[key] = [values[key]];
                            values[key].push(value);
                        } else {
                            values[key] = value;
                        }
                    });
                    if (confirmModal) confirmModal.hide();
                    // reset UI
                    if (formEl) {
                        formEl.innerHTML = '';
                        formEl.style.display = 'none';
                        formEl.classList.remove('was-validated');
                    }
                    resolve({ ok: true, values: values });
                };

                okClone.addEventListener('click', onSubmit);
                cancelClone.addEventListener('click', function () {
                    if (confirmModal) confirmModal.hide();
                    // reset UI
                    if (formEl) {
                        formEl.innerHTML = '';
                        formEl.style.display = 'none';
                        formEl.classList.remove('was-validated');
                    }
                    resolve({ ok: false });
                });

                if (confirmModal) confirmModal.show();
            }).then(function (result) {
                if (result && result.ok && typeof opts?.onSubmit === 'function') {
                    try { opts.onSubmit(result.values); } catch (e) {}
                } else if ((!result || !result.ok) && typeof opts?.onCancel === 'function') {
                    try { opts.onCancel(); } catch (e) {}
                }
                return result;
            });
        };

        // Backward-compatible delete helper
        window.confirmDelete = function (message, url) {
            return showConfirmModal({
                title: 'Konfirmasi Hapus',
                message: message || 'Yakin ingin menghapus data ini?',
                buttonText: 'Hapus',
                buttonClass: 'btn-danger',
                onConfirm: function () {
                    if (url) window.location.href = url;
                }
            });
        };

        // Auto-detect sortable headers and set icons / active state
        document.addEventListener('DOMContentLoaded', function () {
            try {
                const qs = new URLSearchParams(window.location.search);
                const currentSortBy = (qs.get('sort_by') || '').toString();
                const currentSortOrder = ((qs.get('sort_order') || '').toString().toUpperCase() === 'ASC') ? 'ASC' : 'DESC';

                document.querySelectorAll('table thead th').forEach(function (th) {
                    const a = th.querySelector('a[href*="sort_by="]');
                    if (!a) return;
                    th.classList.add('th-sortable');

                    // parse sort_by in this link
                    let linkSortBy = '';
                    try {
                        const href = a.getAttribute('href') || '';
                        const linkUrl = new URL(href, window.location.origin);
                        linkSortBy = (linkUrl.searchParams.get('sort_by') || '').toString();
                    } catch (e) {}

                    if (currentSortBy && linkSortBy && currentSortBy === linkSortBy) {
                        th.classList.remove('sorted-asc', 'sorted-desc');
                        th.classList.add(currentSortOrder === 'ASC' ? 'sorted-asc' : 'sorted-desc');
                    } else {
                        th.classList.remove('sorted-asc', 'sorted-desc');
                    }
                });
            } catch (e) {
                // no-op
            }
        });
    })();
    </script>
    <?php
    if (!empty($additionalInlineScripts)) {
        $scripts = is_array($additionalInlineScripts) ? $additionalInlineScripts : [$additionalInlineScripts];
        foreach ($scripts as $script) {
            if (!empty($script)) {
                echo '<script>' . "\n" . $script . "\n" . '</script>' . "\n";
            }
        }
    }
    ?>
    
    <!-- Floating Home Button -->
    <a href="/dashboard" class="floating-home-btn" aria-label="Kembali ke Dashboard">
        <?= icon('house', '', 24) ?>
    </a>
</body>
</html>


<?php
$title = 'Check-out Kunjungan';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

$additionalStyles = $additionalStyles ?? [];
$additionalStyles[] = 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css';
$additionalStyles[] = $baseUrl . '/assets/css/mapbox-gl-geocoder.css';

$additionalScripts = $additionalScripts ?? [];
$additionalScripts[] = 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js';
$additionalScripts[] = $baseUrl . '/assets/js/mapbox-gl-geocoder.min.js';

$mapboxToken = $mapboxToken ?? ($config['mapbox_access_token'] ?? '');
$hasMapbox = !empty($mapboxToken);

require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="mb-0">Check-out</h2>
        </div>
        <div>
            <a href="/visits" class="btn btn-secondary"><?= icon('back', 'mb-1 me-2', 16) ?>  Kembali</a>
        </div>
    </div>
</div>

<?php if (empty($visit)): ?>
<div class="alert alert-danger">Data kunjungan tidak ditemukan.</div>
<?php else: ?>

<div class="row">
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Detail Kunjungan</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h5 class="fw-semibold mb-1"><?= htmlspecialchars($visit['namacustomer'] ?? '-') ?></h5>
                    <div class="text-muted small mb-2">
                        <?= htmlspecialchars($visit['kodecustomer']) ?> &bull; Alamat: <?= htmlspecialchars($visit['alamatcustomer'].', '.$visit['kotacustomer'].', '.$visit['kotacustomer'] ?? '-') ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="small text-muted">Mulai</div>
                            <div class="fw-semibold"><?= date('d/m/Y H:i', strtotime($visit['check_in_time'])) ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Lokasi Check-in</div>
                            <div class="fw-semibold">Lat <?= htmlspecialchars(number_format($visit['check_in_lat'], 6)) ?>, Lng <?= htmlspecialchars(number_format($visit['check_in_long'], 6)) ?></div>
                        </div>
                    </div>
                </div>

                <hr>

                <h6 class="fw-semibold mb-3">Aktivitas Kunjungan</h6>
                <form action="/visits/<?= $visit['visit_id'] ?>/activities" method="POST" id="activityForm" class="row g-2 align-items-end mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Jenis Aktivitas</label>
                        <select name="activity_type" class="form-select" required>
                            <option value="">Pilih aktivitas</option>
                            <?php if (!empty($activityOptions)): ?>
                                <?php foreach ($activityOptions as $option): ?>
                                <option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars($option) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Belum ada aktivitas aktif</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="deskripsi" class="form-control" placeholder="Catatan singkat aktivitas">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-outline-primary" <?= empty($activityOptions) ? 'disabled' : '' ?>>Tambah</button>
                    </div>
                </form>

                <div class="list-group small">
                    <?php if (empty($activities)): ?>
                        <div class="list-group-item text-muted">Belum ada aktivitas tercatat.</div>
                    <?php else: ?>
                        <?php foreach ($activities as $activity): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong><?= htmlspecialchars($activity['activity_type']) ?></strong>
                                <span class="text-muted"><?= date('d/m/Y H:i', strtotime($activity['timestamp'])) ?></span>
                            </div>
                            <div><?= nl2br(htmlspecialchars($activity['deskripsi'] ?? '-')) ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <hr>

                <h6 class="fw-semibold mb-3">Upload/Foto</h6>
                <div class="mb-3">
                    <div class="d-flex gap-2 mb-2">
                        <input type="file" form="visitCheckoutForm" name="visit_files[]" id="visitFiles" class="form-control" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx">
                        <!-- Mobile camera input (hidden on desktop) -->
                        <input type="file" form="visitCheckoutForm" name="visit_files[]" id="visitFilesMobile" class="form-control d-none" accept="image/*" capture="environment">
                        <button type="button" class="btn btn-primary" id="btnOpenCamera" data-bs-toggle="modal" data-bs-target="#cameraModal">
                            <?= icon('camera', 'mb-1 me-2', 16) ?> Kamera
                        </button>
                        <!-- Mobile direct camera button (hidden on desktop) -->
                        <button type="button" class="btn btn-primary d-none" id="btnMobileCamera">
                            <?= icon('camera', 'mb-1 me-2', 16) ?> Kamera
                        </button>
                    </div>
                    <div class="form-text">Maksimal 5MB per file. Bisa upload lebih dari 1 file.</div>
                    <div id="filePreview" class="mt-2"></div>
                    <?php if (!empty($visitFiles)): ?>
                        <div class="mt-3">
                            <small class="text-muted d-block mb-2">File yang sudah diupload:</small>
                            <div class="list-group list-group-flush">
                                <?php foreach ($visitFiles as $file): ?>
                                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <?php 
                                            $isImage = in_array(strtolower(pathinfo($file['original_filename'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
                                            if ($isImage): 
                                            ?>
                                                <img src="<?= htmlspecialchars(url('/uploads/' . $file['filename'])) ?>" alt="<?= htmlspecialchars($file['original_filename']) ?>" class="me-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <span class="me-2">📄</span>
                                            <?php endif; ?>
                                            <div>
                                                <div class="small fw-semibold"><?= htmlspecialchars($file['original_filename']) ?></div>
                                                <div class="small text-muted"><?= number_format($file['file_size'] / 1024, 2) ?> KB</div>
                                            </div>
                                        </div>
                                        <a href="<?= htmlspecialchars(url('/uploads/' . $file['filename'])) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <form method="POST" action="/visits/checkout/<?= $visit['visit_id'] ?>" id="visitCheckoutForm" enctype="multipart/form-data">
            <input type="hidden" name="check_out_lat" id="checkOutLat">
            <input type="hidden" name="check_out_long" id="checkOutLong">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Selesaikan Kunjungan</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Catatan Akhir</label>
                        <textarea name="catatan" id="catatan" rows="4" class="form-control" placeholder="Ringkasan hasil kunjungan"><?= htmlspecialchars($visit['catatan'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="small text-muted mb-1">Koordinat Check-out</div>
                        <div class="d-flex gap-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Lat</span>
                                <input type="text" class="form-control" id="displayOutLat" readonly>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Lng</span>
                                <input type="text" class="form-control" id="displayOutLng" readonly>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-light w-100 text-primary" id="btnCaptureCheckout" <?= $hasMapbox ? '' : 'disabled' ?>>Refresh Lokasi Saat Ini</button>
                        </div>
                        <div class="small text-muted mt-2" id="checkoutStatus">
                            <?= $hasMapbox
                                ? 'Menunggu pembacaan lokasi perangkat...'
                                : 'Mapbox access token belum tersedia. Tambahkan MAPBOX_ACCESS_TOKEN untuk mengaktifkan peta.'
                            ?>
                        </div>
                    </div>
                    <div class="mapbox-wrapper mapbox-height-240">
                        <div id="mapboxCheckout" class="mapbox-canvas-220"></div>
                    </div>
                </div>
                <div class="card-footer bg-light text-end">
                    <button type="submit" class="btn btn-success" id="btnSubmitCheckout" disabled>Selesaikan Kunjungan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php endif; ?>
</div>

<!-- Modal Kamera -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cameraModalLabel">Ambil Foto dari Kamera</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="btnCloseCamera"></button>
            </div>
            <div class="modal-body text-center">
                <div id="cameraError" class="alert alert-danger d-none"></div>
                <video id="videoPreview" autoplay playsinline style="width: 100%; max-width: 640px; border-radius: 8px; background: #000; display: none;"></video>
                <canvas id="canvasCapture" style="display: none;"></canvas>
                <div id="cameraPlaceholder" class="p-5 bg-light rounded">
                    <p class="text-muted mb-0">Klik tombol "Mulai Kamera" untuk mengaktifkan kamera</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnCancelCamera">Batal</button>
                <button type="button" class="btn btn-primary" id="btnStartCamera">Mulai Kamera</button>
                <button type="button" class="btn btn-success" id="btnCapturePhoto" style="display: none;">📷 Ambil Foto</button>
                <button type="button" class="btn btn-primary" id="btnRetakePhoto" style="display: none;">Ulangi</button>
                <button type="button" class="btn btn-success" id="btnUsePhoto" style="display: none;">Gunakan Foto Ini</button>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<?php if ($hasMapbox && !empty($visit)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapboxToken = <?= json_encode($mapboxToken) ?>;
    mapboxgl.accessToken = mapboxToken;

    const map = new mapboxgl.Map({
        container: 'mapboxCheckout',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [<?= (float)$visit['check_in_long'] ?>, <?= (float)$visit['check_in_lat'] ?>],
        zoom: 13
    });

    new mapboxgl.Marker({ color: '#2563eb' })
        .setLngLat([<?= (float)$visit['check_in_long'] ?>, <?= (float)$visit['check_in_lat'] ?>])
        .setPopup(new mapboxgl.Popup({ offset: 16 }).setHTML('<strong>Lokasi Check-in</strong>'))
        .addTo(map);

    let checkoutMarker = null;
    const btnCapture = document.getElementById('btnCaptureCheckout');
    const displayLat = document.getElementById('displayOutLat');
    const displayLng = document.getElementById('displayOutLng');
    const hiddenLat = document.getElementById('checkOutLat');
    const hiddenLng = document.getElementById('checkOutLong');
    const statusText = document.getElementById('checkoutStatus');
    const submitBtn = document.getElementById('btnSubmitCheckout');
    let geoWatchId = null;

    function updateCheckoutLocation(lat, lng) {
        hiddenLat.value = lat;
        hiddenLng.value = lng;
        displayLat.value = lat.toFixed(6);
        displayLng.value = lng.toFixed(6);
        submitBtn.disabled = false;

        if (checkoutMarker) {
            checkoutMarker.setLngLat([lng, lat]);
        } else {
            checkoutMarker = new mapboxgl.Marker({ color: '#10b981' })
                .setLngLat([lng, lat])
                .setPopup(new mapboxgl.Popup({ offset: 16 }).setHTML('<strong>Lokasi Check-out</strong>'))
                .addTo(map);
        }

        map.easeTo({ center: [lng, lat], zoom: 15 });
    }

    function setStatus(message, type = 'muted') {
        statusText.className = 'small text-' + type;
        statusText.textContent = message;
    }

    function handleGeoSuccess(position) {
        const { latitude, longitude } = position.coords;
        updateCheckoutLocation(latitude, longitude);
        setStatus('Koordinat check-out otomatis diperbarui dari lokasi perangkat.', 'success');
    }

    function handleGeoError(error) {
        console.error(error);
        setStatus('Gagal memperoleh lokasi otomatis. Pastikan izin lokasi telah diberikan.', 'danger');
    }

    if (navigator.geolocation) {
        setStatus('Mengambil lokasi perangkat...', 'info');
        geoWatchId = navigator.geolocation.watchPosition(handleGeoSuccess, handleGeoError, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 15000
        });
    } else {
        setStatus('Perangkat tidak mendukung geolocation.', 'danger');
    }

    btnCapture?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            setStatus('Perangkat tidak mendukung geolocation.', 'danger');
            return;
        }
        setStatus('Mengambil lokasi GPS...', 'info');
        navigator.geolocation.getCurrentPosition(handleGeoSuccess, handleGeoError, { enableHighAccuracy: true });
    });

    map.on('click', (ev) => {
        updateCheckoutLocation(ev.lngLat.lat, ev.lngLat.lng);
        setStatus('Koordinat check-out dapat diedit dengan klik peta.', 'success');
    });

    window.addEventListener('beforeunload', () => {
        if (geoWatchId !== null && navigator.geolocation) {
            navigator.geolocation.clearWatch(geoWatchId);
        }
    });
});
</script>
<?php endif; ?>

<script>
// File preview functions - make them globally accessible
let fileInput, filePreview;
const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function removeFileFromList(fileIndex) {
    if (!fileInput) return;
    
    const files = Array.from(fileInput.files);
    if (fileIndex < 0 || fileIndex >= files.length) return;
    
    // Create new FileList without the removed file
    const dataTransfer = new DataTransfer();
    files.forEach((file, index) => {
        if (index !== fileIndex) {
            dataTransfer.items.add(file);
        }
    });
    
    // Update the input's files
    fileInput.files = dataTransfer.files;
    
    // Refresh the file preview
    updateFilePreview();
}

function updateFilePreview() {
    if (!fileInput || !filePreview) return;
    
    filePreview.innerHTML = '';
    const files = Array.from(fileInput.files);
    
    if (files.length === 0) {
        return;
    }
    
    const validFiles = [];
    const errors = [];
    
    files.forEach((file, index) => {
        // Check file size
        if (file.size > maxFileSize) {
            errors.push(`File "${file.name}" terlalu besar (maksimal 5MB)`);
            return;
        }
        
        // Check file type
        const extension = file.name.split('.').pop().toLowerCase();
        const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        if (!allowedTypes.includes(extension)) {
            errors.push(`File "${file.name}" tidak diizinkan (hanya: ${allowedTypes.join(', ')})`);
            return;
        }
        
        validFiles.push({file: file, index: index});
    });
    
    // Show errors
    if (errors.length > 0) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger';
        errorDiv.innerHTML = '<strong>Error:</strong><ul class="mb-0 mt-2"><li>' + errors.join('</li><li>') + '</li></ul>';
        filePreview.appendChild(errorDiv);
    }
    
    // Show preview for valid files
    if (validFiles.length > 0) {
        const previewDiv = document.createElement('div');
        previewDiv.className = 'list-group';
        
        validFiles.forEach(({file, index}) => {
            const listItem = document.createElement('div');
            listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
            listItem.innerHTML = `
                <div class="d-flex align-items-center flex-grow-1">
                    <span class="me-2">${file.name}</span>
                    <span class="badge bg-secondary">${formatFileSize(file.size)}</span>
                </div>
                <button type="button" class="btn btn-sm btn-danger ms-2 remove-file-btn" data-file-index="${index}" data-file-name="${file.name}">
                    <img src="<?= htmlspecialchars($baseUrl) ?>/assets/icons/trash-can.svg" alt="trash-can" width="14" height="14" class="icon-inline me-1 mb-1"> Hapus
                </button>
            `;
            previewDiv.appendChild(listItem);
        });
        
        filePreview.appendChild(previewDiv);
        
        // Add event listeners to remove buttons
        const removeButtons = filePreview.querySelectorAll('.remove-file-btn');
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const fileIndex = parseInt(this.getAttribute('data-file-index'));
                const fileName = this.getAttribute('data-file-name');
                
                showConfirmModal({
                    title: 'Konfirmasi Hapus',
                    message: `Apakah Anda yakin ingin menghapus file <strong>${fileName}</strong> dari daftar upload?`,
                    buttonText: 'Hapus',
                    buttonClass: 'btn-danger',
                    onConfirm: function() {
                        removeFileFromList(fileIndex);
                    }
                });
            });
        });
    }
}
    
document.addEventListener('DOMContentLoaded', function() {
    fileInput = document.getElementById('visitFiles');
    filePreview = document.getElementById('filePreview');
    
    if (fileInput) {
        fileInput.addEventListener('change', updateFilePreview);
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Detect mobile device
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                     (window.innerWidth <= 768 && 'ontouchstart' in window);
    
    // Camera functionality
    const cameraModal = document.getElementById('cameraModal');
    const btnOpenCamera = document.getElementById('btnOpenCamera');
    const btnMobileCamera = document.getElementById('btnMobileCamera');
    const fileInputMobile = document.getElementById('visitFilesMobile');
    const btnStartCamera = document.getElementById('btnStartCamera');
    const btnCapturePhoto = document.getElementById('btnCapturePhoto');
    const btnRetakePhoto = document.getElementById('btnRetakePhoto');
    const btnUsePhoto = document.getElementById('btnUsePhoto');
    const btnCloseCamera = document.getElementById('btnCloseCamera');
    const btnCancelCamera = document.getElementById('btnCancelCamera');
    const videoPreview = document.getElementById('videoPreview');
    const canvasCapture = document.getElementById('canvasCapture');
    const cameraPlaceholder = document.getElementById('cameraPlaceholder');
    const cameraError = document.getElementById('cameraError');
    // Use global fileInput and filePreview variables (already initialized in previous script)
    if (!fileInput) fileInput = document.getElementById('visitFiles');
    if (!filePreview) filePreview = document.getElementById('filePreview');
    
    let stream = null;
    let capturedImage = null;
    
    // Check if browser supports getUserMedia
    const hasGetUserMedia = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    
    // Mobile device handling
    if (isMobile) {
        console.log('Mobile device detected');
        
        // Show mobile camera button, hide modal camera button
        if (btnMobileCamera) {
            btnMobileCamera.classList.remove('d-none');
        }
        if (btnOpenCamera) {
            btnOpenCamera.style.display = 'none';
        }
        
        // Mobile camera button click handler
        if (btnMobileCamera && fileInputMobile) {
            btnMobileCamera.addEventListener('click', function() {
                fileInputMobile.click();
            });
            
            // Handle file selection from mobile camera
            fileInputMobile.addEventListener('change', function(e) {
                if (e.target.files && e.target.files.length > 0) {
                    // Add mobile camera files to main file input
                    const dataTransfer = new DataTransfer();
                    
                    // Keep existing files
                    if (fileInput.files) {
                        for (let i = 0; i < fileInput.files.length; i++) {
                            dataTransfer.items.add(fileInput.files[i]);
                        }
                    }
                    
                    // Add new mobile camera files
                    for (let i = 0; i < e.target.files.length; i++) {
                        dataTransfer.items.add(e.target.files[i]);
                    }
                    
                    fileInput.files = dataTransfer.files;
                    
                    // Update preview manually
                    if (typeof updateFilePreview === 'function') {
                        updateFilePreview();
                    } else {
                        // Trigger change event to update preview
                        const changeEvent = new Event('change', { bubbles: true });
                        fileInput.dispatchEvent(changeEvent);
                    }
                    
                    // Reset mobile input
                    fileInputMobile.value = '';
                }
            });
        }
    } else {
        console.log('Desktop device detected');
        
        // Desktop: show modal camera button, hide mobile button
        if (btnMobileCamera) {
            btnMobileCamera.classList.add('d-none');
        }
        if (btnOpenCamera) {
            btnOpenCamera.style.display = 'inline-block';
        }
        
        // Desktop camera modal functionality
        if (!hasGetUserMedia) {
            if (btnOpenCamera) {
                btnOpenCamera.disabled = true;
                btnOpenCamera.title = 'Browser tidak mendukung akses kamera';
            }
        }
    }
    
    // Log device and camera support info
    console.log('Device Info:', {
        isMobile: isMobile,
        hasGetUserMedia: hasGetUserMedia,
        userAgent: navigator.userAgent,
        screenWidth: window.innerWidth
    });
    
    // Show start camera button when modal opens
    if (cameraModal) {
        cameraModal.addEventListener('show.bs.modal', function() {
            resetCameraUI();
            if (hasGetUserMedia) {
                btnStartCamera.style.display = 'inline-block';
            } else {
                btnStartCamera.style.display = 'none';
                showCameraError('Browser Anda tidak mendukung akses kamera. Silakan gunakan fitur upload file biasa.');
            }
        });
        
        cameraModal.addEventListener('hide.bs.modal', function() {
            stopCamera();
        });
    }
    
    function resetCameraUI() {
        videoPreview.style.display = 'none';
        cameraPlaceholder.style.display = 'block';
        cameraPlaceholder.innerHTML = '<p class="text-muted mb-0">Klik tombol "Mulai Kamera" untuk mengaktifkan kamera</p>';
        btnStartCamera.style.display = hasGetUserMedia ? 'inline-block' : 'none';
        btnCapturePhoto.style.display = 'none';
        btnRetakePhoto.style.display = 'none';
        btnUsePhoto.style.display = 'none';
        cameraError.classList.add('d-none');
        capturedImage = null;
    }
    
    function showCameraError(message) {
        cameraError.textContent = message;
        cameraError.classList.remove('d-none');
    }
    
    function hideCameraError() {
        cameraError.classList.add('d-none');
    }
    
    function stopStream() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }
    
    function stopCamera() {
        stopStream();
        resetCameraUI();
    }
    
    async function startCamera() {
        try {
            hideCameraError();
            
            // Request camera access
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment', // Prefer back camera on mobile
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            });
            
            videoPreview.srcObject = stream;
            videoPreview.style.display = 'block';
            cameraPlaceholder.style.display = 'none';
            btnStartCamera.style.display = 'none';
            btnCapturePhoto.style.display = 'inline-block';
            
            // Wait for video to be ready
            videoPreview.addEventListener('loadedmetadata', function() {
                canvasCapture.width = videoPreview.videoWidth;
                canvasCapture.height = videoPreview.videoHeight;
            }, { once: true });
            
        } catch (error) {
            console.error('Error accessing camera:', error);
            let errorMessage = 'Gagal mengakses kamera. ';
            
            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                errorMessage += 'Izin akses kamera ditolak. Silakan berikan izin di pengaturan browser.';
            } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
                errorMessage += 'Kamera tidak ditemukan.';
            } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
                errorMessage += 'Kamera sedang digunakan oleh aplikasi lain.';
            } else {
                errorMessage += error.message || 'Terjadi kesalahan.';
            }
            
            showCameraError(errorMessage);
            resetCameraUI();
        }
    }
    
    function capturePhoto() {
        if (!stream || !videoPreview.videoWidth) {
            showCameraError('Kamera belum siap. Tunggu sebentar.');
            return;
        }
        
        try {
            const ctx = canvasCapture.getContext('2d');
            ctx.drawImage(videoPreview, 0, 0, canvasCapture.width, canvasCapture.height);
            
            // Convert canvas to blob
            canvasCapture.toBlob(function(blob) {
                if (!blob) {
                    showCameraError('Gagal mengambil foto.');
                    return;
                }
                
                capturedImage = blob;
                
                // Stop camera stream (but don't reset UI)
                stopStream();
                
                // Hide video preview
                videoPreview.style.display = 'none';
                
                // Show captured image preview
                const img = document.createElement('img');
                img.src = URL.createObjectURL(blob);
                img.style.maxWidth = '100%';
                img.style.borderRadius = '8px';
                img.style.marginBottom = '10px';
                
                const previewContainer = document.createElement('div');
                previewContainer.innerHTML = '';
                previewContainer.appendChild(img);
                
                cameraPlaceholder.innerHTML = '';
                cameraPlaceholder.appendChild(previewContainer);
                cameraPlaceholder.style.display = 'block';
                
                btnCapturePhoto.style.display = 'none';
                btnRetakePhoto.style.display = 'inline-block';
                btnUsePhoto.style.display = 'inline-block';
                
            }, 'image/jpeg', 0.9);
            
        } catch (error) {
            console.error('Error capturing photo:', error);
            showCameraError('Gagal mengambil foto: ' + error.message);
        }
    }
    
    function usePhoto() {
        if (!capturedImage) {
            showCameraError('Tidak ada foto yang diambil.');
            return;
        }
        
        // Create a File object from the blob
        const timestamp = new Date().getTime();
        const filename = `camera_${timestamp}.jpg`;
        const file = new File([capturedImage], filename, { type: 'image/jpeg' });
        
        // Add file to file input
        const dataTransfer = new DataTransfer();
        
        // Keep existing files
        if (fileInput.files) {
            for (let i = 0; i < fileInput.files.length; i++) {
                dataTransfer.items.add(fileInput.files[i]);
            }
        }
        
        // Add new captured photo
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;
        
        // Update preview manually
        if (typeof updateFilePreview === 'function') {
            updateFilePreview();
        } else {
            // Trigger change event to update preview
            const changeEvent = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(changeEvent);
        }
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(cameraModal);
        if (modal) {
            modal.hide();
        }
    }
    
    function retakePhoto() {
        resetCameraUI();
        startCamera();
    }
    
    // Event listeners
    if (btnStartCamera) {
        btnStartCamera.addEventListener('click', startCamera);
    }
    
    if (btnCapturePhoto) {
        btnCapturePhoto.addEventListener('click', capturePhoto);
    }
    
    if (btnRetakePhoto) {
        btnRetakePhoto.addEventListener('click', retakePhoto);
    }
    
    if (btnUsePhoto) {
        btnUsePhoto.addEventListener('click', usePhoto);
    }
    
    if (btnCloseCamera || btnCancelCamera) {
        const closeHandler = () => stopCamera();
        if (btnCloseCamera) btnCloseCamera.addEventListener('click', closeHandler);
        if (btnCancelCamera) btnCancelCamera.addEventListener('click', closeHandler);
    }
});
</script>


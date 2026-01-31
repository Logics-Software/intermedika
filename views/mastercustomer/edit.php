<?php
$title = 'Edit Master Customer';
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

$mapboxToken = $config['mapbox_access_token'] ?? '';
$hasMapboxToken = !empty($mapboxToken);

$formattedLatitude = isset($customer['latitude']) && $customer['latitude'] !== null
    ? number_format((float)$customer['latitude'], 6, '.', '')
    : '';
$formattedLongitude = isset($customer['longitude']) && $customer['longitude'] !== null
    ? number_format((float)$customer['longitude'], 6, '.', '')
    : '';

$currentStatusPkp = strtolower($customer['statuspkp'] ?? 'pkp');
$isPkp = $currentStatusPkp === 'pkp';
$npwpValue = $isPkp ? ($customer['npwp'] ?? '') : '';
$namawpValue = $isPkp ? ($customer['namawp'] ?? '') : '';
$alamatwpValue = $isPkp ? ($customer['alamatwp'] ?? '') : '';

require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/mastercustomer">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/mastercustomer">Customer</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Data Customer</h4>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="/mastercustomer/edit/<?= $customer['id'] ?>">
                        <!-- Kelompok 1: Data Dasar (Readonly) -->
                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <label for="namacustomer" class="form-label">Nama Customer</label>
                                <input type="text" class="form-control" id="namacustomer" value="<?= htmlspecialchars($customer['namacustomer']. ', ' .$customer['namabadanusaha']) ?>" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="alamatcustomer" class="form-label">Alamat Customer</label>
                                <input type="text" class="form-control" id="alamatcustomer" value="<?= htmlspecialchars($customer['alamatcustomer']. ' ' .$customer['kotacustomer'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="notelepon" class="form-label">No Telepon</label>
                                <input type="text" class="form-control" id="notelepon" value="<?= htmlspecialchars($customer['notelepon'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="kontakperson" class="form-label">Kontak Person</label>
                                <input type="text" class="form-control" id="kontakperson" value="<?= htmlspecialchars($customer['kontakperson'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="statuspkp" class="form-label">Status PKP</label>
                                <select class="form-select" id="statuspkp" name="statuspkp" disabled>
                                    <option value="pkp" <?= $currentStatusPkp === 'pkp' ? 'selected' : '' ?>>PKP</option>
                                    <option value="nonpkp" <?= $currentStatusPkp === 'nonpkp' ? 'selected' : '' ?>>Non PKP</option>
                                </select>
                                <input type="hidden" name="statuspkp" value="<?= htmlspecialchars($currentStatusPkp) ?>">
                            </div>
                        </div>

                        <!-- Kelompok 2: Wajib Pajak (Editable) -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-head-green2 text-white">
                                        <h5 class="mb-0 text-dark">Data Wajib Pajak</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-2 mb-3">
                                                <label for="npwp" class="form-label">NPWP</label>
                                                <input type="text" class="form-control" id="npwp_display" value="<?= htmlspecialchars($npwpValue) ?>" placeholder="Masukkan NPWP" data-pkp-hidden="npwp_hidden" <?= $isPkp ? '' : 'disabled' ?>>
                                                <input type="hidden" name="npwp" id="npwp_hidden" value="<?= htmlspecialchars($npwpValue) ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="namawp" class="form-label">Nama WP</label>
                                                <input type="text" class="form-control" id="namawp_display" value="<?= htmlspecialchars($namawpValue) ?>" placeholder="Masukkan Nama Wajib Pajak" data-pkp-hidden="namawp_hidden" <?= $isPkp ? '' : 'disabled' ?>>
                                                <input type="hidden" name="namawp" id="namawp_hidden" value="<?= htmlspecialchars($namawpValue) ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="alamatwp" class="form-label">Alamat WP</label>
                                                <input type="text" class="form-control" id="alamatwp_display" value="<?= htmlspecialchars($alamatwpValue) ?>" placeholder="Masukkan Alamat Wajib Pajak" data-pkp-hidden="alamatwp_hidden" <?= $isPkp ? '' : 'disabled' ?>>
                                                <input type="hidden" name="alamatwp" id="alamatwp_hidden" value="<?= htmlspecialchars($alamatwpValue) ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kelompok 3: Ijin SIPA dan CDOB (Editable) -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-head-green3 text-white">
                                        <h5 class="mb-0 text-dark">Data Ijin SIPA dan CDOB</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="namaapoteker" class="form-label">Nama Apoteker</label>
                                                <input type="text" class="form-control" id="namaapoteker" name="namaapoteker" value="<?= htmlspecialchars($customer['namaapoteker'] ?? '') ?>" placeholder="Masukkan Nama Apoteker">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="nosipa" class="form-label">No. SIPA</label>
                                                <input type="text" class="form-control" id="nosipa" name="nosipa" value="<?= htmlspecialchars($customer['nosipa'] ?? '') ?>" placeholder="Masukkan No. SIPA">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="tanggaledsipa" class="form-label">Tanggal ED SIPA</label>
                                                <input type="date" class="form-control" id="tanggaledsipa" name="tanggaledsipa" value="<?= $customer['tanggaledsipa'] ? date('Y-m-d', strtotime($customer['tanggaledsipa'])) : '' ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="noijinusaha" class="form-label">No. Ijin Usaha</label>
                                                <input type="text" class="form-control" id="noijinusaha" name="noijinusaha" value="<?= htmlspecialchars($customer['noijinusaha'] ?? '') ?>" placeholder="Masukkan No. Ijin Usaha">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="tanggaledijinusaha" class="form-label">Tanggal ED Ijin Usaha</label>
                                                <input type="date" class="form-control" id="tanggaledijinusaha" name="tanggaledijinusaha" value="<?= $customer['tanggaledijinusaha'] ? date('Y-m-d', strtotime($customer['tanggaledijinusaha'])) : '' ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="nocdob" class="form-label">No. CDOB</label>
                                                <input type="text" class="form-control" id="nocdob" name="nocdob" value="<?= htmlspecialchars($customer['nocdob'] ?? '') ?>" placeholder="Masukkan No. CDOB">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="tanggaledcdob" class="form-label">Tanggal ED CDOB</label>
                                                <input type="date" class="form-control" id="tanggaledcdob" name="tanggaledcdob" value="<?= $customer['tanggaledcdob'] ? date('Y-m-d', strtotime($customer['tanggaledcdob'])) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kelompok 4: Lokasi Customer -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-head-green3 text-white">
                                        <h5 class="mb-0 text-dark">Lokasi Customer</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-4">
                                                <label for="latitude" class="form-label">Latitude</label>
                                                <input type="number" step="0.000001" class="form-control" id="latitude" name="latitude" value="<?= htmlspecialchars($formattedLatitude) ?>" placeholder="Contoh: -6.200000">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="longitude" class="form-label">Longitude</label>
                                                <input type="number" step="0.000001" class="form-control" id="longitude" name="longitude" value="<?= htmlspecialchars($formattedLongitude) ?>" placeholder="Contoh: 106.816666">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-filter btn-outline-success w-100" id="btnUseCurrentLocation" <?= $hasMapboxToken ? '' : 'disabled' ?>>Gunakan Lokasi Saya</button>
                                            </div>
                                            <div class="col-12 d-lg-none">
                                                <button type="button" class="btn btn-success w-100" id="btnOpenMapFullscreen">Buka Peta Fullscreen</button>
                                            </div>
                                        </div>
                                        <div id="mapGeocoder" class="mapbox-geocoder-container mt-3"></div>
                                        <div class="mapbox-wrapper mt-3" id="mapWrapper">
                                            <button type="button" class="btn btn-light btn-sm mapbox-close" id="btnCloseMapFullscreen">
                                                Tutup
                                            </button>
                                            <div id="customerLocationMap"></div>
                                        </div>
                                        <div id="locationFeedback" class="location-feedback small mt-2 <?= $hasMapboxToken ? 'text-muted' : 'text-danger' ?>">
                                            <?= $hasMapboxToken
                                                ? 'Cari lokasi, klik peta, seret marker, atau gunakan tombol di atas untuk memperbarui koordinat customer.'
                                                : 'Mapbox access token belum dikonfigurasi. Tambahkan MAPBOX_ACCESS_TOKEN pada environment server untuk mengaktifkan peta.'
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between">
                            <a href="/mastercustomer" class="btn btn-secondary">
                                <?= icon('cancel', 'me-2', 16) ?> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <?= icon('pen-to-square', 'me-2', 16) ?> Update Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$mapboxTokenJs = json_encode($mapboxToken, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$additionalInlineScripts = $additionalInlineScripts ?? [];
$additionalInlineScripts[] = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    var statusSelect = document.getElementById('statuspkp');
    var isPkp = statusSelect && statusSelect.value === 'pkp';
    document.querySelectorAll('[data-pkp-hidden]').forEach(function(input) {
        var hiddenId = input.getAttribute('data-pkp-hidden');
        var hidden = hiddenId ? document.getElementById(hiddenId) : null;
        if (!hidden) {
            return;
        }
        if (isPkp) {
            input.removeAttribute('disabled');
            input.addEventListener('input', function() {
                hidden.value = input.value;
            });
        } else {
            input.value = '';
            input.setAttribute('disabled', 'disabled');
            hidden.value = '';
        }
    });
});
JS;
$additionalInlineScripts[] = <<<JS
(function() {
    const mapContainer = document.getElementById('customerLocationMap');
    const mapWrapper = document.getElementById('mapWrapper');
    if (!mapContainer) {
        return;
    }

    const feedbackEl = document.getElementById('locationFeedback');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const useLocationBtn = document.getElementById('btnUseCurrentLocation');
    const geocoderContainer = document.getElementById('mapGeocoder');
    const openFullscreenBtn = document.getElementById('btnOpenMapFullscreen');
    const closeFullscreenBtn = document.getElementById('btnCloseMapFullscreen');

    if (!latInput || !lngInput) {
        return;
    }

    const mapboxToken = {$mapboxTokenJs};
    if (!mapboxToken) {
        if (feedbackEl) {
            feedbackEl.classList.remove('text-muted');
            feedbackEl.classList.add('text-danger');
            feedbackEl.textContent = 'Mapbox access token belum dikonfigurasi. Tambahkan MAPBOX_ACCESS_TOKEN pada environment server.';
        }
        if (useLocationBtn) {
            useLocationBtn.disabled = true;
        }
        return;
    }

    mapboxgl.accessToken = mapboxToken;

    const parsedLat = parseFloat(latInput.value);
    const parsedLng = parseFloat(lngInput.value);
    const hasInitial = !Number.isNaN(parsedLat) && !Number.isNaN(parsedLng);
    const defaultCenter = hasInitial ? [parsedLng, parsedLat] : [106.816666, -6.200000];
    const defaultZoom = hasInitial ? 15 : 11;

    const map = new mapboxgl.Map({
        container: mapContainer,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: defaultCenter,
        zoom: defaultZoom
    });

    // Ensure map resizes after load
    map.on('load', function() {
        map.resize();
    });

    // Also resize after a short delay to ensure CSS is applied
    setTimeout(function() {
        map.resize();
    }, 100);

    map.addControl(new mapboxgl.NavigationControl());

    if (typeof MapboxGeocoder === 'function') {
        const geocoder = new MapboxGeocoder({
            accessToken: mapboxToken,
            mapboxgl: mapboxgl,
            marker: false,
            placeholder: 'Cari nama lokasi, jalan, atau kota...',
            language: 'id',
            countries: 'id'
        });

        if (geocoderContainer) {
            geocoderContainer.innerHTML = '';
            geocoderContainer.appendChild(geocoder.onAdd(map));
        } else {
            map.addControl(geocoder);
        }

        geocoder.on('result', function(event) {
            if (!event || !event.result || !event.result.center) {
                return;
            }
            const [lng, lat] = event.result.center;
            const coordinates = { lng: lng, lat: lat };
            map.flyTo({ center: [lng, lat], zoom: 16 });
            placeMarker(coordinates);
            setFeedback('Koordinat berhasil diperbarui dari hasil pencarian.', false);
        });

        geocoder.on('clear', function() {
            setFeedback('Pencarian dikosongkan. Klik peta, seret marker, atau gunakan tombol lokasi untuk menetapkan koordinat.', false);
        });
    }

    const geolocateControl = new mapboxgl.GeolocateControl({
        positionOptions: { enableHighAccuracy: true },
        trackUserLocation: false,
        showUserLocation: true,
        showUserHeading: true
    });
    map.addControl(geolocateControl);

    let marker = null;

    function setFeedback(message, isError) {
        if (!feedbackEl) {
            return;
        }
        feedbackEl.textContent = message;
        feedbackEl.classList.toggle('text-danger', Boolean(isError));
        feedbackEl.classList.toggle('text-muted', !isError);
    }

    function showLocationPermissionHelp() {
        const message = 'Tidak dapat memperoleh lokasi otomatis. Pastikan layanan lokasi menyala dan izin lokasi pada browser diizinkan.';
        setFeedback(message, true);
        if (typeof window !== 'undefined') {
            window.alert('Aktifkan GPS dan izinkan akses lokasi pada browser untuk menggunakan fitur ini.');
        }
    }

    function triggerGeolocate() {
        if (!geolocateControl) {
            return;
        }

        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions
                .query({ name: 'geolocation' })
                .then(function (result) {
                    if (result.state === 'granted') {
                        setFeedback('Mengambil lokasi Anda...', false);
                        geolocateControl.trigger();
                    } else if (result.state === 'prompt') {
                        const confirmAllow = window.confirm('Untuk menggunakan fitur ini, izinkan akses lokasi pada perangkat Anda. Lanjutkan?');
                        if (confirmAllow) {
                            setFeedback('Silakan izinkan akses lokasi pada prompt yang muncul...', false);
                            geolocateControl.trigger();
                        } else {
                            setFeedback('Pengambilan lokasi dibatalkan oleh pengguna.', true);
                        }
                    } else {
                        showLocationPermissionHelp();
                    }
                })
                .catch(function () {
                    setFeedback('Mengambil lokasi Anda...', false);
                    geolocateControl.trigger();
                });
        } else {
            setFeedback('Mengambil lokasi Anda...', false);
            geolocateControl.trigger();
        }
    }

    function updateInputs(lngLat) {
        latInput.value = lngLat.lat.toFixed(6);
        lngInput.value = lngLat.lng.toFixed(6);
        setFeedback('Koordinat telah diperbarui.', false);
    }

    function placeMarker(lngLat) {
        if (!marker) {
            marker = new mapboxgl.Marker({ draggable: true })
                .setLngLat(lngLat)
                .addTo(map);

            marker.on('dragend', function() {
                const coords = marker.getLngLat();
                updateInputs(coords);
            });
        } else {
            marker.setLngLat(lngLat);
        }

        updateInputs(lngLat);
    }

    if (hasInitial) {
        placeMarker({ lng: parsedLng, lat: parsedLat });
    }

    function enterFullscreen() {
        if (!mapWrapper || mapWrapper.classList.contains('fullscreen')) {
            return;
        }
        mapWrapper.classList.add('fullscreen');
        document.body.classList.add('mapbox-fullscreen-open');
        if (closeFullscreenBtn) {
            closeFullscreenBtn.focus();
        }
        // Resize multiple times to ensure proper rendering
        setTimeout(function() {
            map.resize();
        }, 100);
        setTimeout(function() {
            map.resize();
        }, 300);
    }

    function exitFullscreen() {
        if (!mapWrapper || !mapWrapper.classList.contains('fullscreen')) {
            return;
        }
        mapWrapper.classList.remove('fullscreen');
        document.body.classList.remove('mapbox-fullscreen-open');
        // Resize multiple times to ensure proper rendering
        setTimeout(function() {
            map.resize();
        }, 100);
        setTimeout(function() {
            map.resize();
        }, 250);
    }

    if (openFullscreenBtn && mapWrapper) {
        openFullscreenBtn.addEventListener('click', function() {
            enterFullscreen();
        });
    }

    if (closeFullscreenBtn) {
        closeFullscreenBtn.addEventListener('click', function() {
            exitFullscreen();
        });
    }

    window.addEventListener('resize', function() {
        setTimeout(function() {
            map.resize();
        }, 150);
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && mapWrapper && mapWrapper.classList.contains('fullscreen')) {
            exitFullscreen();
        }
    });

    map.on('click', function(event) {
        placeMarker(event.lngLat);
    });

    geolocateControl.on('geolocate', function(position) {
        const coords = {
            lng: position.coords.longitude,
            lat: position.coords.latitude
        };
        map.flyTo({ center: [coords.lng, coords.lat], zoom: 16 });
        placeMarker(coords);
        setFeedback('Koordinat berhasil diambil dari lokasi Anda.', false);
    });

    geolocateControl.on('error', function() {
        showLocationPermissionHelp();
    });

    if (useLocationBtn) {
        useLocationBtn.addEventListener('click', function() {
            triggerGeolocate();
        });
    }

    if (closeFullscreenBtn && mapWrapper) {
        mapWrapper.addEventListener('click', function(event) {
            if (mapWrapper.classList.contains('fullscreen') && event.target === mapWrapper) {
                exitFullscreen();
            }
        });
    }

    function tryUpdateFromInputs() {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);

        if (Number.isNaN(lat) || Number.isNaN(lng)) {
            return;
        }

        const coords = { lng: lng, lat: lat };
        placeMarker(coords);
        map.flyTo({ center: [lng, lat], zoom: 15 });
    }

    latInput.addEventListener('change', tryUpdateFromInputs);
    lngInput.addEventListener('change', tryUpdateFromInputs);
})();
JS;

require __DIR__ . '/../layouts/footer.php';
?>


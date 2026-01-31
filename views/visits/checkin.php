<?php
$title = 'Check-in Kunjungan';
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
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/visits">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/visits">Kunjungan</a></li>
                    <li class="breadcrumb-item active">Check-in</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h2 class="mb-0 me-auto">Check-in</h2>
                    <a href="/visits" class="btn btn-secondary"><?= icon('back', 'mb-1 me-2', 16) ?> Kembali</a>
                </div>
            </div>

            <?php if (!empty($activeVisit)): ?>
            <div class="alert alert-warning">
                <strong>Perhatian!</strong> Anda masih memiliki kunjungan yang berjalan. Selesaikan terlebih dahulu sebelum melakukan check-in baru.
                <a href="/visits" class="btn btn-sm btn-outline-primary ms-2">Lihat Kunjungan</a>
            </div>
            <?php else: ?>

            <div class="card-body">
                <div class="row">
                    <form method="POST" action="/visits/check-in" id="visitCheckinForm">
                        <input type="hidden" name="customer_id" id="selectedCustomerId">
                        <input type="hidden" name="check_in_lat" id="checkInLat">
                        <input type="hidden" name="check_in_long" id="checkInLong">

                        <div class="row g-3">
                            <div class="col-lg-4">
                                <div class="card border-primary h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Lokasi Anda</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="small text-muted" id="currentLocationStatus">
                                            <?= $hasMapbox
                                                ? 'Tekan tombol "Gunakan Lokasi Saya" untuk mengambil koordinat GPS.'
                                                : 'Mapbox access token belum tersedia. Tambahkan MAPBOX_ACCESS_TOKEN untuk mengaktifkan peta.'
                                            ?>
                                        </p>
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-outline-primary" id="btnUseLocation" <?= $hasMapbox ? '' : 'disabled' ?>>Gunakan Lokasi Saya</button>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Lat</span>
                                                <input type="text" class="form-control" id="displayLat" placeholder="-6.2" readonly>
                                            </div>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Lng</span>
                                                <input type="text" class="form-control" id="displayLng" placeholder="106.8" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="card border-success h-100">
                                    <div class="card-header bg-success text-white d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                                        <h5 class="mb-0">Customer</h5>
                                        <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto justify-content-md-end">
                                            <input type="search" class="form-control form-control-sm flex-grow-1 search-input-wide" id="customerSearchInput" placeholder="Cari nama atau kode customer">
                                            <button type="button" class="btn btn-light btn-sm" id="btnRefreshNearby">Segarkan</button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mapbox-wrapper mapbox-height-280">
                                            <div id="mapboxCheckin" class="mapbox-canvas-260"></div>
                                        </div>
                                        <div class="mt-3">
                                            <h6 class="fw-semibold">Daftar Customer</h6>
                                            <div class="list-group scrollable-list-260" id="customerResults">
                                                <div class="list-group-item text-muted small">Mulai dengan mengambil lokasi Anda...</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <label for="catatan" class="form-label">Catatan Kunjungan (Opsional)</label>
                                <textarea name="catatan" id="catatan" class="form-control" rows="3" placeholder="Tuliskan tujuan kunjungan atau informasi tambahan"></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                <span id="selectedCustomerInfo">Belum ada customer dipilih.</span>
                            </div>
                            <button type="submit" class="btn btn-primary" id="btnSubmitCheckin" disabled>Mulai Check-in</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="coordinateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tentukan Lokasi Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2 small text-muted" id="coordinateCustomerName"></p>
                <div id="coordinateGeocoder" class="mapbox-geocoder-container mb-3"></div>
                <div id="coordinatePickerMap" class="coordinate-picker-map"></div>
                <div class="small text-muted mt-2" id="coordinateFeedback"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="coordinateSaveBtn">Simpan Koordinat</button>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<?php if ($hasMapbox): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapboxToken = <?= json_encode($mapboxToken) ?>;
    mapboxgl.accessToken = mapboxToken;

    const map = new mapboxgl.Map({
        container: 'mapboxCheckin',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [106.816666, -6.200000],
        zoom: 10
    });

    const geolocate = new mapboxgl.GeolocateControl({
        positionOptions: { enableHighAccuracy: true },
        trackUserLocation: false,
        showAccuracyCircle: false
    });
    map.addControl(geolocate);

    let userMarker = null;
    let customerMarkers = [];
    const btnUseLocation = document.getElementById('btnUseLocation');
    const btnRefreshNearby = document.getElementById('btnRefreshNearby');
    const searchInput = document.getElementById('customerSearchInput');
    const customerResults = document.getElementById('customerResults');
    const statusText = document.getElementById('currentLocationStatus');
    const displayLat = document.getElementById('displayLat');
    const displayLng = document.getElementById('displayLng');
    const hiddenLat = document.getElementById('checkInLat');
    const hiddenLng = document.getElementById('checkInLong');
    const selectedCustomerId = document.getElementById('selectedCustomerId');
    const selectedCustomerInfo = document.getElementById('selectedCustomerInfo');
    const btnSubmit = document.getElementById('btnSubmitCheckin');
    let currentCoords = null;
    let nearestCustomers = [];
    const coordinateModalEl = document.getElementById('coordinateModal');
    const coordinateCustomerName = document.getElementById('coordinateCustomerName');
    const coordinateFeedback = document.getElementById('coordinateFeedback');
    const coordinateSaveBtn = document.getElementById('coordinateSaveBtn');
    const coordinateGeocoderContainer = document.getElementById('coordinateGeocoder');
    const coordinateModal = coordinateModalEl ? new bootstrap.Modal(coordinateModalEl) : null;
    let coordinateMap = null;
    let coordinateMarker = null;
    let coordinateSelectedLat = null;
    let coordinateSelectedLng = null;
    let coordinateCustomerId = null;
    let coordinateGeocoder = null;
    let coordinateGeocoderElement = null;

    function updateStatus(message, type = 'muted') {
        statusText.className = 'small text-' + type;
        statusText.textContent = message;
    }

    function setUserLocation(lat, lng) {
        currentCoords = { lat, lng };
        displayLat.value = lat.toFixed(6);
        displayLng.value = lng.toFixed(6);
        hiddenLat.value = lat;
        hiddenLng.value = lng;
        btnSubmit.disabled = !selectedCustomerId.value;

        if (userMarker) {
            userMarker.setLngLat([lng, lat]);
        } else {
            userMarker = new mapboxgl.Marker({ color: '#1d4ed8' })
                .setLngLat([lng, lat])
                .addTo(map);
        }

        map.easeTo({ center: [lng, lat], zoom: 14 });
        fetchNearestCustomers();
    }

    function clearCustomerMarkers() {
        customerMarkers.forEach(marker => marker.remove());
        customerMarkers = [];
    }

    function openCoordinateModal(customer) {
        if (!coordinateModal) {
            return;
        }
        coordinateCustomerId = customer.id;
        coordinateCustomerName.textContent = `${customer.namacustomer || '-'} (${customer.kodecustomer})`;

        // Check if coordinates are 0,0 or null/undefined
        const customerLat = customer.latitude ? parseFloat(customer.latitude) : null;
        const customerLng = customer.longitude ? parseFloat(customer.longitude) : null;
        const isZeroCoordinates = (customerLat === 0 && customerLng === 0) || 
                                   (customerLat === null && customerLng === null) ||
                                   (customerLat === 0 && customerLng === null) ||
                                   (customerLat === null && customerLng === 0);

        if (customerLat !== null && customerLat !== 0 && customerLng !== null && customerLng !== 0) {
            // Customer has valid coordinates
            coordinateSelectedLat = customerLat;
            coordinateSelectedLng = customerLng;
        } else if (currentCoords) {
            // Use current user location if customer coordinates are 0,0 or null
            coordinateSelectedLat = currentCoords.lat;
            coordinateSelectedLng = currentCoords.lng;
        } else {
            // Default fallback
            coordinateSelectedLat = -6.200000;
            coordinateSelectedLng = 106.816666;
        }

        coordinateFeedback.classList.remove('text-danger');
        coordinateFeedback.textContent = 'Klik peta untuk menentukan posisi customer.';

        coordinateModal.show();

        setTimeout(() => {
            if (!coordinateMap) {
                coordinateMap = new mapboxgl.Map({
                    container: 'coordinatePickerMap',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [coordinateSelectedLng, coordinateSelectedLat],
                    zoom: 14
                });

                coordinateMap.on('click', (ev) => {
                    setCoordinateMarker(ev.lngLat.lat, ev.lngLat.lng);
                });

                if (typeof MapboxGeocoder !== 'undefined') {
                    coordinateGeocoder = new MapboxGeocoder({
                        accessToken: mapboxToken,
                        mapboxgl,
                        marker: false,
                        placeholder: 'Cari nama lokasi, jalan, atau kota'
                    });
                    coordinateGeocoderElement = coordinateGeocoder.onAdd(coordinateMap);
                    if (coordinateGeocoderContainer) {
                        coordinateGeocoderContainer.innerHTML = '';
                        coordinateGeocoderContainer.appendChild(coordinateGeocoderElement);
                    }

                    coordinateGeocoder.on('result', (ev) => {
                        if (ev.result && ev.result.center) {
                            const [lng, lat] = ev.result.center;
                            coordinateMap.setCenter([lng, lat]);
                            setCoordinateMarker(lat, lng);
                        }
                    });

                    coordinateGeocoder.on('clear', () => {
                        coordinateMarker?.remove();
                        coordinateMarker = null;
                        coordinateSelectedLat = null;
                        coordinateSelectedLng = null;
                        coordinateFeedback.textContent = 'Klik peta untuk menentukan posisi customer.';
                    });
                }

                // Auto-detect GPS if coordinates are 0,0 or null
                coordinateMap.on('load', () => {
                    if (isZeroCoordinates && currentCoords) {
                        // Use current user location
                        coordinateSelectedLat = currentCoords.lat;
                        coordinateSelectedLng = currentCoords.lng;
                        coordinateMap.flyTo({ center: [coordinateSelectedLng, coordinateSelectedLat], zoom: 16 });
                        setCoordinateMarker(coordinateSelectedLat, coordinateSelectedLng);
                        coordinateFeedback.textContent = `Koordinat otomatis diambil dari lokasi Anda: ${coordinateSelectedLat.toFixed(6)}, ${coordinateSelectedLng.toFixed(6)}`;
                    } else if (isZeroCoordinates && !currentCoords) {
                        // Try to get GPS location
                        if (navigator.geolocation) {
                            coordinateFeedback.textContent = 'Mengambil lokasi GPS Anda...';
                            navigator.geolocation.getCurrentPosition((position) => {
                                coordinateSelectedLat = position.coords.latitude;
                                coordinateSelectedLng = position.coords.longitude;
                                coordinateMap.flyTo({ center: [coordinateSelectedLng, coordinateSelectedLat], zoom: 16 });
                                setCoordinateMarker(coordinateSelectedLat, coordinateSelectedLng);
                                coordinateFeedback.textContent = `Koordinat otomatis diambil dari GPS: ${coordinateSelectedLat.toFixed(6)}, ${coordinateSelectedLng.toFixed(6)}`;
                            }, (error) => {
                                coordinateFeedback.textContent = 'Tidak dapat memperoleh lokasi GPS. Silakan klik peta untuk menentukan lokasi customer.';
                                coordinateFeedback.classList.add('text-danger');
                            }, { enableHighAccuracy: true, timeout: 10000 });
                        }
                    }
                });
            } else {
                coordinateMap.setCenter([coordinateSelectedLng, coordinateSelectedLat]);
                coordinateMap.setZoom(14);
                if (coordinateGeocoder && coordinateGeocoderContainer && coordinateGeocoderElement) {
                    coordinateGeocoderContainer.innerHTML = '';
                    coordinateGeocoderContainer.appendChild(coordinateGeocoderElement);
                }

                // Auto-detect GPS if coordinates are 0,0 or null (when map already exists)
                if (isZeroCoordinates && currentCoords) {
                    // Use current user location
                    coordinateSelectedLat = currentCoords.lat;
                    coordinateSelectedLng = currentCoords.lng;
                    coordinateMap.flyTo({ center: [coordinateSelectedLng, coordinateSelectedLat], zoom: 16 });
                    setCoordinateMarker(coordinateSelectedLat, coordinateSelectedLng);
                    coordinateFeedback.textContent = `Koordinat otomatis diambil dari lokasi Anda: ${coordinateSelectedLat.toFixed(6)}, ${coordinateSelectedLng.toFixed(6)}`;
                } else if (isZeroCoordinates && !currentCoords) {
                    // Try to get GPS location
                    if (navigator.geolocation) {
                        coordinateFeedback.textContent = 'Mengambil lokasi GPS Anda...';
                        navigator.geolocation.getCurrentPosition((position) => {
                            coordinateSelectedLat = position.coords.latitude;
                            coordinateSelectedLng = position.coords.longitude;
                            coordinateMap.flyTo({ center: [coordinateSelectedLng, coordinateSelectedLat], zoom: 16 });
                            setCoordinateMarker(coordinateSelectedLat, coordinateSelectedLng);
                            coordinateFeedback.textContent = `Koordinat otomatis diambil dari GPS: ${coordinateSelectedLat.toFixed(6)}, ${coordinateSelectedLng.toFixed(6)}`;
                        }, (error) => {
                            coordinateFeedback.textContent = 'Tidak dapat memperoleh lokasi GPS. Silakan klik peta untuk menentukan lokasi customer.';
                            coordinateFeedback.classList.add('text-danger');
                        }, { enableHighAccuracy: true, timeout: 10000 });
                    }
                }
            }

            setCoordinateMarker(coordinateSelectedLat, coordinateSelectedLng);
            coordinateMap.resize();
        }, 250);
    }

    function setCoordinateMarker(lat, lng) {
        coordinateSelectedLat = lat;
        coordinateSelectedLng = lng;
        coordinateFeedback.classList.remove('text-danger');
        coordinateFeedback.textContent = `Latitude: ${lat.toFixed(6)}, Longitude: ${lng.toFixed(6)}`;

        if (coordinateMarker) {
            coordinateMarker.setLngLat([lng, lat]);
        } else {
            coordinateMarker = new mapboxgl.Marker({ color: '#f97316' })
                .setLngLat([lng, lat])
                .addTo(coordinateMap);
        }
    }

    coordinateModalEl?.addEventListener('hidden.bs.modal', () => {
        coordinateCustomerId = null;
        coordinateSelectedLat = null;
        coordinateSelectedLng = null;
        coordinateFeedback.textContent = '';
        if (coordinateGeocoder) {
            coordinateGeocoder.clear();
        }
    });

    coordinateSaveBtn?.addEventListener('click', () => {
        if (!coordinateCustomerId || coordinateSelectedLat === null || coordinateSelectedLng === null) {
            coordinateFeedback.classList.add('text-danger');
            coordinateFeedback.textContent = 'Silakan klik peta untuk menentukan lokasi customer.';
            return;
        }

        coordinateFeedback.classList.remove('text-danger');
        coordinateFeedback.textContent = 'Menyimpan koordinat...';

        fetch(`/visits/customer/${coordinateCustomerId}/coordinates`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                latitude: coordinateSelectedLat,
                longitude: coordinateSelectedLng
            })
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('Gagal menyimpan koordinat');
            }
            return res.json();
        })
        .then(() => {
            coordinateFeedback.textContent = 'Koordinat berhasil disimpan.';
            fetchNearestCustomers();
            setTimeout(() => coordinateModal.hide(), 500);
        })
        .catch(() => {
            coordinateFeedback.classList.add('text-danger');
            coordinateFeedback.textContent = 'Terjadi kesalahan saat menyimpan koordinat. Coba lagi.';
        });
    });

    function renderCustomers() {
        customerResults.innerHTML = '';

        if (!nearestCustomers.length) {
            customerResults.innerHTML = '<div class="list-group-item small text-muted">Customer terdekat belum tersedia. Pastikan koordinat tersimpan atau ubah kata kunci.</div>';
            return;
        }

        nearestCustomers.forEach(customer => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action text-start';
            const hasDistance = customer.distance_km !== null && customer.distance_km !== undefined;
            const distanceText = hasDistance ? `${parseFloat(customer.distance_km).toFixed(2)} km` : 'Koordinat Peta/Map belum ditentukan';
            item.innerHTML = `
                <div class="fw-semibold mb-1">${customer.namacustomer || '-'} <span class="badge bg-light text-dark">${customer.kodecustomer}</span></div>
                <div class="small text-muted mb-1">${customer.alamatcustomer || ''} - ${customer.kotacustomer || ''}</div>
                <div class="small text-primary fw-semibold mb-1">${distanceText}</div>
                ${hasDistance ? '' : '<button type="button" class="btn text-white bg-success btn-outline-success btn-link btn-sm p-1 customer-set-location text-decoration-none" data-action="set-location">Tentukan lokasi di peta</button>'}
            `;

            item.addEventListener('click', function(event) {
                if (event.target && event.target.matches('.customer-set-location')) {
                    event.preventDefault();
                    event.stopPropagation();
                    openCoordinateModal(customer);
                    return;
                }
                selectedCustomerId.value = customer.id;
                if (hasDistance) {
                    btnSubmit.disabled = !(hiddenLat.value && hiddenLng.value);
                    selectedCustomerInfo.innerHTML = `Customer dipilih: <strong>${customer.namacustomer}</strong> (${customer.kodecustomer}) - ${distanceText}`;
                } else {
                    btnSubmit.disabled = true;
                    selectedCustomerInfo.innerHTML = `Customer dipilih: <strong>${customer.namacustomer}</strong> (${customer.kodecustomer})`;
                    selectedCustomerInfo.innerHTML += '<br><span class="text-danger">Koordinat customer belum ditentukan. Set lokasi terlebih dahulu.</span>';
                }
                if (customer.longitude && customer.latitude) {
                    map.easeTo({ center: [customer.longitude, customer.latitude], zoom: 15 });
                }
            });

            customerResults.appendChild(item);

            if (customer.latitude && customer.longitude) {
                const marker = new mapboxgl.Marker({ color: '#059669' })
                    .setLngLat([customer.longitude, customer.latitude])
                    .setPopup(new mapboxgl.Popup({ offset: 16 }).setHTML(`
                        <strong>${customer.namacustomer}</strong><br>
                        ${customer.alamatcustomer || ''}<br>
                        <small>${distanceText}</small>
                    `))
                    .addTo(map);
                customerMarkers.push(marker);
            }
        });
    }

    function fetchNearestCustomers() {
        const query = searchInput.value.trim();

        if (!currentCoords && !query) {
            updateStatus('Ambil lokasi Anda atau masukkan kata kunci customer.', 'muted');
            customerResults.innerHTML = '<div class="list-group-item text-muted small">Mulai dengan mengambil lokasi Anda atau masukkan kata kunci.</div>';
            return;
        }

        updateStatus('Mengambil data customer...', 'info');
        const params = new URLSearchParams({ limit: 50 });
        if (currentCoords) {
            params.append('lat', currentCoords.lat);
            params.append('lng', currentCoords.lng);
        }
        if (query) {
            params.append('q', query);
        }

        fetch('/visits/nearest-customers?' + params.toString())
            .then(res => {
                if (!res.ok) {
                    throw new Error('Gagal memuat data');
                }
                return res.json();
            })
            .then(res => {
                nearestCustomers = res.data || [];
                clearCustomerMarkers();
                renderCustomers();
                if (nearestCustomers.length) {
                    updateStatus(currentCoords ? 'Koordinat tersimpan. Silakan pilih customer terdekat.' : 'Hasil pencarian customer ditampilkan.', 'success');
                } else {
                    updateStatus('Tidak ditemukan customer sesuai data tersebut.', 'danger');
                }
            })
            .catch(() => {
                updateStatus('Gagal mengambil data customer. Coba lagi.', 'danger');
            });
    }

    btnUseLocation?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            updateStatus('Perangkat Anda tidak mendukung geolocation.', 'danger');
            return;
        }
        updateStatus('Mengambil lokasi GPS...', 'info');
        navigator.geolocation.getCurrentPosition((position) => {
            const { latitude, longitude } = position.coords;
            setUserLocation(latitude, longitude);
            updateStatus('Lokasi berhasil diperoleh.');
        }, (error) => {
            console.error(error);
            updateStatus('Tidak dapat memperoleh lokasi otomatis. Pastikan izin lokasi telah diberikan.', 'danger');
        }, { enableHighAccuracy: true });
    });

    btnRefreshNearby?.addEventListener('click', fetchNearestCustomers);
    searchInput?.addEventListener('input', function() {
        if (this.value.length >= 2 || this.value.length === 0) {
            fetchNearestCustomers();
        }
    });

    // Auto geolocate on map load for convenience
    map.on('load', () => {
        if (navigator.geolocation) {
            geolocate.trigger();
        }
    });

    map.on('click', (ev) => {
        setUserLocation(ev.lngLat.lat, ev.lngLat.lng);
        updateStatus('Koordinat diperbarui dari titik peta.', 'success');
    });

    geolocate.on('geolocate', (ev) => {
        const lat = ev.coords.latitude;
        const lng = ev.coords.longitude;
        setUserLocation(lat, lng);
        updateStatus('Lokasi terdeteksi otomatis.');
    });
});
</script>
<?php endif; ?>


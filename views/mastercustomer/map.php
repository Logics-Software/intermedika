<?php
$title = 'Peta Lokasi Customer';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

$additionalStyles = $additionalStyles ?? [];
$additionalStyles[] = 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css';
$additionalStyles[] = $baseUrl . '/assets/css/mapbox-gl-geocoder.css';
$additionalStyles[] = $baseUrl . '/assets/css/choices.min.css';

$additionalInlineStyles = $additionalInlineStyles ?? [];
$additionalInlineStyles[] = <<<CSS
/* Hide header and adjust padding on map page */
body.has-header {
    padding-top: 0 !important;
}
.app-header {
    display: none !important;
}
/* Adjust content container padding since header is hidden */
.container-fluid.content-container {
    padding-top: 1rem !important;
}
/* Fix Choices.js dropdown being cut off */
#customerInfoCard {
    overflow: visible !important;
    position: relative;
    z-index: 1;
}
#customerInfoCard .card {
    overflow: visible !important;
}
#customerInfoCard .card-body {
    overflow: visible !important;
}
#customerInfoCard .row {
    overflow: visible !important;
}
#customerInfoCard .col-md-6 {
    overflow: visible !important;
}
/* Choices.js dropdown styling - ensure it's not cut off and above Mapbox Geocoder */
#customerInfoCard .choices {
    position: relative;
    z-index: 10000 !important;
}
#customerInfoCard .choices .choices__inner {
    position: relative;
    z-index: 10001 !important;
}
#customerInfoCard .choices__list--dropdown {
    position: absolute !important;
    z-index: 10002 !important;
    max-height: 300px;
    overflow-y: auto;
    margin-top: 4px;
}
#customerInfoCard .choices.is-open {
    overflow: visible !important;
    z-index: 10000 !important;
}
#customerInfoCard .choices.is-open .choices__list--dropdown {
    display: block !important;
    z-index: 10002 !important;
}
/* Ensure Mapbox Geocoder doesn't overlap Choices dropdown */
.mapboxgl-ctrl-geocoder {
    z-index: 1000 !important;
}
.mapboxgl-ctrl-geocoder .suggestions {
    z-index: 1000 !important;
}
/* Ensure customer dropdown is always on top */
#customerInfoCard {
    position: relative;
    z-index: 10000 !important;
}
/* Ensure dropdown can extend beyond card boundaries */
.p-3 {
    overflow: visible !important;
}
/* Override any overflow hidden on parent containers */
body > .container-fluid,
body > .container-fluid > .row,
body > .container-fluid > .row > .col-12 {
    overflow: visible !important;
}
CSS;

$additionalScripts = $additionalScripts ?? [];
$additionalScripts[] = 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js';
$additionalScripts[] = $baseUrl . '/assets/js/mapbox-gl-geocoder.min.js';
$additionalScripts[] = $baseUrl . '/assets/js/choices.min.js';

$mapboxToken = $mapboxToken ?? '';
$hasMapboxToken = !empty($mapboxToken);
$customer = $customer ?? null;
$customerId = $customerId ?? null;
$customerError = $customerError ?? null;
$locationIconUrl = $baseUrl . '/assets/icons/fa-location-dot.svg';

require __DIR__ . '/../layouts/header.php';
?>

<div class="p-3">
    <?php if ($customerError): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger mb-0">
                    <?= htmlspecialchars($customerError) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row mb-3" id="customerInfoCard" style="<?= $customer ? '' : 'display: none;' ?>">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6">
                            <select id="customerSelect" class="form-select" data-placeholder="Pilih atau cari customer...">
                                <option value="">-- Pilih Customer --</option>
                            </select>
                            <!-- <div class="text-muted small">Kode: <strong id="customerKode"><?= htmlspecialchars($customer['kodecustomer'] ?? '-') ?></strong></div> -->
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h3 class="h5 mb-1" id="customerName"><?= htmlspecialchars($customer['namacustomer'] ?? '-') ?></h3>
                            <div class="text-muted small" id="customerAddress">Alamat: <?= htmlspecialchars(trim(($customer['alamatcustomer'] ?? '') . ' ' . ($customer['kotacustomer'] ?? ''))) ?></div>
                            <!-- <div class="small text-muted" id="customerId">Kode: <?= htmlspecialchars($customer['kodecustomer'] ?? '-') ?> - Customer ID: <?= (int)($customer['id'] ?? 0) ?></div> -->
                            <div class="small" id="customerCoordinates">
                                <?php if (!empty($customer['latitude']) && !empty($customer['longitude'])): ?>
                                    <span class="text-success">Koordinat saat ini: <?= number_format((float)$customer['latitude'], 6) ?>, <?= number_format((float)$customer['longitude'], 6) ?></span>
                                <?php else: ?>
                                    <span class="text-danger">Customer belum memiliki koordinat.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h3 class="h5 mb-0">Peta Interaktif</h3>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted small">Lat</span>
                                <input type="text" id="selectedLatitude" class="form-control form-control-sm coordinate-input" readonly>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted small">Lng</span>
                                <input type="text" id="selectedLongitude" class="form-control form-control-sm coordinate-input" readonly>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" id="btnCancelCoordinate" style="<?= $customerId ? '' : 'display: none;' ?>">
                            Tutup
                        </button>
                        <button type="button" class="btn btn-success btn-sm d-flex align-items-center" id="btnUseMyLocation" <?= $hasMapboxToken ? '' : 'disabled' ?>>
                            <img src="<?= htmlspecialchars($locationIconUrl) ?>" alt="" width="16" height="16" class="me-1 map-action-icon" loading="lazy" aria-hidden="true">Lokasi Saya
                        </button>
                        <button type="button" class="btn btn-success btn-sm" id="btnSaveCoordinate" style="<?= $customerId ? '' : 'display: none;' ?>">
                            <?= icon('save', 'me-1', 14) ?>Simpan Koordinat
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!$hasMapboxToken): ?>
                        <div class="alert alert-danger mb-0">
                            Mapbox access token belum dikonfigurasi. Tambahkan MAPBOX_ACCESS_TOKEN pada environment server untuk menampilkan peta.
                        </div>
                    <?php else: ?>
                        <div id="mapGeocoder" class="mapbox-geocoder-container mb-3"></div>
                        <div id="mapCanvas" class="map-canvas-large"></div>
                        <div class="small text-muted mt-2" id="mapStatus">Klik peta atau seret marker untuk menentukan koordinat.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$mapboxTokenJs = json_encode($mapboxToken, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$customerJs = json_encode($customer ? [
    'id' => (int)$customer['id'],
    'name' => $customer['namacustomer'] ?? '',
    'latitude' => $customer['latitude'] !== null ? (float)$customer['latitude'] : null,
    'longitude' => $customer['longitude'] !== null ? (float)$customer['longitude'] : null,
    'kodecustomer' => $customer['kodecustomer'] ?? ''
] : null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$saveEndpoint = $customerId ? "/mastercustomer/{$customerId}/coordinates" : null;
$saveEndpointJs = json_encode($saveEndpoint, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

$additionalInlineScripts = $additionalInlineScripts ?? [];
$additionalInlineScripts[] = <<<JS
(function() {
    const mapContainer = document.getElementById('mapCanvas');
    const latField = document.getElementById('selectedLatitude');
    const lngField = document.getElementById('selectedLongitude');
    const statusEl = document.getElementById('mapStatus');
    const saveBtn = document.getElementById('btnSaveCoordinate');
    const cancelBtn = document.getElementById('btnCancelCoordinate');
    const useLocationBtn = document.getElementById('btnUseMyLocation');
    const geocoderContainer = document.getElementById('mapGeocoder');
    const customerSelect = document.getElementById('customerSelect');

    const token = {$mapboxTokenJs};
    let customerData = {$customerJs};
    let saveEndpoint = {$saveEndpointJs};
    let customerChoiceInstance = null;
    let map = null;
    let marker = null;

    function setStatus(message, type) {
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.classList.remove('text-danger', 'text-success', 'text-muted');
        if (type === 'error') {
            statusEl.classList.add('text-danger');
        } else if (type === 'success') {
            statusEl.classList.add('text-success');
        } else {
            statusEl.classList.add('text-muted');
        }
    }

    // Fetch with retry mechanism
    function fetchWithRetry(url, options, maxRetries = 3, delay = 1000) {
        return new Promise(function(resolve, reject) {
            let retries = 0;
            
            function attemptFetch() {
                // Add timeout to fetch
                const controller = new AbortController();
                const timeoutId = setTimeout(function() {
                    controller.abort();
                }, 10000); // 10 second timeout
                
                const fetchOptions = Object.assign({}, options || {}, {
                    signal: controller.signal,
                    cache: 'no-cache',
                    headers: Object.assign({}, options?.headers || {}, {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    })
                });
                
                fetch(url, fetchOptions)
                    .then(function(response) {
                        clearTimeout(timeoutId);
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(function(data) {
                        resolve(data);
                    })
                    .catch(function(error) {
                        clearTimeout(timeoutId);
                        retries++;
                        
                        if (retries < maxRetries) {
                            console.warn('Fetch attempt ' + retries + ' failed, retrying in ' + delay + 'ms...', error.message);
                            setTimeout(attemptFetch, delay);
                        } else {
                            reject(new Error('Gagal memuat data setelah ' + maxRetries + ' percobaan: ' + (error.message || 'Unknown error')));
                        }
                    });
            }
            
            attemptFetch();
        });
    }

    // Function to load customers list
    function loadCustomers() {
        if (!customerSelect) return;
        
        setStatus('Memuat daftar customer...', '');
        fetchWithRetry('/api/mastercustomer?per_page=1000&sort_by=namacustomer&sort_order=ASC', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(function(result) {
                if (result.success && result.data && Array.isArray(result.data)) {
                    const options = result.data.map(function(customer) {
                        return {
                            value: String(customer.id),
                            label: (customer.kodecustomer || '') + ' - ' + (customer.namacustomer || '')
                        };
                    });
                    
                    if (customerChoiceInstance) {
                        customerChoiceInstance.clearChoices();
                        customerChoiceInstance.setChoices(options, 'value', 'label', true);
                        
                        // Set selected value if customerData exists
                        if (customerData && customerData.id) {
                            customerChoiceInstance.setChoiceByValue(String(customerData.id));
                        }
                    } else {
                        // Initialize Choices.js
                        if (typeof Choices !== 'undefined') {
                            customerChoiceInstance = new Choices(customerSelect, {
                                searchEnabled: true,
                                searchResultLimit: 200,
                                searchPlaceholderValue: 'Ketik untuk mencari customer...',
                                shouldSort: false,
                                itemSelectText: '',
                                noResultsText: 'Customer tidak ditemukan',
                                placeholder: true,
                                placeholderValue: 'Pilih atau cari customer...'
                            });
                            
                            customerChoiceInstance.setChoices(options, 'value', 'label', true);
                            
                            // Set selected value if customerData exists
                            if (customerData && customerData.id) {
                                customerChoiceInstance.setChoiceByValue(String(customerData.id));
                            }
                            
                            // Handle customer change
                            customerSelect.addEventListener('change', function() {
                                const selectedId = this.value;
                                if (selectedId) {
                                    loadCustomerData(parseInt(selectedId));
                                }
                            });
                        }
                    }
                    setStatus('Daftar customer berhasil dimuat.', 'success');
                } else {
                    throw new Error('Format data tidak valid');
                }
            })
            .catch(function(error) {
                console.error('Error loading customers:', error);
                setStatus('Gagal memuat daftar customer: ' + error.message, 'error');
            });
    }

    // Function to load customer data and update UI
    function loadCustomerData(customerId) {
        if (!customerId) return;
        
        setStatus('Memuat data customer...', '');
        fetchWithRetry('/api/mastercustomer?id=' + customerId, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(function(result) {
                if (result.success && result.data) {
                    const newCustomer = result.data;
                    customerData = {
                        id: parseInt(newCustomer.id),
                        name: newCustomer.namacustomer || '',
                        latitude: newCustomer.latitude !== null ? parseFloat(newCustomer.latitude) : null,
                        longitude: newCustomer.longitude !== null ? parseFloat(newCustomer.longitude) : null,
                        kodecustomer: newCustomer.kodecustomer || ''
                    };
                    saveEndpoint = '/mastercustomer/' + customerData.id + '/coordinates';
                    
                    // Update customer info card
                    updateCustomerInfo(newCustomer);
                    
                    // Update map
                    if (map && marker) {
                        updateMapWithCustomerData();
                    }
                    
                    // Show/hide save button
                    if (saveBtn) {
                        saveBtn.style.display = 'inline-block';
                    }
                    if (cancelBtn) {
                        cancelBtn.style.display = 'inline-block';
                    }
                    
                    setStatus('Customer dipilih: ' + customerData.name, 'success');
                } else {
                    throw new Error('Data customer tidak ditemukan');
                }
            })
            .catch(function(error) {
                console.error('Error loading customer data:', error);
                setStatus('Gagal memuat data customer: ' + error.message, 'error');
            });
    }

    // Function to update customer info card
    function updateCustomerInfo(customer) {
        const customerCard = document.getElementById('customerInfoCard');
        if (!customerCard) return;
        
        const nameEl = document.getElementById('customerName');
        const kodeEl = document.getElementById('customerKode');
        const idEl = document.getElementById('customerId');
        const coordEl = document.getElementById('customerCoordinates');
        const alamatEl = document.getElementById('customerAddress');
        
        if (nameEl) nameEl.textContent = customer.namacustomer || '-';
        if (kodeEl) kodeEl.textContent = customer.kodecustomer || '-';
        if (idEl) idEl.textContent = 'Customer ID: ' + customer.id;
        
        if (coordEl) {
            if (customer.latitude && customer.longitude && customer.latitude !== 0 && customer.longitude !== 0) {
                coordEl.innerHTML = '<span class="text-success">Koordinat saat ini: ' + 
                    parseFloat(customer.latitude).toFixed(6) + ', ' + 
                    parseFloat(customer.longitude).toFixed(6) + '</span>';
            } else {
                coordEl.innerHTML = '<span class="text-danger">Customer belum memiliki koordinat.</span>';
            }
        }
        
        if (alamatEl) {
            const alamat = (customer.alamatcustomer || '') + ' ' + (customer.kotacustomer || '');
            alamatEl.textContent = 'Alamat: ' + alamat.trim();
        }
        
        // Show customer card
        customerCard.style.display = '';
    }

    // Function to update map with customer data
    function updateMapWithCustomerData() {
        if (!map || !marker || !customerData) return;
        
        const customerLat = customerData.latitude;
        const customerLng = customerData.longitude;
        const isZeroCoordinates = (customerLat === 0 && customerLng === 0) || 
                                   (customerLat === null && customerLng === null) ||
                                   (customerLat === 0 && customerLng === null) ||
                                   (customerLat === null && customerLng === 0);
        
        const initialLat = customerLat !== null && customerLat !== 0 ? customerLat : -6.200000;
        const initialLng = customerLng !== null && customerLng !== 0 ? customerLng : 106.816666;
        const hasInitial = !isZeroCoordinates;
        
        // Update map center and marker
        map.flyTo({
            center: [initialLng, initialLat],
            zoom: hasInitial ? 15 : 11
        });
        
        marker.setLngLat([initialLng, initialLat]);
        
        // Update coordinate fields
        if (!isZeroCoordinates) {
            updateFields({ lat: initialLat, lng: initialLng }, true);
        } else {
            if (latField) latField.value = '';
            if (lngField) lngField.value = '';
        }
    }

    // Load customers list on page load
    if (customerSelect) {
        loadCustomers();
    }

    if (!mapContainer || !token) {
        return;
    }

    mapboxgl.accessToken = token;

    // Check if coordinates are 0,0 or null/undefined
    const customerLat = customerData && typeof customerData.latitude === 'number' ? customerData.latitude : null;
    const customerLng = customerData && typeof customerData.longitude === 'number' ? customerData.longitude : null;
    // Auto-detect if both coordinates are 0, or if both are null/undefined
    const isZeroCoordinates = (customerLat === 0 && customerLng === 0) || 
                               (customerLat === null && customerLng === null) ||
                               (customerLat === 0 && customerLng === null) ||
                               (customerLat === null && customerLng === 0);
    
    const initialLat = customerLat !== null && customerLat !== 0 ? customerLat : -6.200000;
    const initialLng = customerLng !== null && customerLng !== 0 ? customerLng : 106.816666;
    const hasInitial = customerData && typeof customerData.latitude === 'number' && typeof customerData.longitude === 'number' && !isZeroCoordinates;

    map = new mapboxgl.Map({
        container: mapContainer,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [initialLng, initialLat],
        zoom: hasInitial ? 15 : 11
    });

    map.addControl(new mapboxgl.NavigationControl());

    marker = new mapboxgl.Marker({ draggable: true })
        .setLngLat([initialLng, initialLat])
        .addTo(map);

    function updateFields(lngLat, suppressStatus) {
        if (!lngLat) {
            return;
        }
        if (latField) {
            latField.value = lngLat.lat.toFixed(6);
        }
        if (lngField) {
            lngField.value = lngLat.lng.toFixed(6);
        }
        if (!suppressStatus) {
            setStatus('Koordinat diperbarui: ' + lngLat.lat.toFixed(6) + ', ' + lngLat.lng.toFixed(6), '');
        }
    }

    marker.on('dragend', function() {
        updateFields(marker.getLngLat());
    });

    map.on('click', function(event) {
        marker.setLngLat(event.lngLat);
        updateFields(event.lngLat);
    });

    if (typeof MapboxGeocoder === 'function' && geocoderContainer) {
        const geocoder = new MapboxGeocoder({
            accessToken: token,
            mapboxgl: mapboxgl,
            marker: false,
            placeholder: 'Cari lokasi customer…',
            language: 'id',
            countries: 'id'
        });
        geocoderContainer.innerHTML = '';
        geocoderContainer.appendChild(geocoder.onAdd(map));
        geocoder.on('result', function(ev) {
            if (!ev || !ev.result || !ev.result.center) {
                return;
            }
            const center = ev.result.center;
            map.flyTo({ center: center, zoom: 16 });
            marker.setLngLat(center);
            updateFields({ lng: center[0], lat: center[1] });
            setStatus('Lokasi ditemukan: ' + (ev.result.place_name || ''), 'success');
        });
    }

    // Function to get user location
    function getUserLocation() {
        if (!navigator.geolocation) {
            setStatus('Perangkat tidak mendukung geolocation.', 'error');
            return;
        }
        setStatus('Mengambil lokasi perangkat…', '');
        navigator.geolocation.getCurrentPosition(function(pos) {
            const coords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            map.flyTo({ center: [coords.lng, coords.lat], zoom: 16 });
            marker.setLngLat([coords.lng, coords.lat]);
            updateFields(coords);
            setStatus('Koordinat diperoleh dari lokasi perangkat.', 'success');
        }, function(err) {
            setStatus('Tidak dapat memperoleh lokasi: ' + (err && err.message ? err.message : 'akses ditolak'), 'error');
        }, {
            enableHighAccuracy: true,
            timeout: 10000
        });
    }

    if (useLocationBtn) {
        useLocationBtn.addEventListener('click', getUserLocation);
    }

    // Auto-detect GPS if coordinates are 0,0 or null
    if (isZeroCoordinates) {
        // Wait for map to load first
        map.on('load', function() {
            setTimeout(function() {
                getUserLocation();
            }, 500);
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            // Try to close the browser window/tab
            window.close();
            // If window.close() doesn't work (window not opened by script),
            // fallback to redirect after 500ms
            setTimeout(function() {
                if (!document.hidden) {
                    // Try to go back in history, or redirect to customer list
                    if (document.referrer && document.referrer !== window.location.href) {
                        window.location.href = document.referrer;
                    } else {
                        window.location.href = '/mastercustomer';
                    }
                }
            }, 500);
        });
    }

    if (saveBtn && saveEndpoint) {
        saveBtn.addEventListener('click', function() {
            if (!latField || !lngField || !latField.value || !lngField.value) {
                showAlert({
                    title: 'Validasi Koordinat',
                    message: 'Pilih koordinat terlebih dahulu.',
                    buttonText: 'Mengerti',
                    buttonClass: 'btn-primary'
                });
                return;
            }

            const payload = {
                latitude: parseFloat(latField.value),
                longitude: parseFloat(lngField.value)
            };

            if (Number.isNaN(payload.latitude) || Number.isNaN(payload.longitude)) {
                showAlert({
                    title: 'Validasi Koordinat',
                    message: 'Koordinat tidak valid.',
                    buttonText: 'Mengerti',
                    buttonClass: 'btn-primary'
                });
                return;
            }

            setStatus('Menyimpan koordinat...', '');
            saveBtn.disabled = true;

            fetch(saveEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(function(response) {
                    saveBtn.disabled = false;
                    var contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Response tidak valid. Server mengembalikan format yang tidak diharapkan.');
                    }
                    if (!response.ok) {
                        return response.json().then(function(body) {
                            var message = body && (body.error || body.message) ? body.error || body.message : 'Gagal menyimpan koordinat.';
                            throw new Error(message);
                        }).catch(function(err) {
                            if (err.message && err.message.includes('valid')) {
                                throw err;
                            }
                            throw new Error('Gagal menyimpan koordinat.');
                        });
                    }
                    return response.json();
                })
                .then(function(data) {
                    setStatus('Koordinat berhasil disimpan.', 'success');
                    
                    // Show success message
                    showAlert({
                        title: 'Berhasil',
                        message: 'Data koordinat customer telah tersimpan.',
                        buttonText: 'Mengerti',
                        buttonClass: 'btn-success',
                        headerClass: 'bg-success text-white'
                    });
                    
                    // Reload customer data to get updated coordinates after a short delay
                    if (customerData && customerData.id) {
                        setTimeout(function() {
                            loadCustomerData(customerData.id);
                        }, 300);
                    }
                })
                .catch(function(error) {
                    saveBtn.disabled = false;
                    setStatus(error.message || 'Gagal menyimpan koordinat.', 'error');
                    showAlert({
                        title: 'Error',
                        message: error.message || 'Gagal menyimpan koordinat.',
                        buttonText: 'Mengerti',
                        buttonClass: 'btn-danger'
                    });
                });
        });
    }

    // Only update fields if we have valid coordinates (not 0,0 or null)
    if (!isZeroCoordinates) {
        updateFields({ lat: initialLat, lng: initialLng }, true);
    }
})();
JS;

require __DIR__ . '/../layouts/footer.php';
?>


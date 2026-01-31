<?php
$title = 'Kunjungan Sales';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

$statusOptions = ['Direncanakan', 'Sedang Berjalan', 'Selesai', 'Dibatalkan'];

$user = $user ?? Auth::user();
$role = $role ?? ($user['role'] ?? '');
$periode = $periode ?? 'today';
$startDate = $startDate ?? '';
$endDate = $endDate ?? '';


require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kunjungan</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (!empty($activeVisit)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="fw-bold mb-1">Kunjungan Sedang Berjalan</h5>
                    <p class="mb-1">Customer: <strong><?= htmlspecialchars($activeVisit['namacustomer'] ?? '-') ?></strong></p>
                    <p class="mb-1">Mulai: <?= date('d/m/Y H:i', strtotime($activeVisit['check_in_time'])) ?></p>
                    <p class="mb-0">Status: <span class="badge bg-warning text-dark">Sedang Berjalan</span></p>
                </div>
                <?php if (Auth::isSales()): ?>
                <div>
                    <a href="/visits/checkout/<?= $activeVisit['visit_id'] ?>" class="btn btn-outline-primary">Selesaikan Sekarang</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0 me-auto">Kunjungan</h4>
                <?php if (Auth::isSales()): ?>
                    <?php if (!empty($activeVisit)): ?>
                    <a href="/visits/checkout/<?= $activeVisit['visit_id'] ?>" class="btn btn-warning">
                        <?= icon('share-from-square', 'mb-1 me-2', 16) ?> Lanjutkan Check-out
                    </a>
                    <?php else: ?>
                    <a href="/visits/check-in" class="btn btn-primary">
                        <?= icon('paper-plane', 'mb-1 me-2', 16) ?> Check-in Baru
                    </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body">
            <!-- <div class="row search-filter-card"> -->
                <form method="GET" action="/visits" class="mb-3" id="filterForm">
                    <div class="row g-2 align-items-end search-filter-card">
                        <div class="col-12 col-lg-3">
                            <input type="text" class="form-control" name="search" placeholder="Cari customer, kode atau kota" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-6 col-lg-2">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <?php foreach ($statusOptions as $option): ?>
                                <option value="<?= $option ?>" <?= $statusFilter === $option ? 'selected' : '' ?>><?= $option ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-lg-2">
                            <select name="periode" class="form-select" id="periodeSelect" onchange="handleDateFilterChange(true)">
                                <option value="today" <?= ($periode ?? 'today') === 'today' ? 'selected' : '' ?>>Hari ini</option>
                                <option value="week" <?= ($periode ?? '') === 'week' ? 'selected' : '' ?>>Minggu ini</option>
                                <option value="month" <?= ($periode ?? '') === 'month' ? 'selected' : '' ?>>Bulan ini</option>
                                <option value="year" <?= ($periode ?? '') === 'year' ? 'selected' : '' ?>>Tahun ini</option>
                                <option value="custom" <?= ($periode ?? '') === 'custom' ? 'selected' : '' ?>>Custom</option>
                            </select>
                        </div>
                        <div class="col-6 col-lg-2" id="startDateWrapper" style="display: <?= ($periode ?? 'today') === 'custom' ? 'block' : 'none' ?>;">
                            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>" placeholder="Dari">
                        </div>
                        <div class="col-6 col-lg-2" id="endDateWrapper" style="display: <?= ($periode ?? 'today') === 'custom' ? 'block' : 'none' ?>;">
                            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>" placeholder="Sampai">
                        </div>
                        <div class="col-6 col-lg-1">
                            <select name="per_page" class="form-select" onchange="this.form.submit()">
                                <?php foreach ([10, 20, 50, 100, 200, 500, 1000] as $option): ?>
                                <option value="<?= $option ?>" <?= $perPage == $option ? 'selected' : '' ?>><?= $option ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-lg-4 d-lg-flex justify-content-lg-end">
                            <div class="row g-2 w-100">
                                <div class="col-6 col-lg-6">
                                    <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                                </div>
                                <div class="col-6 col-lg-6">
                                    <a href="/visits" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <!-- </div> -->

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Waktu Masuk</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Durasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($visits)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada catatan kunjungan.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($visits as $visit): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= date('d/m/Y H:i', strtotime($visit['check_in_time'])) ?></div>
                                <?php if (!empty($visit['check_out_time'])): ?>
                                <div class="small text-muted">Keluar: <?= date('d/m/Y H:i', strtotime($visit['check_out_time'])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($visit['namacustomer'] ?? '-') ?></div>
                                <!-- <div class="small text-muted">Kode: <?= htmlspecialchars($visit['kodecustomer']) ?></div> -->
                                <div class="small text-muted">Kota: <?= htmlspecialchars($visit['kotacustomer'] ?? '-') ?></div>
                            </td>
                            <td>
                                <?php
                                $badgeClass = 'bg-secondary';
                                switch ($visit['status_kunjungan']) {
                                    case 'Sedang Berjalan':
                                        $badgeClass = 'bg-warning text-dark';
                                        break;
                                    case 'Selesai':
                                        $badgeClass = 'bg-success';
                                        break;
                                    case 'Dibatalkan':
                                        $badgeClass = 'bg-danger';
                                        break;
                                    case 'Direncanakan':
                                        $badgeClass = 'bg-info text-dark';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($visit['status_kunjungan']) ?></span>
                                <?php if (!empty($visit['jarak_dari_kantor'])): ?>
                                <div class="small text-muted mt-1">Jarak: <?= number_format($visit['jarak_dari_kantor'], 2) ?> km</div>
                                <?php endif; ?>
                            </td>
                            <td class="table-text-wrap-220">
                                <small><?= nl2br(htmlspecialchars($visit['catatan'] ?? '-')) ?></small>
                            </td>
                            <td>
                                <?php if (!empty($visit['check_out_time'])): ?>
                                    <?php
                                    $duration = strtotime($visit['check_out_time']) - strtotime($visit['check_in_time']);
                                    $hours = floor($duration / 3600);
                                    $minutes = floor(($duration % 3600) / 60);
                                    ?>
                                    <div><?= $hours ?> jam <?= $minutes ?> menit</div>
                                <?php else: ?>
                                    <span class="text-muted">Sedang berjalan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    <?php if ($visit['status_kunjungan'] === 'Sedang Berjalan'): ?>
                                        <?php if (Auth::isSales()): ?>
                                        <a href="/visits/checkout/<?= $visit['visit_id'] ?>" class="btn btn-sm btn-warning">Check-out</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                    <?php
                                    // Ensure visit_id exists
                                    $visitId = $visit['visit_id'] ?? null;
                                    if (!$visitId) {
                                        continue; // Skip if no visit_id
                                    }
                                    
                                    $detailPayload = [
                                        'visit_id' => $visitId,
                                        'namacustomer' => $visit['namacustomer'] ?? '-',
                                        'kodecustomer' => !empty($visit['master_kodecustomer']) ? $visit['master_kodecustomer'] : ($visit['kodecustomer'] ?? '-'),
                                        'status_kunjungan' => $visit['status_kunjungan'] ?? '-',
                                        'check_in_time' => $visit['check_in_time'] ?? null,
                                        'check_out_time' => $visit['check_out_time'] ?? null,
                                        'check_in_lat' => $visit['check_in_lat'] ?? null,
                                        'check_in_long' => $visit['check_in_long'] ?? null,
                                        'check_out_lat' => $visit['check_out_lat'] ?? null,
                                        'check_out_long' => $visit['check_out_long'] ?? null,
                                        'catatan' => $visit['catatan'] ?? '',
                                        'jarak_dari_kantor' => $visit['jarak_dari_kantor'] ?? null
                                    ];
                                    $detailJson = htmlspecialchars(json_encode($detailPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-visit-detail" data-visit-id="<?= htmlspecialchars($visitId) ?>" data-visit="<?= $detailJson ?>">
                                        Detail
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <?php
            // Get current page from URL (always use $_GET to ensure it's current)
            $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : (isset($page) ? (int)$page : 1);
            // Ensure currentPage is at least 1
            if ($currentPage < 1) {
                $currentPage = 1;
            }
            // Use currentPage for all calculations
            $page = $currentPage;
            $totalPages = (int)$totalPages;
            $perPage = (int)$perPage;
            
            // Build link function for pagination
            $buildLink = function ($p) use ($perPage, $search, $statusFilter, $periode, $startDate, $endDate) {
                $link = '?page=' . $p
                    . '&per_page=' . $perPage
                    . '&search=' . urlencode($search)
                    . '&status=' . urlencode($statusFilter)
                    . '&periode=' . urlencode($periode ?? 'today');
                if (!empty($startDate)) {
                    $link .= '&start_date=' . urlencode($startDate);
                }
                if (!empty($endDate)) {
                    $link .= '&end_date=' . urlencode($endDate);
                }
                return $link;
            };
            $maxLinks = 3;
            $half = (int)floor($maxLinks / 2);
            $start = max(1, $page - $half);
            $end = min($totalPages, $start + $maxLinks - 1);
            if ($end - $start + 1 < $maxLinks) {
                $start = max(1, $end - $maxLinks + 1);
            }
            ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <?php
                        // Calculate previous page, ensuring it's an integer
                        $prevPage = (int)max(1, (int)$page - 1);
                        // Ensure prevPage is at least 1
                        if ($prevPage < 1) $prevPage = 1;
                        ?>
                        <a class="page-link" href="/visits<?php echo $buildLink($prevPage); ?>">Previous</a>
                    </li>
                    <?php
                    if ($start > 1) {
                        echo '<li class="page-item"><a class="page-link" href="/visits' . $buildLink(1) . '">1</a></li>';
                        if ($start > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                        }
                    }
                    for ($i = $start; $i <= $end; $i++) {
                        echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/visits' . $buildLink($i) . '">' . $i . '</a></li>';
                    }
                    if ($end < $totalPages) {
                        if ($end < $totalPages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="/visits' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
                    }
                    ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <?php
                        // Calculate next page: current page + 1 (increment)
                        // $page is already cast to int and validated above
                        // Simply increment current page
                        $nextPage = $page + 1;
                        
                        // Only cap at totalPages if it exceeds (for disabled state)
                        if ($nextPage > $totalPages) {
                            $nextPage = $totalPages;
                        }
                        
                        // Ensure it's an integer
                        $nextPage = (int)$nextPage;
                        ?>
                        <a class="page-link" href="/visits<?php echo $buildLink($nextPage); ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="visitDetailModal" tabindex="-1" aria-labelledby="visitDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="visitDetailModalLabel">Detail Kunjungan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <h6 class="text-muted mb-2">Informasi Customer</h6>
                        <dl class="mb-0">
                            <dt class="small text-muted">Nama</dt>
                            <dd class="mb-2" data-detail="customer">-</dd>
                            <dt class="small text-muted">Kode Customer</dt>
                            <dd class="mb-2" data-detail="kode">-</dd>
                            <dt class="small text-muted">Status</dt>
                            <dd class="mb-0" data-detail="status">-</dd>
                        </dl>
                    </div>
                    <div class="col-12 col-md-6">
                        <h6 class="text-muted mb-2">Ringkasan</h6>
                        <dl class="mb-0">
                            <dt class="small text-muted">Check-in</dt>
                            <dd class="mb-2" data-detail="checkin-time">-</dd>
                            <dt class="small text-muted">Check-out</dt>
                            <dd class="mb-2" data-detail="checkout-time">-</dd>
                            <dt class="small text-muted">Jarak dari Kantor</dt>
                            <dd class="mb-0" data-detail="distance">-</dd>
                        </dl>
                    </div>
                </div>
                <hr class="my-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <h6 class="text-muted mb-2">Lokasi Check-in</h6>
                        <p class="mb-0" data-detail="checkin-location">-</p>
                    </div>
                    <div class="col-12 col-md-6">
                        <h6 class="text-muted mb-2">Lokasi Check-out</h6>
                        <p class="mb-0" data-detail="checkout-location">-</p>
                    </div>
                </div>
                <hr class="my-4">
                <div>
                    <h6 class="text-muted mb-2">Catatan</h6>
                    <p class="mb-0" data-detail="notes">Tidak ada catatan.</p>
                </div>
                <hr class="my-4">
                <div>
                    <h6 class="text-muted mb-3">File/Gambar/Foto</h6>
                    <div id="visitFilesContainer" class="row g-2">
                        <div class="col-12 text-center text-muted py-3">
                            <div class="spinner-border spinner-border-sm me-2" role="status" id="filesLoading">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span id="filesLoadingText">Memuat file...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php
$additionalInlineScripts = $additionalInlineScripts ?? [];
$baseUrlJs = json_encode($baseUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$jsCode = <<<'JAVASCRIPT'
(function() {
    const modalEl = document.getElementById('visitDetailModal');
    if (!modalEl) {
        return;
    }
    const modal = new bootstrap.Modal(modalEl);
    const baseUrl = BASE_URL_PLACEHOLDER;

    function formatDateTime(value) {
        if (!value) {
            return '-';
        }
        try {
            const normalized = value.replace(' ', 'T');
            const date = new Date(normalized);
            if (Number.isNaN(date.getTime())) {
                return value;
            }
            return new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).format(date);
        } catch (error) {
            return value;
        }
    }

    function formatCoordinate(lat, lng) {
        if (lat == null || lng == null) {
            return '-';
        }
        const parsedLat = Number(lat);
        const parsedLng = Number(lng);
        if (Number.isNaN(parsedLat) || Number.isNaN(parsedLng)) {
            return '-';
        }
        return 'Lat ' + parsedLat.toFixed(6) + ', Lng ' + parsedLng.toFixed(6);
    }

    function formatDistance(value) {
        if (value == null || value === '') {
            return '-';
        }
        const parsed = Number(value);
        if (Number.isNaN(parsed)) {
            return value;
        }
        return parsed.toFixed(2) + ' km';
    }

    function updateDetail(selector, text, fallback = '-') {
        const el = modalEl.querySelector('[data-detail="' + selector + '"]');
        if (!el) {
            return;
        }
        el.textContent = text && text !== '' ? text : fallback;
    }

    function loadVisitDetail(visitId) {
        // Show loading state
        updateDetail('customer', 'Memuat...', '-');
        updateDetail('kode', 'Memuat...', '-');
        updateDetail('status', 'Memuat...', '-');
        updateDetail('checkin-time', 'Memuat...', '-');
        updateDetail('checkout-time', 'Memuat...', '-');
        updateDetail('distance', 'Memuat...', '-');
        updateDetail('checkin-location', 'Memuat...', '-');
        updateDetail('checkout-location', 'Memuat...', '-');
        updateDetail('notes', 'Memuat...', 'Tidak ada catatan.');

        const url = '/visits/' + encodeURIComponent(visitId) + '/detail';

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(function(response) {
                if (!response.ok) {
                    return response.text().then(function(text) {
                        throw new Error('HTTP error! status: ' + response.status);
                    });
                }
                return response.json();
            })
            .then(function(payload) {
                if (payload.error) {
                    updateDetail('customer', 'Error: ' + payload.error, '-');
                    return;
                }

                // Update all detail fields
                updateDetail('customer', payload.namacustomer, '-');
                updateDetail('kode', payload.kodecustomer, '-');
                updateDetail('status', payload.status_kunjungan, '-');
                updateDetail('checkin-time', formatDateTime(payload.check_in_time));
                updateDetail('checkout-time', formatDateTime(payload.check_out_time));
                updateDetail('distance', formatDistance(payload.jarak_dari_kantor));
                updateDetail('checkin-location', formatCoordinate(payload.check_in_lat, payload.check_in_long));
                updateDetail('checkout-location', formatCoordinate(payload.check_out_lat, payload.check_out_long));
                updateDetail('notes', payload.catatan || 'Tidak ada catatan.');

                // Load files
                if (payload.visit_id) {
                    loadVisitFiles(payload.visit_id);
                }
            })
            .catch(function(error) {
                console.error('Error loading visit detail:', error);
                updateDetail('customer', 'Error memuat data', '-');
                updateDetail('kode', 'Error memuat data', '-');
                updateDetail('status', 'Error memuat data', '-');
            });
    }

    document.querySelectorAll('.btn-visit-detail').forEach(function(button) {
        button.addEventListener('click', function() {
            // Get visit_id from data attribute or try to parse from data-visit
            let visitId = this.getAttribute('data-visit-id');
            
            // If not found, try to parse from data-visit JSON
            if (!visitId) {
                const payloadRaw = this.getAttribute('data-visit');
                if (payloadRaw) {
                    try {
                        const payload = JSON.parse(payloadRaw);
                        visitId = payload.visit_id;
                    } catch (error) {
                        console.error('Gagal mengurai data kunjungan', error);
                    }
                }
            }

            if (!visitId) {
                console.error('visit_id tidak ditemukan');
                return;
            }

            // Load detail from API
            loadVisitDetail(visitId);
            modal.show();
        });
    });

    function loadVisitFiles(visitId) {
        const container = document.getElementById('visitFilesContainer');
        const loadingEl = document.getElementById('filesLoading');
        const loadingText = document.getElementById('filesLoadingText');
        
        if (!container) return;

        // Show loading
        container.innerHTML = '<div class="col-12 text-center text-muted py-3">' +
            '<div class="spinner-border spinner-border-sm me-2" role="status">' +
            '<span class="visually-hidden">Loading...</span>' +
            '</div>' +
            '<span>Memuat file...</span>' +
            '</div>';

        fetch('/visits/' + visitId + '/files')
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(function(data) {
                if (data.error) {
                    container.innerHTML = '<div class="col-12 text-center text-muted py-3">' + data.error + '</div>';
                    return;
                }

                const files = data.files || [];
                
                if (files.length === 0) {
                    container.innerHTML = '<div class="col-12 text-center text-muted py-3">Tidak ada file yang diupload</div>';
                    return;
                }

                let fileBaseUrl = (typeof baseUrl === 'string') ? baseUrl : '';
                if (!fileBaseUrl || fileBaseUrl === '/' || fileBaseUrl === 'http://' || fileBaseUrl === 'https://') {
                    fileBaseUrl = '';
                }
                // Normalize baseUrl to avoid double slashes
                fileBaseUrl = fileBaseUrl.replace(/\/$/, '');
                
                // Ensure filename is valid
                function sanitizeFilename(filename) {
                    return (filename || '').toString().trim();
                }
                
                const errorImageSrc = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22150%22 height=%22150%22%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EGambar tidak ditemukan%3C/text%3E%3C/svg%3E';
                
                // Build HTML using DOM methods to avoid string concatenation issues
                const fragment = document.createDocumentFragment();
                const row = document.createElement('div');
                row.className = 'row g-2';
                
                files.forEach(function(file) {
                    if (!file || !file.filename) {
                        return;
                    }
                    
                    const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes((file.file_type || '').toLowerCase());
                    const filename = sanitizeFilename(file.filename);
                    const fileUrl = (fileBaseUrl ? fileBaseUrl + '/' : '') + 'uploads/' + filename;
                    const fileSizeKB = file.file_size ? (file.file_size / 1024).toFixed(2) : '0';
                    const originalFilename = file.original_filename || '';
                    
                    const col = document.createElement('div');
                    col.className = 'col-6 col-md-4 col-lg-3';
                    
                    const card = document.createElement('div');
                    card.className = 'card border';
                    
                    if (isImage) {
                        const link = document.createElement('a');
                        link.href = fileUrl;
                        link.target = '_blank';
                        link.className = 'text-decoration-none';
                        
                        const img = document.createElement('img');
                        img.src = fileUrl;
                        img.className = 'card-img-top visit-file-image';
                        img.alt = originalFilename;
                        img.style.cssText = 'height: 150px; object-fit: cover; cursor: pointer;';
                        img.setAttribute('data-error-src', errorImageSrc);
                        img.onerror = function() {
                            this.onerror = null;
                            this.src = errorImageSrc;
                        };
                        
                        link.appendChild(img);
                        card.appendChild(link);
                        
                        const cardBody = document.createElement('div');
                        cardBody.className = 'card-body p-2';
                        
                        const titleDiv = document.createElement('div');
                        titleDiv.className = 'small text-truncate';
                        titleDiv.title = originalFilename;
                        titleDiv.textContent = originalFilename;
                        
                        const sizeDiv = document.createElement('div');
                        sizeDiv.className = 'small text-muted';
                        sizeDiv.textContent = fileSizeKB + ' KB';
                        
                        const downloadLink = document.createElement('a');
                        downloadLink.href = fileUrl;
                        downloadLink.download = originalFilename;
                        downloadLink.className = 'btn btn-sm btn-outline-primary w-100 mt-1';
                        const small = document.createElement('small');
                        small.textContent = 'Download';
                        downloadLink.appendChild(small);
                        
                        cardBody.appendChild(titleDiv);
                        cardBody.appendChild(sizeDiv);
                        cardBody.appendChild(downloadLink);
                        card.appendChild(cardBody);
                    } else {
                        const cardBody = document.createElement('div');
                        cardBody.className = 'card-body text-center p-3';
                        
                        const iconDiv = document.createElement('div');
                        iconDiv.className = 'mb-2';
                        iconDiv.style.fontSize = '3rem';
                        iconDiv.textContent = '📄';
                        
                        const titleDiv = document.createElement('div');
                        titleDiv.className = 'small text-truncate';
                        titleDiv.title = originalFilename;
                        titleDiv.textContent = originalFilename;
                        
                        const sizeDiv = document.createElement('div');
                        sizeDiv.className = 'small text-muted';
                        sizeDiv.textContent = fileSizeKB + ' KB';
                        
                        const downloadLink = document.createElement('a');
                        downloadLink.href = fileUrl;
                        downloadLink.download = originalFilename;
                        downloadLink.className = 'btn btn-sm btn-outline-primary w-100 mt-2';
                        const small = document.createElement('small');
                        small.textContent = 'Download';
                        downloadLink.appendChild(small);
                        
                        cardBody.appendChild(iconDiv);
                        cardBody.appendChild(titleDiv);
                        cardBody.appendChild(sizeDiv);
                        cardBody.appendChild(downloadLink);
                        card.appendChild(cardBody);
                    }
                    
                    col.appendChild(card);
                    row.appendChild(col);
                });
                
                fragment.appendChild(row);
                container.innerHTML = '';
                container.appendChild(fragment);
            })
            .catch(function(error) {
                console.error('Error loading files:', error);
                container.innerHTML = '<div class="col-12 text-center text-danger py-3">Gagal memuat file: ' + error.message + '</div>';
            });
    }
})();
JAVASCRIPT;
$jsCode = str_replace('BASE_URL_PLACEHOLDER', $baseUrlJs, $jsCode);
$additionalInlineScripts[] = $jsCode;

// JavaScript untuk toggle custom date range
$customDateRangeJs = <<<'JAVASCRIPT'
function handleDateFilterChange(triggerSubmit = false) {
    const filter = document.getElementById('periodeSelect').value;
    const startWrapper = document.getElementById('startDateWrapper');
    const endWrapper = document.getElementById('endDateWrapper');
    const isCustom = filter === 'custom';
    
    if (startWrapper && endWrapper) {
        startWrapper.style.display = isCustom ? 'block' : 'none';
        endWrapper.style.display = isCustom ? 'block' : 'none';
    }
    
    if (!isCustom && triggerSubmit) {
        const startInput = document.querySelector('input[name="start_date"]');
        const endInput = document.querySelector('input[name="end_date"]');
        if (startInput) startInput.value = '';
        if (endInput) endInput.value = '';
        const form = document.getElementById('filterForm');
        if (form) form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    handleDateFilterChange(false);
});
JAVASCRIPT;
$additionalInlineScripts[] = $customDateRangeJs;
?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


<?php
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
// Fallback to relative path if base_url is not set correctly
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}
// Define BASE_URL constant for compatibility
define('BASE_URL', $baseUrl);

// Helper function to combine baseUrl with path (avoid double slashes)
if (!function_exists('url')) {
    function url($path) {
        $base = rtrim(BASE_URL, '/');
        $path = ltrim($path, '/');
        return $base . '/' . $path;
    }
}

// Helper function to display icon
if (!function_exists('icon')) {
    function icon($name, $class = '', $size = 16) {
        $config = require __DIR__ . '/../../config/app.php';
        $baseUrl = rtrim($config['base_url'], '/');
        if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
            $baseUrl = '/';
        }
        $iconPath = $baseUrl . '/assets/icons/' . $name . '.svg';
        $classes = trim('icon-inline ' . $class);
        $classAttr = ' class="' . htmlspecialchars($classes) . '"';
        return '<img src="' . htmlspecialchars($iconPath) . '" alt="' . htmlspecialchars($name) . '" width="' . $size . '" height="' . $size . '"' . $classAttr . '>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $title ?? 'Indoprima Online' ?> - Indoprima Online</title>
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($baseUrl) ?>/assets/images/logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($baseUrl) ?>/assets/images/logo-32.png">
    <link rel="icon" type="image/png" sizes="64x64" href="<?= htmlspecialchars($baseUrl) ?>/assets/images/logo-64.png">
    <link rel="apple-touch-icon" sizes="128x128" href="<?= htmlspecialchars($baseUrl) ?>/assets/images/logo-128.png">
    
    <?php
    // Cache busting - use file modification time as version
    $cssVersion = file_exists(__DIR__ . '/../../assets/css/style.css') ? filemtime(__DIR__ . '/../../assets/css/style.css') : time();
    $bootstrapVersion = file_exists(__DIR__ . '/../../assets/css/bootstrap.min.css') ? filemtime(__DIR__ . '/../../assets/css/bootstrap.min.css') : time();
    ?>
    <link href="<?= htmlspecialchars($baseUrl) ?>/assets/css/bootstrap.min.css?v=<?= $bootstrapVersion ?>" rel="stylesheet" type="text/css">
    <link href="<?= htmlspecialchars($baseUrl) ?>/assets/css/style.css?v=<?= $cssVersion ?>" rel="stylesheet" type="text/css">
    
    <?php
    // Load download alerts CSS on pages with file downloads
    $currentPath = $_SERVER['REQUEST_URI'] ?? '';
    $downloadPages = ['/messages/', '/orders/', '/visits/'];
    $needsDownloadCSS = false;
    
    foreach ($downloadPages as $page) {
        if (strpos($currentPath, $page) !== false) {
            $needsDownloadCSS = true;
            break;
        }
    }
    
    if ($needsDownloadCSS) {
        $downloadCSSVersion = file_exists(__DIR__ . '/../../assets/css/download-alerts.css') ? filemtime(__DIR__ . '/../../assets/css/download-alerts.css') : time();
        echo '<link href="' . htmlspecialchars($baseUrl) . '/assets/css/download-alerts.css?v=' . $downloadCSSVersion . '" rel="stylesheet" type="text/css">';
    }
    ?>
    <?php if (!empty($additionalStyles)):
        $styles = is_array($additionalStyles) ? $additionalStyles : [$additionalStyles];
        foreach ($styles as $styleHref):
            if (!empty($styleHref)):
    ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($styleHref) ?>">
    <?php
            endif;
        endforeach;
    endif;
    ?>
    <?php if (!empty($additionalInlineStyles)):
        $inlineStyles = is_array($additionalInlineStyles) ? $additionalInlineStyles : [$additionalInlineStyles];
        foreach ($inlineStyles as $inlineStyle):
            if (!empty($inlineStyle)):
    ?>
    <style><?= $inlineStyle ?></style>
    <?php
            endif;
        endforeach;
    endif;
    ?>
</head>
<body class="<?= Auth::check() ? 'has-header' : '' ?>"><?php
// Get user data if logged in
$currentUser = Auth::check() ? Auth::user() : null;
$appConfig = require __DIR__ . '/../../config/app.php';

// Get system settings for menu visibility
$showOrderMenu = true;
$showInkasoMenu = true;
if (Auth::check()) {
    try {
        $settingModel = new Setting();
        $setting = $settingModel->getMainSetting();
        if ($setting) {
            $showOrderMenu = ($setting['order_online'] === 'aktif');
            $showInkasoMenu = ($setting['inkaso_online'] === 'aktif');
        }
    } catch (Exception $e) {
        // Default to true if error
        error_log("Error loading settings: " . $e->getMessage());
    }
}

// Check if current page is /mastercustomer/map to hide header
$isMapPage = strpos($_SERVER['REQUEST_URI'] ?? '', '/mastercustomer/map') !== false;
if (Auth::check() && $currentUser && !$isMapPage): ?><header class="app-header">
        <nav class="navbar">
            <div class="container-fluid">
                <div class="header-content">
                    <!-- Logo Section -->
                    <div class="header-logo-section">
                        <a href="/dashboard" class="d-flex align-items-center text-decoration-none">
                            <img src="<?= htmlspecialchars($baseUrl) ?>/assets/images/logo.png" alt="Logo" class="header-logo">
                        </a>
                        <h1 class="header-app-name"><?= htmlspecialchars($appConfig['app_name']) ?></h1>
                    </div>

                    <!-- Hamburger Menu Button (Mobile Only) -->
                    <button class="hamburger-menu-toggle" type="button" id="hamburgerMenuToggle" aria-label="Toggle menu" aria-expanded="false">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>

                    <!-- Navigation Menu -->
                    <nav class="header-nav-menu" id="headerNavMenu">
                        <a href="/dashboard" class="nav-link fw-bold <?= ($_SERVER['REQUEST_URI'] ?? '') === '/dashboard' ? 'active' : '' ?>">Dashboard</a>
                        
                        <?php if (Auth::check() && Auth::isSales()): ?>
                        <!-- Menu untuk Role Sales -->
                        <a href="/mastercustomer" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/mastercustomer') !== false ? 'active' : '' ?>">Data Customer</a>
                        <a href="/visits" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/visits') !== false ? 'active' : '' ?>">Kunjungan</a>
                        <?php if ($showOrderMenu || $showOrderMenu): ?>
                            <div class="nav-dropdown">
                                <button class="nav-dropdown-toggle" type="button" aria-expanded="false">
                                    Transaksi
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-left: 0.25rem;">
                                        <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <div class="nav-dropdown-menu">
                                    <?php if ($showOrderMenu): ?>
                                    <a href="/orders" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/orders') !== false ? 'active' : '' ?>">Order Penjualan</a>
                                    <?php endif; ?>
                                    <?php if ($showInkasoMenu): ?>
                                    <a href="/penerimaan" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/penerimaan') !== false ? 'active' : '' ?>">Inkaso</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="nav-dropdown">
                            <button class="nav-dropdown-toggle" type="button" aria-expanded="false">
                                Laporan
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-left: 0.25rem;">
                                    <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div class="nav-dropdown-menu">
                                <!-- <a href="/laporan/daftar-barang" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/daftar-barang') !== false ? 'active' : '' ?>">Daftar Barang</a> -->
                                <a href="/laporan/daftar-stok" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/daftar-stok') !== false ? 'active' : '' ?>">Daftar Stok</a>
                                <a href="/laporan/daftar-tagihan" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/daftar-tagihan') !== false ? 'active' : '' ?>">Daftar Tagihan</a>
                                <a href="/penjualan" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/daftar-harga') !== false ? 'active' : '' ?>">Daftar Faktur Penjualan</a>
                                <a href="/laporan/omset" class="nav-dropdown-item">Omset Penjualan</a>
                                <a href="/laporan/distribusi-penjualan" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/distribusi-penjualan') !== false ? 'active' : '' ?>">Distribusi Penjualan</a>
                                <a href="/laporan/barang-tidak-terjual" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/barang-tidak-terjual') !== false ? 'active' : '' ?>">Barang Tidak Terjual</a>
                                <a href="/laporan/customer-non-aktif" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/customer-non-aktif') !== false ? 'active' : '' ?>">Customer Non Aktif</a>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Menu untuk Role Admin, Manajemen, Operator -->
                        <!-- Menu Setting untuk Role Admin -->
                        <?php if (Auth::isAdmin()): ?>
                        <a href="/setting" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/setting') !== false ? 'active' : '' ?>">Setting</a>
                        <?php endif; ?>

                        <?php if (Auth::isManajemen()): ?>
                        <div class="nav-dropdown">
                            <button class="nav-dropdown-toggle" type="button" aria-expanded="false">
                                User
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-left: 0.25rem;">
                                    <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div class="nav-dropdown-menu">
                                <a href="/users" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/users') !== false ? 'active' : '' ?>">Manajemen User</a>
                                <a href="/login-logs" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/login-logs') !== false ? 'active' : '' ?>">Login Logs</a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (Auth::check() && in_array($currentUser['role'] ?? '', ['admin', 'manajemen', 'operator'])): ?>
                        <div class="nav-dropdown">
                            <button class="nav-dropdown-toggle" type="button" aria-expanded="false">
                                Tabel
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-left: 0.25rem;">
                                    <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div class="nav-dropdown-menu">
                                <a href="/tabelpabrik" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tabelpabrik') !== false ? 'active' : '' ?>">Tabel Pabrik</a>
                                <a href="/tabelgolongan" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tabelgolongan') !== false ? 'active' : '' ?>">Tabel Golongan</a>
                                <a href="/tabelaktivitas" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tabelaktivitas') !== false ? 'active' : '' ?>">Tabel Aktivitas</a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (Auth::check() && in_array($currentUser['role'] ?? '', ['admin', 'manajemen', 'operator'])): ?>
                        <div class="nav-dropdown">
                            <button class="nav-dropdown-toggle" type="button" aria-expanded="false">
                                Master
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-left: 0.25rem;">
                                    <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div class="nav-dropdown-menu">
                                <a href="/masterbarang" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/masterbarang') !== false ? 'active' : '' ?>">Master Barang</a>
                                <a href="/mastercustomer" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/mastercustomer') !== false ? 'active' : '' ?>">Master Customer</a>
                                <a href="/mastersales" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/mastersales') !== false ? 'active' : '' ?>">Master Sales</a>
                                <a href="/mastersupplier" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/mastersupplier') !== false ? 'active' : '' ?>">Master Supplier</a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (Auth::check() && in_array($currentUser['role'] ?? '', ['admin', 'manajemen', 'operator'])): ?>
                            <a href="/visits" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/visits') !== false ? 'active' : '' ?>">Kunjungan</a>
                        <?php endif; ?>

                        <?php if (Auth::check() && in_array($currentUser['role'] ?? '', ['admin', 'manajemen', 'operator'])): ?>
                        <div class="nav-dropdown">
                            <button class="nav-dropdown-toggle" type="button" aria-expanded="false">
                                Transaksi
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-left: 0.25rem;">
                                    <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div class="nav-dropdown-menu">
                                <?php if ($showOrderMenu): ?>
                                <a href="/orders" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/orders') !== false ? 'active' : '' ?>">Order Penjualan</a>
                                <?php endif; ?>
                                <a href="/penjualan" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/penjualan') !== false ? 'active' : '' ?>">Penjualan</a>
                                <?php if ($showInkasoMenu): ?>
                                <a href="/penerimaan" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/penerimaan') !== false ? 'active' : '' ?>">Inkaso</a>
                                <?php endif; ?>
                                <a href="/pembelian" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/pembelian') !== false ? 'active' : '' ?>">Pembelian</a>
                                <a href="/perubahanharga" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/perubahanharga') !== false ? 'active' : '' ?>">Perubahan Harga</a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (Auth::check() && in_array($currentUser['role'] ?? '', ['admin', 'manajemen', 'operator'])): ?>
                        <div class="nav-dropdown">
                            <button class="nav-dropdown-toggle" type="button" aria-expanded="false">
                                Laporan
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-left: 0.25rem;">
                                    <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div class="nav-dropdown-menu">
                                <a href="/laporan/daftar-barang" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/daftar-barang') !== false ? 'active' : '' ?>">Daftar Barang</a>
                                <a href="/laporan/daftar-stok" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/daftar-stok') !== false ? 'active' : '' ?>">Daftar Stok</a>
                                <a href="/laporan/daftar-harga" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/daftar-harga') !== false ? 'active' : '' ?>">Daftar Harga Barang</a>
                                <a href="/laporan/daftar-tagihan" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/daftar-tagihan') !== false ? 'active' : '' ?>">Daftar Tagihan</a>
                                <a href="/laporan/omset" class="nav-dropdown-item">Omset Penjualan</a>
                                <a href="/laporan/distribusi-penjualan" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/distribusi-penjualan') !== false ? 'active' : '' ?>">Distribusi Penjualan</a>
                                <a href="/laporan/barang-tidak-terjual" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/barang-tidak-terjual') !== false ? 'active' : '' ?>">Barang Tidak Terjual</a>
                                <a href="/laporan/customer-non-aktif" class="nav-dropdown-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/laporan/customer-non-aktif') !== false ? 'active' : '' ?>">Customer Non Aktif</a>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>                        
                    </nav>

                    <!-- User Profile Section -->
                    <div class="header-user-profile">
                        <?php
                        // Get unread message count for bell icon
                        $unreadCount = 0;
                        if (Auth::check()) {
                            try {
                                // Use MessageModel to avoid conflict with core Message class
                                require_once __DIR__ . '/../../models/MessageModel.php';
                                $messageModel = new MessageModel();
                                $unreadCount = $messageModel->getUnreadCount($currentUser['id']);
                            } catch (Exception $e) {
                                // Silently fail if MessageModel not available
                            }
                        }
                        ?>
                        <!-- Messages Bell Icon with Dropdown -->
                        <div class="header-messages-dropdown" id="headerMessagesDropdown">
                            <button class="header-messages-icon" type="button" id="headerMessagesToggle" title="Pesan Masuk" aria-expanded="false">
                                <?= icon('bell-light', '', 20) ?>
                                <?php if ($unreadCount > 0): ?>
                                    <span class="badge bg-danger messages-badge"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="messages-dropdown-menu" id="messagesDropdownMenu">
                                <div class="messages-dropdown-header">
                                    <h6 class="mb-0">Pesan Masuk</h6>
                                    <a href="/messages" class="text-decoration-none small">Lihat Semua</a>
                                </div>
                                <div class="messages-dropdown-body">
                                    <?php
                                    $unreadMessages = [];
                                    if ($unreadCount > 0) {
                                        try {
                                            $unreadMessages = $messageModel->getUnreadMessages($currentUser['id'], 10);
                                        } catch (Exception $e) {
                                            // Silently fail
                                        }
                                    }
                                    
                                    if (empty($unreadMessages)):
                                    ?>
                                        <div class="messages-empty text-center py-3 text-muted">
                                            <small>Tidak ada pesan baru</small>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($unreadMessages as $msg): 
                                            $timeAgo = '';
                                            $createdAt = strtotime($msg['created_at']);
                                            $now = time();
                                            $diff = $now - $createdAt;
                                            
                                            if ($diff < 60) {
                                                $timeAgo = 'Baru saja';
                                            } elseif ($diff < 3600) {
                                                $timeAgo = floor($diff / 60) . ' menit lalu';
                                            } elseif ($diff < 86400) {
                                                $timeAgo = floor($diff / 3600) . ' jam lalu';
                                            } elseif ($diff < 604800) {
                                                $timeAgo = floor($diff / 86400) . ' hari lalu';
                                            } else {
                                                $timeAgo = date('d M Y', $createdAt);
                                            }
                                            
                                            $subject = htmlspecialchars($msg['subject'] ?? 'Tidak ada subjek');
                                            $senderName = htmlspecialchars($msg['sender_name'] ?? 'Unknown');
                                            $contentPreview = strip_tags($msg['content'] ?? '');
                                            $contentPreview = mb_substr($contentPreview, 0, 50);
                                            if (mb_strlen($msg['content'] ?? '') > 50) {
                                                $contentPreview .= '...';
                                            }
                                        ?>
                                            <a href="/messages/show/<?= $msg['id'] ?>" class="message-item">
                                                <div class="message-item-header">
                                                    <span class="message-sender"><?= $senderName ?></span>
                                                    <span class="message-time"><?= $timeAgo ?></span>
                                                </div>
                                                <div class="message-subject"><?= $subject ?></div>
                                                <div class="message-preview text-muted small"><?= htmlspecialchars($contentPreview) ?></div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="user-profile-dropdown" id="userProfileDropdown">
                            <button class="user-profile-toggle" type="button" id="userProfileToggle" aria-expanded="false">
                                <div class="user-avatar">
                                    <?php if (!empty($currentUser['picture'])): ?>
                                        <?php 
                                        $config = require __DIR__ . '/../../config/app.php';
                                        $uploadUrl = $config['upload_url'] ?? '/uploads/';
                                        $pictureUrl = url($uploadUrl . htmlspecialchars($currentUser['picture']));
                                        $fallbackText = strtoupper(substr($currentUser['username'] ?? 'U', 0, 1));
                                        ?>
                                        <img src="<?= $pictureUrl ?>" alt="<?= htmlspecialchars($currentUser['namalengkap'] ?? $currentUser['username'] ?? 'User') ?>" class="user-avatar-img" data-fallback="<?= htmlspecialchars($fallbackText) ?>" onerror="this.style.display='none'; if(!this.parentElement.querySelector('.avatar-fallback')) { var span = document.createElement('span'); span.className='avatar-fallback'; span.textContent=this.getAttribute('data-fallback'); this.parentElement.appendChild(span); }">
                                    <?php else: ?>
                                        <span class="avatar-fallback"><?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="user-name"><?= htmlspecialchars($currentUser['namalengkap'] ?? $currentUser['username'] ?? 'User') ?></span>
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-left: 0.25rem;">
                                    <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <div class="user-dropdown-menu">
                                <div class="dropdown-header mb-2">
                                    <p class="dropdown-user-name"><?= htmlspecialchars($currentUser['namalengkap'] ?? $currentUser['username'] ?? 'User') ?></p>
                                    <?php if (!empty($currentUser['email'])): ?>
                                        <p class="dropdown-user-email"><?= htmlspecialchars($currentUser['email']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <a href="/messages" class="dropdown-item">
                                    <?= icon('envelope', 'me-2', 16) ?> Pesan
                                    <?php if ($unreadCount > 0): ?>
                                        <span class="badge bg-danger ms-auto"><?= $unreadCount ?></span>
                                    <?php endif; ?>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="/profile" class="dropdown-item"><?= icon('user-gear', 'me-2', 16) ?> Edit Profil</a>
                                <a href="/profile/change-password" class="dropdown-item"><?= icon('key', 'me-2', 16) ?> Ubah Password</a>
                                <a href="/settings" class="dropdown-item"><?= icon('gear', 'me-2', 16) ?> Setting</a>
                                <div class="dropdown-divider"></div>
                                <a href="/logout" class="dropdown-item danger">
                                    <?= icon('logout', 'me-2', 16) ?> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <script>
    // Toggle messages dropdown
    document.addEventListener('DOMContentLoaded', function() {
        const messagesDropdown = document.getElementById('headerMessagesDropdown');
        const messagesToggle = document.getElementById('headerMessagesToggle');
        
        if (messagesToggle) {
            messagesToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                messagesDropdown.classList.toggle('show');
                // Close profile dropdown if open
                const profileDropdown = document.getElementById('userProfileDropdown');
                if (profileDropdown) {
                    profileDropdown.classList.remove('show');
                }
            });
        }
        
        // Toggle user profile dropdown
        const dropdown = document.getElementById('userProfileDropdown');
        const toggle = document.getElementById('userProfileToggle');
        
        if (toggle) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('show');
                // Close messages dropdown if open
                if (messagesDropdown) {
                    messagesDropdown.classList.remove('show');
                }
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (dropdown && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
            if (messagesDropdown && !messagesDropdown.contains(e.target)) {
                messagesDropdown.classList.remove('show');
            }
        });

        // Hamburger menu toggle
        const hamburgerToggle = document.getElementById('hamburgerMenuToggle');
        const navMenu = document.getElementById('headerNavMenu');
        
        if (hamburgerToggle && navMenu) {
            hamburgerToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const isExpanded = hamburgerToggle.getAttribute('aria-expanded') === 'true';
                hamburgerToggle.setAttribute('aria-expanded', !isExpanded);
                hamburgerToggle.classList.toggle('active');
                navMenu.classList.toggle('show');
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!hamburgerToggle.contains(e.target) && !navMenu.contains(e.target)) {
                    hamburgerToggle.setAttribute('aria-expanded', 'false');
                    hamburgerToggle.classList.remove('active');
                    navMenu.classList.remove('show');
                }
            });

            // Close mobile menu when clicking on a nav link
            const navLinks = navMenu.querySelectorAll('.nav-link, .nav-dropdown-item');
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    hamburgerToggle.setAttribute('aria-expanded', 'false');
                    hamburgerToggle.classList.remove('active');
                    navMenu.classList.remove('show');
                });
            });
        }

        // Handle dropdown menus
        const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');
        dropdownToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = this.closest('.nav-dropdown');
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                
                // Close all other dropdowns
                dropdownToggles.forEach(function(otherToggle) {
                    if (otherToggle !== toggle) {
                        otherToggle.setAttribute('aria-expanded', 'false');
                        otherToggle.closest('.nav-dropdown').classList.remove('show');
                    }
                });
                
                // Toggle current dropdown
                this.setAttribute('aria-expanded', !isExpanded);
                dropdown.classList.toggle('show', !isExpanded);
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.nav-dropdown')) {
                dropdownToggles.forEach(function(toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.closest('.nav-dropdown').classList.remove('show');
                });
            }
        });

        // Inject mobile back buttons into card headers on small screens
        function setupMobileBackButtons() {
            var isSmall = window.matchMedia('(max-width: 575.98px)').matches;
            if (!isSmall) return;

            var headers = document.querySelectorAll('.card .card-header');
            headers.forEach(function(header) {
                if (header.querySelector('.mobile-back-btn')) return;

                var container = header.querySelector('.d-flex') || header;
                var title = header.querySelector('h4, h3, h2, .card-title');
                if (!title) return;

                // Resolve base URL from PHP for asset path
                var baseUrl = <?= json_encode($baseUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'mobile-back-btn';
                btn.setAttribute('aria-label', 'Kembali');
                btn.innerHTML = '<img src="' + baseUrl + '/assets/icons/arrow-left.svg" alt="Kembali" width="20" height="20" class="icon-inline">';
                btn.addEventListener('click', function() {
                    // Priority 1: Check for custom back URL from card header data attribute
                    var customBackUrl = header.getAttribute('data-back-url');
                    if (customBackUrl) {
                        window.location.href = customBackUrl;
                        return;
                    }
                    
                    // Priority 2: Check for breadcrumb parent URL
                    var breadcrumbNav = document.querySelector('nav[aria-label="breadcrumb"][data-breadcrumb-parent]');
                    if (breadcrumbNav) {
                        var breadcrumbParent = breadcrumbNav.getAttribute('data-breadcrumb-parent');
                        if (breadcrumbParent) {
                            window.location.href = breadcrumbParent;
                            return;
                        }
                    }
                    
                    // Priority 3: Try to get parent from breadcrumb links (second to last link)
                    var breadcrumbLinks = document.querySelectorAll('nav[aria-label="breadcrumb"] .breadcrumb-item:not(.active) a');
                    if (breadcrumbLinks.length > 0) {
                        // Get the last non-active breadcrumb link (parent page)
                        var parentLink = breadcrumbLinks[breadcrumbLinks.length - 1];
                        if (parentLink && parentLink.href) {
                            window.location.href = parentLink.href;
                            return;
                        }
                    }
                    
                    // Fallback to history.back() or dashboard
                    if (document.referrer && document.referrer !== window.location.href) {
                        history.back();
                    } else {
                        window.location.href = "/dashboard";
                    }
                });

                container.insertBefore(btn, title);
            });
        }

        setupMobileBackButtons();
        window.addEventListener('resize', function() {
            // Re-run to add buttons if layout changes to small
            setupMobileBackButtons();
        });
    });
</script><?php endif; ?>        
<?php require __DIR__ . '/../partials/alerts.php'; ?>
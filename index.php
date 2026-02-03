<?php
// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (production-safe defaults)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Override PHP upload settings if possible (only works if not disabled by server)
// This helps when php.ini cannot be modified
@ini_set('upload_max_filesize', '6M'); // 6M for buffer (app limit is 5MB)
@ini_set('post_max_size', '6M'); // 6M for buffer (app limit is 5MB)
@ini_set('max_execution_time', '300'); // 5 minutes for large uploads
@ini_set('max_input_time', '300');

// Autoload classes
spl_autoload_register(function ($class) {
    // Special handling for Message class to avoid conflict
    // Always load core Message first, never load models/Message.php
    if ($class === 'Message') {
        $corePath = __DIR__ . '/core/' . $class . '.php';
        if (file_exists($corePath) && !class_exists('Message', false)) {
            require $corePath;
        }
        // Never load models/Message.php to avoid conflict
        // Use MessageModel for database operations instead
        return;
    }
    
    $paths = [
        __DIR__ . '/core/' . $class . '.php',
        __DIR__ . '/models/' . $class . '.php',
        __DIR__ . '/controllers/' . $class . '.php',
        __DIR__ . '/controllers/api/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        // Skip models/Message.php to prevent conflict
        if (strpos($path, '/models/Message.php') !== false) {
            continue;
        }
        if (file_exists($path)) {
            require $path;
            return;
        }
    }
});

// Start session
Session::start();

// Initialize router
$router = new Router();

// Root route - handled in Router dispatch

// Auth routes
$router->get('/login', 'AuthController', 'login');
$router->post('/login', 'AuthController', 'login');
$router->get('/logout', 'AuthController', 'logout');

// WebAuthn API routes
$router->post('/api/webauthn/registration/start', 'ApiWebAuthnController', 'registrationStart');
$router->post('/api/webauthn/registration/complete', 'ApiWebAuthnController', 'registrationComplete');
$router->post('/api/webauthn/authentication/start', 'ApiWebAuthnController', 'authenticationStart');
$router->post('/api/webauthn/authentication/complete', 'ApiWebAuthnController', 'authenticationComplete');
$router->get('/api/webauthn/credentials', 'ApiWebAuthnController', 'listCredentials');
$router->post('/api/webauthn/credentials/delete', 'ApiWebAuthnController', 'deleteCredential');

// Download routes with error handling
$router->get('/download/file', 'DownloadController', 'file');
$router->get('/download/check', 'DownloadController', 'check');

// Dashboard routes
$router->get('/dashboard', 'DashboardController', 'index');

// Login Log routes (admin/manajemen only)
$router->get('/login-logs', 'LoginLogController', 'index');

// User management routes (admin/manajemen only)
$router->get('/users', 'UserController', 'index');
$router->get('/users/create', 'UserController', 'create');
$router->post('/users/create', 'UserController', 'create');
$router->get('/users/edit/{id}', 'UserController', 'edit');
$router->post('/users/edit/{id}', 'UserController', 'edit');
$router->get('/users/delete/{id}', 'UserController', 'delete');

// Profile routes
$router->get('/profile', 'ProfileController', 'index');
$router->post('/profile', 'ProfileController', 'update');
$router->get('/profile/change-password', 'ProfileController', 'changePassword');
$router->post('/profile/change-password', 'ProfileController', 'changePassword');
$router->get('/settings', 'ProfileController', 'settings');

// System Setting routes (admin only)
$router->get('/setting', 'SettingController', 'index');
$router->post('/setting', 'SettingController', 'index');

// Master Barang routes
$router->get('/masterbarang', 'MasterbarangController', 'index');
$router->get('/masterbarang/view/{id}', 'MasterbarangController', 'show');
$router->get('/masterbarang/edit/{id}', 'MasterbarangController', 'edit');
$router->post('/masterbarang/edit/{id}', 'MasterbarangController', 'edit');

// Master Customer routes
$router->get('/mastercustomer', 'MastercustomerController', 'index');
$router->get('/mastercustomer/map', 'MastercustomerController', 'map');
$router->get('/mastercustomer/edit/{id}', 'MastercustomerController', 'edit');
$router->post('/mastercustomer/edit/{id}', 'MastercustomerController', 'edit');
$router->post('/mastercustomer/{id}/coordinates', 'MastercustomerController', 'updateCoordinates');

// Master Supplier routes
$router->get('/mastersupplier', 'MastersupplierController', 'index');

// Master Sales routes
$router->get('/mastersales', 'MastersalesController', 'index');

// Order transaction routes
$router->get('/orders', 'OrderController', 'index');
$router->get('/orders/create', 'OrderController', 'create');
$router->post('/orders/create', 'OrderController', 'create');
$router->get('/orders/view/{noorder}', 'OrderController', 'show');
$router->get('/orders/edit/{noorder}', 'OrderController', 'edit');
$router->post('/orders/edit/{noorder}', 'OrderController', 'edit');
$router->get('/orders/delete/{noorder}', 'OrderController', 'delete');
$router->post('/orders/delete-file', 'OrderController', 'deleteFile');

// Penjualan routes
$router->get('/penjualan', 'PenjualanController', 'index');
$router->get('/penjualan/view/{nopenjualan}', 'PenjualanController', 'show');

// Penerimaan Piutang routes
$router->get('/penerimaan', 'PenerimaanController', 'index');
$router->get('/penerimaan/create', 'PenerimaanController', 'create');
$router->post('/penerimaan/create', 'PenerimaanController', 'create');
$router->get('/penerimaan/view/{nopenerimaan}', 'PenerimaanController', 'show');
$router->get('/penerimaan/edit/{nopenerimaan}', 'PenerimaanController', 'edit');
$router->post('/penerimaan/edit/{nopenerimaan}', 'PenerimaanController', 'edit');
$router->get('/penerimaan/delete/{nopenerimaan}', 'PenerimaanController', 'delete');
$router->get('/penerimaan/get-available-penjualan', 'PenerimaanController', 'getAvailablePenjualan');

// Tabel Aktivitas routes
$router->get('/tabelaktivitas', 'TabelaktivitasController', 'index');
$router->get('/tabelaktivitas/create', 'TabelaktivitasController', 'create');
$router->post('/tabelaktivitas/create', 'TabelaktivitasController', 'create');
$router->get('/tabelaktivitas/edit/{id}', 'TabelaktivitasController', 'edit');
$router->post('/tabelaktivitas/edit/{id}', 'TabelaktivitasController', 'edit');
$router->get('/tabelaktivitas/delete/{id}', 'TabelaktivitasController', 'delete');

// Tabel Golongan routes
$router->get('/tabelgolongan', 'TabelgolonganController', 'index');

// Tabel Pabrik routes
$router->get('/tabelpabrik', 'TabelpabrikController', 'index');

// Visit routes (sales only)
$router->get('/visits', 'VisitController', 'index');
$router->get('/visits/check-in', 'VisitController', 'checkin');
$router->post('/visits/check-in', 'VisitController', 'checkin');
$router->get('/visits/checkout/{id}', 'VisitController', 'checkout');
$router->post('/visits/checkout/{id}', 'VisitController', 'checkout');
$router->get('/visits/nearest-customers', 'VisitController', 'nearestCustomers');
$router->post('/visits/customer/{id}/coordinates', 'VisitController', 'updateCustomerCoordinates');
$router->get('/visits/{id}/detail', 'VisitController', 'getVisitDetail');
$router->get('/visits/{id}/files', 'VisitController', 'getVisitFiles');
$router->post('/visits/{id}/activities', 'VisitController', 'createActivity');

// API Health Check / Ping route (no authentication required - for VB bridging)
$router->get('/api/health', 'ApiHealthController', 'index');
$router->get('/api/ping', 'ApiHealthController', 'index');

// API routes (no authentication required - for VB bridging)
$router->get('/api/users', 'ApiController', 'users');
$router->post('/api/users', 'ApiController', 'users');
$router->put('/api/users', 'ApiController', 'users');
$router->delete('/api/users', 'ApiController', 'users');

// API Mastersales routes (no authentication required)
$router->get('/api/mastersales', 'ApiMastersalesController', 'index');
$router->post('/api/mastersales', 'ApiMastersalesController', 'index');
$router->put('/api/mastersales', 'ApiMastersalesController', 'index');
$router->delete('/api/mastersales', 'ApiMastersalesController', 'index');

// API Tabelpabrik routes (no authentication required)
$router->get('/api/tabelpabrik', 'ApiTabelpabrikController', 'index');
$router->post('/api/tabelpabrik', 'ApiTabelpabrikController', 'index');
$router->put('/api/tabelpabrik', 'ApiTabelpabrikController', 'index');
$router->patch('/api/tabelpabrik', 'ApiTabelpabrikController', 'index');
$router->delete('/api/tabelpabrik', 'ApiTabelpabrikController', 'index');

// API Tabelgolongan routes (no authentication required)
$router->get('/api/tabelgolongan', 'ApiTabelgolonganController', 'index');
$router->post('/api/tabelgolongan', 'ApiTabelgolonganController', 'index');
$router->put('/api/tabelgolongan', 'ApiTabelgolonganController', 'index');
$router->delete('/api/tabelgolongan', 'ApiTabelgolonganController', 'index');

// API Mastersupplier routes (no authentication required)
$router->get('/api/mastersupplier', 'ApiMastersupplierController', 'index');
$router->post('/api/mastersupplier', 'ApiMastersupplierController', 'index');
$router->put('/api/mastersupplier', 'ApiMastersupplierController', 'index');
$router->delete('/api/mastersupplier', 'ApiMastersupplierController', 'index');

// API Masterbarang routes (no authentication required)
$router->get('/api/masterbarang', 'ApiMasterbarangController', 'index');
$router->post('/api/masterbarang', 'ApiMasterbarangController', 'index');
$router->put('/api/masterbarang', 'ApiMasterbarangController', 'index');
$router->patch('/api/masterbarang', 'ApiMasterbarangController', 'index');
$router->delete('/api/masterbarang', 'ApiMasterbarangController', 'index');

// API Penjualan routes
$router->get('/api/penjualan', 'ApiPenjualanController', 'index');
$router->post('/api/penjualan', 'ApiPenjualanController', 'index');
$router->put('/api/penjualan', 'ApiPenjualanController', 'index');
$router->patch('/api/penjualan', 'ApiPenjualanController', 'index');
$router->delete('/api/penjualan', 'ApiPenjualanController', 'index');

// API Penerimaan routes
$router->get('/api/penerimaan', 'ApiPenerimaanController', 'index');
$router->post('/api/penerimaan', 'ApiPenerimaanController', 'index');
$router->put('/api/penerimaan', 'ApiPenerimaanController', 'index');
$router->patch('/api/penerimaan', 'ApiPenerimaanController', 'index');
$router->delete('/api/penerimaan', 'ApiPenerimaanController', 'index');

// API Omset routes (no authentication required - for VB bridging)
$router->get('/api/omset', 'ApiOmsetController', 'omset');
$router->post('/api/omset', 'ApiOmsetController', 'omset');
$router->put('/api/omset', 'ApiOmsetController', 'omset');
$router->delete('/api/omset', 'ApiOmsetController', 'omset');

// API Mastercustomer routes (no authentication required)
$router->get('/api/mastercustomer', 'ApiMastercustomerController', 'index');
$router->post('/api/mastercustomer', 'ApiMastercustomerController', 'index');
$router->put('/api/mastercustomer', 'ApiMastercustomerController', 'index');
$router->delete('/api/mastercustomer', 'ApiMastercustomerController', 'index');

// API Headerorder routes (for bridging)
$router->get('/api/headerorder', 'ApiHeaderorderController', 'index');
$router->post('/api/headerorder', 'ApiHeaderorderController', 'index');
$router->put('/api/headerorder', 'ApiHeaderorderController', 'index');
$router->patch('/api/headerorder', 'ApiHeaderorderController', 'index');

// Pembelian Barang routes
$router->get('/pembelian', 'PembelianController', 'index');
$router->get('/pembelian/create', 'PembelianController', 'create');
$router->post('/pembelian/create', 'PembelianController', 'create');
$router->get('/pembelian/view/{id}', 'PembelianController', 'show');
$router->get('/pembelian/edit/{id}', 'PembelianController', 'edit');
$router->post('/pembelian/edit/{id}', 'PembelianController', 'edit');
$router->get('/pembelian/delete/{id}', 'PembelianController', 'delete');

// API Pembelianbarang routes (no authentication required - for VB bridging)
$router->get('/api/pembelianbarang', 'ApiPembelianbarangController', 'index');
$router->post('/api/pembelianbarang', 'ApiPembelianbarangController', 'index');
$router->put('/api/pembelianbarang', 'ApiPembelianbarangController', 'index');
$router->patch('/api/pembelianbarang', 'ApiPembelianbarangController', 'index');
$router->delete('/api/pembelianbarang', 'ApiPembelianbarangController', 'index');

// Perubahan Harga routes
$router->get('/perubahanharga', 'PerubahanhargaController', 'index');
$router->get('/perubahanharga/create', 'PerubahanhargaController', 'create');
$router->post('/perubahanharga/create', 'PerubahanhargaController', 'create');
$router->get('/perubahanharga/view/{id}', 'PerubahanhargaController', 'show');
$router->get('/perubahanharga/edit/{id}', 'PerubahanhargaController', 'edit');
$router->post('/perubahanharga/edit/{id}', 'PerubahanhargaController', 'edit');
$router->get('/perubahanharga/delete/{id}', 'PerubahanhargaController', 'delete');

// API Perubahanharga routes (no authentication required - for VB bridging)
$router->get('/api/perubahanharga', 'ApiPerubahanhargaController', 'index');
$router->post('/api/perubahanharga', 'ApiPerubahanhargaController', 'index');
$router->put('/api/perubahanharga', 'ApiPerubahanhargaController', 'index');
$router->patch('/api/perubahanharga', 'ApiPerubahanhargaController', 'index');
$router->delete('/api/perubahanharga', 'ApiPerubahanhargaController', 'index');

// Laporan routes
$router->get('/laporan/daftar-barang', 'LaporanController', 'daftarBarang');
$router->get('/laporan/daftar-stok', 'LaporanController', 'daftarStok');
$router->get('/laporan/daftar-harga', 'LaporanController', 'daftarHarga');
$router->get('/laporan/daftar-tagihan', 'LaporanController', 'daftarTagihan');
$router->get('/laporan/distribusi-penjualan', 'LaporanController', 'distribusiPenjualan');
$router->get('/laporan/barang-tidak-terjual', 'LaporanController', 'barangTidakTerjual');
$router->get('/laporan/customer-non-aktif', 'LaporanController', 'customerNonAktif');
$router->get('/laporan/omset', 'OmsetController', 'index');
$router->get('/laporan/omset-harian', 'OmsetHarianController', 'index');

// API Omset Harian routes (no authentication required - for VB bridging)
$router->get('/api/omset-harian', 'ApiOmsetHarianController', 'index');
$router->post('/api/omset-harian', 'ApiOmsetHarianController', 'index');
$router->put('/api/omset-harian', 'ApiOmsetHarianController', 'index');
$router->delete('/api/omset-harian', 'ApiOmsetHarianController', 'index');

// Message routes - specific routes first, then generic ones
$router->get('/messages/show/{id}', 'MessageController', 'show');
$router->get('/messages/delete/{id}', 'MessageController', 'delete');
$router->get('/messages/sent', 'MessageController', 'sent');
$router->get('/messages/create', 'MessageController', 'create');
$router->get('/messages/search', 'MessageController', 'search');
$router->get('/messages/searchUsers', 'MessageController', 'searchUsers');
$router->get('/messages/getUnreadCount', 'MessageController', 'getUnreadCount');
$router->get('/messages/markAllAsRead', 'MessageController', 'markAllAsRead');
$router->get('/messages/markAsRead', 'MessageController', 'markAsRead');
$router->post('/messages/store', 'MessageController', 'store');
$router->get('/messages', 'MessageController', 'index');

// Dispatch
$router->dispatch();


<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definisikan BASE_URL jika belum ada
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/config.php';
}

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman admin!';
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Tambahkan fungsi untuk cek akses customer
function isCustomer() {
    return isset($_SESSION['user_id']) && !isset($_SESSION['is_admin']);
}

// Perbaikan logika redirect
$currentPath = $_SERVER['PHP_SELF'];
if (strpos($currentPath, '/admin/') !== false && !isAdmin()) {
    $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman admin!';
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Admin navigation
$adminNav = [
    [
        'title' => 'Dashboard',
        'url' => '/admin/dashboard.php',
        'icon' => 'ni ni-tv-2'
    ],
    [
        'title' => 'Produk',
        'url' => '/admin/products',
        'icon' => 'ni ni-box-2'
    ],
    [
        'title' => 'Kategori',
        'url' => '/admin/categories',
        'icon' => 'ni ni-box-2'
    ],
    [
        'title' => 'Pesanan',
        'url' => '/admin/orders',
        'icon' => 'ni ni-cart'
    ],
    [
        'title' => 'Pengguna',
        'url' => '/admin/users',
        'icon' => 'ni ni-single-02'
    ]
];

// Get current page for navigation
$currentPage = str_replace(BASE_URL, '', $_SERVER['PHP_SELF']);

// Tambahkan fungsi untuk mengecek halaman saat ini
function isAdminDashboard() {
    return strpos($_SERVER['PHP_SELF'], '/admin/dashboard.php') !== false;
}

// Redirect admin ke dashboard jika mencoba mengakses halaman utama
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    // Jika admin mengakses halaman utama (index.php), redirect ke dashboard
    if ($_SERVER['PHP_SELF'] === '/index.php' || $_SERVER['PHP_SELF'] === '/') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    }
}
?> 
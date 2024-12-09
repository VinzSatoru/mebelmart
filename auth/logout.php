<?php
require_once '../config/config.php';

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Hancurkan session
session_destroy();

// Set pesan sukses
$_SESSION['success'] = 'Anda berhasil logout';

// Redirect ke halaman login
redirect('/auth/login.php');
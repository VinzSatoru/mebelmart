<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Script ini hanya dijalankan sekali untuk membuat admin pertama
// Setelah admin dibuat, sebaiknya file ini dihapus atau diproteksi

$db = new Database();

// Cek apakah sudah ada admin
$adminExists = $db->getCollection('users')->countDocuments(['role' => 'admin']);

if ($adminExists > 0) {
    die('Admin sudah ada! Hapus file ini untuk keamanan.');
}

// Data admin default
$adminData = [
    'username' => 'admin',
    'email' => 'admin@example.com',
    'password' => password_hash('admin123', PASSWORD_DEFAULT), // Password: admin123
    'role' => 'admin',
    'status' => 'active',
    'created_at' => new MongoDB\BSON\UTCDateTime()
];

// Insert admin ke database
$result = $db->getCollection('users')->insertOne($adminData);

if ($result->getInsertedCount()) {
    echo 'Admin berhasil dibuat!<br>';
    echo 'Username: admin<br>';
    echo 'Password: admin123<br>';
    echo '<strong>Harap segera ganti password setelah login!</strong>';
} else {
    echo 'Gagal membuat admin!';
}
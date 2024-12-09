<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

if (!isAdmin()) {
    redirect('/auth/login.php');
}

$db = new Database();

// Daftar kategori default untuk toko mebel
$defaultCategories = [
    [
        'name' => 'Meja',
        'description' => 'Berbagai jenis meja seperti meja makan, meja tamu, meja kerja, dan meja rias',
        'status' => 'active'
    ],
    [
        'name' => 'Kursi',
        'description' => 'Koleksi kursi seperti kursi makan, kursi tamu, kursi kerja, dan kursi santai',
        'status' => 'active'
    ],
    [
        'name' => 'Almari',
        'description' => 'Berbagai jenis almari seperti almari pakaian, almari hias, dan almari dapur',
        'status' => 'active'
    ],
    
];

$categories = $db->getCollection('categories');

// Cek apakah kategori sudah ada
$existingCategories = $categories->count();

if ($existingCategories > 0) {
    echo '<div style="max-width: 600px; margin: 50px auto; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24;">';
    echo '<h3>Peringatan!</h3>';
    echo '<p>Kategori sudah ada dalam database. Untuk menghindari duplikasi, proses dibatalkan.</p>';
    echo '<p>Jika Anda ingin menambah kategori baru, silakan gunakan form tambah kategori di halaman admin.</p>';
    echo '<a href="../categories" style="display: inline-block; margin-top: 10px; padding: 8px 15px; background: #dc3545; color: white; text-decoration: none; border-radius: 3px;">Kembali ke Halaman Kategori</a>';
    echo '</div>';
    exit;
}

// Tambahkan timestamp
foreach ($defaultCategories as &$category) {
    $category['created_at'] = new MongoDB\BSON\UTCDateTime();
}

// Insert kategori default
$result = $categories->insertMany($defaultCategories);

if ($result->getInsertedCount() > 0) {
    echo '<div style="max-width: 600px; margin: 50px auto; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;">';
    echo '<h3>Sukses!</h3>';
    echo '<p>Berhasil menambahkan ' . $result->getInsertedCount() . ' kategori default.</p>';
    echo '<a href="../categories" style="display: inline-block; margin-top: 10px; padding: 8px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 3px;">Kembali ke Halaman Kategori</a>';
    echo '</div>';
} else {
    echo '<div style="max-width: 600px; margin: 50px auto; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24;">';
    echo '<h3>Error!</h3>';
    echo '<p>Gagal menambahkan kategori default.</p>';
    echo '<a href="../categories" style="display: inline-block; margin-top: 10px; padding: 8px 15px; background: #dc3545; color: white; text-decoration: none; border-radius: 3px;">Kembali ke Halaman Kategori</a>';
    echo '</div>';
}
?>
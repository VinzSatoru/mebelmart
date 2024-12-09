<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/admin.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Anda tidak memiliki akses!';
    redirect('/admin');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    $_SESSION['error'] = 'Permintaan tidak valid';
    redirect('/admin/categories');
}

try {
    $db = new Database();
    
    // Cek apakah kategori masih digunakan di produk
    $productsUsingCategory = $db->getCollection('products')->countDocuments([
        'category_id' => new MongoDB\BSON\ObjectId($_POST['id'])
    ]);

    if ($productsUsingCategory > 0) {
        throw new Exception('Kategori ini masih digunakan oleh beberapa produk');
    }

    $result = $db->getCollection('categories')->deleteOne([
        '_id' => new MongoDB\BSON\ObjectId($_POST['id'])
    ]);

    if ($result->getDeletedCount() > 0) {
        $_SESSION['success'] = 'Kategori berhasil dihapus';
    } else {
        $_SESSION['error'] = 'Kategori tidak ditemukan';
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Gagal menghapus kategori: ' . $e->getMessage();
}

redirect('/admin/categories');

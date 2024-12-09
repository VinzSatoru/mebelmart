<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/admin.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini!';
    redirect('/admin/products');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    $_SESSION['error'] = 'Permintaan tidak valid';
    redirect('/admin/products');
}

try {
    $db = new Database();
    $result = $db->getCollection('products')->deleteOne([
        '_id' => new MongoDB\BSON\ObjectId($_POST['id'])
    ]);

    if ($result->getDeletedCount() > 0) {
        $_SESSION['success'] = 'Produk berhasil dihapus';
    } else {
        $_SESSION['error'] = 'Produk tidak ditemukan';
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Gagal menghapus produk: ' . $e->getMessage();
}

redirect('/admin/products');

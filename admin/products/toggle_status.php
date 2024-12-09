<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirect('/admin/products');
}

if (!isset($_POST['id']) || !isset($_POST['current_status'])) {
    $_SESSION['error'] = 'Invalid product data';
    redirect('/admin/products');
}

try {
    $db = new Database();
    
    // Toggle status
    $newStatus = $_POST['current_status'] === 'active' ? 'inactive' : 'active';
    
    $result = $db->getCollection('products')->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($_POST['id'])],
        [
            '$set' => [
                'status' => $newStatus,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ]
    );

    if ($result->getModifiedCount() > 0) {
        $_SESSION['success'] = 'Status produk berhasil diubah';
    } else {
        $_SESSION['error'] = 'Gagal mengubah status produk';
    }

} catch (Exception $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

redirect('/admin/products'); 
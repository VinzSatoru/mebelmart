<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/admin.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Akses ditolak';
    redirect('/auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?? null;
    $newStatus = $_POST['status'] ?? null;

    if ($orderId && $newStatus) {
        try {
            $db = new Database();
            
            // Tambahkan debug untuk melihat data yang diterima
            var_dump([
                'order_id' => $orderId,
                'new_status' => $newStatus
            ]);

            // Cek data order sebelum update
            $existingOrder = $db->getCollection('orders')->findOne([
                '_id' => new MongoDB\BSON\ObjectId($orderId)
            ]);

            var_dump([
                'existing_status' => $existingOrder->status
            ]);

            // Lakukan update
            $result = $db->getCollection('orders')->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($orderId)],
                [
                    '$set' => [
                        'status' => $newStatus,
                        'updated_at' => new MongoDB\BSON\UTCDateTime()
                    ]
                ]
            );

            // Cek hasil update
            var_dump([
                'modified_count' => $result->getModifiedCount()
            ]);

            // Cek data order setelah update
            $updatedOrder = $db->getCollection('orders')->findOne([
                '_id' => new MongoDB\BSON\ObjectId($orderId)
            ]);

            var_dump([
                'updated_status' => $updatedOrder->status
            ]);

            if ($result->getModifiedCount() > 0 || $updatedOrder->status === $newStatus) {
                $_SESSION['success'] = 'Status pesanan berhasil diubah menjadi ' . getStatusText($newStatus);
            } else {
                $_SESSION['error'] = 'Gagal mengubah status pesanan';
            }

        } catch (Exception $e) {
            $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'Data tidak valid';
    }
}

redirect('/admin/orders');
?> 
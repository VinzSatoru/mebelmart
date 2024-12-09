<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu';
    redirect('/auth/login.php');
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'ID pesanan tidak valid';
    redirect('/orders');
}

try {
    $db = new Database();
    $order_id = $_GET['id'];

    // Ambil data order untuk mendapatkan total
    $order = $db->getCollection('orders')->findOne([
        '_id' => new MongoDB\BSON\ObjectId($order_id)
    ]);

    if (!$order) {
        throw new Exception('Pesanan tidak ditemukan');
    }

    // Update status pembayaran dan status pesanan
    $result = $db->getCollection('orders')->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($order_id)],
        [
            '$set' => [
                'payment_status' => 'paid',
                'status' => 'processing',
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ]
    );

    if ($result->getModifiedCount() > 0) {
        // Update total pendapatan di collection statistics
        $currentMonth = date('Y-m');
        $total = (float)$order->total->__toString();

        $stats = $db->getCollection('statistics')->findOne([
            'month' => $currentMonth
        ]);

        if ($stats) {
            // Update statistik yang sudah ada
            $currentRevenue = (float)$stats->revenue->__toString();
            $newRevenue = $currentRevenue + $total;

            $db->getCollection('statistics')->updateOne(
                ['month' => $currentMonth],
                [
                    '$set' => [
                        'revenue' => new MongoDB\BSON\Decimal128((string)$newRevenue),
                        'updated_at' => new MongoDB\BSON\UTCDateTime()
                    ],
                    '$inc' => ['orders_count' => 1]
                ]
            );
        } else {
            // Buat statistik baru untuk bulan ini
            $db->getCollection('statistics')->insertOne([
                'month' => $currentMonth,
                'revenue' => new MongoDB\BSON\Decimal128((string)$total),
                'orders_count' => 1,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]);
        }

        $_SESSION['success'] = 'Pembayaran berhasil dikonfirmasi';
    } else {
        $_SESSION['error'] = 'Gagal memproses pembayaran';
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

redirect('/orders'); 
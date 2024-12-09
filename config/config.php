<?php
session_start();

// Konfigurasi dasar
define('BASE_URL', '/mebelmart');
define('UPLOAD_DIR', __DIR__ . '/../assets/images/products/');

// Fungsi helper
function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function formatRupiah($price) {
    if ($price instanceof MongoDB\BSON\Decimal128) {
        $price = (float)$price->__toString();
    }
    return 'Rp ' . number_format($price, 0, ',', '.');
}

/**
 * Format angka menjadi format mata uang Rupiah
 * 
 * @param mixed $number Angka yang akan diformat
 * @return string Format Rupiah
 */
if (!function_exists('formatCurrency')) {
    function formatCurrency($number) {
        if ($number instanceof MongoDB\BSON\Decimal128) {
            $number = $number->__toString();
        }
        return 'Rp ' . number_format((float)$number, 0, ',', '.');
    }
}

// Fungsi untuk mendapatkan teks status
if (!function_exists('getStatusText')) {
    function getStatusText($status) {
        switch ($status) {
            case 'pending':
                return 'Menunggu Pembayaran';
            case 'processing':
                return 'Diproses';
            case 'shipped':
                return 'Dalam Pengiriman';
            case 'delivered':
                return 'Selesai';
            default:
                return 'Tidak Diketahui';
        }
    }
}

// Fungsi untuk mendapatkan kelas badge status
if (!function_exists('getStatusBadgeClass')) {
    function getStatusBadgeClass($status) {
        switch ($status) {
            case 'pending':
                return 'warning';
            case 'processing':
                return 'info';
            case 'shipped':
                return 'primary';
            case 'completed':
                return 'success';
            default:
                return 'secondary';
        }
    }
}

// Tambahkan fungsi untuk format currency jika belum ada
function formatCurrency($amount) {
    if ($amount instanceof MongoDB\BSON\Decimal128) {
        $amount = $amount->__toString();
    }
    return 'Rp ' . number_format((float)$amount, 0, ',', '.');
}

// Tambahkan fungsi formatDate
function formatDate($date) {
    if ($date instanceof MongoDB\BSON\UTCDateTime) {
        return $date->toDateTime()->format('d M Y H:i');
    }
    return date('d M Y H:i', strtotime($date));
}

// Tambahkan fungsi untuk format data grafik
function formatChartData($data) {
    return json_encode($data, JSON_NUMERIC_CHECK);
}

// Tambahkan fungsi untuk mendapatkan data pendapatan
function getRevenueData($db) {
    $result = $db->getCollection('orders')->aggregate([
        [
            '$match' => [
                'status' => ['$in' => ['delivered', 'shipped', 'processing']],
                'payment_status' => 'paid'
            ]
        ],
        [
            '$group' => [
                '_id' => [
                    'year' => ['$year' => '$created_at'],
                    'month' => ['$month' => '$created_at']
                ],
                'total' => ['$sum' => ['$toDouble' => '$total']]
            ]
        ],
        [
            '$sort' => [
                '_id.year' => 1,
                '_id.month' => 1
            ]
        ]
    ])->toArray();

    $chartData = [];
    foreach ($result as $item) {
        $date = sprintf('%d-%02d', $item->_id->year, $item->_id->month);
        $chartData[] = [
            'date' => $date,
            'revenue' => $item->total
        ];
    }

    return $chartData;
}

// Tambahkan fungsi untuk mendapatkan pesanan terbaru
function getLatestOrders($db, $limit = 5) {
    return $db->getCollection('orders')->aggregate([
        [
            '$lookup' => [
                'from' => 'users',
                'localField' => 'user_id',
                'foreignField' => '_id',
                'as' => 'user'
            ]
        ],
        [
            '$unwind' => '$user'
        ],
        [
            '$sort' => ['created_at' => -1]
        ],
        [
            '$limit' => $limit
        ]
    ])->toArray();
}

<?php
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    if (!isset($_POST['cart_id'])) {
        throw new Exception('Cart ID is required');
    }

    $db = new Database();
    $cart_id = $_POST['cart_id'];
    $user_id = $_SESSION['user_id'];

    // Verify cart item belongs to user
    $result = $db->getCollection('cart')->deleteOne([
        '_id' => new MongoDB\BSON\ObjectId($cart_id),
        'user_id' => new MongoDB\BSON\ObjectId($user_id)
    ]);

    if ($result->getDeletedCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Item berhasil dihapus'
        ]);
    } else {
        throw new Exception('Gagal menghapus item dari keranjang');
    }

} catch (Exception $e) {
    error_log('Cart Remove Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'cart_id' => $_POST['cart_id'] ?? 'not set'
        ]
    ]);
} 
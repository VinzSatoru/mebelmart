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
    $db = new Database();
    
    // Validasi input
    if (!isset($_POST['cart_id']) || !isset($_POST['change'])) {
        throw new Exception('Invalid parameters');
    }
    
    $cart_id = $_POST['cart_id'];
    $change = (int)$_POST['change'];
    $user_id = $_SESSION['user_id'];

    // Get cart item
    $cart = $db->getCollection('cart')->findOne([
        '_id' => new MongoDB\BSON\ObjectId($cart_id),
        'user_id' => new MongoDB\BSON\ObjectId($user_id)
    ]);

    if (!$cart) {
        throw new Exception('Cart item not found');
    }

    // Get product
    $product = $db->getCollection('products')->findOne([
        '_id' => $cart->product_id
    ]);

    if (!$product) {
        throw new Exception('Product not found');
    }

    // Calculate new quantity
    $newQuantity = $cart->quantity + $change;

    // Validate new quantity
    if ($newQuantity < 1) {
        throw new Exception('Quantity cannot be less than 1');
    }

    if ($newQuantity > $product->stock) {
        throw new Exception('Requested quantity exceeds available stock');
    }

    // Update cart
    $result = $db->getCollection('cart')->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($cart_id)],
        ['$set' => ['quantity' => $newQuantity]]
    );

    if ($result->getModifiedCount() > 0) {
        // Calculate new subtotal
        $price = (float)$product->price->__toString();
        $subtotal = $price * $newQuantity;

        echo json_encode([
            'success' => true,
            'newQuantity' => $newQuantity,
            'newSubtotal' => formatRupiah($subtotal)
        ]);
    } else {
        throw new Exception('Failed to update quantity');
    }

} catch (Exception $e) {
    // Log error untuk debugging
    error_log('Cart Update Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => [
            'cart_id' => $_POST['cart_id'] ?? 'not set',
            'change' => $_POST['change'] ?? 'not set'
        ]
    ]);
} 
<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Cek login
if (!isLoggedIn()) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu';
    redirect('/auth/login.php');
}

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirect('/cart');
}

// Validasi input
if (empty($_POST['full_name']) || empty($_POST['phone']) || 
    empty($_POST['address']) || empty($_POST['city']) || 
    empty($_POST['postal_code']) || empty($_POST['payment_method'])) {
    $_SESSION['error'] = 'Semua field harus diisi!';
    redirect('/checkout');
}

$db = new Database();
$user_id = $_SESSION['user_id'];

try {
    // Get cart items
    $cartItems = $db->getCollection('cart')->aggregate([
        [
            '$match' => [
                'user_id' => new MongoDB\BSON\ObjectId($user_id)
            ]
        ],
        [
            '$lookup' => [
                'from' => 'products',
                'localField' => 'product_id',
                'foreignField' => '_id',
                'as' => 'product'
            ]
        ],
        [
            '$unwind' => '$product'
        ]
    ])->toArray();

    if (empty($cartItems)) {
        $_SESSION['error'] = 'Keranjang belanja kosong';
        redirect('/cart');
    }

    // Calculate total
    $total = 0;
    $items = [];
    foreach ($cartItems as $item) {
        // Cek stok
        if ($item->quantity > $item->product->stock) {
            $_SESSION['error'] = "Stok {$item->product->name} tidak mencukupi!";
            redirect('/cart');
        }

        $price = (float)$item->product->price->__toString();
        $subtotal = $price * $item->quantity;
        $total += $subtotal;

        // Prepare items for order
        $items[] = [
            'product_id' => $item->product_id,
            'name' => $item->product->name,
            'price' => $item->product->price,
            'quantity' => $item->quantity,
            'subtotal' => new MongoDB\BSON\Decimal128((string)$subtotal)
        ];

        // Update product stock
        $db->getCollection('products')->updateOne(
            ['_id' => $item->product_id],
            ['$inc' => ['stock' => -$item->quantity]]
        );
    }

    // Add shipping cost
    $shipping_cost = 50000;
    $total += $shipping_cost;

    // Create order
    $order = [
        'user_id' => new MongoDB\BSON\ObjectId($user_id),
        'items' => $items,
        'shipping_info' => [
            'full_name' => $_POST['full_name'],
            'phone' => $_POST['phone'],
            'address' => $_POST['address'],
            'city' => $_POST['city'],
            'postal_code' => $_POST['postal_code'],
            'notes' => $_POST['notes'] ?? ''
        ],
        'payment_method' => $_POST['payment_method'],
        'shipping_cost' => new MongoDB\BSON\Decimal128((string)$shipping_cost),
        'total' => new MongoDB\BSON\Decimal128((string)$total),
        'status' => 'pending', // Ubah ke pending dulu
        'payment_status' => 'unpaid', // Set unpaid dulu
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'updated_at' => new MongoDB\BSON\UTCDateTime()
    ];

    $result = $db->getCollection('orders')->insertOne($order);

    if ($result->getInsertedCount() > 0) {
        // Clear cart
        $db->getCollection('cart')->deleteMany([
            'user_id' => new MongoDB\BSON\ObjectId($user_id)
        ]);

        $_SESSION['success'] = 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.';
        redirect('/orders/detail.php?id=' . $result->getInsertedId());
    } else {
        throw new Exception('Gagal membuat pesanan');
    }

} catch (Exception $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
    redirect('/checkout');
} 
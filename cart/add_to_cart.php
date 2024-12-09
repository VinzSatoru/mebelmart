<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

// Tambahkan debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log untuk melihat method request
file_put_contents('debug.log', 'Request Method: ' . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
file_put_contents('debug.log', 'POST data: ' . print_r($_POST, true) . "\n", FILE_APPEND);

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu';
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    file_put_contents('debug.log', 'Invalid request method' . "\n", FILE_APPEND);
    header('Location: ../index.php');
    exit;
}

$product_id = $_POST['product_id'] ?? '';
$quantity = (int)($_POST['quantity'] ?? 1);

if (empty($product_id)) {
    $_SESSION['error'] = 'Invalid product';
    file_put_contents('debug.log', 'Invalid product ID' . "\n", FILE_APPEND);
    header('Location: ../index.php');
    exit;
}

try {
    $db = new Database();
    
    // Log product ID yang dicari
    file_put_contents('debug.log', 'Looking for product: ' . $product_id . "\n", FILE_APPEND);
    
    $product = $db->getCollection('products')->findOne([
        '_id' => new MongoDB\BSON\ObjectId($product_id),
        'status' => 'active'
    ]);

    if (!$product) {
        throw new Exception('Produk tidak ditemukan atau tidak tersedia');
    }

    // Cek stok
    if ($product->stock < $quantity) {
        throw new Exception('Stok tidak mencukupi');
    }

    $price = (float)$product->price->__toString();
    $subtotal = $price * $quantity;

    $cart = $db->getCollection('carts')->findOne([
        'user_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id']),
        'status' => 'active'
    ]);

    if (!$cart) {
        // Buat keranjang baru
        $result = $db->getCollection('carts')->insertOne([
            'user_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id']),
            'items' => [
                [
                    'product_id' => new MongoDB\BSON\ObjectId($product_id),
                    'name' => $product->name,
                    'price' => new MongoDB\BSON\Decimal128((string)$price),
                    'quantity' => $quantity,
                    'subtotal' => new MongoDB\BSON\Decimal128((string)$subtotal)
                ]
            ],
            'total' => new MongoDB\BSON\Decimal128((string)$subtotal),
            'status' => 'active',
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]);

        if ($result->getInsertedCount() > 0) {
            $_SESSION['success'] = 'Produk berhasil ditambahkan ke keranjang';
            file_put_contents('debug.log', 'Cart created successfully' . "\n", FILE_APPEND);
            header('Location: ../cart/');
            exit;
        }
    } else {
        // Update keranjang yang ada
        $items = $cart->items;
        $found = false;
        $total = 0;

        foreach ($items as &$item) {
            if ($item->product_id == $product_id) {
                $newQuantity = $item->quantity + $quantity;
                if ($newQuantity > $product->stock) {
                    throw new Exception('Stok tidak mencukupi');
                }
                $item->quantity = $newQuantity;
                $itemPrice = (float)$item->price->__toString();
                $item->subtotal = new MongoDB\BSON\Decimal128((string)($itemPrice * $newQuantity));
                $found = true;
            }
            $total += (float)$item->subtotal->__toString();
        }

        if (!$found) {
            $items[] = [
                'product_id' => new MongoDB\BSON\ObjectId($product_id),
                'name' => $product->name,
                'price' => new MongoDB\BSON\Decimal128((string)$price),
                'quantity' => $quantity,
                'subtotal' => new MongoDB\BSON\Decimal128((string)$subtotal)
            ];
            $total += $subtotal;
        }

        $result = $db->getCollection('carts')->updateOne(
            ['_id' => $cart->_id],
            [
                '$set' => [
                    'items' => $items,
                    'total' => new MongoDB\BSON\Decimal128((string)$total),
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );

        if ($result->getModifiedCount() > 0) {
            $_SESSION['success'] = 'Keranjang berhasil diupdate';
            file_put_contents('debug.log', 'Cart updated successfully' . "\n", FILE_APPEND);
            header('Location: ../cart/');
            exit;
        }
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    file_put_contents('debug.log', 'Error: ' . $e->getMessage() . "\n", FILE_APPEND);
    header('Location: ../index.php');
    exit;
}

// Jika sampai di sini, berarti ada yang salah
file_put_contents('debug.log', 'Reached end of script without redirect' . "\n", FILE_APPEND);
header('Location: ../index.php');
exit; 
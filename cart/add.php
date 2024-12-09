<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.';
    redirect('/auth/login.php');
}

// Cek method dan data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    redirect('/products');
}

$db = new Database();
$product_id = $_POST['product_id'];
$quantity = (int)$_POST['quantity'];
$user_id = $_SESSION['user_id'];

try {
    // Cek stok produk
    $product = $db->getCollection('products')->findOne([
        '_id' => new MongoDB\BSON\ObjectId($product_id)
    ]);

    if (!$product) {
        throw new Exception('Produk tidak ditemukan.');
    }

    if ($product->stock < $quantity) {
        throw new Exception('Stok produk tidak mencukupi.');
    }

    // Cek apakah produk sudah ada di keranjang
    $existingCart = $db->getCollection('cart')->findOne([
        'user_id' => new MongoDB\BSON\ObjectId($user_id),
        'product_id' => new MongoDB\BSON\ObjectId($product_id)
    ]);

    if ($existingCart) {
        // Update quantity jika produk sudah ada
        $newQuantity = $existingCart->quantity + $quantity;
        if ($newQuantity > $product->stock) {
            throw new Exception('Total quantity melebihi stok yang tersedia.');
        }

        $db->getCollection('cart')->updateOne(
            ['_id' => $existingCart->_id],
            ['$set' => ['quantity' => $newQuantity]]
        );
    } else {
        // Tambah produk baru ke keranjang
        $db->getCollection('cart')->insertOne([
            'user_id' => new MongoDB\BSON\ObjectId($user_id),
            'product_id' => new MongoDB\BSON\ObjectId($product_id),
            'quantity' => $quantity,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
    }

    $_SESSION['success'] = 'Produk berhasil ditambahkan ke keranjang.';
    redirect('/cart');

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    redirect('/products/detail.php?id=' . $product_id);
}
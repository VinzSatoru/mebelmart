<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

if (!isAdmin()) {
    redirect('/auth/login.php');
}

$db = new Database();

// Get category IDs first
$categories = $db->getCollection('categories')->find([], [
    'projection' => ['_id' => 1, 'name' => 1]
])->toArray();

$categoryMap = [];
foreach ($categories as $category) {
    $categoryMap[$category->name] = $category->_id;
}

// Default products sesuai dengan schema validation
$products = [
    // Kategori Meja
    [
        'name' => 'Meja Makan Minimalis',
        'description' => 'Meja makan modern dengan 6 kursi',
        'price' => new MongoDB\BSON\Decimal128('2500000'),
        'stock' => intval(10),
        'category_id' => $categoryMap['Meja'],
        'image_url' => 'meja-makan-minimalis.jpg',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ],
    [
        'name' => 'Meja Kerja',
        'description' => 'Meja kerja dengan laci penyimpanan',
        'price' => new MongoDB\BSON\Decimal128('1200000'),
        'stock' => intval(15),
        'category_id' => $categoryMap['Meja'],
        'image_url' => 'meja-kerja.jpg',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ],
    
    // Kategori Kursi
    [
        'name' => 'Kursi Kantor Ergonomis',
        'description' => 'Kursi kantor dengan sandaran adjustable',
        'price' => new MongoDB\BSON\Decimal128('1500000'),
        'stock' => intval(15),
        'category_id' => $categoryMap['Kursi'],
        'image_url' => 'kursi-kantor.jpg',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ],
    [
        'name' => 'Kursi Makan',
        'description' => 'Kursi makan kayu solid dengan bantalan empuk',
        'price' => new MongoDB\BSON\Decimal128('500000'),
        'stock' => intval(24),
        'category_id' => $categoryMap['Kursi'],
        'image_url' => 'kursi-makan.jpg',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ],
    
    // Kategori Almari
    [
        'name' => 'Lemari Pakaian 3 Pintu',
        'description' => 'Lemari pakaian kayu jati dengan 3 pintu',
        'price' => new MongoDB\BSON\Decimal128('3500000'),
        'stock' => intval(5),
        'category_id' => $categoryMap['Almari'],
        'image_url' => 'lemari-3-pintu.jpg',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ],
    [
        'name' => 'Lemari Buku',
        'description' => 'Lemari buku 5 tingkat dengan pintu kaca',
        'price' => new MongoDB\BSON\Decimal128('2000000'),
        'stock' => intval(8),
        'category_id' => $categoryMap['Almari'],
        'image_url' => 'lemari-buku.jpg',
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]
];

// Insert products dengan error handling
foreach ($products as $product) {
    try {
        $result = $db->getCollection('products')->insertOne($product);
        if ($result->getInsertedCount() > 0) {
            echo "Berhasil menambahkan produk: {$product['name']}<br>";
        }
    } catch (Exception $e) {
        echo "Gagal menambahkan produk {$product['name']}: " . $e->getMessage() . "<br>";
    }
}

echo "<br>Proses selesai!";
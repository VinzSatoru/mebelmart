<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Cek ID produk
if (!isset($_GET['id'])) {
    redirect('/products');
}

$db = new Database();

// Get product dengan category info menggunakan aggregate
$product = $db->getCollection('products')->aggregate([
    [
        '$match' => [
            '_id' => new MongoDB\BSON\ObjectId($_GET['id'])
        ]
    ],
    [
        '$lookup' => [
            'from' => 'categories',
            'localField' => 'category_id',
            'foreignField' => '_id',
            'as' => 'category'
        ]
    ],
    [
        '$unwind' => '$category'
    ]
])->toArray();

// Jika produk tidak ditemukan
if (empty($product)) {
    redirect('/products');
}

$product = $product[0];

// Get related products dari kategori yang sama
$relatedProducts = $db->getCollection('products')->aggregate([
    [
        '$match' => [
            'category_id' => $product->category_id,
            '_id' => ['$ne' => $product->_id] // Exclude current product
        ]
    ],
    [
        '$lookup' => [
            'from' => 'categories',
            'localField' => 'category_id',
            'foreignField' => '_id',
            'as' => 'category'
        ]
    ],
    [
        '$unwind' => '$category'
    ],
    [
        '$limit' => 4
    ]
])->toArray();
?>

<?php include '../includes/header.php'; ?>


<div class="container py-5">
    <div class="mb-4">
        <a href="<?= BASE_URL ?>/products/index.php" class="btn btn-primary rounded-pill shadow-sm hover-effect">
            <i class="bi bi-arrow-left-circle-fill me-2"></i>
            Lihat Produk
        </a>
    </div>

    <!-- Product Detail -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-body p-4">
            <div class="row">
                <!-- Product Image -->
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="product-image-container">
                        <div class="card">
                            <div class="card-body">
                                <img src="<?= BASE_URL ?>/assets/images/products/<?= $product->image ?? 'default.jpg' ?>" 
                                     class="img-fluid rounded" 
                                     alt="<?= htmlspecialchars($product->name ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-md-6">
                    <div class="product-info">
                        <h1 class="h2 mb-2"><?= $product->name ?></h1>
                        <span class="badge bg-secondary mb-3"><?= $product->category->name ?></span>
                        
                        <div class="h3 mb-4 text-primary">
                            <?= formatRupiah($product->price) ?>
                        </div>

                        <div class="mb-4">
                            <h5 class="mb-3">Deskripsi Produk</h5>
                            <p class="text-muted">
                                <?= nl2br($product->description) ?>
                            </p>
                        </div>

                        <!-- Stock Status -->
                        <div class="mb-4">
                            <h5 class="mb-3">Status Stok</h5>
                            <?php if ($product->stock > 0): ?>
                                <span class="badge bg-success">Tersedia (<?= $product->stock ?> unit)</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Stok Habis</span>
                            <?php endif; ?>
                        </div>

                        <!-- Add to Cart Form -->
                        <?php if ($product->stock > 0): ?>
                        <form action="<?= BASE_URL ?>/cart/add.php" method="POST" class="mb-4">
                            <input type="hidden" name="product_id" value="<?= $product->_id ?>">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Jumlah</label>
                                    <input type="number" name="quantity" class="form-control" 
                                           value="1" min="1" max="<?= $product->stock ?>">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                                    </button>
                                </div>
                            </div>
                        </form>
                        <?php endif; ?>

                        <!-- Additional Info -->
                        <div class="additional-info">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-truck text-primary me-2"></i>
                                        <span>Pengiriman Cepat</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-shield-check text-primary me-2"></i>
                                        <span>Garansi Produk</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
    .hover-effect {
        transition: all 0.3s ease;
        border: none;
        background: linear-gradient(45deg, #1a1c20 0%, #2d3436 100%);
        padding: 10px 25px;
        font-size: 1rem;
    }

    .hover-effect:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
        background: linear-gradient(45deg, #2d3436 0%, #1a1c20 100%);
    }

    .hover-effect i {
        transition: transform 0.3s ease;
    }

    .hover-effect:hover i {
        transform: translateX(-3px);
    }
    </style>
    
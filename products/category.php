<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    $_SESSION['error'] = 'Kategori tidak valid';
    redirect('/');
}

try {
    $db = new Database();
    
    // Decode slug dan bersihkan
    $slug = htmlspecialchars_decode(urldecode($_GET['slug']));
    
    // Ambil data kategori berdasarkan slug
    $category = $db->getCollection('categories')->findOne([
        'slug' => $slug
    ]);

    if (!$category) {
        throw new Exception('Kategori tidak ditemukan');
    }

    // Ambil produk berdasarkan kategori
    $products = $db->getCollection('products')->find([
        'category_id' => $category->_id,
        'status' => 'active'
    ])->toArray();

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    redirect('/');
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="mb-4">
        <a href="<?= BASE_URL ?>/products/index.php" class="btn btn-primary rounded-pill shadow-sm hover-effect">
            <i class="bi bi-arrow-left-circle-fill me-2"></i>
            Lihat Produk
        </a>
    </div>

    <div class="row gx-4 gx-lg-5 row-cols-1 row-cols-md-3 row-cols-xl-4 justify-content-center">
        <?php foreach ($products as $product): ?>
            <div class="col mb-5">
                <div class="card h-100">
                    <!-- Product image-->
                    <img class="card-img-top" src="<?= BASE_URL ?>/assets/images/products/<?= $product->image ?? 'default.jpg' ?>" alt="<?= htmlspecialchars($product->name) ?>" />
                    <!-- Product details-->
                    <div class="card-body p-4">
                        <div class="text-center">
                            <!-- Product name-->
                            <h5 class="fw-bolder"><?= htmlspecialchars($product->name) ?></h5>
                            <!-- Product price-->
                            <?= formatCurrency($product->price) ?>
                        </div>
                    </div>
                    <!-- Product actions-->
                    <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                        <div class="text-center">
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
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($products)): ?>
            <div class="col-12 text-center">
                <p>Belum ada produk dalam kategori ini.</p>
            </div>
        <?php endif; ?>
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


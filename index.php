<?php 
require_once 'config/config.php';
require_once 'config/database.php';

// Redirect admin ke dashboard jika mencoba mengakses halaman utama
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$db = new Database();
$products = $db->getCollection('products')->aggregate([
    [
        '$lookup' => [
            'from' => 'categories',
            'localField' => 'category_id',
            'foreignField' => '_id',
            'as' => 'category'
        ]
    ],
    [
        '$unwind' => [
            'path' => '$category',
            'preserveNullAndEmptyArrays' => true
        ]
    ],
    [
        '$project' => [
            'name' => 1,
            'description' => 1,
            'price' => 1,
            'stock' => 1,
            'image' => 1,
            'category_name' => '$category.name'
        ]
    ],
    [
        '$sort' => ['created_at' => -1]
    ]
])->toArray();

// Query untuk produk unggulan (3 produk terbaru)
$featuredProducts = $db->getCollection('products')->aggregate([
    [
        '$lookup' => [
            'from' => 'categories',
            'localField' => 'category_id',
            'foreignField' => '_id',
            'as' => 'category'
        ]
    ],
    [
        '$unwind' => [
            'path' => '$category',
            'preserveNullAndEmptyArrays' => true
        ]
    ],
    [
        '$project' => [
            'name' => 1,
            'description' => 1,
            'price' => 1,
            'stock' => 1,
            'image' => 1,
            'category_name' => '$category.name'
        ]
    ],
    [
        '$sort' => ['stock' => -1]
    ],
    [
        '$limit' => 6
    ]
])->toArray();
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>


<!-- Hero Section -->
<div class="hero-section position-relative overflow-hidden">
    <div id="particles-js" class="position-absolute w-100 h-100"></div>
    <div class="container position-relative" style="z-index: 1;">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold text-white">Selamat Datang di MebelMart</h1>
                <p class="lead text-white-50">Temukan koleksi mebel berkualitas untuk rumah impian Anda.</p>
                <a href="<?= BASE_URL ?>/products" class="btn btn-primary btn-lg rounded-pill">
                    Lihat Katalog <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="col-md-6">
                <img src="<?= BASE_URL ?>/assets/images/hero-image.jpg" class="img-fluid rounded-3" alt="Furniture Display">
            </div>
        </div>
    </div>
</div>

<!-- Featured Products -->
<div id="products" class="container my-5 pt-5">
    <h2 class="section-title text-center mb-4">Produk Unggulan</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($featuredProducts as $product): ?>
            <div class="col">
                <div class="featured-product-card card h-100">
                    <?php if (!empty($product->image)): ?>
                        <img src="<?= BASE_URL ?>/assets/images/products/<?= $product->image ?>" 
                             class="card-img-top" 
                             alt="<?= $product->name ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <img src="<?= BASE_URL ?>/assets/images/no-image.jpg" 
                             class="card-img-top" 
                             alt="No Image"
                             style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="badge bg-primary mb-2">Unggulan</div>
                        <h5 class="card-title"><?= $product->name ?></h5>
                        <p class="card-text text-muted">
                            <?= isset($product->category_name) ? $product->category_name : 'Tanpa Kategori' ?>
                        </p>
                        <p class="card-text"><?= substr($product->description, 0, 100) ?>...</p>
                        <h6 class="card-subtitle mb-3 text-primary fw-bold"><?= formatCurrency($product->price) ?></h6>
                        
                        <?php if ($product->stock > 0): ?>
                            <div class="d-grid gap-2">
                                <a href="products/detail.php?id=<?= $product->_id ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-info-circle me-2"></i>Detail
                                </a>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100" disabled>Stok Habis</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>



<!-- Categories Section -->
<div id="categories" class="bg-light py-5">
    <div class="container">
        <h2 class="section-title text-center mb-4">Kategori Produk</h2>
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="category-card card h-100">
                        <div class="card-body text-center">
                            <?php
                            // Tentukan icon berdasarkan nama kategori
                            $icon = 'box';  // default icon
                            switch(strtolower($category->name)) {
                                case 'kursi':
                                    $icon = 'person-square';  // Menggunakan icon alternatif untuk kursi
                                    break;
                                case 'meja':
                                    $icon = 'table';
                                    break;
                                case 'lemari':
                                    $icon = 'cabinet-fill';
                                    break;
                                case 'kasur':
                                    $icon = 'lamp';
                                    break;
                            }
                            ?>
                            <i class="bi bi-<?= $icon ?> h1 text-primary mb-3"></i>
                            <h5 class="card-title"><?= $category->name ?></h5>
                            <a href="<?= BASE_URL ?>/products/category.php?slug=<?= $category->slug ?>" class="btn btn-outline-dark mt-auto">Lihat Semua</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Why Choose Us -->
<div class="why-choose-us">
    <div class="container">
        <h2 class="section-title text-center mb-4">Mengapa Memilih Kami?</h2>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="text-center">
                    <i class="bi bi-truck display-4 text-primary"></i>
                    <h5 class="mt-3">Pengiriman Cepat</h5>
                    <p>Layanan pengiriman ke seluruh Indonesia</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <i class="bi bi-shield-check display-4 text-primary"></i>
                    <h5 class="mt-3">Kualitas Terjamin</h5>
                    <p>Produk berkualitas dengan garansi</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <i class="bi bi-credit-card display-4 text-primary"></i>
                    <h5 class="mt-3">Pembayaran Aman</h5>
                    <p>Transaksi aman dan terpercaya</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <i class="bi bi-headset display-4 text-primary"></i>
                    <h5 class="mt-3">Layanan 24/7</h5>
                    <p>Dukungan pelanggan setiap saat</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Particles.js -->
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
particlesJS('particles-js', {
    particles: {
        number: {
            value: 80,
            density: {
                enable: true,
                value_area: 800
            }
        },
        color: {
            value: '#ffffff'
        },
        shape: {
            type: 'circle'
        },
        opacity: {
            value: 0.5,
            random: false
        },
        size: {
            value: 3,
            random: true
        },
        line_linked: {
            enable: true,
            distance: 150,
            color: '#ffffff',
            opacity: 0.4,
            width: 1
        },
        move: {
            enable: true,
            speed: 6,
            direction: 'none',
            random: false,
            straight: false,
            out_mode: 'out',
            bounce: false
        }
    },
    interactivity: {
        detect_on: 'canvas',
        events: {
            onhover: {
                enable: true,
                mode: 'repulse'
            },
            onclick: {
                enable: true,
                mode: 'push'
            },
            resize: true
        }
    },
    retina_detect: true
});

// Add smooth scrolling
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').split('#')[1];
        const target = document.getElementById(targetId);
        
        if (target) {
            const navbarHeight = document.querySelector('.navbar').offsetHeight;
            const targetPosition = target.getBoundingClientRect().top + window.pageYOffset;
            
            window.scrollTo({
                top: targetPosition - navbarHeight,
                behavior: 'smooth'
            });
        }
    });
});
</script>

<style>

#particles-js {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    z-index: 0;
}

/* Pastikan konten hero berada di atas particles */
.hero-section .container {
    position: relative;
    z-index: 1;
}

.section-title {
  position: relative;
  padding-bottom: 15px;
  font-weight: 600;
}

.section-title::after {
  content: '';
  position: absolute;
  left: 50%;
  bottom: 0;
  transform: translateX(-50%);
  width: 80px;
  height: 3px;
  background: linear-gradient(45deg, #1a1c20 0%, #2d3436 100%);
  border-radius: 2px;
}

/* Tambahkan padding-top untuk offset navbar fixed */
#products, #categories {
    scroll-margin-top: 80px; /* Sesuaikan dengan tinggi navbar */
}
</style>
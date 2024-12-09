<?php
require_once '../config/config.php';
require_once '../config/database.php';


$db = new Database();

// Filter dan Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Tampilkan 12 produk per halaman
$skip = ($page - 1) * $limit;

// Build match stage
$matchStage = [];

// Filter kategori
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $matchStage['category_id'] = new MongoDB\BSON\ObjectId($_GET['category']);
}

// Filter search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $matchStage['name'] = ['$regex' => $_GET['search'], '$options' => 'i'];
}

// Get products with category info
$pipeline = [
    [
        '$match' => (object)$matchStage
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
        '$sort' => ['created_at' => -1]
    ],
    [
        '$skip' => $skip
    ],
    [
        '$limit' => $limit
    ]
];

$products = $db->getCollection('products')->aggregate($pipeline)->toArray();

// Get total count for pagination
$totalProducts = $db->getCollection('products')->countDocuments($matchStage);
$totalPages = ceil($totalProducts / $limit);

// Get categories for filter
$categories = $db->getCollection('categories')->find([], [
    'sort' => ['name' => 1]
])->toArray();

// Simpan search value untuk form
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$category_id = isset($_GET['category']) ? $_GET['category'] : '';
?>

<?php include '../includes/header.php'; ?>

<div class="container py-4">
    <!-- Tombol kembali yang menarik di bagian atas -->
    <div class="mb-4">
        <a href="<?= BASE_URL ?>" class="btn btn-primary rounded-pill shadow-sm hover-effect">
            <i class="bi bi-arrow-left-circle-fill me-2"></i>
            Kembali ke Beranda
        </a>
    </div>

    <!-- Header dan Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h2 mb-4">Katalog Produk</h1>
            
            <!-- Filter Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->_id ?>" 
                                        <?= $category_id == $category->_id ? 'selected' : '' ?>>
                                    <?= $category->name ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Cari Produk</label>
                            <input type="text" name="search" class="form-control" 
                                   value="<?= $search ?>" placeholder="Nama produk...">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row g-4">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm product-card">
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
                    
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-secondary">
                                <?= isset($product->category->name) ? $product->category->name : 'Tanpa Kategori' ?>
                            </span>
                        </div>
                        <h5 class="card-title"><?= $product->name ?></h5>
                        <p class="card-text text-muted small">
                            <?= substr($product->description, 0, 100) ?>...
                        </p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0"><?= formatCurrency($product->price) ?></span>
                                <div class="btn-group">
                                    <a href="detail.php?id=<?= $product->_id ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="text-muted">
                    <i class="bi bi-search display-1"></i>
                    <p class="mt-3">Tidak ada produk yang ditemukan</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?><?= $category_id ? '&category=' . $category_id : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                    Previous
                </a>
            </li>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?><?= $category_id ? '&category=' . $category_id : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>
            
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?><?= $category_id ? '&category=' . $category_id : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                    Next
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Add to Cart Script -->
<script>
function addToCart(productId) {
    // Implementasi add to cart akan dibuat nanti
    alert('Fitur add to cart akan segera hadir!');
}
</script>

<?php include '../includes/footer.php'; ?>

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

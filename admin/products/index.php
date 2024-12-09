<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

if (!isAdmin()) {
    redirect('/auth/login.php');
}

$db = new Database();

// Filter dan Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
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

// Pipeline untuk aggregate
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
        '$sort' => ['name' => 1]
    ],
    [
        '$skip' => $skip
    ],
    [
        '$limit' => $limit
    ]
];

// Get products with category info
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

<!-- Rest of the HTML code remains the same -->

<?php include '../../includes/header.php'; ?>



<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Manajemen Produk</h1>
        <a href="create.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Produk
        </a>
    </div>
    <div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <a href="<?= BASE_URL ?>" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

    <!-- Filter dan Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category->_id ?>" 
                                <?= isset($_GET['category']) && $_GET['category'] == $category->_id ? 'selected' : '' ?>>
                            <?= $category->name ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cari Produk</label>
                    <input type="text" name="search" class="form-control" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Nama produk...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Produk -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="80">Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <div class="d-flex px-2 py-1">
                                    <div>
                                        <?php if (!empty($product->image)): ?>
                                            <img src="<?= BASE_URL ?>/assets/images/products/<?= $product->image ?>" 
                                                 class="img-thumbnail" 
                                                 alt="<?= $product->name ?>" 
                                                 width="60">
                                        <?php else: ?>
                                            <img src="<?= BASE_URL ?>/assets/images/no-image.jpg" 
                                                 class="img-thumbnail" 
                                                 alt="No Image" 
                                                 width="60">
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center ms-3">
                                        <h6 class="mb-0 text-sm"><?= $product->name ?></h6>
                                    </div>
                                </div>
                            </td>
                            <td><?= $product->name ?></td>
                            <td><?= $product->category->name ?></td>
                            <td>Rp <?= formatRupiah($product->price, 0, ',', '.') ?></td>
                            <td><?= $product->stock ?></td>
                            <td>
                                <form action="toggle_status.php" method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $product->_id ?>">
                                    <input type="hidden" name="current_status" 
                                           value="<?= isset($product->status) ? $product->status : 'inactive' ?>">
                                    <button type="submit" 
                                            class="btn btn-sm btn-<?= isset($product->status) && $product->status === 'active' ? 'success' : 'secondary' ?>">
                                        <?= isset($product->status) ? ucfirst($product->status) : 'Inactive' ?>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit.php?id=<?= $product->_id ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            onclick="confirmDelete('<?= $product->_id ?>', '<?= $product->name ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-4"></i>
                                    <p class="mt-2">Tidak ada produk ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus produk <strong id="productName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="delete.php" method="POST" class="d-inline">
                    <input type="hidden" name="id" id="productId">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('productId').value = id;
    document.getElementById('productName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include '../../includes/footer.php'; ?>
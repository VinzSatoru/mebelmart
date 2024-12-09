<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/admin.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Anda tidak memiliki akses!';
    redirect('/admin');
}

$db = new Database();
$categories = $db->getCollection('categories')->find([], [
    'sort' => ['name' => 1]
])->toArray();

include '../../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Kategori Produk</h2>
        <a href="create.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Kategori
        </a>
    </div>
    <div class="row mb-4">
        <div class="col-12">
            <a href="<?= BASE_URL ?>" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Produk</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $index => $category): ?>
                            <?php
                            // Ambil produk yang sesuai dengan kategori
                            $products = $db->getCollection('products')->find([
                                'category_id' => $category->_id
                            ])->toArray();
                            $productNames = array_map(fn($product) => $product->name, $products);
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($category->name ?? '') ?></td>
                                <td><?= htmlspecialchars(implode(', ', $productNames)) ?></td>
                                <td>
                                    <span class="badge bg-<?= isset($category->status) && $category->status === 'active' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($category->status ?? 'inactive') ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?= $category->_id ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="delete.php" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                        <input type="hidden" name="id" value="<?= $category->_id ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada kategori</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/admin.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini!';
    redirect('/admin/products');
}

$db = new Database();

// Cek apakah ID produk ada
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'ID produk tidak valid';
    redirect('/admin/products');
}

$product = $db->getCollection('products')->findOne([
    '_id' => new MongoDB\BSON\ObjectId($_GET['id'])
]);

if (!$product) {
    $_SESSION['error'] = 'Produk tidak ditemukan';
    redirect('/admin/products');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $status = $_POST['status'] ?? 'inactive';
    
    // Handle file upload
    $image = $product->image ?? ''; // Default ke gambar yang sudah ada
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../assets/images/products/';
        
        // Buat direktori jika belum ada
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Hapus foto lama jika ada
        if (!empty($product->image) && file_exists($uploadDir . $product->image)) {
            unlink($uploadDir . $product->image);
        }
        
        // Generate nama file unik
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid() . '.' . $extension;
        
        // Pindahkan file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image)) {
            // File berhasil diupload
        } else {
            $_SESSION['error'] = 'Gagal mengupload gambar';
            $image = $product->image; // Kembalikan ke gambar lama jika gagal
        }
    }

    try {
        $db->getCollection('products')->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($_GET['id'])],
            ['$set' => [
                'name' => $name,
                'description' => $description,
                'price' => new MongoDB\BSON\Decimal128($price),
                'stock' => (int)$stock,
                'status' => $status,
                'image' => $image,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]]
        );

        $_SESSION['product_success'] = 'Produk berhasil diupdate';
        redirect('/admin/products');
    } catch (Exception $e) {
        $_SESSION['error'] = 'Gagal mengupdate produk: ' . $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4">Edit Produk</h2>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Produk</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($product->name) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi Produk</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="4"><?= htmlspecialchars($product->description ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Harga</label>
                            <input type="number" class="form-control" id="price" name="price" 
                                   value="<?= (float)$product->price->__toString() ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="stock" class="form-label">Stok</label>
                            <input type="number" class="form-control" id="stock" name="stock" 
                                   value="<?= $product->stock ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?= $product->status === 'active' ? 'selected' : '' ?>>Aktif</option>
                                <option value="inactive" <?= $product->status === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="image" class="form-label">Foto Produk</label>
                            <input type="file" class="form-control mb-2" id="image" name="image" 
                                   accept="image/*" onchange="previewImage(this)">
                            <div class="text-center">
                                <?php if (!empty($product->image)): ?>
                                    <img src="<?= BASE_URL ?>/assets/images/products/<?= $product->image ?>" 
                                         id="preview" class="img-fluid rounded" 
                                         alt="<?= htmlspecialchars($product->name) ?>">
                                <?php else: ?>
                                    <img src="<?= BASE_URL ?>/assets/images/no-image.png" 
                                         id="preview" class="img-fluid rounded" 
                                         alt="No Image">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="<?= BASE_URL ?>/admin/products" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../../includes/footer.php'; ?>

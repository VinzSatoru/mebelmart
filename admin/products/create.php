<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/admin.php';

$db = new Database();

// Ambil data kategori untuk dropdown
$categories = $db->getCollection('categories')->find()->toArray();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Admin MebelMart</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Argon Dashboard CSS -->
    <link rel="stylesheet" href="https://demos.creative-tim.com/argon-dashboard/assets/css/argon-dashboard.min.css">
</head>
<body>
    <main class="main-content mt-1 border-radius-lg">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>Tambah Produk Baru</h6>
                        </div>
                        <div class="card-body">
                            <form action="add_product.php" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label">Nama Produk</label>
                                            <input type="text" class="form-control" name="name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label">Kategori</label>
                                            <select class="form-control" name="category_id" required>
                                                <option value="">Pilih Kategori</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= $category->_id ?>"><?= $category->name ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-control-label">Deskripsi Produk</label>
                                    <textarea class="form-control" name="description" rows="3" required></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label">Harga</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" class="form-control" name="price" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label">Stok</label>
                                            <input type="number" class="form-control" name="stock" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-control-label">Gambar Produk</label>
                                    <div class="custom-file">
                                        <input type="file" class="form-control" name="image" accept="image/*" required>
                                    </div>
                                </div>
                                
                                <div class="text-end mt-4">
                                    <a href="index.php" class="btn btn-secondary">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Produk</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://demos.creative-tim.com/argon-dashboard/assets/js/argon-dashboard.min.js"></script>
</body>
</html>

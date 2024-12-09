<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/admin.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Anda tidak memiliki akses!';
    redirect('/admin');
}

// Fungsi untuk generate slug
function generateSlug($name) {
    // Konversi ke lowercase
    $slug = strtolower($name);
    // Ganti spasi dengan dash
    $slug = str_replace(' ', '-', $slug);
    // Hapus karakter special
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    // Hapus multiple dash
    $slug = preg_replace('/-+/', '-', $slug);
    return $slug;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $status = $_POST['status'] ?? 'inactive';
    
    // Generate slug dari nama
    $slug = generateSlug($name);
    
    try {
        $db = new Database();
        
        // Cek apakah kategori sudah ada
        $exists = $db->getCollection('categories')->findOne(['slug' => $slug]);
        if ($exists) {
            throw new Exception('Kategori dengan nama tersebut sudah ada');
        }
        
        $result = $db->getCollection('categories')->insertOne([
            'name' => $name,
            'slug' => $slug,
            'status' => $status,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]);

        if ($result->getInsertedCount() > 0) {
            $_SESSION['success'] = 'Kategori berhasil ditambahkan';
            redirect('/admin/categories');
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Gagal menambahkan kategori: ' . $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tambah Kategori</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/admin/categories" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

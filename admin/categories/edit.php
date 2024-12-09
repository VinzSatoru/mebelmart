<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/admin.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Anda tidak memiliki akses!';
    redirect('/admin');
}

$db = new Database();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'ID kategori tidak valid';
    redirect('/admin/categories');
}

try {
    $category = $db->getCollection('categories')->findOne([
        '_id' => new MongoDB\BSON\ObjectId($_GET['id'])
    ]);

    if (!$category) {
        throw new Exception('Kategori tidak ditemukan');
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    redirect('/admin/categories');
}

// Fungsi untuk generate slug (sama seperti di create.php)
function generateSlug($name) {
    $slug = strtolower($name);
    $slug = str_replace(' ', '-', $slug);
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return $slug;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $status = $_POST['status'] ?? 'inactive';
    
    // Generate slug dari nama
    $slug = generateSlug($name);
    
    try {
        // Cek duplikat slug kecuali untuk kategori yang sedang diedit
        $exists = $db->getCollection('categories')->findOne([
            '_id' => ['$ne' => new MongoDB\BSON\ObjectId($_GET['id'])],
            'slug' => $slug
        ]);
        
        if ($exists) {
            throw new Exception('Kategori dengan nama tersebut sudah ada');
        }
        
        $result = $db->getCollection('categories')->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($_GET['id'])],
            [
                '$set' => [
                    'name' => $name,
                    'slug' => $slug,
                    'status' => $status,
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );

        if ($result->getModifiedCount() > 0) {
            $_SESSION['success'] = 'Kategori berhasil diupdate';
            redirect('/admin/categories');
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Gagal mengupdate kategori: ' . $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Kategori</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($category->name) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?= $category->status === 'active' ? 'selected' : '' ?>>
                                    Active
                                </option>
                                <option value="inactive" <?= $category->status === 'inactive' ? 'selected' : '' ?>>
                                    Inactive
                                </option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/admin/categories" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

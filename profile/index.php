<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$db = new Database();
$user = $db->getCollection('users')->findOne([
    '_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photo = $_FILES['photo'];
            $allowedTypes = ['image/jpeg', 'image/png'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            // Debug: Cek informasi file
            error_log('File Type: ' . $photo['type']);
            error_log('File Size: ' . $photo['size']);

            // Validasi tipe file
            if (!in_array($photo['type'], $allowedTypes)) {
                throw new Exception('Tipe file harus JPG atau PNG');
            }

            // Validasi ukuran
            if ($photo['size'] > $maxSize) {
                throw new Exception('Ukuran file maksimal 5MB');
            }

            // Generate nama file unik
            $extension = pathinfo($photo['name'], PATHINFO_EXTENSION);
            $filename = $_SESSION['user_id'] . '_' . time() . '.' . $extension;
            $uploadPath = '../uploads/profiles/' . $filename;

            // Buat folder jika belum ada
            if (!file_exists('../uploads/profiles')) {
                mkdir('../uploads/profiles', 0777, true);
            }

            // Debug: Cek path upload
            error_log('Upload Path: ' . $uploadPath);

            // Hapus foto lama jika ada
            if (!empty($user->photo)) {
                $oldPhoto = '../uploads/profiles/' . $user->photo;
                if (file_exists($oldPhoto)) {
                    unlink($oldPhoto);
                }
            }

            // Upload file
            if (move_uploaded_file($photo['tmp_name'], $uploadPath)) {
                // Update database
                $result = $db->getCollection('users')->updateOne(
                    ['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])],
                    ['$set' => [
                        'photo' => $filename,
                        'updated_at' => new MongoDB\BSON\UTCDateTime()
                    ]]
                );

                if ($result->getModifiedCount() > 0) {
                    $_SESSION['profile_success'] = 'Foto profil berhasil diupdate';
                    header('Location: ' . BASE_URL . '/profile');
                    exit;
                }
            } else {
                throw new Exception('Gagal mengupload file');
            }
        } else {
            // Debug: Cek error upload
            error_log('Upload Error: ' . $_FILES['photo']['error']);
            throw new Exception('Pilih file foto terlebih dahulu');
        }
    } catch (Exception $e) {
        $_SESSION['profile_error'] = $e->getMessage();
    }
}

include '../includes/header.php';
?>

<!-- Sweet Alert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 text-primary"><i class="bi bi-person-circle"></i> Profil Saya</h4>
                </div>
                <div class="card-body">
                    <!-- Profile Section -->
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <?php if (!empty($user->photo)): ?>
                                <img src="<?= BASE_URL ?>/uploads/profiles/<?= $user->photo ?>" 
                                     alt="Profile Photo" 
                                     class="rounded-circle img-thumbnail shadow"
                                     style="width: 180px; height: 180px; object-fit: cover;">
                            <?php else: ?>
                                <img src="<?= BASE_URL ?>/assets/images/default-profile.jpg" 
                                     alt="Default Profile" 
                                     class="rounded-circle img-thumbnail shadow"
                                     style="width: 180px; height: 180px; object-fit: cover;">
                            <?php endif; ?>
                            <label for="photo" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 cursor-pointer" style="cursor: pointer;">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                        </div>
                    </div>

                    <!-- User Info -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">Nama Lengkap</h6>
                                    <p class="h5 mb-0"><?= htmlspecialchars($user->name ?? 'Belum diatur') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">Email</h6>
                                    <p class="h5 mb-0"><?= htmlspecialchars($user->email ?? 'Belum diatur') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
                        <input type="file" class="d-none" id="photo" name="photo" accept="image/jpeg,image/png" onchange="submitForm()">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Upload foto profil dengan format JPG/PNG, maksimal ukuran 5MB
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tombol kembali yang lebih menarik -->
            <div class="text-center">
                <a href="<?= BASE_URL ?>" class="btn btn-primary btn-lg rounded-pill shadow-sm hover-effect">
                    <i class="bi bi-house-heart-fill me-2"></i>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function submitForm() {
    document.getElementById('uploadForm').submit();
}

// Sweet Alert untuk success message
<?php if (isset($_SESSION['profile_success'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= $_SESSION['profile_success'] ?>',
        timer: 2000,
        showConfirmButton: false
    });
    <?php unset($_SESSION['profile_success']); ?>
<?php endif; ?>

// Sweet Alert untuk error message
<?php if (isset($_SESSION['profile_error'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: '<?= $_SESSION['profile_error'] ?>',
    });
    <?php unset($_SESSION['profile_error']); ?>
<?php endif; ?>
</script>

<style>
.card {
    border-radius: 15px;
}
.card-header {
    border-radius: 15px 15px 0 0 !important;
}
.bg-light {
    background-color: #f8f9fa !important;
}
.cursor-pointer {
    cursor: pointer;
}
.shadow {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.hover-effect {
    transition: all 0.3s ease;
    border: none;
    background: linear-gradient(45deg, #1a1c20 0%, #2d3436 100%);
}

.hover-effect:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3) !important;
    background: linear-gradient(45deg, #2d3436 0%, #1a1c20 100%);
}

.btn-lg {
    padding: 12px 30px;
    font-size: 1.1rem;
}
</style>

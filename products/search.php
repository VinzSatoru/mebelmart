<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Cek login
if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

// Redirect admin ke halaman admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    redirect('/admin');
}

$db = new Database();
$search = $_GET['q'] ?? '';

try {
    // Gunakan regex untuk pencarian yang lebih fleksibel
    $products = $db->getCollection('products')->find([
        'name' => ['$regex' => $search, '$options' => 'i'],
        'status' => 'active'
    ])->toArray();

    include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Tombol kembali -->
    <div class="mb-4">
        <a href="<?= BASE_URL ?>" class="btn btn-primary rounded-pill shadow-sm hover-effect">
            <i class="bi bi-arrow-left-circle-fill me-2"></i>
            Kembali ke Beranda
        </a>
    </div>

    <h2 class="mb-4">Hasil Pencarian: "<?= htmlspecialchars($search) ?>"</h2>
    
    <?php if (empty($products)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Tidak ada produk yang ditemukan untuk pencarian ini.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($products as $product): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm product-card">
                        <img src="<?= BASE_URL ?>/assets/images/products/<?= $product->image ?>" 
                             class="card-img-top" alt="<?= htmlspecialchars($product->name) ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product->name) ?></h5>
                            <p class="card-text text-muted mb-2">
                                <?= htmlspecialchars($product->description ?? '') ?>
                            </p>
                            <p class="card-text fw-bold mb-3">
                                Rp <?= number_format($product->price->__toString(), 0, ',', '.') ?>
                            </p>
                            <div class="d-grid">
                                <button class="btn btn-primary add-to-cart" 
                                        data-product-id="<?= $product->_id ?>">
                                    <i class="bi bi-cart-plus me-2"></i>
                                    Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.product-card {
    transition: transform 0.3s ease;
    border: none;
    border-radius: 15px;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-5px);
}

.hover-effect {
    transition: all 0.3s ease;
    border: none;
    background: linear-gradient(45deg, #1a1c20 0%, #2d3436 100%);
}

.hover-effect:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3) !important;
    background: linear-gradient(45deg, #2d3436 0%, #1a1c20 100%);
}
</style>

<script>
// Script untuk menambah ke keranjang
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        
        fetch(`${BASE_URL}/cart/add_to_cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=1`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Terjadi kesalahan saat menambahkan ke keranjang'
            });
        });
    });
});
</script>

<?php
    include '../includes/footer.php';
} catch (Exception $e) {
    $_SESSION['error'] = 'Terjadi kesalahan saat mencari produk';
    redirect('/');
}
?> 
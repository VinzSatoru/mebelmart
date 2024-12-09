<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$db = new Database();
$user_id = $_SESSION['user_id'];

// Get cart items dengan informasi produk
$cartItems = $db->getCollection('cart')->aggregate([
    [
        '$match' => [
            'user_id' => new MongoDB\BSON\ObjectId($user_id)
        ]
    ],
    [
        '$lookup' => [
            'from' => 'products',
            'localField' => 'product_id',
            'foreignField' => '_id',
            'as' => 'product'
        ]
    ],
    [
        '$unwind' => '$product'
    ],
    [
        '$sort' => ['created_at' => -1]
    ]
])->toArray();

// Hitung total
$total = 0;
foreach ($cartItems as $item) {
    $price = (float)$item->product->price->__toString();
    $total += $price * $item->quantity;
}
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

    <h1 class="h2 mb-4">Keranjang Belanja</h1>

    <?php if (!empty($cartItems)): ?>
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item mb-3 pb-3 border-bottom" data-cart-id="<?= (string)$item->_id ?>">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <img src="<?= BASE_URL ?>/assets/images/products/<?= $product->image ?? 'default.jpg' ?>"  
                                             class="img-fluid rounded" alt="<?= htmlspecialchars($product->name ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <h5 class="mb-1"><?= $item->product->name ?></h5>
                                        <p class="text-muted mb-0"><?= formatRupiah($item->product->price) ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                    onclick="updateQuantity('<?= (string)$item->_id ?>', -1)">-</button>
                                            <input type="number" class="form-control form-control-sm text-center quantity-input" 
                                                   value="<?= $item->quantity ?>" min="1" 
                                                   max="<?= $item->product->stock ?>" readonly>
                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    onclick="updateQuantity('<?= (string)$item->_id ?>', 1)">+</button>
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <p class="mb-0 fw-bold subtotal">
                                            <?php 
                                            $price = (float)$item->product->price->__toString();
                                            $subtotal = $price * $item->quantity;
                                            echo formatRupiah($subtotal);
                                            ?>
                                        </p>
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <button type="button" class="btn btn-link text-danger p-0"
                                                onclick="removeItem('<?= (string)$item->_id ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Ringkasan Belanja</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total Harga</span>
                            <span class="fw-bold"><?= formatRupiah($total) ?></span>
                        </div>
                        <hr>
                        <a href="<?= BASE_URL ?>/checkout" class="btn btn-primary w-100">
                            Lanjut ke Pembayaran
                        </a>
                        <a href="<?= BASE_URL ?>/products" class="btn btn-outline-secondary w-100 mt-2">
                            Lanjut Belanja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x display-1 text-muted"></i>
            <h4 class="mt-3">Keranjang Belanja Kosong</h4>
            <p class="text-muted">Anda belum menambahkan produk ke keranjang.</p>
            <a href="<?= BASE_URL ?>/products" class="btn btn-primary">
                Mulai Belanja
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Update Quantity Script -->
<script>
function updateQuantity(cartId, change) {
    console.log('Updating cart:', { cartId, change });

    fetch('<?= BASE_URL ?>/cart/update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `cart_id=${cartId}&change=${change}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server response:', data);
        
        if (data.success) {
            const cartItemSelector = `[data-cart-id='${cartId}']`;
            console.log('Looking for cart item with selector:', cartItemSelector);
            
            const cartItem = document.querySelector(cartItemSelector);
            if (cartItem) {
                const quantityInput = cartItem.querySelector('.quantity-input');
                const subtotalElement = cartItem.querySelector('.subtotal');
                
                if (quantityInput && subtotalElement) {
                    quantityInput.value = data.newQuantity;
                    subtotalElement.textContent = data.newSubtotal;
                    
                    updateCartTotal();
                } else {
                    console.error('Required elements not found in cart item:', cartId);
                }
            } else {
                console.error('Cart item element not found for ID:', cartId);
            }
        } else {
            alert(data.message || 'Terjadi kesalahan saat mengupdate quantity');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Terjadi kesalahan dalam komunikasi dengan server');
    });
}

function updateCartTotal() {
    const subtotals = document.querySelectorAll('.subtotal');
    let total = 0;
    
    subtotals.forEach(element => {
        const value = element.textContent
            .replace('Rp ', '')
            .replace(/\./g, '')
            .trim();
        
        const numValue = parseInt(value);
        if (!isNaN(numValue)) {
            total += numValue;
        }
    });
    
    const totalElement = document.querySelector('.cart-total');
    if (totalElement) {
        totalElement.textContent = formatRupiah(total);
    } else {
        console.error('Total element not found');
    }
}

function formatRupiah(number) {
    return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function removeItem(cartId) {
    if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
        fetch('<?= BASE_URL ?>/cart/remove.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartItem = document.querySelector(`[data-cart-id='${cartId}']`);
                if (cartItem) {
                    cartItem.remove();
                    updateCartTotal();
                    
                    const remainingItems = document.querySelectorAll('.cart-item');
                    if (remainingItems.length === 0) {
                        location.reload();
                    }
                }
            } else {
                alert(data.message || 'Gagal menghapus item');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Terjadi kesalahan dalam komunikasi dengan server');
        });
    }
}
</script>

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

<?php include '../includes/footer.php'; ?>
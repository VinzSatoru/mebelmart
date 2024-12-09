<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu';
    redirect('/auth/login.php');
}

$db = new Database();
$user_id = $_SESSION['user_id'];

// Get cart items
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
    ]
])->toArray();

// Calculate total
$total = 0;
foreach ($cartItems as $item) {
    $price = (float)$item->product->price->__toString();
    $total += $price * $item->quantity;
}

// Add shipping cost
$shipping_cost = 50000;
$total += $shipping_cost;

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Form Pengiriman</h5>
                </div>
                <div class="card-body">
                    <form action="process.php" method="POST">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">Kota</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Kode Pos</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan (opsional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       id="transfer" value="transfer" required>
                                <label class="form-check-label" for="transfer">
                                    Transfer Bank
                                </label>
                            </div>
                        </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Ringkasan Pesanan</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($cartItems as $item): ?>
                        <?php 
                            $price = (float)$item->product->price->__toString();
                            $subtotal = $price * $item->quantity;
                        ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= $item->product->name ?> x <?= $item->quantity ?></span>
                            <span><?= formatCurrency($subtotal) ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span><?= formatCurrency($total - $shipping_cost) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Biaya Pengiriman</span>
                        <span><?= formatCurrency($shipping_cost) ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total</strong>
                        <strong><?= formatCurrency($total) ?></strong>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Buat Pesanan
                    </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 
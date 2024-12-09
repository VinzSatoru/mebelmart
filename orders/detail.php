<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu';
    redirect('/auth/login.php');
}

$db = new Database();

// Cek ID pesanan
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'ID Pesanan tidak valid';
    redirect('/orders');
}

try {
    // Ambil detail pesanan
    $order = $db->getCollection('orders')->findOne([
        '_id' => new MongoDB\BSON\ObjectId($_GET['id']),
        'user_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])
    ]);

    if (!$order) {
        throw new Exception('Pesanan tidak ditemukan');
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    redirect('/orders');
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Detail Pesanan #<?= substr($order->_id, -8) ?></h5>
                </div>
                <div class="card-body">
                    <!-- Status Pesanan -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Status Pesanan</h6>
                        <span class="badge bg-<?= $order->status === 'pending' ? 'warning' : 
                            ($order->status === 'processing' ? 'info' : 
                            ($order->status === 'shipped' ? 'primary' : 
                            ($order->status === 'delivered' ? 'success' : 'secondary'))) ?>">
                            <?= ucfirst($order->status) ?>
                        </span>
                    </div>

                    <!-- Informasi Pengiriman -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Informasi Pengiriman</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($order->shipping_info->full_name) ?></p>
                                <p class="mb-1"><strong>Telepon:</strong> <?= htmlspecialchars($order->shipping_info->phone) ?></p>
                                <p class="mb-1"><strong>Alamat:</strong> <?= htmlspecialchars($order->shipping_info->address) ?></p>
                                <p class="mb-1"><strong>Kota:</strong> <?= htmlspecialchars($order->shipping_info->city) ?></p>
                                <p class="mb-1"><strong>Kode Pos:</strong> <?= htmlspecialchars($order->shipping_info->postal_code) ?></p>
                                <?php if (!empty($order->shipping_info->notes)): ?>
                                    <p class="mb-0"><strong>Catatan:</strong> <?= htmlspecialchars($order->shipping_info->notes) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Produk -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Detail Produk</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order->items as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item->name) ?></td>
                                            <td class="text-center"><?= $item->quantity ?></td>
                                            <td class="text-end"><?= formatCurrency($item->price) ?></td>
                                            <td class="text-end"><?= formatCurrency($item->subtotal) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <?php
                                        $total = (float)$order->total->__toString();
                                        $shipping = (float)$order->shipping_cost->__toString();
                                        $subtotal = $total - $shipping;
                                    ?>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Subtotal</strong></td>
                                        <td class="text-end"><?= formatCurrency($subtotal) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Biaya Pengiriman</strong></td>
                                        <td class="text-end"><?= formatCurrency($shipping) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total</strong></td>
                                        <td class="text-end"><strong><?= formatCurrency($total) ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Informasi Pembayaran -->
                    <?php if ($order->status === 'pending'): ?>
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">Instruksi Pembayaran</h6>
                            <p class="mb-0">Silakan transfer ke rekening berikut:</p>
                            <ul class="mb-0">
                                <li>Bank BCA</li>
                                <li>No. Rekening: 1234567890</li>
                                <li>Atas Nama: MebelMart</li>
                                <li>Jumlah: <?= formatCurrency($order->total) ?></li>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Tombol Kembali -->
                    <div class="text-center mt-4">
                        <a href="<?= BASE_URL ?>/orders" class="btn btn-secondary">
                            Kembali ke Daftar Pesanan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
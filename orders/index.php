<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Fungsi helper untuk label status
function getStatusLabel($status) {
    switch ($status) {
        case 'pending':
            return 'Menunggu Pembayaran';
        case 'paid':
            return 'Dibayar';
        case 'processing':
            return 'Diproses';
        case 'shipped':
            return 'Dikirim';
        case 'delivered':
            return 'Sampai Tujuan';
        case 'completed':
            return 'Selesai';
        case 'cancelled':
            return 'Dibatalkan';
        default:
            return ucfirst($status);
    }
}

// Fungsi helper untuk warna badge
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'paid':
            return 'info';
        case 'processing':
            return 'primary';
        case 'shipped':
            return 'info';
        case 'delivered':
            return 'success';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu';
    redirect('/auth/login.php');
}

$db = new Database();
$user_id = $_SESSION['user_id'];

// Get orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$skip = ($page - 1) * $limit;

// Status filter
$statusFilter = isset($_GET['status']) && $_GET['status'] !== 'all' 
    ? ['status' => $_GET['status']] 
    : [];

// Combine user filter with status filter
$filter = array_merge(
    ['user_id' => new MongoDB\BSON\ObjectId($user_id)],
    $statusFilter
);

// Get total orders for pagination
$totalOrders = $db->getCollection('orders')->countDocuments($filter);
$totalPages = ceil($totalOrders / $limit);

// Get orders
$orders = $db->getCollection('orders')
    ->find($filter, [
        'sort' => ['created_at' => -1],
        'skip' => $skip,
        'limit' => $limit
    ])
    ->toArray();

include '../includes/header.php';
?>

<div class="container py-5">
    <!-- Tombol kembali yang menarik di bagian atas -->
    <div class="mb-4">
        <a href="<?= BASE_URL ?>" class="btn btn-primary rounded-pill shadow-sm hover-effect">
            <i class="bi bi-arrow-left-circle-fill me-2"></i>
            Kembali ke Beranda
        </a>
    </div>

        <div class="row mb-4">
            <div class="col">
                <h2 class="mb-0">Pesanan Saya</h2>
                <p class="text-muted">Kelola dan pantau status pesanan Anda</p>
            </div>
        </div>

        <!-- Filter Status -->
        <div class="mb-4">
            <div class="btn-group">
                <a href="?status=all" 
                   class="btn btn-outline-secondary <?= !isset($_GET['status']) || $_GET['status'] === 'all' ? 'active' : '' ?>">
                    Semua Pesanan
                </a>
                <a href="?status=pending" 
                   class="btn btn-outline-warning <?= isset($_GET['status']) && $_GET['status'] === 'pending' ? 'active' : '' ?>">
                    Menunggu Pembayaran
                </a>
                <a href="?status=processing" 
                   class="btn btn-outline-info <?= isset($_GET['status']) && $_GET['status'] === 'processing' ? 'active' : '' ?>">
                    Sedang Diproses
                </a>
                <a href="?status=shipped" 
                   class="btn btn-outline-primary <?= isset($_GET['status']) && $_GET['status'] === 'shipped' ? 'active' : '' ?>">
                    Dalam Pengiriman
                </a>
                <a href="?status=delivered" 
                   class="btn btn-outline-success <?= isset($_GET['status']) && $_GET['status'] === 'delivered' ? 'active' : '' ?>">
                    Selesai
                </a>
            </div>
        </div>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                <h5 class="alert-heading">Belum ada pesanan</h5>
                <p class="mb-0">Anda belum memiliki pesanan. <a href="<?= BASE_URL ?>/products" class="alert-link">Mulai belanja sekarang!</a></p>
            </div>
        <?php else: ?>
            <!-- Orders List -->
            <?php foreach ($orders as $order): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <span class="text-primary fw-bold">#<?= substr($order->_id, -8) ?></span>
                                <span class="mx-2">•</span>
                                <span class="text-muted">
                                    <?= date('d M Y H:i', $order->created_at->toDateTime()->getTimestamp()) ?>
                                </span>
                            </div>
                            <div class="col-md-4 text-md-center">
                                <span class="badge bg-<?= getStatusBadgeClass($order->status) ?> px-3 py-2">
                                    <?= getStatusText($order->status) ?>
                                </span>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <strong>Total: <?= formatCurrency($order->total) ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Shipping Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Informasi Pengiriman</h6>
                                <p class="mb-1"><strong><?= $order->shipping_info->full_name ?></strong></p>
                                <p class="mb-1"><?= $order->shipping_info->phone ?></p>
                                <p class="mb-1"><?= $order->shipping_info->address ?></p>
                                <p class="mb-0"><?= $order->shipping_info->city ?>, <?= $order->shipping_info->postal_code ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Metode Pembayaran</h6>
                                <p class="mb-1"><?= ucfirst($order->payment_method ?? 'Tidak Diketahui') ?></p>
                                <p class="mb-0">Status: 
                                    <span class="badge bg-<?= isset($order->payment_status) && $order->payment_status === 'paid' ? 'success' : 'warning' ?>">
                                        <?= isset($order->payment_status) && $order->payment_status === 'paid' ? 'Lunas' : 'Belum Dibayar' ?>
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="table-responsive mb-3">
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
                                            <td>
                                                <h6 class="mb-0"><?= $item->name ?></h6>
                                            </td>
                                            <td class="text-center"><?= $item->quantity ?></td>
                                            <td class="text-end"><?= formatCurrency($item->price) ?></td>
                                            <td class="text-end"><?= formatCurrency($item->subtotal) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Biaya Pengiriman</strong></td>
                                        <td class="text-end"><?= formatCurrency($order->shipping_cost) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total</strong></td>
                                        <td class="text-end"><strong><?= formatCurrency($order->total) ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 text-end">
                            <a href="detail.php?id=<?= $order->_id ?>" class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i> Lihat Detail
                            </a>
                            <?php if ($order->status === 'pending' && (!isset($order->payment_status) || $order->payment_status !== 'paid')): ?>
                                <a href="process_payment.php?id=<?= $order->_id ?>" 
                                   class="btn btn-primary"
                                   onclick="return confirm('Apakah Anda yakin ingin melakukan pembayaran sekarang?')">
                                    <i class="bi bi-credit-card"></i> Bayar Sekarang
                                </a>
                            <?php endif; ?>
                            <td>
                                <span class="badge bg-<?= getStatusBadgeClass($order->status) ?> status-badge">
                                    <?= getStatusLabel($order->status) ?>
                                </span>
                            </td>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-center">
                    <nav>
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                                    <a class="page-link" 
                                       href="?page=<?= $i ?><?= isset($_GET['status']) ? '&status=' . $_GET['status'] : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Tambahkan style -->
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



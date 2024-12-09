<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/admin.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Akses ditolak';
    redirect('/auth/login.php');
}

$db = new Database();
$orders = $db->getCollection('orders')
    ->find([], ['sort' => ['created_at' => -1]])
    ->toArray();

include '../../includes/header.php';
?>

<div class="container-fluid py-4">
<div class="row mb-4">
        <div class="col-12">
            <a href="<?= BASE_URL ?>" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h4>Pengelolaan Pesanan</h4>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center">
                            <thead>
                                <tr>
                                    <th class="ps-3" style="width: 10%">ID Pesanan</th>
                                    <th style="width: 15%">Pelanggan</th>
                                    <th style="width: 15%">Total</th>
                                    <th style="width: 8%">Status Pembayaran</th>
                                    <th style="width: 10%">Status Pesanan</th>
                                    <th style="width: 10%">Tanggal</th>
                                    <th style="width: 16%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= substr($order->_id, -8) ?></td>
                                        <td>
                                            <?= htmlspecialchars($order->shipping_info->full_name) ?><br>
                                            <small><?= htmlspecialchars($order->shipping_info->phone) ?></small>
                                        </td>
                                        <td><?= formatCurrency($order->total) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $order->payment_status === 'paid' ? 'success' : 'warning' ?>">
                                                <?= $order->payment_status === 'paid' ? 'Lunas' : 'Belum Lunas' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= getStatusBadgeClass($order->status) ?>">
                                                <?= getStatusText($order->status) ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($order->created_at) ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <form action="update_status.php" method="POST">
                                                    <input type="hidden" name="order_id" value="<?= $order->_id ?>">
                                                    <div class="d-flex gap-2">
                                                        <select name="status" class="form-select form-select-sm" style="min-width: 150px;">
                                                            <option value="pending" <?= $order->status === 'pending' ? 'selected' : '' ?>>
                                                                Menunggu Pembayaran
                                                            </option>
                                                            <option value="processing" <?= $order->status === 'processing' ? 'selected' : '' ?>>
                                                                Diproses
                                                            </option>
                                                            <option value="shipped" <?= $order->status === 'shipped' ? 'selected' : '' ?>>
                                                                Dalam Pengiriman
                                                            </option>
                                                            <option value="delivered" <?= $order->status === 'delivered' ? 'selected' : '' ?>>
                                                                Selesai
                                                            </option>
                                                        </select>
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            Update
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Tambahan CSS untuk memastikan tampilan lebih baik */
.table td, .table th {
    padding: 1rem;
    vertical-align: middle;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Pastikan dropdown dan tombol tidak terpotong */
.form-select, .btn {
    z-index: 1;
    position: relative;
}

/* Berikan ruang yang cukup untuk kolom aksi */
td:last-child {
    min-width: 180px;
}

/* Atur lebar maksimum tabel */
.table {
    width: 100%;
    max-width: 100%;
    margin-bottom: 1rem;
    table-layout: fixed;
}

/* Atur padding untuk konten dalam sel */
.table td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<?php include '../../includes/footer.php'; ?>


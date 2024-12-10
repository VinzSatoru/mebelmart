<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/admin.php';

if (!isAdmin()) {
    $_SESSION['error'] = 'Akses ditolak';
    redirect('/auth/login.php');
}

$db = new Database();
$users = $db->getCollection('users')
    ->find(
        [
            'role' => ['$ne' => 'admin']
        ], 
        [
            'sort' => ['created_at' => -1]
        ]
    )->toArray();

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
                    <h4>Daftar Pelanggan</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center">
                            <thead>
                                <tr>
                                    <th class="ps-3" style="width: 5%">No</th>
                                    <th style="width: 15%">Nama</th>
                                    <th style="width: 20%">Email</th>
                                    <th style="width: 15%">No. Telepon</th>
                                    <th style="width: 25%">Alamat</th>
                                    <th style="width: 10%">Terdaftar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($users as $user): 
                                ?>
                                    <tr>
                                        <td class="ps-3"><?= $no++ ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <?php if (!empty($user->photo)): ?>
                                                        <img src="<?= BASE_URL ?>/uploads/profiles/<?= $user->photo ?>" 
                                                             class="rounded-circle" 
                                                             alt="<?= htmlspecialchars($user->username) ?>">
                                                    <?php else: ?>
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 35px; height: 35px;">
                                                            <span class="text-white"><?= strtoupper(substr($user->username, 0, 1)) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($user->username) ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($user->email) ?></td>
                                        <td><?= htmlspecialchars($user->phone ?? '-') ?></td>
                                        <td>
                                            <?php if (!empty($user->address)): ?>
                                                <?= htmlspecialchars($user->address) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Belum diisi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatDate($user->created_at) ?></td>
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
.table td, .table th {
    padding: 1rem;
    vertical-align: middle;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table {
    width: 100%;
    max-width: 100%;
    margin-bottom: 1rem;
    table-layout: fixed;
}

.table td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.avatar {
    width: 35px;
    height: 35px;
    overflow: hidden;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>

<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Cek jika sudah login
if (isLoggedIn()) {
    redirect('/');
}

// Inisialisasi variabel
$error = '';
$success = '';

// Proses form ketika di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);

    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($phone)) {
        $error = 'Semua field harus diisi!';
    } elseif (!ctype_digit($_POST['phone'])) { // Cek jika input phone hanya angka
        $error = 'Nomor telepon hanya boleh berisi angka!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } elseif (strlen($phone) < 10 || strlen($phone) > 13) {
        $error = 'Nomor telepon harus terdiri dari 10-13 angka!';
    } else {
        try {
            $db = new Database();
            $users = $db->getCollection('users');

            // Cek username sudah ada
            $existingUsername = $users->findOne(['username' => $username]);
            if ($existingUsername) {
                $error = 'Username sudah digunakan!';
            } else {
                // Cek email sudah ada
                $existingEmail = $users->findOne(['email' => $email]);
                if ($existingEmail) {
                    $error = 'Email sudah terdaftar!';
                } else {
                    // Insert user baru
                    $result = $users->insertOne([
                        'username' => $username,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'role' => 'customer',
                        'created_at' => new MongoDB\BSON\UTCDateTime()
                    ]);

                    if ($result->getInsertedCount()) {
                        $success = 'Registrasi berhasil! Silakan login.';
                    } else {
                        $error = 'Terjadi kesalahan saat mendaftar.';
                    }
                }
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>


<!-- Tambahkan Google Fonts di header -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    body {
        background: #1a1c20;
        position: relative;
        font-family: 'Poppins', sans-serif;
        overflow: hidden;
    }

    /* Garis-garis bergerak */
    [class^="line-"] {
        position: absolute;
        left: -50%;
        width: 200%;
        height: 1px;
        background: linear-gradient(
            90deg,
            transparent,
            transparent 30%,
            rgba(45, 52, 54, 0.5) 50%,
            transparent 70%,
            transparent 100%
        );
    }

    /* Garis tipis */
    .line-1 { top: 10%; animation: moveLine 12s linear infinite; }
    .line-2 { top: 20%; animation: moveLineReverse 8s linear infinite; }
    .line-3 { top: 30%; animation: moveLine 15s linear infinite; }
    .line-4 { top: 40%; animation: moveLineReverse 10s linear infinite; }
    .line-5 { top: 50%; animation: moveLine 9s linear infinite; }
    .line-6 { top: 60%; animation: moveLineReverse 14s linear infinite; }
    .line-7 { top: 70%; animation: moveLine 11s linear infinite; }
    .line-8 { top: 80%; animation: moveLineReverse 13s linear infinite; }
    .line-9 { top: 90%; animation: moveLine 16s linear infinite; }

    /* Garis tebal */
    .line-thick-1 {
        top: 25%;
        height: 2px;
        background: linear-gradient(
            90deg,
            transparent,
            transparent 30%,
            rgba(45, 52, 54, 0.3) 50%,
            transparent 70%,
            transparent 100%
        );
        animation: moveLine 20s linear infinite;
    }

    .line-thick-2 {
        top: 75%;
        height: 2px;
        background: linear-gradient(
            90deg,
            transparent,
            transparent 30%,
            rgba(45, 52, 54, 0.3) 50%,
            transparent 70%,
            transparent 100%
        );
        animation: moveLineReverse 20s linear infinite;
    }

    /* Garis diagonal */
    .line-diagonal-1 {
        top: 0;
        width: 150%;
        transform: rotate(45deg);
        transform-origin: 0 0;
        animation: moveLineDiagonal 25s linear infinite;
    }

    .line-diagonal-2 {
        bottom: 0;
        width: 150%;
        transform: rotate(-45deg);
        transform-origin: 100% 100%;
        animation: moveLineDiagonalReverse 25s linear infinite;
    }

    @keyframes moveLine {
        0% { transform: translateX(-50%); }
        100% { transform: translateX(0%); }
    }

    @keyframes moveLineReverse {
        0% { transform: translateX(0%); }
        100% { transform: translateX(-50%); }
    }

    @keyframes moveLineDiagonal {
        0% { transform: rotate(45deg) translateX(-50%); }
        100% { transform: rotate(45deg) translateX(0%); }
    }

    @keyframes moveLineDiagonalReverse {
        0% { transform: rotate(-45deg) translateX(0%); }
        100% { transform: rotate(-45deg) translateX(-50%); }
    }

    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease;
        background: #fff;
        width: 100%;
        max-width: 800px; /* Tambah max-width */
        margin: 0 auto; /* Tengahkan card */
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card-body {
        padding: 2rem 5rem !important; /* Diperbesar dari 4rem */
    }

    .logo-wrapper {
        background: linear-gradient(45deg, #1a1c20 0%, #2d3436 100%);
        margin: -3rem -5rem 2rem -5rem; /* Sesuaikan dengan padding card-body */
        padding-top: 2rem;
        padding-bottom: 0.25rem;
        /* Sedikit lebih besar */
        text-align: center;
    }

    .logo-wrapper img {
        max-width: 60px; /* Diperbesar dari 150px */
    }

    h4 {
        color: #fff;
        font-weight: 600;
        margin-top: 1rem;
        font-size: 1.5rem;
    }
    .mb-4 {
        margin-bottom: -0.5rem !important;
        width: 100%;
    }
    .form-label {
        font-weight: 500;
        color: #1a1c20;
    }

    .form-control {
        border-radius: 10px;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        font-size: 0.95rem;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #1a1c20;
        box-shadow: 0 0 0 0.2rem rgba(26, 28, 32, 0.1);
    }

    .input-group-text {
        border: 2px solid #e9ecef;
        background-color: #f8f9fa;
        color: #1a1c20;
    }

    .btn-primary {
        margin-top: 1rem;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        color: #ffffff;
        border-radius: 8px;
        background: linear-gradient(45deg, #1a1c20 0%, #2d3436 100%);
        border: none;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(26, 28, 32, 0.3);
        background: linear-gradient(45deg, #2d3436 0%, #1a1c20 100%);
    }

    .alert {
        border-radius: 8px;
        border: none;
    }

    .login-link {
        color: #1a1c20;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .login-link:hover {
        color: #2d3436;
        text-decoration: none;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeIn 0.6s ease-out;
    }
</style>

<!-- Tambahkan elemen untuk garis-garis -->
<div class="line-1"></div>
<div class="line-2"></div>
<div class="line-3"></div>
<div class="line-4"></div>
<div class="line-5"></div>
<div class="line-6"></div>
<div class="line-7"></div>
<div class="line-8"></div>
<div class="line-9"></div>
<div class="line-thick-1"></div>
<div class="line-thick-2"></div>
<div class="line-diagonal-1"></div>
<div class="line-diagonal-2"></div>

<div class="login-container">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="logo-wrapper">
                            <img src="<?= BASE_URL ?>/assets/images/logogin.png" 
                                 alt="MebelMart Logo" 
                                 class="img-fluid">
                            <h4>Daftar Akun</h4>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger mx-3 mt-3"><?= $error ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success mx-3 mt-3"><?= $success ?></div>
                            <div class="text-center mb-3" style="margin-top: 1rem;">
                                <a href="login.php" class="btn btn-primary">Login Sekarang</a>
                            </div>
                        <?php else: ?>
                            <div class="px-3">
                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="mb-4">
                                        <label for="username" class="form-label">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-person"></i>
                                            </span>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   placeholder="Masukkan username" required minlength="3">
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-envelope"></i>
                                            </span>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   placeholder="Masukkan email" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="email" class="form-label">Nomor Telepon</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-envelope"></i>
                                            </span>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   placeholder="Masukkan nomor telepon anda" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-lock"></i>
                                            </span>
                                            <input type="password" class="form-control" id="password" name="password" 
                                                   placeholder="Masukkan password" required minlength="6">
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-lock-fill"></i>
                                            </span>
                                            <input type="password" class="form-control" id="confirm_password" 
                                                   name="confirm_password" placeholder="Konfirmasi password" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 mb-4">
                                        <i class="bi bi-person-plus me-2"></i>Daftar
                                    </button>
                                </form>
                                
                                <div class="text-center">
                                    <p class="mb-0">Sudah punya akun? 
                                        <a href="login.php" class="login-link">Login di sini</a>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Validation Script -->
<script>
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>
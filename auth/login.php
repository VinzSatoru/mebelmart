<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (isLoggedIn()) {
    redirect('/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = filter_input(INPUT_POST, 'login_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = $_POST['password'];

    $db = new Database();
    $users = $db->getCollection('users');

    // Cek login menggunakan username atau email
    $user = $users->findOne([
        '$or' => [
            ['username' => $login_id],
            ['email' => $login_id]
        ]
    ]);

    if ($user && password_verify($password, $user->password)) {
        $_SESSION['user_id'] = (string) $user->_id;
        $_SESSION['username'] = $user->username;
        $_SESSION['is_admin'] = isset($user->is_admin) && $user->is_admin === true;
        
        // Debug info
        var_dump($_SESSION);
        
        if ($_SESSION['is_admin']) {
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
        } else {
            header('Location: ' . BASE_URL);
        }
        exit;
    } else {
        $error = '* Username/Email atau password salah!';
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
        position: relative;
        z-index: 1;
    }

    .card {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        transition: transform 0.3s ease;
        background: rgba(255, 255, 255, 0.95);
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
        position: relative;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(26, 28, 32, 0.4),
                   0 0 20px rgba(45, 52, 54, 0.2);
    }

    .card-body {
        padding: 3rem 5rem !important;
    }

    .logo-wrapper {
        background: linear-gradient(45deg, #1a1c20 0%, #2d3436 100%);
        margin: -3rem -5rem 2rem -5rem;
        padding: 2.5rem;
        text-align: center;
    }

    .logo-wrapper img {
        max-width: 180px;
    }

    h4 {
        color: #fff;
        font-weight: 600;
        margin-top: 1rem;
        font-size: 1.5rem;
    }

    .form-label {
        font-weight: 500;
        color: #1a1c20;
        margin-top: 1rem;
    }

    .form-control {
        border-radius: 8px;
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
        padding: 0.75rem 1.5rem;
        margin-top: 1rem;
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

    .btn-outline-secondary {
        border: 2px solid #e9ecef;
        color: #1a1c20;
    }

    .btn-outline-secondary:hover {
        background-color: #f8f9fa;
        color: #1a1c20;
        border-color: #1a1c20;
    }

    .alert {
        border-radius: 8px;
        color: #fe2e2e;
        font-size: 12px;
        border: none;
    }

    .register-link {
        color: #1a1c20;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .register-link:hover {
        color: #2d3436;
        text-decoration: none;
    }

    /* Animasi tambahan */
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
                            <h4>Selamat Datang</h4>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-4">
                                <label for="login_id" class="form-label">Username atau Email</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="login_id" 
                                           name="login_id" 
                                           placeholder="Masukkan username atau email"
                                           required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label" style="">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Masukkan password"
                                           required>
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-4">
                                <i class="bi bi-box-arrow-in-right me-2 text-white"></i>Masuk
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Belum punya akun? 
                                <a href="register.php" class="register-link">Daftar di sini</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});
</script>


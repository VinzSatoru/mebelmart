<?php
$current_page = basename($_SERVER['PHP_SELF']);
function isActive($page) {
    global $current_page;
    return ($current_page == $page) ? 'active' : '';
}

$db = new Database();
$categories = $db->getCollection('categories')->find([], [
    'sort' => ['name' => 1]
])->toArray();

$cartCount = 0;
if (isLoggedIn()) {
    $user_id = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
    $cartCount = $db->getCollection('cart')->countDocuments(['user_id' => $user_id]);
}

$userPhoto = null;
if (isLoggedIn()) {
    $db = new Database();
    $user = $db->getCollection('users')->findOne([
        '_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])
    ]);
    $userPhoto = $user->photo ?? null;
}
?>



<!-- Top Navbar -->
<nav class="navbar navbar-expand navbar-light bg-white fixed-top shadow-sm py-2">
    <div class="container-fluid px-4">
        <!-- Sidebar Toggle -->
        <button class="btn btn-link text-dark me-3" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>

        <!-- Logo for Mobile -->
        <a class="navbar-brand" href="<?= BASE_URL ?>">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="MebelMart" height="35">
        </a>

        <!-- Menu Navigasi -->
        <div class="d-flex align-items-center">
            <a class="nav-link me-3" href="<?= BASE_URL ?>/#products">
                <i class="bi bi-box-seam me-2"></i> Produk
            </a>
            <a class="nav-link me-3" href="<?= BASE_URL ?>/#categories">
                <i class="bi bi-grid me-2"></i> Kategori
            </a>
            <a class="nav-link" href="<?= BASE_URL ?>/about.php">
                <i class="bi bi-info-circle me-2"></i> Tentang Kami
            </a>
        </div>

        <!-- Search Form -->
        <form class="d-flex flex-grow-1 mx-3" action="<?= BASE_URL ?>/products/search.php" method="GET">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari produk..." 
                       name="q" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar bg-white shadow-sm" id="sidebar">
    <!-- User Section -->
    <div class="p-3 border-bottom">
        <?php if (isLoggedIn()): ?>
            <div class="sidebar-header p-3 text-center border-bottom">
            <?php if ($userPhoto): ?>
                <img src="<?= BASE_URL ?>/uploads/profiles/<?= $userPhoto ?>" 
                     alt="Profile" 
                     class="rounded-circle mb-1"
                     style="width: 60px; height: 60px; object-fit: cover;">
            <?php else: ?>
                <img class="center" src="<?= BASE_URL ?>/assets/images/default-profile.jpg" 
                     alt="Profile" 
                     class="rounded-circle mb-1"
                     style="width: 32px; height: 32px; object-fit: cover;">
            <?php endif; ?>
                </div>
                 <div>
                <span class="small text-center" style="font-size: 11px; white-space: nowrap;">
                    <h6 class="mb-0"><?= $_SESSION['username'] ?></h6>
                </span>
                </div>
             
        <?php else: ?>
            <div class="d-grid gap-2">
                <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                </a>
                <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i> Daftar
                </a>
            </div>
        <?php endif; ?>
    </div>

   <!-- Menu Navigation -->
<div class="sidebar-menu p-3">
    <ul class="nav flex-column">
        <!-- Menu Umum -->
        <li class="nav-item">
            <a class="nav-link <?= isActive('index.php') ?>" href="<?= BASE_URL ?>">
                <i class="bi bi-house-door me-2"></i> Beranda
            </a>
        </li>

        <?php if (isAdmin()): ?>
            <!-- Menu Khusus Admin -->
             <!-- Tambahkan di dropdown menu user -->
             <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/profile">
                        <i class="bi bi-person me-2"></i> Profil Saya
                    </a>
                </li>
            <li class="nav-item">
                <a class="nav-link <?= isActive('admin/dashboard.php') ?>" 
                   href="<?= BASE_URL ?>/admin/dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard Admin
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= isActive('admin/orders') ?>" 
                   href="<?= BASE_URL ?>/admin/orders">
                    <i class="bi bi-cart-check me-2"></i> Daftar Pesanan
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= isActive('admin/categories') ?>" 
                   href="<?= BASE_URL ?>/admin/categories">
                    <i class="bi bi-grid me-2"></i> Edit Kategori
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= isActive('admin/products') ?>" 
                   href="<?= BASE_URL ?>/admin/products">
                    <i class="bi bi-box-seam me-2"></i> Edit Produk
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= isActive('admin/users') ?>" 
                   href="<?= BASE_URL ?>/admin/users">
                    <i class="bi bi-people me-2"></i> Pengguna
                </a>
            </li>

        <?php else: ?>
            <!-- Menu Khusus Customer -->
            <?php if (isLoggedIn()): ?>
                <!-- Tambahkan di dropdown menu user -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/profile">
                        <i class="bi bi-person me-2"></i> Profil Saya
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/products/index.php">
                        <i class="bi bi-box-seam me-2"></i> 
                        Produk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/cart/index.php">
                        <i class="bi bi-cart3 me-2"></i> 
                        Keranjang
                        <?php if ($cartCount > 0): ?>
                            <span class="badge bg-danger ms-2"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('orders') ?>" href="<?= BASE_URL ?>/orders">
                        <i class="bi bi-box-seam me-2"></i> Pesanan Saya
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/user/wishlist.php">
                        <i class="bi bi-heart me-2"></i> Wishlist
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Menu Login/Logout -->
        <?php if (isLoggedIn()): ?>
            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="<?= BASE_URL ?>/auth/logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i> Keluar
                </a>
            </li>
        <?php else: ?>
            <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/products/index.php">
                        <i class="bi bi-box-seam me-2"></i> 
                        Produk
                    </a>
                </li>
        <?php endif; ?>
    </ul>
</div>
  
         
        </ul>
    </div>
</div>

<!-- Main Content Wrapper -->
<div class="content-wrapper" id="content">
    <!-- Content akan di-inject di sini -->

<!-- Bagian HTML tetap sama, kita fokus pada CSS dan JavaScript -->

<style>
:root {
    --sidebar-width: 280px;
    --top-navbar-height: 60px;
}

body {
    padding-top: var(--top-navbar-height);
    overflow-x: hidden; /* Prevent horizontal scroll */
}

/* Sidebar Styles */
.sidebar {
    position: fixed;
    top: var(--top-navbar-height);
    left: -280px; /* Default hidden */
    width: var(--sidebar-width);
    height: calc(100vh - var(--top-navbar-height));
    overflow-y: auto;
    transition: all 0.3s ease-in-out;
    z-index: 1040;
}

.sidebar.show {
    left: 0; /* Show sidebar */
}

/* Content Wrapper */
.content-wrapper {
    margin-left: 0; /* Default no margin */
    transition: all 0.3s ease-in-out;
    width: 100%;
}

.content-wrapper.shifted {
    margin-left: var(--sidebar-width);
}

/* Overlay */
.sidebar-overlay {
    position: fixed;
    top: var(--top-navbar-height);
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1030;
    display: none;
}

.sidebar-overlay.show {
    display: block;
}

/* Responsive */
@media (min-width: 992px) {
    .content-wrapper.shifted {
        margin-left: var(--sidebar-width);
    }
}

@media (max-width: 991.98px) {
    .content-wrapper.shifted {
        margin-left: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const sidebarToggle = document.getElementById('sidebarToggle');
    let overlay;

    // Create overlay if it doesn't exist
    if (!document.querySelector('.sidebar-overlay')) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    } else {
        overlay = document.querySelector('.sidebar-overlay');
    }

    // Toggle sidebar function
    function toggleSidebar() {
        sidebar.classList.toggle('show');
        content.classList.toggle('shifted');
        overlay.classList.toggle('show');
        
        // Toggle aria-expanded
        const isExpanded = sidebar.classList.contains('show');
        sidebarToggle.setAttribute('aria-expanded', isExpanded);
    }

    // Event listeners
    sidebarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        toggleSidebar();
    });

    // Close sidebar when clicking overlay
    overlay.addEventListener('click', function() {
        toggleSidebar();
    });

    // Close sidebar when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('show')) {
            toggleSidebar();
        }
    });

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 991.98) {
                overlay.classList.remove('show');
            }
        }, 250);
    });
});

</script>
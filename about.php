<?php
require_once 'config/config.php';
include 'includes/header.php';
?>

<style>
    .about-hero {
        background: linear-gradient(45deg, #1a1c20 0%, #2d3436 100%);
        padding: 100px 0;
        color: white;
    }

    .about-hero img {
        max-width: 80%;
        margin: 0 auto;
        display: block;
    }

    .about-section {
        padding: 80px 0;
    }

    .about-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        height: 100%;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .about-card:hover {
        transform: translateY(-5px);
    }

    .about-icon {
        font-size: 2.5rem;
        color: #1a1c20;
        margin-bottom: 20px;
    }

    .team-member {
        text-align: center;
        margin-bottom: 30px;
    }

    .team-member img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 15px;
        border: 5px solid #f8f9fa;
    }

    .counter-box {
        text-align: center;
        padding: 20px;
        background: white;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    .counter-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #1a1c20;
    }
</style>

<!-- Hero Section -->
<div class="about-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold mb-4">Tentang MebelMart</h1>
                <p class="lead">Kami adalah destinasi terpercaya untuk furniture berkualitas dengan desain modern dan klasik yang akan memperindah ruangan Anda.</p>
            </div>
            <div class="col-md-6 text-center">
                <img src="<?= BASE_URL ?>/assets/images/logogin.png" alt="About MebelMart" class="img-fluid rounded-3 mx-auto d-block">
            </div>
        </div>
    </div>
</div>

<!-- Visi Misi Section -->
<div class="about-section bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="about-card">
                    <div class="about-icon">
                        <i class="bi bi-eye"></i>
                    </div>
                    <h3>Visi</h3>
                    <p>Menjadi perusahaan furniture terdepan yang menghadirkan produk berkualitas tinggi dengan harga terjangkau untuk memenuhi kebutuhan setiap rumah di Indonesia.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="about-card">
                    <div class="about-icon">
                        <i class="bi bi-bullseye"></i>
                    </div>
                    <h3>Misi</h3>
                    <ul>
                        <li>Menyediakan produk furniture berkualitas dengan harga kompetitif</li>
                        <li>Memberikan pelayanan terbaik kepada pelanggan</li>
                        <li>Mengembangkan inovasi dalam desain dan produksi</li>
                        <li>Mendukung industri furniture lokal</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Keunggulan Section -->
<div class="about-section">
    <div class="container">
        <h2 class="text-center mb-5">Keunggulan Kami</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="about-card text-center">
                    <div class="about-icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <h4>Kualitas Terjamin</h4>
                    <p>Setiap produk melalui quality control ketat untuk memastikan kualitas terbaik.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="about-card text-center">
                    <div class="about-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h4>Pengiriman Cepat</h4>
                    <p>Layanan pengiriman cepat dan aman ke seluruh Indonesia.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="about-card text-center">
                    <div class="about-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h4>Layanan 24/7</h4>
                    <p>Tim customer service kami siap membantu Anda 24 jam setiap hari.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Section -->
<div class="about-section bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="counter-box">
                    <div class="counter-number">5000+</div>
                    <div>Produk Terjual</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="counter-box">
                    <div class="counter-number">1000+</div>
                    <div>Pelanggan Puas</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="counter-box">
                    <div class="counter-number">500+</div>
                    <div>Produk Tersedia</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="counter-box">
                    <div class="counter-number">50+</div>
                    <div>Kota Terjangkau</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 
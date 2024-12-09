<!-- Footer -->
<footer class="footer mt-5">
    <div class="footer-top py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h4 class="mb-4">MebelMart</h4>
                        <p class="mb-4">Temukan berbagai koleksi mebel berkualitas untuk melengkapi ruangan Anda dengan desain yang elegan dan modern.</p>
                        <div class="social-links">
                            <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="social-link"><i class="bi bi-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h4 class="mb-4">Tautan Cepat</h4>
                        <ul class="footer-links">
                            <li><a href="<?= BASE_URL ?>">Beranda</a></li>
                            <li><a href="<?= BASE_URL ?>/products">Produk</a></li>
                            <li><a href="<?= BASE_URL ?>/cart">Keranjang</a></li>
                            <li><a href="<?= BASE_URL ?>/orders">Pesanan</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h4 class="mb-4">Kontak Kami</h4>
                        <ul class="footer-contact">
                            <li><i class="bi bi-geo-alt-fill"></i> Jl. Sunan Mantingan No. 51, Tahunan, Jepara</li>
                            <li><i class="bi bi-telephone-fill"></i> +62 857-4145-8614</li>
                            <li><i class="bi bi-envelope-fill"></i> mebelmart@gmail.com</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom py-3">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; <?= date('Y') ?> MebelMart. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

.footer {
    background: linear-gradient(45deg, #1a1c20 0%, #2d3436 100%);
    color: #ffffff;
    font-family: 'Poppins', sans-serif;
}

.footer-widget h4 {
    color: #ffffff;
    font-weight: 600;
    font-size: 1.5rem;
    position: relative;
    padding-bottom: 15px;
}

.footer-widget h4::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 50px;
    height: 2px;
    background: #ffffff;
}

.footer-widget p {
    color: #b0b0b0;
    line-height: 1.8;
}

.social-links {
    display: flex;
    gap: 15px;
}

.social-link {
    color: #ffffff;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.social-link:hover {
    color: #b0b0b0;
    transform: translateY(-3px);
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: #b0b0b0;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
}

.footer-links a:hover {
    color: #ffffff;
    transform: translateX(5px);
}

.footer-contact {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-contact li {
    color: #b0b0b0;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.footer-contact li i {
    color: #ffffff;
    font-size: 1.1rem;
}

.footer-bottom {
    background: rgba(0, 0, 0, 0.2);
    font-size: 0.9rem;
    color: #b0b0b0;
}

@media (max-width: 768px) {
    .footer-widget {
        text-align: center;
        margin-bottom: 30px;
    }

    .footer-widget h4::after {
        left: 50%;
        transform: translateX(-50%);
    }

    .social-links {
        justify-content: center;
    }

    .footer-contact li {
        justify-content: center;
    }
}
</style>

</body>
</html>
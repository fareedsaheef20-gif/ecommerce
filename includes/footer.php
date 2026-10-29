<?php
// Footer include
?>
<!-- Footer -->
<footer class="footer mt-5">
    <div class="footer-top">
        <div class="container">
            <div class="row g-4">
                <!-- Brand Column -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-brand">
                        <a href="<?php echo SITE_URL; ?>/index.php" class="footer-logo">
                            <span class="brand-icon"><i class="fas fa-bolt"></i></span>
                            <span>Ashrif<span class="brand-accent">Mobile</span></span>
                        </a>
                        <p class="mt-3">Your trusted destination for premium electronics. Quality products, unbeatable prices.</p>
                        <div class="social-links">
                            <a href="#" class="social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">Quick Links</h6>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/products.php">Shop</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/about.php">About Us</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact.php">Contact</a></li>
                    </ul>
                </div>

                <!-- Categories -->
                <div class="col-lg-3 col-md-6 col-6">
                    <h6 class="footer-heading">Categories</h6>
                    <ul class="footer-links">
                        <?php foreach (array_slice($categories, 0, 6) as $cat): ?>
                            <li><a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $cat['slug']; ?>"><?php echo htmlspecialchars($cat['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="footer-heading">Contact Us</h6>
                    <ul class="footer-contact">
                        <li><i class="fas fa-map-marker-alt"></i> 45 Galle Road, Colombo 03, Sri Lanka</li>
                        <li><i class="fas fa-phone"></i> +94 11 234 5678</li>
                        <li><i class="fas fa-envelope"></i> info@electrohub.com</li>
                        <li><i class="fas fa-clock"></i> Mon-Sat: 9AM - 8PM</li>
                    </ul>
                    <!-- Payment Methods -->
                    <div class="payment-methods mt-3">
                        <span class="me-2"><i class="fab fa-cc-visa fa-2x"></i></span>
                        <span class="me-2"><i class="fab fa-cc-mastercard fa-2x"></i></span>
                        <span class="me-2"><i class="fab fa-cc-paypal fa-2x"></i></span>
                        <span><i class="fas fa-money-bill-wave fa-2x"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center py-3">
                <p class="mb-0 text-muted small">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                <div class="mt-2 mt-md-0">
                    <a href="#" class="text-muted small me-3">Privacy Policy</a>
                    <a href="#" class="text-muted small me-3">Terms of Service</a>
                    <a href="#" class="text-muted small">Return Policy</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top -->
<button class="back-to-top" id="backToTop" aria-label="Back to top">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>

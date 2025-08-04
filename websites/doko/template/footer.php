    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <!-- Company Info -->
                <div class="footer-section">
                    <h3>DOKO</h3>
                    <p>Nepal's most trusted online grocery marketplace, connecting families with fresh, authentic products from local suppliers across the country.</p>
                    <div class="social-links">
                        <a href="https://facebook.com/doko" class="social-link" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://instagram.com/doko" class="social-link" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="products.php">All Products</a></li>
                        <li><a href="offers.php">Special Offers</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div class="footer-section">
                    <h3>Customer Service</h3>
                    <ul class="footer-links">
                        <li><a href="help.php">Help Center</a></li>
                        <li><a href="returns.php">Returns & Refunds</a></li>
                        <li><a href="shipping.php">Shipping Info</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>New Baneshwor, Kathmandu</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+977-9851234567</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>care@doko.com.np</span>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> DOKO. All rights reserved. Made with ❤️ in Nepal.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="js/main.js"></script>
    
    <?php if (isset($GLOBALS['additional_js']) && !empty($GLOBALS['additional_js'])): ?>
        <?php foreach ($GLOBALS['additional_js'] as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($GLOBALS['inline_js']) && !empty($GLOBALS['inline_js'])): ?>
        <script>
            <?php echo $GLOBALS['inline_js']; ?>
        </script>
    <?php endif; ?>
</body>
</html>

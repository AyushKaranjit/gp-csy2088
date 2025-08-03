    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-brand">
                        <img src="https://img.icons8.com/fluency/48/shopping-bag.png" alt="DOKO">
                        <span class="footer-brand-text">DOKO</span>
                    </div>
                    <p class="footer-description">Nepal's freshest grocery marketplace</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h5>Customer Care</h5>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Track Order</a></li>
                        <li><a href="#">Returns</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h5>DOKO</h5>
                    <ul class="footer-links">
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h5>Newsletter</h5>
                    <p class="newsletter-text">Get deals & offers</p>
                    <div class="newsletter-form">
                        <input type="email" class="newsletter-input" placeholder="Email">
                        <button type="submit" class="newsletter-btn">Subscribe</button>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> DOKO. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

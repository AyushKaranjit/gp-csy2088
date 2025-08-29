<?php
/**
 * DOKO E-Commerce Website - Footer Template
 *
 * This website was developed as part of an academic project for educational purposes.
 * All code was written by Team Graduation to demonstrate web development skills.
 *
 * @author Team Graduation
 * @version 1.0
 * @date 2025
 */

// Detect admin context (kept for future conditional logic if needed)
$is_admin_context = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false;
// Unified footer styling for both storefront and admin
$footer_class = 'footer';
?>
    <!-- Footer -->
    <footer class="<?php echo $footer_class; ?>">
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
                <p>&copy; <?php echo date('Y'); ?> DOKO. All rights reserved. <span style="opacity:.8">Made in Nepal.</span></p>
                <!-- Unified footer: removed separate admin version line to match home page exactly -->
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <?php
    // Determine the correct path to JS files based on current location
    $current_script = $_SERVER['SCRIPT_NAME'];
    $js_path = 'js/';
    
    // Check if we're in a subdirectory
    if (strpos($current_script, '/api/') !== false) {
        $js_path = '../js/';
    } elseif (strpos($current_script, '/admin/') !== false) {
        // Handle admin subdirectories like /admin/dashboard/, /admin/products/, etc.
        $js_path = '../../js/';
    } elseif (strpos($current_script, '/manager/') !== false) {
        // Handle manager subdirectories like /manager/dashboard/, /manager/products/, etc.
        $js_path = '../../js/';
    }
    
    // Verify that the JavaScript files actually exist
    // Use DOCUMENT_ROOT to resolve existence to web root /js directory for consistency
    $doc_root = rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__), '/\\');
    $main_js_exists = file_exists($doc_root . '/js/main.js') || file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $js_path . 'main.js');
    $mobile_js_exists = file_exists($doc_root . '/js/mobile-nav.js') || file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $js_path . 'mobile-nav.js');
    $product_actions_exists = file_exists($doc_root . '/js/product-actions.js') || file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $js_path . 'product-actions.js');
    ?>
    
    <?php if ($product_actions_exists): ?>
    <script src="<?php echo $js_path; ?>product-actions.js?v=<?php echo date('YmdHis'); ?>" onerror="console.error('Failed to load product-actions.js');"></script>
    <?php else: ?>
    <script>console.warn('product-actions.js not found at path: <?php echo $js_path; ?>product-actions.js');</script>
    <?php endif; ?>

    <?php if ($main_js_exists): ?>
    <script src="<?php echo $js_path; ?>main.js?v=<?php echo date('YmdHis'); ?>" onerror="console.error('main.js failed to load');"></script>
    <?php else: ?>
    <script>console.error('main.js not found at path: <?php echo $js_path; ?>main.js');</script>
    <?php endif; ?>
    
    <?php if ($mobile_js_exists): ?>
    <script src="<?php echo $js_path; ?>mobile-nav.js?v=<?php echo date('YmdHis'); ?>" onerror="console.log('Mobile nav script failed to load, using fallback')"></script>
    <?php else: ?>
    <script>console.warn('mobile-nav.js not found at path: <?php echo $js_path; ?>mobile-nav.js, using fallback');</script>
    <?php endif; ?>
    
    <!-- Fallback mobile navigation -->
    <script>
    // Fallback mobile navigation in case mobile-nav.js fails to load
    document.addEventListener('DOMContentLoaded', function() {
        const mobileToggle = document.getElementById('mobile-menu-toggle');
        const navList = document.getElementById('nav-list');
        
        if (mobileToggle && navList) {
            mobileToggle.addEventListener('click', function() {
                navList.classList.toggle('active');
                const icon = this.querySelector('i');
                if (icon) {
                    if (navList.classList.contains('active')) {
                        icon.className = 'fas fa-times';
                    } else {
                        icon.className = 'fas fa-bars';
                    }
                }
            });
            
            // Close mobile menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!mobileToggle.contains(e.target) && !navList.contains(e.target)) {
                    navList.classList.remove('active');
                    const icon = mobileToggle.querySelector('i');
                    if (icon) {
                        icon.className = 'fas fa-bars';
                    }
                }
            });
        }
    });
    </script>
    
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

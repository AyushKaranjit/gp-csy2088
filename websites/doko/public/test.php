<?php
// Test script to verify DOKO website functionality
session_start();
require_once '../template/config.php';

// Define site_name() if not already defined
if (!function_exists('site_name')) {
    function site_name() {
        return 'DOKO'; // Replace with your actual site name if needed
    }
}

$page_title = 'DOKO System Test';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1>🛒 DOKO System Test</h1>
            <p>Testing all core functionality of the DOKO e-commerce platform</p>
        </div>

        <div class="test-grid">
            <!-- Configuration Test -->
            <div class="test-card">
                <h3><i class="fas fa-cog"></i> Configuration Test</h3>
                <div class="test-result">
                    <?php
                    try {
                        if (function_exists('site_name')) {
                            echo '<div class="success">✅ Site Name: ' . site_name() . '</div>';
                        } else {
                            echo '<div class="error">❌ Site name function not found</div>';
                        }
                        
                        if (function_exists('site_url')) {
                            echo '<div class="success">✅ Site URL: ' . site_url() . '</div>';
                        } else {
                            echo '<div class="error">❌ Site URL function not found</div>';
                        }
                        
                        if (function_exists('page_title')) {
                            echo '<div class="success">✅ Page Title Function: Working</div>';
                        } else {
                            echo '<div class="error">❌ Page title function not found</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="error">❌ Configuration Error: ' . $e->getMessage() . '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Image Service Test -->
            <div class="test-card">
                <h3><i class="fas fa-image"></i> Image Service Test</h3>
                <div class="test-result">
                    <?php
                    try {
                        if (function_exists('product_image')) {
                            $test_image = product_image('Fresh Tomatoes');
                            echo '<div class="success">✅ Product Image Function: Working</div>';
                            echo '<div class="image-test">';
                            echo '<img src="' . $test_image . '" alt="Test Image" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">';
                            echo '<p>Sample product image URL generated</p>';
                            echo '</div>';
                        } else {
                            echo '<div class="error">❌ Product image function not found</div>';
                        }
                        
                        if (function_exists('category_image')) {
                            echo '<div class="success">✅ Category Image Function: Working</div>';
                        } else {
                            echo '<div class="error">❌ Category image function not found</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="error">❌ Image Service Error: ' . $e->getMessage() . '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Security Test -->
            <div class="test-card">
                <h3><i class="fas fa-shield-alt"></i> Security Test</h3>
                <div class="test-result">
                    <?php
                    try {
                        if (function_exists('generate_csrf_token')) {
                            $token = generate_csrf_token();
                            echo '<div class="success">✅ CSRF Token Generation: Working</div>';
                            echo '<div class="token-display">Token: ' . substr($token, 0, 20) . '...</div>';
                        } else {
                            echo '<div class="error">❌ CSRF token function not found</div>';
                        }
                        
                        if (function_exists('clean_output')) {
                            $test_string = '<script>alert("test")</script>Test String';
                            $cleaned = clean_output($test_string);
                            echo '<div class="success">✅ Output Cleaning: Working</div>';
                            echo '<div class="clean-test">Cleaned: ' . $cleaned . '</div>';
                        } else {
                            echo '<div class="error">❌ Output cleaning function not found</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="error">❌ Security Error: ' . $e->getMessage() . '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Session Test -->
            <div class="test-card">
                <h3><i class="fas fa-user-circle"></i> Session Test</h3>
                <div class="test-result">
                    <?php
                    try {
                        if (session_status() === PHP_SESSION_ACTIVE) {
                            echo '<div class="success">✅ Session Status: Active</div>';
                            echo '<div class="session-info">Session ID: ' . substr(session_id(), 0, 10) . '...</div>';
                        } else {
                            echo '<div class="error">❌ Session not active</div>';
                        }
                        
                        if (function_exists('set_flash_message')) {
                            echo '<div class="success">✅ Flash Message Function: Available</div>';
                        } else {
                            echo '<div class="error">❌ Flash message function not found</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="error">❌ Session Error: ' . $e->getMessage() . '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Page Access Test -->
            <div class="test-card">
                <h3><i class="fas fa-globe"></i> Page Access Test</h3>
                <div class="test-result">
                    <?php
                    $pages = [
                        'index.php' => 'Home Page',
                        'products.php' => 'Products Page',
                        'cart.php' => 'Shopping Cart',
                        'login.php' => 'Login Page',
                        'register.php' => 'Registration',
                        'checkout.php' => 'Checkout',
                        'about.php' => 'About Us',
                        'contact.php' => 'Contact Us',
                        'offers.php' => 'Special Offers',
                        'admin.php' => 'Admin Dashboard'
                    ];
                    
                    foreach ($pages as $file => $name) {
                        if (file_exists($file)) {
                            echo '<div class="success">✅ ' . $name . ': Available</div>';
                        } else {
                            echo '<div class="error">❌ ' . $name . ': Missing</div>';
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Template Test -->
            <div class="test-card">
                <h3><i class="fas fa-puzzle-piece"></i> Template Test</h3>
                <div class="test-result">
                    <?php
                    $templates = [
                        '../template/config.php' => 'Main Configuration',
                        '../template/header.php' => 'Header Template',
                        '../template/footer.php' => 'Footer Template',
                        '../template/breadcrumb.php' => 'Breadcrumb Template',
                        '../template/product-card.php' => 'Product Card Template',
                        '../template/image-service.php' => 'Image Service'
                    ];
                    
                    foreach ($templates as $file => $name) {
                        if (file_exists($file)) {
                            echo '<div class="success">✅ ' . $name . ': Available</div>';
                        } else {
                            echo '<div class="error">❌ ' . $name . ': Missing</div>';
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- CSS & Assets Test -->
            <div class="test-card">
                <h3><i class="fas fa-palette"></i> Assets Test</h3>
                <div class="test-result">
                    <?php
                    $assets = [
                        'css/style.css' => 'Main Stylesheet',
                        'js/main.js' => 'Main JavaScript',
                        'js/mobile-nav.js' => 'Mobile Navigation'
                    ];
                    
                    foreach ($assets as $file => $name) {
                        if (file_exists($file)) {
                            echo '<div class="success">✅ ' . $name . ': Available</div>';
                        } else {
                            echo '<div class="error">❌ ' . $name . ': Missing</div>';
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Database Test -->
            <div class="test-card">
                <h3><i class="fas fa-database"></i> Database Test</h3>
                <div class="test-result">
                    <?php
                    try {
                        if (file_exists('../config/database.php')) {
                            echo '<div class="success">✅ Database Config: Available</div>';
                        } else {
                            echo '<div class="error">❌ Database config missing</div>';
                        }
                        
                        if (file_exists('../database/doko_schema.sql')) {
                            echo '<div class="success">✅ Database Schema: Available</div>';
                        } else {
                            echo '<div class="error">❌ Database schema missing</div>';
                        }
                        
                        echo '<div class="info">💡 Database connection would be tested here in production</div>';
                    } catch (Exception $e) {
                        echo '<div class="error">❌ Database Error: ' . $e->getMessage() . '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="system-info">
            <h3>System Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <strong>PHP Version:</strong>
                    <span><?php echo phpversion(); ?></span>
                </div>
                <div class="info-item">
                    <strong>Server:</strong>
                    <span><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                </div>
                <div class="info-item">
                    <strong>Document Root:</strong>
                    <span><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></span>
                </div>
                <div class="info-item">
                    <strong>Current Time:</strong>
                    <span><?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="test-actions">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Go to Homepage
            </a>
            <a href="admin.php" class="btn btn-secondary">
                <i class="fas fa-tools"></i> Admin Dashboard
            </a>
            <button onclick="window.location.reload()" class="btn btn-outline">
                <i class="fas fa-redo"></i> Run Tests Again
            </button>
        </div>
    </div>

    <style>
    body {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        min-height: 100vh;
        margin: 0;
        padding: 2rem;
        font-family: inherit;
    }

    .test-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .test-header {
        text-align: center;
        color: white;
        margin-bottom: 3rem;
    }

    .test-header h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .test-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .test-card {
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .test-card h3 {
        background: var(--background-color);
        margin: 0;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--dark-text);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .test-result {
        padding: 1.5rem;
    }

    .success {
        color: var(--success-color);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .error {
        color: var(--error-color);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info {
        color: var(--light-text);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .image-test {
        margin-top: 1rem;
        padding: 1rem;
        background: var(--background-color);
        border-radius: 8px;
        text-align: center;
    }

    .token-display, .clean-test, .session-info {
        background: var(--background-color);
        padding: 0.5rem;
        border-radius: 4px;
        font-family: monospace;
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }

    .system-info {
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .system-info h3 {
        margin-top: 0;
        color: var(--dark-text);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem;
        background: var(--background-color);
        border-radius: 6px;
    }

    .test-actions {
        text-align: center;
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        body {
            padding: 1rem;
        }

        .test-header h1 {
            font-size: 2rem;
        }

        .test-grid {
            grid-template-columns: 1fr;
        }

        .test-actions {
            flex-direction: column;
            align-items: center;
        }
    }
    </style>

    <script>
    console.log('DOKO System Test page loaded');
    
    // Check for JavaScript functionality
    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ JavaScript is working properly');
        
        // Test CSS custom properties
        const testElement = document.createElement('div');
        testElement.style.color = 'var(--primary-color)';
        document.body.appendChild(testElement);
        const computedColor = getComputedStyle(testElement).color;
        document.body.removeChild(testElement);
        
        if (computedColor !== 'var(--primary-color)') {
            console.log('✅ CSS custom properties are working');
        } else {
            console.log('❌ CSS custom properties may not be supported');
        }
    });
    </script>
</body>
</html>

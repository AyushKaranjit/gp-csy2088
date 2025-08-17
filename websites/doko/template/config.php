<?php
/**
 * Template Configuration and Helper Functions
 * This file contains common configuration and helper functions for all templates
 */

// Include image service
require_once __DIR__ . '/image-service.php';

// Site Configuration
if (!defined('SITE_NAME')) define('SITE_NAME', 'DOKO');
if (!defined('SITE_TAGLINE')) define('SITE_TAGLINE', 'Fresh Groceries');
if (!defined('SITE_EMAIL')) define('SITE_EMAIL', 'care@doko.com.np');
if (!defined('SITE_PHONE')) define('SITE_PHONE', '+977-9851234567');
// Enable server-side proxy for external product/hero images to avoid CORB and allow caching
if (!defined('IMAGE_PROXY_ENABLED')) define('IMAGE_PROXY_ENABLED', true);

// Define base paths for different setups
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
// When run under CLI (PHPUnit) HTTP_HOST may be missing; provide safe defaults
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$base_path = dirname($script_name);

// Check if we're in public folder or root and define URLs only once
if (!defined('BASE_URL')) {
    if (basename($base_path) === 'public') {
        // We're in public folder
        define('BASE_URL', $protocol . $host . $base_path . '/');
        define('ASSETS_URL', $protocol . $host . $base_path . '/');
        define('SITE_URL', $protocol . $host . $base_path);
        define('ROOT_PATH', dirname(__DIR__) . '/');
    } else {
        // We're in root folder
        define('BASE_URL', $protocol . $host . $base_path . '/');
        define('ASSETS_URL', $protocol . $host . $base_path . '/public/');
        define('SITE_URL', $protocol . $host . $base_path . '/public');
        define('ROOT_PATH', __DIR__ . '/../');
    }
}

// Navigation Menu Configuration
$navigation_menu = [
    'home' => ['title' => 'Home', 'url' => 'index.php'],
    'categories' => ['title' => 'Categories', 'url' => 'categories.php'],
    'products' => ['title' => 'All Products', 'url' => 'products.php'],
    'offers' => ['title' => 'Special Offers', 'url' => 'offers.php'],
    'about' => ['title' => 'About Us', 'url' => 'about.php'],
    'contact' => ['title' => 'Contact', 'url' => 'contact.php']
];

// Product Categories
$product_categories = [
    1 => ['name' => 'Fresh Vegetables', 'icon' => 'fas fa-carrot'],
    2 => ['name' => 'Fresh Fruits', 'icon' => 'fas fa-apple-alt'],
    3 => ['name' => 'Dairy Products', 'icon' => 'fas fa-cheese'],
    4 => ['name' => 'Grains & Pulses', 'icon' => 'fas fa-seedling'],
    5 => ['name' => 'Spices & Herbs', 'icon' => 'fas fa-pepper-hot'],
    6 => ['name' => 'Snacks & Beverages', 'icon' => 'fas fa-cookie-bite']
];

/**
 * Include header template with page-specific variables
 */
if (!function_exists('include_header')) {
    function include_header($page_title = null, $page_description = null, $current_page = '', $additional_css = []) {
        // Set default values if not provided
        if (!$page_title) {
            $page_title = SITE_NAME . ' | Nepal\'s Premier Online Grocery Store';
        }
        
        if (!$page_description) {
            $page_description = 'Fresh groceries delivered to your doorstep across Nepal. Quality products, competitive prices, and reliable service.';
        }
        
        // Make variables available to the template
        include __DIR__ . '/header.php';
    }
}

/**
 * Include footer template with optional additional scripts
 */
if (!function_exists('include_footer')) {
    function include_footer($additional_js = [], $inline_js = '') {
        // Pass variables to footer scope
        $GLOBALS['additional_js'] = $additional_js;
        $GLOBALS['inline_js'] = $inline_js;
        include __DIR__ . '/footer.php';
    }
}

/**
 * Generate page title with site name
 */
if (!function_exists('page_title')) {
    function page_title($title) {
        return $title . ' - ' . SITE_NAME;
    }
}

/**
 * Generate URL for internal links
 */
function site_url($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

/**
 * Format price in Nepali Rupees
 */
function format_price($price) {
    return 'Rs. ' . number_format($price, 2);
}

/**
 * Get category name by ID
 */
function get_category_name($category_id) {
    global $product_categories;
    return isset($product_categories[$category_id]) ? $product_categories[$category_id]['name'] : 'Unknown Category';
}

/**
 * Generate breadcrumb navigation
 */
function generate_breadcrumb($items) {
    echo '<nav class="breadcrumb">';
    echo '<div class="container">';
    echo '<ol class="breadcrumb-list">';
    
    foreach ($items as $index => $item) {
        $is_last = ($index === count($items) - 1);
        
        if ($is_last) {
            echo '<li class="breadcrumb-item active">' . htmlspecialchars($item['title']) . '</li>';
        } else {
            echo '<li class="breadcrumb-item">';
            echo '<a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['title']) . '</a>';
            echo '</li>';
        }
    }
    
    echo '</ol>';
    echo '</div>';
    echo '</nav>';
}

/**
 * Sanitize output for HTML
 */
function clean_output($text) {
    if ($text === null) return '';
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in (placeholder function)
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user info (placeholder function)
 */
function get_logged_in_user() {
    if (is_logged_in()) {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? 'User',
            'email' => $_SESSION['user_email'] ?? ''
        ];
    }
    return null;
}

/**
 * Generate CSRF token for forms
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect to a URL
 */
if (!function_exists('redirect')) {
    function redirect($url) {
        header('Location: ' . $url);
        exit();
    }
}

/**
 * Set flash message
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Get product image URL with fallback to default image
 */
function product_image($product_name, $width = 300, $height = 300) {
    // Clean product name for file lookup
    $clean_name = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $product_name));
    $clean_name = str_replace(' ', '-', trim($clean_name));
    
    // Check if specific product image exists
    $specific_image_path = "uploads/products/{$clean_name}.jpg";
    $specific_image_full_path = __DIR__ . "/../public/{$specific_image_path}";
    
    if (file_exists($specific_image_full_path)) {
        return $specific_image_path;
    }
    
    // Check for PNG version
    $specific_image_path_png = "uploads/products/{$clean_name}.png";
    $specific_image_full_path_png = __DIR__ . "/../public/{$specific_image_path_png}";
    
    if (file_exists($specific_image_full_path_png)) {
        return $specific_image_path_png;
    }
    
    // Return default image (use an existing local image if uploads/default-product.jpg is missing)
    // Prefer existing hero/slider image from /images as a fallback
    $default1 = '/uploads/default-product.jpg';
    $fallback = '/images/Slider1.jpg';
    $absDefault = __DIR__ . '/../public' . $default1;
    if (is_file($absDefault)) return $default1;
    if (is_file(__DIR__ . '/../public' . $fallback)) return $fallback;
    return '/images/Slider1.jpg';
}

/**
 * Display flash message HTML
 */
function display_flash_message() {
    $flash = get_flash_message();
    if ($flash) {
        $class = 'alert-' . $flash['type'];
        echo '<div class="alert ' . $class . ' alert-dismissible">';
        echo '<button type="button" class="close" data-dismiss="alert">&times;</button>';
        echo htmlspecialchars($flash['message']);
        echo '</div>';
    }
}
?>

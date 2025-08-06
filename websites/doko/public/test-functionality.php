<?php
/**
 * Functionality Test Script
 * Test product detail, cart, wishlist, and admin functionality
 */

session_start();
require_once __DIR__ . '/../config/database.php';

// Color codes for output
function success($msg) { echo "\033[32m✓ $msg\033[0m\n"; }
function error($msg) { echo "\033[31m✗ $msg\033[0m\n"; }
function info($msg) { echo "\033[34mℹ $msg\033[0m\n"; }
function warning($msg) { echo "\033[33m⚠ $msg\033[0m\n"; }

echo "========================================\n";
echo "DOKO E-commerce Functionality Test\n";
echo "========================================\n\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    success("Database connection established");
    
    // Test 1: Check if products exist
    info("\n1. Testing Product Functionality:");
    $stmt = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['count'] > 0) {
        success("Found {$result['count']} active products");
        
        // Get a sample product
        $stmt = $conn->query("SELECT * FROM products WHERE status = 'active' LIMIT 1");
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        success("Sample product: {$product['name']} (ID: {$product['product_id']})");
        info("  - Price: Rs. {$product['price']}");
        info("  - Stock: {$product['stock_quantity']} units");
    } else {
        warning("No active products found");
    }
    
    // Test 2: Check user roles
    info("\n2. Testing User Roles:");
    $stmt = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($roles as $role) {
        info("  - {$role['role']}: {$role['count']} users");
    }
    
    // Check admin user
    $stmt = $conn->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        success("Admin user found: {$admin['username']} ({$admin['email']})");
    } else {
        warning("No admin user found - creating one...");
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, status) VALUES (?, ?, ?, ?, ?, 'admin', 'active')");
        $stmt->execute(['admin', 'admin@doko.com', password_hash('admin123', PASSWORD_DEFAULT), 'Admin', 'User']);
        success("Admin user created (username: admin, password: admin123)");
    }
    
    // Test 3: Check cart table
    info("\n3. Testing Cart Functionality:");
    $stmt = $conn->query("SHOW TABLES LIKE 'cart'");
    if ($stmt->rowCount() > 0) {
        success("Cart table exists");
        $stmt = $conn->query("SELECT COUNT(*) as count FROM cart");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        info("  - Current cart items: {$result['count']}");
    } else {
        error("Cart table does not exist");
    }
    
    // Test 4: Check wishlist table
    info("\n4. Testing Wishlist Functionality:");
    $stmt = $conn->query("SHOW TABLES LIKE 'wishlist'");
    if ($stmt->rowCount() > 0) {
        success("Wishlist table exists");
        $stmt = $conn->query("SELECT COUNT(*) as count FROM wishlist");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        info("  - Current wishlist items: {$result['count']}");
    } else {
        warning("Wishlist table does not exist - will be created on first use");
    }
    
    // Test 5: Check categories
    info("\n5. Testing Categories:");
    $stmt = $conn->query("SELECT COUNT(*) as count FROM categories WHERE status = 'active'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['count'] > 0) {
        success("Found {$result['count']} active categories");
        $stmt = $conn->query("SELECT name FROM categories WHERE status = 'active' LIMIT 5");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categories as $cat) {
            info("  - {$cat['name']}");
        }
    } else {
        warning("No active categories found");
    }
    
    // Test 6: Check file paths
    info("\n6. Testing File Paths:");
    $files = [
        'product-detail.php' => 'Product Detail Page',
        'cart.php' => 'Cart Page',
        'wishlist.php' => 'Wishlist Page',
        'admin.php' => 'Admin Panel',
        'api/cart-add.php' => 'Cart API',
        'api/wishlist.php' => 'Wishlist API',
        'js/main.js' => 'Main JavaScript',
        'js/product-actions.js' => 'Product Actions JavaScript'
    ];
    
    foreach ($files as $file => $desc) {
        if (file_exists(__DIR__ . '/' . $file)) {
            success("$desc exists");
        } else {
            error("$desc missing ($file)");
        }
    }
    
    // Test 7: Check session
    info("\n7. Testing Session:");
    if (session_status() === PHP_SESSION_ACTIVE) {
        success("Session is active");
        if (isset($_SESSION['user_id'])) {
            info("  - User logged in (ID: {$_SESSION['user_id']}, Role: {$_SESSION['role']})");
        } else {
            info("  - No user logged in");
        }
    } else {
        warning("Session not active");
    }
    
    echo "\n========================================\n";
    echo "Test Complete!\n";
    echo "========================================\n\n";
    
    echo "Quick Links:\n";
    echo "- Homepage: http://localhost/\n";
    echo "- Products: http://localhost/products.php\n";
    echo "- Admin Panel: http://localhost/admin.php\n";
    echo "- PhpMyAdmin: http://localhost:8081/\n";
    echo "\nAdmin Login:\n";
    echo "- Username: admin\n";
    echo "- Password: admin123\n";
    
} catch (Exception $e) {
    error("Test failed: " . $e->getMessage());
}
?>

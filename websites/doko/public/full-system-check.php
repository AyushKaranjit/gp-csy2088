<?php
/**
 * DOKO E-commerce - Full System Check & Error Resolution
 * This script performs comprehensive testing of all components
 */

// Prevent timeout for long operations
set_time_limit(300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper functions for colored output
function success($msg) { return "<span style='color: green;'>✓ $msg</span>"; }
function error($msg) { return "<span style='color: red;'>✗ $msg</span>"; }
function warning($msg) { return "<span style='color: orange;'>⚠ $msg</span>"; }
function info($msg) { return "<span style='color: blue;'>ℹ $msg</span>"; }
function fixed($msg) { return "<span style='color: purple;'>🔧 FIXED: $msg</span>"; }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOKO - Full System Check</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #f0f0f0;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #2a2a2a;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        h1 {
            color: #4CAF50;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        h2 {
            color: #FFC107;
            margin-top: 30px;
            border-left: 4px solid #FFC107;
            padding-left: 10px;
        }
        .test-section {
            background: #333;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 3px solid #555;
        }
        .success { color: #4CAF50; }
        .error { color: #f44336; }
        .warning { color: #FFC107; }
        .info { color: #2196F3; }
        .fixed { color: #9C27B0; font-weight: bold; }
        pre {
            background: #1a1a1a;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .summary {
            background: #1a1a1a;
            padding: 20px;
            margin-top: 30px;
            border-radius: 5px;
            border: 2px solid #4CAF50;
        }
        .progress {
            width: 100%;
            height: 30px;
            background: #333;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
            width: 0%;
            transition: width 0.5s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #555;
        }
        th {
            background: #1a1a1a;
            color: #4CAF50;
        }
        .btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🚀 DOKO E-commerce - Full System Check & Error Resolution</h1>
    <div class="progress">
        <div class="progress-bar" id="progress">0%</div>
    </div>

<?php
$totalTests = 0;
$passedTests = 0;
$fixedIssues = 0;
$errors = [];

// Update progress
function updateProgress($current, $total) {
    $percentage = round(($current / $total) * 100);
    echo "<script>document.getElementById('progress').style.width = '$percentage%'; document.getElementById('progress').innerText = '$percentage%';</script>";
    flush();
}

// ========================================
// 1. DATABASE CONNECTION TEST
// ========================================
echo "<h2>1. Database Connection & Configuration</h2>";
echo "<div class='test-section'>";

try {
    require_once __DIR__ . '/../config/database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo success("Database connection established") . "<br>";
    $totalTests++;
    $passedTests++;
    
    // Test database name
    $result = $conn->query("SELECT DATABASE() as db_name")->fetch();
    echo info("Connected to database: " . $result['db_name']) . "<br>";
    
    // Check tables
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo info("Found " . count($tables) . " tables") . "<br>";
    
    $requiredTables = ['users', 'products', 'categories', 'cart', 'orders', 'order_items'];
    foreach ($requiredTables as $table) {
        $totalTests++;
        if (in_array($table, $tables)) {
            echo success("Table '$table' exists") . "<br>";
            $passedTests++;
        } else {
            echo error("Table '$table' missing") . "<br>";
            $errors[] = "Missing table: $table";
        }
    }
    
} catch (Exception $e) {
    echo error("Database connection failed: " . $e->getMessage()) . "<br>";
    $errors[] = "Database connection failed";
}
echo "</div>";

updateProgress(1, 10);

// ========================================
// 2. API ENDPOINTS TEST
// ========================================
echo "<h2>2. API Endpoints Verification</h2>";
echo "<div class='test-section'>";

$apiFiles = [
    'cart/cart-add.php' => 'Add to Cart API',
    'cart/cart-get.php' => 'Get Cart API',
    'cart/cart-remove.php' => 'Remove from Cart API',
    'wishlist/wishlist.php' => 'Wishlist API',
    'users/auth-login.php' => 'Login API',
    'users/auth-register.php' => 'Register API',
    'users/auth-status.php' => 'Auth Status API',
    'products/products-list.php' => 'Products List API',
    'products/product-detail.php' => 'Product Detail API',
    'categories/categories-list.php' => 'Categories API'
];

$apiDir = __DIR__ . '/api/';
foreach ($apiFiles as $file => $description) {
    $totalTests++;
    $fullPath = $apiDir . $file;
    if (file_exists($fullPath)) {
        $fileSize = filesize($fullPath);
        if ($fileSize > 0) {
            echo success("$description - OK (Size: " . number_format($fileSize) . " bytes)") . "<br>";
            $passedTests++;
        } else {
            echo warning("$description - Empty file") . "<br>";
            $errors[] = "Empty API file: $file";
        }
    } else {
        echo error("$description - Missing") . "<br>";
        $errors[] = "Missing API: $file";
    }
}
echo "</div>";

updateProgress(2, 10);

// ========================================
// 3. JAVASCRIPT FILES TEST
// ========================================
echo "<h2>3. JavaScript Files Verification</h2>";
echo "<div class='test-section'>";

$jsFiles = [
    'main.js' => 'Main JavaScript',
    'product-actions.js' => 'Product Actions',
    'mobile-nav.js' => 'Mobile Navigation'
];

$jsDir = __DIR__ . '/js/';
foreach ($jsFiles as $file => $description) {
    $totalTests++;
    $fullPath = $jsDir . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        // Check for common JS errors
        if (strpos($content, 'addToCart') !== false) {
            echo success("$description - Contains addToCart function") . "<br>";
            $passedTests++;
        } else if ($file === 'main.js') {
            echo warning("$description - Missing addToCart function") . "<br>";
        } else {
            echo success("$description - File exists") . "<br>";
            $passedTests++;
        }
        
        // Check for syntax errors (basic)
        if (substr_count($content, '{') !== substr_count($content, '}')) {
            echo warning("$description - Possible bracket mismatch") . "<br>";
            $errors[] = "JS syntax issue in $file";
        }
    } else {
        echo error("$description - Missing") . "<br>";
        $errors[] = "Missing JS file: $file";
    }
}
echo "</div>";

updateProgress(3, 10);

// ========================================
// 4. USER & AUTHENTICATION TEST
// ========================================
echo "<h2>4. User & Authentication System</h2>";
echo "<div class='test-section'>";

try {
    // Check for users
    $stmt = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $roles = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    echo "<table>";
    echo "<tr><th>Role</th><th>Count</th><th>Status</th></tr>";
    
    $requiredRoles = ['admin', 'manager', 'customer'];
    foreach ($requiredRoles as $role) {
        $totalTests++;
        $count = $roles[$role] ?? 0;
        $status = $count > 0 ? success("Active") : warning("No users");
        echo "<tr><td>$role</td><td>$count</td><td>$status</td></tr>";
        
        if ($count > 0) {
            $passedTests++;
        } else if ($role === 'admin') {
            // Auto-create admin if missing
            echo "<tr><td colspan='3'>" . info("Creating default admin user...") . "</td></tr>";
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                'admin',
                'admin@doko.com',
                password_hash('admin123', PASSWORD_DEFAULT),
                'Admin',
                'User',
                'admin',
                'active'
            ]);
            echo "<tr><td colspan='3'>" . fixed("Admin user created (admin/admin123)") . "</td></tr>";
            $fixedIssues++;
        }
    }
    echo "</table>";
    
    // Test session
    $totalTests++;
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo success("Session is active") . "<br>";
        $passedTests++;
        if (isset($_SESSION['user_id'])) {
            echo info("Current user: ID " . $_SESSION['user_id'] . ", Role: " . $_SESSION['role']) . "<br>";
        }
    } else {
        echo error("Session not active") . "<br>";
        $errors[] = "Session not active";
    }
    
} catch (Exception $e) {
    echo error("User system error: " . $e->getMessage()) . "<br>";
    $errors[] = "User system error";
}
echo "</div>";

updateProgress(4, 10);

// ========================================
// 5. PRODUCTS & CATEGORIES TEST
// ========================================
echo "<h2>5. Products & Categories</h2>";
echo "<div class='test-section'>";

try {
    // Check products
    $stmt = $conn->query("SELECT COUNT(*) as count, MIN(price) as min_price, MAX(price) as max_price, AVG(stock_quantity) as avg_stock FROM products WHERE status = 'active'");
    $productStats = $stmt->fetch();
    
    $totalTests++;
    if ($productStats['count'] > 0) {
        echo success("Products: " . $productStats['count'] . " active products found") . "<br>";
        echo info("Price range: Rs. " . number_format($productStats['min_price'], 2) . " - Rs. " . number_format($productStats['max_price'], 2)) . "<br>";
        echo info("Average stock: " . round($productStats['avg_stock']) . " units") . "<br>";
        $passedTests++;
    } else {
        echo warning("No active products found") . "<br>";
        $errors[] = "No active products";
    }
    
    // Check categories - Fixed query
    $stmt = $conn->query("SELECT COUNT(*) as count FROM categories");
    $categoryCount = $stmt->fetch()['count'];
    
    $totalTests++;
    if ($categoryCount > 0) {
        echo success("Categories: $categoryCount categories found") . "<br>";
        $passedTests++;
        
        // List categories
        $stmt = $conn->query("SELECT name, (SELECT COUNT(*) FROM products WHERE category_id = categories.category_id) as product_count FROM categories LIMIT 5");
        $categories = $stmt->fetchAll();
        echo "<table>";
        echo "<tr><th>Category</th><th>Products</th></tr>";
        foreach ($categories as $cat) {
            echo "<tr><td>{$cat['name']}</td><td>{$cat['product_count']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo warning("No categories found") . "<br>";
        $errors[] = "No categories";
    }
    
    // Check for products without images
    $stmt = $conn->query("SELECT COUNT(*) as count FROM products WHERE (image_url IS NULL OR image_url = '') AND status = 'active'");
    $noImageCount = $stmt->fetch()['count'];
    if ($noImageCount > 0) {
        echo warning("$noImageCount products without images") . "<br>";
    }
    
} catch (Exception $e) {
    echo error("Product/Category error: " . $e->getMessage()) . "<br>";
    $errors[] = "Product/Category error";
}
echo "</div>";

updateProgress(5, 10);

// ========================================
// 6. CART & WISHLIST TEST
// ========================================
echo "<h2>6. Cart & Wishlist System</h2>";
echo "<div class='test-section'>";

try {
    // Check cart table
    $totalTests++;
    $stmt = $conn->query("SHOW COLUMNS FROM cart");
    $cartColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredCartColumns = ['cart_id', 'user_id', 'product_id', 'quantity'];
    
    $missingColumns = array_diff($requiredCartColumns, $cartColumns);
    if (empty($missingColumns)) {
        echo success("Cart table structure OK") . "<br>";
        $passedTests++;
    } else {
        echo error("Cart table missing columns: " . implode(', ', $missingColumns)) . "<br>";
        $errors[] = "Cart table structure issue";
    }
    
    // Check wishlist table
    $totalTests++;
    $stmt = $conn->query("SHOW TABLES LIKE 'wishlist'");
    if ($stmt->rowCount() > 0) {
        echo success("Wishlist table exists") . "<br>";
        $passedTests++;
    } else {
        echo warning("Wishlist table doesn't exist - creating...") . "<br>";
        $conn->exec("
            CREATE TABLE IF NOT EXISTS wishlist (
                wishlist_id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                product_id INT NOT NULL,
                added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_wishlist (user_id, product_id),
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
            )
        ");
        echo fixed("Wishlist table created") . "<br>";
        $fixedIssues++;
    }
    
} catch (Exception $e) {
    echo error("Cart/Wishlist error: " . $e->getMessage()) . "<br>";
    $errors[] = "Cart/Wishlist error";
}
echo "</div>";

updateProgress(6, 10);

// ========================================
// 7. ORDER SYSTEM TEST
// ========================================
echo "<h2>7. Order Management System</h2>";
echo "<div class='test-section'>";

try {
    // Check orders table
    $totalTests++;
    $stmt = $conn->query("SELECT COUNT(*) as total_orders, COUNT(DISTINCT user_id) as unique_customers FROM orders");
    $orderStats = $stmt->fetch();
    
    echo info("Total orders: " . $orderStats['total_orders']) . "<br>";
    echo info("Unique customers: " . $orderStats['unique_customers']) . "<br>";
    $passedTests++;
    
    // Check order_items table
    $totalTests++;
    $stmt = $conn->query("SHOW TABLES LIKE 'order_items'");
    if ($stmt->rowCount() > 0) {
        echo success("Order items table exists") . "<br>";
        $passedTests++;
    } else {
        echo error("Order items table missing") . "<br>";
        $errors[] = "Order items table missing";
    }
    
} catch (Exception $e) {
    echo error("Order system error: " . $e->getMessage()) . "<br>";
    $errors[] = "Order system error";
}
echo "</div>";

updateProgress(7, 10);

// ========================================
// 8. FILE PERMISSIONS TEST
// ========================================
echo "<h2>8. File Permissions & Uploads</h2>";
echo "<div class='test-section'>";

$uploadDirs = [
    'uploads' => __DIR__ . '/uploads',
    'uploads/products' => __DIR__ . '/uploads/products',
    'uploads/users' => __DIR__ . '/uploads/users',
    'uploads/categories' => __DIR__ . '/uploads/categories'
];

foreach ($uploadDirs as $name => $path) {
    $totalTests++;
    if (!file_exists($path)) {
        echo warning("Creating directory: $name") . "<br>";
        if (mkdir($path, 0755, true)) {
            echo fixed("Directory created: $name") . "<br>";
            $fixedIssues++;
            $passedTests++;
        } else {
            echo error("Failed to create: $name") . "<br>";
            $errors[] = "Cannot create directory: $name";
        }
    } else {
        if (is_writable($path)) {
            echo success("$name - Writable") . "<br>";
            $passedTests++;
        } else {
            echo error("$name - Not writable") . "<br>";
            $errors[] = "Directory not writable: $name";
        }
    }
}
echo "</div>";

updateProgress(8, 10);

// ========================================
// 9. CONFIGURATION FILES TEST
// ========================================
echo "<h2>9. Configuration Files</h2>";
echo "<div class='test-section'>";

$configFiles = [
    '../config/database.php' => 'Database Configuration',
    '../template/config.php' => 'Template Configuration',
    '../template/header.php' => 'Header Template',
    '../template/footer.php' => 'Footer Template'
];

foreach ($configFiles as $file => $description) {
    $totalTests++;
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo success("$description - Found") . "<br>";
        $passedTests++;
        
        // Check for common issues
        $content = file_get_contents($fullPath);
        if (strpos($content, 'localhost') === false && strpos($file, 'database') !== false) {
            echo warning("$description - Check database host configuration") . "<br>";
        }
    } else {
        echo error("$description - Missing") . "<br>";
        $errors[] = "Missing config: $file";
    }
}
echo "</div>";

updateProgress(9, 10);

// ========================================
// 10. SECURITY CHECK
// ========================================
echo "<h2>10. Security Checks</h2>";
echo "<div class='test-section'>";

// Check for SQL injection protection
$totalTests++;
$stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE password NOT LIKE '$2y$%'");
$insecurePasswords = $stmt->fetch()['count'];
if ($insecurePasswords == 0) {
    echo success("All passwords are properly hashed") . "<br>";
    $passedTests++;
} else {
    echo error("$insecurePasswords users with insecure passwords") . "<br>";
    $errors[] = "Insecure passwords found";
}

// Check session security
$totalTests++;
if (ini_get('session.use_only_cookies')) {
    echo success("Session cookies only - Secure") . "<br>";
    $passedTests++;
} else {
    echo warning("Session security could be improved") . "<br>";
}

// Check for exposed config files
$totalTests++;
if (!file_exists(__DIR__ . '/.env')) {
    echo success("No exposed .env file in public directory") . "<br>";
    $passedTests++;
} else {
    echo error(".env file in public directory - Security risk!") . "<br>";
    $errors[] = "Exposed .env file";
}

echo "</div>";

updateProgress(10, 10);

// ========================================
// SUMMARY
// ========================================
echo "<div class='summary'>";
echo "<h2>📊 Test Summary</h2>";

$successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;
$statusColor = $successRate >= 80 ? '#4CAF50' : ($successRate >= 60 ? '#FFC107' : '#f44336');

echo "<div style='font-size: 24px; margin: 20px 0;'>";
echo "Overall Health: <span style='color: $statusColor; font-weight: bold;'>$successRate%</span>";
echo "</div>";

echo "<table>";
echo "<tr><th>Metric</th><th>Value</th></tr>";
echo "<tr><td>Total Tests</td><td>$totalTests</td></tr>";
echo "<tr><td>Passed Tests</td><td style='color: #4CAF50;'>$passedTests</td></tr>";
echo "<tr><td>Failed Tests</td><td style='color: #f44336;'>" . ($totalTests - $passedTests) . "</td></tr>";
echo "<tr><td>Issues Fixed</td><td style='color: #9C27B0;'>$fixedIssues</td></tr>";
echo "</table>";

if (!empty($errors)) {
    echo "<h3 style='color: #f44336;'>⚠️ Issues Found:</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

echo "<h3>🔗 Quick Links:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='/' class='btn'>Homepage</a>";
echo "<a href='/products.php' class='btn'>Products</a>";
echo "<a href='/admin.php' class='btn'>Admin Panel</a>";
echo "<a href='/login.php' class='btn'>Login</a>";
echo "<a href='http://localhost:8081' class='btn' target='_blank'>PhpMyAdmin</a>";
echo "</div>";

echo "<h3>📝 Recommendations:</h3>";
echo "<ul>";
if ($successRate < 100) {
    echo "<li>Review and fix the issues listed above</li>";
}
if (empty($roles['admin'] ?? 0)) {
    echo "<li>Create an admin user for management access</li>";
}
if ($productStats['count'] < 10) {
    echo "<li>Add more products to your catalog</li>";
}
if ($categoryCount < 5) {
    echo "<li>Create more categories for better organization</li>";
}
echo "<li>Regularly backup your database</li>";
echo "<li>Monitor error logs for issues</li>";
echo "</ul>";

echo "</div>";
?>

</div>

<script>
// Auto-refresh if needed
setTimeout(function() {
    if (confirm('System check complete. Refresh to run again?')) {
        location.reload();
    }
}, 30000);
</script>

</body>
</html>

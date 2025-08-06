<?php
/**
 * DOKO E-commerce System - Quick Feature Test
 * Tests all major functionality to verify system is working
 */

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>DOKO - Feature Test Results</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
    .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
    .test-item { margin: 10px 0; padding: 8px; border-left: 4px solid #007bff; background: #f8f9fa; }
    h1 { color: #333; text-align: center; }
    h2 { color: #555; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    .status { font-weight: bold; }
    .details { font-size: 0.9em; color: #666; margin-top: 5px; }
</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🛒 DOKO E-commerce - System Feature Test</h1>";
echo "<p style='text-align: center; color: #666;'>Comprehensive test of all major system features</p>";

$totalTests = 0;
$passedTests = 0;
$errors = [];

function testFeature($name, $test, $details = '') {
    global $totalTests, $passedTests, $errors;
    $totalTests++;
    
    try {
        $result = $test();
        if ($result['success']) {
            $passedTests++;
            echo "<div class='test-item success'>";
            echo "<div class='status'>✅ {$name}: PASSED</div>";
            if ($details) echo "<div class='details'>{$details}</div>";
            if (isset($result['data'])) echo "<div class='details'>{$result['data']}</div>";
            echo "</div>";
        } else {
            echo "<div class='test-item error'>";
            echo "<div class='status'>❌ {$name}: FAILED</div>";
            echo "<div class='details'>Error: {$result['message']}</div>";
            if ($details) echo "<div class='details'>{$details}</div>";
            echo "</div>";
            $errors[] = $name;
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>";
        echo "<div class='status'>❌ {$name}: ERROR</div>";
        echo "<div class='details'>Exception: " . $e->getMessage() . "</div>";
        echo "</div>";
        $errors[] = $name;
    }
}

// Database Configuration Tests
echo "<div class='test-section'>";
echo "<h2>🗄️ Database Configuration</h2>";

testFeature("Database Connection", function() {
    require_once 'config/database.php';
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT 1");
        $stmt->execute();
        return ['success' => true, 'data' => 'Database connection successful'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}, "Tests database connectivity and configuration");

testFeature("Required Tables Check", function() {
    require_once 'config/database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $requiredTables = ['users', 'products', 'categories', 'cart', 'orders', 'product_images'];
    $existingTables = [];
    
    $stmt = $conn->prepare("SHOW TABLES");
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            $existingTables[] = $table;
        }
    }
    
    if (count($existingTables) === count($requiredTables)) {
        return ['success' => true, 'data' => 'All required tables exist: ' . implode(', ', $existingTables)];
    } else {
        $missing = array_diff($requiredTables, $existingTables);
        return ['success' => false, 'message' => 'Missing tables: ' . implode(', ', $missing)];
    }
}, "Verifies all required database tables exist");

echo "</div>";

// API Functionality Tests
echo "<div class='test-section'>";
echo "<h2>🔌 API Functionality</h2>";

testFeature("Products API", function() {
    $url = 'http://localhost/api/products.php';
    $response = file_get_contents($url);
    if ($response === false) {
        return ['success' => false, 'message' => 'Failed to fetch products API'];
    }
    
    $data = json_decode($response, true);
    if (isset($data['success']) && $data['success'] && isset($data['products'])) {
        $count = count($data['products']);
        return ['success' => true, 'data' => "Found {$count} products"];
    } else {
        return ['success' => false, 'message' => 'Invalid API response format'];
    }
}, "Tests product listing API endpoint");

testFeature("Categories API", function() {
    $url = 'http://localhost/api/categories.php';
    $response = file_get_contents($url);
    if ($response === false) {
        return ['success' => false, 'message' => 'Failed to fetch categories API'];
    }
    
    $data = json_decode($response, true);
    if (isset($data['success']) && $data['success'] && isset($data['categories'])) {
        $count = count($data['categories']);
        return ['success' => true, 'data' => "Found {$count} categories"];
    } else {
        return ['success' => false, 'message' => 'Invalid API response format'];
    }
}, "Tests category listing API endpoint");

testFeature("Cart API", function() {
    $url = 'http://localhost/api/cart-get.php';
    $response = file_get_contents($url);
    if ($response === false) {
        return ['success' => false, 'message' => 'Failed to fetch cart API'];
    }
    
    $data = json_decode($response, true);
    if (isset($data['success']) && $data['success'] !== false) {
        return ['success' => true, 'data' => 'Cart API responding correctly'];
    } else {
        return ['success' => false, 'message' => 'Cart API not responding properly'];
    }
}, "Tests shopping cart API functionality");

echo "</div>";

// File System Tests
echo "<div class='test-section'>";
echo "<h2>📁 File System</h2>";

testFeature("Core Files Existence", function() {
    $coreFiles = [
        'config/database.php',
        'public/index.php',
        'public/products.php',
        'public/login.php',
        'public/cart.php'
    ];
    
    $missingFiles = [];
    foreach ($coreFiles as $file) {
        if (!file_exists($file)) {
            $missingFiles[] = $file;
        }
    }
    
    if (empty($missingFiles)) {
        return ['success' => true, 'data' => 'All core files present: ' . count($coreFiles) . ' files'];
    } else {
        return ['success' => false, 'message' => 'Missing files: ' . implode(', ', $missingFiles)];
    }
}, "Verifies all essential system files are present");

testFeature("Upload Directory", function() {
    $uploadDir = 'public/uploads';
    if (is_dir($uploadDir) && is_writable($uploadDir)) {
        $files = glob($uploadDir . '/*');
        $count = count($files);
        return ['success' => true, 'data' => "Upload directory accessible with {$count} files"];
    } else {
        return ['success' => false, 'message' => 'Upload directory not accessible or not writable'];
    }
}, "Tests file upload directory accessibility");

echo "</div>";

// Web Page Tests
echo "<div class='test-section'>";
echo "<h2>🌐 Web Pages</h2>";

testFeature("Main Homepage", function() {
    $url = 'http://localhost/index.php';
    $headers = get_headers($url);
    if (strpos($headers[0], '200') !== false) {
        return ['success' => true, 'data' => 'Homepage accessible'];
    } else {
        return ['success' => false, 'message' => 'Homepage not accessible: ' . $headers[0]];
    }
}, "Tests main homepage accessibility");

testFeature("Products Page", function() {
    $url = 'http://localhost/products.php';
    $headers = get_headers($url);
    if (strpos($headers[0], '200') !== false) {
        return ['success' => true, 'data' => 'Products page accessible'];
    } else {
        return ['success' => false, 'message' => 'Products page not accessible: ' . $headers[0]];
    }
}, "Tests product listing page");

testFeature("Login Page", function() {
    $url = 'http://localhost/login.php';
    $headers = get_headers($url);
    if (strpos($headers[0], '200') !== false) {
        return ['success' => true, 'data' => 'Login page accessible'];
    } else {
        return ['success' => false, 'message' => 'Login page not accessible: ' . $headers[0]];
    }
}, "Tests user authentication page");

echo "</div>";

// Recently Fixed Features
echo "<div class='test-section'>";
echo "<h2>🔧 Recently Fixed Features</h2>";

testFeature("Product Images Table", function() {
    require_once 'config/database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM product_images");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result && $result['count'] >= 0) {
        return ['success' => true, 'data' => "Product images table working with {$result['count']} entries"];
    } else {
        return ['success' => false, 'message' => 'Product images table not accessible'];
    }
}, "Tests the newly created product_images table");

testFeature("Cart Price Field Fix", function() {
    require_once 'config/database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("DESCRIBE cart");
    $stmt->execute();
    $fields = $stmt->fetchAll();
    
    $priceField = null;
    foreach ($fields as $field) {
        if ($field['Field'] === 'price') {
            $priceField = $field;
            break;
        }
    }
    
    if ($priceField && $priceField['Default'] === '0.00') {
        return ['success' => true, 'data' => 'Cart price field has default value: ' . $priceField['Default']];
    } else {
        return ['success' => false, 'message' => 'Cart price field default value not set'];
    }
}, "Verifies cart price field default value fix");

testFeature("Admin APIs Access", function() {
    $adminApis = [
        'inventory-list.php',
        'orders-list.php',
        'stock-update.php',
        'users-list.php'
    ];
    
    $workingApis = [];
    foreach ($adminApis as $api) {
        $url = "http://localhost/api/{$api}";
        $response = file_get_contents($url);
        if ($response !== false) {
            $data = json_decode($response, true);
            if (isset($data['success']) || isset($data['message'])) {
                $workingApis[] = $api;
            }
        }
    }
    
    if (count($workingApis) === count($adminApis)) {
        return ['success' => true, 'data' => 'All admin APIs responding: ' . implode(', ', $workingApis)];
    } else {
        $missing = array_diff($adminApis, $workingApis);
        return ['success' => false, 'message' => 'Some admin APIs not responding: ' . implode(', ', $missing)];
    }
}, "Tests the previously missing admin API endpoints");

echo "</div>";

// Performance Tests
echo "<div class='test-section'>";
echo "<h2>⚡ Performance</h2>";

testFeature("Database Query Performance", function() {
    require_once 'config/database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $start = microtime(true);
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    $result = $stmt->fetch();
    $end = microtime(true);
    
    $duration = round(($end - $start) * 1000, 2);
    
    if ($duration < 100) { // Less than 100ms is good
        return ['success' => true, 'data' => "Query executed in {$duration}ms"];
    } else {
        return ['success' => false, 'message' => "Query too slow: {$duration}ms"];
    }
}, "Tests database query response time");

testFeature("API Response Time", function() {
    $start = microtime(true);
    $response = file_get_contents('http://localhost/api/products.php');
    $end = microtime(true);
    
    $duration = round(($end - $start) * 1000, 2);
    
    if ($duration < 500 && $response !== false) { // Less than 500ms is acceptable
        return ['success' => true, 'data' => "API responded in {$duration}ms"];
    } else {
        return ['success' => false, 'message' => "API too slow: {$duration}ms"];
    }
}, "Tests API endpoint response time");

echo "</div>";

// Final Results
echo "<div class='test-section'>";
if ($passedTests === $totalTests) {
    echo "<div class='success'>";
    echo "<h2>🎉 All Tests Passed!</h2>";
    echo "<p><strong>System Status: FULLY OPERATIONAL</strong></p>";
    echo "<p>All {$totalTests} tests passed successfully. Your DOKO e-commerce system is working perfectly!</p>";
} else {
    $failedTests = $totalTests - $passedTests;
    echo "<div class='warning'>";
    echo "<h2>⚠️ Some Tests Failed</h2>";
    echo "<p><strong>System Status: PARTIALLY OPERATIONAL</strong></p>";
    echo "<p>{$passedTests}/{$totalTests} tests passed. {$failedTests} tests failed.</p>";
    if (!empty($errors)) {
        echo "<p><strong>Failed Tests:</strong> " . implode(', ', $errors) . "</p>";
    }
}

$percentage = round(($passedTests / $totalTests) * 100, 1);
echo "<div style='margin: 20px 0;'>";
echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px;'>";
echo "<h3 style='margin: 0 0 10px 0;'>📊 Test Summary</h3>";
echo "<div><strong>Total Tests:</strong> {$totalTests}</div>";
echo "<div><strong>Passed:</strong> <span style='color: #28a745;'>{$passedTests}</span></div>";
echo "<div><strong>Failed:</strong> <span style='color: #dc3545;'>" . ($totalTests - $passedTests) . "</span></div>";
echo "<div><strong>Success Rate:</strong> <span style='color: " . ($percentage >= 90 ? '#28a745' : ($percentage >= 70 ? '#ffc107' : '#dc3545')) . ";'>{$percentage}%</span></div>";
echo "</div>";
echo "</div>";

echo "</div>";
echo "</div>";

echo "<div style='text-align: center; margin: 20px 0; color: #666;'>";
echo "<hr>";
echo "<p>DOKO E-commerce System - Feature Test Complete</p>";
echo "<p>Test Date: " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

echo "</div>";
echo "</body></html>";
?>

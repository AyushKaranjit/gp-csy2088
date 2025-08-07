<?php
/**
 * Test file to debug auth issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing auth system...\n\n";

// Test 1: Check if database config exists
$config_path = __DIR__ . '/../config/database.php';
echo "1. Database config path: $config_path\n";
echo "   Exists: " . (file_exists($config_path) ? 'YES' : 'NO') . "\n\n";

if (file_exists($config_path)) {
    echo "2. Including database config...\n";
    try {
        require_once $config_path;
        echo "   Database config loaded successfully\n\n";
        
        // Test database connection
        echo "3. Testing database connection...\n";
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            echo "   Database connected successfully\n\n";
        } catch (Exception $e) {
            echo "   Database connection failed: " . $e->getMessage() . "\n\n";
        }
        
    } catch (Exception $e) {
        echo "   Failed to load database config: " . $e->getMessage() . "\n\n";
    }
}

// Test 2: Check AuthController
$auth_path = __DIR__ . '/../src/Controllers/AuthController.php';
echo "4. AuthController path: $auth_path\n";
echo "   Exists: " . (file_exists($auth_path) ? 'YES' : 'NO') . "\n\n";

if (file_exists($auth_path)) {
    echo "5. Including AuthController...\n";
    try {
        require_once $auth_path;
        echo "   AuthController loaded successfully\n\n";
    } catch (Exception $e) {
        echo "   Failed to load AuthController: " . $e->getMessage() . "\n\n";
    }
}

echo "Test completed.\n";
?>

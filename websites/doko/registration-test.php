<?php
/**
 * Simple Registration Test
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>Registration Test</title>";
echo "<style>body{font-family:Arial;margin:20px;} .result{padding:10px;margin:10px 0;border-radius:5px;} .success{background:#d4edda;color:#155724;} .error{background:#f8d7da;color:#721c24;}</style>";
echo "</head><body>";

echo "<h1>Registration Test</h1>";

// Test 1: Direct function call
try {
    require_once 'src/Controllers/AuthController.php';
    
    echo "<div class='result success'>✅ AuthController loaded successfully</div>";
    
    $authController = new AuthController();
    echo "<div class='result success'>✅ AuthController instantiated</div>";
    
} catch (Exception $e) {
    echo "<div class='result error'>❌ Error loading AuthController: " . $e->getMessage() . "</div>";
}

// Test 2: Database connection
try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<div class='result success'>✅ Database connection successful</div>";
    
    // Check if users table exists
    $stmt = $conn->prepare("DESCRIBE users");
    $stmt->execute();
    $fields = $stmt->fetchAll();
    
    echo "<div class='result success'>✅ Users table exists with " . count($fields) . " fields</div>";
    
} catch (Exception $e) {
    echo "<div class='result error'>❌ Database error: " . $e->getMessage() . "</div>";
}

// Test 3: Check existing users
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo "<div class='result success'>✅ Current users in database: " . $result['count'] . "</div>";
    
} catch (Exception $e) {
    echo "<div class='result error'>❌ Error counting users: " . $e->getMessage() . "</div>";
}

echo "</body></html>";
?>

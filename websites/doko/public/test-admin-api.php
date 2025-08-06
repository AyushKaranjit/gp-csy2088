<?php
// Test admin API connectivity
session_start();
require_once '../template/config.php';
require_once '../src/Controllers/AuthController.php';

echo "<h1>Admin API Test</h1>";

// Check if user is logged in
$auth = new AuthController();
echo "<p><strong>Authentication Status:</strong></p>";
echo "<ul>";
echo "<li>Logged in: " . ($auth->isLoggedIn() ? 'Yes' : 'No') . "</li>";
echo "<li>Is Admin: " . ($auth->hasAdminAccess() ? 'Yes' : 'No') . "</li>";
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    echo "<li>User: " . $user['username'] . " (" . $user['email'] . ")</li>";
    echo "<li>Role: " . $user['role'] . "</li>";
}
echo "</ul>";

// Test database connection
try {
    require_once '../config/database.php';
    $db = Database::getInstance();
    echo "<p><strong>Database Connection:</strong> ✅ Success</p>";
    
    // Test orders table
    $testQuery = "SHOW TABLES LIKE 'orders'";
    $result = $db->query($testQuery);
    echo "<p><strong>Orders Table:</strong> " . ($result->rowCount() > 0 ? '✅ Found' : '❌ Not Found') . "</p>";
    
    // Test users table
    $testQuery = "SHOW TABLES LIKE 'users'";
    $result = $db->query($testQuery);
    echo "<p><strong>Users Table:</strong> " . ($result->rowCount() > 0 ? '✅ Found' : '❌ Not Found') . "</p>";
    
    // Get table count
    $tablesQuery = "SHOW TABLES";
    $tablesResult = $db->query($tablesQuery);
    $tableCount = $tablesResult->rowCount();
    echo "<p><strong>Total Tables:</strong> " . $tableCount . "</p>";
    
} catch (Exception $e) {
    echo "<p><strong>Database Connection:</strong> ❌ Error: " . $e->getMessage() . "</p>";
}

// Login form if not logged in
if (!$auth->isLoggedIn()) {
    echo "<hr><h2>Admin Login</h2>";
    echo "<form method='POST' action='../public/api/auth-login.php'>";
    echo "<p><input type='text' name='username' placeholder='Username (admin)' value='admin' required></p>";
    echo "<p><input type='password' name='password' placeholder='Password (password)' required></p>";
    echo "<p><button type='submit'>Login</button></p>";
    echo "</form>";
}
?>

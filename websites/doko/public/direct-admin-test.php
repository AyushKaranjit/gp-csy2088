<?php
// Quick admin API test
session_start();

// Simulate admin authentication for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['email'] = 'admin@doko.com';

echo "<h1>Admin API Direct Test</h1>";

// Test admin-users.php directly
echo "<h2>Testing admin-users.php</h2>";
ob_start();
try {
    include 'api/admin-users.php';
    $output = ob_get_clean();
    echo "<pre>Output: " . htmlspecialchars($output) . "</pre>";
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test admin-orders.php directly  
echo "<h2>Testing admin-orders.php</h2>";
ob_start();
try {
    include 'api/admin-orders.php';
    $output = ob_get_clean();
    echo "<pre>Output: " . htmlspecialchars($output) . "</pre>";
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

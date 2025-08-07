<?php
/**
 * Simple API Test Script
 * Tests if basic API endpoints are working
 */

// Set response header
header('Content-Type: application/json');

$tests = [];
$baseDir = __DIR__ . '/api/';

// Test 1: Check if cart-add-working.php exists
$tests['cart_add_file'] = [
    'name' => 'Cart Add API File',
    'status' => file_exists($baseDir . 'cart-add-working.php') ? 'PASS' : 'FAIL',
    'details' => file_exists($baseDir . 'cart-add-working.php') ? 'File exists' : 'File missing'
];

// Test 2: Check if wishlist.php exists
$tests['wishlist_file'] = [
    'name' => 'Wishlist API File', 
    'status' => file_exists($baseDir . 'wishlist.php') ? 'PASS' : 'FAIL',
    'details' => file_exists($baseDir . 'wishlist.php') ? 'File exists' : 'File missing'
];

// Test 3: Check if admin files exist
$adminFiles = ['admin-products.php', 'admin-users.php', 'admin-orders.php'];
$tests['admin_files'] = [
    'name' => 'Admin API Files',
    'status' => 'PASS',
    'details' => []
];

foreach ($adminFiles as $file) {
    $exists = file_exists($baseDir . $file);
    $tests['admin_files']['details'][$file] = $exists ? 'EXISTS' : 'MISSING';
    if (!$exists) {
        $tests['admin_files']['status'] = 'FAIL';
    }
}

// Test 4: Check logout.php
$tests['logout_file'] = [
    'name' => 'Logout File',
    'status' => file_exists(__DIR__ . '/logout.php') ? 'PASS' : 'FAIL',
    'details' => file_exists(__DIR__ . '/logout.php') ? 'File exists' : 'File missing'
];

// Test 5: Check checkout.php
$tests['checkout_file'] = [
    'name' => 'Checkout File',
    'status' => file_exists(__DIR__ . '/checkout.php') ? 'PASS' : 'FAIL',
    'details' => file_exists(__DIR__ . '/checkout.php') ? 'File exists' : 'File missing'
];

// Test 6: Check database connection
try {
    require_once '../config/database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $tests['database'] = [
        'name' => 'Database Connection',
        'status' => 'PASS',
        'details' => 'Database connected successfully'
    ];
} catch (Exception $e) {
    $tests['database'] = [
        'name' => 'Database Connection',
        'status' => 'FAIL',
        'details' => 'Error: ' . $e->getMessage()
    ];
}

// Test 7: Check if session works
session_start();
$_SESSION['test'] = 'working';
$tests['session'] = [
    'name' => 'Session Functionality',
    'status' => isset($_SESSION['test']) && $_SESSION['test'] === 'working' ? 'PASS' : 'FAIL',
    'details' => isset($_SESSION['test']) ? 'Session working' : 'Session not working'
];

// Calculate overall status
$totalTests = count($tests);
$passedTests = count(array_filter($tests, function($test) { return $test['status'] === 'PASS'; }));
$overallStatus = $passedTests === $totalTests ? 'ALL PASS' : ($passedTests > 0 ? 'PARTIAL' : 'ALL FAIL');

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'overall_status' => $overallStatus,
    'summary' => "$passedTests/$totalTests tests passed",
    'tests' => $tests,
    'recommendations' => []
];

// Add recommendations based on failed tests
if ($tests['cart_add_file']['status'] === 'FAIL') {
    $result['recommendations'][] = 'Upload cart-add-working.php to api/ directory';
}
if ($tests['database']['status'] === 'FAIL') {
    $result['recommendations'][] = 'Check database configuration and connection';
}
if ($tests['admin_files']['status'] === 'FAIL') {
    $result['recommendations'][] = 'Ensure all admin API files are uploaded';
}

// Output results
if (isset($_GET['format']) && $_GET['format'] === 'html') {
    // HTML output
    header('Content-Type: text/html');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>DOKO API Test Results</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
            .status { padding: 10px; border-radius: 4px; margin: 10px 0; }
            .pass { background: #d4edda; border-left: 4px solid #28a745; }
            .fail { background: #f8d7da; border-left: 4px solid #dc3545; }
            .partial { background: #fff3cd; border-left: 4px solid #ffc107; }
            pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>DOKO E-commerce API Test Results</h1>
            <div class="status <?php echo strtolower(str_replace(' ', '-', $overallStatus)); ?>">
                <strong>Overall Status:</strong> <?php echo $overallStatus; ?> (<?php echo $passedTests; ?>/<?php echo $totalTests; ?> tests passed)
            </div>
            
            <h2>Test Details</h2>
            <?php foreach ($tests as $key => $test): ?>
                <div class="status <?php echo strtolower($test['status']); ?>">
                    <strong><?php echo htmlspecialchars($test['name']); ?>:</strong> 
                    <?php echo $test['status']; ?>
                    <br><small><?php echo is_array($test['details']) ? json_encode($test['details'], JSON_PRETTY_PRINT) : htmlspecialchars($test['details']); ?></small>
                </div>
            <?php endforeach; ?>
            
            <?php if (!empty($result['recommendations'])): ?>
                <h2>Recommendations</h2>
                <ul>
                    <?php foreach ($result['recommendations'] as $rec): ?>
                        <li><?php echo htmlspecialchars($rec); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <h2>Raw JSON Output</h2>
            <pre><?php echo json_encode($result, JSON_PRETTY_PRINT); ?></pre>
        </div>
    </body>
    </html>
    <?php
} else {
    // JSON output
    echo json_encode($result, JSON_PRETTY_PRINT);
}
?>

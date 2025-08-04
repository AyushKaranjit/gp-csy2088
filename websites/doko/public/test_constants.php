<?php
// Test file to check if constants are properly defined
session_start();
require_once '../template/config.php';
require_once '../config/database.php';

echo "<h1>DOKO Configuration Test</h1>";
echo "<h2>Constants Status:</h2>";

$constants_to_check = [
    'SITE_NAME', 'SITE_TAGLINE', 'SITE_EMAIL', 'SITE_PHONE',
    'BASE_URL', 'ASSETS_URL', 'SITE_URL', 'ROOT_PATH',
    'APP_NAME', 'APP_VERSION', 'UPLOAD_PATH',
    'SMTP_HOST', 'SMTP_PORT', 'JWT_SECRET', 'DELIVERY_CHARGE'
];

echo "<ul>";
foreach ($constants_to_check as $constant) {
    if (defined($constant)) {
        echo "<li><strong>$constant:</strong> " . constant($constant) . " ✅</li>";
    } else {
        echo "<li><strong>$constant:</strong> NOT DEFINED ❌</li>";
    }
}
echo "</ul>";

echo "<h2>Page Test:</h2>";
echo "<p>If you see this without errors, the configuration is working properly!</p>";
echo "<p><a href='index.php'>← Back to Home</a></p>";
?>

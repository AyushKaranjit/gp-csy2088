<?php
// Simple test for newsletter API
echo "Newsletter API Test\n";
echo "==================\n\n";

// Test data
$testEmail = "test" . time() . "@example.com";
echo "Testing with email: $testEmail\n\n";

// Simulate the API call
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Mock JSON input
$GLOBALS['__TEST_JSON_INPUT'] = ['email' => $testEmail];

// Include the newsletter API
require_once __DIR__ . '/newsletter.php';

echo "\nTest completed.\n";
?>

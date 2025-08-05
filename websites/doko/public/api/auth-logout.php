<?php
/**
 * User Logout API
 * Logout and destroy session
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../src/Controllers/AuthController.php';

try {
    // Use AuthController for logout
    $auth = new AuthController();
    $result = $auth->logout();
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Logout API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during logout'
    ]);
}
?>

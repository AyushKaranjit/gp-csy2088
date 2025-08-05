<?php
/**
 * Login API Endpoint
 * User authentication in api subdirectory
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set proper CORS and JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../config/database.php';
require_once '../src/Controllers/AuthController.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['email']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email and password are required'
        ]);
        exit;
    }
    
    $email = trim($input['email']);
    $password = $input['password'];
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit;
    }
    
    // Use AuthController for login
    $auth = new AuthController();
    $result = $auth->login($email, $password);
    
    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(401);
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    error_log("Login API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during login'
    ]);
}
?>

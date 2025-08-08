<?php
/** Login API Endpoint (refactored) */
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method('POST');

try {
    // Debug: Log that we reached this point
    error_log("Auth-login: Starting processing");
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    error_log("Auth-login: Input received: " . json_encode($input));
    
    if (!$input || !isset($input['email']) || !isset($input['password'])) { ApiResponse::error('Email and password are required', 400); return; }
    
    $email = trim($input['email']);
    $password = $input['password'];
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { ApiResponse::error('Invalid email format', 400); return; }
    
    // Ensure session started for cookie persistence in tests
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

    // Use AuthController for login
    $auth = new AuthController();
    $result = $auth->login($email, $password);
    error_log('Auth-login result: '.json_encode($result));
    
    if ($result['success']) { ApiResponse::success($result); }
    else { ApiResponse::error($result['message'] ?? 'Login failed', 401, ['code' => 'AUTH_FAILED']); }
    
} catch (Exception $e) {
    error_log("Login API error: " . $e->getMessage());
    ApiResponse::error('An error occurred during login', 500);
}
?>

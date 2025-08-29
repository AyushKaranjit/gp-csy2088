<?php
/** Login API Endpoint (refactored) */
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method('POST');

try {
    // Get JSON input (test harness injects via json_input helper)
    $input = json_input();
    // Fallback: accept standard form submission if JSON body empty
    if ((!$input || !is_array($input) || empty($input)) && !empty($_POST)) {
        $input = [
            'email' => $_POST['email'] ?? null,
            'password' => $_POST['password'] ?? null
        ];
    }
    
    if (!$input || !isset($input['email']) || !isset($input['password'])) { ApiResponse::error('Email and password are required', 400); return; }
    
    $email = trim($input['email']);
    $password = $input['password'];
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { ApiResponse::error('Invalid email format', 400); return; }
    
    // Ensure session started for cookie persistence in tests
    if (session_status() === PHP_SESSION_NONE) { 
        // Set session cookie parameters for better browser compatibility
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
        session_start(); 
    }

    // Use AuthController for login
    $auth = new AuthController();
    $result = $auth->login($email, $password);
    
    if ($result['success']) { ApiResponse::success($result); }
    else { ApiResponse::error($result['message'] ?? 'Login failed', 401, ['code' => 'AUTH_FAILED']); }
    
} catch (Exception $e) {
    error_log("Login API error: " . $e->getMessage());
    ApiResponse::error('An error occurred during login', 500);
}
?>

<?php
/**
 * Google OAuth Login API Endpoint
 * DOKO Grocery E-commerce
 */

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../../config/env.php';
use Doko\Http\ApiResponse;
require_method('POST');

try {
    // Get JSON input
    $input = json_input();
    
    if (!$input || !isset($input['credential'])) {
        ApiResponse::error('Google credential is required', 400);
        return;
    }
    
    $credential = $input['credential'];
    
    // Verify Google JWT token
    $google_client_id = env('GOOGLE_CLIENT_ID', 'demo-client-id');
    
    // Decode JWT without verification for demo (in production, verify signature)
    $parts = explode('.', $credential);
    if (count($parts) !== 3) {
        ApiResponse::error('Invalid Google credential format', 400);
        return;
    }
    
    $payload = json_decode(base64_decode(str_pad(strtr($parts[1], '-_', '+/'), strlen($parts[1]) % 4, '=', STR_PAD_RIGHT)), true);
    
    if (!$payload || !isset($payload['email'])) {
        ApiResponse::error('Invalid Google credential payload', 400);
        return;
    }
    
    // Extract user information
    $email = $payload['email'];
    $first_name = $payload['given_name'] ?? '';
    $last_name = $payload['family_name'] ?? '';
    $full_name = $payload['name'] ?? ($first_name . ' ' . $last_name);
    $profile_image = $payload['picture'] ?? '';
    $google_id = $payload['sub'] ?? '';
    
    // Ensure session started
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
    
    // Check if user already exists
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    $userQuery = "SELECT * FROM users WHERE email = :email OR google_id = :google_id LIMIT 1";
    $stmt = $db->prepare($userQuery);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':google_id', $google_id);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Update existing user with Google info if not set
        if (empty($user['google_id'])) {
            $updateQuery = "UPDATE users SET google_id = :google_id, profile_image = :profile_image, updated_at = NOW() WHERE user_id = :user_id";
            $stmt = $db->prepare($updateQuery);
            $stmt->bindParam(':google_id', $google_id);
            $stmt->bindParam(':profile_image', $profile_image);
            $stmt->bindParam(':user_id', $user['user_id']);
            $stmt->execute();
        }
    } else {
        // Create new user
        $username = strtolower(str_replace(' ', '', $full_name)) . rand(100, 999);
        $password = password_hash(uniqid(), PASSWORD_DEFAULT); // Random password for OAuth users
        
        $insertQuery = "INSERT INTO users (
            username, email, password, first_name, last_name, 
            google_id, profile_image, role, status, email_verified, created_at
        ) VALUES (
            :username, :email, :password, :first_name, :last_name,
            :google_id, :profile_image, 'customer', 'active', 1, NOW()
        )";
        
        $stmt = $db->prepare($insertQuery);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':google_id', $google_id);
        $stmt->bindParam(':profile_image', $profile_image);
        
        if ($stmt->execute()) {
            $user_id = $db->lastInsertId();
            
            // Fetch the newly created user
            $userQuery = "SELECT * FROM users WHERE user_id = :user_id LIMIT 1";
            $stmt = $db->prepare($userQuery);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            ApiResponse::error('Failed to create user account', 500);
            return;
        }
    }
    
    // Log user in
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['profile_image'] = $user['profile_image'];
    $_SESSION['logged_in'] = true;
    
    // Update last login
    $updateLoginQuery = "UPDATE users SET last_login = NOW() WHERE user_id = :user_id";
    $stmt = $db->prepare($updateLoginQuery);
    $stmt->bindParam(':user_id', $user['user_id']);
    $stmt->execute();
    
    // Determine redirect URL based on user role
    $redirect_url = 'index.php';
    switch ($user['role']) {
        case 'admin':
            $redirect_url = 'admin.php';
            break;
        case 'manager':
            $redirect_url = 'manager.php';
            break;
        case 'customer':
            $redirect_url = 'customer.php';
            break;
    }
    
    ApiResponse::success([
        'message' => 'Google login successful',
        'user' => [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role']
        ],
        'redirect_url' => $redirect_url
    ]);
    
} catch (Exception $e) {
    error_log("Google OAuth error: " . $e->getMessage());
    ApiResponse::error('Google login failed: ' . $e->getMessage(), 500);
}
?>

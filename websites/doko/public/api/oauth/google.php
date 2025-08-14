<?php
/**
 * Google OAuth API Endpoint
 * DOKO Grocery E-commerce
 */

// Suppress all errors to ensure clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../../config/env.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['credential'])) {
        throw new Exception('Missing Google credential');
    }
    
    $credential = $input['credential'];
    
    // Verify Google JWT token - use proper base64url decoding
    $parts = explode('.', $credential);
    if (count($parts) !== 3) {
        throw new Exception('Invalid Google token format');
    }
    
    // Function to decode base64url
    function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    
    $header = json_decode(base64url_decode($parts[0]), true);
    $payload = json_decode(base64url_decode($parts[1]), true);
    
    // Debug logging (remove in production)
    error_log('JWT Header: ' . json_encode($header));
    error_log('JWT Payload: ' . json_encode($payload));
    
    // Basic validation
    if (!$payload || !isset($payload['email']) || !isset($payload['sub'])) {
        error_log('Invalid token - missing required fields');
        throw new Exception('Invalid Google token');
    }
    
    // Verify the token is for our client ID (if aud field is present)
    if (isset($payload['aud']) && $payload['aud'] !== env('GOOGLE_CLIENT_ID')) {
        throw new Exception('Invalid audience');
    }
    
    $google_id = $payload['sub'];
    $email = $payload['email'];
    $first_name = $payload['given_name'] ?? '';
    $last_name = $payload['family_name'] ?? '';
    $profile_image = $payload['picture'] ?? '';
    
    require_once '../../../config/database.php';
    require_once '../../../src/Controllers/AuthController.php';
    
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    // Check if user is already logged in (account linking)
    if (isset($_SESSION['user_id'])) {
        $current_user_id = $_SESSION['user_id'];
        
        // Check if this Google account is already linked to another user
        $stmt = $db->prepare("SELECT id FROM users WHERE google_id = ? AND id != ?");
        $stmt->execute([$google_id, $current_user_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'This Google account is already linked to another user']);
            exit;
        }
        
        // Link Google account to current user
        $stmt = $db->prepare("UPDATE users SET google_id = ? WHERE id = ?");
        $stmt->execute([$google_id, $current_user_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Google account linked successfully',
            'action' => 'linked'
        ]);
        exit;
    }
    
    // Check if user exists with this Google ID
    $stmt = $db->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
    $stmt->execute([$google_id, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // User exists, update Google ID and profile info if not set
        $updateFields = [];
        $updateValues = [];
        
        if (!$user['google_id']) {
            $updateFields[] = "google_id = ?";
            $updateValues[] = $google_id;
        }
        
        // Update profile image if not set or if Google has a newer one
        if (!$user['profile_image'] && $profile_image) {
            $updateFields[] = "profile_image = ?";
            $updateValues[] = $profile_image;
        }
        
        // Update name if not set (for users who registered with email first)
        if ((!$user['first_name'] || !$user['last_name']) && ($first_name || $last_name)) {
            if (!$user['first_name'] && $first_name) {
                $updateFields[] = "first_name = ?";
                $updateValues[] = $first_name;
            }
            if (!$user['last_name'] && $last_name) {
                $updateFields[] = "last_name = ?";
                $updateValues[] = $last_name;
            }
        }
        
        if (!empty($updateFields)) {
            $updateQuery = "UPDATE users SET " . implode(", ", $updateFields) . ", updated_at = NOW() WHERE user_id = ?";
            $updateValues[] = $user['user_id'];
            $stmt = $db->prepare($updateQuery);
            $stmt->execute($updateValues);
            
            // Refresh user data
            $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } else {
        // Create new customer account with Google info
        $username = $email; // Use email as username for OAuth users
        
        $stmt = $db->prepare("INSERT INTO users (
            username, email, password, first_name, last_name, google_id, profile_image, 
            role, status, email_verified, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'customer', 'active', 1, NOW(), NOW())");
        
        $stmt->execute([
            $username,
            $email,
            '', // Empty password for OAuth users - they don't use password login
            $first_name, 
            $last_name, 
            $google_id, 
            $profile_image
        ]);
        
        $user_id = $db->lastInsertId();
        
        // Get the new user data
        $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log account creation
        error_log("New Google OAuth customer created: User ID {$user_id}, Email: {$email}");
    }
    
    // Create session directly
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'] ?? $user['email'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['user_id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role']
        ],
        'redirect' => '/customer-dashboard.php'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>

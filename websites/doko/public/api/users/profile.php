<?php
/**
 * User Profile API
 * Get current user profile information
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

    require_once __DIR__ . '/../../../src/Controllers/AuthController.php';try {
    // Use AuthController to check if user is logged in
    $auth = new AuthController();
    
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'User not logged in'
        ]);
        exit;
    }
    
    // Get user profile data
    $profile = $auth->getCurrentUser();
    
    if ($profile) {
        echo json_encode([
            'success' => true,
            'user' => $profile
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'User profile not found'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Profile API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching profile'
    ]);
}
?>

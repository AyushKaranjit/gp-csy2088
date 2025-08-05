<?php
/**
 * Cart Clear API
 * Clear all items from cart
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'])) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../config/database.php';
require_once '../src/Controllers/AuthController.php';

try {
    // Check if user is logged in
    $auth = new AuthController();
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'User must be logged in'
        ]);
        exit;
    }
    
    $user = $auth->getCurrentUser();
    $user_id = $user['user_id'];
    
    // Get database connection
    $db = Database::getInstance();
    
    // Clear all items from cart
    $query = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $db->execute($query, [$user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart cleared successfully',
        'items_removed' => $stmt->rowCount()
    ]);
    
} catch (Exception $e) {
    error_log("Clear cart API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while clearing cart'
    ]);
}
?>

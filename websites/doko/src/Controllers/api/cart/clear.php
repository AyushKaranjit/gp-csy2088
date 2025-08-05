<?php
/**
 * Cart Clear API
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'])) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../../../config/database.php';

try {
    session_start();
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $session_id = session_id();
    
    // Clear all cart items for the user/session
    $deleteQuery = "DELETE FROM cart WHERE (user_id = :user_id OR session_id = :session_id)";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bindParam(':user_id', $user_id);
    $deleteStmt->bindParam(':session_id', $session_id);
    $deleteStmt->execute();
    
    $removedCount = $deleteStmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart cleared successfully',
        'data' => [
            'items_removed' => $removedCount,
            'cart_stats' => [
                'item_count' => 0,
                'total_items' => 0
            ]
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Cart clear error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while clearing cart'
    ]);
}
?>

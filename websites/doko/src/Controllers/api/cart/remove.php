<?php
/**
 * Cart Remove API
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
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        exit;
    }
    
    $product_id = (int)$input['product_id'];
    $variant_id = isset($input['variant_id']) ? (int)$input['variant_id'] : null;
    
    if ($product_id <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid product ID'
        ]);
        exit;
    }
    
    session_start();
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $session_id = session_id();
    
    // Find and remove cart item
    $deleteQuery = "DELETE FROM cart 
                    WHERE (user_id = :user_id OR session_id = :session_id)
                    AND product_id = :product_id 
                    AND (variant_id = :variant_id OR (variant_id IS NULL AND :variant_id IS NULL))";
    
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bindParam(':user_id', $user_id);
    $deleteStmt->bindParam(':session_id', $session_id);
    $deleteStmt->bindParam(':product_id', $product_id);
    $deleteStmt->bindParam(':variant_id', $variant_id);
    $deleteStmt->execute();
    
    if ($deleteStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Item not found in cart'
        ]);
        exit;
    }
    
    // Get updated cart count
    $countQuery = "SELECT COUNT(*) as item_count, SUM(quantity) as total_items 
                   FROM cart 
                   WHERE (user_id = :user_id OR session_id = :session_id)";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bindParam(':user_id', $user_id);
    $countStmt->bindParam(':session_id', $session_id);
    $countStmt->execute();
    $cartStats = $countStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart successfully',
        'data' => [
            'product_id' => $product_id,
            'variant_id' => $variant_id,
            'cart_stats' => [
                'item_count' => (int)$cartStats['item_count'],
                'total_items' => (int)$cartStats['total_items']
            ]
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Cart remove error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while removing item from cart'
    ]);
}
?>

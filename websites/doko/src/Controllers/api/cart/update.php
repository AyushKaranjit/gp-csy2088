<?php
/**
 * Cart Update API
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST');
header('Access-Control-Allow-Headers: Content-Type');

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../../../config/database.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Product ID and quantity are required'
        ]);
        exit;
    }
    
    $product_id = (int)$input['product_id'];
    $variant_id = isset($input['variant_id']) ? (int)$input['variant_id'] : null;
    $quantity = (int)$input['quantity'];
    
    if ($product_id <= 0 || $quantity < 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid product ID or quantity'
        ]);
        exit;
    }
    
    session_start();
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $session_id = session_id();
    
    // Find cart item
    $findQuery = "SELECT c.cart_id, p.stock_quantity, pv.stock_quantity as variant_stock
                  FROM cart c
                  JOIN products p ON c.product_id = p.product_id
                  LEFT JOIN product_variants pv ON c.variant_id = pv.variant_id
                  WHERE (c.user_id = :user_id OR c.session_id = :session_id)
                  AND c.product_id = :product_id 
                  AND (c.variant_id = :variant_id OR (c.variant_id IS NULL AND :variant_id IS NULL))";
    
    $findStmt = $conn->prepare($findQuery);
    $findStmt->bindParam(':user_id', $user_id);
    $findStmt->bindParam(':session_id', $session_id);
    $findStmt->bindParam(':product_id', $product_id);
    $findStmt->bindParam(':variant_id', $variant_id);
    $findStmt->execute();
    
    if ($findStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Item not found in cart'
        ]);
        exit;
    }
    
    $cartItem = $findStmt->fetch();
    $availableStock = $variant_id ? $cartItem['variant_stock'] : $cartItem['stock_quantity'];
    
    if ($quantity === 0) {
        // Remove item from cart
        $deleteQuery = "DELETE FROM cart WHERE cart_id = :cart_id";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':cart_id', $cartItem['cart_id']);
        $deleteStmt->execute();
        
        $message = 'Item removed from cart';
    } else {
        // Check stock availability
        if ($quantity > $availableStock) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient stock. Only ' . $availableStock . ' items available.'
            ]);
            exit;
        }
        
        // Update quantity
        $updateQuery = "UPDATE cart SET quantity = :quantity, updated_at = CURRENT_TIMESTAMP 
                        WHERE cart_id = :cart_id";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':quantity', $quantity);
        $updateStmt->bindParam(':cart_id', $cartItem['cart_id']);
        $updateStmt->execute();
        
        $message = 'Cart item updated successfully';
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
        'message' => $message,
        'data' => [
            'product_id' => $product_id,
            'variant_id' => $variant_id,
            'new_quantity' => $quantity,
            'cart_stats' => [
                'item_count' => (int)$cartStats['item_count'],
                'total_items' => (int)$cartStats['total_items']
            ]
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Cart update error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating cart'
    ]);
}
?>

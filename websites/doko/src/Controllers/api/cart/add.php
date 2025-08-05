<?php
/**
 * Cart Add API
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    
    if ($product_id <= 0 || $quantity <= 0) {
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
    
    // Check if product exists and is active
    $productQuery = "SELECT product_id, name, price, stock_quantity, status FROM products WHERE product_id = :product_id";
    $productStmt = $conn->prepare($productQuery);
    $productStmt->bindParam(':product_id', $product_id);
    $productStmt->execute();
    
    if ($productStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }
    
    $product = $productStmt->fetch();
    
    if ($product['status'] !== 'active') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Product is not available'
        ]);
        exit;
    }
    
    $itemPrice = $product['price'];
    $stockQuantity = $product['stock_quantity'];
    
    // Check variant if specified
    if ($variant_id) {
        $variantQuery = "SELECT variant_id, price, stock_quantity, is_active FROM product_variants WHERE variant_id = :variant_id AND product_id = :product_id";
        $variantStmt = $conn->prepare($variantQuery);
        $variantStmt->bindParam(':variant_id', $variant_id);
        $variantStmt->bindParam(':product_id', $product_id);
        $variantStmt->execute();
        
        if ($variantStmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Product variant not found'
            ]);
            exit;
        }
        
        $variant = $variantStmt->fetch();
        
        if (!$variant['is_active']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Product variant is not available'
            ]);
            exit;
        }
        
        $itemPrice = $variant['price'];
        $stockQuantity = $variant['stock_quantity'];
    }
    
    // Check stock availability
    if ($stockQuantity < $quantity) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Insufficient stock. Only ' . $stockQuantity . ' items available.'
        ]);
        exit;
    }
    
    // Check if item already exists in cart
    $checkQuery = "SELECT cart_id, quantity FROM cart 
                   WHERE (user_id = :user_id OR session_id = :session_id) 
                   AND product_id = :product_id 
                   AND (variant_id = :variant_id OR (variant_id IS NULL AND :variant_id IS NULL))";
    
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':user_id', $user_id);
    $checkStmt->bindParam(':session_id', $session_id);
    $checkStmt->bindParam(':product_id', $product_id);
    $checkStmt->bindParam(':variant_id', $variant_id);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        // Update existing cart item
        $existing = $checkStmt->fetch();
        $newQuantity = $existing['quantity'] + $quantity;
        
        if ($newQuantity > $stockQuantity) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Cannot add more items. Total would exceed available stock.'
            ]);
            exit;
        }
        
        $updateQuery = "UPDATE cart SET quantity = :quantity, price = :price, updated_at = CURRENT_TIMESTAMP 
                        WHERE cart_id = :cart_id";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':quantity', $newQuantity);
        $updateStmt->bindParam(':price', $itemPrice);
        $updateStmt->bindParam(':cart_id', $existing['cart_id']);
        $updateStmt->execute();
        
        $message = 'Item quantity updated in cart';
    } else {
        // Add new cart item
        $insertQuery = "INSERT INTO cart (user_id, session_id, product_id, variant_id, quantity, price) 
                        VALUES (:user_id, :session_id, :product_id, :variant_id, :quantity, :price)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bindParam(':user_id', $user_id);
        $insertStmt->bindParam(':session_id', $session_id);
        $insertStmt->bindParam(':product_id', $product_id);
        $insertStmt->bindParam(':variant_id', $variant_id);
        $insertStmt->bindParam(':quantity', $quantity);
        $insertStmt->bindParam(':price', $itemPrice);
        $insertStmt->execute();
        
        $message = 'Item added to cart successfully';
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
            'quantity_added' => $quantity,
            'cart_stats' => [
                'item_count' => (int)$cartStats['item_count'],
                'total_items' => (int)$cartStats['total_items']
            ]
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Cart add error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while adding item to cart'
    ]);
}
?>

<?php
/**
 * Shopping Cart API Endpoint
 * Handles cart operations (add, remove, update, get)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/Controllers/AuthController.php';

try {
    $db = Database::getInstance();
    $auth = new AuthController();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Get user session info
    $userId = null;
    $sessionId = null;
    
    if ($auth->isLoggedIn()) {
        $user = $auth->getCurrentUser();
        $userId = $user['user_id'];
    } else {
        // Use session ID for guest users
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $sessionId = session_id();
    }
    
    switch ($method) {
        case 'GET':
            handleGetCart($db, $userId, $sessionId);
            break;
        case 'POST':
            handleAddToCart($db, $userId, $sessionId);
            break;
        case 'PUT':
            handleUpdateCart($db, $userId, $sessionId);
            break;
        case 'DELETE':
            handleRemoveFromCart($db, $userId, $sessionId);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Cart API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}

/**
 * Get cart contents
 */
function handleGetCart($db, $userId, $sessionId) {
    try {
        $sql = "SELECT c.cart_id, c.product_id, c.variant_id, c.quantity, c.price,
                       p.name, p.slug, p.short_description, p.stock_quantity, p.unit,
                       pi.image_url, pi.alt_text,
                       pv.variant_name, pv.variant_value
                FROM cart c
                JOIN products p ON c.product_id = p.product_id
                LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = TRUE
                LEFT JOIN product_variants pv ON c.variant_id = pv.variant_id
                WHERE ";
        
        $params = [];
        if ($userId) {
            $sql .= "c.user_id = ?";
            $params[] = $userId;
        } else {
            $sql .= "c.session_id = ?";
            $params[] = $sessionId;
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        $stmt = $db->execute($sql, $params);
        $cartItems = $stmt->fetchAll();
        
        // Calculate totals
        $subtotal = 0;
        $totalItems = 0;
        
        foreach ($cartItems as &$item) {
            $item['total_price'] = $item['quantity'] * $item['price'];
            $subtotal += $item['total_price'];
            $totalItems += $item['quantity'];
        }
        
        echo json_encode([
            'success' => true,
            'cart' => [
                'items' => $cartItems,
                'subtotal' => $subtotal,
                'total_items' => $totalItems,
                'user_id' => $userId,
                'session_id' => $sessionId
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Get cart error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch cart'
        ]);
    }
}

/**
 * Add item to cart
 */
function handleAddToCart($db, $userId, $sessionId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    if (!isset($input['product_id']) || !isset($input['quantity'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID and quantity are required']);
        return;
    }
    
    try {
        $productId = (int)$input['product_id'];
        $variantId = isset($input['variant_id']) ? (int)$input['variant_id'] : null;
        $quantity = (int)$input['quantity'];
        
        if ($quantity <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0']);
            return;
        }
        
        // Get product details
        $productSql = "SELECT product_id, name, price, stock_quantity, status FROM products WHERE product_id = ?";
        $productStmt = $db->execute($productSql, [$productId]);
        $product = $productStmt->fetch();
        
        if (!$product || $product['status'] !== 'active') {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found or inactive']);
            return;
        }
        
        if ($quantity > $product['stock_quantity']) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Insufficient stock. Available: ' . $product['stock_quantity']
            ]);
            return;
        }
        
        // Check if item already exists in cart
        $checkSql = "SELECT cart_id, quantity FROM cart WHERE product_id = ? AND ";
        $checkParams = [$productId];
        
        if ($userId) {
            $checkSql .= "user_id = ?";
            $checkParams[] = $userId;
        } else {
            $checkSql .= "session_id = ?";
            $checkParams[] = $sessionId;
        }
        
        if ($variantId) {
            $checkSql .= " AND variant_id = ?";
            $checkParams[] = $variantId;
        } else {
            $checkSql .= " AND variant_id IS NULL";
        }
        
        $checkStmt = $db->execute($checkSql, $checkParams);
        $existingItem = $checkStmt->fetch();
        
        if ($existingItem) {
            // Update existing item
            $newQuantity = $existingItem['quantity'] + $quantity;
            
            if ($newQuantity > $product['stock_quantity']) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot add more items. Stock limit reached.'
                ]);
                return;
            }
            
            $updateSql = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE cart_id = ?";
            $db->execute($updateSql, [$newQuantity, $existingItem['cart_id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated successfully',
                'action' => 'updated'
            ]);
        } else {
            // Add new item
            $insertSql = "INSERT INTO cart (user_id, session_id, product_id, variant_id, quantity, price, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $insertParams = [$userId, $sessionId, $productId, $variantId, $quantity, $product['price']];
            $db->execute($insertSql, $insertParams);
            
            echo json_encode([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'action' => 'added'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Add to cart error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add item to cart'
        ]);
    }
}

/**
 * Update cart item quantity
 */
function handleUpdateCart($db, $userId, $sessionId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['cart_id']) || !isset($input['quantity'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cart ID and quantity are required']);
        return;
    }
    
    try {
        $cartId = (int)$input['cart_id'];
        $quantity = (int)$input['quantity'];
        
        if ($quantity <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0']);
            return;
        }
        
        // Get cart item and product details
        $sql = "SELECT c.cart_id, c.product_id, c.user_id, c.session_id, p.stock_quantity
                FROM cart c
                JOIN products p ON c.product_id = p.product_id
                WHERE c.cart_id = ?";
        
        $stmt = $db->execute($sql, [$cartId]);
        $cartItem = $stmt->fetch();
        
        if (!$cartItem) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Cart item not found']);
            return;
        }
        
        // Verify ownership
        if (($userId && $cartItem['user_id'] != $userId) || 
            (!$userId && $cartItem['session_id'] != $sessionId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }
        
        if ($quantity > $cartItem['stock_quantity']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient stock. Available: ' . $cartItem['stock_quantity']
            ]);
            return;
        }
        
        // Update quantity
        $updateSql = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE cart_id = ?";
        $db->execute($updateSql, [$quantity, $cartId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Cart updated successfully'
        ]);
        
    } catch (Exception $e) {
        error_log("Update cart error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update cart'
        ]);
    }
}

/**
 * Remove item from cart
 */
function handleRemoveFromCart($db, $userId, $sessionId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['cart_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cart ID is required']);
        return;
    }
    
    try {
        $cartId = (int)$input['cart_id'];
        
        // Get cart item for ownership verification
        $checkSql = "SELECT user_id, session_id FROM cart WHERE cart_id = ?";
        $checkStmt = $db->execute($checkSql, [$cartId]);
        $cartItem = $checkStmt->fetch();
        
        if (!$cartItem) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Cart item not found']);
            return;
        }
        
        // Verify ownership
        if (($userId && $cartItem['user_id'] != $userId) || 
            (!$userId && $cartItem['session_id'] != $sessionId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }
        
        // Remove item
        $deleteSql = "DELETE FROM cart WHERE cart_id = ?";
        $stmt = $db->execute($deleteSql, [$cartId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Item removed from cart'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to remove item'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Remove from cart error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to remove item from cart'
        ]);
    }
}
?>

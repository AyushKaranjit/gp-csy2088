<?php
/**
 * Cart Update API
 * Update cart item quantity
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST');
header('Access-Control-Allow-Headers: Content-Type');

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../config/database.php';
require_once '../src/Controllers/AuthController.php';

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
    $quantity = (int)$input['quantity'];
    
    if ($quantity <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Quantity must be greater than 0'
        ]);
        exit;
    }
    
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
    
    // Check if product exists and has enough stock
    $query = "SELECT stock FROM products WHERE product_id = ? AND status = 'active'";
    $stmt = $db->execute($query, [$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }
    
    if ($product['stock'] < $quantity) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Insufficient stock'
        ]);
        exit;
    }
    
    // Update cart item
    $query = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?";
    $stmt = $db->execute($query, [$quantity, $user_id, $product_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Cart updated successfully'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Cart item not found'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Update cart API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating cart'
    ]);
}
?>

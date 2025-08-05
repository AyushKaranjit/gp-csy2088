<?php
/**
 * Cart Add API
 * Add product to cart
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    
    // Check if product exists
    $query = "SELECT product_id, name, price, stock FROM products WHERE product_id = ? AND status = 'active'";
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
    
    // Check stock availability
    if ($product['stock'] < $quantity) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Insufficient stock'
        ]);
        exit;
    }
    
    // Check if item already exists in cart
    $query = "SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $db->execute($query, [$user_id, $product_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing cart item
        $new_quantity = $existing['quantity'] + $quantity;
        $query = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE cart_id = ?";
        $db->execute($query, [$new_quantity, $existing['cart_id']]);
    } else {
        // Add new cart item
        $query = "INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())";
        $db->execute($query, [$user_id, $product_id, $quantity]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Add to cart API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while adding to cart'
    ]);
}
?>

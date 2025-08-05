<?php
/**
 * Cart Get API
 * Get cart contents
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
    
    // Get cart items with product details
    $query = "SELECT c.cart_id, c.product_id, c.quantity, c.created_at,
                     p.name, p.price, p.image, p.stock, p.status
              FROM cart c
              JOIN products p ON c.product_id = p.product_id
              WHERE c.user_id = ? AND p.status = 'active'
              ORDER BY c.created_at DESC";
    
    $stmt = $db->execute($query, [$user_id]);
    $cart_items = $stmt->fetchAll();
    
    $total_amount = 0;
    $total_items = 0;
    $items = [];
    
    foreach ($cart_items as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $total_amount += $item_total;
        $total_items += $item['quantity'];
        
        $items[] = [
            'cart_id' => (int)$item['cart_id'],
            'product_id' => (int)$item['product_id'],
            'name' => $item['name'],
            'price' => (float)$item['price'],
            'quantity' => (int)$item['quantity'],
            'image' => $item['image'],
            'stock' => (int)$item['stock'],
            'item_total' => (float)$item_total,
            'created_at' => $item['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'items' => $items,
        'total_items' => $total_items,
        'total_amount' => (float)$total_amount
    ]);
    
} catch (Exception $e) {
    error_log("Get cart API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while getting cart'
    ]);
}
?>

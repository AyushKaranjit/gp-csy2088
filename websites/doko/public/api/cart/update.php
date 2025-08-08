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

// Include database configuration with error handling
$config_path = __DIR__ . '/../../../config/database.php';
if (!file_exists($config_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Configuration file not found']);
    exit;
}

require_once $config_path;
require_once __DIR__ . '/../../../src/Controllers/AuthController.php';

try {
    // Get JSON input or fallback
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (!$input) { $input = $_POST; }
    if (!isset($input['product_id']) || !isset($input['quantity'])) {
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
            'message' => 'Authentication required'
        ]);
        exit;
    }
    
    $user = $auth->getCurrentUser();
    $user_id = $user['user_id'];
    
    // Get database connection
    $db = Database::getInstance();
    
    // Check if product exists and has enough stock
    $query = "SELECT stock_quantity FROM products WHERE product_id = ? AND status = 'active'";
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
    
    if ($product['stock_quantity'] < $quantity) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Insufficient stock'
        ]);
        exit;
    }
    
    // Ensure price column present for legacy entries (if null set to current product price)
    $db->execute("UPDATE cart SET price = ? WHERE user_id = ? AND product_id = ? AND (price IS NULL OR price = 0)", [$product['stock_quantity']?($product['price']??0):($product['price']??0), $user_id, $product_id]);
    // Update cart item quantity
    $query = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?";
    $stmt = $db->execute($query, [$quantity, $user_id, $product_id]);
    
    if ($stmt->rowCount() > 0) {
        // compute new total
        $totalQuery = "SELECT SUM(c.quantity * p.price) as total FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?";
        $totalStmt = $db->execute($totalQuery, [$user_id]);
        $total = (float) ($totalStmt->fetchColumn() ?? 0);
        echo json_encode([
            'success' => true,
            'message' => 'Cart updated successfully',
            'total' => $total
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

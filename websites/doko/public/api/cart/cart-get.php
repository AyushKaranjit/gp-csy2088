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
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
    if (!$user || !isset($user['user_id'])) {
        echo json_encode([
            'success' => true,
            'items' => [],
            'total_items' => 0,
            'total_amount' => 0.0,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    $user_id = $user['user_id'];
    
    // Get database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get cart items with product details using simpler query first
    $query = "SELECT c.cart_id, c.product_id, c.quantity, c.created_at,
                     p.name, p.price, p.stock_quantity, p.status
              FROM cart c
              JOIN products p ON c.product_id = p.product_id
              WHERE c.user_id = ? AND p.status = 'active'
              ORDER BY c.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_amount = 0;
    $total_items = 0;
    $items = [];
    
    foreach ($cart_items as $item) {
        $item_total = floatval($item['price']) * intval($item['quantity']);
        $total_amount += $item_total;
        $total_items += intval($item['quantity']);
        
        $items[] = [
            'cart_id' => (int)$item['cart_id'],
            'product_id' => (int)$item['product_id'],
            'name' => $item['name'],
            'price' => (float)$item['price'],
            'quantity' => (int)$item['quantity'],
            'image' => 'uploads/products/default.svg', // Use default image for now
            'stock' => (int)$item['stock_quantity'],
            'item_total' => (float)$item_total,
            'created_at' => $item['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'items' => $items,
        // legacy alias expected by tests
        'total_items' => $total_items,
        'total_amount' => (float)$total_amount,
        'total' => (float)$total_amount
    ]);
    
} catch (Exception $e) {
    error_log("Get cart API error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
    // Always return JSON, never HTML
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred',
        'error_details' => $e->getMessage(),
        'items' => [],
        'total_items' => 0,
        'total_amount' => 0.0
    ]);
}
?>

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
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
            'message' => 'Please log in to add items to cart',
            'redirect' => '/login.php'
        ]);
        exit;
    }
    
    $user = $auth->getCurrentUser();
    if (!$user || !isset($user['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    $user_id = $user['user_id'];
    
    // Get database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if product exists
    $query = "SELECT product_id, name, price, stock_quantity FROM products WHERE product_id = ? AND status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }
    
    // Check stock availability
    if ($product['stock_quantity'] < $quantity) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Insufficient stock'
        ]);
        exit;
    }
    
    // Check if item already exists in cart
    $query = "SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing cart item
        $new_quantity = $existing['quantity'] + $quantity;
        $query = "UPDATE cart SET quantity = ?, created_at = NOW() WHERE cart_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$new_quantity, $existing['cart_id']]);
    } else {
        // Add new cart item
        $query = "INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->execute([$user_id, $product_id, $quantity]);
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

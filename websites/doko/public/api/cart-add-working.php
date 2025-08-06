<?php
/**
 * Working Cart Add API
 * Simplified version that works with session and database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Include database configuration
    require_once __DIR__ . '/../../config/database.php';
    
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    // Log the input for debugging
    error_log("Cart Add Input: " . json_encode($input));
    
    if (!isset($input['product_id']) || !isset($input['quantity'])) {
        echo json_encode(['success' => false, 'message' => 'Product ID and quantity are required', 'debug' => $input]);
        exit;
    }
    
    $product_id = (int)$input['product_id'];
    $quantity = (int)$input['quantity'];
    
    error_log("Cart Add Parsed - Product ID: $product_id, Quantity: $quantity");
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }
    
    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0', 'debug' => ['original_quantity' => $input['quantity'], 'parsed_quantity' => $quantity]]);
        exit;
    }
    
    // Get database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if product exists
    $stmt = $conn->prepare("SELECT product_id, name, price, stock_quantity FROM products WHERE product_id = ? AND status = 'active'");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Check stock
    if ($product['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock. Only ' . $product['stock_quantity'] . ' available.']);
        exit;
    }
    
    // For now, just use session cart (works without login)
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'product_id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity
        ];
    }
    
    $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
    
    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart successfully!',
        'product_name' => $product['name'],
        'quantity' => $quantity,
        'cart_count' => $cart_count
    ]);
    
} catch (Exception $e) {
    error_log("Cart Add Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>

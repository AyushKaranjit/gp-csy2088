<?php
/**
 * Simple Cart Add API
 * Basic cart functionality with error handling
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Simple response function
function sendResponse($success, $message = '', $data = []) {
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $data);
    echo json_encode($response);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Only POST method allowed');
    }
    
    // Basic database connection
    $host = 'localhost';
    $dbname = 'doko_grocery';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Check if user is logged in
    session_start();
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        sendResponse(false, 'Please log in to add items to cart');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = (int)($input['product_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 1);
    
    if (!$productId || $quantity < 1) {
        sendResponse(false, 'Invalid product ID or quantity');
    }
    
    // Check if product exists
    $productStmt = $pdo->prepare("SELECT product_id, name, price FROM products WHERE product_id = ?");
    $productStmt->execute([$productId]);
    $product = $productStmt->fetch();
    
    if (!$product) {
        sendResponse(false, 'Product not found');
    }
    
    // Create cart table if it doesn't exist
    $createTable = "
        CREATE TABLE IF NOT EXISTS cart (
            cart_id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_cart_item (user_id, product_id)
        )
    ";
    $pdo->exec($createTable);
    
    // Check if item already exists in cart
    $checkStmt = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $checkStmt->execute([$userId, $productId]);
    $existingItem = $checkStmt->fetch();
    
    if ($existingItem) {
        // Update quantity
        $newQuantity = $existingItem['quantity'] + $quantity;
        $updateStmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $updateStmt->execute([$newQuantity, $existingItem['cart_id']]);
        $message = 'Cart updated successfully';
    } else {
        // Add new item
        $insertStmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insertStmt->execute([$userId, $productId, $quantity]);
        $message = 'Added to cart successfully';
    }
    
    sendResponse(true, $message);
    
} catch (Exception $e) {
    error_log("Cart Add API Error: " . $e->getMessage());
    sendResponse(false, 'Server error occurred');
}
?>

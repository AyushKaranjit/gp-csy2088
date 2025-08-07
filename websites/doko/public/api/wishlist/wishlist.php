<?php
/**
 * Simple Wishlist API
 * Handles basic wishlist operations with error handling
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Simple response function
function sendResponse($success, $message = '', $data = []) {
    $response = array_merge([
        'success' => $success,
        'message' => $message,
        'count' => 0,
        'items' => []
    ], $data);
    echo json_encode($response);
    exit;
}

try {
    // Use proper database configuration
    require_once __DIR__ . '/../../../config/database.php';
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Check if user is logged in (simple session check)
    session_start();
    $userId = $_SESSION['user_id'] ?? null;
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Handle GET requests (get wishlist)
    if ($method === 'GET') {
        if (!$userId) {
            sendResponse(true, 'Not logged in', ['count' => 0, 'items' => []]);
        }
        
        // Check if wishlist table exists
        $checkTable = $pdo->query("SHOW TABLES LIKE 'wishlist'")->rowCount();
        if ($checkTable === 0) {
            sendResponse(true, 'Wishlist empty', ['count' => 0, 'items' => []]);
        }
        
        // Get wishlist items
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
        $stmt->execute([$userId]);
        $count = $stmt->fetch()['count'] ?? 0;
        
        sendResponse(true, 'Wishlist loaded', ['count' => (int)$count, 'items' => []]);
    }
    
    // Handle POST requests (add/toggle wishlist)
    if ($method === 'POST') {
        if (!$userId) {
            sendResponse(false, 'Please log in to use wishlist');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = (int)($input['product_id'] ?? 0);
        $action = $input['action'] ?? 'add';
        
        if (!$productId) {
            sendResponse(false, 'Invalid product ID');
        }
        
        // Check if product exists
        $productStmt = $pdo->prepare("SELECT product_id, name FROM products WHERE product_id = ?");
        $productStmt->execute([$productId]);
        $product = $productStmt->fetch();
        
        if (!$product) {
            sendResponse(false, 'Product not found');
        }
        
        // Check if wishlist table exists, create if not
        $createTable = "
            CREATE TABLE IF NOT EXISTS wishlist (
                wishlist_id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                product_id INT NOT NULL,
                added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_wishlist (user_id, product_id)
            )
        ";
        $pdo->exec($createTable);
        
        // Check if item is in wishlist
        $checkStmt = $pdo->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $checkStmt->execute([$userId, $productId]);
        $existsInWishlist = $checkStmt->fetch();
        
        if ($action === 'toggle') {
            if ($existsInWishlist) {
                // Remove from wishlist
                $deleteStmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
                $deleteStmt->execute([$userId, $productId]);
                $message = 'Removed from wishlist';
                $inWishlist = false;
            } else {
                // Add to wishlist
                $insertStmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
                $insertStmt->execute([$userId, $productId]);
                $message = 'Added to wishlist';
                $inWishlist = true;
            }
        } else {
            if ($existsInWishlist) {
                sendResponse(false, 'Item already in wishlist');
            }
            
            // Add to wishlist
            $insertStmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $insertStmt->execute([$userId, $productId]);
            $message = 'Added to wishlist';
            $inWishlist = true;
        }
        
        // Get updated count
        $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
        $countStmt->execute([$userId]);
        $count = $countStmt->fetch()['count'] ?? 0;
        
        sendResponse(true, $message, [
            'count' => (int)$count,
            'in_wishlist' => $inWishlist
        ]);
    }
    
    // Handle DELETE requests
    if ($method === 'DELETE') {
        if (!$userId) {
            sendResponse(false, 'Please log in to use wishlist');
        }
        
        $productId = (int)($_GET['product_id'] ?? 0);
        if (!$productId) {
            sendResponse(false, 'Invalid product ID');
        }
        
        $deleteStmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $deleteStmt->execute([$userId, $productId]);
        
        $countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
        $countStmt->execute([$userId]);
        $count = $countStmt->fetch()['count'] ?? 0;
        
        sendResponse(true, 'Removed from wishlist', ['count' => (int)$count]);
    }
    
    sendResponse(false, 'Method not allowed');
    
} catch (Exception $e) {
    error_log("Wishlist API Error: " . $e->getMessage());
    sendResponse(false, 'Server error occurred');
}
?>

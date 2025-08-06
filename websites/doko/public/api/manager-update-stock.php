<?php
/**
 * Manager Stock Update API
 * Allow managers to update product stock quantities
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

try {
    // Start session
    session_start();
    
    // Check if user is logged in and has manager or admin privileges
    require_once __DIR__ . '/../../src/Controllers/AuthController.php';
    $auth = new AuthController();
    
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }
    
    if (!$auth->hasManagerAccess()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied. Manager privileges required.']);
        exit;
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id']) || !isset($input['stock_quantity'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID and stock quantity are required']);
        exit;
    }
    
    // Validate stock quantity
    $stockQuantity = intval($input['stock_quantity']);
    if ($stockQuantity < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Stock quantity cannot be negative']);
        exit;
    }
    
    // Include database configuration
    require_once __DIR__ . '/../../config/database.php';
    
    $database = Database::getInstance();
    $pdo = $database->getConnection();
    
    // Get current stock for logging
    $stmt = $pdo->prepare('SELECT product_id, name, stock_quantity FROM products WHERE product_id = ?');
    $stmt->execute([$input['product_id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    $oldStock = $product['stock_quantity'];
    
    // Update stock quantity
    $stmt = $pdo->prepare('UPDATE products SET stock_quantity = ?, updated_at = NOW() WHERE product_id = ?');
    $result = $stmt->execute([$stockQuantity, $input['product_id']]);
    
    if ($result) {
        // Log stock change for audit trail
        $reason = $input['reason'] ?? 'manager_update';
        $notes = $input['notes'] ?? '';
        $userId = $_SESSION['user_id'];
        
        // You could create a stock_history table to track changes
        error_log("Stock updated - Product: {$product['name']} (ID: {$input['product_id']}) | Old: {$oldStock} | New: {$stockQuantity} | Reason: {$reason} | Manager: {$userId}");
        
        // Optional: Insert into stock_history table if it exists
        try {
            $historyStmt = $pdo->prepare('INSERT INTO stock_history (product_id, old_quantity, new_quantity, change_reason, notes, changed_by, changed_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $historyStmt->execute([
                $input['product_id'],
                $oldStock,
                $stockQuantity,
                $reason,
                $notes,
                $userId
            ]);
        } catch (Exception $e) {
            // Stock history table might not exist, ignore error
            error_log("Stock history logging failed (table might not exist): " . $e->getMessage());
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Stock updated successfully',
            'product_id' => $input['product_id'],
            'old_stock' => $oldStock,
            'new_stock' => $stockQuantity
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update stock']);
    }
    
} catch (Exception $e) {
    error_log("Manager update stock API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating stock']);
}
?>

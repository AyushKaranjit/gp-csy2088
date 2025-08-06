<?php
/**
 * Manager Product Update API
 * Allow managers to update product information
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
    
    if (!$input || !isset($input['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        exit;
    }
    
    // Include database configuration
    require_once __DIR__ . '/../../config/database.php';
    
    $database = Database::getInstance();
    $pdo = $database->getConnection();
    
    // Validate that product exists
    $stmt = $pdo->prepare('SELECT product_id FROM products WHERE product_id = ?');
    $stmt->execute([$input['product_id']]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Prepare update fields
    $updateFields = [];
    $updateValues = [];
    
    if (isset($input['name']) && !empty($input['name'])) {
        $updateFields[] = 'name = ?';
        $updateValues[] = trim($input['name']);
    }
    
    if (isset($input['price']) && is_numeric($input['price'])) {
        $updateFields[] = 'price = ?';
        $updateValues[] = floatval($input['price']);
    }
    
    if (isset($input['stock_quantity']) && is_numeric($input['stock_quantity'])) {
        $updateFields[] = 'stock_quantity = ?';
        $updateValues[] = intval($input['stock_quantity']);
    }
    
    if (isset($input['description'])) {
        $updateFields[] = 'description = ?';
        $updateValues[] = trim($input['description']);
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
        exit;
    }
    
    // Add updated_at timestamp
    $updateFields[] = 'updated_at = NOW()';
    
    // Add product_id for WHERE clause
    $updateValues[] = $input['product_id'];
    
    // Update product
    $sql = 'UPDATE products SET ' . implode(', ', $updateFields) . ' WHERE product_id = ?';
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($updateValues);
    
    if ($result) {
        // Log activity
        error_log("Manager updated product {$input['product_id']} - User: {$_SESSION['user_id']}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully',
            'product_id' => $input['product_id']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update product']);
    }
    
} catch (Exception $e) {
    error_log("Manager update product API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating product']);
}
?>

<?php
/**
 * Wishlist API Endpoint
 * Handles wishlist operations (add, remove, get)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Disable error display, log errors instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    // Include required files with error handling
    $configPath = __DIR__ . '/../../config/database.php';
    $authPath = __DIR__ . '/../../src/Controllers/AuthController.php';
    
    if (!file_exists($configPath)) {
        throw new Exception('Database config not found');
    }
    if (!file_exists($authPath)) {
        throw new Exception('Auth controller not found');
    }
    
    require_once $configPath;
    require_once $authPath;
    
    // Initialize with error handling
    $db = Database::getInstance();
    if (!$db) {
        throw new Exception('Database connection failed');
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    // For GET requests, return basic response even if not logged in
    if ($method === 'GET') {
        try {
            $auth = new AuthController();
            if (!$auth->isLoggedIn()) {
                echo json_encode([
                    'success' => true,
                    'count' => 0,
                    'items' => [],
                    'message' => 'Not logged in'
                ]);
                exit;
            }
            
            $user = $auth->getCurrentUser();
            $userId = $user['user_id'];
            handleGetWishlist($db, $userId);
            
        } catch (Exception $e) {
            error_log("Wishlist GET error: " . $e->getMessage());
            echo json_encode([
                'success' => true,
                'count' => 0,
                'items' => [],
                'message' => 'Error loading wishlist'
            ]);
        }
        exit;
    }
    
    // For POST/DELETE, authentication required
    $auth = new AuthController();
    if (!$auth->isLoggedIn()) {
        echo json_encode([
            'success' => false,
            'message' => 'Please log in to use wishlist',
            'count' => 0
        ]);
        exit;
    }
    
    $user = $auth->getCurrentUser();
    $userId = $user['user_id'];
    
    switch ($method) {
        case 'GET':
            handleGetWishlist($db, $userId);
            break;
        case 'POST':
            handleAddToWishlist($db, $userId);
            break;
        case 'DELETE':
            handleRemoveFromWishlist($db, $userId);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Wishlist API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'count' => 0
    ]);
}

/**
 * Get wishlist items
 */
function handleGetWishlist($db, $userId) {
    try {
        // First check if wishlist table exists
        $checkTable = "SHOW TABLES LIKE 'wishlist'";
        $tableCheck = $db->getConnection()->query($checkTable);
        
        if ($tableCheck->rowCount() === 0) {
            // Table doesn't exist, return empty result
            echo json_encode([
                'success' => true,
                'count' => 0,
                'items' => []
            ]);
            return;
        }
        
        // Get wishlist count with simple query
        $countSql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
        $countResult = $db->getConnection()->prepare($countSql);
        $countResult->execute([$userId]);
        $count = $countResult->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        
        // Get wishlist items with simpler query
        $sql = "SELECT w.wishlist_id, w.product_id, w.added_at,
                       p.name, p.price, p.stock_quantity
                FROM wishlist w
                JOIN products p ON w.product_id = p.product_id
                WHERE w.user_id = ?
                ORDER BY w.added_at DESC";
        
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$userId]);
        $wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'count' => (int)$count,
            'items' => $wishlistItems
        ]);
        
    } catch (Exception $e) {
        error_log("Get wishlist error: " . $e->getMessage());
        echo json_encode([
            'success' => true,
            'count' => 0,
            'items' => [],
            'error' => 'Database error'
        ]);
    }
}

/**
 * Add item to wishlist
 */
function handleAddToWishlist($db, $userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        return;
    }
    
    try {
        $productId = (int)$input['product_id'];
        
        // Check if product exists and is active
        $productSql = "SELECT product_id, name, status FROM products WHERE product_id = ?";
        $productStmt = $db->execute($productSql, [$productId]);
        $product = $productStmt->fetch();
        
        if (!$product || $product['status'] !== 'active') {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found or inactive']);
            return;
        }
        
        // Check if already in wishlist
        $checkSql = "SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?";
        $checkStmt = $db->execute($checkSql, [$userId, $productId]);
        
        if ($checkStmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Product already in wishlist'
            ]);
            return;
        }
        
        // Add to wishlist
        $insertSql = "INSERT INTO wishlist (user_id, product_id, added_at) VALUES (?, ?, NOW())";
        $db->execute($insertSql, [$userId, $productId]);
        
        // Get updated count
        $countSql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
        $countStmt = $db->execute($countSql, [$userId]);
        $count = $countStmt->fetch()['count'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Added to wishlist',
            'count' => (int)$count
        ]);
        
    } catch (Exception $e) {
        error_log("Add to wishlist error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add to wishlist'
        ]);
    }
}

/**
 * Remove item from wishlist
 */
function handleRemoveFromWishlist($db, $userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        return;
    }
    
    try {
        $productId = (int)$input['product_id'];
        
        // Remove from wishlist
        $deleteSql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = $db->execute($deleteSql, [$userId, $productId]);
        
        if ($stmt->rowCount() > 0) {
            // Get updated count
            $countSql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
            $countStmt = $db->execute($countSql, [$userId]);
            $count = $countStmt->fetch()['count'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Removed from wishlist',
                'count' => (int)$count
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Product not found in wishlist'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Remove from wishlist error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to remove from wishlist'
        ]);
    }
}
?>

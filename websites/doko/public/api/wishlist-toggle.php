<?php
require_once '../../config/database.php';
require_once '../../src/Controllers/AuthController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

try {
    $authController = new AuthController();
    
    if (!$authController->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Please login to manage wishlist']);
        exit;
    }

    $user = $authController->getCurrentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['product_id']) || !is_numeric($input['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }

    $productId = intval($input['product_id']);
    $userId = $user['id'];

    // Check if database connection is available
    if (!isset($pdo)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection not available']);
        exit;
    }

    // Check if product exists
    $checkProduct = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $checkProduct->execute([$productId]);
    if (!$checkProduct->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    // Check if item is already in wishlist
    $checkWishlist = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $checkWishlist->execute([$userId, $productId]);
    $existingItem = $checkWishlist->fetch();

    if ($existingItem) {
        // Remove from wishlist
        $removeItem = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $removeItem->execute([$userId, $productId]);
        
        echo json_encode([
            'success' => true, 
            'added' => false,
            'message' => 'Removed from wishlist'
        ]);
    } else {
        // Add to wishlist
        $addItem = $pdo->prepare("INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())");
        $addItem->execute([$userId, $productId]);
        
        echo json_encode([
            'success' => true, 
            'added' => true,
            'message' => 'Added to wishlist'
        ]);
    }

} catch (Exception $e) {
    error_log("Wishlist Toggle Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
    }

    // Database connection
    $database = Database::getInstance();
    $conn = $database->getConnection();

    // Check if product exists
    $stmt = $conn->prepare("SELECT product_id, name FROM products WHERE product_id = ? AND status = 'active'");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        throw new Exception('Product not found');
    }

    // Create wishlist table if it doesn't exist
    $createTable = "CREATE TABLE IF NOT EXISTS wishlist (
        wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_wishlist (user_id, product_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
    )";
    $conn->exec($createTable);

    // Check if item is already in wishlist
    $stmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $wishlistItem = $stmt->fetch();

    $inWishlist = !empty($wishlistItem);
    $action = '';

    if ($inWishlist) {
        // Remove from wishlist
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        
        if ($stmt->execute([$userId, $productId])) {
            $action = 'removed from';
            $inWishlist = false;
        } else {
            throw new Exception('Failed to remove from wishlist');
        }
    } else {
        // Add to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        
        if ($stmt->execute([$userId, $productId])) {
            $action = 'added to';
            $inWishlist = true;
        } else {
            throw new Exception('Failed to add to wishlist');
        }
    }

    // Get updated wishlist count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    $wishlistCount = $result['count'];

    echo json_encode([
        'success' => true,
        'message' => "Product {$action} wishlist successfully",
        'action' => $action,
        'inWishlist' => $inWishlist,
        'wishlistCount' => $wishlistCount
    ]);

} catch (Exception $e) {
    error_log("Wishlist Toggle Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

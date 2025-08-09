<?php
/** Wishlist API (refactored thin wrapper) */
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method(['GET','POST','DELETE']);

try {
    $auth = auth_controller();
    $isLoggedIn = $auth->isLoggedIn();
    $db = db();
    $pdo = $db->getConnection();
    $user = $auth->getCurrentUser();
    $userId = $isLoggedIn && $user && isset($user['user_id']) ? (int)$user['user_id'] : 0;
    $method = $_SERVER['REQUEST_METHOD'];

    // Ensure table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlist (wishlist_id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, product_id INT NOT NULL, added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uniq_user_product (user_id, product_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if ($method === 'GET') {
        if (!$isLoggedIn) {
            ApiResponse::error('Authentication required', 401, ['count' => 0, 'items' => [], 'is_logged_in' => false]);
            return;
        }
        $stmt = $pdo->prepare('SELECT product_id, added_at FROM wishlist WHERE user_id=? ORDER BY added_at DESC');
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll();
        ApiResponse::success(['count' => count($items), 'items' => $items, 'is_logged_in' => true]);
        return;
    }

    $input = json_input();
    if ($method === 'POST') {
        if (!$isLoggedIn) { ApiResponse::error('Authentication required', 401); return; }
        $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
        $action = $input['action'] ?? 'add';
        if ($productId <= 0) { ApiResponse::error('Invalid product ID', 400); return; }
        $exists = $pdo->prepare('SELECT wishlist_id FROM wishlist WHERE user_id=? AND product_id=?');
        $exists->execute([$userId, $productId]);
        $row = $exists->fetch();
        $message = '';
        $inWishlist = false;
        if ($action === 'toggle') {
            if ($row) {
                $del = $pdo->prepare('DELETE FROM wishlist WHERE user_id=? AND product_id=?');
                $del->execute([$userId, $productId]);
                $message = 'Removed from wishlist';
            } else {
                $ins = $pdo->prepare('INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)');
                $ins->execute([$userId, $productId]);
                $message = 'Added to wishlist';
                $inWishlist = true;
            }
        } else { // add
            if ($row) { ApiResponse::error('Item already in wishlist', 409); return; }
            $ins = $pdo->prepare('INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)');
            $ins->execute([$userId, $productId]);
            $message = 'Added to wishlist';
            $inWishlist = true;
        }
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id=?');
        $countStmt->execute([$userId]);
        $count = (int)$countStmt->fetchColumn();
        ApiResponse::success([
            'message' => $message,
            'count' => $count,
            'in_wishlist' => $inWishlist,
            'is_logged_in' => true
        ]);
        return;
    }

    if ($method === 'DELETE') {
        if (!$isLoggedIn) { ApiResponse::error('Authentication required', 401); return; }
        $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
        if ($productId <= 0) { ApiResponse::error('Invalid product ID', 400); return; }
        $del = $pdo->prepare('DELETE FROM wishlist WHERE user_id=? AND product_id=?');
        $del->execute([$userId, $productId]);
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id=?');
        $countStmt->execute([$userId]);
        $count = (int)$countStmt->fetchColumn();
        ApiResponse::success([
            'message' => 'Removed from wishlist',
            'count' => $count,
            'is_logged_in' => true
        ]);
        return;
    }

    ApiResponse::error('Method not allowed', 405);
} catch (Exception $e) {
    error_log('Wishlist API error: ' . $e->getMessage());
    ApiResponse::error('Server error occurred', 500);
}
?>

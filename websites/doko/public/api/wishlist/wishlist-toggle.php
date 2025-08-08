<?php
// Cleaned and consolidated wishlist toggle endpoint
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/AuthController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $auth = new AuthController();
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    $user = $auth->getCurrentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Only POST allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    if (empty($input['product_id']) || !ctype_digit(strval($input['product_id']))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid product_id']);
        exit;
    }
    $productId = (int)$input['product_id'];
    $userId = (int)$user['user_id']; // AuthController stores user_id

    $pdo = Database::getInstance()->getConnection();

    // Ensure wishlist table (idempotent creation)
    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlist (
        wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_user_product (user_id, product_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Validate product exists and active
    $stmt = $pdo->prepare("SELECT product_id FROM products WHERE product_id = ? AND is_active = 1");
    $stmt->execute([$productId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    // Toggle logic
    $stmt = $pdo->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $del = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $del->execute([$userId, $productId]);
        $added = false;
        $message = 'Removed from wishlist';
    } else {
        $ins = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $ins->execute([$userId, $productId]);
        $added = true;
        $message = 'Added to wishlist';
    }

    // Updated count
    $count = $pdo->prepare("SELECT COUNT(*) AS c FROM wishlist WHERE user_id = ?");
    $count->execute([$userId]);
    $wishlistCount = (int)$count->fetch()['c'];

    echo json_encode([
        'success' => true,
        'added' => $added,
        'message' => $message,
        'wishlist_count' => $wishlistCount
    ]);
} catch (Throwable $e) {
    error_log('Wishlist Toggle Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>

<?php
/**
 * Wishlist Get API
 * Returns authenticated user's wishlist with product data & primary image.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/AuthController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

try {
    $auth = new AuthController();
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    $user = $auth->getCurrentUser();
    if (!$user) {
        echo json_encode(['success' => true, 'count' => 0, 'items' => []]);
        exit;
    }

    $userId = (int)$user['user_id'];
    $pdo = Database::getInstance()->getConnection();

    $sql = "SELECT 
                w.wishlist_id,
                w.product_id,
                p.name,
                p.price,
                p.stock_quantity,
                pi.image_url AS primary_image,
                w.added_at
            FROM wishlist w
            INNER JOIN products p ON w.product_id = p.product_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
            WHERE w.user_id = ?
            ORDER BY w.added_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $items = [];
    foreach ($rows as $row) {
        $candidate = $row['primary_image'] ?? '';
        if ($candidate) {
            if (preg_match('#^https?://#i', $candidate)) {
                $image_url = $candidate;
            } else {
                $image_url = '/uploads/' . $candidate;
            }
        } else {
            $image_url = '/uploads/default-product.jpg';
        }
        $items[] = [
            'wishlist_id' => (int)$row['wishlist_id'],
            'product_id'  => (int)$row['product_id'],
            'name'        => $row['name'],
            'price'       => (float)$row['price'],
            'stock'       => (int)$row['stock_quantity'],
            'image_url'   => $image_url,
            'added_at'    => $row['added_at'],
            'in_stock'    => ((int)$row['stock_quantity']) > 0
        ];
    }

    echo json_encode(['success' => true, 'count' => count($items), 'items' => $items]);
} catch (Exception $e) {
    error_log('Wishlist Get Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>

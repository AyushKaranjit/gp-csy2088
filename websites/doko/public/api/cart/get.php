<?php
// Canonical GET cart endpoint (unified). Wrapper cart-get.php requires this file.
// Replaces legacy non-bootstrap implementation.
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method('GET');

try {
    $auth = auth_controller();
    $isLoggedIn = $auth->isLoggedIn();
    $user = $auth->getCurrentUser();
    if (!$isLoggedIn || !$user || !isset($user['user_id'])) {
        ApiResponse::error('Authentication required', 401, [
            'items' => [],
            'total_items' => 0,
            'total_amount' => 0.0,
            'total' => 0.0,
            'is_logged_in' => false
        ]);
        return;
    }

    $db = db();
    $productsPk = schema_products_pk();
    $cartPk = schema_cart_pk();

    $sql = "SELECT c.{$cartPk} AS cart_id, c.product_id, c.quantity, c.created_at, p.name, p.price, p.stock_quantity,
                   COALESCE(pi.image_url, '/images/default-product.jpg') AS product_image
            FROM cart c
            JOIN products p ON c.product_id = p.{$productsPk}
            LEFT JOIN product_images pi ON p.{$productsPk} = pi.product_id AND pi.is_primary = 1
            WHERE c.user_id = ? AND p.status = 'active'
            ORDER BY c.created_at DESC";
    $stmt = $db->execute($sql, [$user['user_id']]);
    $rows = $stmt->fetchAll();

    $items = [];
    $total = 0.0; $totalItems = 0;
    foreach ($rows as $r) {
        $itemTotal = (float)$r['price'] * (int)$r['quantity'];
        $total += $itemTotal; $totalItems += (int)$r['quantity'];
        
        // Determine image path (support external absolute URL or local upload filename)
        $image_url = '/images/default-product.jpg';
        $candidate = $r['product_image'] ?? '';
        if ($candidate) {
            if (preg_match('#^https?://#', $candidate)) {
                $image_url = $candidate; // external
            } else {
                $image_url = '/uploads/' . $candidate;
            }
        }
        
        $items[] = [
            'cart_id' => (int)$r['cart_id'],
            'product_id' => (int)$r['product_id'],
            'name' => $r['name'],
            'price' => (float)$r['price'],
            'quantity' => (int)$r['quantity'],
            'image' => $image_url,
            'stock' => (int)$r['stock_quantity'],
            'item_total' => $itemTotal,
            'created_at' => $r['created_at']
        ];
    }

    ApiResponse::success([
        'items' => $items,
        'total_items' => $totalItems,
        'total_amount' => $total,
        'total' => $total,
        'is_logged_in' => true
    ]);
} catch (Throwable $e) {
    error_log('cart-get (unified get.php) error: '.$e->getMessage());
    ApiResponse::error('Failed to fetch cart', 500, ['exception' => $e->getMessage()]);
}
?>

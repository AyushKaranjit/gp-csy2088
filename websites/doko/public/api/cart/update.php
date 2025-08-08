<?php
// Refactored cart update endpoint (by product_id) using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method(['PUT','POST']);

try {
    $auth = auth_controller();
    if (!$auth->isLoggedIn()) {
        ApiResponse::error('Authentication required', 401, ['is_logged_in' => false]);
    }
    $input = json_input();
    $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
    $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 0;
    if ($productId <= 0 || $quantity <= 0) {
        ApiResponse::error('Product ID and quantity are required', 400);
    }

    $db = db();
    $userId = $auth->getCurrentUser()['user_id'];

    $stmt = $db->execute("SELECT stock_quantity, price FROM products WHERE product_id = ? AND status='active'", [$productId]);
    $product = $stmt->fetch();
    if (!$product) {
        ApiResponse::error('Product not found', 404);
    }
    if ((int)$product['stock_quantity'] < $quantity) {
        ApiResponse::error('Insufficient stock', 400);
    }

    // Update or ensure price then quantity
    $db->execute("UPDATE cart SET price = ? WHERE user_id = ? AND product_id = ? AND (price IS NULL OR price = 0)", [(float)($product['price'] ?? 0), $userId, $productId]);
    $res = $db->execute("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?", [$quantity, $userId, $productId]);
    if ($res->rowCount() === 0) {
        ApiResponse::error('Cart item not found', 404);
    }
    $total = (float)($db->execute("SELECT COALESCE(SUM(c.quantity * p.price),0) FROM cart c JOIN products p ON c.product_id=p.product_id WHERE c.user_id = ?", [$userId])->fetchColumn() ?? 0);
    ApiResponse::success(['message' => 'Cart updated successfully', 'total' => $total, 'is_logged_in' => true]);
} catch (Throwable $e) {
    error_log('cart-update error: '.$e->getMessage());
    ApiResponse::error('Failed to update cart', 500, ['exception' => $e->getMessage()]);
}

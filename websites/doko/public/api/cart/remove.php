<?php
// Refactored cart remove endpoint (by product_id) using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method(['DELETE','POST']);

try {
    $auth = auth_controller();
    if (!$auth->isLoggedIn()) { ApiResponse::error('Authentication required', 401, ['is_logged_in' => false]); return; }
    $input = json_input();
    $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
    if ($productId <= 0) { ApiResponse::error('Product ID is required', 400); return; }
    $db = db();
    $productsPk = schema_products_pk();
    $userId = $auth->getCurrentUser()['user_id'];
    $res = $db->execute("DELETE FROM cart WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
    if ($res->rowCount() === 0) { ApiResponse::error('Product not found in cart', 404); return; }
    $total = (float)($db->execute("SELECT COALESCE(SUM(c.quantity * p.price),0) FROM cart c JOIN products p ON c.product_id=p.{$productsPk} WHERE c.user_id = ?", [$userId])->fetchColumn() ?? 0);
    ApiResponse::success(['message' => 'Product removed from cart successfully', 'total' => $total, 'is_logged_in' => true]);
} catch (Throwable $e) {
    error_log('cart-remove error: '.$e->getMessage());
    ApiResponse::error('Failed to remove from cart', 500, ['exception' => $e->getMessage()]);
}

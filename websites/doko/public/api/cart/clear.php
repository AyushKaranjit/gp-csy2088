<?php
// Refactored cart clear endpoint using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method(['DELETE','POST']);

try {
    $auth = auth_controller();
    if (!$auth->isLoggedIn()) {
        ApiResponse::error('Authentication required', 401, ['is_logged_in' => false]);
    }
    $db = db();
    $userId = $auth->getCurrentUser()['user_id'];
    $res = $db->execute("DELETE FROM cart WHERE user_id = ?", [$userId]);
    ApiResponse::success([
        'message' => 'Cart cleared successfully',
        'items_removed' => $res->rowCount(),
        'total' => 0,
        'is_logged_in' => true
    ]);
} catch (Throwable $e) {
    error_log('cart-clear error: '.$e->getMessage());
    ApiResponse::error('Failed to clear cart', 500, ['exception' => $e->getMessage()]);
}

<?php
// Refactored cancel-order endpoint using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method(['POST','DELETE']);

try {
  $auth = auth_controller();
  if (!$auth->isLoggedIn()) { ApiResponse::error('Authentication required', 401); }
  $input = json_input();
  $orderId = (int)($input['order_id'] ?? 0);
  if ($orderId <= 0) { ApiResponse::error('Order ID required', 400); }
  $db = db();
  $order = $db->execute('SELECT order_id,user_id,status FROM orders WHERE order_id=?', [$orderId])->fetch();
  if (!$order) { ApiResponse::error('Order not found', 404); }
  $user = $auth->getCurrentUser();
  if (!$auth->isAdmin() && (int)$order['user_id'] !== (int)$user['user_id']) { ApiResponse::error('Access denied', 403); }
  if (!in_array($order['status'], ['pending','confirmed'], true)) { ApiResponse::error('Order cannot be cancelled', 400); }
  $db->execute('UPDATE orders SET status="cancelled", updated_at=NOW() WHERE order_id=?', [$orderId]);
  ApiResponse::success(['message' => 'Order cancelled successfully']);
} catch (Throwable $e) {
  error_log('cancel-order error: '.$e->getMessage());
  ApiResponse::error('Server error', 500, ['exception' => $e->getMessage()]);
}

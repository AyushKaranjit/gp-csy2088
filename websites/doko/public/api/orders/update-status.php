<?php
// Refactored update-status endpoint using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method(['POST','PUT','PATCH']);

try {
  $auth = auth_controller();
  if (!$auth->isLoggedIn() || !$auth->isAdmin()) { ApiResponse::error('Admin access required', 403); }
  $input = json_input();
  $orderId = (int)($input['order_id'] ?? 0);
  $status = trim((string)($input['status'] ?? ''));
  if ($orderId <= 0 || $status === '') { ApiResponse::error('Order ID and status required', 400); }
  $valid = ['pending','confirmed','processing','shipped','delivered','cancelled','refunded'];
  if (!in_array($status, $valid, true)) { ApiResponse::error('Invalid status', 400); }
  $db = db();
  $order = $db->execute('SELECT order_id,status FROM orders WHERE order_id=?', [$orderId])->fetch();
  if (!$order) { ApiResponse::error('Order not found', 404); }
  $db->execute('UPDATE orders SET status=?, updated_at=NOW() WHERE order_id=?', [$status,$orderId]);
  ApiResponse::success(['message' => 'Order status updated successfully', 'status' => $status]);
} catch (Throwable $e) {
  error_log('update-status error: '.$e->getMessage());
  ApiResponse::error('Server error', 500, ['exception' => $e->getMessage()]);
}

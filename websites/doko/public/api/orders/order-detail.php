<?php
// Refactored order-detail endpoint using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method('GET');

try {
  $auth = auth_controller();
  if (!$auth->isLoggedIn()) { ApiResponse::error('Authentication required', 401); }
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if ($id <= 0) { ApiResponse::error('Invalid order id', 400); }
  $db = db();
  $order = $db->execute('SELECT * FROM orders WHERE order_id=?', [$id])->fetch();
  if (!$order) { ApiResponse::error('Order not found', 404); }
  $user = $auth->getCurrentUser();
  if (!$auth->isAdmin() && (int)$order['user_id'] !== (int)$user['user_id']) { ApiResponse::error('Access denied', 403); }
  $pk = schema_products_pk();
  $items = $db->execute("SELECT oi.order_item_id, oi.product_id, oi.quantity, oi.unit_price as price, oi.total_price as total, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id=p.$pk WHERE oi.order_id=?", [$id])->fetchAll();
  ApiResponse::success(['order' => $order, 'items' => $items]);
} catch (Throwable $e) {
  error_log('order-detail error: '.$e->getMessage());
  ApiResponse::error('Server error', 500, ['exception' => $e->getMessage()]);
}

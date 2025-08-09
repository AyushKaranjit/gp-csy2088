<?php
/**
 * Admin Orders (Legacy Compatibility Endpoint)
 * Prefer granular orders endpoints under /api/orders/* for detail & actions.
 */
require_once __DIR__ . '/../_bootstrap.php';

use Doko\Http\ApiResponse;

require_method('GET');
ensure_session();
$auth = auth_controller();
if (!$auth->isLoggedIn()) { ApiResponse::error('Authentication required', 401); return; }
if (!$auth->isAdmin()) { ApiResponse::error('Access denied', 403); return; }

$page = int_param('page', 1, 1);
$perPage = int_param('per_page', 10, 1, 100);
$offset = ($page - 1) * $perPage;

try {
  $database = db();
  $pdo = $database->getConnection();
  $total = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
  $stmt = $pdo->prepare("SELECT order_id, order_number, user_id, status, total_amount, ordered_at FROM orders ORDER BY order_id DESC LIMIT :limit OFFSET :offset");
  $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

  ApiResponse::success([
    'orders' => $orders,
    'pagination' => [
      'current_page' => $page,
      'per_page' => $perPage,
      'total' => $total,
      'total_pages' => (int)ceil($total / $perPage)
    ],
    'meta' => [
      'deprecated' => true,
      'replacement' => '/api/orders/orders-list.php'
    ]
  ]);
} catch (Throwable $e) {
  ApiResponse::error('Failed to load orders', 500, ['error' => $e->getMessage()]);
}

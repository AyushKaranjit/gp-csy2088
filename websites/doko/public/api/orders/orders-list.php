<?php
/** Orders List API (clean refactor) */
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method('GET');

try {
    $auth = auth_controller();
    if (!$auth->isLoggedIn()) { ApiResponse::error('Authentication required', 401); return; }
    $db = db();
    $user = $auth->getCurrentUser();
    $status = $_GET['status'] ?? null;
    $page = int_param('page', 1, 1);
    $limit = int_param('limit', 20, 1, 100);
    $offset = ($page - 1) * $limit;
    $params = [];
    $where = [];
    if (!$auth->isAdmin()) { $where[] = 'o.user_id = ?'; $params[] = $user['user_id']; }
    if ($status) { $where[] = 'o.status = ?'; $params[] = $status; }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    $count = $db->execute("SELECT COUNT(*) AS c FROM orders o $whereSql", $params)->fetch()['c'] ?? 0;
    $sql = "SELECT o.order_id, o.order_number, o.user_id, o.status, o.total_amount, o.ordered_at FROM orders o $whereSql ORDER BY o.ordered_at DESC LIMIT $limit OFFSET $offset";
    $orders = $db->execute($sql, $params)->fetchAll();
    ApiResponse::success([
        'orders' => $orders,
        'pagination' => [
            'current_page' => $page,
            'total_items' => (int)$count,
            'total_pages' => (int)ceil($count / $limit),
            'items_per_page' => $limit,
            'has_next' => ($offset + $limit) < $count,
            'has_previous' => $page > 1
        ]
    ]);
} catch (Exception $e) {
    error_log('Orders list error: ' . $e->getMessage());
    ApiResponse::error('Failed to fetch orders', 500);
}
?>

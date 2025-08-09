<?php
// Refactored customer orders endpoint (history + placement) using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { ApiResponse::error('Method not allowed', 405, ['allowed'=>'GET']); }

try {
    $auth = auth_controller();
    if (!$auth->isLoggedIn()) { ApiResponse::error('Please login to access orders', 401); }
    $userId = $auth->getCurrentUser()['user_id'];
    $db = db();
    $pdo = $db->getConnection();
    // Correct schema columns (orders + order_items)
    $sql = "SELECT o.order_id, o.order_number, o.total_amount, o.subtotal, o.status, o.ordered_at, o.created_at,
                   COUNT(oi.order_item_id) AS item_count, COALESCE(SUM(oi.quantity),0) AS total_quantity
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.order_id
            ORDER BY o.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($orders as &$o){
        $o['display_date'] = $o['ordered_at'] ?: $o['created_at'];
    }
    ApiResponse::success(['orders'=>$orders]);
} catch (Throwable $e) {
    error_log('customer-orders error: '.$e->getMessage());
    ApiResponse::error('Server error: '.$e->getMessage(), 500);
}
        

<?php
/**
 * Admin Metrics Summary API
 * Returns high-level KPIs & recent orders for dashboard consumption.
 */
require_once __DIR__ . '/../../_bootstrap.php';

use Doko\Http\ApiResponse;

require_method('GET');
ensure_session();
$auth = auth_controller();
if (!$auth->isLoggedIn()) { ApiResponse::error('Authentication required', 401); return; }
if (!$auth->isAdmin()) { ApiResponse::error('Access denied', 403); return; }

// Query params
$recentLimit = int_param('recent_limit', 5, 1, 50);

try {
    $database = db();
    $pdo = $database->getConnection();

    // Aggregate metrics
    $metrics = [
        'total_users' => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'active_products' => (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn(),
        'total_orders' => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
        'pending_orders' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn(),
        'delivered_orders' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='delivered'")->fetchColumn(),
        'total_revenue' => (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status='paid'")->fetchColumn(),
        'low_stock_products' => (int)$pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= min_stock_level")->fetchColumn(),
    ];

    // Recent orders
    $recentStmt = $pdo->prepare(
        "SELECT order_id, order_number, user_id, status, total_amount, ordered_at 
         FROM orders 
         ORDER BY ordered_at DESC 
         LIMIT :limit"
    );
    $recentStmt->bindValue(':limit', $recentLimit, PDO::PARAM_INT);
    $recentStmt->execute();
    $recent = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

    ApiResponse::success([
        'metrics' => $metrics,
        'recent_orders' => $recent,
        'generated_at' => date('c'),
        'params' => [ 'recent_limit' => $recentLimit ]
    ]);
} catch (Throwable $e) {
    ApiResponse::error('Failed to load metrics', 500, [ 'error' => $e->getMessage() ]);
}

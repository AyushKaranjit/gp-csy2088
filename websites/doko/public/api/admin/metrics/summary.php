<?php
/**
 * Admin Metrics Summary API
 * Provides high-level KPIs for dashboard.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

session_start();
require_once __DIR__ . '/../../../../config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Basic aggregates (guard with IFNULL)
    $totals = [];
    $totals['total_users'] = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totals['active_products'] = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn();
    $totals['total_orders'] = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totals['pending_orders'] = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
    $totals['delivered_orders'] = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='delivered'")->fetchColumn();
    $totals['total_revenue'] = (float)$pdo->query("SELECT IFNULL(SUM(total_amount),0) FROM orders WHERE payment_status='paid'")->fetchColumn();
    $totals['low_stock_products'] = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= min_stock_level")->fetchColumn();

    // Recent orders (last 5)
    $recentStmt = $pdo->query("SELECT order_id, order_number, user_id, status, total_amount, ordered_at FROM orders ORDER BY ordered_at DESC LIMIT 5");
    $recent = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'metrics' => $totals,
        'recent_orders' => $recent,
        'generated_at' => date('c')
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Failed to load metrics','error'=>$e->getMessage()]);
}

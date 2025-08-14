<?php
/**
 * Order Statistics API Endpoint
 * Provides order statistics for manager dashboard badges
 */

require_once '../_bootstrap.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

try {
    require_once '../../config/database.php';
    require_once '../../src/Controllers/AuthController.php';
    
    // Check if user has manager access
    $auth = new AuthController();
    if (!$auth->hasManagerAccess()) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized access']);
        exit;
    }
    
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    // Get order statistics
    $stats = [];
    
    // Pending orders count
    $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    $stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Processing orders count
    $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'processing'");
    $stats['processing_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total orders count
    $stmt = $db->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Today's orders
    $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
    $stats['today_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo json_encode($stats);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>

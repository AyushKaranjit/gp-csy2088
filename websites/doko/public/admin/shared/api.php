<?php
/**
 * Admin Dashboard API
 * Handles all admin dashboard data and operations
 */

require_once '../../config/database.php';
require_once '../../src/Controllers/AuthController.php';

header('Content-Type: application/json');

// Check authentication
$auth = new AuthController();
if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    $database = Database::getInstance();
    $db = $database->getConnection();

    switch ($action) {
        case 'stats':
            handleGetStats($db);
            break;
            
        case 'reports':
            handleGetReports($db);
            break;
            
        case 'activity':
            handleGetActivity($db);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

function handleGetStats($db) {
    try {
        // Get comprehensive dashboard statistics
        $stats = [];
        
        // Users statistics
        $userStatsQuery = "
            SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_30d
            FROM users 
            WHERE role = 'customer'
        ";
        $stmt = $db->query($userStatsQuery);
        $stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Products statistics
        $productStatsQuery = "
            SELECT 
                COUNT(*) as total_products,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_products,
                COUNT(CASE WHEN stock_quantity <= 10 THEN 1 END) as low_stock_products,
                COUNT(CASE WHEN stock_quantity = 0 THEN 1 END) as out_of_stock_products
            FROM products
        ";
        $stmt = $db->query($productStatsQuery);
        $stats['products'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Orders statistics
        $orderStatsQuery = "
            SELECT 
                COUNT(*) as total_orders,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing_orders,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
                COUNT(CASE WHEN created_at >= CURDATE() THEN 1 END) as orders_today,
                COALESCE(SUM(CASE WHEN status IN ('completed', 'delivered') THEN total_amount ELSE 0 END), 0) as total_revenue,
                COALESCE(AVG(CASE WHEN status IN ('completed', 'delivered') THEN total_amount ELSE NULL END), 0) as avg_order_value
            FROM orders
        ";
        $stmt = $db->query($orderStatsQuery);
        $stats['orders'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Recent activity
        $recentActivityQuery = "
            SELECT 'order' as type, order_id as id, total_amount as amount, created_at 
            FROM orders 
            ORDER BY created_at DESC 
            LIMIT 5
        ";
        $stmt = $db->query($recentActivityQuery);
        $stats['recent_activity'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $stats]);
        
    } catch (Exception $e) {
        throw $e;
    }
}

function handleGetReports($db) {
    try {
        $reportType = $_GET['type'] ?? 'sales';
        $period = $_GET['period'] ?? '30d';
        
        $reports = [];
        
        switch ($reportType) {
            case 'sales':
                $reports = getSalesReport($db, $period);
                break;
                
            case 'products':
                $reports = getProductsReport($db, $period);
                break;
                
            case 'customers':
                $reports = getCustomersReport($db, $period);
                break;
                
            default:
                $reports = getSalesReport($db, $period);
                break;
        }
        
        echo json_encode(['success' => true, 'data' => $reports]);
        
    } catch (Exception $e) {
        throw $e;
    }
}

function getSalesReport($db, $period) {
    $dateFilter = getPeriodFilter($period);
    
    $query = "
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as order_count,
            SUM(total_amount) as total_sales,
            AVG(total_amount) as avg_order_value
        FROM orders 
        WHERE $dateFilter
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ";
    
    $stmt = $db->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductsReport($db, $period) {
    $dateFilter = getPeriodFilter($period);
    
    $query = "
        SELECT 
            p.product_id,
            p.name,
            p.stock_quantity,
            COALESCE(SUM(oi.quantity), 0) as units_sold,
            COALESCE(SUM(oi.quantity * oi.price), 0) as revenue
        FROM products p
        LEFT JOIN order_items oi ON p.product_id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.order_id
        WHERE p.status = 'active' AND (o.created_at IS NULL OR $dateFilter)
        GROUP BY p.product_id
        ORDER BY units_sold DESC
        LIMIT 20
    ";
    
    $stmt = $db->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCustomersReport($db, $period) {
    $dateFilter = getPeriodFilter($period);
    
    $query = "
        SELECT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.email,
            COUNT(o.order_id) as order_count,
            COALESCE(SUM(o.total_amount), 0) as total_spent,
            MAX(o.created_at) as last_order_date
        FROM users u
        LEFT JOIN orders o ON u.user_id = o.user_id
        WHERE u.role = 'customer' AND (o.created_at IS NULL OR $dateFilter)
        GROUP BY u.user_id
        ORDER BY total_spent DESC
        LIMIT 20
    ";
    
    $stmt = $db->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPeriodFilter($period) {
    switch ($period) {
        case '7d':
            return "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        case '30d':
            return "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        case '90d':
            return "created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
        case '1y':
            return "created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        default:
            return "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }
}

function handleGetActivity($db) {
    try {
        $limit = $_GET['limit'] ?? 20;
        
        $query = "
            SELECT 
                'order' as activity_type,
                o.order_id as item_id,
                CONCAT('New order #', o.order_id, ' by ', u.first_name, ' ', u.last_name) as description,
                o.total_amount as amount,
                o.created_at as timestamp
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            
            UNION ALL
            
            SELECT 
                'user' as activity_type,
                u.user_id as item_id,
                CONCAT('New user registration: ', u.first_name, ' ', u.last_name) as description,
                0 as amount,
                u.created_at as timestamp
            FROM users u
            WHERE u.role = 'customer'
            
            ORDER BY timestamp DESC
            LIMIT $limit
        ";
        
        $stmt = $db->query($query);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $activities]);
        
    } catch (Exception $e) {
        throw $e;
    }
}
?>

<?php
/**
 * Admin Dashboard API - Enhanced Version
 * DOKO Grocery E-commerce - Admin Only
 */

require_once '../ApiConfig.php';

try {
    // Require admin access
    ApiAuth::requireAdmin();
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetDashboardData($conn);
            break;
        default:
            ApiResponse::error('Method not allowed', 405);
            break;
    }
    
} catch (Exception $e) {
    ApiLogger::error("Dashboard API error: " . $e->getMessage());
    ApiResponse::error('An error occurred: ' . $e->getMessage());
}

function handleGetDashboardData($conn) {
    $timeframe = isset($_GET['timeframe']) ? $_GET['timeframe'] : '30days';
    
    // Get date range based on timeframe
    switch ($timeframe) {
        case '24hours':
            $dateCondition = "DATE(created_at) = CURDATE()";
            $orderDateCondition = "DATE(order_date) = CURDATE()";
            break;
        case '7days':
            $dateCondition = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $orderDateCondition = "order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case '30days':
        default:
            $dateCondition = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $orderDateCondition = "order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
        case '1year':
            $dateCondition = "created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            $orderDateCondition = "order_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            break;
    }
    
    // Get overview statistics
    $overviewStats = getOverviewStats($conn, $dateCondition, $orderDateCondition);
    
    // Get sales chart data
    $salesChart = getSalesChartData($conn, $timeframe);
    
    // Get top products
    $topProducts = getTopProducts($conn, $orderDateCondition);
    
    // Get recent orders
    $recentOrders = getRecentOrders($conn);
    
    // Get low stock products
    $lowStockProducts = getLowStockProducts($conn);
    
    // Get recent activities
    $recentActivities = getRecentActivities($conn);
    
    $dashboardData = [
        'overview' => $overviewStats,
        'sales_chart' => $salesChart,
        'top_products' => $topProducts,
        'recent_orders' => $recentOrders,
        'low_stock_products' => $lowStockProducts,
        'recent_activities' => $recentActivities,
        'timeframe' => $timeframe,
        'last_updated' => date('c')
    ];
    
    ApiResponse::success($dashboardData, 'Dashboard data retrieved successfully');
}

function getOverviewStats($conn, $dateCondition, $orderDateCondition) {
    // Total sales
    $salesQuery = "SELECT 
                      COUNT(*) as total_orders,
                      COALESCE(SUM(total_amount), 0) as total_revenue,
                      COALESCE(AVG(total_amount), 0) as avg_order_value
                   FROM orders 
                   WHERE status != 'cancelled' AND {$orderDateCondition}";
    $salesStmt = $conn->prepare($salesQuery);
    $salesStmt->execute();
    $salesData = $salesStmt->fetch();
    
    // New customers
    $customerQuery = "SELECT COUNT(*) as new_customers FROM users WHERE role = 'customer' AND {$dateCondition}";
    $customerStmt = $conn->prepare($customerQuery);
    $customerStmt->execute();
    $newCustomers = $customerStmt->fetchColumn();
    
    // Total products
    $productQuery = "SELECT COUNT(*) as total_products FROM products WHERE status = 'active'";
    $productStmt = $conn->prepare($productQuery);
    $productStmt->execute();
    $totalProducts = $productStmt->fetchColumn();
    
    // Pending orders
    $pendingQuery = "SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'";
    $pendingStmt = $conn->prepare($pendingQuery);
    $pendingStmt->execute();
    $pendingOrders = $pendingStmt->fetchColumn();
    
    // Low stock products
    $lowStockQuery = "SELECT COUNT(*) as low_stock_count 
                      FROM products 
                      WHERE stock_quantity <= (
                          SELECT COALESCE(setting_value, 10) 
                          FROM system_settings 
                          WHERE setting_key = 'low_stock_threshold'
                      ) AND status = 'active'";
    $lowStockStmt = $conn->prepare($lowStockQuery);
    $lowStockStmt->execute();
    $lowStockCount = $lowStockStmt->fetchColumn();
    
    return [
        'total_orders' => (int)$salesData['total_orders'],
        'total_revenue' => (float)$salesData['total_revenue'],
        'avg_order_value' => (float)$salesData['avg_order_value'],
        'new_customers' => (int)$newCustomers,
        'total_products' => (int)$totalProducts,
        'pending_orders' => (int)$pendingOrders,
        'low_stock_count' => (int)$lowStockCount
    ];
}

function getSalesChartData($conn, $timeframe) {
    switch ($timeframe) {
        case '24hours':
            $groupBy = "DATE_FORMAT(order_date, '%H:00')";
            $dateFormat = '%H:00';
            break;
        case '7days':
            $groupBy = "DATE(order_date)";
            $dateFormat = '%Y-%m-%d';
            break;
        case '30days':
            $groupBy = "DATE(order_date)";
            $dateFormat = '%Y-%m-%d';
            break;
        case '1year':
        default:
            $groupBy = "DATE_FORMAT(order_date, '%Y-%m')";
            $dateFormat = '%Y-%m';
            break;
    }
    
    $query = "SELECT 
                 {$groupBy} as period,
                 COUNT(*) as order_count,
                 COALESCE(SUM(total_amount), 0) as revenue
              FROM orders 
              WHERE status != 'cancelled' 
              AND order_date >= CASE 
                  WHEN :timeframe = '24hours' THEN DATE_SUB(NOW(), INTERVAL 24 HOUR)
                  WHEN :timeframe = '7days' THEN DATE_SUB(NOW(), INTERVAL 7 DAY)
                  WHEN :timeframe = '30days' THEN DATE_SUB(NOW(), INTERVAL 30 DAY)
                  ELSE DATE_SUB(NOW(), INTERVAL 1 YEAR)
              END
              GROUP BY {$groupBy}
              ORDER BY period";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':timeframe', $timeframe);
    $stmt->execute();
    
    $chartData = $stmt->fetchAll();
    
    return array_map(function($row) {
        return [
            'period' => $row['period'],
            'order_count' => (int)$row['order_count'],
            'revenue' => (float)$row['revenue']
        ];
    }, $chartData);
}

function getTopProducts($conn, $orderDateCondition) {
    $query = "SELECT 
                 p.product_id,
                 p.name,
                 p.price,
                 p.stock_quantity,
                 SUM(oi.quantity) as total_sold,
                 SUM(oi.quantity * oi.price) as total_revenue,
                 COUNT(DISTINCT oi.order_id) as order_count
              FROM products p
              JOIN order_items oi ON p.product_id = oi.product_id
              JOIN orders o ON oi.order_id = o.order_id
              WHERE o.status != 'cancelled' AND {$orderDateCondition}
              GROUP BY p.product_id
              ORDER BY total_sold DESC
              LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $products = $stmt->fetchAll();
    
    return array_map(function($product) {
        return [
            'product_id' => (int)$product['product_id'],
            'name' => $product['name'],
            'price' => (float)$product['price'],
            'stock_quantity' => (int)$product['stock_quantity'],
            'total_sold' => (int)$product['total_sold'],
            'total_revenue' => (float)$product['total_revenue'],
            'order_count' => (int)$product['order_count']
        ];
    }, $products);
}

function getRecentOrders($conn) {
    $query = "SELECT 
                 o.order_id,
                 o.order_number,
                 o.total_amount,
                 o.status,
                 o.order_date,
                 u.first_name,
                 u.last_name,
                 u.email
              FROM orders o
              JOIN users u ON o.user_id = u.user_id
              ORDER BY o.order_date DESC
              LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $orders = $stmt->fetchAll();
    
    return array_map(function($order) {
        return [
            'order_id' => (int)$order['order_id'],
            'order_number' => $order['order_number'],
            'total_amount' => (float)$order['total_amount'],
            'status' => $order['status'],
            'order_date' => $order['order_date'],
            'customer' => [
                'name' => trim($order['first_name'] . ' ' . $order['last_name']),
                'email' => $order['email']
            ]
        ];
    }, $orders);
}

function getLowStockProducts($conn) {
    $query = "SELECT 
                 p.product_id,
                 p.name,
                 p.sku,
                 p.stock_quantity,
                 p.price,
                 c.name as category_name
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id
              WHERE p.stock_quantity <= (
                  SELECT COALESCE(setting_value, 10) 
                  FROM system_settings 
                  WHERE setting_key = 'low_stock_threshold'
              ) 
              AND p.status = 'active'
              ORDER BY p.stock_quantity ASC
              LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $products = $stmt->fetchAll();
    
    return array_map(function($product) {
        return [
            'product_id' => (int)$product['product_id'],
            'name' => $product['name'],
            'sku' => $product['sku'],
            'stock_quantity' => (int)$product['stock_quantity'],
            'price' => (float)$product['price'],
            'category_name' => $product['category_name']
        ];
    }, $products);
}

function getRecentActivities($conn) {
    $query = "SELECT 
                 al.action,
                 al.entity_type,
                 al.entity_id,
                 al.created_at,
                 u.username,
                 u.first_name,
                 u.last_name
              FROM activity_logs al
              JOIN users u ON al.user_id = u.user_id
              ORDER BY al.created_at DESC
              LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $activities = $stmt->fetchAll();
    
    return array_map(function($activity) {
        return [
            'action' => $activity['action'],
            'entity_type' => $activity['entity_type'],
            'entity_id' => $activity['entity_id'] ? (int)$activity['entity_id'] : null,
            'created_at' => $activity['created_at'],
            'user' => [
                'username' => $activity['username'],
                'full_name' => trim($activity['first_name'] . ' ' . $activity['last_name'])
            ]
        ];
    }, $activities);
}
?>

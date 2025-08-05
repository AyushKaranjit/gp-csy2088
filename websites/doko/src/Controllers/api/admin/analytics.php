<?php
/**
 * Advanced Analytics API - Admin Only
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    // Check if user is admin
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Admin access required'
        ]);
        exit;
    }
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    $endpoint = $_GET['endpoint'] ?? 'overview';
    
    switch ($endpoint) {
        case 'overview':
            getAnalyticsOverview($conn);
            break;
        case 'sales':
            getSalesAnalytics($conn);
            break;
        case 'products':
            getProductAnalytics($conn);
            break;
        case 'customers':
            getCustomerAnalytics($conn);
            break;
        case 'inventory':
            getInventoryAnalytics($conn);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid endpoint']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

function getAnalyticsOverview($conn) {
    try {
        $timeframe = $_GET['timeframe'] ?? '30days';
        $dateCondition = getDateCondition($timeframe);
        
        // Get key metrics
        $metrics = [];
        
        // Total sales
        $salesQuery = "SELECT 
                          COUNT(*) as total_orders,
                          SUM(total_amount) as total_revenue,
                          AVG(total_amount) as avg_order_value
                       FROM orders 
                       WHERE status != 'cancelled' AND {$dateCondition}";
        $salesStmt = $conn->prepare($salesQuery);
        $salesStmt->execute();
        $salesData = $salesStmt->fetch(PDO::FETCH_ASSOC);
        
        // Customer metrics
        $customerQuery = "SELECT 
                             COUNT(DISTINCT user_id) as total_customers,
                             COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_customers
                          FROM users 
                          WHERE role = 'customer'";
        $customerStmt = $conn->prepare($customerQuery);
        $customerStmt->execute();
        $customerData = $customerStmt->fetch(PDO::FETCH_ASSOC);
        
        // Product metrics
        $productQuery = "SELECT 
                            COUNT(*) as total_products,
                            COUNT(CASE WHEN stock_quantity <= min_stock_level THEN 1 END) as low_stock_products,
                            COUNT(CASE WHEN stock_quantity = 0 THEN 1 END) as out_of_stock_products
                         FROM products 
                         WHERE status = 'active'";
        $productStmt = $conn->prepare($productQuery);
        $productStmt->execute();
        $productData = $productStmt->fetch(PDO::FETCH_ASSOC);
        
        // Top selling products
        $topProductsQuery = "SELECT 
                                p.name,
                                p.price,
                                SUM(oi.quantity) as total_sold,
                                SUM(oi.total_price) as total_revenue
                             FROM products p
                             JOIN order_items oi ON p.product_id = oi.product_id
                             JOIN orders o ON oi.order_id = o.order_id
                             WHERE o.status != 'cancelled' AND {$dateCondition}
                             GROUP BY p.product_id
                             ORDER BY total_sold DESC
                             LIMIT 10";
        $topProductsStmt = $conn->prepare($topProductsQuery);
        $topProductsStmt->execute();
        $topProducts = $topProductsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Recent activity
        $recentActivityQuery = "SELECT 
                                   al.action,
                                   al.entity_type,
                                   al.entity_id,
                                   al.created_at,
                                   u.username
                                FROM activity_logs al
                                JOIN users u ON al.user_id = u.user_id
                                ORDER BY al.created_at DESC
                                LIMIT 20";
        $recentActivityStmt = $conn->prepare($recentActivityQuery);
        $recentActivityStmt->execute();
        $recentActivity = $recentActivityStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'sales' => $salesData,
                'customers' => $customerData,
                'products' => $productData,
                'top_products' => $topProducts,
                'recent_activity' => $recentActivity,
                'timeframe' => $timeframe
            ]
        ]);
        
    } catch (Exception $e) {
        throw $e;
    }
}

function getSalesAnalytics($conn) {
    try {
        $timeframe = $_GET['timeframe'] ?? '30days';
        $dateCondition = getDateCondition($timeframe);
        
        // Daily sales data
        $dailySalesQuery = "SELECT 
                               DATE(ordered_at) as date,
                               COUNT(*) as orders,
                               SUM(total_amount) as revenue,
                               AVG(total_amount) as avg_order_value
                            FROM orders 
                            WHERE status != 'cancelled' AND {$dateCondition}
                            GROUP BY DATE(ordered_at)
                            ORDER BY date DESC";
        $dailySalesStmt = $conn->prepare($dailySalesQuery);
        $dailySalesStmt->execute();
        $dailySales = $dailySalesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Sales by category
        $categorySalesQuery = "SELECT 
                                  c.name as category_name,
                                  COUNT(oi.order_item_id) as items_sold,
                                  SUM(oi.total_price) as revenue
                               FROM categories c
                               JOIN products p ON c.category_id = p.category_id
                               JOIN order_items oi ON p.product_id = oi.product_id
                               JOIN orders o ON oi.order_id = o.order_id
                               WHERE o.status != 'cancelled' AND {$dateCondition}
                               GROUP BY c.category_id
                               ORDER BY revenue DESC";
        $categorySalesStmt = $conn->prepare($categorySalesQuery);
        $categorySalesStmt->execute();
        $categorySales = $categorySalesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Payment method breakdown
        $paymentMethodQuery = "SELECT 
                                  payment_method,
                                  COUNT(*) as orders,
                                  SUM(total_amount) as revenue
                               FROM orders 
                               WHERE status != 'cancelled' AND {$dateCondition}
                               GROUP BY payment_method";
        $paymentMethodStmt = $conn->prepare($paymentMethodQuery);
        $paymentMethodStmt->execute();
        $paymentMethods = $paymentMethodStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'daily_sales' => $dailySales,
                'category_sales' => $categorySales,
                'payment_methods' => $paymentMethods,
                'timeframe' => $timeframe
            ]
        ]);
        
    } catch (Exception $e) {
        throw $e;
    }
}

function getProductAnalytics($conn) {
    try {
        // Most viewed products
        $mostViewedQuery = "SELECT 
                               p.name,
                               p.price,
                               p.stock_quantity,
                               p.view_count
                            FROM products p
                            WHERE p.status = 'active'
                            ORDER BY p.view_count DESC
                            LIMIT 20";
        $mostViewedStmt = $conn->prepare($mostViewedQuery);
        $mostViewedStmt->execute();
        $mostViewed = $mostViewedStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Low stock alerts
        $lowStockQuery = "SELECT 
                             p.name,
                             p.stock_quantity,
                             p.min_stock_level,
                             c.name as category_name
                          FROM products p
                          JOIN categories c ON p.category_id = c.category_id
                          WHERE p.status = 'active' 
                          AND p.stock_quantity <= p.min_stock_level
                          ORDER BY p.stock_quantity ASC";
        $lowStockStmt = $conn->prepare($lowStockQuery);
        $lowStockStmt->execute();
        $lowStock = $lowStockStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Product performance
        $performanceQuery = "SELECT 
                                p.name,
                                p.price,
                                p.stock_quantity,
                                COALESCE(SUM(oi.quantity), 0) as total_sold,
                                COALESCE(SUM(oi.total_price), 0) as total_revenue,
                                p.view_count,
                                CASE 
                                    WHEN p.view_count > 0 THEN (COALESCE(SUM(oi.quantity), 0) / p.view_count * 100)
                                    ELSE 0 
                                END as conversion_rate
                             FROM products p
                             LEFT JOIN order_items oi ON p.product_id = oi.product_id
                             LEFT JOIN orders o ON oi.order_id = o.order_id AND o.status != 'cancelled'
                             WHERE p.status = 'active'
                             GROUP BY p.product_id
                             ORDER BY total_revenue DESC
                             LIMIT 50";
        $performanceStmt = $conn->prepare($performanceQuery);
        $performanceStmt->execute();
        $performance = $performanceStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'most_viewed' => $mostViewed,
                'low_stock' => $lowStock,
                'performance' => $performance
            ]
        ]);
        
    } catch (Exception $e) {
        throw $e;
    }
}

function getCustomerAnalytics($conn) {
    try {
        $timeframe = $_GET['timeframe'] ?? '30days';
        $dateCondition = getDateCondition($timeframe);
        
        // Customer acquisition
        $acquisitionQuery = "SELECT 
                                DATE(created_at) as date,
                                COUNT(*) as new_customers
                             FROM users
                             WHERE role = 'customer' AND {$dateCondition}
                             GROUP BY DATE(created_at)
                             ORDER BY date DESC";
        $acquisitionStmt = $conn->prepare($acquisitionQuery);
        $acquisitionStmt->execute();
        $acquisition = $acquisitionStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top customers
        $topCustomersQuery = "SELECT 
                                 u.username,
                                 u.email,
                                 COUNT(o.order_id) as total_orders,
                                 SUM(o.total_amount) as total_spent,
                                 AVG(o.total_amount) as avg_order_value,
                                 MAX(o.ordered_at) as last_order_date
                              FROM users u
                              JOIN orders o ON u.user_id = o.user_id
                              WHERE u.role = 'customer' AND o.status != 'cancelled'
                              GROUP BY u.user_id
                              ORDER BY total_spent DESC
                              LIMIT 20";
        $topCustomersStmt = $conn->prepare($topCustomersQuery);
        $topCustomersStmt->execute();
        $topCustomers = $topCustomersStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Customer segments
        $segmentsQuery = "SELECT 
                             CASE 
                                 WHEN total_spent >= 10000 THEN 'VIP'
                                 WHEN total_spent >= 5000 THEN 'Premium'
                                 WHEN total_spent >= 1000 THEN 'Regular'
                                 ELSE 'New'
                             END as segment,
                             COUNT(*) as customer_count,
                             AVG(total_spent) as avg_spent,
                             AVG(total_orders) as avg_orders
                          FROM (
                              SELECT 
                                  u.user_id,
                                  COUNT(o.order_id) as total_orders,
                                  COALESCE(SUM(o.total_amount), 0) as total_spent
                              FROM users u
                              LEFT JOIN orders o ON u.user_id = o.user_id AND o.status != 'cancelled'
                              WHERE u.role = 'customer'
                              GROUP BY u.user_id
                          ) customer_stats
                          GROUP BY segment
                          ORDER BY avg_spent DESC";
        $segmentsStmt = $conn->prepare($segmentsQuery);
        $segmentsStmt->execute();
        $segments = $segmentsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'acquisition' => $acquisition,
                'top_customers' => $topCustomers,
                'segments' => $segments,
                'timeframe' => $timeframe
            ]
        ]);
        
    } catch (Exception $e) {
        throw $e;
    }
}

function getInventoryAnalytics($conn) {
    try {
        // Stock overview
        $stockOverviewQuery = "SELECT 
                                  c.name as category_name,
                                  COUNT(p.product_id) as total_products,
                                  SUM(p.stock_quantity * p.cost_price) as inventory_value,
                                  COUNT(CASE WHEN p.stock_quantity <= p.min_stock_level THEN 1 END) as low_stock_count,
                                  COUNT(CASE WHEN p.stock_quantity = 0 THEN 1 END) as out_of_stock_count
                               FROM categories c
                               LEFT JOIN products p ON c.category_id = p.category_id AND p.status = 'active'
                               GROUP BY c.category_id
                               ORDER BY inventory_value DESC";
        $stockOverviewStmt = $conn->prepare($stockOverviewQuery);
        $stockOverviewStmt->execute();
        $stockOverview = $stockOverviewStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Recent stock movements
        $stockMovementsQuery = "SELECT 
                                   sm.movement_type,
                                   sm.quantity_change,
                                   sm.notes,
                                   sm.created_at,
                                   p.name as product_name,
                                   u.username as created_by
                                FROM stock_movements sm
                                JOIN products p ON sm.product_id = p.product_id
                                LEFT JOIN users u ON sm.created_by = u.user_id
                                ORDER BY sm.created_at DESC
                                LIMIT 50";
        $stockMovementsStmt = $conn->prepare($stockMovementsQuery);
        $stockMovementsStmt->execute();
        $stockMovements = $stockMovementsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fast moving items
        $fastMovingQuery = "SELECT 
                               p.name,
                               p.stock_quantity,
                               COALESCE(SUM(oi.quantity), 0) as total_sold,
                               CASE 
                                   WHEN p.stock_quantity > 0 THEN (COALESCE(SUM(oi.quantity), 0) / p.stock_quantity)
                                   ELSE 0 
                               END as turnover_ratio
                            FROM products p
                            LEFT JOIN order_items oi ON p.product_id = oi.product_id
                            LEFT JOIN orders o ON oi.order_id = o.order_id 
                                AND o.status != 'cancelled' 
                                AND o.ordered_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                            WHERE p.status = 'active'
                            GROUP BY p.product_id
                            HAVING total_sold > 0
                            ORDER BY turnover_ratio DESC
                            LIMIT 20";
        $fastMovingStmt = $conn->prepare($fastMovingQuery);
        $fastMovingStmt->execute();
        $fastMoving = $fastMovingStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'stock_overview' => $stockOverview,
                'stock_movements' => $stockMovements,
                'fast_moving' => $fastMoving
            ]
        ]);
        
    } catch (Exception $e) {
        throw $e;
    }
}

function getDateCondition($timeframe) {
    switch ($timeframe) {
        case '7days':
            return "ordered_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        case '30days':
            return "ordered_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        case '90days':
            return "ordered_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
        case '1year':
            return "ordered_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        default:
            return "ordered_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }
}
?>

<?php
/**
 * Admin Dashboard API
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../Controllers/AuthController.php';
require_once '../../../config/database.php';

try {
    // Check authentication
    $auth = new AuthController();
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required'
        ]);
        exit;
    }
    
    // Check if user is admin
    if (!$auth->isAdmin()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Admin access required'
        ]);
        exit;
    }
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    // Get dashboard statistics
    $dashboard = getDashboardStats($conn);
    
    echo json_encode([
        'success' => true,
        'data' => $dashboard
    ]);
    
} catch (Exception $e) {
    error_log("Dashboard API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching dashboard data'
    ]);
}

function getDashboardStats($conn) {
    $stats = [];
    
    // Sales Overview
    $salesQuery = "SELECT 
                       COUNT(*) as total_orders,
                       COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                       COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_orders,
                       SUM(CASE WHEN status = 'delivered' THEN total_amount ELSE 0 END) as total_revenue,
                       AVG(CASE WHEN status = 'delivered' THEN total_amount ELSE NULL END) as avg_order_value,
                       COUNT(CASE WHEN DATE(ordered_at) = CURDATE() THEN 1 END) as today_orders,
                       SUM(CASE WHEN DATE(ordered_at) = CURDATE() AND status = 'delivered' THEN total_amount ELSE 0 END) as today_revenue
                   FROM orders";
    $salesStmt = $conn->prepare($salesQuery);
    $salesStmt->execute();
    $stats['sales'] = $salesStmt->fetch();
    
    // User Statistics
    $userQuery = "SELECT 
                      COUNT(*) as total_users,
                      COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
                      COUNT(CASE WHEN role = 'customer' THEN 1 END) as customers,
                      COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as new_users_today,
                      COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as new_users_week
                  FROM users";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->execute();
    $stats['users'] = $userStmt->fetch();
    
    // Product Statistics
    $productQuery = "SELECT 
                         COUNT(*) as total_products,
                         COUNT(CASE WHEN status = 'active' THEN 1 END) as active_products,
                         COUNT(CASE WHEN stock_quantity <= min_stock_level THEN 1 END) as low_stock_products,
                         COUNT(CASE WHEN stock_quantity = 0 THEN 1 END) as out_of_stock_products,
                         COUNT(CASE WHEN featured = 1 THEN 1 END) as featured_products
                     FROM products";
    $productStmt = $conn->prepare($productQuery);
    $productStmt->execute();
    $stats['products'] = $productStmt->fetch();
    
    // Category Statistics
    $categoryQuery = "SELECT COUNT(*) as total_categories, COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_categories FROM categories";
    $categoryStmt = $conn->prepare($categoryQuery);
    $categoryStmt->execute();
    $stats['categories'] = $categoryStmt->fetch();
    
    // Recent Orders
    $recentOrdersQuery = "SELECT o.order_id, o.order_number, o.total_amount, o.status, o.ordered_at,
                                 u.first_name, u.last_name, u.email,
                                 COUNT(oi.order_item_id) as item_count
                          FROM orders o
                          JOIN users u ON o.user_id = u.user_id
                          LEFT JOIN order_items oi ON o.order_id = oi.order_id
                          GROUP BY o.order_id
                          ORDER BY o.ordered_at DESC
                          LIMIT 10";
    $recentOrdersStmt = $conn->prepare($recentOrdersQuery);
    $recentOrdersStmt->execute();
    $stats['recent_orders'] = $recentOrdersStmt->fetchAll();
    
    // Top Selling Products
    $topProductsQuery = "SELECT p.product_id, p.name, p.price, p.stock_quantity,
                                pi.image_url,
                                SUM(oi.quantity) as total_sold,
                                SUM(oi.total_price) as total_revenue
                         FROM products p
                         LEFT JOIN order_items oi ON p.product_id = oi.product_id
                         LEFT JOIN orders o ON oi.order_id = o.order_id AND o.status = 'delivered'
                         LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = TRUE
                         WHERE oi.product_id IS NOT NULL
                         GROUP BY p.product_id
                         ORDER BY total_sold DESC
                         LIMIT 10";
    $topProductsStmt = $conn->prepare($topProductsQuery);
    $topProductsStmt->execute();
    $stats['top_products'] = $topProductsStmt->fetchAll();
    
    // Low Stock Alert
    $lowStockQuery = "SELECT p.product_id, p.name, p.sku, p.stock_quantity, p.min_stock_level,
                             c.name as category_name,
                             pi.image_url
                      FROM products p
                      LEFT JOIN categories c ON p.category_id = c.category_id
                      LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = TRUE
                      WHERE p.stock_quantity <= p.min_stock_level 
                      AND p.status = 'active'
                      ORDER BY p.stock_quantity ASC
                      LIMIT 10";
    $lowStockStmt = $conn->prepare($lowStockQuery);
    $lowStockStmt->execute();
    $stats['low_stock'] = $lowStockStmt->fetchAll();
    
    // Monthly Sales Chart Data (Last 12 months)
    $monthlySalesQuery = "SELECT 
                              DATE_FORMAT(ordered_at, '%Y-%m') as month,
                              COUNT(*) as order_count,
                              SUM(CASE WHEN status = 'delivered' THEN total_amount ELSE 0 END) as revenue
                          FROM orders 
                          WHERE ordered_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                          GROUP BY month
                          ORDER BY month ASC";
    $monthlySalesStmt = $conn->prepare($monthlySalesQuery);
    $monthlySalesStmt->execute();
    $stats['monthly_sales'] = $monthlySalesStmt->fetchAll();
    
    // Daily Sales Chart Data (Last 30 days)
    $dailySalesQuery = "SELECT 
                            DATE(ordered_at) as date,
                            COUNT(*) as order_count,
                            SUM(CASE WHEN status = 'delivered' THEN total_amount ELSE 0 END) as revenue
                        FROM orders 
                        WHERE ordered_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        GROUP BY date
                        ORDER BY date ASC";
    $dailySalesStmt = $conn->prepare($dailySalesQuery);
    $dailySalesStmt->execute();
    $stats['daily_sales'] = $dailySalesStmt->fetchAll();
    
    // Order Status Distribution
    $statusQuery = "SELECT status, COUNT(*) as count 
                    FROM orders 
                    WHERE ordered_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY status";
    $statusStmt = $conn->prepare($statusQuery);
    $statusStmt->execute();
    $stats['order_status_distribution'] = $statusStmt->fetchAll();
    
    // Customer Growth (Last 12 months)
    $growthQuery = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        COUNT(*) as new_customers
                    FROM users 
                    WHERE role = 'customer' 
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                    GROUP BY month
                    ORDER BY month ASC";
    $growthStmt = $conn->prepare($growthQuery);
    $growthStmt->execute();
    $stats['customer_growth'] = $growthStmt->fetchAll();
    
    // Payment Method Distribution
    $paymentQuery = "SELECT payment_method, COUNT(*) as count
                     FROM orders 
                     WHERE ordered_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                     GROUP BY payment_method";
    $paymentStmt = $conn->prepare($paymentQuery);
    $paymentStmt->execute();
    $stats['payment_methods'] = $paymentStmt->fetchAll();
    
    return $stats;
}
?>

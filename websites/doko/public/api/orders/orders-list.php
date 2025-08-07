<?php
/**
 * Orders List API
 * Returns comprehensive order information with filtering and pagination
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';
require_once '../../src/Controllers/AuthController.php';

try {
    $db = Database::getInstance();
    $auth = new AuthController();
    
    // Start session to check authentication
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is authenticated
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required'
        ]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'] ?? 'customer';
    
    // Get query parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;
    
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
    $customer_id = isset($_GET['customer_id']) ? $_GET['customer_id'] : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
    $sort_order = isset($_GET['sort_order']) && strtoupper($_GET['sort_order']) === 'ASC' ? 'ASC' : 'DESC';
    
    // Valid sort columns
    $valid_sort_columns = ['order_id', 'created_at', 'total_amount', 'status', 'customer_name'];
    if (!in_array($sort_by, $valid_sort_columns)) {
        $sort_by = 'created_at';
    }
    
    // Build WHERE conditions
    $where_conditions = [];
    $params = [];
    
    // Role-based filtering
    if ($user_role === 'customer') {
        // Customers can only see their own orders
        $where_conditions[] = "o.user_id = ?";
        $params[] = $user_id;
    } elseif ($user_role === 'manager' || $user_role === 'admin') {
        // Admins and managers can see all orders, optionally filtered by customer
        if (!empty($customer_id)) {
            $where_conditions[] = "o.user_id = ?";
            $params[] = $customer_id;
        }
    }
    
    if (!empty($status_filter)) {
        $where_conditions[] = "o.status = ?";
        $params[] = $status_filter;
    }
    
    if (!empty($date_from)) {
        $where_conditions[] = "DATE(o.created_at) >= ?";
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = "DATE(o.created_at) <= ?";
        $params[] = $date_to;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(o.order_id LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get total count for pagination
    $count_query = "
        SELECT COUNT(DISTINCT o.order_id) as total
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        $where_clause
    ";
    
    $count_stmt = $db->execute($count_query, $params);
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $limit);
    
    // Main orders query
    $query = "
        SELECT 
            o.order_id,
            o.user_id,
            o.status,
            o.total_amount,
            o.shipping_cost,
            o.tax_amount,
            o.discount_amount,
            o.payment_method,
            o.payment_status,
            o.shipping_address,
            o.billing_address,
            o.tracking_number,
            o.notes,
            o.created_at,
            o.updated_at,
            u.first_name,
            u.last_name,
            u.email,
            u.phone,
            CONCAT(u.first_name, ' ', u.last_name) as customer_name,
            COUNT(oi.order_item_id) as item_count,
            CASE 
                WHEN o.status = 'pending' THEN 'warning'
                WHEN o.status = 'confirmed' THEN 'info'
                WHEN o.status = 'processing' THEN 'primary'
                WHEN o.status = 'shipped' THEN 'secondary'
                WHEN o.status = 'delivered' THEN 'success'
                WHEN o.status = 'cancelled' THEN 'danger'
                WHEN o.status = 'refunded' THEN 'dark'
                ELSE 'light'
            END as status_class
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        $where_clause
        GROUP BY o.order_id
        ORDER BY $sort_by $sort_order
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $db->execute($query, $params);
    $orders = $stmt->fetchAll();
    
    // Get order items for each order
    foreach ($orders as &$order) {
        $items_query = "
            SELECT 
                oi.order_item_id,
                oi.product_id,
                oi.quantity,
                oi.price,
                oi.total,
                p.name as product_name,
                p.sku,
                p.stock_quantity,
                c.name as category_name,
                (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.product_id AND pi.is_primary = 1 LIMIT 1) as product_image
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.product_id
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE oi.order_id = ?
            ORDER BY oi.order_item_id
        ";
        
        $items_stmt = $db->execute($items_query, [$order['order_id']]);
        $order['items'] = $items_stmt->fetchAll();
        
        // Set default image for products without images
        foreach ($order['items'] as &$item) {
            if (empty($item['product_image'])) {
                $item['product_image'] = 'uploads/default-product.jpg';
            }
            $item['price'] = number_format($item['price'], 2);
            $item['total'] = number_format($item['total'], 2);
        }
        
        // Format order data
        $order['total_amount'] = number_format($order['total_amount'], 2);
        $order['shipping_cost'] = number_format($order['shipping_cost'], 2);
        $order['tax_amount'] = number_format($order['tax_amount'], 2);
        $order['discount_amount'] = number_format($order['discount_amount'], 2);
        
        // Format dates
        $order['created_at'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
        $order['updated_at'] = date('Y-m-d H:i:s', strtotime($order['updated_at']));
        $order['created_at_formatted'] = date('M d, Y h:i A', strtotime($order['created_at']));
        
        // Parse addresses
        if (!empty($order['shipping_address'])) {
            $order['shipping_address'] = json_decode($order['shipping_address'], true);
        }
        if (!empty($order['billing_address'])) {
            $order['billing_address'] = json_decode($order['billing_address'], true);
        }
        
        // Add status history (if available)
        $order['can_cancel'] = in_array($order['status'], ['pending', 'confirmed']);
        $order['can_refund'] = in_array($order['status'], ['delivered']);
        $order['can_ship'] = in_array($order['status'], ['confirmed', 'processing']);
        $order['can_process'] = $order['status'] === 'confirmed';
        
        // Calculate order age
        $order['order_age_days'] = floor((time() - strtotime($order['created_at'])) / (24 * 60 * 60));
    }
    
    // Get order statistics (for admin/manager)
    $stats = [];
    if (in_array($user_role, ['admin', 'manager'])) {
        $stats_base_where = $user_role === 'customer' ? 'WHERE user_id = ' . $user_id : '';
        
        $stats_query = "
            SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as avg_order_value,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN status = 'refunded' THEN 1 ELSE 0 END) as refunded_orders,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_orders,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total_amount ELSE 0 END) as today_revenue,
                SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_orders,
                SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as month_orders
            FROM orders 
            $stats_base_where
        ";
        
        $stats_stmt = $db->execute($stats_query, $user_role === 'customer' ? [] : []);
        $stats = $stats_stmt->fetch();
        
        // Format stats
        $stats['total_revenue'] = number_format($stats['total_revenue'], 2);
        $stats['avg_order_value'] = number_format($stats['avg_order_value'], 2);
        $stats['today_revenue'] = number_format($stats['today_revenue'], 2);
    }
    
    // Get recent activity
    $recent_activity = [];
    if (in_array($user_role, ['admin', 'manager'])) {
        $activity_query = "
            SELECT 
                o.order_id,
                o.status,
                o.total_amount,
                o.updated_at,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                'order_status_change' as activity_type
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            ORDER BY o.updated_at DESC
            LIMIT 10
        ";
        
        $activity_stmt = $db->execute($activity_query);
        $recent_activity = $activity_stmt->fetchAll();
        
        foreach ($recent_activity as &$activity) {
            $activity['updated_at'] = date('M d, Y h:i A', strtotime($activity['updated_at']));
            $activity['total_amount'] = number_format($activity['total_amount'], 2);
        }
    }
    
    $response = [
        'success' => true,
        'message' => 'Orders retrieved successfully',
        'data' => [
            'orders' => $orders,
            'statistics' => $stats,
            'recent_activity' => $recent_activity,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_records' => $total_records,
                'per_page' => $limit,
                'has_next' => $page < $total_pages,
                'has_prev' => $page > 1
            ],
            'filters' => [
                'status' => $status_filter,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'customer_id' => $customer_id,
                'search' => $search,
                'sort_by' => $sort_by,
                'sort_order' => $sort_order
            ],
            'user_role' => $user_role
        ],
        'timestamp' => date('c')
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred while retrieving orders',
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
    
    // Log error for debugging
    error_log("Orders List API Error: " . $e->getMessage());
}
?>

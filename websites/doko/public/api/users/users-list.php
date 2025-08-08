<?php
// Refactored users list admin endpoint using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

try {
    $auth = auth_controller();
    if (!$auth->isLoggedIn() || !$auth->isAdmin()) { ApiResponse::error('Unauthorized access. Admin role required.', 401); }
    $db = db();
    
    // Get query parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;
    
    $role_filter = isset($_GET['role']) ? $_GET['role'] : '';
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
    $sort_order = isset($_GET['sort_order']) && strtoupper($_GET['sort_order']) === 'ASC' ? 'ASC' : 'DESC';
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
    
    // Valid sort columns
    $valid_sort_columns = ['user_id', 'username', 'email', 'first_name', 'last_name', 'role', 'is_active', 'created_at', 'last_login'];
    if (!in_array($sort_by, $valid_sort_columns)) {
        $sort_by = 'created_at';
    }
    
    // Build WHERE conditions
    $where_conditions = [];
    $params = [];
    
    if (!empty($role_filter)) {
        $where_conditions[] = "u.role = ?";
        $params[] = $role_filter;
    }
    
    if (!empty($status_filter)) {
        if ($status_filter === 'active') {
            $where_conditions[] = "u.is_active = 1";
        } elseif ($status_filter === 'inactive') {
            $where_conditions[] = "u.is_active = 0";
        }
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.phone LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if (!empty($date_from)) {
        $where_conditions[] = "DATE(u.created_at) >= ?";
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = "DATE(u.created_at) <= ?";
        $params[] = $date_to;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get total count for pagination
    $count_query = "
        SELECT COUNT(DISTINCT u.user_id) as total
        FROM users u
        $where_clause
    ";
    
    $count_stmt = $db->execute($count_query, $params);
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $limit);
    
    // Main users query
    $query = "
        SELECT 
            u.user_id,
            u.username,
            u.email,
            u.first_name,
            u.last_name,
            u.phone,
            u.role,
            u.is_active,
            u.email_verified,
            u.created_at,
            u.updated_at,
            u.last_login,
            u.profile_image,
            CONCAT(u.first_name, ' ', u.last_name) as full_name,
            CASE 
                WHEN u.is_active = 1 THEN 'Active'
                ELSE 'Inactive'
            END as status_text,
            CASE 
                WHEN u.is_active = 1 THEN 'success'
                ELSE 'danger'
            END as status_class,
            CASE 
                WHEN u.role = 'admin' THEN 'primary'
                WHEN u.role = 'manager' THEN 'info'
                WHEN u.role = 'customer' THEN 'secondary'
                ELSE 'light'
            END as role_class,
            -- Get order statistics
            (SELECT COUNT(*) FROM orders WHERE user_id = u.user_id) as total_orders,
            (SELECT SUM(total_amount) FROM orders WHERE user_id = u.user_id AND status = 'delivered') as total_spent,
            (SELECT MAX(created_at) FROM orders WHERE user_id = u.user_id) as last_order_date,
            -- Get address count
            (SELECT COUNT(*) FROM user_addresses WHERE user_id = u.user_id) as address_count
        FROM users u
        $where_clause
        ORDER BY $sort_by $sort_order
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $db->execute($query, $params);
    $users = $stmt->fetchAll();
    
    // Process the results
    foreach ($users as &$user) {
        // Format dates
        $user['created_at'] = date('Y-m-d H:i:s', strtotime($user['created_at']));
        $user['updated_at'] = date('Y-m-d H:i:s', strtotime($user['updated_at']));
        $user['created_at_formatted'] = date('M d, Y h:i A', strtotime($user['created_at']));
        
        // Format last login
        if ($user['last_login']) {
            $user['last_login'] = date('Y-m-d H:i:s', strtotime($user['last_login']));
            $user['last_login_formatted'] = date('M d, Y h:i A', strtotime($user['last_login']));
            
            // Calculate days since last login
            $days_since_login = floor((time() - strtotime($user['last_login'])) / (24 * 60 * 60));
            $user['days_since_last_login'] = $days_since_login;
            
            if ($days_since_login > 30) {
                $user['login_status'] = 'Inactive';
                $user['login_status_class'] = 'warning';
            } elseif ($days_since_login > 7) {
                $user['login_status'] = 'Rarely Active';
                $user['login_status_class'] = 'info';
            } else {
                $user['login_status'] = 'Active';
                $user['login_status_class'] = 'success';
            }
        } else {
            $user['last_login_formatted'] = 'Never';
            $user['login_status'] = 'Never Logged In';
            $user['login_status_class'] = 'danger';
            $user['days_since_last_login'] = -1;
        }
        
        // Format last order date
        if ($user['last_order_date']) {
            $user['last_order_date'] = date('Y-m-d H:i:s', strtotime($user['last_order_date']));
            $user['last_order_formatted'] = date('M d, Y', strtotime($user['last_order_date']));
        } else {
            $user['last_order_formatted'] = 'No orders';
        }
        
        // Format spending
        $user['total_spent'] = $user['total_spent'] ? number_format($user['total_spent'], 2) : '0.00';
        
        // Set default profile image
        if (empty($user['profile_image'])) {
            $user['profile_image'] = 'uploads/users/default-avatar.png';
        }
        
        // Calculate user age (account age)
        $user['account_age_days'] = floor((time() - strtotime($user['created_at'])) / (24 * 60 * 60));
        
        // Customer value segmentation
        $total_spent_num = floatval(str_replace(',', '', $user['total_spent']));
        if ($total_spent_num >= 10000) {
            $user['customer_segment'] = 'VIP';
            $user['segment_class'] = 'warning';
        } elseif ($total_spent_num >= 5000) {
            $user['customer_segment'] = 'Premium';
            $user['segment_class'] = 'info';
        } elseif ($total_spent_num > 0) {
            $user['customer_segment'] = 'Regular';
            $user['segment_class'] = 'success';
        } else {
            $user['customer_segment'] = 'New';
            $user['segment_class'] = 'secondary';
        }
        
        // Get recent orders for this user
        $recent_orders_query = "
            SELECT order_id, total_amount, status, created_at
            FROM orders 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 3
        ";
        $orders_stmt = $db->execute($recent_orders_query, [$user['user_id']]);
        $user['recent_orders'] = $orders_stmt->fetchAll();
        
        foreach ($user['recent_orders'] as &$order) {
            $order['total_amount'] = number_format($order['total_amount'], 2);
            $order['created_at'] = date('M d, Y', strtotime($order['created_at']));
        }
    }
    
    // Get user statistics
    $stats_query = "
        SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
            SUM(CASE WHEN role = 'manager' THEN 1 ELSE 0 END) as manager_count,
            SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as customer_count,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
            SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_users,
            SUM(CASE WHEN email_verified = 1 THEN 1 ELSE 0 END) as verified_emails,
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_registrations,
            SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_registrations,
            SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as month_registrations,
            SUM(CASE WHEN last_login IS NOT NULL AND DATE(last_login) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active_last_month
        FROM users
    ";
    
    $stats_stmt = $db->execute($stats_query);
    $stats = $stats_stmt->fetch();
    
    // Get registration trends (last 12 months)
    $trends_query = "
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as registrations
        FROM users
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ";
    
    $trends_stmt = $db->execute($trends_query);
    $registration_trends = $trends_stmt->fetchAll();
    
    // Get top customers by spending
    $top_customers_query = "
        SELECT 
            u.user_id,
            CONCAT(u.first_name, ' ', u.last_name) as full_name,
            u.email,
            COUNT(o.order_id) as total_orders,
            SUM(o.total_amount) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.user_id = o.user_id AND o.status = 'delivered'
        WHERE u.role = 'customer'
        GROUP BY u.user_id
        HAVING total_spent > 0
        ORDER BY total_spent DESC
        LIMIT 10
    ";
    
    $top_customers_stmt = $db->execute($top_customers_query);
    $top_customers = $top_customers_stmt->fetchAll();
    
    foreach ($top_customers as &$customer) {
        $customer['total_spent'] = number_format($customer['total_spent'], 2);
    }
    
    ApiResponse::success([
        'users' => $users,
        'statistics' => $stats,
        'registration_trends' => $registration_trends,
        'top_customers' => $top_customers,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total_records,
            'per_page' => $limit,
            'has_next' => $page < $total_pages,
            'has_prev' => $page > 1
        ],
        'filters' => [
            'role' => $role_filter,
            'status' => $status_filter,
            'search' => $search,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'sort_by' => $sort_by,
            'sort_order' => $sort_order
        ]
    ]);
} catch (Throwable $e) {
    error_log('users-list error: '.$e->getMessage());
    ApiResponse::error('Server error occurred while retrieving users', 500, ['exception' => $e->getMessage()]);
}

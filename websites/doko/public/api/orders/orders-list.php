<?php
// Orders list endpoint used by tests (simplified)
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/Controllers/AuthController.php';
try {
    $db = Database::getInstance();
    $auth = new AuthController();
    if (!$auth->isLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Authentication required']); exit; }
    $user = $auth->getCurrentUser();
    $status = $_GET['status'] ?? null;
    $params = [];
    $sql = "SELECT o.order_id, o.order_number, o.user_id, o.status, o.total_amount, o.ordered_at FROM orders o WHERE ";
    if ($auth->isAdmin()) { $sql .= '1=1'; } else { $sql .= 'o.user_id = ?'; $params[] = $user['user_id']; }
    if ($status) { $sql .= ' AND o.status = ?'; $params[] = $status; }
    $sql .= ' ORDER BY o.ordered_at DESC';
    $orders = $db->execute($sql, $params)->fetchAll();
    echo json_encode(['success'=>true,'orders'=>$orders]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Failed to fetch orders']);
}
?>
<?php /* Removed large alternate implementation for clarity */ ?>
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
    
    <?php
    header('Content-Type: application/json');
    require_once __DIR__ . '/../../../config/database.php';
    require_once __DIR__ . '/../../../src/Controllers/AuthController.php';
    if (session_status()===PHP_SESSION_NONE) session_start();
    try {
        $db = Database::getInstance();
        $auth = new AuthController();
        if(!$auth->isLoggedIn()){ http_response_code(401); echo json_encode(['success'=>false,'message'=>'Authentication required']); exit; }
        $user = $auth->getCurrentUser();
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $sql = 'SELECT order_id, user_id, status, total_amount, ordered_at FROM orders';
        $params = [];
        $clauses = [];
        if(!$auth->isAdmin()) { $clauses[] = 'user_id = ?'; $params[] = $user['user_id']; }
        if($status){ $clauses[] = 'status = ?'; $params[] = $status; }
        if($clauses){ $sql .= ' WHERE ' . implode(' AND ', $clauses); }
        $sql .= ' ORDER BY ordered_at DESC LIMIT 50';
        $orders = $db->execute($sql,$params)->fetchAll();
        echo json_encode(['success'=>true,'orders'=>$orders]);
    } catch(Exception $e){ http_response_code(500); echo json_encode(['success'=>false,'message'=>'Server error']); }
    ?>
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

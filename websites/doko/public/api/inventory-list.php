<?php
/**
 * Inventory List API
 * Returns comprehensive inventory information with stock levels
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
    
    // Check if user is authenticated and is admin/manager
    if (!$auth->isLoggedIn() || (!$auth->isAdmin() && !$auth->isManager())) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access. Admin or Manager role required.'
        ]);
        exit;
    }
    
    // Get query parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;
    
    $category_filter = isset($_GET['category']) ? $_GET['category'] : '';
    $low_stock_only = isset($_GET['low_stock']) && $_GET['low_stock'] === '1';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'product_name';
    $sort_order = isset($_GET['sort_order']) && strtoupper($_GET['sort_order']) === 'DESC' ? 'DESC' : 'ASC';
    
    // Valid sort columns
    $valid_sort_columns = ['product_name', 'category_name', 'stock_quantity', 'price', 'created_at', 'low_stock_threshold'];
    if (!in_array($sort_by, $valid_sort_columns)) {
        $sort_by = 'product_name';
    }
    
    // Build WHERE conditions
    $where_conditions = [];
    $params = [];
    
    if (!empty($category_filter)) {
        $where_conditions[] = "c.category_id = ?";
        $params[] = $category_filter;
    }
    
    if ($low_stock_only) {
        $where_conditions[] = "p.stock_quantity <= p.low_stock_threshold";
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(p.name LIKE ? OR p.sku LIKE ? OR c.name LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get total count for pagination
    $count_query = "
        SELECT COUNT(DISTINCT p.product_id) as total
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        $where_clause
    ";
    
    $count_stmt = $db->execute($count_query, $params);
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $limit);
    
    // Main inventory query
    $query = "
        SELECT 
            p.product_id,
            p.name as product_name,
            p.sku,
            p.price,
            p.stock_quantity,
            p.low_stock_threshold,
            p.is_active,
            p.created_at,
            p.updated_at,
            c.name as category_name,
            c.category_id,
            b.name as brand_name,
            b.brand_id,
            CASE 
                WHEN p.stock_quantity <= 0 THEN 'Out of Stock'
                WHEN p.stock_quantity <= p.low_stock_threshold THEN 'Low Stock'
                WHEN p.stock_quantity <= (p.low_stock_threshold * 2) THEN 'Medium Stock'
                ELSE 'In Stock'
            END as stock_status,
            CASE 
                WHEN p.stock_quantity <= 0 THEN 'danger'
                WHEN p.stock_quantity <= p.low_stock_threshold THEN 'warning'
                WHEN p.stock_quantity <= (p.low_stock_threshold * 2) THEN 'info'
                ELSE 'success'
            END as stock_status_class,
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.product_id AND pi.is_primary = 1 LIMIT 1) as primary_image,
            (SELECT COUNT(*) FROM order_items oi WHERE oi.product_id = p.product_id) as total_sold
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        $where_clause
        ORDER BY $sort_by $sort_order
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $db->execute($query, $params);
    $inventory_items = $stmt->fetchAll();
    
    // Get inventory statistics
    $stats_query = "
        SELECT 
            COUNT(*) as total_products,
            SUM(stock_quantity) as total_stock,
            SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock,
            SUM(CASE WHEN stock_quantity <= low_stock_threshold AND stock_quantity > 0 THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_products,
            AVG(stock_quantity) as avg_stock_level,
            SUM(price * stock_quantity) as total_inventory_value
        FROM products
        WHERE 1=1
    ";
    
    if (!empty($category_filter)) {
        $stats_query .= " AND category_id = ?";
        $stats_stmt = $db->execute($stats_query, [$category_filter]);
    } else {
        $stats_stmt = $db->execute($stats_query);
    }
    
    $stats = $stats_stmt->fetch();
    
    // Get category breakdown
    $category_query = "
        SELECT 
            c.category_id,
            c.name as category_name,
            COUNT(p.product_id) as product_count,
            SUM(p.stock_quantity) as total_stock,
            SUM(CASE WHEN p.stock_quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock_count,
            SUM(CASE WHEN p.stock_quantity <= p.low_stock_threshold AND p.stock_quantity > 0 THEN 1 ELSE 0 END) as low_stock_count
        FROM categories c
        LEFT JOIN products p ON c.category_id = p.category_id
        WHERE c.is_active = 1
        GROUP BY c.category_id, c.name
        ORDER BY c.name ASC
    ";
    
    $category_stmt = $db->execute($category_query);
    $category_breakdown = $category_stmt->fetchAll();
    
    // Process the results
    foreach ($inventory_items as &$item) {
        // Format dates
        $item['created_at'] = date('Y-m-d H:i:s', strtotime($item['created_at']));
        $item['updated_at'] = date('Y-m-d H:i:s', strtotime($item['updated_at']));
        
        // Format price
        $item['price'] = number_format($item['price'], 2);
        
        // Set default image if none exists
        if (empty($item['primary_image'])) {
            $item['primary_image'] = 'uploads/default-product.jpg';
        }
        
        // Calculate stock percentage
        if ($item['low_stock_threshold'] > 0) {
            $item['stock_percentage'] = min(100, ($item['stock_quantity'] / ($item['low_stock_threshold'] * 3)) * 100);
        } else {
            $item['stock_percentage'] = $item['stock_quantity'] > 0 ? 100 : 0;
        }
        
        // Add recommendations
        $item['recommendations'] = [];
        if ($item['stock_quantity'] <= 0) {
            $item['recommendations'][] = 'Restock immediately - Product is out of stock';
        } elseif ($item['stock_quantity'] <= $item['low_stock_threshold']) {
            $item['recommendations'][] = 'Consider restocking - Stock level is below threshold';
        }
        
        if ($item['total_sold'] > 10 && $item['stock_quantity'] <= $item['low_stock_threshold']) {
            $item['recommendations'][] = 'High-demand product with low stock - Priority restock';
        }
        
        if (!$item['is_active'] && $item['stock_quantity'] > 0) {
            $item['recommendations'][] = 'Product is inactive but has stock - Consider activation';
        }
    }
    
    // Format statistics
    $stats['total_inventory_value'] = number_format($stats['total_inventory_value'], 2);
    $stats['avg_stock_level'] = round($stats['avg_stock_level'], 2);
    
    $response = [
        'success' => true,
        'message' => 'Inventory data retrieved successfully',
        'data' => [
            'inventory' => $inventory_items,
            'statistics' => $stats,
            'category_breakdown' => $category_breakdown,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_records' => $total_records,
                'per_page' => $limit,
                'has_next' => $page < $total_pages,
                'has_prev' => $page > 1
            ],
            'filters' => [
                'category' => $category_filter,
                'low_stock_only' => $low_stock_only,
                'search' => $search,
                'sort_by' => $sort_by,
                'sort_order' => $sort_order
            ]
        ],
        'timestamp' => date('c')
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred while retrieving inventory data',
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
    
    // Log error for debugging
    error_log("Inventory List API Error: " . $e->getMessage());
}
?>

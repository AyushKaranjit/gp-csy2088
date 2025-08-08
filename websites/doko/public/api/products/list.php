<?php
/**
 * Products List API
 * Get products with filtering and pagination
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Include database configuration with error handling
$config_path = __DIR__ . '/../../../config/database.php';
if (!file_exists($config_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Configuration file not found']);
    exit;
}

require_once $config_path;

try {
    // Get query parameters
    // Accept legacy alias 'category' as well
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : (isset($_GET['category']) ? (int)$_GET['category'] : null);
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $featured = isset($_GET['featured']) ? (bool)$_GET['featured'] : null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    // Support page based pagination for tests: page param overrides offset if present
    if (isset($_GET['page'])) {
        $page = max(1, (int)$_GET['page']);
        $offset = ($page - 1) * $limit;
    } else {
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $page = floor($offset / $limit) + 1;
    }
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
    $order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';
    
    // Validate sort field
    $allowed_sorts = ['name', 'price', 'created_at', 'stock_quantity'];
    if (!in_array($sort, $allowed_sorts)) {
        $sort = 'created_at';
    }
    
    // Get database connection
    $db = Database::getInstance();
    
    // Build query
    $where_conditions = ['p.status = ?'];
    $params = ['active'];
    
    if ($category_id) {
        $where_conditions[] = 'p.category_id = ?';
        $params[] = $category_id;
    }
    
    if ($featured !== null) {
        $where_conditions[] = 'p.featured = ?';
        $params[] = $featured ? 1 : 0;
    }
    
    if ($search) {
        $where_conditions[] = '(p.name LIKE ? OR p.description LIKE ?)';
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    // Optional price range filters (for tests compatibility)
    if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
        $where_conditions[] = 'p.price >= ?';
        $params[] = (float)$_GET['min_price'];
    }
    if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
        $where_conditions[] = 'p.price <= ?';
        $params[] = (float)$_GET['max_price'];
    }

    $where_clause = implode(' AND ', $where_conditions);
    
    // Count total products
    $count_query = "SELECT COUNT(*) as total FROM products p WHERE $where_clause";
    $stmt = $db->execute($count_query, $params);
    $total = $stmt->fetch()['total'];
    
    // Get products
    $query = "SELECT p.product_id, p.name, p.description, p.price, p.original_price,
                     p.stock_quantity, p.unit, p.featured, p.category_id, p.created_at,
                     c.name as category_name, pi.image_url AS primary_image
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id
              LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
              WHERE $where_clause
              ORDER BY p.$sort $order
              LIMIT $limit OFFSET $offset";
    
    $stmt = $db->execute($query, $params);
    $products = $stmt->fetchAll();
    
    $formatted_products = [];
    foreach ($products as $product) {
        // Determine image path (support external absolute URL or local upload filename)
        $image_url = '/uploads/default-product.jpg';
        $candidate = $product['primary_image'] ?? ($product['image_url'] ?? '');
        if ($candidate) {
            if (preg_match('#^https?://#i', $candidate)) {
                $image_url = $candidate; // external
            } elseif (file_exists(__DIR__ . '/../../uploads/' . $candidate)) {
                $image_url = '/uploads/' . $candidate;
            }
        }
        
        $formatted_products[] = [
            'product_id' => (int)$product['product_id'],
            'name' => $product['name'],
            'short_description' => $product['short_description'] ?? substr($product['description'], 0, 100) . '...',
            'description' => $product['description'],
            'price' => (float)$product['price'],
            'original_price' => $product['original_price'] ? (float)$product['original_price'] : null,
            'image_url' => $image_url,
            'stock' => (int)$product['stock_quantity'],
            'unit' => $product['unit'] ?: 'piece',
            'featured' => (bool)$product['featured'],
            'category_id' => (int)$product['category_id'],
            'category_name' => $product['category_name'],
            'created_at' => $product['created_at'],
            'in_stock' => (int)$product['stock_quantity'] > 0,
            'discount_percentage' => $product['original_price'] && $product['original_price'] > $product['price'] ? 
                round((($product['original_price'] - $product['price']) / $product['original_price']) * 100) : 0
        ];
    }
    
    $totalPages = ceil($total / $limit);
    echo json_encode([
        'success' => true,
        // Provide both new ('data') and legacy ('products') keys for backward compatibility
        'data' => $formatted_products,
        'products' => $formatted_products,
        'pagination' => [
            // Legacy snake_case keys expected by tests
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => (int)$total,
            'items_per_page' => $limit,
            'has_next' => $page < $totalPages,
            'has_previous' => $page > 1,
            // Existing camelCase keys kept for any newer consumers
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset,
            'hasMore' => ($offset + $limit) < $total
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Product list API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching products'
    ]);
}
?>

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

require_once '../config/database.php';

try {
    // Get query parameters
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
    $order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';
    
    // Validate sort field
    $allowed_sorts = ['name', 'price', 'created_at', 'stock'];
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
    
    if ($search) {
        $where_conditions[] = '(p.name LIKE ? OR p.description LIKE ?)';
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Count total products
    $count_query = "SELECT COUNT(*) as total FROM products p WHERE $where_clause";
    $stmt = $db->execute($count_query, $params);
    $total = $stmt->fetch()['total'];
    
    // Get products
    $query = "SELECT p.product_id, p.name, p.description, p.price, p.image, p.stock, 
                     p.category_id, p.created_at, c.name as category_name
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id
              WHERE $where_clause
              ORDER BY p.$sort $order
              LIMIT $limit OFFSET $offset";
    
    $stmt = $db->execute($query, $params);
    $products = $stmt->fetchAll();
    
    $formatted_products = [];
    foreach ($products as $product) {
        $formatted_products[] = [
            'product_id' => (int)$product['product_id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => (float)$product['price'],
            'image_url' => $product['image'], // Changed to image_url for JS compatibility
            'stock' => (int)$product['stock'],
            'category_id' => (int)$product['category_id'],
            'category_name' => $product['category_name'],
            'created_at' => $product['created_at'],
            'unit' => 'piece' // Add unit field for cart display
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formatted_products, // Changed from 'products' to 'data'
        'pagination' => [ // Added pagination object
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset,
            'currentPage' => floor($offset / $limit) + 1,
            'totalPages' => ceil($total / $limit),
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

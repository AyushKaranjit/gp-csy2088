<?php
/**
 * Product Detail API  
 * Get single product details
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
    // Get product ID from query parameter
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($product_id <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Valid product ID is required'
        ]);
        exit;
    }
    
    // Get database connection
    $db = Database::getInstance();
    
    // Get product details with category information
    $query = "SELECT p.product_id, p.name, p.description, p.price, p.original_price,
                     p.stock_quantity, p.unit, p.category_id, p.created_at, p.updated_at, p.status,
                     c.name as category_name
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id
              WHERE p.product_id = ? AND p.status = 'active'";
    
    $stmt = $db->execute($query, [$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }
    
    // Format product data
    $image_url = '/uploads/default-product.jpg';
    if (!empty($product['image_url']) && file_exists('../uploads/' . $product['image_url'])) {
        $image_url = '/uploads/' . $product['image_url'];
    }
    
    $formatted_product = [
        'product_id' => (int)$product['product_id'],
        'name' => $product['name'],
        'short_description' => $product['short_description'] ?? substr($product['description'], 0, 150) . '...',
        'description' => $product['description'],
        'price' => (float)$product['price'],
        'original_price' => $product['original_price'] ? (float)$product['original_price'] : null,
        'image_url' => $image_url,
        'stock' => (int)$product['stock_quantity'],
        'unit' => $product['unit'] ?: 'piece',
        'category_id' => (int)$product['category_id'],
        'category_name' => $product['category_name'],
        'created_at' => $product['created_at'],
        'updated_at' => $product['updated_at'],
        'in_stock' => (int)$product['stock_quantity'] > 0,
        'discount_percentage' => $product['original_price'] && $product['original_price'] > $product['price'] ? 
            round((($product['original_price'] - $product['price']) / $product['original_price']) * 100) : 0
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $formatted_product
    ]);
    
} catch (Exception $e) {
    error_log("Product detail API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching product details'
    ]);
}
?>

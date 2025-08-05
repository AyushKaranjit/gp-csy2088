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

require_once '../config/database.php';

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
    $query = "SELECT p.product_id, p.name, p.description, p.price, p.image, p.stock, 
                     p.category_id, p.created_at, p.updated_at, p.status,
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
    $formatted_product = [
        'product_id' => (int)$product['product_id'],
        'name' => $product['name'],
        'description' => $product['description'],
        'price' => (float)$product['price'],
        'image' => $product['image'],
        'stock' => (int)$product['stock'],
        'category_id' => (int)$product['category_id'],
        'category_name' => $product['category_name'],
        'created_at' => $product['created_at'],
        'updated_at' => $product['updated_at'],
        'in_stock' => (int)$product['stock'] > 0
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
        'created_at' => $product['created_at'],
        'updated_at' => $product['updated_at'],
        'in_stock' => (int)$product['stock'] > 0
    ];
    
    echo json_encode([
        'success' => true,
        'product' => $formatted_product
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

<?php
/**
 * Product Detail API - Get single product by ID
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';
require_once '../../src/Models/Product.php';

try {
    $product = new Product();
    
    // Get product ID from query parameter
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$product_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        exit;
    }
    
    // Get product details
    $productData = $product->getProductById($product_id);
    
    if (!$productData) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }
    
    // Format product data
    $formattedProduct = [
        'product_id' => (int)$productData['product_id'],
        'name' => $productData['name'],
        'description' => $productData['description'],
        'price' => (float)$productData['price'],
        'original_price' => $productData['original_price'] ? (float)$productData['original_price'] : null,
        'discount_percent' => $productData['original_price'] ? 
            round(((float)$productData['original_price'] - (float)$productData['price']) / (float)$productData['original_price'] * 100) : 0,
        'category_id' => (int)$productData['category_id'],
        'category_name' => $productData['category_name'],
        'stock' => (int)$productData['stock'],
        'unit' => $productData['unit'],
        'weight' => $productData['weight'],
        'image_url' => $productData['image_url'],
        'featured' => (bool)$productData['featured'],
        'is_active' => (bool)$productData['is_active'],
        'nutritional_info' => $productData['nutritional_info'],
        'avg_rating' => $productData['avg_rating'] ? round((float)$productData['avg_rating'], 1) : null,
        'review_count' => (int)$productData['review_count'],
        'in_stock' => (int)$productData['stock'] > 0,
        'created_at' => $productData['created_at'],
        'updated_at' => $productData['updated_at']
    ];
    
    $response = [
        'success' => true,
        'data' => $formattedProduct
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching product: ' . $e->getMessage()
    ]);
}
?>

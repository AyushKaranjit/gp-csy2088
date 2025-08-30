<?php
/** Product Detail API (refactored) */
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method('GET');

try {
    // Get product ID from query parameter
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($product_id <= 0) { ApiResponse::error('Valid product ID is required', 400); return; }
    
    // Get database connection
    $db = Database::getInstance();
    $productsPk = schema_products_pk();
    
    // Get product details with category information
    $query = "SELECT p.{$productsPk} AS product_id, p.name, p.slug, p.description, p.price, p.original_price,
                     p.stock_quantity, p.unit, p.category_id, p.created_at, p.updated_at, p.status,
                     c.name as category_name, pi.image_url AS primary_image
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id
              LEFT JOIN product_images pi ON p.{$productsPk} = pi.product_id AND pi.is_primary = 1
              WHERE p.{$productsPk} = ? AND p.status = 'active'";
    
    $stmt = $db->execute($query, [$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) { ApiResponse::error('Product not found', 404); return; }
    
    // Format product data
    $image_url = '/images/default-product.jpg';
    $candidate = $product['primary_image'] ?? ($product['image_url'] ?? '');
    if ($candidate) {
        if (preg_match('#^https?://#i', $candidate)) {
            $image_url = $candidate;
        } elseif (preg_match('#^/images/#', $candidate)) {
            $image_url = $candidate;
        } elseif (file_exists(__DIR__ . '/uploads/' . $candidate)) {
            $image_url = '/uploads/' . $candidate;
        } elseif (file_exists(__DIR__ . '/images/' . $candidate)) {
            $image_url = '/images/' . $candidate;
        }
    }
    // Fallback to slug-based image
    if ($image_url === '/images/default-product.jpg' && !empty($product['slug'])) {
        $slug_candidate = $product['slug'] . '.jpg';
        if (file_exists(__DIR__ . '/images/' . $slug_candidate)) {
            $image_url = '/images/' . $slug_candidate;
        }
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
        // legacy key expected in some integration tests
        'stock_quantity' => (int)$product['stock_quantity'],
        'unit' => $product['unit'] ?: 'piece',
        'category_id' => (int)$product['category_id'],
        'category_name' => $product['category_name'],
        'created_at' => $product['created_at'],
        'updated_at' => $product['updated_at'],
        'in_stock' => (int)$product['stock_quantity'] > 0,
        'discount_percentage' => $product['original_price'] && $product['original_price'] > $product['price'] ? 
            round((($product['original_price'] - $product['price']) / $product['original_price']) * 100) : 0
    ];
    
    ApiResponse::success([
        'data' => $formatted_product,
        'product' => $formatted_product
    ]);
    
} catch (Exception $e) {
    error_log("Product detail API error: " . $e->getMessage());
    ApiResponse::error('An error occurred while fetching product details', 500);
}
?>

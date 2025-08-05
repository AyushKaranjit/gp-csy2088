<?php
/**
 * Featured Products API
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    // Get featured products with category and image info
    $query = "SELECT p.product_id, p.sku, p.name, p.slug, p.short_description, 
                     p.price, p.original_price, p.stock_quantity, p.unit, 
                     p.weight, p.weight_unit, p.featured, p.status,
                     c.name as category_name, c.slug as category_slug,
                     b.name as brand_name,
                     pi.image_url,
                     AVG(pr.rating) as average_rating,
                     COUNT(pr.review_id) as review_count,
                     CASE 
                         WHEN p.stock_quantity <= p.min_stock_level THEN 'low'
                         WHEN p.stock_quantity = 0 THEN 'out'
                         ELSE 'in_stock'
                     END as stock_status
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id
              LEFT JOIN brands b ON p.brand_id = b.brand_id
              LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = TRUE
              LEFT JOIN product_reviews pr ON p.product_id = pr.product_id AND pr.status = 'approved'
              WHERE p.featured = TRUE 
              AND p.status = 'active'
              AND c.is_active = TRUE
              GROUP BY p.product_id
              ORDER BY p.sort_order ASC, p.created_at DESC
              LIMIT 12";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    // Include image service for fallback images
    require_once '../../../template/image-service.php';
    
    // Format product data
    $formattedProducts = array_map(function($p) {
        $discountPercent = 0;
        if ($p['original_price'] && $p['original_price'] > $p['price']) {
            $discountPercent = round((($p['original_price'] - $p['price']) / $p['original_price']) * 100);
        }
        
        // Use fallback image if no image available
        $imageUrl = $p['image_url'] ?: getProductImage($p['name']);
        
        return [
            'product_id' => (int)$p['product_id'],
            'sku' => $p['sku'],
            'name' => $p['name'],
            'slug' => $p['slug'],
            'short_description' => $p['short_description'],
            'price' => (float)$p['price'],
            'original_price' => $p['original_price'] ? (float)$p['original_price'] : null,
            'discount_percent' => $discountPercent,
            'category_name' => $p['category_name'],
            'category_slug' => $p['category_slug'],
            'brand_name' => $p['brand_name'],
            'stock_quantity' => (int)$p['stock_quantity'],
            'stock_status' => $p['stock_status'],
            'unit' => $p['unit'],
            'weight' => $p['weight'] ? (float)$p['weight'] : null,
            'weight_unit' => $p['weight_unit'],
            'image_url' => $imageUrl,
            'average_rating' => $p['average_rating'] ? round((float)$p['average_rating'], 1) : 0,
            'review_count' => (int)$p['review_count'],
            'is_featured' => true,
            'in_stock' => $p['stock_quantity'] > 0,
            'created_at' => $p['created_at'] ?? null
        ];
    }, $products);
    
    $response = [
        'success' => true,
        'data' => $formattedProducts,
        'count' => count($formattedProducts)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Featured products error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching featured products: ' . $e->getMessage()
    ]);
}
?>

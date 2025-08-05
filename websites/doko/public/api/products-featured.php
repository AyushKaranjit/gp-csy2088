<?php
/**
 * Featured Products API
 * DOKO Grocery E-commerce
 */

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
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    // Get pagination parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 12;
    $offset = ($page - 1) * $limit;
    
    // Get featured products
    $query = "SELECT p.product_id, p.name, p.slug, p.description, p.price, p.original_price,
                     p.image_url, p.stock_quantity, p.unit, p.weight, p.weight_unit,
                     p.is_featured, p.created_at,
                     c.name as category_name, c.slug as category_slug
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id
              WHERE p.status = 'active' AND p.featured = TRUE
              ORDER BY p.created_at DESC, p.name ASC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert numeric fields
    foreach ($products as &$product) {
        $product['product_id'] = (int)$product['product_id'];
        $product['price'] = (float)$product['price'];
        $product['original_price'] = $product['original_price'] ? (float)$product['original_price'] : null;
        $product['stock_quantity'] = (int)$product['stock_quantity'];
        $product['weight'] = $product['weight'] ? (float)$product['weight'] : null;
        $product['is_featured'] = (bool)$product['is_featured'];
        
        // Calculate discount percentage if applicable
        if ($product['original_price'] && $product['original_price'] > $product['price']) {
            $product['discount_percentage'] = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
        } else {
            $product['discount_percentage'] = 0;
        }
        
        // Add image URL with fallback
        if (!$product['image_url'] || !file_exists('../uploads/products/' . $product['image_url'])) {
            $product['image_url'] = 'default.jpg';
        }
    }
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) FROM products WHERE status = 'active' AND featured = TRUE";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute();
    $totalProducts = (int)$countStmt->fetchColumn();
    
    $totalPages = ceil($totalProducts / $limit);
    
    echo json_encode([
        'success' => true,
        'data' => $products, // Changed from 'products' to 'data'
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalProducts,
            'items_per_page' => $limit,
            'has_next' => $page < $totalPages,
            'has_previous' => $page > 1
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>

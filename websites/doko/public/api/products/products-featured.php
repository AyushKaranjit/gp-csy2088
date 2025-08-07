<?php
/**
 * Featured Products API
 * DOKO Grocery     // Convert numeric fields and add proper image URLs
    foreach ($products as &$product) {
        $product['product_id'] = (int)$product['product_id'];
        $product['price'] = (float)$product['price'];
        $product['original_price'] = $product['original_price'] ? (float)$product['original_price'] : null;
        $product['stock_quantity'] = (int)$product['stock_quantity'];
        $product['weight'] = $product['weight'] ? (float)$product['weight'] : null;
        $product['is_featured'] = (bool)$product['is_featured'];
        
        // Set default image if none exists
        if (empty($product['image_url']) || $product['image_url'] === null) {
            $product['image_url'] = '/uploads/products/product-default.svg';
        } else if (!str_starts_with($product['image_url'], 'http') && !str_starts_with($product['image_url'], '/')) {
            $product['image_url'] = '/' . ltrim($product['image_url'], '/');
        }
        
        // Calculate discount percentage if applicable
        if ($product['original_price'] && $product['original_price'] > $product['price']) {
            $product['discount_percentage'] = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
        } else {
            $product['discount_percentage'] = 0;
        }

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
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    // Get pagination parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 12;
    $offset = ($page - 1) * $limit;
    
    // Get featured products with complete information
    $query = "SELECT p.product_id, p.name, p.slug, p.description, p.short_description,
                     p.price, p.original_price, p.cost_price,
                     p.stock_quantity, p.unit, p.weight, p.weight_unit,
                     p.featured, p.created_at, p.sku, p.barcode,
                     p.nutritional_info, p.ingredients, p.allergen_info,
                     c.name as category_name, c.slug as category_slug,
                     b.name as brand_name,
                     COALESCE(pi.image_url, 'default.jpg') as image_url
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id
              LEFT JOIN brands b ON p.brand_id = b.brand_id
              LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
              WHERE p.status = 'active' AND p.featured = TRUE
              ORDER BY p.created_at DESC, p.name ASC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert numeric fields and add proper image URLs
    foreach ($products as &$product) {
        $product['product_id'] = (int)$product['product_id'];
        $product['price'] = (float)$product['price'];
        $product['original_price'] = $product['original_price'] ? (float)$product['original_price'] : null;
        $product['cost_price'] = $product['cost_price'] ? (float)$product['cost_price'] : null;
        $product['stock_quantity'] = (int)$product['stock_quantity'];
        $product['weight'] = $product['weight'] ? (float)$product['weight'] : null;
        $product['featured'] = (bool)$product['featured'];
        
        // Set default values for missing fields
        $product['brand_name'] = $product['brand_name'] ?? 'Generic';
        $product['category_name'] = $product['category_name'] ?? 'General';
        $product['short_description'] = $product['short_description'] ?? substr($product['description'], 0, 100) . '...';
        
        // Calculate discount percentage if applicable
        if ($product['original_price'] && $product['original_price'] > $product['price']) {
            $product['discount_percentage'] = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
        } else {
            $product['discount_percentage'] = 0;
        }
        
        // Set proper image URL with fallback
        if (!empty($product['image_url'])) {
            // Check if it's already a full URL or just a filename
            if (strpos($product['image_url'], '/uploads/') === 0) {
                // Already a proper path
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . $product['image_url'];
            } else {
                // Just a filename, construct path
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/' . $product['image_url'];
                $product['image_url'] = '/uploads/products/' . $product['image_url'];
            }
            
            // Check if file exists, otherwise use default
            if (!file_exists($imagePath)) {
                $product['image_url'] = '/uploads/products/default.jpg';
            }
        } else {
            $product['image_url'] = '/uploads/products/default.jpg';
        }
        
        // Add stock status
        $product['in_stock'] = $product['stock_quantity'] > 0;
        
        // Add short description if missing
        if (empty($product['short_description']) && !empty($product['description'])) {
            $product['short_description'] = substr($product['description'], 0, 100) . '...';
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

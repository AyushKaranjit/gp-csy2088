<?php
/**
 * Product Search API
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
    
    // Get search query
    $searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 20;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    
    if (empty($searchQuery)) {
        echo json_encode([
            'success' => true,
            'data' => [],
            'pagination' => [
                'current_page' => 1,
                'per_page' => $limit,
                'total' => 0,
                'total_pages' => 0
            ],
            'query' => $searchQuery
        ]);
        exit;
    }
    
    // Log search query for analytics
    $logQuery = "INSERT INTO search_queries (query_text, user_id, session_id, ip_address) 
                 VALUES (:query_text, :user_id, :session_id, :ip_address)";
    $logStmt = $conn->prepare($logQuery);
    session_start();
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $sessionId = session_id();
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $logStmt->bindParam(':query_text', $searchQuery);
    $logStmt->bindParam(':user_id', $userId);
    $logStmt->bindParam(':session_id', $sessionId);
    $logStmt->bindParam(':ip_address', $ipAddress);
    $logStmt->execute();
    
    // Search products using FULLTEXT or LIKE
    $searchTerm = '%' . $searchQuery . '%';
    
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
                     END as stock_status,
                     -- Relevance scoring
                     CASE 
                         WHEN p.name LIKE :exact_match THEN 100
                         WHEN p.name LIKE :starts_with THEN 90
                         WHEN p.name LIKE :search_term THEN 80
                         WHEN p.short_description LIKE :search_term THEN 70
                         WHEN c.name LIKE :search_term THEN 60
                         WHEN b.name LIKE :search_term THEN 50
                         ELSE 40
                     END as relevance_score
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id
              LEFT JOIN brands b ON p.brand_id = b.brand_id
              LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = TRUE
              LEFT JOIN product_reviews pr ON p.product_id = pr.product_id AND pr.status = 'approved'
              WHERE p.status = 'active'
              AND (c.is_active = TRUE OR c.is_active IS NULL)
              AND (
                  p.name LIKE :search_term
                  OR p.short_description LIKE :search_term
                  OR p.description LIKE :search_term
                  OR c.name LIKE :search_term
                  OR b.name LIKE :search_term
                  OR p.sku LIKE :search_term
              )
              GROUP BY p.product_id
              ORDER BY relevance_score DESC, p.featured DESC, p.sort_order ASC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    $exactMatch = $searchQuery;
    $startsWith = $searchQuery . '%';
    $stmt->bindParam(':exact_match', $exactMatch);
    $stmt->bindParam(':starts_with', $startsWith);
    $stmt->bindParam(':search_term', $searchTerm);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll();
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(DISTINCT p.product_id) as total
                   FROM products p
                   LEFT JOIN categories c ON p.category_id = c.category_id
                   LEFT JOIN brands b ON p.brand_id = b.brand_id
                   WHERE p.status = 'active'
                   AND (c.is_active = TRUE OR c.is_active IS NULL)
                   AND (
                       p.name LIKE :search_term
                       OR p.short_description LIKE :search_term
                       OR p.description LIKE :search_term
                       OR c.name LIKE :search_term
                       OR b.name LIKE :search_term
                       OR p.sku LIKE :search_term
                   )";
    
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bindParam(':search_term', $searchTerm);
    $countStmt->execute();
    $totalProducts = $countStmt->fetchColumn();
    $totalPages = ceil($totalProducts / $limit);
    
    // Update search query with results count
    $updateQuery = "UPDATE search_queries SET results_count = :results_count 
                    WHERE query_text = :query_text AND session_id = :session_id 
                    ORDER BY searched_at DESC LIMIT 1";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindParam(':results_count', $totalProducts);
    $updateStmt->bindParam(':query_text', $searchQuery);
    $updateStmt->bindParam(':session_id', $sessionId);
    $updateStmt->execute();
    
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
            'is_featured' => (bool)$p['featured'],
            'in_stock' => $p['stock_quantity'] > 0,
            'relevance_score' => (int)$p['relevance_score']
        ];
    }, $products);
    
    $response = [
        'success' => true,
        'data' => $formattedProducts,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => (int)$totalProducts,
            'total_pages' => (int)$totalPages
        ],
        'query' => $searchQuery,
        'count' => count($formattedProducts)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Product search error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error searching products: ' . $e->getMessage()
    ]);
}
?>

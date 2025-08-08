<?php
/** Product Search API (refactored) */
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method('GET');

try {
    $database = Database::getInstance();
    $conn = $database->getConnection();
    $productsPk = schema_products_pk();
    
    // Get search parameters
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 12;
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    $min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
    $max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'relevance';
    
    if (empty($query)) { ApiResponse::error('Search query is required', 400); return; }
    
    $offset = ($page - 1) * $limit;
    
    // Build the search query
    $whereConditions = ["p.status = 'active'"];
    $params = [];
    $paramTypes = []; // track for clarity (not strictly needed with PDO)
    
    // Add search condition
    $searchTerms = explode(' ', $query);
    $searchConditions = [];
    
    $dynamicSearchParams = [];
    foreach ($searchTerms as $term) {
        $term = trim($term);
        if ($term === '') continue;
        $dynamicSearchParams[] = '%' . $term . '%';
        // placeholders will be added later using positional ? marks
        $searchConditions[] = '(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)';
    }
    
    if (!empty($searchConditions)) {
        $whereConditions[] = '(' . implode(' OR ', $searchConditions) . ')';
    }
    
    // Add category filter
    if ($category_id) {
        $whereConditions[] = 'p.category_id = ?';
        $params[] = $category_id;
    }
    
    // Add price filters
    if ($min_price !== null) {
        $whereConditions[] = 'p.price >= ?';
        $params[] = $min_price;
    }
    
    if ($max_price !== null) {
        $whereConditions[] = 'p.price <= ?';
        $params[] = $max_price;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Determine sort order
    $orderBy = 'ORDER BY ';
    $relevanceParams = [];
    switch ($sort_by) {
        case 'price_low':
            $orderBy .= 'p.price ASC';
            break;
        case 'price_high':
            $orderBy .= 'p.price DESC';
            break;
        case 'name':
            $orderBy .= 'p.name ASC';
            break;
        case 'newest':
            $orderBy .= 'p.created_at DESC';
            break;
        case 'featured':
                $orderBy .= 'p.featured DESC, p.name ASC';
            break;
        case 'relevance':
        default:
            // Use simple LOCATE-based ordering without extra bound params to avoid HY093 issues
            // First term gets highest priority; fallback to name ASC
            $firstTerm = null;
            foreach($searchTerms as $t){ $t = trim($t); if($t!==''){ $firstTerm = $t; break; } }
            if($firstTerm){
                // LOCATE returns 0 if not found; order by found first then position then name
                $safe = str_replace("'", "''", $firstTerm);
                $orderBy .= "(CASE WHEN LOCATE('{$safe}', p.name) > 0 THEN 0 ELSE 1 END), LOCATE('{$safe}', p.name), p.name ASC";
            } else {
                $orderBy .= 'p.name ASC';
            }
            break;
    }
    
    // Main search query
    // Replace named :limit/:offset with positional placeholders to align with unified execute order
    $searchQuery = "SELECT p.{$productsPk} AS product_id, p.name, p.slug, p.description, p.price, p.original_price,
                           p.stock_quantity, p.unit, p.weight, p.weight_unit,
                           p.featured, p.created_at,
                           c.name as category_name, c.slug as category_slug,
                           COALESCE(pi.image_url, 'default.jpg') AS image_url
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.category_id
                    LEFT JOIN product_images pi ON p.{$productsPk} = pi.product_id AND pi.is_primary = 1
                    $whereClause
                    $orderBy
                    LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($searchQuery);
    // Build final ordered param list: dynamic search (each term repeated 3 times) then filter params then limit/offset
    $finalParams = [];
    foreach ($dynamicSearchParams as $sp) { $finalParams[] = $sp; $finalParams[] = $sp; $finalParams[] = $sp; }
    foreach ($params as $p) { $finalParams[] = $p; }
    $finalParams[] = (int)$limit;
    $finalParams[] = (int)$offset;
    $stmt->execute($finalParams);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert numeric fields
    foreach ($products as &$product) {
        $product['product_id'] = (int)$product['product_id'];
        $product['price'] = (float)$product['price'];
        $product['original_price'] = $product['original_price'] ? (float)$product['original_price'] : null;
        $product['stock_quantity'] = (int)$product['stock_quantity'];
        $product['weight'] = $product['weight'] ? (float)$product['weight'] : null;
        // Normalize featured flag (schema uses 'featured')
        if (isset($product['featured'])) {
            $product['is_featured'] = (bool)$product['featured'];
        } elseif (isset($product['is_featured'])) {
            $product['is_featured'] = (bool)$product['is_featured'];
        } else {
            $product['is_featured'] = false;
        }
        
        // Calculate discount percentage if applicable
        if ($product['original_price'] && $product['original_price'] > $product['price']) {
            $product['discount_percentage'] = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
        } else {
            $product['discount_percentage'] = 0;
        }
        
        // Add image URL with fallback
        if (empty($product['image_url'])) {
            $product['image_url'] = '/images/default-product.jpg';
        } else if (strpos($product['image_url'], '/uploads/') !== 0) {
            $product['image_url'] = '/uploads/products/' . ltrim($product['image_url'], '/');
        }
    }
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) FROM products p
                   LEFT JOIN categories c ON p.category_id = c.category_id
                   $whereClause";
    
    $countStmt = $conn->prepare($countQuery);
    $countParams = [];
    foreach ($dynamicSearchParams as $sp) { $countParams[] = $sp; $countParams[] = $sp; $countParams[] = $sp; }
    foreach ($params as $p) { $countParams[] = $p; }
    $countStmt->execute($countParams);
    $totalProducts = (int)$countStmt->fetchColumn();
    
    $totalPages = ceil($totalProducts / $limit);
    
    ApiResponse::success([
        'data' => $products,
        'products' => $products,
        'search_query' => $query,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalProducts,
            'items_per_page' => $limit,
            'has_next' => $page < $totalPages,
            'has_previous' => $page > 1
        ],
        'filters_applied' => [
            'category_id' => $category_id,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'sort_by' => $sort_by
        ]
    ]);
    
} catch (Exception $e) {
    ApiResponse::error('Server error: ' . $e->getMessage(), 500);
}
?>

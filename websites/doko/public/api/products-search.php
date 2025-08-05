<?php
/**
 * Product Search API
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
    
    // Get search parameters
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 12;
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    $min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
    $max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'relevance';
    
    if (empty($query)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Search query is required'
        ]);
        exit;
    }
    
    $offset = ($page - 1) * $limit;
    
    // Build the search query
    $whereConditions = ["p.status = 'active'"];
    $params = [];
    
    // Add search condition
    $searchTerms = explode(' ', $query);
    $searchConditions = [];
    
    foreach ($searchTerms as $index => $term) {
        $term = trim($term);
        if (!empty($term)) {
            $paramKey = ':search_' . $index;
            $searchConditions[] = "(p.name LIKE $paramKey OR p.description LIKE $paramKey OR c.name LIKE $paramKey)";
            $params[$paramKey] = '%' . $term . '%';
        }
    }
    
    if (!empty($searchConditions)) {
        $whereConditions[] = '(' . implode(' OR ', $searchConditions) . ')';
    }
    
    // Add category filter
    if ($category_id) {
        $whereConditions[] = 'p.category_id = :category_id';
        $params[':category_id'] = $category_id;
    }
    
    // Add price filters
    if ($min_price !== null) {
        $whereConditions[] = 'p.price >= :min_price';
        $params[':min_price'] = $min_price;
    }
    
    if ($max_price !== null) {
        $whereConditions[] = 'p.price <= :max_price';
        $params[':max_price'] = $max_price;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Determine sort order
    $orderBy = 'ORDER BY ';
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
            $orderBy .= 'p.is_featured DESC, p.name ASC';
            break;
        case 'relevance':
        default:
            // Simple relevance scoring based on name match
            $orderBy .= 'CASE ';
            foreach ($searchTerms as $index => $term) {
                $term = trim($term);
                if (!empty($term)) {
                    $orderBy .= "WHEN p.name LIKE :sort_search_$index THEN 1 ";
                    $params[":sort_search_$index"] = '%' . $term . '%';
                }
            }
            $orderBy .= 'ELSE 2 END, p.name ASC';
            break;
    }
    
    // Main search query
    $searchQuery = "SELECT p.product_id, p.name, p.slug, p.description, p.price, p.original_price,
                           p.image_url, p.stock_quantity, p.unit, p.weight, p.weight_unit,
                           p.is_featured, p.created_at,
                           c.name as category_name, c.slug as category_slug
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.category_id
                    $whereClause
                    $orderBy
                    LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($searchQuery);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
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
    $countQuery = "SELECT COUNT(*) FROM products p
                   LEFT JOIN categories c ON p.category_id = c.category_id
                   $whereClause";
    
    $countStmt = $conn->prepare($countQuery);
    
    // Bind parameters for count query (excluding sort parameters)
    foreach ($params as $key => $value) {
        if (strpos($key, 'sort_search_') !== 0) {
            $countStmt->bindValue($key, $value);
        }
    }
    
    $countStmt->execute();
    $totalProducts = (int)$countStmt->fetchColumn();
    
    $totalPages = ceil($totalProducts / $limit);
    
    echo json_encode([
        'success' => true,
        'data' => $products, // Changed from 'products' to 'data'
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
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>

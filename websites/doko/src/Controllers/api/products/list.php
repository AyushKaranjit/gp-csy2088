<?php
/**
 * Products API - List all products with filtering
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
    
    // Get query parameters
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : null;
    $featured = isset($_GET['featured']) ? (int)$_GET['featured'] : null;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 12;
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'newest';
    $price_range = isset($_GET['price_range']) ? $_GET['price_range'] : null;
    $availability = isset($_GET['availability']) ? $_GET['availability'] : null;
    
    $offset = ($page - 1) * $limit;
    
    // Build filter parameters
    $params = [];
    if ($category_id) $params['category_id'] = $category_id;
    if ($search) $params['search'] = $search;
    if ($featured !== null) $params['featured'] = $featured;
    if ($sort_by) $params['sort_by'] = $sort_by;
    if ($price_range) $params['price_range'] = $price_range;
    if ($availability) $params['availability'] = $availability;
    
    // Get products
    $products = $product->getAllProducts($category_id, $search, $featured, $limit, $offset);
    
    // Get total count for pagination
    $totalProducts = $product->getProductCount($category_id, $search);
    $totalPages = ceil($totalProducts / $limit);
    
    // Apply additional filters
    if ($price_range) {
        $products = array_filter($products, function($p) use ($price_range) {
            $price = (float)$p['price'];
            switch ($price_range) {
                case '0-100': return $price <= 100;
                case '100-250': return $price > 100 && $price <= 250;
                case '250-500': return $price > 250 && $price <= 500;
                case '500-1000': return $price > 500 && $price <= 1000;
                case '1000+': return $price > 1000;
                default: return true;
            }
        });
    }
    
    if ($availability) {
        $products = array_filter($products, function($p) use ($availability) {
            switch ($availability) {
                case 'in_stock': return $p['stock'] > 0;
                case 'on_sale': return $p['original_price'] && $p['original_price'] > $p['price'];
                case 'featured': return $p['featured'] == 1;
                default: return true;
            }
        });
    }
    
    // Apply sorting
    switch ($sort_by) {
        case 'price_low':
            usort($products, function($a, $b) {
                return (float)$a['price'] <=> (float)$b['price'];
            });
            break;
        case 'price_high':
            usort($products, function($a, $b) {
                return (float)$b['price'] <=> (float)$a['price'];
            });
            break;
        case 'name':
            usort($products, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            break;
        case 'rating':
            usort($products, function($a, $b) {
                return (float)$b['avg_rating'] <=> (float)$a['avg_rating'];
            });
            break;
        case 'popularity':
            // Could be based on order count, for now use featured status
            usort($products, function($a, $b) {
                return $b['featured'] <=> $a['featured'];
            });
            break;
        case 'newest':
        default:
            // Already sorted by created_at DESC in the query
            break;
    }
    
    // Format product data
    $formattedProducts = array_map(function($p) {
        return [
            'product_id' => (int)$p['product_id'],
            'name' => $p['name'],
            'description' => $p['description'],
            'price' => (float)$p['price'],
            'original_price' => $p['original_price'] ? (float)$p['original_price'] : null,
            'discount_percent' => $p['original_price'] ? 
                round(((float)$p['original_price'] - (float)$p['price']) / (float)$p['original_price'] * 100) : 0,
            'category_id' => (int)$p['category_id'],
            'category_name' => $p['category_name'],
            'stock' => (int)$p['stock'],
            'unit' => $p['unit'],
            'weight' => $p['weight'],
            'image_url' => $p['image_url'],
            'featured' => (bool)$p['featured'],
            'is_active' => (bool)$p['is_active'],
            'avg_rating' => $p['avg_rating'] ? round((float)$p['avg_rating'], 1) : null,
            'review_count' => (int)$p['review_count'],
            'in_stock' => (int)$p['stock'] > 0,
            'created_at' => $p['created_at']
        ];
    }, $products);
    
    $response = [
        'success' => true,
        'data' => $formattedProducts,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalProducts' => $totalProducts,
            'productsPerPage' => $limit,
            'hasNextPage' => $page < $totalPages,
            'hasPrevPage' => $page > 1
        ],
        'filters' => [
            'category_id' => $category_id,
            'search' => $search,
            'featured' => $featured,
            'sort_by' => $sort_by,
            'price_range' => $price_range,
            'availability' => $availability
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching products: ' . $e->getMessage()
    ]);
}
?>

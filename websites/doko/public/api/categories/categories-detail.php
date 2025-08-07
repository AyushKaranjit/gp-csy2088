<?php
/**
 * Category Detail API
 * Get detailed information about a specific category including products
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

// Check if category ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Category ID is required']);
    exit;
}

$category_id = (int)$_GET['id'];

if ($category_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
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
    // Get database connection
    $db = Database::getInstance();
    
    // Get category details
    $category_query = "SELECT 
                          c.category_id,
                          c.parent_id,
                          c.name,
                          c.slug,
                          c.description,
                          c.image_url,
                          c.icon,
                          c.sort_order,
                          c.meta_title,
                          c.meta_description,
                          c.is_active,
                          c.is_featured,
                          c.created_at,
                          c.updated_at,
                          parent.name as parent_name
                       FROM categories c
                       LEFT JOIN categories parent ON c.parent_id = parent.category_id
                       WHERE c.category_id = ? AND c.is_active = 1";
    
    $category = $db->fetchRow($category_query, [$category_id]);
    
    if (!$category) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Category not found']);
        exit;
    }
    
    // Get subcategories
    $subcategories_query = "SELECT 
                               category_id,
                               name,
                               slug,
                               description,
                               image_url,
                               icon,
                               sort_order
                            FROM categories 
                            WHERE parent_id = ? AND is_active = 1
                            ORDER BY sort_order ASC, name ASC";
    
    $subcategories = $db->fetchAll($subcategories_query, [$category_id]);
    
    // Get products in this category (with pagination support)
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 12;
    $offset = ($page - 1) * $limit;
    
    // Count total products
    $count_query = "SELECT COUNT(*) as total 
                    FROM products p 
                    WHERE p.category_id = ? AND p.is_active = 1";
    
    $total_result = $db->fetchRow($count_query, [$category_id]);
    $total_products = (int)$total_result['total'];
    
    // Get products with details
    $products_query = "SELECT 
                          p.product_id,
                          p.name,
                          p.slug,
                          p.description,
                          p.short_description,
                          p.sku,
                          p.price,
                          p.compare_price,
                          p.cost_price,
                          p.weight,
                          p.weight_unit,
                          p.stock_quantity,
                          p.min_stock_level,
                          p.image_url,
                          p.gallery_images,
                          p.is_featured,
                          p.meta_title,
                          p.meta_description,
                          p.created_at,
                          p.updated_at,
                          b.name as brand_name,
                          b.logo_url as brand_logo
                       FROM products p
                       LEFT JOIN brands b ON p.brand_id = b.brand_id
                       WHERE p.category_id = ? AND p.is_active = 1
                       ORDER BY p.is_featured DESC, p.created_at DESC
                       LIMIT ? OFFSET ?";
    
    $products = $db->fetchAll($products_query, [$category_id, $limit, $offset]);
    
    // Format products data
    $formatted_products = [];
    foreach ($products as $product) {
        $gallery_images = [];
        if (!empty($product['gallery_images'])) {
            $gallery_images = json_decode($product['gallery_images'], true) ?: [];
        }
        
        $formatted_products[] = [
            'product_id' => (int)$product['product_id'],
            'name' => $product['name'],
            'slug' => $product['slug'],
            'description' => $product['description'],
            'short_description' => $product['short_description'],
            'sku' => $product['sku'],
            'price' => (float)$product['price'],
            'compare_price' => $product['compare_price'] ? (float)$product['compare_price'] : null,
            'cost_price' => $product['cost_price'] ? (float)$product['cost_price'] : null,
            'weight' => $product['weight'] ? (float)$product['weight'] : null,
            'weight_unit' => $product['weight_unit'],
            'stock_quantity' => (int)$product['stock_quantity'],
            'min_stock_level' => (int)$product['min_stock_level'],
            'in_stock' => (int)$product['stock_quantity'] > 0,
            'image_url' => $product['image_url'],
            'gallery_images' => $gallery_images,
            'is_featured' => (bool)$product['is_featured'],
            'brand' => [
                'name' => $product['brand_name'],
                'logo_url' => $product['brand_logo']
            ],
            'meta_title' => $product['meta_title'],
            'meta_description' => $product['meta_description'],
            'created_at' => $product['created_at'],
            'updated_at' => $product['updated_at']
        ];
    }
    
    // Format subcategories
    $formatted_subcategories = [];
    foreach ($subcategories as $subcategory) {
        $formatted_subcategories[] = [
            'category_id' => (int)$subcategory['category_id'],
            'name' => $subcategory['name'],
            'slug' => $subcategory['slug'],
            'description' => $subcategory['description'],
            'image_url' => $subcategory['image_url'],
            'icon' => $subcategory['icon'],
            'sort_order' => (int)$subcategory['sort_order']
        ];
    }
    
    // Format category data
    $formatted_category = [
        'category_id' => (int)$category['category_id'],
        'parent_id' => $category['parent_id'] ? (int)$category['parent_id'] : null,
        'name' => $category['name'],
        'slug' => $category['slug'],
        'description' => $category['description'],
        'image_url' => $category['image_url'],
        'icon' => $category['icon'],
        'sort_order' => (int)$category['sort_order'],
        'meta_title' => $category['meta_title'],
        'meta_description' => $category['meta_description'],
        'is_active' => (bool)$category['is_active'],
        'is_featured' => (bool)$category['is_featured'],
        'parent_name' => $category['parent_name'],
        'created_at' => $category['created_at'],
        'updated_at' => $category['updated_at']
    ];
    
    // Calculate pagination info
    $total_pages = ceil($total_products / $limit);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'category' => $formatted_category,
            'subcategories' => $formatted_subcategories,
            'products' => $formatted_products,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_products' => $total_products,
                'per_page' => $limit,
                'has_next' => $page < $total_pages,
                'has_prev' => $page > 1
            ]
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Category detail API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching category details'
    ]);
}
?>
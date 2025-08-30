<?php
/**
 * Products API Endpoint
 * Handles product listing, search, and details
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../../config/database.php';

try {
    $db = Database::getInstance();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetProducts($db);
            break;
        case 'POST':
            handleCreateProduct($db);
            break;
        case 'PUT':
            handleUpdateProduct($db);
            break;
        case 'DELETE':
            handleDeleteProduct($db);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Products API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}

/**
 * Handle GET requests for products
 */
function handleGetProducts($db) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $search = isset($_GET['search']) ? $_GET['search'] : null;
    $featured = isset($_GET['featured']) ? (bool)$_GET['featured'] : false;
    
    $offset = ($page - 1) * $limit;
    
    // Base query
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                   AVG(pr.rating) as average_rating, COUNT(pr.review_id) as review_count
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN product_reviews pr ON p.product_id = pr.product_id AND pr.status = 'approved'
            WHERE p.status = 'active'";
    
    $params = [];
    
    // Add filters
    if ($category) {
        $sql .= " AND c.slug = ?";
        $params[] = $category;
    }
    
    if ($search) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($featured) {
        $sql .= " AND p.featured = TRUE";
    }
    
    $sql .= " GROUP BY p.product_id ORDER BY p.sort_order ASC, p.created_at DESC";
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    try {
        $stmt = $db->execute($sql, $params);
        $products = $stmt->fetchAll();
        
        // Get total count
        $countSql = "SELECT COUNT(DISTINCT p.product_id) as total
                     FROM products p
                     LEFT JOIN categories c ON p.category_id = c.category_id
                     WHERE p.status = 'active'";
        
        $countParams = [];
        if ($category) {
            $countSql .= " AND c.slug = ?";
            $countParams[] = $category;
        }
        
        if ($search) {
            $countSql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
            $searchTerm = "%$search%";
            $countParams[] = $searchTerm;
            $countParams[] = $searchTerm;
            $countParams[] = $searchTerm;
        }
        
        if ($featured) {
            $countSql .= " AND p.featured = TRUE";
        }
        
        $countStmt = $db->execute($countSql, $countParams);
        $total = $countStmt->fetch()['total'];
        
        echo json_encode([
            'success' => true,
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => (int)$total,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Get products error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch products'
        ]);
    }
}

/**
 * Handle POST requests to create product
 */
function handleCreateProduct($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    // Required fields
    $required = ['name', 'price', 'category_id'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    try {
        // Generate SKU
        $sku = generateSKU($input['name']);
        $slug = generateSlug($input['name']);
        
        $sql = "INSERT INTO products (sku, name, slug, short_description, description, price, 
                                    original_price, category_id, brand_id, stock_quantity, 
                                    unit, weight, weight_unit, featured, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $sku,
            $input['name'],
            $slug,
            $input['short_description'] ?? '',
            $input['description'] ?? '',
            $input['price'],
            $input['original_price'] ?? $input['price'],
            $input['category_id'],
            $input['brand_id'] ?? null,
            $input['stock_quantity'] ?? 0,
            $input['unit'] ?? 'piece',
            $input['weight'] ?? 1.0,
            $input['weight_unit'] ?? 'kg',
            $input['featured'] ?? false,
            'active'
        ];
        
        $stmt = $db->execute($sql, $params);
        $productId = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Product created successfully',
            'product_id' => $productId
        ]);
        
    } catch (Exception $e) {
        error_log("Create product error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create product'
        ]);
    }
}

/**
 * Handle PUT requests to update product
 */
function handleUpdateProduct($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        return;
    }
    
    try {
        // Build update query dynamically
        $fields = [];
        $params = [];
        
        $updateable = ['name', 'short_description', 'description', 'price', 'original_price', 
                      'category_id', 'brand_id', 'stock_quantity', 'unit', 'weight', 
                      'weight_unit', 'featured', 'status'];
        
        foreach ($updateable as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            return;
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $input['product_id'];
        
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE product_id = ?";
        
        $stmt = $db->execute($sql, $params);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Product updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Product not found or no changes made'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Update product error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update product'
        ]);
    }
}

/**
 * Handle DELETE requests
 */
function handleDeleteProduct($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        return;
    }
    
    try {
        $product_id = $input['product_id'];
        
        // First, get the product's category_id before deletion
        $getProductQuery = "SELECT category_id FROM products WHERE product_id = ?";
        $getProductStmt = $db->prepare($getProductQuery);
        $getProductStmt->execute([$product_id]);
        $product = $getProductStmt->fetch();
        
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        $category_id = $product['category_id'];
        
        // Soft delete - set status to inactive
        $sql = "UPDATE products SET status = 'inactive', updated_at = NOW() WHERE product_id = ?";
        $stmt = $db->execute($sql, [$product_id]);
        
        if ($stmt->rowCount() > 0) {
            // Check if the category has any remaining active products
            if ($category_id) {
                $checkCategoryQuery = "SELECT COUNT(*) as product_count FROM products WHERE category_id = ? AND status = 'active'";
                $checkCategoryStmt = $db->prepare($checkCategoryQuery);
                $checkCategoryStmt->execute([$category_id]);
                $result = $checkCategoryStmt->fetch();
                
                // If no active products remain in the category, soft delete the category
                if ($result['product_count'] == 0) {
                    $deleteCategoryQuery = "UPDATE categories SET status = 'inactive', updated_at = NOW() WHERE category_id = ?";
                    $deleteCategoryStmt = $db->prepare($deleteCategoryQuery);
                    $deleteCategoryStmt->execute([$category_id]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Product deleted successfully. Category was also deleted as it contained no active products.'
                    ]);
                    return;
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Product not found'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Delete product error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete product'
        ]);
    }
}

/**
 * Generate SKU from product name
 */
function generateSKU($name) {
    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
    if (strlen($prefix) < 3) {
        $prefix = str_pad($prefix, 3, 'A');
    }
    return $prefix . sprintf('%03d', rand(1, 999));
}

/**
 * Generate slug from product name
 */
function generateSlug($name) {
    return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
}
?>

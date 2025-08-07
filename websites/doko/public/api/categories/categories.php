<?php
/**
 * Categories API Endpoint
 * Handles category listing and management
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
require_once __DIR__ . '/../../../src/Controllers/AuthController.php';

try {
    $db = Database::getInstance();
    $auth = new AuthController();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetCategories($db);
            break;
        case 'POST':
            handleCreateCategory($db, $auth);
            break;
        case 'PUT':
            handleUpdateCategory($db, $auth);
            break;
        case 'DELETE':
            handleDeleteCategory($db, $auth);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Categories API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}

/**
 * Handle GET requests for categories
 */
function handleGetCategories($db) {
    $featured = isset($_GET['featured']) ? (bool)$_GET['featured'] : false;
    $withProducts = isset($_GET['with_products']) ? (bool)$_GET['with_products'] : false;
    
    try {
        $sql = "SELECT c.*, COUNT(p.product_id) as product_count
                FROM categories c
                LEFT JOIN products p ON c.category_id = p.category_id AND p.status = 'active'
                WHERE c.status = 'active'";
        
        if ($featured) {
            $sql .= " AND c.is_featured = TRUE";
        }
        
        $sql .= " GROUP BY c.category_id ORDER BY c.sort_order ASC, c.name ASC";
        
        $stmt = $db->execute($sql);
        $categories = $stmt->fetchAll();
        
        // If with_products is requested, get sample products for each category
        if ($withProducts) {
            foreach ($categories as &$category) {
                $productsSql = "SELECT product_id, name, slug, price, short_description,
                                      stock_quantity, featured
                               FROM products 
                               WHERE category_id = ? AND status = 'active' 
                               ORDER BY featured DESC, total_sales DESC, created_at DESC 
                               LIMIT 6";
                
                $productsStmt = $db->execute($productsSql, [$category['category_id']]);
                $category['products'] = $productsStmt->fetchAll();
            }
        }
        
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
        
    } catch (Exception $e) {
        error_log("Get categories error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch categories'
        ]);
    }
}

/**
 * Handle POST requests to create category
 */
function handleCreateCategory($db, $auth) {
    if (!$auth->hasAdminAccess()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    if (!isset($input['name']) || empty($input['name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        return;
    }
    
    try {
        // Generate slug
        $slug = generateSlug($input['name']);
        
        // Check if slug already exists
        $checkSql = "SELECT category_id FROM categories WHERE slug = ?";
        $checkStmt = $db->execute($checkSql, [$slug]);
        
        if ($checkStmt->fetch()) {
            $slug .= '-' . time(); // Make it unique
        }
        
        $sql = "INSERT INTO categories (parent_id, name, slug, description, icon, color, 
                                      sort_order, is_featured, status, meta_title, 
                                      meta_description, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, NOW())";
        
        $params = [
            $input['parent_id'] ?? null,
            $input['name'],
            $slug,
            $input['description'] ?? '',
            $input['icon'] ?? null,
            $input['color'] ?? '#007bff',
            $input['sort_order'] ?? 0,
            $input['is_featured'] ?? false,
            $input['meta_title'] ?? $input['name'],
            $input['meta_description'] ?? $input['description'] ?? ''
        ];
        
        $stmt = $db->execute($sql, $params);
        $categoryId = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Category created successfully',
            'category_id' => $categoryId
        ]);
        
    } catch (Exception $e) {
        error_log("Create category error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create category'
        ]);
    }
}

/**
 * Handle PUT requests to update category
 */
function handleUpdateCategory($db, $auth) {
    if (!$auth->hasAdminAccess()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['category_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category ID is required']);
        return;
    }
    
    try {
        // Build update query dynamically
        $fields = [];
        $params = [];
        
        $updateable = ['parent_id', 'name', 'description', 'icon', 'color', 
                      'sort_order', 'is_featured', 'status', 'meta_title', 'meta_description'];
        
        foreach ($updateable as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        
        // Update slug if name is changed
        if (isset($input['name'])) {
            $fields[] = "slug = ?";
            $params[] = generateSlug($input['name']);
        }
        
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            return;
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $input['category_id'];
        
        $sql = "UPDATE categories SET " . implode(', ', $fields) . " WHERE category_id = ?";
        
        $stmt = $db->execute($sql, $params);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Category updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Category not found or no changes made'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Update category error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update category'
        ]);
    }
}

/**
 * Handle DELETE requests
 */
function handleDeleteCategory($db, $auth) {
    if (!$auth->hasAdminAccess()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['category_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category ID is required']);
        return;
    }
    
    try {
        $categoryId = (int)$input['category_id'];
        
        // Check if category has products
        $productsSql = "SELECT COUNT(*) as count FROM products WHERE category_id = ? AND status = 'active'";
        $productsStmt = $db->execute($productsSql, [$categoryId]);
        $productCount = $productsStmt->fetch()['count'];
        
        if ($productCount > 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "Cannot delete category. It has $productCount active products."
            ]);
            return;
        }
        
        // Check if category has subcategories
        $subCategoriesSql = "SELECT COUNT(*) as count FROM categories WHERE parent_id = ? AND status = 'active'";
        $subCategoriesStmt = $db->execute($subCategoriesSql, [$categoryId]);
        $subCategoryCount = $subCategoriesStmt->fetch()['count'];
        
        if ($subCategoryCount > 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "Cannot delete category. It has $subCategoryCount subcategories."
            ]);
            return;
        }
        
        // Soft delete - set status to inactive
        $sql = "UPDATE categories SET status = 'inactive', updated_at = NOW() WHERE category_id = ?";
        $stmt = $db->execute($sql, [$categoryId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Category not found'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Delete category error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete category'
        ]);
    }
}

/**
 * Generate slug from category name
 */
function generateSlug($name) {
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}
?>

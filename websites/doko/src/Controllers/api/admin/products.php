<?php
/**
 * Product Management API - Admin Only
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../Controllers/AuthController.php';
require_once '../../../config/database.php';

try {
    // Check authentication
    $auth = new AuthController();
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required'
        ]);
        exit;
    }
    
    // Check if user is admin
    if (!$auth->isAdmin()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Admin access required'
        ]);
        exit;
    }
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetProducts($conn);
            break;
        case 'POST':
            handleCreateProduct($conn);
            break;
        case 'PUT':
            handleUpdateProduct($conn);
            break;
        case 'DELETE':
            handleDeleteProduct($conn);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Product management error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

function handleGetProducts($conn) {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    
    $offset = ($page - 1) * $limit;
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if ($search) {
        $whereConditions[] = "(p.name LIKE :search OR p.sku LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    if ($category) {
        $whereConditions[] = "p.category_id = :category";
        $params[':category'] = $category;
    }
    
    if ($status) {
        $whereConditions[] = "p.status = :status";
        $params[':status'] = $status;
    }
    
    $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $query = "SELECT p.*, c.name as category_name, b.name as brand_name,
                     pi.image_url as primary_image,
                     COUNT(DISTINCT pi2.image_id) as image_count
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id
              LEFT JOIN brands b ON p.brand_id = b.brand_id
              LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = TRUE
              LEFT JOIN product_images pi2 ON p.product_id = pi2.product_id
              {$whereClause}
              GROUP BY p.product_id
              ORDER BY p.created_at DESC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll();
    
    // Get total count
    $countQuery = "SELECT COUNT(DISTINCT p.product_id) as total FROM products p {$whereClause}";
    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'data' => $products,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => (int)$total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

function handleCreateProduct($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    // Required fields
    $required = ['name', 'price', 'category_id', 'stock_quantity'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
            return;
        }
    }
    
    // Generate SKU if not provided
    if (empty($input['sku'])) {
        $input['sku'] = 'DOKO-' . strtoupper(uniqid());
    }
    
    // Generate slug from name
    if (empty($input['slug'])) {
        $input['slug'] = generateSlug($input['name']);
    }
    
    $conn->beginTransaction();
    
    try {
        $query = "INSERT INTO products (
            sku, name, slug, short_description, description, price, original_price,
            category_id, brand_id, stock_quantity, min_stock_level, unit, weight, weight_unit,
            featured, status, nutritional_info, ingredients, allergen_info, storage_instructions,
            expiry_days, tax_rate, meta_title, meta_description
        ) VALUES (
            :sku, :name, :slug, :short_description, :description, :price, :original_price,
            :category_id, :brand_id, :stock_quantity, :min_stock_level, :unit, :weight, :weight_unit,
            :featured, :status, :nutritional_info, :ingredients, :allergen_info, :storage_instructions,
            :expiry_days, :tax_rate, :meta_title, :meta_description
        )";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':sku', $input['sku']);
        $stmt->bindParam(':name', $input['name']);
        $stmt->bindParam(':slug', $input['slug']);
        $stmt->bindParam(':short_description', $input['short_description'] ?? null);
        $stmt->bindParam(':description', $input['description'] ?? null);
        $stmt->bindParam(':price', $input['price']);
        $stmt->bindParam(':original_price', $input['original_price'] ?? null);
        $stmt->bindParam(':category_id', $input['category_id']);
        $stmt->bindParam(':brand_id', $input['brand_id'] ?? null);
        $stmt->bindParam(':stock_quantity', $input['stock_quantity']);
        $stmt->bindParam(':min_stock_level', $input['min_stock_level'] ?? 5);
        $stmt->bindParam(':unit', $input['unit'] ?? 'piece');
        $stmt->bindParam(':weight', $input['weight'] ?? null);
        $stmt->bindParam(':weight_unit', $input['weight_unit'] ?? 'g');
        $stmt->bindParam(':featured', $input['featured'] ?? false);
        $stmt->bindParam(':status', $input['status'] ?? 'active');
        $stmt->bindParam(':nutritional_info', isset($input['nutritional_info']) ? json_encode($input['nutritional_info']) : null);
        $stmt->bindParam(':ingredients', $input['ingredients'] ?? null);
        $stmt->bindParam(':allergen_info', $input['allergen_info'] ?? null);
        $stmt->bindParam(':storage_instructions', $input['storage_instructions'] ?? null);
        $stmt->bindParam(':expiry_days', $input['expiry_days'] ?? null);
        $stmt->bindParam(':tax_rate', $input['tax_rate'] ?? 0.00);
        $stmt->bindParam(':meta_title', $input['meta_title'] ?? null);
        $stmt->bindParam(':meta_description', $input['meta_description'] ?? null);
        
        $stmt->execute();
        $productId = $conn->lastInsertId();
        
        // Add images if provided
        if (isset($input['images']) && is_array($input['images'])) {
            foreach ($input['images'] as $index => $imageUrl) {
                $imageQuery = "INSERT INTO product_images (product_id, image_url, is_primary, sort_order) VALUES (:product_id, :image_url, :is_primary, :sort_order)";
                $imageStmt = $conn->prepare($imageQuery);
                $imageStmt->bindParam(':product_id', $productId);
                $imageStmt->bindParam(':image_url', $imageUrl);
                $imageStmt->bindParam(':is_primary', $index === 0);
                $imageStmt->bindParam(':sort_order', $index);
                $imageStmt->execute();
            }
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => ['product_id' => $productId]
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

function handleUpdateProduct($conn) {
    $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$productId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    // Check if product exists
    $checkQuery = "SELECT product_id FROM products WHERE product_id = :product_id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':product_id', $productId);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    
    // Build update query dynamically
    $updateFields = [];
    $params = [':product_id' => $productId];
    
    $allowedFields = [
        'name', 'slug', 'short_description', 'description', 'price', 'original_price',
        'category_id', 'brand_id', 'stock_quantity', 'min_stock_level', 'unit', 'weight',
        'weight_unit', 'featured', 'status', 'ingredients', 'allergen_info',
        'storage_instructions', 'expiry_days', 'tax_rate', 'meta_title', 'meta_description'
    ];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = :$field";
            $params[":$field"] = $input[$field];
        }
    }
    
    if (isset($input['nutritional_info'])) {
        $updateFields[] = "nutritional_info = :nutritional_info";
        $params[':nutritional_info'] = json_encode($input['nutritional_info']);
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        return;
    }
    
    $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
    
    $query = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE product_id = :product_id";
    $stmt = $conn->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update product');
    }
}

function handleDeleteProduct($conn) {
    $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$productId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        return;
    }
    
    // Check if product exists
    $checkQuery = "SELECT product_id FROM products WHERE product_id = :product_id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':product_id', $productId);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    
    // Soft delete (change status to discontinued)
    $query = "UPDATE products SET status = 'discontinued', updated_at = CURRENT_TIMESTAMP WHERE product_id = :product_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':product_id', $productId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete product');
    }
}

function generateSlug($string) {
    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}
?>

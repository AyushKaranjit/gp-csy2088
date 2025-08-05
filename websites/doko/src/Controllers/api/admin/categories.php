<?php
/**
 * Category Management API - Admin Only
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    // Check if user is admin
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
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
            handleGetCategories($conn);
            break;
        case 'POST':
            handleCreateCategory($conn);
            break;
        case 'PUT':
            handleUpdateCategory($conn);
            break;
        case 'DELETE':
            handleDeleteCategory($conn);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

function handleGetCategories($conn) {
    try {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $query = "SELECT c.*, 
                         COUNT(p.product_id) as product_count,
                         AVG(p.price) as avg_price
                  FROM categories c 
                  LEFT JOIN products p ON c.category_id = p.category_id AND p.status = 'active'";
        
        $params = [];
        
        if ($search) {
            $query .= " WHERE c.name LIKE :search OR c.description LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        $query .= " GROUP BY c.category_id 
                   ORDER BY c.sort_order ASC, c.name ASC 
                   LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM categories c";
        if ($search) {
            $countQuery .= " WHERE c.name LIKE :search OR c.description LIKE :search";
        }
        
        $countStmt = $conn->prepare($countQuery);
        if ($search) {
            $countStmt->bindValue(':search', '%' . $search . '%');
        }
        $countStmt->execute();
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo json_encode([
            'success' => true,
            'data' => $categories,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int)$totalCount,
                'pages' => ceil($totalCount / $limit)
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching categories: ' . $e->getMessage()
        ]);
    }
}

function handleCreateCategory($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['name'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Category name is required'
            ]);
            return;
        }
        
        // Check if category name already exists
        $checkQuery = "SELECT category_id FROM categories WHERE name = :name";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindValue(':name', $input['name']);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Category name already exists'
            ]);
            return;
        }
        
        // Create slug from name
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['name'])));
        
        $query = "INSERT INTO categories (name, slug, description, sort_order, is_featured, is_active, meta_title, meta_description) 
                 VALUES (:name, :slug, :description, :sort_order, :is_featured, :is_active, :meta_title, :meta_description)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':name', $input['name']);
        $stmt->bindValue(':slug', $slug);
        $stmt->bindValue(':description', $input['description'] ?? '');
        $stmt->bindValue(':sort_order', $input['sort_order'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':is_featured', isset($input['is_featured']) ? (bool)$input['is_featured'] : false, PDO::PARAM_BOOL);
        $stmt->bindValue(':is_active', isset($input['is_active']) ? (bool)$input['is_active'] : true, PDO::PARAM_BOOL);
        $stmt->bindValue(':meta_title', $input['meta_title'] ?? $input['name']);
        $stmt->bindValue(':meta_description', $input['meta_description'] ?? '');
        
        if ($stmt->execute()) {
            $categoryId = $conn->lastInsertId();
            
            // Log activity
            logActivity($_SESSION['user_id'], 'create', 'category', $categoryId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => ['category_id' => $categoryId]
            ]);
        } else {
            throw new Exception('Failed to create category');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error creating category: ' . $e->getMessage()
        ]);
    }
}

function handleUpdateCategory($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['category_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Category ID is required'
            ]);
            return;
        }
        
        $categoryId = (int)$input['category_id'];
        
        // Check if category exists
        $checkQuery = "SELECT * FROM categories WHERE category_id = :category_id";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $checkStmt->execute();
        
        $existingCategory = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$existingCategory) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Category not found'
            ]);
            return;
        }
        
        // Build update query dynamically
        $updateFields = [];
        $params = [':category_id' => $categoryId];
        
        if (isset($input['name'])) {
            $updateFields[] = "name = :name";
            $params[':name'] = $input['name'];
            
            // Update slug if name changes
            $updateFields[] = "slug = :slug";
            $params[':slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['name'])));
        }
        
        if (isset($input['description'])) {
            $updateFields[] = "description = :description";
            $params[':description'] = $input['description'];
        }
        
        if (isset($input['sort_order'])) {
            $updateFields[] = "sort_order = :sort_order";
            $params[':sort_order'] = (int)$input['sort_order'];
        }
        
        if (isset($input['is_featured'])) {
            $updateFields[] = "is_featured = :is_featured";
            $params[':is_featured'] = (bool)$input['is_featured'];
        }
        
        if (isset($input['is_active'])) {
            $updateFields[] = "is_active = :is_active";
            $params[':is_active'] = (bool)$input['is_active'];
        }
        
        if (isset($input['meta_title'])) {
            $updateFields[] = "meta_title = :meta_title";
            $params[':meta_title'] = $input['meta_title'];
        }
        
        if (isset($input['meta_description'])) {
            $updateFields[] = "meta_description = :meta_description";
            $params[':meta_description'] = $input['meta_description'];
        }
        
        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'No fields to update'
            ]);
            return;
        }
        
        $query = "UPDATE categories SET " . implode(', ', $updateFields) . " WHERE category_id = :category_id";
        
        $stmt = $conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if ($stmt->execute()) {
            // Log activity
            logActivity($_SESSION['user_id'], 'update', 'category', $categoryId, $existingCategory, $input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Category updated successfully'
            ]);
        } else {
            throw new Exception('Failed to update category');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error updating category: ' . $e->getMessage()
        ]);
    }
}

function handleDeleteCategory($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['category_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Category ID is required'
            ]);
            return;
        }
        
        $categoryId = (int)$input['category_id'];
        
        // Check if category has products
        $productCountQuery = "SELECT COUNT(*) as count FROM products WHERE category_id = :category_id AND status = 'active'";
        $productCountStmt = $conn->prepare($productCountQuery);
        $productCountStmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $productCountStmt->execute();
        
        $productCount = $productCountStmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($productCount > 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "Cannot delete category. It has {$productCount} active products. Please move or delete the products first."
            ]);
            return;
        }
        
        // Soft delete - mark as inactive
        $query = "UPDATE categories SET is_active = FALSE WHERE category_id = :category_id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Log activity
            logActivity($_SESSION['user_id'], 'delete', 'category', $categoryId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } else {
            throw new Exception('Failed to delete category');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting category: ' . $e->getMessage()
        ]);
    }
}
?>

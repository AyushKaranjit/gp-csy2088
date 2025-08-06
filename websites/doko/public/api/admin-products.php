<?php
/**
 * Admin Products API Endpoint
 * Handle product management operations for admin panel
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set proper CORS and JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/AuthController.php';

try {
    // Check admin authentication
    $auth = new AuthController();
    if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $db = Database::getInstance()->getConnection();

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
    error_log("Admin Products API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

function handleGetProducts($db) {
    try {
        // Check if getting single product
        if (isset($_GET['product_id'])) {
            getSingleProduct($db, (int)$_GET['product_id']);
            return;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';

        $offset = ($page - 1) * $limit;
        
        // Build query with filters
        $where_conditions = [];
        $params = [];
        
        if (!empty($search)) {
            $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($category > 0) {
            $where_conditions[] = "p.category_id = ?";
            $params[] = $category;
        }
        
        if ($status !== 'all') {
            if ($status === 'active') {
                $where_conditions[] = "p.status = 'active' AND p.stock_quantity > 0";
            } elseif ($status === 'inactive') {
                $where_conditions[] = "p.status = 'inactive'";
            } elseif ($status === 'out_of_stock') {
                $where_conditions[] = "p.stock_quantity <= 0";
            }
        }
        
        $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

        // Get total count
        $count_sql = "SELECT COUNT(*) as total FROM products p $where_clause";
        $count_stmt = $db->prepare($count_sql);
        $count_stmt->execute($params);
        $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get products
        $sql = "SELECT 
                    p.product_id, p.name, p.description, p.price, p.original_price,
                    p.stock_quantity, p.unit, p.status, p.featured,
                    p.created_at, p.updated_at,
                    c.name as category_name, c.category_id
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                $where_clause
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format product data
        foreach ($products as &$product) {
            $product['price'] = (float)$product['price'];
            $product['original_price'] = (float)$product['original_price'];
            $product['stock'] = (int)$product['stock_quantity']; // Map for frontend compatibility
            $product['is_active'] = ($product['status'] === 'active');
            $product['featured'] = (bool)$product['featured'];
            $product['category_id'] = (int)$product['category_id'];
            $product['discount_percentage'] = $product['original_price'] > 0 ? 
                round((($product['original_price'] - $product['price']) / $product['original_price']) * 100) : 0;
            // Add image URL (placeholder for now)
            $product['image_url'] = 'uploads/placeholder-product.jpg';
        }

        echo json_encode([
            'success' => true,
            'data' => $products,
            'pagination' => [
                'total' => (int)$total,
                'limit' => $limit,
                'offset' => $offset,
                'currentPage' => $page,
                'totalPages' => ceil($total / $limit),
                'hasMore' => $offset + $limit < $total
            ]
        ]);

    } catch (Exception $e) {
        error_log("Get products error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch products']);
    }
}

function getSingleProduct($db, $product_id) {
    try {
        $sql = "SELECT 
                    p.product_id, p.name, p.description, p.price, p.original_price,
                    p.stock_quantity as stock, p.unit, p.status as is_active, p.featured,
                    p.created_at, p.updated_at,
                    c.name as category_name, c.category_id
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                WHERE p.product_id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        // Format product data
        $product['price'] = (float)$product['price'];
        $product['original_price'] = (float)$product['original_price'];
        $product['stock'] = (int)$product['stock'];
        $product['is_active'] = ($product['is_active'] === 'active');
        $product['featured'] = (bool)$product['featured'];
        $product['category_id'] = (int)$product['category_id'];
        // Add image URL (placeholder for now)
        $product['image_url'] = 'uploads/placeholder-product.jpg';

        echo json_encode([
            'success' => true,
            'data' => [$product] // Return as array for consistency
        ]);

    } catch (Exception $e) {
        error_log("Get single product error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch product']);
    }
}

function handleCreateProduct($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['name']) || !isset($input['price'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product name and price are required']);
            return;
        }

        $sql = "INSERT INTO products (
                    name, description, price, original_price, category_id, 
                    stock_quantity, unit, featured, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $input['name'],
            $input['description'] ?? '',
            $input['price'],
            $input['original_price'] ?? $input['price'],
            $input['category_id'] ?? 1,
            $input['stock'] ?? 0,
            $input['unit'] ?? 'piece',
            isset($input['featured']) ? (bool)$input['featured'] : false,
            isset($input['is_active']) && $input['is_active'] ? 'active' : 'inactive'
        ]);

        if ($result) {
            $product_id = $db->lastInsertId();
            echo json_encode([
                'success' => true, 
                'message' => 'Product created successfully',
                'product_id' => $product_id
            ]);
        } else {
            throw new Exception('Failed to insert product');
        }

    } catch (Exception $e) {
        error_log("Create product error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create product']);
    }
}

function handleUpdateProduct($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['product_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            return;
        }

        // Build update query dynamically
        $update_fields = [];
        $params = [];
        
        $allowed_fields = ['name', 'description', 'price', 'original_price', 'category_id', 
                          'unit', 'featured'];
        
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                if ($field === 'stock') {
                    $update_fields[] = "stock_quantity = ?";
                    $params[] = $input[$field];
                } else {
                    $update_fields[] = "$field = ?";
                    $params[] = $input[$field];
                }
            }
        }
        
        // Handle special fields
        if (isset($input['stock'])) {
            $update_fields[] = "stock_quantity = ?";
            $params[] = $input['stock'];
        }
        
        if (isset($input['is_active'])) {
            $update_fields[] = "status = ?";
            $params[] = $input['is_active'] ? 'active' : 'inactive';
        }
        
        if (empty($update_fields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            return;
        }
        
        $params[] = $input['product_id'];
        
        $sql = "UPDATE products SET " . implode(', ', $update_fields) . " WHERE product_id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            throw new Exception('Failed to update product');
        }

    } catch (Exception $e) {
        error_log("Update product error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update product']);
    }
}

function handleDeleteProduct($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['product_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            return;
        }

        $sql = "DELETE FROM products WHERE product_id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$input['product_id']]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            throw new Exception('Failed to delete product');
        }

    } catch (Exception $e) {
        error_log("Delete product error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
    }
}
?>

<?php
// Suppress PHP errors from appearing in JSON response
error_reporting(0);
ini_set('display_errors', 0);

header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once '../../config/database.php';
require_once '../../template/config.php';
require_once '../../src/Controllers/AuthController.php';

// Verify admin authentication
$auth = new AuthController();
if (!$auth->isLoggedIn() || !$auth->hasAdminAccess()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Test database connection
    $testQuery = "SHOW TABLES LIKE 'orders'";
    $testResult = $db->execute($testQuery);
    if (!$testResult || $testResult->rowCount() === 0) {
        throw new Exception("Orders table not found in database");
    }
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get all orders or specific order
            if (isset($_GET['order_id'])) {
                $order_id = (int)$_GET['order_id'];
                
                // Get specific order details
                $query = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone
                         FROM orders o
                         LEFT JOIN users u ON o.user_id = u.user_id
                         WHERE o.order_id = ?";
                $stmt = $db->execute($query, [$order_id]);
                $order = $stmt->fetch();
                
                if (!$order) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Order not found']);
                    exit;
                }
                
                // Get order items
                $query = "SELECT oi.*, p.name, p.image_url
                         FROM order_items oi
                         LEFT JOIN products p ON oi.product_id = p.product_id
                         WHERE oi.order_id = ?";
                $stmt = $db->execute($query, [$order_id]);
                $items = $stmt->fetchAll();
                
                $order['items'] = $items;
                
                echo json_encode(['success' => true, 'data' => $order]);
                
            } else {
                // Get all orders with pagination
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                $offset = ($page - 1) * $limit;
                $status = isset($_GET['status']) ? $_GET['status'] : '';
                
                $where_clause = '';
                $params = [];
                
                if ($status) {
                    $where_clause = 'WHERE o.status = ?';
                    $params[] = $status;
                }
                
                $query = "SELECT o.order_id, o.total_amount, o.status, o.created_at,
                                u.first_name, u.last_name, u.email,
                                COUNT(oi.item_id) as item_count
                         FROM orders o
                         LEFT JOIN users u ON o.user_id = u.user_id
                         LEFT JOIN order_items oi ON o.order_id = oi.order_id
                         {$where_clause}
                         GROUP BY o.order_id
                         ORDER BY o.created_at DESC
                         LIMIT ? OFFSET ?";
                
                $params[] = $limit;
                $params[] = $offset;
                
                $stmt = $db->execute($query, $params);
                $orders = $stmt->fetchAll();
                
                // Get total count
                $count_query = "SELECT COUNT(DISTINCT o.order_id) as total FROM orders o {$where_clause}";
                $count_params = $status ? [$status] : [];
                $count_stmt = $db->execute($count_query, $count_params);
                $total = $count_stmt->fetch()['total'];
                
                echo json_encode([
                    'success' => true,
                    'data' => $orders,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $total,
                        'pages' => ceil($total / $limit)
                    ]
                ]);
            }
            break;
            
        case 'POST':
            // Create new order (admin use)
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['user_id']) || !isset($input['items']) || !isset($input['total_amount'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }
            
            $db->beginTransaction();
            
            try {
                // Create order
                $query = "INSERT INTO orders (user_id, total_amount, status, payment_method, shipping_address, created_at) 
                         VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $db->execute($query, [
                    $input['user_id'],
                    $input['total_amount'],
                    $input['status'] ?? 'pending',
                    $input['payment_method'] ?? 'cash',
                    $input['shipping_address'] ?? ''
                ]);
                
                $order_id = $db->getConnection()->lastInsertId();
                
                // Add order items
                foreach ($input['items'] as $item) {
                    $query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                    $db->execute($query, [
                        $order_id,
                        $item['product_id'],
                        $item['quantity'],
                        $item['price']
                    ]);
                }
                
                $db->commit();
                echo json_encode(['success' => true, 'message' => 'Order created successfully', 'order_id' => $order_id]);
                
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;
            
        case 'PUT':
            // Update order
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['order_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Order ID is required']);
                exit;
            }
            
            $updates = [];
            $params = [];
            
            if (isset($input['status'])) {
                $updates[] = 'status = ?';
                $params[] = $input['status'];
            }
            if (isset($input['payment_method'])) {
                $updates[] = 'payment_method = ?';
                $params[] = $input['payment_method'];
            }
            if (isset($input['shipping_address'])) {
                $updates[] = 'shipping_address = ?';
                $params[] = $input['shipping_address'];
            }
            if (isset($input['notes'])) {
                $updates[] = 'notes = ?';
                $params[] = $input['notes'];
            }
            
            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No fields to update']);
                exit;
            }
            
            $updates[] = 'updated_at = NOW()';
            $params[] = $input['order_id'];
            
            $query = "UPDATE orders SET " . implode(', ', $updates) . " WHERE order_id = ?";
            $stmt = $db->execute($query, $params);
            
            echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
            break;
            
        case 'DELETE':
            // Delete order (soft delete)
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['order_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Order ID is required']);
                exit;
            }
            
            $query = "UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE order_id = ?";
            $stmt = $db->execute($query, [$input['order_id']]);
            
            echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Admin Orders API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection error', 
        'debug' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>

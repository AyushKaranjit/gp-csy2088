<?php
/**
 * Orders API Endpoint
 * Handles order creation, listing, and management
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
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
            handleGetOrders($db, $auth);
            break;
        case 'POST':
            handleCreateOrder($db, $auth);
            break;
        case 'PUT':
            handleUpdateOrder($db, $auth);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Orders API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}

/**
 * Handle GET requests for orders
 */
function handleGetOrders($db, $auth) {
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        return;
    }
    
    $user = $auth->getCurrentUser();
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    
    $offset = ($page - 1) * $limit;
    
    try {
        // Base query
        $sql = "SELECT o.*, COUNT(oi.order_item_id) as item_count,
                       SUM(oi.quantity) as total_quantity
                FROM orders o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                WHERE ";
        
        $params = [];
        
        // Admin can see all orders, customers only their own
        if ($auth->isAdmin()) {
            $sql .= "1=1";
        } else {
            $sql .= "o.user_id = ?";
            $params[] = $user['user_id'];
        }
        
        // Add status filter
        if ($status) {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " GROUP BY o.order_id ORDER BY o.ordered_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->execute($sql, $params);
        $orders = $stmt->fetchAll();
        
        // Get order items for each order
        foreach ($orders as &$order) {
            $itemsSql = "SELECT oi.*, p.name as product_name, p.slug as product_slug
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.product_id
                        WHERE oi.order_id = ?";
            
            $itemsStmt = $db->execute($itemsSql, [$order['order_id']]);
            $order['items'] = $itemsStmt->fetchAll();
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM orders WHERE ";
        $countParams = [];
        
        if ($auth->isAdmin()) {
            $countSql .= "1=1";
        } else {
            $countSql .= "user_id = ?";
            $countParams[] = $user['user_id'];
        }
        
        if ($status) {
            $countSql .= " AND status = ?";
            $countParams[] = $status;
        }
        
        $countStmt = $db->execute($countSql, $countParams);
        $total = $countStmt->fetch()['total'];
        
        echo json_encode([
            'success' => true,
            'orders' => $orders,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => (int)$total,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Get orders error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch orders'
        ]);
    }
}

/**
 * Handle POST requests to create order
 */
function handleCreateOrder($db, $auth) {
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    // Required fields
    $required = ['shipping_address', 'payment_method'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    try {
        $user = $auth->getCurrentUser();
        $userId = $user['user_id'];
        
        // Start transaction
        $db->beginTransaction();
        
        // Get cart items
        $cartSql = "SELECT c.product_id, c.variant_id, c.quantity, c.price,
                          p.name, p.stock_quantity, p.status
                   FROM cart c
                   JOIN products p ON c.product_id = p.product_id
                   WHERE c.user_id = ? AND p.status = 'active'";
        
        $cartStmt = $db->execute($cartSql, [$userId]);
        $cartItems = $cartStmt->fetchAll();
        
        if (empty($cartItems)) {
            $db->rollback();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            return;
        }
        
        // Verify stock availability
        foreach ($cartItems as $item) {
            if ($item['quantity'] > $item['stock_quantity']) {
                $db->rollback();
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "Insufficient stock for {$item['name']}"
                ]);
                return;
            }
        }
        
        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['quantity'] * $item['price'];
        }
        
        $taxRate = 0.13; // 13% tax
        $taxAmount = $subtotal * $taxRate;
        $shippingFee = $subtotal >= 1000 ? 0 : 100; // Free shipping over Rs. 1000
        $discountAmount = $input['discount_amount'] ?? 0;
        $totalAmount = $subtotal + $taxAmount + $shippingFee - $discountAmount;
        
        // Generate order number
        $orderNumber = 'DOKO' . date('Ymd') . sprintf('%04d', rand(1, 9999));
        
        // Create order
        $orderSql = "INSERT INTO orders (order_number, user_id, status, payment_status, 
                                       subtotal, tax_amount, shipping_fee, discount_amount, total_amount,
                                       shipping_address, billing_address, payment_method,
                                       delivery_date, delivery_time_slot, delivery_instructions,
                                       notes, ordered_at, created_at)
                    VALUES (?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $orderParams = [
            $orderNumber,
            $userId,
            $subtotal,
            $taxAmount,
            $shippingFee,
            $discountAmount,
            $totalAmount,
            json_encode($input['shipping_address']),
            json_encode($input['billing_address'] ?? $input['shipping_address']),
            $input['payment_method'],
            $input['delivery_date'] ?? null,
            $input['delivery_time_slot'] ?? null,
            $input['delivery_instructions'] ?? null,
            $input['notes'] ?? null
        ];
        
        $orderStmt = $db->execute($orderSql, $orderParams);
        $orderId = $db->lastInsertId();
        
        // Create order items
        foreach ($cartItems as $item) {
            $orderItemSql = "INSERT INTO order_items (order_id, product_id, variant_id, 
                                                    product_name, product_sku, quantity, 
                                                    unit_price, total_price, created_at)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $orderItemParams = [
                $orderId,
                $item['product_id'],
                $item['variant_id'],
                $item['name'],
                'SKU' . $item['product_id'], // Simplified SKU
                $item['quantity'],
                $item['price'],
                $item['quantity'] * $item['price']
            ];
            
            $db->execute($orderItemSql, $orderItemParams);
            
            // Update product stock
            $updateStockSql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
            $db->execute($updateStockSql, [$item['quantity'], $item['product_id']]);
        }
        
        // Clear cart
        $clearCartSql = "DELETE FROM cart WHERE user_id = ?";
        $db->execute($clearCartSql, [$userId]);
        
        // Commit transaction
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order created successfully',
            'order' => [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'status' => 'pending'
            ]
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        error_log("Create order error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create order'
        ]);
    }
}

/**
 * Handle PUT requests to update order
 */
function handleUpdateOrder($db, $auth) {
    if (!$auth->hasAdminAccess()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['order_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        return;
    }
    
    try {
        // Get current order
        $orderSql = "SELECT * FROM orders WHERE order_id = ?";
        $orderStmt = $db->execute($orderSql, [$input['order_id']]);
        $order = $orderStmt->fetch();
        
        if (!$order) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        // Build update query
        $fields = [];
        $params = [];
        
        $updateable = ['status', 'payment_status', 'tracking_number', 'admin_notes', 
                      'confirmed_at', 'shipped_at', 'delivered_at'];
        
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
        
        // Auto-set timestamps based on status
        if (isset($input['status'])) {
            switch ($input['status']) {
                case 'confirmed':
                    if (!$order['confirmed_at']) {
                        $fields[] = "confirmed_at = NOW()";
                    }
                    break;
                case 'shipped':
                    if (!$order['shipped_at']) {
                        $fields[] = "shipped_at = NOW()";
                    }
                    break;
                case 'delivered':
                    if (!$order['delivered_at']) {
                        $fields[] = "delivered_at = NOW()";
                    }
                    break;
            }
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $input['order_id'];
        
        $updateSql = "UPDATE orders SET " . implode(', ', $fields) . " WHERE order_id = ?";
        $stmt = $db->execute($updateSql, $params);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Order updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No changes made'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Update order error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update order'
        ]);
    }
}
?>

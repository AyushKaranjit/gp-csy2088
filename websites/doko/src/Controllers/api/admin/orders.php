<?php
/**
 * Order Management API - Admin Only
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT');
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
            handleGetOrders($conn);
            break;
        case 'PUT':
            handleUpdateOrder($conn);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Order management error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

function handleGetOrders($conn) {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : null;
    $dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : null;
    
    $offset = ($page - 1) * $limit;
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if ($status) {
        $whereConditions[] = "o.status = :status";
        $params[':status'] = $status;
    }
    
    if ($search) {
        $whereConditions[] = "(o.order_number LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    if ($dateFrom) {
        $whereConditions[] = "DATE(o.ordered_at) >= :date_from";
        $params[':date_from'] = $dateFrom;
    }
    
    if ($dateTo) {
        $whereConditions[] = "DATE(o.ordered_at) <= :date_to";
        $params[':date_to'] = $dateTo;
    }
    
    $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $query = "SELECT o.*, 
                     u.first_name, u.last_name, u.email, u.phone,
                     COUNT(oi.order_item_id) as item_count
              FROM orders o
              JOIN users u ON o.user_id = u.user_id
              LEFT JOIN order_items oi ON o.order_id = oi.order_id
              {$whereClause}
              GROUP BY o.order_id
              ORDER BY o.ordered_at DESC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $orders = $stmt->fetchAll();
    
    // Get order items for each order
    foreach ($orders as &$order) {
        $itemsQuery = "SELECT oi.*, p.name as product_name, p.image_url 
                       FROM order_items oi
                       LEFT JOIN products p ON oi.product_id = p.product_id
                       WHERE oi.order_id = :order_id";
        $itemsStmt = $conn->prepare($itemsQuery);
        $itemsStmt->bindParam(':order_id', $order['order_id']);
        $itemsStmt->execute();
        $order['items'] = $itemsStmt->fetchAll();
        
        // Parse JSON fields
        $order['shipping_address'] = json_decode($order['shipping_address'], true);
        $order['billing_address'] = json_decode($order['billing_address'], true);
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(DISTINCT o.order_id) as total 
                   FROM orders o 
                   JOIN users u ON o.user_id = u.user_id 
                   {$whereClause}";
    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    
    // Get order statistics
    $statsQuery = "SELECT 
                       COUNT(*) as total_orders,
                       SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                       SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
                       SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                       SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                       SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                       SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                       SUM(total_amount) as total_revenue,
                       AVG(total_amount) as average_order_value
                   FROM orders 
                   WHERE DATE(ordered_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    $statsStmt = $conn->prepare($statsQuery);
    $statsStmt->execute();
    $stats = $statsStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'data' => $orders,
        'statistics' => $stats,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => (int)$total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

function handleUpdateOrder($conn) {
    $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$orderId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    // Check if order exists
    $checkQuery = "SELECT order_id, status FROM orders WHERE order_id = :order_id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':order_id', $orderId);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        return;
    }
    
    $currentOrder = $checkStmt->fetch();
    $currentStatus = $currentOrder['status'];
    
    // Build update query
    $updateFields = [];
    $params = [':order_id' => $orderId];
    
    $allowedFields = ['status', 'payment_status', 'tracking_number', 'delivery_date', 'delivery_time_slot', 'admin_notes'];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = :$field";
            $params[":$field"] = $input[$field];
        }
    }
    
    // Handle status-specific timestamp updates
    if (isset($input['status']) && $input['status'] !== $currentStatus) {
        $newStatus = $input['status'];
        
        switch ($newStatus) {
            case 'confirmed':
                $updateFields[] = "confirmed_at = CURRENT_TIMESTAMP";
                break;
            case 'shipped':
                $updateFields[] = "shipped_at = CURRENT_TIMESTAMP";
                break;
            case 'delivered':
                $updateFields[] = "delivered_at = CURRENT_TIMESTAMP";
                // Update product sales count
                updateProductSales($conn, $orderId);
                break;
        }
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        return;
    }
    
    $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
    
    $query = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE order_id = :order_id";
    $stmt = $conn->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    if ($stmt->execute()) {
        // Send notification email if status changed
        if (isset($input['status']) && $input['status'] !== $currentStatus) {
            sendOrderStatusNotification($orderId, $input['status']);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Order updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update order');
    }
}

function updateProductSales($conn, $orderId) {
    $query = "UPDATE products p 
              SET total_sales = total_sales + (
                  SELECT oi.quantity 
                  FROM order_items oi 
                  WHERE oi.order_id = :order_id AND oi.product_id = p.product_id
              )
              WHERE p.product_id IN (
                  SELECT DISTINCT product_id 
                  FROM order_items 
                  WHERE order_id = :order_id
              )";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':order_id', $orderId);
    $stmt->execute();
}

function sendOrderStatusNotification($orderId, $newStatus) {
    // This would integrate with your email service
    // For now, just log the notification
    error_log("Order #$orderId status changed to: $newStatus");
    
    // You can implement email sending here using PHPMailer or similar
    // Example:
    /*
    $emailService = new EmailService();
    $emailService->sendOrderStatusUpdate($orderId, $newStatus);
    */
}
?>

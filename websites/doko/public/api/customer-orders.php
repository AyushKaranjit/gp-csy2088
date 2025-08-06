<?php
/**
 * Customer Orders API
 * Handle order placement and order history for customers
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../template/config.php';
require_once '../../src/Controllers/AuthController.php';

try {
    $auth = new AuthController();
    
    // Verify user is logged in
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Please login to access orders']);
        exit;
    }
    
    $currentUser = $auth->getCurrentUser();
    $userId = $currentUser['user_id'];
    
    require_once '../../config/database.php';
    $database = Database::getInstance();
    $pdo = $database->getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get user's order history
        $stmt = $pdo->prepare('
            SELECT 
                o.*,
                COUNT(oi.item_id) as total_items,
                SUM(oi.quantity) as total_quantity
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.order_id
            ORDER BY o.created_at DESC
        ');
        
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format orders for display
        foreach ($orders as &$order) {
            $order['formatted_date'] = date('M j, Y g:i A', strtotime($order['created_at']));
            $order['status_color'] = match($order['status']) {
                'pending' => 'warning',
                'processing' => 'info', 
                'shipped' => 'primary',
                'delivered' => 'success',
                'cancelled' => 'danger',
                default => 'secondary'
            };
        }
        
        echo json_encode([
            'success' => true,
            'data' => $orders
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Place new order
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        // Validate required fields
        $requiredFields = ['delivery_address', 'phone', 'cart_items'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
                exit;
            }
        }
        
        $cartItems = $input['cart_items'];
        $deliveryAddress = trim($input['delivery_address']);
        $phone = trim($input['phone']);
        $paymentMethod = $input['payment_method'] ?? 'cash_on_delivery';
        $specialInstructions = $input['special_instructions'] ?? '';
        
        if (empty($cartItems) || !is_array($cartItems)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            exit;
        }
        
        $pdo->beginTransaction();
        
        try {
            // Calculate totals
            $subtotal = 0;
            $validItems = [];
            
            // Verify products and calculate totals
            foreach ($cartItems as $item) {
                $stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = ? AND is_active = 1');
                $stmt->execute([$item['product_id']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$product) {
                    throw new Exception("Product not found: " . $item['product_id']);
                }
                
                if ($product['stock'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for: " . $product['name']);
                }
                
                $itemTotal = $product['price'] * $item['quantity'];
                $subtotal += $itemTotal;
                
                $validItems[] = [
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => $item['quantity'],
                    'total' => $itemTotal
                ];
            }
            
            // Calculate delivery charge
            $deliveryCharge = $subtotal >= 1000 ? 0 : 50;
            $totalAmount = $subtotal + $deliveryCharge;
            
            // Generate order ID
            $orderNumber = 'DOKO' . date('Ymd') . strtoupper(substr(uniqid(), -6));
            
            // Insert order
            $stmt = $pdo->prepare('
                INSERT INTO orders (
                    order_number, user_id, total_amount, subtotal, delivery_charge,
                    delivery_address, phone, payment_method, special_instructions,
                    status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ');
            
            $stmt->execute([
                $orderNumber, $userId, $totalAmount, $subtotal, $deliveryCharge,
                $deliveryAddress, $phone, $paymentMethod, $specialInstructions,
                'pending'
            ]);
            
            $orderId = $pdo->lastInsertId();
            
            // Insert order items
            $stmt = $pdo->prepare('
                INSERT INTO order_items (order_id, product_id, quantity, price, total)
                VALUES (?, ?, ?, ?, ?)
            ');
            
            foreach ($validItems as $item) {
                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                    $item['total']
                ]);
                
                // Update product stock
                $updateStmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE product_id = ?');
                $updateStmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Order placed successfully!',
                'data' => [
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                    'total_amount' => $totalAmount,
                    'estimated_delivery' => date('Y-m-d', strtotime('+2 days'))
                ]
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } else {
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
?>

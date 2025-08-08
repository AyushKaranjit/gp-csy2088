<?php
// Refactored customer orders endpoint (history + placement) using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET','POST'], true)) { ApiResponse::error('Method not allowed', 405); }

try {
    $auth = auth_controller();
    if (!$auth->isLoggedIn()) { ApiResponse::error('Please login to access orders', 401); }
    $userId = $auth->getCurrentUser()['user_id'];
    $db = db();
    $pdo = $db->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare("SELECT o.*, COUNT(oi.item_id) as total_items, SUM(oi.quantity) as total_quantity FROM orders o LEFT JOIN order_items oi ON o.order_id = oi.order_id WHERE o.user_id = ? GROUP BY o.order_id ORDER BY o.created_at DESC");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($orders as &$order) {
            $order['formatted_date'] = date('M j, Y g:i A', strtotime($order['created_at']));
            $order['status_color'] = match($order['status']) {
                'pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger', default => 'secondary'
            };
        }
        ApiResponse::success(['orders' => $orders]);
    } else { // POST place order
        $input = json_input();
        if (!$input) { $input = $_POST; }
        foreach (['delivery_address','phone','cart_items'] as $f) { if (empty($input[$f])) { ApiResponse::error("Missing required field: $f", 400); } }
        $cartItems = $input['cart_items'];
        if (!is_array($cartItems) || !$cartItems) { ApiResponse::error('Cart is empty', 400); }
        $deliveryAddress = trim($input['delivery_address']);
        $phone = trim($input['phone']);
        $paymentMethod = $input['payment_method'] ?? 'cash_on_delivery';
        $specialInstructions = $input['special_instructions'] ?? '';
        $pdo->beginTransaction();
        try {
            $subtotal = 0; $validItems = [];
            foreach ($cartItems as $item) {
                $stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = ? AND is_active = 1');
                $stmt->execute([$item['product_id']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$product) { throw new Exception('Product not found: '.$item['product_id']); }
                if ($product['stock'] < $item['quantity']) { throw new Exception('Insufficient stock for: '.$product['name']); }
                $itemTotal = $product['price'] * $item['quantity'];
                $subtotal += $itemTotal;
                $validItems[] = [ 'product_id'=>$product['product_id'],'price'=>$product['price'],'quantity'=>$item['quantity'],'total'=>$itemTotal ];
            }
            $deliveryCharge = $subtotal >= 1000 ? 0 : 50;
            $totalAmount = $subtotal + $deliveryCharge;
            $orderNumber = 'DOKO'.date('Ymd').strtoupper(substr(uniqid(), -6));
            $stmt = $pdo->prepare("INSERT INTO orders (order_number, user_id, total_amount, subtotal, delivery_charge, delivery_address, phone, payment_method, special_instructions, status, created_at) VALUES (?,?,?,?,?,?,?,?,?,? ,NOW())");
            $stmt->execute([$orderNumber,$userId,$totalAmount,$subtotal,$deliveryCharge,$deliveryAddress,$phone,$paymentMethod,$specialInstructions,'pending']);
            $orderId = $pdo->lastInsertId();
            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price, total) VALUES (?,?,?,?,?)');
            foreach ($validItems as $vi) {
                $itemStmt->execute([$orderId,$vi['product_id'],$vi['quantity'],$vi['price'],$vi['total']]);
                $pdo->prepare('UPDATE products SET stock = stock - ? WHERE product_id = ?')->execute([$vi['quantity'],$vi['product_id']]);
            }
            $pdo->commit();
            ApiResponse::success([
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'estimated_delivery' => date('Y-m-d', strtotime('+2 days'))
            ], 201);
        } catch (Throwable $ie) { $pdo->rollBack(); throw $ie; }
    }
} catch (Throwable $e) {
    error_log('customer-orders error: '.$e->getMessage());
    ApiResponse::error('Server error: '.$e->getMessage(), 500);
}
        

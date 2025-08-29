<?php
/**
 * Order Placement API Endpoint
 * Handles creating new orders from checkout
 */
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', 405, ['allowed' => 'POST']);
    exit;
}

try {
    $auth = auth_controller();
    if (!$auth->isLoggedIn()) {
        ApiResponse::error('Authentication required', 401);
        exit;
    }

    $user = $auth->getCurrentUser();
    if (!$user) {
        ApiResponse::error('User not found', 404);
        exit;
    }

    $db = db();
    $payload = json_input();

    // Validate required fields
    $required = [
        'customer' => ['first_name', 'last_name', 'email', 'phone'],
        'delivery' => ['address', 'city', 'area', 'delivery_date', 'delivery_time'],
        'payment_method',
        'items',
        'total'
    ];

    // Check customer information
    if (empty($payload['customer'])) {
        ApiResponse::error('Customer information is required', 400);
        exit;
    }

    foreach ($required['customer'] as $field) {
        if (empty($payload['customer'][$field])) {
            ApiResponse::error("Customer $field is required", 400);
            exit;
        }
    }

    // Check delivery information
    if (empty($payload['delivery'])) {
        ApiResponse::error('Delivery information is required', 400);
        exit;
    }

    foreach ($required['delivery'] as $field) {
        if (empty($payload['delivery'][$field])) {
            ApiResponse::error("Delivery $field is required", 400);
            exit;
        }
    }

    // Check payment method
    if (empty($payload['payment_method'])) {
        ApiResponse::error('Payment method is required', 400);
        exit;
    }

    // Check items
    if (empty($payload['items']) || !is_array($payload['items'])) {
        ApiResponse::error('Order items are required', 400);
        exit;
    }

    // Validate payment method mapping
    $paymentMethodMap = [
        'cod' => 'cash_on_delivery',
        'esewa' => 'esewa',
        'khalti' => 'khalti'
    ];

    $paymentMethod = $payload['payment_method'];
    if (isset($paymentMethodMap[$paymentMethod])) {
        $paymentMethod = $paymentMethodMap[$paymentMethod];
    }

    // Calculate order totals
    $subtotal = 0;
    $orderItems = [];

    foreach ($payload['items'] as $item) {
        if (empty($item['id']) || empty($item['quantity']) || !isset($item['price'])) {
            ApiResponse::error('Invalid item data', 400);
            exit;
        }

    // Get product details from database
        $product = $db->execute(
            "SELECT product_id, name, price, stock_quantity, sku FROM products WHERE product_id = ?",
            [$item['id']]
        )->fetch();

        if (!$product) {
            ApiResponse::error("Product not found: {$item['id']}", 404);
            exit;
        }

        if ($product['stock_quantity'] < $item['quantity']) {
            ApiResponse::error("Insufficient stock for product: {$product['name']}", 400);
            exit;
        }

        $lineTotal = $product['price'] * $item['quantity'];
        $subtotal += $lineTotal;

        $orderItems[] = [
            'product_id' => $product['product_id'],
            'name' => $product['name'],
            'sku' => $product['sku'] ?? 'SKU' . $product['product_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $product['price'],
            'total_price' => $lineTotal
        ];
    }

    // Calculate delivery charge
    $deliveryCharge = $subtotal >= 1000 ? 0 : 50;
    $tax = 0; // No tax for now
    $total = $subtotal + $deliveryCharge + $tax;

    // Create shipping address JSON
    $shippingAddress = [
        'address' => $payload['delivery']['address'],
        'city' => $payload['delivery']['city'],
        'area' => $payload['delivery']['area'],
        'landmark' => $payload['delivery']['landmark'] ?? '',
        'delivery_notes' => $payload['delivery']['delivery_notes'] ?? ''
    ];

    // Generate order number
    $orderNumber = 'DOKO' . date('YmdHis') . substr(uniqid('', true), -5);

    // Insert order
    $stmt = $db->prepare("
        INSERT INTO orders (
            order_number, user_id, status, payment_status, subtotal, tax_amount,
            shipping_fee, discount_amount, total_amount, shipping_address,
            billing_address, payment_method, delivery_date, delivery_time_slot,
            delivery_instructions, ordered_at, created_at
        ) VALUES (?, ?, 'pending', 'pending', ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");

    $stmt->execute([
        $orderNumber,
        $user['user_id'],
        $subtotal,
        $tax,
        $deliveryCharge,
        $total,
        json_encode($shippingAddress),
        json_encode($shippingAddress), // Same as shipping for now
        $paymentMethod,
        $payload['delivery']['delivery_date'],
        $payload['delivery']['delivery_time'],
        $payload['delivery']['delivery_notes'] ?? ''
    ]);

    $orderId = $db->lastInsertId();

    // Insert order items and update stock
    $itemStmt = $db->prepare("
        INSERT INTO order_items (
            order_id, product_id, product_name, product_sku,
            quantity, unit_price, total_price, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stockStmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");

    foreach ($orderItems as $item) {
        $itemStmt->execute([
            $orderId,
            $item['product_id'],
            $item['name'],
            $item['sku'],
            $item['quantity'],
            $item['unit_price'],
            $item['total_price']
        ]);

        $stockStmt->execute([$item['quantity'], $item['product_id']]);
    }

    // Clear user's cart
    $db->execute("DELETE FROM cart WHERE user_id = ?", [$user['user_id']]);

    // Store order data in session for confirmation page
    $_SESSION['order_data'] = [
        'order_id' => $orderId,
        'order_number' => $orderNumber,
        'customer' => $payload['customer'],
        'delivery' => $payload['delivery'],
        'payment_method' => $paymentMethod,
        'items' => $orderItems,
        'subtotal' => $subtotal,
        'delivery_charge' => $deliveryCharge,
        'tax' => $tax,
        'total' => $total,
        'ordered_at' => date('Y-m-d H:i:s')
    ];

    ApiResponse::success([
        'message' => 'Order placed successfully',
        'order_id' => (int)$orderId,
        'order_number' => $orderNumber,
        'total_amount' => (float)$total,
        'redirect' => 'order-confirmation.php'
    ], 201);

} catch (Throwable $e) {
    error_log('Order placement error: ' . $e->getMessage());
    ApiResponse::error('Failed to place order. Please try again.', 500);
}
?>

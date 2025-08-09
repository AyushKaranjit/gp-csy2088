<?php
// Order detail endpoint for authenticated customer viewing their own order
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { ApiResponse::error('Method not allowed', 405, ['allowed'=>'GET']); }

try {
    $auth = auth_controller();
    if (!$auth->isLoggedIn()) { ApiResponse::error('Authentication required', 401); }

    $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
    if ($orderId <= 0) { ApiResponse::error('Invalid order_id', 400); }

    $db = db();
    $pdo = $db->getConnection();

    $sql = "SELECT o.order_id, o.order_number, o.user_id, o.status, o.payment_status, o.payment_method, 
                   o.subtotal, o.tax_amount, o.shipping_fee, o.discount_amount, o.total_amount,
                   o.shipping_address, o.billing_address, o.delivery_date, o.delivery_time_slot, o.delivery_instructions,
                   o.ordered_at, o.created_at, o.updated_at
            FROM orders o WHERE o.order_id = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) { ApiResponse::error('Order not found', 404); }

    // Ownership check
    $currentUser = $auth->getCurrentUser();
    if ((int)$currentUser['user_id'] !== (int)$order['user_id']) { ApiResponse::error('Forbidden', 403); }

    // Decode addresses
    $shippingAddress = json_decode($order['shipping_address'] ?? 'null', true) ?: [];
    $billingAddress = json_decode($order['billing_address'] ?? 'null', true) ?: [];

    // Fetch items
    $itemsStmt = $pdo->prepare("SELECT oi.order_item_id, oi.product_id, oi.product_name, oi.product_sku, oi.quantity, oi.unit_price, oi.total_price, oi.product_snapshot
                                 FROM order_items oi WHERE oi.order_id = ? ORDER BY oi.order_item_id ASC");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Attempt to enrich with product image (best effort)
    $imgStmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, image_id ASC LIMIT 1");
    foreach ($items as &$it) {
        $snapshot = json_decode($it['product_snapshot'] ?? 'null', true) ?: [];
        $it['name'] = $it['product_name'];
        $it['price'] = (float)$it['unit_price'];
        $it['line_total'] = (float)$it['total_price'];
        $it['image'] = $snapshot['image'] ?? null;
        if (!$it['image']) {
            $imgStmt->execute([$it['product_id']]);
            $imgRow = $imgStmt->fetch(PDO::FETCH_ASSOC);
            if ($imgRow && !empty($imgRow['image_url'])) {
                $it['image'] = $imgRow['image_url'];
            }
        }
        if (!$it['image']) { $it['image'] = '/images/default-product.jpg'; }
        unset($it['product_snapshot']);
    }

    $response = [
        'order_id' => (int)$order['order_id'],
        'order_number' => $order['order_number'],
        'status' => $order['status'],
        'payment_status' => $order['payment_status'],
        'payment_method' => $order['payment_method'],
        'totals' => [
            'subtotal' => (float)$order['subtotal'],
            'tax' => (float)$order['tax_amount'],
            'shipping' => (float)$order['shipping_fee'],
            'discount' => (float)$order['discount_amount'],
            'total' => (float)$order['total_amount']
        ],
        'shipping_address' => $shippingAddress,
        'billing_address' => $billingAddress,
        'delivery' => [
            'date' => $order['delivery_date'],
            'time_slot' => $order['delivery_time_slot'],
            'instructions' => $order['delivery_instructions']
        ],
        'ordered_at' => $order['ordered_at'] ?: $order['created_at'],
        'items' => $items
    ];

    ApiResponse::success(['order' => $response]);
} catch (Throwable $e) {
    error_log('order-detail error: ' . $e->getMessage());
    ApiResponse::error('Server error', 500);
}

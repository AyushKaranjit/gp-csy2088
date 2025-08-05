<?php
/**
 * Cart Get API
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../../../config/database.php';

try {
    session_start();
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $session_id = session_id();
    
    // Get cart items with product details
    $query = "SELECT c.cart_id, c.product_id, c.variant_id, c.quantity, c.price,
                     p.name as product_name, p.slug, p.price as current_price, 
                     p.original_price, p.stock_quantity, p.unit, p.weight, p.weight_unit,
                     cat.name as category_name,
                     pi.image_url,
                     pv.variant_name, pv.price as variant_price, pv.stock_quantity as variant_stock
              FROM cart c
              JOIN products p ON c.product_id = p.product_id
              LEFT JOIN categories cat ON p.category_id = cat.category_id
              LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = TRUE
              LEFT JOIN product_variants pv ON c.variant_id = pv.variant_id
              WHERE (c.user_id = :user_id OR c.session_id = :session_id)
              AND p.status = 'active'
              ORDER BY c.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':session_id', $session_id);
    $stmt->execute();
    
    $cartItems = $stmt->fetchAll();
    
    $items = [];
    $subtotal = 0;
    $totalItems = 0;
    
    foreach ($cartItems as $item) {
        $itemPrice = $item['variant_id'] ? $item['variant_price'] : $item['current_price'];
        $itemStock = $item['variant_id'] ? $item['variant_stock'] : $item['stock_quantity'];
        $itemTotal = $itemPrice * $item['quantity'];
        
        $items[] = [
            'cart_id' => (int)$item['cart_id'],
            'product_id' => (int)$item['product_id'],
            'variant_id' => $item['variant_id'] ? (int)$item['variant_id'] : null,
            'name' => $item['product_name'],
            'slug' => $item['slug'],
            'variant_name' => $item['variant_name'],
            'price' => (float)$itemPrice,
            'original_price' => $item['original_price'] ? (float)$item['original_price'] : null,
            'quantity' => (int)$item['quantity'],
            'unit' => $item['unit'],
            'weight' => $item['weight'] ? (float)$item['weight'] : null,
            'weight_unit' => $item['weight_unit'],
            'category_name' => $item['category_name'],
            'image_url' => $item['image_url'],
            'stock_quantity' => (int)$itemStock,
            'total_price' => (float)$itemTotal,
            'in_stock' => $itemStock > 0,
            'max_quantity' => min((int)$itemStock, 99)
        ];
        
        $subtotal += $itemTotal;
        $totalItems += $item['quantity'];
    }
    
    // Get delivery fee from settings
    $settingsQuery = "SELECT setting_value FROM system_settings WHERE setting_key = 'delivery_fee'";
    $settingsStmt = $conn->prepare($settingsQuery);
    $settingsStmt->execute();
    $deliveryFee = $settingsStmt->fetchColumn() ?: 100.00;
    
    // Get free shipping threshold
    $freeShippingQuery = "SELECT setting_value FROM system_settings WHERE setting_key = 'free_shipping_threshold'";
    $freeShippingStmt = $conn->prepare($freeShippingQuery);
    $freeShippingStmt->execute();
    $freeShippingThreshold = $freeShippingStmt->fetchColumn() ?: 2000.00;
    
    $shippingFee = $subtotal >= $freeShippingThreshold ? 0 : $deliveryFee;
    $total = $subtotal + $shippingFee;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'items' => $items,
            'summary' => [
                'subtotal' => (float)$subtotal,
                'shipping_fee' => (float)$shippingFee,
                'total' => (float)$total,
                'total_items' => (int)$totalItems,
                'item_count' => count($items),
                'free_shipping_threshold' => (float)$freeShippingThreshold,
                'free_shipping_eligible' => $subtotal >= $freeShippingThreshold
            ]
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Cart get error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching cart data'
    ]);
}
?>

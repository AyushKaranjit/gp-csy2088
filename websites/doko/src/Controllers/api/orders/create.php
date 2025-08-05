<?php
/**
 * Create Order API
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../../../config/database.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }
    
    // Required fields
    $required_fields = ['shipping_address', 'payment_method'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
            ]);
            exit;
        }
    }
    
    session_start();
    
    if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required'
        ]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Get cart items
        $cartQuery = "SELECT c.cart_id, c.product_id, c.variant_id, c.quantity, c.price,
                             p.name, p.sku, p.stock_quantity, p.unit, p.weight, p.weight_unit,
                             pv.variant_name, pv.stock_quantity as variant_stock
                      FROM cart c
                      JOIN products p ON c.product_id = p.product_id
                      LEFT JOIN product_variants pv ON c.variant_id = pv.variant_id
                      WHERE c.user_id = :user_id
                      AND p.status = 'active'";
        
        $cartStmt = $conn->prepare($cartQuery);
        $cartStmt->bindParam(':user_id', $user_id);
        $cartStmt->execute();
        $cartItems = $cartStmt->fetchAll();
        
        if (empty($cartItems)) {
            throw new Exception('Cart is empty');
        }
        
        // Validate stock and calculate totals
        $subtotal = 0;
        $orderItems = [];
        
        foreach ($cartItems as $item) {
            $availableStock = $item['variant_id'] ? $item['variant_stock'] : $item['stock_quantity'];
            
            if ($availableStock < $item['quantity']) {
                throw new Exception("Insufficient stock for {$item['name']}. Only {$availableStock} available.");
            }
            
            $itemTotal = $item['price'] * $item['quantity'];
            $subtotal += $itemTotal;
            
            $orderItems[] = [
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'product_name' => $item['name'],
                'product_sku' => $item['sku'],
                'variant_name' => $item['variant_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total_price' => $itemTotal,
                'unit' => $item['unit'],
                'weight' => $item['weight'],
                'weight_unit' => $item['weight_unit']
            ];
        }
        
        // Get system settings
        $settingsQuery = "SELECT setting_key, setting_value FROM system_settings 
                          WHERE setting_key IN ('tax_rate', 'delivery_fee', 'free_shipping_threshold')";
        $settingsStmt = $conn->prepare($settingsQuery);
        $settingsStmt->execute();
        $settings = [];
        while ($setting = $settingsStmt->fetch()) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $taxRate = isset($settings['tax_rate']) ? (float)$settings['tax_rate'] : 13.0;
        $deliveryFee = isset($settings['delivery_fee']) ? (float)$settings['delivery_fee'] : 100.0;
        $freeShippingThreshold = isset($settings['free_shipping_threshold']) ? (float)$settings['free_shipping_threshold'] : 2000.0;
        
        // Calculate order totals
        $taxAmount = ($subtotal * $taxRate) / 100;
        $shippingFee = $subtotal >= $freeShippingThreshold ? 0 : $deliveryFee;
        $discountAmount = 0; // TODO: Apply coupon discounts
        $totalAmount = $subtotal + $taxAmount + $shippingFee - $discountAmount;
        
        // Generate order number
        $orderNumber = 'DOKO' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Prepare order data
        $shippingAddress = json_encode($input['shipping_address']);
        $billingAddress = isset($input['billing_address']) ? json_encode($input['billing_address']) : $shippingAddress;
        $paymentMethod = $input['payment_method'];
        $deliveryDate = isset($input['delivery_date']) ? $input['delivery_date'] : null;
        $deliveryTimeSlot = isset($input['delivery_time_slot']) ? $input['delivery_time_slot'] : null;
        $deliveryInstructions = isset($input['delivery_instructions']) ? $input['delivery_instructions'] : null;
        $notes = isset($input['notes']) ? $input['notes'] : null;
        
        // Insert order
        $orderQuery = "INSERT INTO orders (
                           order_number, user_id, status, payment_status,
                           subtotal, tax_amount, shipping_fee, discount_amount, total_amount,
                           shipping_address, billing_address, payment_method,
                           delivery_date, delivery_time_slot, delivery_instructions, notes
                       ) VALUES (
                           :order_number, :user_id, 'pending', 'pending',
                           :subtotal, :tax_amount, :shipping_fee, :discount_amount, :total_amount,
                           :shipping_address, :billing_address, :payment_method,
                           :delivery_date, :delivery_time_slot, :delivery_instructions, :notes
                       )";
        
        $orderStmt = $conn->prepare($orderQuery);
        $orderStmt->bindParam(':order_number', $orderNumber);
        $orderStmt->bindParam(':user_id', $user_id);
        $orderStmt->bindParam(':subtotal', $subtotal);
        $orderStmt->bindParam(':tax_amount', $taxAmount);
        $orderStmt->bindParam(':shipping_fee', $shippingFee);
        $orderStmt->bindParam(':discount_amount', $discountAmount);
        $orderStmt->bindParam(':total_amount', $totalAmount);
        $orderStmt->bindParam(':shipping_address', $shippingAddress);
        $orderStmt->bindParam(':billing_address', $billingAddress);
        $orderStmt->bindParam(':payment_method', $paymentMethod);
        $orderStmt->bindParam(':delivery_date', $deliveryDate);
        $orderStmt->bindParam(':delivery_time_slot', $deliveryTimeSlot);
        $orderStmt->bindParam(':delivery_instructions', $deliveryInstructions);
        $orderStmt->bindParam(':notes', $notes);
        $orderStmt->execute();
        
        $orderId = $conn->lastInsertId();
        
        // Insert order items
        $itemQuery = "INSERT INTO order_items (
                          order_id, product_id, variant_id, product_name, product_sku,
                          quantity, unit_price, total_price, product_snapshot
                      ) VALUES (
                          :order_id, :product_id, :variant_id, :product_name, :product_sku,
                          :quantity, :unit_price, :total_price, :product_snapshot
                      )";
        
        $itemStmt = $conn->prepare($itemQuery);
        
        foreach ($orderItems as $item) {
            $productSnapshot = json_encode([
                'name' => $item['product_name'],
                'sku' => $item['product_sku'],
                'variant_name' => $item['variant_name'],
                'unit' => $item['unit'],
                'weight' => $item['weight'],
                'weight_unit' => $item['weight_unit']
            ]);
            
            $itemStmt->bindParam(':order_id', $orderId);
            $itemStmt->bindParam(':product_id', $item['product_id']);
            $itemStmt->bindParam(':variant_id', $item['variant_id']);
            $itemStmt->bindParam(':product_name', $item['product_name']);
            $itemStmt->bindParam(':product_sku', $item['product_sku']);
            $itemStmt->bindParam(':quantity', $item['quantity']);
            $itemStmt->bindParam(':unit_price', $item['unit_price']);
            $itemStmt->bindParam(':total_price', $item['total_price']);
            $itemStmt->bindParam(':product_snapshot', $productSnapshot);
            $itemStmt->execute();
            
            // Update stock using stored procedure
            $stockType = $item['variant_id'] ? 'variant' : 'product';
            $stockId = $item['variant_id'] ?: $item['product_id'];
            
            if ($item['variant_id']) {
                $updateStockQuery = "UPDATE product_variants SET stock_quantity = stock_quantity - :quantity WHERE variant_id = :id";
            } else {
                $updateStockQuery = "UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE product_id = :id";
            }
            
            $stockStmt = $conn->prepare($updateStockQuery);
            $stockStmt->bindParam(':quantity', $item['quantity']);
            $stockStmt->bindParam(':id', $stockId);
            $stockStmt->execute();
            
            // Log stock movement
            $movementQuery = "INSERT INTO stock_movements (
                                  product_id, variant_id, movement_type, quantity_change,
                                  quantity_before, quantity_after, reference_type, reference_id
                              ) VALUES (
                                  :product_id, :variant_id, 'sale', :quantity_change,
                                  :quantity_before, :quantity_after, 'order', :order_id
                              )";
            
            $movementStmt = $conn->prepare($movementQuery);
            $quantityBefore = $item['variant_id'] ? $item['variant_stock'] : $item['stock_quantity'];
            $quantityAfter = $quantityBefore - $item['quantity'];
            $quantityChange = -$item['quantity'];
            
            $movementStmt->bindParam(':product_id', $item['product_id']);
            $movementStmt->bindParam(':variant_id', $item['variant_id']);
            $movementStmt->bindParam(':quantity_change', $quantityChange);
            $movementStmt->bindParam(':quantity_before', $quantityBefore);
            $movementStmt->bindParam(':quantity_after', $quantityAfter);
            $movementStmt->bindParam(':order_id', $orderId);
            $movementStmt->execute();
        }
        
        // Clear cart
        $clearCartQuery = "DELETE FROM cart WHERE user_id = :user_id";
        $clearCartStmt = $conn->prepare($clearCartQuery);
        $clearCartStmt->bindParam(':user_id', $user_id);
        $clearCartStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Create notification
        $notificationQuery = "INSERT INTO notifications (user_id, type, title, message, data)
                              VALUES (:user_id, 'order_update', 'Order Placed Successfully', 
                                      'Your order #:order_number has been placed successfully.', 
                                      :notification_data)";
        $notificationStmt = $conn->prepare($notificationQuery);
        $notificationData = json_encode(['order_id' => $orderId, 'order_number' => $orderNumber]);
        $notificationStmt->bindParam(':user_id', $user_id);
        $notificationStmt->bindParam(':order_number', $orderNumber);
        $notificationStmt->bindParam(':notification_data', $notificationData);
        $notificationStmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => [
                'order_id' => (int)$orderId,
                'order_number' => $orderNumber,
                'total_amount' => (float)$totalAmount,
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'item_count' => count($orderItems)
            ]
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Create order error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

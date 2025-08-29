<?php
/**
 * Orders API Unit Tests
 * Tests order functionality via API endpoints
 */

namespace Doko\Tests\Unit;

use Doko\Tests\TestCase;
use Exception;

class OrdersApiTest extends TestCase
{
    public function testCanCreateOrder()
    {
        // Setup: Create user, product, and add to cart
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        $product = $this->createTestProduct([
            'name' => 'Order Test Product',
            'price' => 35.99,
            'stock_quantity' => 10
        ]);
        
        // Add product to cart first
    $this->postRequest('/api/cart/add.php', [
            'product_id' => $product['product_id'],
            'quantity' => 2
        ]);
        
        // Create order from cart
        $orderData = [
            'shipping_address' => '123 Test Street',
            'shipping_city' => 'Test City',
            'shipping_state' => 'Test State',
            'shipping_zip' => '12345',
            'payment_method' => 'cash_on_delivery'
        ];
        
        $response = $this->postRequest('/api/orders/orders.php', $orderData);
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('order_id', $response);
        $this->assertJsonHasKey('total_amount', $response);
        $this->assertEquals(71.98, $response['total_amount']); // 2 × $35.99
    }
    
    public function testCanGetUserOrders()
    {
        // Create user and order
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        // Create test order
        $order = $this->createTestOrderWithUser($user['user_id']);
        
        // Get user orders
        $response = $this->getRequest('/api/orders/orders-list.php');
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('orders', $response);
        $this->assertGreaterThanOrEqual(1, count($response['orders']));
        
        // Verify order belongs to user
        $foundOrder = false;
        foreach ($response['orders'] as $responseOrder) {
            if ($responseOrder['order_id'] == $order['order_id']) {
                $this->assertEquals($user['user_id'], $responseOrder['user_id']);
                $foundOrder = true;
            }
        }
        $this->assertTrue($foundOrder);
    }
    
    public function testCanGetOrderDetail()
    {
        // Setup
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        $product = $this->createTestProduct(['name' => 'Order Detail Product', 'price' => 25.00]);
                $order = $this->createTestOrderWithUser($user['user_id'], [
            'status' => 'pending',
            'total_amount' => 150.00
        ]);
        
        // Add order item
        $this->createTestOrderItem($order['order_id'], $product['product_id'], 2, 25.00);
        
        // Get order detail
        $response = $this->getRequest('/api/orders/order-detail.php?id=' . $order['order_id']);
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('order', $response);
        $this->assertJsonHasKey('items', $response);
        
        $this->assertEquals($order['order_id'], $response['order']['order_id']);
        $this->assertEquals(50.00, $response['order']['total_amount']);
        $this->assertEquals(1, count($response['items']));
        $this->assertEquals($product['product_id'], $response['items'][0]['product_id']);
    }
    
    public function testCanUpdateOrderStatus()
    {
        // Setup (admin user required for status updates)
        $adminUser = $this->createTestUser(['role' => 'admin']);
        $this->loginUser($adminUser);
        
        $customer = $this->createTestUser(['role' => 'customer']);
        $order = $this->createTestOrderWithUser($customer['user_id'], ['status' => 'pending']);
        
        // Update order status
        $response = $this->postRequest('/api/orders/update-status.php', [
            'order_id' => $order['order_id'],
            'status' => 'processing'
        ]);
        
        $this->assertResponseSuccess($response);
        $this->assertEquals('Order status updated successfully', $response['message']);
        
        // Verify status was updated
        $detailResponse = $this->getRequest('/api/orders/order-detail.php?id=' . $order['order_id']);
        $this->assertEquals('processing', $detailResponse['order']['status']);
    }
    
    public function testCanCancelOrder()
    {
        // Setup
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        $order = $this->createTestOrderWithUser($user['user_id'], ['status' => 'pending']);
        
        // Cancel order
        $response = $this->postRequest('/api/orders/cancel-order.php', [
            'order_id' => $order['order_id']
        ]);
        
        $this->assertResponseSuccess($response);
        $this->assertEquals('Order cancelled successfully', $response['message']);
        
        // Verify order status
        $detailResponse = $this->getRequest('/api/orders/order-detail.php?id=' . $order['order_id']);
        $this->assertEquals('cancelled', $detailResponse['order']['status']);
    }
    
    public function testCannotCancelShippedOrder()
    {
        // Setup
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        $order = $this->createTestOrderWithUser($user['user_id'], ['status' => 'shipped']);
        
        // Try to cancel shipped order
        $response = $this->postRequest('/api/orders/cancel-order.php', [
            'order_id' => $order['order_id']
        ]);
        
        $this->assertResponseError($response);
        $this->assertStringContainsString('cannot be cancelled', strtolower($response['message']));
    }
    
    public function testOrderReducesProductStock()
    {
        // Setup
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        $product = $this->createTestProduct([
            'name' => 'Stock Test Product',
            'price' => 20.00,
            'stock_quantity' => 10
        ]);
        
        // Add to cart and create order
    $this->postRequest('/api/cart/add.php', [
            'product_id' => $product['product_id'],
            'quantity' => 3
        ]);
        
        $orderData = [
            'shipping_address' => '123 Test Street',
            'shipping_city' => 'Test City',
            'shipping_state' => 'Test State',
            'shipping_zip' => '12345',
            'payment_method' => 'cash_on_delivery'
        ];
        
        $response = $this->postRequest('/api/orders/orders.php', $orderData);
        $this->assertResponseSuccess($response);
        
        // Check product stock was reduced
        $productResponse = $this->getRequest('/api/products/product-detail.php?id=' . $product['product_id']);
        $this->assertEquals(7, $productResponse['product']['stock_quantity']); // 10 - 3 = 7
    }
    
    public function testCanGetOrdersByStatus()
    {
        // Setup
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        $pendingOrder = $this->createTestOrderWithUser($user['user_id'], ['status' => 'pending']);
        $shippedOrder = $this->createTestOrderWithUser($user['user_id'], ['status' => 'shipped']);
        $deliveredOrder = $this->createTestOrderWithUser($user['user_id'], ['status' => 'delivered']);
        
        // Get pending orders
        $response = $this->getRequest('/api/orders/orders-list.php?status=pending');
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('orders', $response);
        
        // Verify all returned orders are pending
        foreach ($response['orders'] as $order) {
            $this->assertEquals('pending', $order['status']);
        }
    }
    
    public function testUserCanOnlyAccessOwnOrders()
    {
        // Create two users
        $user1 = $this->createTestUser(['email' => 'user1@test.com']);
        $user2 = $this->createTestUser(['email' => 'user2@test.com']);
        
        // Create orders for both users
        $order1 = $this->createTestOrderWithUser($user1['user_id']);
        $order2 = $this->createTestOrderWithUser($user2['user_id']);
        
        // Login as user1
        $this->loginUser($user1);
        
        // Try to access user2's order
        $response = $this->getRequest('/api/orders/order-detail.php?id=' . $order2['order_id']);
        
        $this->assertResponseError($response);
        $this->assertStringContainsString('access denied', strtolower($response['message']));
    }
    
    protected function createTestOrderWithUser($userId, $data = [])
    {
        $defaultData = [
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Default Street',
            'shipping_city' => 'Default City',
            'shipping_state' => 'Default State',
            'shipping_zip' => '12345',
            'payment_method' => 'cash_on_delivery'
        ];
        
        $orderData = array_merge($defaultData, $data);
        $orderData['user_id'] = $userId;
        
    // More unique order number to avoid collisions in fast sequential inserts
    $orderNumber = 'UT' . date('YmdHis') . substr(uniqid('', true), -6);
        $addressJson = json_encode([
            'address' => $orderData['shipping_address'],
            'city' => $orderData['shipping_city'],
            'state' => $orderData['shipping_state'],
            'zip' => $orderData['shipping_zip']
        ]);
        $stmt = $this->getPdo()->prepare("INSERT INTO orders (order_number, user_id, status, payment_status, subtotal, tax_amount, shipping_fee, discount_amount, total_amount, shipping_address, billing_address, payment_method, ordered_at, created_at) VALUES (?, ?, ?, 'pending', ?, 0, 0, 0, ?, ?, ?, ?, NOW(), NOW())");
        $attempts = 0;
        while (true) {
            try {
                $stmt->execute([
                    $orderNumber,
                    $orderData['user_id'],
                    $orderData['status'],
                    $orderData['total_amount'],
                    $orderData['total_amount'],
                    $addressJson,
                    $addressJson,
                    $orderData['payment_method']
                ]);
                break;
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false && $attempts < 3) {
                    $orderNumber = 'UT' . date('YmdHis') . substr(uniqid('', true), -6);
                    $attempts++;
                    continue;
                }
                throw $e;
            }
        }
        
        $orderData['order_id'] = $this->getPdo()->lastInsertId();
        return $orderData;
    }
    
    private function createTestOrderItem($orderId, $productId, $quantity, $price)
    {
        // Fetch product details for required name & sku columns
        $prod = $this->getPdo()->prepare("SELECT name, sku FROM products WHERE product_id = ? LIMIT 1");
        $prod->execute([$productId]);
        $p = $prod->fetch();
        $productName = $p ? $p['name'] : ('Prod #' . $productId);
        $productSku = $p && !empty($p['sku']) ? $p['sku'] : ('SKU' . $productId);
        $stmt = $this->getPdo()->prepare("INSERT INTO order_items (order_id, product_id, variant_id, product_name, product_sku, quantity, unit_price, total_price, created_at) VALUES (?, ?, NULL, ?, ?, ?, ?, ?, NOW())");
        $total = $quantity * $price;
        $stmt->execute([$orderId, $productId, $productName, $productSku, $quantity, $price, $total]);
        return [
            'order_item_id' => $this->getPdo()->lastInsertId(),
            'order_id' => $orderId,
            'product_id' => $productId,
            'product_name' => $productName,
            'product_sku' => $productSku,
            'quantity' => $quantity,
            'unit_price' => $price,
            'total_price' => $total
        ];
    }
}

<?php
/**
 * Shopping Workflow Integration Test
 * Tests complete shopping experience from product browse to order completion
 */

require_once __DIR__ . '/../TestCase.php';

class ShoppingWorkflowIntegrationTest extends TestCase
{
    public function testCompleteShoppingWorkflow()
    {
        // 1. Setup: Create products and categories
        $category = $this->createTestCategory(['name' => 'Electronics']);
        
        $product1 = $this->createTestProduct([
            'name' => 'Smartphone',
            'price' => 299.99,
            'category_id' => $category['category_id'],
            'stock_quantity' => 10
        ]);
        
        $product2 = $this->createTestProduct([
            'name' => 'Headphones',
            'price' => 99.99,
            'category_id' => $category['category_id'],
            'stock_quantity' => 5
        ]);
        
        // 2. Browse products (as guest)
        $browseResponse = $this->getRequest('/api/products/products-list.php');
        $this->assertResponseSuccess($browseResponse);
        $this->assertJsonHasKey('products', $browseResponse);
        $this->assertGreaterThanOrEqual(2, count($browseResponse['products']));
        
        // 3. View product detail
        $detailResponse = $this->getRequest('/api/products/product-detail.php?id=' . $product1['product_id']);
        $this->assertResponseSuccess($detailResponse);
        $this->assertEquals('Smartphone', $detailResponse['product']['name']);
        $this->assertEquals(299.99, $detailResponse['product']['price']);
        
        // 4. Register and login user
        $userData = [
            'username' => 'shopper',
            'email' => 'shopper@test.com',
            'password' => 'shopping123',
            'first_name' => 'Test',
            'last_name' => 'Shopper'
        ];
        
        $registerResponse = $this->postRequest('/api/users/auth-register.php', $userData);
        $this->assertResponseSuccess($registerResponse);
        
        $loginResponse = $this->postRequest('/api/users/auth-login.php', [
            'email' => 'shopper@test.com',
            'password' => 'shopping123'
        ]);
        $this->assertResponseSuccess($loginResponse);
        
        // 5. Add products to cart
        $addToCartResponse1 = $this->postRequest('/api/cart/cart-add.php', [
            'product_id' => $product1['product_id'],
            'quantity' => 1
        ]);
        $this->assertResponseSuccess($addToCartResponse1);
        
        $addToCartResponse2 = $this->postRequest('/api/cart/cart-add.php', [
            'product_id' => $product2['product_id'],
            'quantity' => 2
        ]);
        $this->assertResponseSuccess($addToCartResponse2);
        
        // 6. View cart contents
        $cartResponse = $this->getRequest('/api/cart/cart-get.php');
        $this->assertResponseSuccess($cartResponse);
        $this->assertCount(2, $cartResponse['items']);
        $this->assertEquals(499.97, $cartResponse['total']); // $299.99 + (2 × $99.99)
        
        // 7. Update cart quantity
        $updateCartResponse = $this->postRequest('/api/cart/cart-update.php', [
            'product_id' => $product2['product_id'],
            'quantity' => 1  // Reduce from 2 to 1
        ]);
        $this->assertResponseSuccess($updateCartResponse);
        
        // 8. Verify cart update
        $updatedCartResponse = $this->getRequest('/api/cart/cart-get.php');
        $this->assertEquals(399.98, $updatedCartResponse['total']); // $299.99 + $99.99
        
        // 9. Add to wishlist
        $wishlistResponse = $this->postRequest('/api/wishlist/wishlist.php', [
            'product_id' => $product1['product_id'],
            'action' => 'add'
        ]);
        $this->assertResponseSuccess($wishlistResponse);
        
        // 10. View wishlist
        $viewWishlistResponse = $this->getRequest('/api/wishlist/wishlist.php');
        $this->assertResponseSuccess($viewWishlistResponse);
        $this->assertGreaterThanOrEqual(1, count($viewWishlistResponse['items']));
        
        // 11. Create order from cart
        $orderData = [
            'shipping_address' => '123 Shopping Street',
            'shipping_city' => 'Commerce City',
            'shipping_state' => 'Shopping State',
            'shipping_zip' => '54321',
            'payment_method' => 'credit_card',
            'payment_details' => [
                'card_number' => '4111111111111111',
                'expiry' => '12/25',
                'cvv' => '123'
            ]
        ];
        
        $orderResponse = $this->postRequest('/api/orders/orders.php', $orderData);
        $this->assertResponseSuccess($orderResponse);
        $this->assertJsonHasKey('order_id', $orderResponse);
        $this->assertEquals(399.98, $orderResponse['total_amount']);
        
        $orderId = $orderResponse['order_id'];
        
        // 12. Verify cart is cleared after order
        $clearedCartResponse = $this->getRequest('/api/cart/cart-get.php');
        $this->assertCount(0, $clearedCartResponse['items']);
        $this->assertEquals(0, $clearedCartResponse['total']);
        
        // 13. Verify order details
        $orderDetailResponse = $this->getRequest('/api/orders/order-detail.php?id=' . $orderId);
        $this->assertResponseSuccess($orderDetailResponse);
        $this->assertEquals($orderId, $orderDetailResponse['order']['order_id']);
        $this->assertEquals('pending', $orderDetailResponse['order']['status']);
        $this->assertCount(2, $orderDetailResponse['items']);
        
        // 14. Verify product stock was reduced
        $product1DetailResponse = $this->getRequest('/api/products/product-detail.php?id=' . $product1['product_id']);
        $this->assertEquals(9, $product1DetailResponse['product']['stock_quantity']); // 10 - 1
        
        $product2DetailResponse = $this->getRequest('/api/products/product-detail.php?id=' . $product2['product_id']);
        $this->assertEquals(4, $product2DetailResponse['product']['stock_quantity']); // 5 - 1
        
        // 15. View user's orders
        $userOrdersResponse = $this->getRequest('/api/orders/orders-list.php');
        $this->assertResponseSuccess($userOrdersResponse);
        $this->assertGreaterThanOrEqual(1, count($userOrdersResponse['orders']));
        
        // Verify our order is in the list
        $foundOrder = false;
        foreach ($userOrdersResponse['orders'] as $order) {
            if ($order['order_id'] == $orderId) {
                $this->assertEquals(399.98, $order['total_amount']);
                $foundOrder = true;
            }
        }
        $this->assertTrue($foundOrder);
    }
    
    public function testGuestToRegisteredUserConversion()
    {
        // 1. Browse as guest and add to cart via session
        $product = $this->createTestProduct([
            'name' => 'Guest Product',
            'price' => 25.00,
            'stock_quantity' => 10
        ]);
        
        // Simulate guest cart (would normally be stored in session)
        $_SESSION['guest_cart'] = [
            $product['product_id'] => ['quantity' => 2, 'price' => 25.00]
        ];
        
        // 2. Register new user
        $userData = [
            'username' => 'newuser',
            'email' => 'newuser@test.com',
            'password' => 'newpass123',
            'first_name' => 'New',
            'last_name' => 'User'
        ];
        
        $registerResponse = $this->postRequest('/api/users/auth-register.php', $userData);
        $this->assertResponseSuccess($registerResponse);
        
        // 3. Login
        $loginResponse = $this->postRequest('/api/users/auth-login.php', [
            'email' => 'newuser@test.com',
            'password' => 'newpass123'
        ]);
        $this->assertResponseSuccess($loginResponse);
        
        // 4. Transfer guest cart to user cart (simulate cart merge)
        $mergeCartResponse = $this->postRequest('/api/cart/cart-add.php', [
            'product_id' => $product['product_id'],
            'quantity' => 2
        ]);
        $this->assertResponseSuccess($mergeCartResponse);
        
        // 5. Verify cart contents
        $cartResponse = $this->getRequest('/api/cart/cart-get.php');
        $this->assertResponseSuccess($cartResponse);
        $this->assertCount(1, $cartResponse['items']);
        $this->assertEquals(50.00, $cartResponse['total']); // 2 × $25.00
    }
    
    public function testOutOfStockHandling()
    {
        // 1. Create product with limited stock
        $product = $this->createTestProduct([
            'name' => 'Limited Stock Product',
            'price' => 50.00,
            'stock_quantity' => 1
        ]);
        
        // 2. Create two users
        $user1 = $this->createTestUser(['email' => 'user1@test.com']);
        $user2 = $this->createTestUser(['email' => 'user2@test.com']);
        
        // 3. User 1 adds product to cart
        $this->loginUser($user1);
        $addResponse1 = $this->postRequest('/api/cart/cart-add.php', [
            'product_id' => $product['product_id'],
            'quantity' => 1
        ]);
        $this->assertResponseSuccess($addResponse1);
        
        // 4. User 1 creates order (should succeed and deplete stock)
        $orderResponse1 = $this->postRequest('/api/orders/orders.php', [
            'shipping_address' => '123 First Street',
            'shipping_city' => 'First City',
            'shipping_state' => 'First State',
            'shipping_zip' => '11111',
            'payment_method' => 'credit_card'
        ]);
        $this->assertResponseSuccess($orderResponse1);
        
        // 5. Logout user 1, login user 2
        $this->postRequest('/api/users/auth-logout.php');
        $this->loginUser($user2);
        
        // 6. User 2 tries to add same product (should fail - out of stock)
        $addResponse2 = $this->postRequest('/api/cart/cart-add.php', [
            'product_id' => $product['product_id'],
            'quantity' => 1
        ]);
        $this->assertResponseError($addResponse2);
        $this->assertStringContainsString('out of stock', strtolower($addResponse2['message']));
        
        // 7. Verify product shows as out of stock
        $productResponse = $this->getRequest('/api/products/product-detail.php?id=' . $product['product_id']);
        $this->assertEquals(0, $productResponse['product']['stock_quantity']);
        $this->assertFalse($productResponse['product']['in_stock']);
    }
    
    private function createTestCategory($data = [])
    {
        $defaultData = [
            'name' => 'Test Category',
            'description' => 'A test category',
            'status' => 'active'
        ];
        
        $categoryData = array_merge($defaultData, $data);
        
        $stmt = $this->db->prepare("
            INSERT INTO categories (name, description, status) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([
            $categoryData['name'],
            $categoryData['description'],
            $categoryData['status']
        ]);
        
        $categoryData['category_id'] = $this->db->lastInsertId();
        return $categoryData;
    }
}

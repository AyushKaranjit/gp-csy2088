<?php
/**
 * Cart API Unit Tests
 * Tests cart functionality
 */

require_once __DIR__ . '/../TestCase.php';

class CartApiTest extends TestCase
{
    public function testCanAddProductToCart()
    {
        // Create test product
        $product = $this->createTestProduct([
            'name' => 'Test Cart Product',
            'price' => 25.99,
            'stock_quantity' => 10
        ]);
        
        // Login user
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        // Add to cart
    $response = $this->postRequest('/api/cart/add.php', [
            'product_id' => $product['product_id'],
            'quantity' => 2
        ]);
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('message', $response);
        $this->assertEquals('Product added to cart successfully', $response['message']);
    }
    
    public function testCanGetCartContents()
    {
        // Create test product and user
        $product = $this->createTestProduct([
            'name' => 'Cart Item Product',
            'price' => 15.50
        ]);
        
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        // Add product to cart
    $this->postRequest('/api/cart/add.php', [
            'product_id' => $product['product_id'],
            'quantity' => 1
        ]);
        
        // Get cart contents
    $response = $this->getRequest('/api/cart/get.php');
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('items', $response);
        $this->assertJsonHasKey('total', $response);
        $this->assertCount(1, $response['items']);
        $this->assertEquals($product['product_id'], $response['items'][0]['product_id']);
    }
    
    public function testCanUpdateCartQuantity()
    {
        // Setup
        $product = $this->createTestProduct();
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        // Add to cart
    $this->postRequest('/api/cart/add.php', [
            'product_id' => $product['product_id'],
            'quantity' => 1
        ]);
        
        // Update quantity
    $response = $this->postRequest('/api/cart/update.php', [
            'product_id' => $product['product_id'],
            'quantity' => 3
        ]);
        
        $this->assertResponseSuccess($response);
        
        // Verify updated quantity
    $cartResponse = $this->getRequest('/api/cart/get.php');
        $this->assertEquals(3, $cartResponse['items'][0]['quantity']);
    }
    
    public function testCanRemoveFromCart()
    {
        // Setup
        $product = $this->createTestProduct();
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        // Add to cart
    $this->postRequest('/api/cart/add.php', [
            'product_id' => $product['product_id'],
            'quantity' => 2
        ]);
        
        // Remove from cart
    $response = $this->postRequest('/api/cart/remove.php', [
            'product_id' => $product['product_id']
        ]);
        
        $this->assertResponseSuccess($response);
        
        // Verify removal
    $cartResponse = $this->getRequest('/api/cart/get.php');
        $this->assertCount(0, $cartResponse['items']);
    }
    
    public function testCanClearCart()
    {
        // Setup with multiple products
        $product1 = $this->createTestProduct(['name' => 'Product 1']);
        $product2 = $this->createTestProduct(['name' => 'Product 2']);
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        // Add products to cart
    $this->postRequest('/api/cart/add.php', [
            'product_id' => $product1['product_id'],
            'quantity' => 1
        ]);
    $this->postRequest('/api/cart/add.php', [
            'product_id' => $product2['product_id'],
            'quantity' => 2
        ]);
        
        // Clear cart
    $response = $this->postRequest('/api/cart/clear.php');
        
        $this->assertResponseSuccess($response);
        
        // Verify cart is empty
    $cartResponse = $this->getRequest('/api/cart/get.php');
        $this->assertCount(0, $cartResponse['items']);
        $this->assertEquals(0, $cartResponse['total']);
    }
    
    public function testCartCalculatesTotalCorrectly()
    {
        // Setup
        $product1 = $this->createTestProduct(['name' => 'Product 1', 'price' => 10.00]);
        $product2 = $this->createTestProduct(['name' => 'Product 2', 'price' => 25.50]);
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        // Add products with specific quantities
    $this->postRequest('/api/cart/add.php', [
            'product_id' => $product1['product_id'],
            'quantity' => 2  // 2 × $10.00 = $20.00
        ]);
    $this->postRequest('/api/cart/add.php', [
            'product_id' => $product2['product_id'],
            'quantity' => 1  // 1 × $25.50 = $25.50
        ]);
        
        // Get cart and verify total
    $response = $this->getRequest('/api/cart/get.php');
        
        $this->assertResponseSuccess($response);
        $this->assertEquals(45.50, $response['total']); // $20.00 + $25.50
    }
    
    public function testCartRequiresAuthentication()
    {
        // Try to access cart without login
    $response = $this->getRequest('/api/cart/get.php');
        
        $this->assertResponseError($response);
        $this->assertStringContainsString('authentication', strtolower($response['message']));
    }
    
    public function testCannotAddOutOfStockProduct()
    {
        // Create out of stock product
        $product = $this->createTestProduct([
            'name' => 'Out of Stock Product',
            'price' => 10.00,
            'stock_quantity' => 0
        ]);
        
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        // Try to add to cart
    $response = $this->postRequest('/api/cart/add.php', [
            'product_id' => $product['product_id'],
            'quantity' => 1
        ]);
        
        $this->assertResponseError($response);
        $this->assertStringContainsString('out of stock', strtolower($response['message']));
    }
    
    public function testCannotAddMoreThanAvailableStock()
    {
        // Create product with limited stock
        $product = $this->createTestProduct([
            'name' => 'Limited Stock Product',
            'price' => 10.00,
            'stock_quantity' => 3
        ]);
        
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        // Try to add more than available
    $response = $this->postRequest('/api/cart/add.php', [
            'product_id' => $product['product_id'],
            'quantity' => 5  // More than available stock (3)
        ]);
        
        $this->assertResponseError($response);
        $this->assertStringContainsString('insufficient stock', strtolower($response['message']));
    }
}

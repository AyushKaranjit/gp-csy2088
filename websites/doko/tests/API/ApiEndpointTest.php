<?php
/**
 * API Endpoint Tests
 * Tests all API endpoints for proper functionality
 */

namespace Doko\Tests\API;

use Doko\Tests\TestCase;

class ApiEndpointTest extends TestCase
{
    public function testAllProductEndpointsRespond()
    {
        $endpoints = [
            '/api/products/products-list.php',
            '/api/products/products-featured.php',
            '/api/products/products-search.php?q=test'
        ];
        
        foreach ($endpoints as $endpoint) {
            $response = $this->getRequest($endpoint);
            $this->assertIsArray($response, "Endpoint $endpoint should return array");
            
            // Should return either success or proper error structure
            if (isset($response['success'])) {
                $this->assertJsonHasKey('success', $response);
                $this->assertIsBool($response['success']);
            }
        }
    }
    
    public function testProductDetailEndpoint()
    {
        // Test with valid product
        $product = $this->createTestProduct();
        $response = $this->getRequest('/api/products/product-detail.php?id=' . $product['product_id']);
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('product', $response);
        $this->assertEquals($product['product_id'], $response['product']['product_id']);
        
        // Test with invalid product ID
        $invalidResponse = $this->getRequest('/api/products/product-detail.php?id=99999');
        $this->assertResponseError($invalidResponse);
        
        // Test with missing ID parameter
        $missingResponse = $this->getRequest('/api/products/product-detail.php');
        $this->assertResponseError($missingResponse);
    }
    
    public function testCategoryEndpoints()
    {
        $endpoints = [
            '/api/categories/categories-list.php'
        ];
        
        foreach ($endpoints as $endpoint) {
            $response = $this->getRequest($endpoint);
            $this->assertIsArray($response);
            
            if (isset($response['success'])) {
                $this->assertIsBool($response['success']);
            }
        }
    }
    
    public function testAuthenticationEndpoints()
    {
        // Test registration endpoint
        $registerData = [
            'username' => 'apitest',
            'email' => 'apitest@test.com',
            'password' => 'password123',
            'first_name' => 'API',
            'last_name' => 'Test'
        ];
        
        $registerResponse = $this->postRequest('/api/users/auth-register.php', $registerData);
        $this->assertIsArray($registerResponse);
        $this->assertJsonHasKey('success', $registerResponse);
        
        // Test login endpoint
        $loginData = [
            'email' => 'apitest@test.com',
            'password' => 'password123'
        ];
        
        $loginResponse = $this->postRequest('/api/users/auth-login.php', $loginData);
        $this->assertIsArray($loginResponse);
        $this->assertJsonHasKey('success', $loginResponse);
        
        // Test auth status endpoint
        $statusResponse = $this->getRequest('/api/users/auth-status.php');
        $this->assertIsArray($statusResponse);
        $this->assertJsonHasKey('logged_in', $statusResponse);
        
        // Test logout endpoint
        $logoutResponse = $this->postRequest('/api/users/auth-logout.php');
        $this->assertIsArray($logoutResponse);
        $this->assertJsonHasKey('success', $logoutResponse);
    }
    
    public function testCartEndpointsRequireAuth()
    {
        $cartEndpoints = [
            '/api/cart/get.php',
            '/api/cart/add.php',
            '/api/cart/update.php',
            '/api/cart/remove.php',
            '/api/cart/clear.php'
        ];
        
        // Test without authentication
        foreach ($cartEndpoints as $endpoint) {
            $response = $this->getRequest($endpoint);
            
            // Should return error or redirect for unauthenticated requests
            if (isset($response['success'])) {
                $this->assertFalse($response['success'], "Endpoint $endpoint should require authentication");
            }
        }
        
        // Test with authentication
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        $product = $this->createTestProduct();
        
        // Test cart-get (should work when authenticated)
    $getResponse = $this->getRequest('/api/cart/get.php');
        $this->assertIsArray($getResponse);
        
        // Test cart-add with valid data
    $addResponse = $this->postRequest('/api/cart/add.php', [
            'product_id' => $product['product_id'],
            'quantity' => 1
        ]);
        $this->assertIsArray($addResponse);
    }
    
    public function testOrderEndpointsRequireAuth()
    {
        $orderEndpoints = [
            '/api/orders/orders-list.php'
        ];
        
        // Test without authentication
        foreach ($orderEndpoints as $endpoint) {
            $response = $this->getRequest($endpoint);
            
            if (isset($response['success'])) {
                $this->assertFalse($response['success'], "Endpoint $endpoint should require authentication");
            }
        }
        
        // Test with authentication
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        $listResponse = $this->getRequest('/api/orders/orders-list.php');
        $this->assertIsArray($listResponse);
    }
    
    public function testWishlistEndpoints()
    {
        // Test without authentication
        $response = $this->getRequest('/api/wishlist/wishlist.php');
        
        if (isset($response['success'])) {
            $this->assertFalse($response['success'], "Wishlist should require authentication");
        }
        
        // Test with authentication
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        $product = $this->createTestProduct();
        
        // Test add to wishlist
        $addResponse = $this->postRequest('/api/wishlist/wishlist.php', [
            'product_id' => $product['product_id'],
            'action' => 'add'
        ]);
        $this->assertIsArray($addResponse);
        
        // Test get wishlist
        $getResponse = $this->getRequest('/api/wishlist/wishlist.php');
        $this->assertIsArray($getResponse);
    }
    
    public function testAdminEndpointsRequireAdminAuth()
    {
        $adminEndpoints = [
            '/api/admin/admin-users.php',
            '/api/admin/admin-products.php',
            '/api/admin/admin-orders.php'
        ];
        
        // Test without authentication
        foreach ($adminEndpoints as $endpoint) {
            $response = $this->getRequest($endpoint);
            
            if (isset($response['success'])) {
                $this->assertFalse($response['success'], "Admin endpoint $endpoint should require authentication");
            }
        }
        
        // Test with regular user authentication (should fail)
        $regularUser = $this->createTestUser(['role' => 'customer']);
        $this->loginUser($regularUser);
        
        foreach ($adminEndpoints as $endpoint) {
            $response = $this->getRequest($endpoint);
            
            if (isset($response['success'])) {
                $this->assertFalse($response['success'], "Admin endpoint $endpoint should require admin role");
            }
        }
        
        // Logout regular user
        $this->postRequest('/api/users/auth-logout.php');
        
        // Test with admin authentication (should succeed)
        $adminUser = $this->createTestUser(['role' => 'admin']);
        $this->loginUser($adminUser);
        
        foreach ($adminEndpoints as $endpoint) {
            $response = $this->getRequest($endpoint);
            $this->assertIsArray($response, "Admin endpoint $endpoint should respond for admin users");
        }
    }
    
    public function testApiErrorHandling()
    {
        // Test malformed requests
        $malformedEndpoints = [
            '/api/products/product-detail.php?id=abc', // Non-numeric ID
            '/api/products/product-detail.php?id=-1',  // Negative ID
            '/api/products/products-search.php',       // Missing search query
        ];
        
        foreach ($malformedEndpoints as $endpoint) {
            $response = $this->getRequest($endpoint);
            $this->assertIsArray($response, "Endpoint $endpoint should return array even for malformed requests");
            
            if (isset($response['success'])) {
                $this->assertFalse($response['success'], "Malformed request to $endpoint should return error");
                $this->assertJsonHasKey('message', $response, "Error response should include message");
            }
        }
    }
    
    public function testApiResponseHeaders()
    {
        // This test would verify proper HTTP headers are set
        // For now, just verify endpoints respond
        $endpoints = [
            '/api/products/products-list.php',
            '/api/categories/categories-list.php',
            '/api/users/auth-status.php'
        ];
        
        foreach ($endpoints as $endpoint) {
            $response = $this->getRequest($endpoint);
            $this->assertIsArray($response, "Endpoint $endpoint should return structured response");
        }
    }
    
    public function testApiRateLimiting()
    {
        // Basic test to ensure endpoints don't crash under rapid requests
        $endpoint = '/api/products/products-list.php';
        
        for ($i = 0; $i < 5; $i++) {
            $response = $this->getRequest($endpoint);
            $this->assertIsArray($response, "Endpoint should handle multiple rapid requests");
        }
    }
    
    public function testApiDataValidation()
    {
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        // Test cart-add with invalid data
        $invalidRequests = [
            [], // Empty data
            ['product_id' => ''], // Empty product ID
            ['product_id' => 'abc'], // Non-numeric product ID
            ['product_id' => 1, 'quantity' => 0], // Zero quantity
            ['product_id' => 1, 'quantity' => -1], // Negative quantity
            ['product_id' => 1, 'quantity' => 'abc'], // Non-numeric quantity
        ];
        
        foreach ($invalidRequests as $index => $data) {
            $response = $this->postRequest('/api/cart/add.php', $data);
            $this->assertIsArray($response, "Request $index should return structured response");
            
            if (isset($response['success'])) {
                $this->assertFalse($response['success'], "Invalid data request $index should return error");
            }
        }
    }
}

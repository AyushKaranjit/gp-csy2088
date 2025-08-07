<?php
/**
 * Products API Unit Tests
 * Tests product functionality
 */

require_once __DIR__ . '/../TestCase.php';

class ProductsApiTest extends TestCase
{
    public function testCanGetProductsList()
    {
        // Create test products
        $product1 = $this->createTestProduct(['name' => 'Test Product 1', 'price' => 19.99]);
        $product2 = $this->createTestProduct(['name' => 'Test Product 2', 'price' => 29.99]);
        
        // Get products list
        $response = $this->getRequest('/api/products/products-list.php');
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('products', $response);
        $this->assertGreaterThanOrEqual(2, count($response['products']));
    }
    
    public function testCanGetProductDetail()
    {
        // Create test product
        $product = $this->createTestProduct([
            'name' => 'Detailed Test Product',
            'description' => 'A detailed product for testing',
            'price' => 45.99,
            'stock_quantity' => 15
        ]);
        
        // Get product detail
        $response = $this->getRequest('/api/products/product-detail.php?id=' . $product['product_id']);
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('product', $response);
        $this->assertEquals($product['product_id'], $response['product']['product_id']);
        $this->assertEquals('Detailed Test Product', $response['product']['name']);
        $this->assertEquals(45.99, $response['product']['price']);
    }
    
    public function testCanSearchProducts()
    {
        // Create searchable products
        $product1 = $this->createTestProduct(['name' => 'Red Apple iPhone']);
        $product2 = $this->createTestProduct(['name' => 'Blue Samsung Phone']);
        $product3 = $this->createTestProduct(['name' => 'Green Apple Watch']);
        
        // Search for "apple"
        $response = $this->getRequest('/api/products/products-search.php?q=apple');
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('products', $response);
        $this->assertEquals(2, count($response['products'])); // Should find 2 apple products
    }
    
    public function testCanGetFeaturedProducts()
    {
        // Create featured products
        $featured1 = $this->createTestProduct(['name' => 'Featured Product 1', 'featured' => 1]);
        $featured2 = $this->createTestProduct(['name' => 'Featured Product 2', 'featured' => 1]);
        $regular = $this->createTestProduct(['name' => 'Regular Product', 'featured' => 0]);
        
        // Get featured products
        $response = $this->getRequest('/api/products/products-featured.php');
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('products', $response);
        
        // Verify only featured products are returned
        foreach ($response['products'] as $product) {
            $this->assertEquals(1, $product['featured']);
        }
    }
    
    public function testCanFilterProductsByCategory()
    {
        // Create categories
        $category1 = $this->createTestCategory(['name' => 'Electronics']);
        $category2 = $this->createTestCategory(['name' => 'Clothing']);
        
        // Create products in different categories
        $electronic1 = $this->createTestProduct(['name' => 'Phone', 'category_id' => $category1['category_id']]);
        $electronic2 = $this->createTestProduct(['name' => 'Laptop', 'category_id' => $category1['category_id']]);
        $clothing = $this->createTestProduct(['name' => 'T-Shirt', 'category_id' => $category2['category_id']]);
        
        // Get electronics products
        $response = $this->getRequest('/api/products/products-list.php?category=' . $category1['category_id']);
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('products', $response);
        $this->assertEquals(2, count($response['products']));
        
        // Verify all products are from electronics category
        foreach ($response['products'] as $product) {
            $this->assertEquals($category1['category_id'], $product['category_id']);
        }
    }
    
    public function testCanSortProducts()
    {
        // Create products with different prices
        $cheap = $this->createTestProduct(['name' => 'Cheap Product', 'price' => 10.99]);
        $expensive = $this->createTestProduct(['name' => 'Expensive Product', 'price' => 99.99]);
        $medium = $this->createTestProduct(['name' => 'Medium Product', 'price' => 45.50]);
        
        // Get products sorted by price ascending
        $response = $this->getRequest('/api/products/products-list.php?sort=price&order=asc');
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('products', $response);
        $this->assertGreaterThanOrEqual(3, count($response['products']));
        
        // Verify products are sorted by price (ascending)
        $prices = array_column($response['products'], 'price');
        $sortedPrices = $prices;
        sort($sortedPrices);
        $this->assertEquals($sortedPrices, $prices);
    }
    
    public function testProductDetailReturnsErrorForInvalidId()
    {
        // Try to get non-existent product
        $response = $this->getRequest('/api/products/product-detail.php?id=99999');
        
        $this->assertResponseError($response);
        $this->assertStringContainsString('not found', strtolower($response['message']));
    }
    
    public function testCanGetProductsWithPagination()
    {
        // Create multiple products
        for ($i = 1; $i <= 25; $i++) {
            $this->createTestProduct(['name' => "Product $i"]);
        }
        
        // Get first page (limit 10)
        $response = $this->getRequest('/api/products/products-list.php?page=1&limit=10');
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('products', $response);
        $this->assertJsonHasKey('pagination', $response);
        $this->assertEquals(10, count($response['products']));
        $this->assertEquals(1, $response['pagination']['current_page']);
        $this->assertGreaterThan(2, $response['pagination']['total_pages']);
    }
    
    public function testProductsIncludeStockStatus()
    {
        // Create products with different stock levels
        $inStock = $this->createTestProduct(['name' => 'In Stock Product', 'stock_quantity' => 10]);
        $outOfStock = $this->createTestProduct(['name' => 'Out of Stock Product', 'stock_quantity' => 0]);
        
        // Get products
        $response = $this->getRequest('/api/products/products-list.php');
        
        $this->assertResponseSuccess($response);
        
        // Find our test products in the response
        foreach ($response['products'] as $product) {
            if ($product['product_id'] == $inStock['product_id']) {
                $this->assertTrue($product['in_stock']);
            }
            if ($product['product_id'] == $outOfStock['product_id']) {
                $this->assertFalse($product['in_stock']);
            }
        }
    }
    
    public function testCanGetProductsByPriceRange()
    {
        // Create products with different prices
        $cheap = $this->createTestProduct(['name' => 'Cheap Product', 'price' => 15.00]);
        $medium = $this->createTestProduct(['name' => 'Medium Product', 'price' => 50.00]);
        $expensive = $this->createTestProduct(['name' => 'Expensive Product', 'price' => 150.00]);
        
        // Get products in medium price range (20-100)
        $response = $this->getRequest('/api/products/products-list.php?min_price=20&max_price=100');
        
        $this->assertResponseSuccess($response);
        $this->assertJsonHasKey('products', $response);
        
        // Verify all products are within price range
        foreach ($response['products'] as $product) {
            $this->assertGreaterThanOrEqual(20, $product['price']);
            $this->assertLessThanOrEqual(100, $product['price']);
        }
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

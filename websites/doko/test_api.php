<?php
// Test API response with test framework simulation
require_once 'vendor/autoload.php';
require_once 'tests/TestCase.php';

class DebugTest extends Doko\Tests\TestCase {
    public function testDebug() {
        // Create test product
        $product = $this->createTestProduct([
            'name' => 'Debug Test Product',
            'description' => 'Debug product for testing',
            'price' => 45.99,
            'stock_quantity' => 15
        ]);

        echo "Created product ID: " . $product['product_id'] . PHP_EOL;

        // Get product detail
        $response = $this->getRequest('/api/products/product-detail.php?id=' . $product['product_id']);

        echo "Response type: " . gettype($response) . PHP_EOL;
        echo "Response keys: " . implode(', ', array_keys($response)) . PHP_EOL;
        echo "Response: " . print_r($response, true) . PHP_EOL;

        if (isset($response['success'])) {
            echo "Success value: " . ($response['success'] ? 'true' : 'false') . PHP_EOL;
        } else {
            echo "No success key found!" . PHP_EOL;
        }

        if (isset($response['product'])) {
            echo "Product key exists" . PHP_EOL;
        } else {
            echo "Product key missing!" . PHP_EOL;
        }
    }
}

$test = new DebugTest();
$test->testDebug();

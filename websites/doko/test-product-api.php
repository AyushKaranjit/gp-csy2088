<?php
/**
 * Test script to verify product API functionality
 */

require_once 'config/database.php';

try {
    echo "<h2>Testing Product API</h2>";
    
    // Test database connection
    $db = Database::getInstance()->getConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Test if categories table has data
    $categoryStmt = $db->query("SELECT COUNT(*) as count FROM categories");
    $categoryCount = $categoryStmt->fetch()['count'];
    echo "<p>📊 Categories in database: $categoryCount</p>";
    
    if ($categoryCount == 0) {
        echo "<p>⚠️ No categories found. You may need to run the database setup.</p>";
    }
    
    // Test if products table exists and structure
    $tableStmt = $db->query("DESCRIBE products");
    $columns = $tableStmt->fetchAll();
    
    echo "<h3>Products table structure:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li><strong>{$column['Field']}</strong>: {$column['Type']} " . 
             ($column['Null'] == 'NO' ? '(Required)' : '(Optional)') . "</li>";
    }
    echo "</ul>";
    
    // Test creating a sample product
    echo "<h3>Testing product creation:</h3>";
    
    // Generate unique SKU
    function generateUniqueSKU($db, $productName) {
        $baseSku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $productName), 0, 6));
        if (empty($baseSku)) {
            $baseSku = 'PROD';
        }
        
        $counter = 1;
        $sku = $baseSku . sprintf('%03d', $counter);
        
        // Check if SKU already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE sku = ?");
        $stmt->execute([$sku]);
        while ($stmt->fetchColumn() > 0) {
            $counter++;
            $sku = $baseSku . sprintf('%03d', $counter);
            
            if ($counter > 999) {
                $sku = $baseSku . time();
                break;
            }
            $stmt->execute([$sku]);
        }
        
        return $sku;
    }
    
    function generateSlug($text) {
        $slug = strtolower($text);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return substr($slug, 0, 100);
    }
    
    $testProductName = "Test Product " . time();
    $sku = generateUniqueSKU($db, $testProductName);
    $slug = generateSlug($testProductName);
    
    echo "<p>Generated SKU: <strong>$sku</strong></p>";
    echo "<p>Generated Slug: <strong>$slug</strong></p>";
    
    $sql = "INSERT INTO products (
                sku, name, slug, description, price, original_price, category_id, 
                stock_quantity, unit, featured, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        $sku,
        $testProductName,
        $slug,
        'Test product description',
        99.99,
        109.99,
        1, // Category ID
        10, // Stock
        'piece',
        0, // Not featured
        'active'
    ]);
    
    if ($result) {
        $productId = $db->lastInsertId();
        echo "<p>✅ Test product created successfully with ID: $productId</p>";
        
        // Clean up - delete the test product
        $deleteStmt = $db->prepare("DELETE FROM products WHERE product_id = ?");
        $deleteStmt->execute([$productId]);
        echo "<p>🗑️ Test product cleaned up</p>";
        
    } else {
        echo "<p>❌ Failed to create test product</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>Stack trace:\n" . $e->getTraceAsString() . "</pre>";
}
?>

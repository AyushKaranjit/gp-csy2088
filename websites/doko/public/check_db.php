<?php
echo 'Script started' . PHP_EOL;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Http/ApiResponse.php';

echo 'Required files loaded' . PHP_EOL;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    echo 'Database connected' . PHP_EOL;

    // Check product 26
    $stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = ?');
    $stmt->execute([26]);
    $product = $stmt->fetch();
    echo 'Product 26 data:' . PHP_EOL;
    print_r($product);

    // Check product_images for product 26
    $stmt = $pdo->prepare('SELECT * FROM product_images WHERE product_id = ?');
    $stmt->execute([26]);
    $images = $stmt->fetchAll();
    echo 'Product 26 images:' . PHP_EOL;
    print_r($images);

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>

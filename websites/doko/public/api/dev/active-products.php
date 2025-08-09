<?php
/**
 * Development Utility Endpoint: List Active Products
 * Provides a lightweight JSON dump of currently active products (id, name, stock, status)
 * to help debug 404 / product-not-found cart & wishlist issues.
 *
 * NOTE: Keep output minimal; NOT for public consumption. Consider restricting or removing in production.
 */
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method('GET');

try {
    $db = Database::getInstance();
    $pk = schema_products_pk();
    $sql = "SELECT {$pk} AS product_id, name, status, stock_quantity, price, updated_at FROM products WHERE status='active' ORDER BY name ASC LIMIT 500";
    $stmt = $db->execute($sql);
    $rows = $stmt->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'product_id' => (int)$r['product_id'],
            'name' => $r['name'],
            'status' => $r['status'],
            'stock_quantity' => isset($r['stock_quantity']) ? (int)$r['stock_quantity'] : null,
            'price' => isset($r['price']) ? (float)$r['price'] : null,
            'updated_at' => $r['updated_at'] ?? null
        ];
    }
    ApiResponse::success([
        'count' => count($out),
        'products' => $out
    ]);
} catch (Exception $e) {
    error_log('Active products dev endpoint error: ' . $e->getMessage());
    ApiResponse::error('Failed to load active products', 500);
}
?>

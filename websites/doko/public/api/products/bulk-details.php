<?php
// Bulk product details lookup for cart/wishlist hydration
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method('POST');

try {
    $db = db();
    $productsPk = schema_products_pk();
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $ids = isset($body['product_ids']) && is_array($body['product_ids']) ? $body['product_ids'] : [];
    $ids = array_values(array_unique(array_filter($ids, fn($v)=>is_numeric($v) && $v>0)));
    if (!$ids) {
        ApiResponse::success(['items'=>[]]);
        return;
    }
    if (count($ids) > 100) $ids = array_slice($ids,0,100); // safety limit
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT p.$productsPk AS product_id, p.name, p.price, p.stock_quantity, p.status, COALESCE(pi.image_url, '') AS image_url
            FROM products p
            LEFT JOIN product_images pi ON p.$productsPk = pi.product_id AND pi.is_primary = 1
            WHERE p.$productsPk IN ($placeholders)";
    $stmt = $db->execute($sql, $ids);
    $rows = $stmt->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $image_url = '/images/default-product.jpg';
        $candidate = $r['image_url'];
        if ($candidate) {
            if (preg_match('#^https?://#i', $candidate)) {
                $image_url = $candidate;
            } elseif (file_exists(__DIR__ . '/../../uploads/' . $candidate)) {
                $image_url = '/uploads/' . $candidate;
            }
        }
        $out[] = [
            'product_id' => (int)$r['product_id'],
            'name' => $r['name'],
            'price' => (float)$r['price'],
            'stock' => (int)$r['stock_quantity'],
            'status' => $r['status'],
            'image' => $image_url
        ];
    }
    ApiResponse::success(['items'=>$out]);
} catch (Throwable $e) {
    error_log('bulk-details error: '.$e->getMessage());
    ApiResponse::error('Failed to load product details', 500);
}
?>

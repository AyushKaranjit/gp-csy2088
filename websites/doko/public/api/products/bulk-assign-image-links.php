<?php
/**
 * Bulk Assign Image Links API
 * Assigns a generated external placeholder image URL to every product lacking a primary image.
 * Query params: ?token=YOUR_SECRET (simple guard) & base=https://example.com/images/{id}.jpg (optional pattern)
 * Pattern tokens: {id}, {slug}
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../../config/database.php';
require_once '../../../src/Controllers/AuthController.php';

session_start();
$auth = new AuthController();
if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Simple token check (optional environment variable BULK_IMAGE_TOKEN)
$provided = $_GET['token'] ?? '';
$expected = getenv('BULK_IMAGE_TOKEN') ?: '';
if ($expected && hash_equals($expected, $provided) === false) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$pattern = $_GET['base'] ?? 'https://via.placeholder.com/600x600.png?text=Product+{id}';

try {
    $pdo = Database::getInstance()->getConnection();
    // Find products without primary image
    $sql = "SELECT p.product_id, p.slug
            FROM products p
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
            WHERE pi.image_id IS NULL";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;
    foreach ($rows as $row) {
        $url = str_replace(['{id}', '{slug}'], [$row['product_id'], $row['slug'] ?? $row['product_id']], $pattern);
        if (!preg_match('#^https?://#i', $url)) continue; // ensure absolute
        $ins = $pdo->prepare('INSERT INTO product_images (product_id, image_url, is_primary, sort_order) VALUES (?, ?, 1, 0)');
        $ins->execute([$row['product_id'], $url]);
        $count++;
    }

    echo json_encode(['success' => true, 'assigned' => $count, 'pattern' => $pattern]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

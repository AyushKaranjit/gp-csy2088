<?php
/**
 * Product Image Link API
 * Allows an admin to attach an external (absolute) image URL as the primary image for a product.
 * Accepts JSON: { "product_id": 123, "image_url": "https://..." }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../../../config/database.php';
require_once '../../../src/Controllers/AuthController.php';

session_start();
$auth = new AuthController();
if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid JSON');
    }
    $product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
    $image_url = trim($input['image_url'] ?? '');

    if ($product_id <= 0 || empty($image_url)) {
        throw new Exception('product_id and image_url are required');
    }
    if (!preg_match('#^https?://#i', $image_url)) {
        throw new Exception('image_url must be an absolute http(s) URL');
    }

    $db = Database::getInstance()->getConnection();
    // Ensure product exists
    $stmt = $db->prepare('SELECT product_id FROM products WHERE product_id = ?');
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Product not found');
    }

    // Demote existing primary image if any
    $db->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = ? AND is_primary = 1')->execute([$product_id]);

    // Insert new record (store absolute URL directly)
    $ins = $db->prepare('INSERT INTO product_images (product_id, image_url, is_primary, sort_order) VALUES (?, ?, 1, 0)');
    $ins->execute([$product_id, $image_url]);

    echo json_encode(['success' => true, 'message' => 'Primary image link set', 'product_id' => $product_id, 'image_url' => $image_url]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method('POST');
csrf_check();

try {
    $data = json_input();
    $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    $rating = isset($data['rating']) ? (int)$data['rating'] : 0;
    $title = trim($data['title'] ?? '');
    $review = trim($data['review'] ?? '');

    if ($product_id <= 0 || $rating < 1 || $rating > 5) { ApiResponse::error('Invalid input', 400); return; }

    $auth = auth_controller();
    if (!$auth->isLoggedIn() || !$auth->isCustomer()) { ApiResponse::error('Authentication required', 401); return; }
    $userId = $_SESSION['user_id'];

    $db = db();
    $stmt = $db->prepare("INSERT INTO product_reviews (product_id, user_id, rating, title, review, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->execute([$product_id, $userId, $rating, $title, $review]);
    $id = $db->lastInsertId();

    ApiResponse::success(['review_id' => (int)$id, 'message' => 'Review submitted and awaiting moderation']);
} catch (Exception $e) {
    error_log('Reviews create error: ' . $e->getMessage());
    ApiResponse::error('Failed to create review', 500);
}

?>

<?php
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method('PUT');
csrf_check();

try {
    $data = json_input();
    $review_id = isset($data['review_id']) ? (int)$data['review_id'] : 0;
    $rating = isset($data['rating']) ? (int)$data['rating'] : 0;
    $title = trim($data['title'] ?? '');
    $review = trim($data['review'] ?? '');

    if ($review_id <= 0 || $rating < 1 || $rating > 5) { ApiResponse::error('Invalid input', 400); return; }

    $auth = auth_controller();
    if (!$auth->isLoggedIn()) { ApiResponse::error('Authentication required', 401); return; }

    $db = db();
    $existing = $db->fetchRow('SELECT * FROM product_reviews WHERE review_id = ?', [$review_id]);
    if (!$existing) { ApiResponse::error('Review not found', 404); return; }

    // Only admin or owner may edit
    $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $existing['user_id'];
    if (!($auth->isAdmin() || $isOwner)) { ApiResponse::error('Permission denied', 403); return; }

    $status = $auth->isAdmin() ? ($data['status'] ?? $existing['status']) : 'pending';

    $stmt = $db->prepare('UPDATE product_reviews SET rating = ?, title = ?, review = ?, status = ?, updated_at = NOW() WHERE review_id = ?');
    $stmt->execute([$rating, $title, $review, $status, $review_id]);

    ApiResponse::success(['message' => 'Review updated']);
} catch (Exception $e) {
    error_log('Reviews update error: ' . $e->getMessage());
    ApiResponse::error('Failed to update review', 500);
}

?>

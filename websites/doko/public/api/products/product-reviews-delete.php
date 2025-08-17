<?php
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method('DELETE');
csrf_check();

try {
    // For DELETE, accept JSON body or query param
    $id = 0;
    $input = json_input();
    if (!empty($input['review_id'])) $id = (int)$input['review_id'];
    if (!$id && isset($_GET['review_id'])) $id = (int)$_GET['review_id'];
    if ($id <= 0) { ApiResponse::error('review_id required', 400); return; }

    $auth = auth_controller();
    if (!$auth->isLoggedIn()) { ApiResponse::error('Authentication required', 401); return; }

    $db = db();
    $existing = $db->fetchRow('SELECT * FROM product_reviews WHERE review_id = ?', [$id]);
    if (!$existing) { ApiResponse::error('Review not found', 404); return; }

    $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $existing['user_id'];
    if (!($auth->isAdmin() || $isOwner)) { ApiResponse::error('Permission denied', 403); return; }

    $db->execute('DELETE FROM product_reviews WHERE review_id = ?', [$id]);
    ApiResponse::success(['message' => 'Review deleted']);
} catch (Exception $e) {
    error_log('Reviews delete error: ' . $e->getMessage());
    ApiResponse::error('Failed to delete review', 500);
}

?>

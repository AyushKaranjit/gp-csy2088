<?php
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method('GET');

try {
    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
    if ($product_id <= 0) { ApiResponse::error('product_id required', 400); return; }

    $db = db();
    $auth = auth_controller();
    $currentUserId = $auth->isLoggedIn() ? ($_SESSION['user_id'] ?? null) : null;
    $isAdmin = is_object($auth) && $auth->isAdmin();

    // Admins should be able to see all reviews for moderation; others see approved + their own pending
    if ($isAdmin) {
        $query = "SELECT r.*, u.username, u.profile_image FROM product_reviews r LEFT JOIN users u ON r.user_id = u.user_id
                  WHERE r.product_id = ? ORDER BY r.created_at DESC";
        $params = [$product_id];
    } else {
        $query = "SELECT r.*, u.username, u.profile_image FROM product_reviews r LEFT JOIN users u ON r.user_id = u.user_id
                  WHERE r.product_id = ? AND (r.status = 'approved'";
        $params = [$product_id];
        if ($currentUserId) { $query .= " OR r.user_id = ?"; $params[] = $currentUserId; }
        $query .= ") ORDER BY r.created_at DESC";
    }

    $rows = $db->fetchAll($query, $params);

    // Attach permission flags and normalize output
    $out = [];
    foreach ($rows as $r) {
        $r['can_edit'] = ($currentUserId && isset($r['user_id']) && $currentUserId == $r['user_id']);
        $r['can_delete'] = $r['can_edit'];
        $r['can_moderate'] = $isAdmin;
        $out[] = $r;
    }

    ApiResponse::success(['reviews' => $out, 'current_user_id' => $currentUserId, 'is_admin' => $isAdmin]);
} catch (Exception $e) {
    error_log('Reviews list error: ' . $e->getMessage());
    ApiResponse::error('Failed to load reviews', 500);
}

?>

<?php
/**
 * Admin Users (Legacy Compatibility Endpoint)
 * NOTE: Prefer /api/users/users-list.php which offers pagination & filtering.
 * This endpoint now simply proxies a minimal paginated list and will be removed.
 */
require_once __DIR__ . '/../_bootstrap.php';

use Doko\Http\ApiResponse;

require_method('GET');
ensure_session();
$auth = auth_controller();
if (!$auth->isLoggedIn()) {
    ApiResponse::error('Authentication required', 401);
    exit;
}
if (!$auth->isAdmin()) {
    ApiResponse::error('Access denied', 403);
    exit;
}

$page = int_param('page', 1, 1);
$perPage = int_param('per_page', 25, 1, 100);
$offset = ($page - 1) * $perPage;

try {
    $database = db();
    $pdo = $database->getConnection();

    $total = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stmt = $pdo->prepare("SELECT user_id, username, email, role, status, created_at FROM users ORDER BY user_id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ApiResponse::success([
        'users' => $users,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => (int)ceil($total / $perPage)
        ],
        'meta' => [
            'deprecated' => true,
            'replacement' => '/api/users/users-list.php'
        ]
    ]);
} catch (Throwable $e) {
    ApiResponse::error('Failed to load users', 500, ['error' => $e->getMessage()]);
}

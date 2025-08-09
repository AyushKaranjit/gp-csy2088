<?php
// Refactored change-password endpoint using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method('POST');

try {
    ensure_session();
    if (empty($_SESSION['user_id'])) { ApiResponse::error('User not logged in', 401); return; }
    $input = json_input();
    if (!$input || !isset($input['current_password']) || !isset($input['new_password'])) { ApiResponse::error('Current password and new password are required', 400); return; }
    $db = db();
    $pdo = $db->getConnection();
    $stmt = $pdo->prepare('SELECT password FROM users WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) { ApiResponse::error('User not found', 404); return; }
    if (!password_verify($input['current_password'], $user['password'])) { ApiResponse::error('Current password is incorrect', 400); return; }
    if (strlen($input['new_password']) < 6) { ApiResponse::error('New password must be at least 6 characters long', 400); return; }
    $hashed = password_hash($input['new_password'], PASSWORD_DEFAULT);
    $upd = $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?');
    $ok = $upd->execute([$hashed, $_SESSION['user_id']]);
    if (!$ok) { ApiResponse::error('Failed to update password', 500); return; }
    ApiResponse::success(['message' => 'Password changed successfully']);
} catch (Throwable $e) {
    error_log('change-password error: '.$e->getMessage());
    ApiResponse::error('Failed to change password', 500, ['exception' => $e->getMessage()]);
}

<?php
// Password update endpoint (backwards-compatible with change-password)
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method('POST');

try {
    ensure_session();
    if (empty($_SESSION['user_id'])) {
        ApiResponse::error('Authentication required', 401);
    }
    // Support form-data or JSON
    $input = [];
    if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $decoded = json_input();
        if (is_array($decoded)) $input = $decoded;
    } else {
        foreach ($_POST as $k=>$v) { $input[$k]=trim((string)$v); }
    }
    $current = $input['current_password'] ?? $input['current'] ?? null;
    $new = $input['new_password'] ?? $input['new'] ?? null;
    if (!$current || !$new) {
        ApiResponse::error('Current password and new password are required', 400);
    }
    if (strlen($new) < 6) {
        ApiResponse::error('New password must be at least 6 characters', 400);
    }
    $pdo = db()->getConnection();
    $stmt = $pdo->prepare('SELECT password FROM users WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { ApiResponse::error('User not found', 404); }
    if (!password_verify($current, $row['password'])) {
        ApiResponse::error('Current password incorrect', 400);
    }
    $hashed = password_hash($new, PASSWORD_DEFAULT);
    $up = $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?');
    if (!$up->execute([$hashed, $_SESSION['user_id']])) {
        ApiResponse::error('Failed to update password', 500);
    }
    ApiResponse::success(['message' => 'Password updated']);
} catch (Throwable $e) {
    error_log('password-update error: '.$e->getMessage());
    ApiResponse::error('Failed to update password', 500, ['exception' => $e->getMessage()]);
}

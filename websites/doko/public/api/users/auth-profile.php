<?php
// Refactored auth-profile (update basic profile fields) using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method('POST');

try {
    ensure_session();
    $auth = auth_controller();
    if (!$auth->isLoggedIn()) { ApiResponse::error('User not logged in', 401); }
    $input = json_input();
    $allowed = ['first_name','last_name','phone'];
    $updates = [];
    foreach ($allowed as $f) { if (array_key_exists($f,$input)) { $val = trim((string)$input[$f]); $updates[$f]=$val; $_SESSION[$f]=$val; } }
    if (!$updates) { ApiResponse::success(['message'=>'No changes made']); }
    $db = db();
    $setParts=[];$params=[];
    foreach ($updates as $k=>$v) { $setParts[] = "$k = ?"; $params[] = $v; }
    $params[] = $_SESSION['user_id'];
    $sql = 'UPDATE users SET '.implode(',', $setParts).', updated_at = NOW() WHERE user_id = ?';
    $db->execute($sql,$params);
    ApiResponse::success(['message' => 'Profile updated successfully']);
} catch (Throwable $e) {
    error_log('auth-profile error: '.$e->getMessage());
    ApiResponse::error('Failed to update profile', 500, ['exception'=>$e->getMessage()]);
}

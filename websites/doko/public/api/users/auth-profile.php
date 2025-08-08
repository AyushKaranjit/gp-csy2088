<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../../src/Controllers/AuthController.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']);
    exit;
}
$auth = new AuthController();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'User not logged in']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$allowed = ['first_name','last_name','phone'];
$updates = [];
foreach ($allowed as $f) { if (isset($input[$f])) { $updates[$f] = trim($input[$f]); } }
if (!$updates) { echo json_encode(['success'=>true,'message'=>'No changes made']); exit; }
try {
    $db = Database::getInstance();
    $set = [];$params=[];
    foreach ($updates as $k=>$v) { $set[] = "$k = ?"; $params[] = $v; $_SESSION[$k] = $v; }
    $params[] = $_SESSION['user_id'];
    $sql = 'UPDATE users SET '.implode(',', $set).', updated_at=NOW() WHERE user_id = ?';
    $db->execute($sql,$params);
    echo json_encode(['success'=>true,'message'=>'Profile updated successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Failed to update profile']);
}
?>

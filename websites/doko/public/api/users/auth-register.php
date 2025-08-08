<?php
// Backwards compatibility wrapper for tests expecting /api/users/auth-register.php
// Provide minimal implementation if actual file missing
$target = __DIR__ . '/register.php';
if (file_exists($target)) {
	require $target;
} else {
	header('Content-Type: application/json');
	http_response_code(500);
	echo json_encode(['success'=>false,'message'=>'Registration endpoint missing']);
}
?>

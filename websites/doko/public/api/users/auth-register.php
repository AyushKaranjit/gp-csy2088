<?php
// Deprecated wrapper for /api/users/register.php
$target = __DIR__ . '/register.php';
if (file_exists($target)) {
	require $target; // register.php handles output
} else {
	header('Content-Type: application/json');
	http_response_code(500);
	echo json_encode(['success'=>false,'message'=>'Registration endpoint missing']);
}
// Wrapper retained temporarily for compatibility.
?>

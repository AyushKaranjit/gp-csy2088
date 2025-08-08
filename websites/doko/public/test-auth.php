<?php
// Deprecated debug script removed. 410 Gone
 http_response_code(410);
 header('Content-Type: application/json');
 echo json_encode(['success' => false, 'message' => 'test-auth.php has been removed']);
 exit;
?>

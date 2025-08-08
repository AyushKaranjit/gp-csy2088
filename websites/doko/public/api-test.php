<?php
// Deprecated API test script removed. Returning 410 Gone.
http_response_code(410);
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'api-test.php has been removed']);
exit;
?>

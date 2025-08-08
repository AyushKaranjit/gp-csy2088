<?php
// Manual functionality test page has been removed during cleanup.
http_response_code(410);
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'test-functionality.php has been removed']);
exit;
?>

<?php
// Deprecated backup cart add endpoint. Use add.php.
header('Content-Type: application/json');
http_response_code(410);
echo json_encode([
    'success' => false,
    'message' => 'Deprecated endpoint. Use add.php',
    'deprecated' => true,
    'replacement' => 'add.php'
]);
?>

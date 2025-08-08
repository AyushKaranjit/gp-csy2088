<?php
// Deprecated simple wishlist endpoint. Use wishlist.php.
header('Content-Type: application/json');
http_response_code(410);
echo json_encode([
    'success' => false,
    'message' => 'Deprecated endpoint. Use wishlist.php',
    'deprecated' => true,
    'replacement' => 'wishlist.php'
]);
?>

<?php
// Deprecated wishlist get endpoint. Use wishlist.php (GET) instead.
header('Content-Type: application/json');
http_response_code(410);
echo json_encode([
    'success' => false,
    'message' => 'Deprecated endpoint. Use wishlist.php',
    'deprecated' => true,
    'replacement' => 'wishlist.php'
]);
?>

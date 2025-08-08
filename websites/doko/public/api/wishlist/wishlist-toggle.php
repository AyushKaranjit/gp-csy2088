<?php
// Deprecated wishlist toggle endpoint. Use /api/wishlist/wishlist.php (POST with action=toggle)
header('Content-Type: application/json');
http_response_code(410);
echo json_encode([
    'success' => false,
    'message' => 'Deprecated endpoint. Use wishlist.php (action=toggle)',
    'deprecated' => true,
    'replacement' => 'wishlist.php'
]);
?>

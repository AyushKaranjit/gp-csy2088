<?php
// Deprecated monolithic cart endpoint. Use granular endpoints instead:
//  GET    cart-get.php
//  POST   add.php / update.php
//  PUT    update.php
//  DELETE remove.php / clear.php
header('Content-Type: application/json');
http_response_code(410);
echo json_encode([
    'success' => false,
    'message' => 'Deprecated cart endpoint. Use cart-get.php, add.php, update.php, remove.php, clear.php.',
    'deprecated' => true,
    'replacements' => [
        'get' => 'cart-get.php',
        'add' => 'add.php',
        'update' => 'update.php',
        'remove' => 'remove.php',
        'clear' => 'clear.php'
    ]
]);
?>

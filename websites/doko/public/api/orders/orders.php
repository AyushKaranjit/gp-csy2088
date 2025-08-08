<?php
// Deprecated monolithic orders endpoint.
// Use: 
//  GET    /api/orders/orders-list.php         (already refactored)
//  GET    /api/orders/order-detail.php?id=ID  (refactored)
//  POST   /api/users/customer-orders.php      (order placement)
//  POST   /api/orders/update-status.php       (admin status change)
//  POST   /api/orders/cancel-order.php        (cancel)
http_response_code(410);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'This endpoint is deprecated. Use specialized order endpoints.',
    'endpoints' => [
        'list' => 'orders-list.php',
        'detail' => 'order-detail.php',
        'place' => '../users/customer-orders.php',
        'status_update' => 'update-status.php',
        'cancel' => 'cancel-order.php'
    ]
]);
?>

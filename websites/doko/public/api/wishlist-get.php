<?php
require_once '../../config/database.php';
require_once '../../src/Controllers/AuthController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

try {
    $authController = new AuthController();
    
    if (!$authController->isLoggedIn()) {
        echo json_encode(['success' => true, 'count' => 0, 'items' => []]);
        exit;
    }

    $user = $authController->getCurrentUser();
    if (!$user) {
        echo json_encode(['success' => true, 'count' => 0, 'items' => []]);
        exit;
    }

    $userId = $user['id'];

    // Check if database connection is available
    if (!isset($pdo)) {
        echo json_encode(['success' => true, 'count' => 0, 'items' => []]);
        exit;
    }

    // Get wishlist count and items
    $query = $pdo->prepare("
        SELECT 
            w.id,
            w.product_id,
            p.name,
            p.price,
            p.image_url,
            w.created_at
        FROM wishlist w 
        LEFT JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    
    $query->execute([$userId]);
    $items = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($items),
        'items' => $items
    ]);

} catch (Exception $e) {
    error_log("Wishlist Get Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>

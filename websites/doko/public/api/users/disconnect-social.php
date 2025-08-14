<?php
require_once '../_bootstrap.php';
require_once '../../src/Controllers/AuthController.php';

header('Content-Type: application/json');

$auth = new AuthController();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['provider'])) {
        echo json_encode(['success' => false, 'error' => 'Provider is required']);
        exit;
    }
    
    $provider = strtolower(trim($input['provider']));
    
    if (!in_array($provider, ['google'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid provider']);
        exit;
    }
    
    // Check if user has password or other login method
    $stmt = $pdo->prepare("SELECT password, google_id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    
    // Check if disconnecting would leave user with no login method
    $has_password = !empty($user['password']);
    $has_google = !empty($user['google_id']);
    
    if (!$has_password && $provider === 'google' && $has_google) {
        echo json_encode([
            'success' => false, 
            'error' => 'Cannot disconnect your only login method. Please set a password first.'
        ]);
        exit;
    }
    
    // Disconnect the social account
    $column = $provider . '_id';
    $stmt = $pdo->prepare("UPDATE users SET {$column} = NULL WHERE id = ?");
    $stmt->execute([$user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => ucfirst($provider) . ' account disconnected successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Disconnect social account error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to disconnect account']);
}
?>

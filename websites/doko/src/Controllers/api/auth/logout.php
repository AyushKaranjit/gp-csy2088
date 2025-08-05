<?php
/**
 * User Logout API
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../../../config/database.php';

try {
    session_start();
    
    if (isset($_SESSION['user_id'])) {
        $database = Database::getInstance();
        $conn = $database->getConnection();
        
        // Mark session as inactive
        $sessionId = session_id();
        $updateQuery = "UPDATE user_sessions SET is_active = FALSE WHERE session_id = :session_id";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':session_id', $sessionId);
        $updateStmt->execute();
    }
    
    // Destroy session
    session_unset();
    session_destroy();
    
    // Clear session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful'
    ]);
    
} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during logout'
    ]);
}
?>

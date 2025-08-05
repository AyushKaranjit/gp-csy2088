<?php
/**
 * User Registration API
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

require_once '../../../Controllers/AuthController.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }
    
    // Required fields
    $required_fields = ['username', 'email', 'password', 'first_name', 'last_name'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => ucfirst($field) . ' is required'
            ]);
            exit;
        }
    }
    
    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit;
    }
    
    // Validate password strength
    if (strlen($input['password']) < 6) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 6 characters long'
        ]);
        exit;
    }
    
    // Validate username (alphanumeric and underscore only)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $input['username'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username can only contain letters, numbers, and underscores'
        ]);
        exit;
    }
    
    if (strlen($input['username']) < 3 || strlen($input['username']) > 50) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username must be between 3 and 50 characters long'
        ]);
        exit;
    }
    
    // Register user
    $auth = new AuthController();
    $result = $auth->register($input);
    
    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    error_log("Registration API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during registration. Please try again.'
    ]);
}
?>

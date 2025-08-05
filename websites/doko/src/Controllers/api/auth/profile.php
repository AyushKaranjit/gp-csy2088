<?php
/**
 * User Profile API
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../../../config/database.php';

try {
    session_start();
    
    if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required'
        ]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    // Get user profile data
    $query = "SELECT u.user_id, u.username, u.email, u.first_name, u.last_name, u.phone, 
                     u.date_of_birth, u.gender, u.profile_image, u.role, u.status, 
                     u.email_verified, u.phone_verified, u.created_at,
                     COUNT(DISTINCT o.order_id) as total_orders,
                     COALESCE(SUM(o.total_amount), 0) as total_spent
              FROM users u
              LEFT JOIN orders o ON u.user_id = o.user_id AND o.status = 'delivered'
              WHERE u.user_id = :user_id
              GROUP BY u.user_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    $user = $stmt->fetch();
    
    // Get user addresses
    $addressQuery = "SELECT address_id, address_type, address_label, street_address, city, state, 
                            postal_code, country, landmark, is_default
                     FROM user_addresses 
                     WHERE user_id = :user_id 
                     ORDER BY is_default DESC, created_at DESC";
    
    $addressStmt = $conn->prepare($addressQuery);
    $addressStmt->bindParam(':user_id', $user_id);
    $addressStmt->execute();
    $addresses = $addressStmt->fetchAll();
    
    // Format response
    $profileData = [
        'user_id' => (int)$user['user_id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'phone' => $user['phone'],
        'date_of_birth' => $user['date_of_birth'],
        'gender' => $user['gender'],
        'profile_image' => $user['profile_image'],
        'role' => $user['role'],
        'status' => $user['status'],
        'email_verified' => (bool)$user['email_verified'],
        'phone_verified' => (bool)$user['phone_verified'],
        'created_at' => $user['created_at'],
        'statistics' => [
            'total_orders' => (int)$user['total_orders'],
            'total_spent' => (float)$user['total_spent']
        ],
        'addresses' => array_map(function($addr) {
            return [
                'address_id' => (int)$addr['address_id'],
                'address_type' => $addr['address_type'],
                'address_label' => $addr['address_label'],
                'street_address' => $addr['street_address'],
                'city' => $addr['city'],
                'state' => $addr['state'],
                'postal_code' => $addr['postal_code'],
                'country' => $addr['country'],
                'landmark' => $addr['landmark'],
                'is_default' => (bool)$addr['is_default']
            ];
        }, $addresses)
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $profileData
    ]);
    
} catch (Exception $e) {
    error_log("Profile error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching profile data'
    ]);
}
?>

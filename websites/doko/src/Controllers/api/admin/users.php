<?php
/**
 * User Management API - Admin Only
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../Controllers/AuthController.php';
require_once '../../../config/database.php';

try {
    // Check authentication
    $auth = new AuthController();
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required'
        ]);
        exit;
    }
    
    // Check if user is admin
    if (!$auth->isAdmin()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Admin access required'
        ]);
        exit;
    }
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetUsers($conn);
            break;
        case 'PUT':
            handleUpdateUser($conn);
            break;
        case 'DELETE':
            handleDeleteUser($conn);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("User management error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

function handleGetUsers($conn) {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
    $role = isset($_GET['role']) ? $_GET['role'] : null;
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    $offset = ($page - 1) * $limit;
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if ($role) {
        $whereConditions[] = "u.role = :role";
        $params[':role'] = $role;
    }
    
    if ($status) {
        $whereConditions[] = "u.status = :status";
        $params[':status'] = $status;
    }
    
    if ($search) {
        $whereConditions[] = "(u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search OR u.username LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $query = "SELECT u.user_id, u.username, u.email, u.first_name, u.last_name, u.phone,
                     u.role, u.status, u.email_verified, u.phone_verified, u.last_login,
                     u.created_at, u.updated_at,
                     COUNT(DISTINCT o.order_id) as total_orders,
                     COALESCE(SUM(o.total_amount), 0) as total_spent,
                     COUNT(DISTINCT ua.address_id) as address_count
              FROM users u
              LEFT JOIN orders o ON u.user_id = o.user_id AND o.status = 'delivered'
              LEFT JOIN user_addresses ua ON u.user_id = ua.user_id
              {$whereClause}
              GROUP BY u.user_id
              ORDER BY u.created_at DESC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $users = $stmt->fetchAll();
    
    // Format user data
    foreach ($users as &$user) {
        $user['total_orders'] = (int)$user['total_orders'];
        $user['total_spent'] = (float)$user['total_spent'];
        $user['address_count'] = (int)$user['address_count'];
        $user['email_verified'] = (bool)$user['email_verified'];
        $user['phone_verified'] = (bool)$user['phone_verified'];
        
        // Remove sensitive data
        unset($user['password']);
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM users u {$whereClause}";
    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    
    // Get user statistics
    $statsQuery = "SELECT 
                       COUNT(*) as total_users,
                       SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as customers,
                       SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                       SUM(CASE WHEN role = 'manager' THEN 1 ELSE 0 END) as managers,
                       SUM(CASE WHEN role = 'vendor' THEN 1 ELSE 0 END) as vendors,
                       SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
                       SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_users,
                       SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended_users,
                       SUM(CASE WHEN email_verified = 1 THEN 1 ELSE 0 END) as verified_emails,
                       SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as new_users_week,
                       SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_users_month
                   FROM users";
    $statsStmt = $conn->prepare($statsQuery);
    $statsStmt->execute();
    $stats = $statsStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'data' => $users,
        'statistics' => $stats,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => (int)$total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

function handleUpdateUser($conn) {
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    // Check if user exists
    $checkQuery = "SELECT user_id FROM users WHERE user_id = :user_id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':user_id', $userId);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }
    
    // Prevent admin from changing their own role or status
    session_start();
    if ($userId == $_SESSION['user_id']) {
        if (isset($input['role']) || isset($input['status'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cannot change your own role or status']);
            return;
        }
    }
    
    // Build update query
    $updateFields = [];
    $params = [':user_id' => $userId];
    
    $allowedFields = [
        'first_name', 'last_name', 'phone', 'role', 'status', 
        'email_verified', 'phone_verified', 'date_of_birth', 'gender'
    ];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = :$field";
            $params[":$field"] = $input[$field];
        }
    }
    
    // Handle password update separately
    if (isset($input['password']) && !empty($input['password'])) {
        if (strlen($input['password']) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
            return;
        }
        
        $updateFields[] = "password = :password";
        $params[':password'] = password_hash($input['password'], PASSWORD_DEFAULT);
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        return;
    }
    
    $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
    
    $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update user');
    }
}

function handleDeleteUser($conn) {
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        return;
    }
    
    // Check if user exists
    $checkQuery = "SELECT user_id, role FROM users WHERE user_id = :user_id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':user_id', $userId);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }
    
    $user = $checkStmt->fetch();
    
    // Prevent admin from deleting themselves
    session_start();
    if ($userId == $_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
        return;
    }
    
    // Prevent deleting other admins (only super admin should be able to)
    if ($user['role'] === 'admin') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot delete admin accounts']);
        return;
    }
    
    // Check if user has orders
    $ordersQuery = "SELECT COUNT(*) FROM orders WHERE user_id = :user_id";
    $ordersStmt = $conn->prepare($ordersQuery);
    $ordersStmt->bindParam(':user_id', $userId);
    $ordersStmt->execute();
    $orderCount = $ordersStmt->fetchColumn();
    
    if ($orderCount > 0) {
        // Soft delete - change status to inactive instead of deleting
        $query = "UPDATE users SET status = 'inactive', updated_at = CURRENT_TIMESTAMP WHERE user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'User account deactivated (has existing orders)'
            ]);
        } else {
            throw new Exception('Failed to deactivate user');
        }
    } else {
        // Hard delete - no orders exist
        $conn->beginTransaction();
        
        try {
            // Delete user addresses first
            $deleteAddressQuery = "DELETE FROM user_addresses WHERE user_id = :user_id";
            $deleteAddressStmt = $conn->prepare($deleteAddressQuery);
            $deleteAddressStmt->bindParam(':user_id', $userId);
            $deleteAddressStmt->execute();
            
            // Delete user sessions
            $deleteSessionQuery = "DELETE FROM user_sessions WHERE user_id = :user_id";
            $deleteSessionStmt = $conn->prepare($deleteSessionQuery);
            $deleteSessionStmt->bindParam(':user_id', $userId);
            $deleteSessionStmt->execute();
            
            // Delete user
            $deleteUserQuery = "DELETE FROM users WHERE user_id = :user_id";
            $deleteUserStmt = $conn->prepare($deleteUserQuery);
            $deleteUserStmt->bindParam(':user_id', $userId);
            $deleteUserStmt->execute();
            
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
}
?>

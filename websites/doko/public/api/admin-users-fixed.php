<?php
/**
 * Admin Users Management API
 * Handle user CRUD operations for admin panel
 */

// CORS headers
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

try {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../src/Controllers/AuthController.php';

    // Verify admin authentication
    $auth = new AuthController();
    if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    $db = Database::getInstance()->getConnection();
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            handleGetUsers($db);
            break;
        case 'POST':
            handleCreateUser($db);
            break;
        case 'PUT':
            handleUpdateUser($db);
            break;
        case 'DELETE':
            handleDeleteUser($db);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Admin Users API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

function handleGetUsers($db) {
    try {
        if (isset($_GET['id'])) {
            // Get specific user
            $user_id = (int)$_GET['id'];
            $query = "SELECT user_id, username, email, first_name, last_name, phone, address, 
                            role, status, email_verified, created_at, updated_at,
                            CONCAT(first_name, ' ', last_name) as full_name
                     FROM users WHERE user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found']);
                return;
            }
            
            echo json_encode(['success' => true, 'data' => $user]);
        } else {
            // Get all users with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = ($page - 1) * $limit;
            
            $countQuery = "SELECT COUNT(*) as total FROM users";
            $stmt = $db->prepare($countQuery);
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $query = "SELECT user_id, username, email, first_name, last_name, phone, address, 
                            role, status, email_verified, created_at, updated_at,
                            CONCAT(first_name, ' ', last_name) as full_name
                     FROM users 
                     ORDER BY created_at DESC 
                     LIMIT ? OFFSET ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$limit, $offset]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true, 
                'data' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_records' => $total,
                    'per_page' => $limit
                ]
            ]);
        }
    } catch (Exception $e) {
        error_log("Get users error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch users']);
    }
}

function handleCreateUser($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required = ['first_name', 'last_name', 'username', 'email', 'password', 'role'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                return;
            }
        }
        
        // Check if username or email already exists
        $checkQuery = "SELECT user_id FROM users WHERE username = ? OR email = ?";
        $stmt = $db->prepare($checkQuery);
        $stmt->execute([$input['username'], $input['email']]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
        
        // Insert user
        $query = "INSERT INTO users (username, email, password, first_name, last_name, phone, 
                                   address, role, status, email_verified) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            $input['username'],
            $input['email'],
            $hashedPassword,
            $input['first_name'],
            $input['last_name'],
            $input['phone'] ?? null,
            $input['address'] ?? null,
            $input['role'],
            $input['status'] ?? 'active',
            $input['email_verified'] ?? 0
        ]);
        
        if ($result) {
            $user_id = $db->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'User created successfully', 'user_id' => $user_id]);
        } else {
            throw new Exception('Failed to create user');
        }
        
    } catch (Exception $e) {
        error_log("Create user error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create user']);
    }
}

function handleUpdateUser($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['user_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            return;
        }
        
        $user_id = (int)$input['user_id'];
        
        // Build dynamic update query
        $fields = [];
        $values = [];
        
        $allowedFields = ['first_name', 'last_name', 'username', 'email', 'phone', 'address', 'role', 'status', 'email_verified'];
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $values[] = $input[$field];
            }
        }
        
        // Handle password update if provided
        if (!empty($input['password'])) {
            $fields[] = "password = ?";
            $values[] = password_hash($input['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            return;
        }
        
        $values[] = $user_id;
        $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
        
        $stmt = $db->prepare($query);
        $result = $stmt->execute($values);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            throw new Exception('Failed to update user');
        }
        
    } catch (Exception $e) {
        error_log("Update user error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update user']);
    }
}

function handleDeleteUser($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['user_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            return;
        }
        
        $user_id = (int)$input['user_id'];
        
        // Check if user exists and is not the current admin
        $currentUser = $_SESSION['user_id'];
        if ($user_id == $currentUser) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
            return;
        }
        
        // Instead of deleting, we'll deactivate the user
        $query = "UPDATE users SET status = 'inactive' WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $result = $stmt->execute([$user_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'User deactivated successfully']);
        } else {
            throw new Exception('Failed to deactivate user');
        }
        
    } catch (Exception $e) {
        error_log("Delete user error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to deactivate user']);
    }
}
?>

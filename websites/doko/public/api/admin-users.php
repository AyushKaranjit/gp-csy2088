<?php
// Suppress PHP errors from appearing in JSON response
error_reporting(0);
ini_set('display_errors', 0);

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

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once '../../config/database.php';
require_once '../../template/config.php';
require_once '../../src/Controllers/AuthController.php';

// Verify admin authentication
$auth = new AuthController();
if (!$auth->isLoggedIn() || !$auth->hasAdminAccess()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Debug: Check if users table exists
    $tables_query = "SHOW TABLES LIKE 'users'";
    $tables_result = $db->query($tables_query);
    if ($tables_result->rowCount() === 0) {
        throw new Exception('Users table does not exist in database');
    }
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get all users or specific user
            if (isset($_GET['user_id'])) {
                $user_id = (int)$_GET['user_id'];
                
                $query = "SELECT user_id, username, email, first_name, last_name, phone, address, 
                                role, status, email_verified, created_at, updated_at,
                                CONCAT(first_name, ' ', last_name) as full_name
                         FROM users WHERE user_id = ?";
                $stmt = $db->execute($query, [$user_id]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                    exit;
                }
                
                // Remove sensitive data
                unset($user['password']);
                
                echo json_encode(['success' => true, 'data' => $user]);
                
            } else {
                // Get all users with filters
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                $offset = ($page - 1) * $limit;
                $role = isset($_GET['role']) ? $_GET['role'] : '';
                $status = isset($_GET['status']) ? $_GET['status'] : '';
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                
                $where_clauses = [];
                $params = [];
                
                if ($role) {
                    $where_clauses[] = 'role = ?';
                    $params[] = $role;
                }
                if ($status) {
                    $where_clauses[] = 'status = ?';
                    $params[] = $status;
                }
                if ($search) {
                    $where_clauses[] = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR username LIKE ?)';
                    $search_term = "%$search%";
                    $params[] = $search_term;
                    $params[] = $search_term;
                    $params[] = $search_term;
                    $params[] = $search_term;
                }
                
                $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
                
                $query = "SELECT user_id, username, email, first_name, last_name, phone, address,
                                role, status, email_verified, created_at,
                                CONCAT(first_name, ' ', last_name) as full_name
                         FROM users
                         {$where_sql}
                         ORDER BY created_at DESC
                         LIMIT ? OFFSET ?";
                
                $params[] = $limit;
                $params[] = $offset;
                
                $stmt = $db->execute($query, $params);
                $users = $stmt->fetchAll();
                
                // Get total count
                $count_query = "SELECT COUNT(*) as total FROM users {$where_sql}";
                $count_params = array_slice($params, 0, -2); // Remove limit and offset
                $count_stmt = $db->execute($count_query, $count_params);
                $total = $count_stmt->fetch()['total'];
                
                echo json_encode([
                    'success' => true,
                    'data' => $users,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $total,
                        'pages' => ceil($total / $limit)
                    ]
                ]);
            }
            break;
            
        case 'POST':
            // Create new user
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Username, email and password are required']);
                exit;
            }
            
            // Check if username or email already exists
            $query = "SELECT user_id FROM users WHERE username = ? OR email = ?";
            $stmt = $db->execute($query, [$input['username'], $input['email']]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
                exit;
            }
            
            // Hash password
            $password_hash = password_hash($input['password'], PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (username, email, password, first_name, last_name, phone, address, role, status, email_verified, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $db->execute($query, [
                $input['username'],
                $input['email'],
                $password_hash,
                $input['first_name'] ?? '',
                $input['last_name'] ?? '',
                $input['phone'] ?? null,
                $input['address'] ?? null,
                $input['role'] ?? 'customer',
                $input['status'] ?? 'active',
                isset($input['email_verified']) ? (int)$input['email_verified'] : 0
            ]);
            
            $user_id = $db->getConnection()->lastInsertId();
            
            echo json_encode(['success' => true, 'message' => 'User created successfully', 'user_id' => $user_id]);
            break;
            
        case 'PUT':
            // Update user
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['user_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'User ID is required']);
                exit;
            }
            
            // Check if user exists
            $query = "SELECT user_id FROM users WHERE user_id = ?";
            $stmt = $db->execute($query, [$input['user_id']]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found']);
                exit;
            }
            
            $updates = [];
            $params = [];
            
            if (isset($input['username'])) {
                $updates[] = 'username = ?';
                $params[] = $input['username'];
            }
            if (isset($input['email'])) {
                $updates[] = 'email = ?';
                $params[] = $input['email'];
            }
            if (isset($input['first_name'])) {
                $updates[] = 'first_name = ?';
                $params[] = $input['first_name'];
            }
            if (isset($input['last_name'])) {
                $updates[] = 'last_name = ?';
                $params[] = $input['last_name'];
            }
            if (isset($input['phone'])) {
                $updates[] = 'phone = ?';
                $params[] = $input['phone'];
            }
            if (isset($input['address'])) {
                $updates[] = 'address = ?';
                $params[] = $input['address'];
            }
            if (isset($input['role'])) {
                $updates[] = 'role = ?';
                $params[] = $input['role'];
            }
            if (isset($input['status'])) {
                $updates[] = 'status = ?';
                $params[] = $input['status'];
            }
            if (isset($input['email_verified'])) {
                $updates[] = 'email_verified = ?';
                $params[] = (int)$input['email_verified'];
            }
            if (isset($input['password'])) {
                $updates[] = 'password = ?';
                $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
            }
            
            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No fields to update']);
                exit;
            }
            
            $updates[] = 'updated_at = NOW()';
            $params[] = $input['user_id'];
            
            $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";
            $stmt = $db->execute($query, $params);
            
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            break;
            
        case 'DELETE':
            // Delete user (soft delete by setting status to inactive)
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['user_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'User ID is required']);
                exit;
            }
            
            // Don't allow deleting own account
            $current_user = $auth->getCurrentUser();
            if ($input['user_id'] == $current_user['user_id']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
                exit;
            }
            
            // Soft delete by setting status to inactive
            $query = "UPDATE users SET status = 'inactive', updated_at = NOW() WHERE user_id = ?";
            $stmt = $db->execute($query, [$input['user_id']]);
            
            echo json_encode(['success' => true, 'message' => 'User deactivated successfully']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
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
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $role = isset($_GET['role']) ? $_GET['role'] : 'all';
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';

        $offset = ($page - 1) * $limit;
        
        // Build query with filters
        $where_conditions = [];
        $params = [];
        
        if (!empty($search)) {
            $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR username LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($role !== 'all') {
            $where_conditions[] = "role = ?";
            $params[] = $role;
        }
        
        if ($status !== 'all') {
            $where_conditions[] = "status = ?";
            $params[] = $status;
        }
        
        $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

        // Get total count
        $count_sql = "SELECT COUNT(*) as total FROM users $where_clause";
        $count_stmt = $db->prepare($count_sql);
        $count_stmt->execute($params);
        $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get users (exclude password)
        $sql = "SELECT 
                    user_id, username, email, first_name, last_name, phone,
                    role, status, email_verified, phone_verified, last_login,
                    created_at, updated_at
                FROM users 
                $where_clause
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format user data
        foreach ($users as &$user) {
            $user['user_id'] = (int)$user['user_id'];
            $user['email_verified'] = (bool)$user['email_verified'];
            $user['phone_verified'] = (bool)$user['phone_verified'];
            $user['full_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
        }

        echo json_encode([
            'success' => true,
            'data' => $users,
            'pagination' => [
                'total' => (int)$total,
                'limit' => $limit,
                'offset' => $offset,
                'currentPage' => $page,
                'totalPages' => ceil($total / $limit),
                'hasMore' => $offset + $limit < $total
            ]
        ]);

    } catch (Exception $e) {
        error_log("Get users error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch users']);
    }
}

function handleUpdateUser($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['user_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            return;
        }

        // Build update query dynamically
        $update_fields = [];
        $params = [];
        
        $allowed_fields = ['first_name', 'last_name', 'email', 'phone', 'role', 'status', 
                          'email_verified', 'phone_verified'];
        
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $update_fields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        
        if (empty($update_fields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            return;
        }
        
        $params[] = $input['user_id'];
        
        $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);

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
        
        if (!$input || !isset($input['user_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            return;
        }

        // Check if trying to delete the current admin user
        $auth = new AuthController();
        $currentUser = $auth->getCurrentUser();
        
        if ($currentUser && $currentUser['user_id'] == $input['user_id']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
            return;
        }

        // Instead of actually deleting, we'll deactivate the user
        $sql = "UPDATE users SET status = 'inactive' WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$input['user_id']]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'User deactivated successfully']);
        } else {
            throw new Exception('Failed to deactivate user');
        }

    } catch (Exception $e) {
        error_log("Delete user error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to deactivate user',
            'debug' => [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]
        ]);
    }
} catch (Exception $e) {
    error_log("Admin Users API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Internal server error',
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>

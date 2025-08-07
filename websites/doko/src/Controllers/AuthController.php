<?php
/**
 * Simple Authentication Controller
 * DOKO Grocery E-commerce
 */

require_once __DIR__ . '/../../config/database.php';

class AuthController {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Safe session start - prevents "session already started" errors
     */
    private function ensureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function login($email, $password) {
        try {
            // Check if user exists
            $query = "SELECT user_id, username, email, password, first_name, last_name, role, status, email_verified 
                      FROM users WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // Check if account is active
            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Account is not active'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // Start session safely
            $this->ensureSession();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // Remove password from response
            unset($user['password']);
            
            // Determine redirect URL based on user role
            $redirect_url = 'index.php'; // Default redirect
            
            switch ($user['role']) {
                case 'admin':
                    $redirect_url = 'admin.php';
                    break;
                case 'customer':
                    $redirect_url = 'customer.php';
                    break;
                default:
                    $redirect_url = 'index.php';
                    break;
            }
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $user,
                'redirect_url' => $redirect_url
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }
    
    public function register($userData) {
        try {
            // Check if email already exists
            $checkQuery = "SELECT user_id FROM users WHERE email = :email";
            $checkStmt = $this->db->prepare($checkQuery);
            $emailCheck = $userData['email'];
            $checkStmt->bindParam(':email', $emailCheck);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email already registered'];
            }
            
            // Check if username already exists
            $checkQuery = "SELECT user_id FROM users WHERE username = :username";
            $checkStmt = $this->db->prepare($checkQuery);
            $usernameCheck = $userData['username'];
            $checkStmt->bindParam(':username', $usernameCheck);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Username already taken'];
            }
            
            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $insertQuery = "INSERT INTO users (username, email, password, first_name, last_name, phone, role, status, created_at) 
                           VALUES (:username, :email, :password, :first_name, :last_name, :phone, 'customer', 'active', NOW())";
            
            $insertStmt = $this->db->prepare($insertQuery);
            $username = $userData['username'];
            $email = $userData['email'];
            $firstName = $userData['first_name'];
            $lastName = $userData['last_name'];
            $phone = $userData['phone'] ?? null;
            
            $insertStmt->bindParam(':username', $username);
            $insertStmt->bindParam(':email', $email);
            $insertStmt->bindParam(':password', $hashedPassword);
            $insertStmt->bindParam(':first_name', $firstName);
            $insertStmt->bindParam(':last_name', $lastName);
            $insertStmt->bindParam(':phone', $phone);
            
            if ($insertStmt->execute()) {
                $userId = $this->db->lastInsertId();
                
                return [
                    'success' => true,
                    'message' => 'Registration successful',
                    'user_id' => $userId
                ];
            } else {
                return ['success' => false, 'message' => 'Registration failed'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    public function isLoggedIn() {
        $this->ensureSession();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function isAdmin() {
        $this->ensureSession();
        return $this->isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    public function isCustomer() {
        $this->ensureSession();
        return $this->isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
    }
    
    public function hasRole($role) {
        $this->ensureSession();
        return $this->isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    public function hasAdminAccess() {
        return $this->isAdmin();
    }
    
    public function getUserRole() {
        $this->ensureSession();
        return $this->isLoggedIn() ? $_SESSION['role'] : null;
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name'],
            'role' => $_SESSION['role']
        ];
    }
    
    public function logout() {
        $this->ensureSession();
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
}
?>
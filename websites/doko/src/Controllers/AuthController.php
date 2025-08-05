<?php
/**
 * Authentication Controller
 * DOKO Grocery E-commerce
 * Handles user and admin authentication
 */

class AuthController {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../../config/database.php';
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Authenticate user/admin login
     */
    public function login($email, $password) {
        try {
            // Check if user exists and get user data
            $query = "SELECT user_id, username, email, password, first_name, last_name, phone, role, status, login_attempts, locked_until 
                      FROM users 
                      WHERE email = :email";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            $user = $stmt->fetch();
            
            // Check if account is locked
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                return ['success' => false, 'message' => 'Account is temporarily locked. Please try again later.'];
            }
            
            // Check if account is active
            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Account is not active. Please contact support.'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                // Increment login attempts
                $this->incrementLoginAttempts($user['user_id']);
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            // Reset login attempts and update last login
            $this->resetLoginAttempts($user['user_id']);
            
            // Create session
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['logged_in'] = true;
            
            // Generate session ID for tracking
            $sessionId = session_id();
            
            // Insert session record
            $this->createSessionRecord($sessionId, $user['user_id']);
            
            // Determine redirect URL based on role
            $redirectUrl = $this->getRedirectUrl($user['role']);
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'full_name' => $user['first_name'] . ' ' . $user['last_name']
                ],
                'redirect_url' => $redirectUrl
            ];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during login'];
        }
    }
    
    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl($role) {
        switch ($role) {
            case 'admin':
            case 'manager':
                return 'admin.php';
            case 'vendor':
                return 'vendor-dashboard.php';
            case 'customer':
            default:
                return 'index.php';
        }
    }
    
    /**
     * Increment login attempts
     */
    private function incrementLoginAttempts($userId) {
        $query = "UPDATE users SET login_attempts = login_attempts + 1 WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        // Lock account after 5 failed attempts
        $checkQuery = "SELECT login_attempts FROM users WHERE user_id = :user_id";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(':user_id', $userId);
        $checkStmt->execute();
        $attempts = $checkStmt->fetchColumn();
        
        if ($attempts >= 5) {
            $lockQuery = "UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE user_id = :user_id";
            $lockStmt = $this->db->prepare($lockQuery);
            $lockStmt->bindParam(':user_id', $userId);
            $lockStmt->execute();
        }
    }
    
    /**
     * Reset login attempts
     */
    private function resetLoginAttempts($userId) {
        $query = "UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = CURRENT_TIMESTAMP WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
    }
    
    /**
     * Create session record
     */
    private function createSessionRecord($sessionId, $userId) {
        $query = "INSERT INTO user_sessions (session_id, user_id, ip_address, user_agent, expires_at) 
                  VALUES (:session_id, :user_id, :ip_address, :user_agent, DATE_ADD(NOW(), INTERVAL 24 HOUR))";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':session_id', $sessionId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR'] ?? '');
        $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $stmt->execute();
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        session_start();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Check if user has admin privileges
     */
    public function isAdmin() {
        session_start();
        return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager']);
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_start();
        
        if (isset($_SESSION['user_id'])) {
            // Mark session as inactive
            $sessionId = session_id();
            $query = "UPDATE user_sessions SET is_active = FALSE WHERE session_id = :session_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':session_id', $sessionId);
            $stmt->execute();
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
        
        return ['success' => true, 'message' => 'Logout successful'];
    }
    
    /**
     * Register new user
     */
    public function register($userData) {
        try {
            // Validate required fields
            $required = ['username', 'email', 'password', 'first_name', 'last_name'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    return ['success' => false, 'message' => ucfirst($field) . ' is required'];
                }
            }
            
            // Check if username exists
            $checkQuery = "SELECT user_id FROM users WHERE username = :username";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':username', $userData['username']);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Username already exists'];
            }
            
            // Check if email exists
            $checkQuery = "SELECT user_id FROM users WHERE email = :email";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':email', $userData['email']);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Insert new user
            $insertQuery = "INSERT INTO users (username, email, password, first_name, last_name, phone, role, status) 
                           VALUES (:username, :email, :password, :first_name, :last_name, :phone, 'customer', 'active')";
            
            $insertStmt = $this->db->prepare($insertQuery);
            $insertStmt->bindParam(':username', $userData['username']);
            $insertStmt->bindParam(':email', $userData['email']);
            $insertStmt->bindParam(':password', $hashedPassword);
            $insertStmt->bindParam(':first_name', $userData['first_name']);
            $insertStmt->bindParam(':last_name', $userData['last_name']);
            $insertStmt->bindParam(':phone', $userData['phone'] ?? null);
            
            if ($insertStmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Registration successful',
                    'user_id' => $this->db->lastInsertId()
                ];
            } else {
                return ['success' => false, 'message' => 'Registration failed'];
            }
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during registration'];
        }
    }
}
?>

<?php
/**
 * Simple Authentication Controller
 * DOKO Grocery E-commerce
 */

class AuthController {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
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
            
            // Start session
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // Remove password from response
            unset($user['password']);
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $user
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
            $checkStmt->bindParam(':email', $userData['email']);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email already registered'];
            }
            
            // Check if username already exists
            $checkQuery = "SELECT user_id FROM users WHERE username = :username";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':username', $userData['username']);
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
            $insertStmt->bindParam(':username', $userData['username']);
            $insertStmt->bindParam(':email', $userData['email']);
            $insertStmt->bindParam(':password', $hashedPassword);
            $insertStmt->bindParam(':first_name', $userData['first_name']);
            $insertStmt->bindParam(':last_name', $userData['last_name']);
            $insertStmt->bindParam(':phone', $userData['phone'] ?? null);
            
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
        session_start();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function isAdmin() {
        session_start();
        return $this->isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
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
        session_start();
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
}
?>

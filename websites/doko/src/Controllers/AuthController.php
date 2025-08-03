<?php
namespace Controllers;

/**
 * Auth Controller
 * Handles authentication and user management
 */
class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new \Models\User();
    }
    
    /**
     * User registration
     */
    public function register($params = []) {
        try {
            $input = \Core\Router::getInput();
            
            // Validate required fields
            $requiredFields = ['name', 'email', 'password'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    \Core\Router::json([
                        'success' => false,
                        'error' => "Field {$field} is required"
                    ], 400);
                    return;
                }
            }
            
            // Validate email format
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Invalid email format'
                ], 400);
                return;
            }
            
            // Validate password length
            if (strlen($input['password']) < 6) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Password must be at least 6 characters long'
                ], 400);
                return;
            }
            
            $userId = $this->userModel->createUser($input);
            $user = $this->userModel->getUserById($userId);
            
            // Start session
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            \Core\Router::json([
                'success' => true,
                'message' => 'Registration successful',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ], 201);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * User login
     */
    public function login($params = []) {
        try {
            $input = \Core\Router::getInput();
            
            // Validate required fields
            if (!isset($input['email']) || !isset($input['password'])) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Email and password are required'
                ], 400);
                return;
            }
            
            $user = $this->userModel->authenticate($input['email'], $input['password']);
            
            if (!$user) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Invalid email or password'
                ], 401);
                return;
            }
            
            // Start session
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            \Core\Router::json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * User logout
     */
    public function logout($params = []) {
        session_start();
        session_destroy();
        
        \Core\Router::json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }
    
    /**
     * Get current user
     */
    public function me($params = []) {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            \Core\Router::json([
                'success' => false,
                'error' => 'Not authenticated'
            ], 401);
            return;
        }
        
        try {
            $user = $this->userModel->getUserById($_SESSION['user_id']);
            
            if (!$user) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'User not found'
                ], 404);
                return;
            }
            
            \Core\Router::json([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'address' => $user['address'],
                    'role' => $user['role']
                ]
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($params = []) {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            \Core\Router::json([
                'success' => false,
                'error' => 'Not authenticated'
            ], 401);
            return;
        }
        
        try {
            $input = \Core\Router::getInput();
            $userId = $_SESSION['user_id'];
            
            // Remove sensitive fields
            unset($input['password'], $input['role'], $input['id']);
            
            $this->userModel->updateUser($userId, $input);
            $user = $this->userModel->getUserById($userId);
            
            // Update session
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            \Core\Router::json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'address' => $user['address'],
                    'role' => $user['role']
                ]
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($params = []) {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            \Core\Router::json([
                'success' => false,
                'error' => 'Not authenticated'
            ], 401);
            return;
        }
        
        try {
            $input = \Core\Router::getInput();
            
            if (!isset($input['current_password']) || !isset($input['new_password'])) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Current password and new password are required'
                ], 400);
                return;
            }
            
            // Verify current password
            $user = $this->userModel->getUserById($_SESSION['user_id']);
            if (!password_verify($input['current_password'], $user['password'])) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Current password is incorrect'
                ], 400);
                return;
            }
            
            // Validate new password
            if (strlen($input['new_password']) < 6) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'New password must be at least 6 characters long'
                ], 400);
                return;
            }
            
            $this->userModel->updatePassword($_SESSION['user_id'], $input['new_password']);
            
            \Core\Router::json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check if user is authenticated
     */
    public function checkAuth() {
        session_start();
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Check if user is admin
     */
    public function checkAdmin() {
        session_start();
        return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin']);
    }
    
    /**
     * Require authentication middleware
     */
    public function requireAuth() {
        if (!$this->checkAuth()) {
            \Core\Router::json([
                'success' => false,
                'error' => 'Authentication required'
            ], 401);
            exit;
        }
    }
    
    /**
     * Require admin middleware
     */
    public function requireAdmin() {
        $this->requireAuth();
        
        if (!$this->checkAdmin()) {
            \Core\Router::json([
                'success' => false,
                'error' => 'Admin access required'
            ], 403);
            exit;
        }
    }
}
?>

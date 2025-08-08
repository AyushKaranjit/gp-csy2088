<?php
/**
 * Enhanced User Model for New Database Schema
 * Handles user authentication, registration, and management
 */

require_once __DIR__ . '/../../config/database.php';

// Define default session timeout (in seconds) if not already defined
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600);
}

if (!function_exists('logActivity')) {
    /**
     * Generic activity logger
     * @param int|string|null $userId
     * @param string $action
     * @param string|null $entityType
     * @param int|string|null $entityId
     * @param mixed $metadata
     * @param mixed $data
     */
    function logActivity($userId, $action, $entityType = null, $entityId = null, $metadata = null, $data = null) {
        try {
            $db = Database::getInstance();
            // Attempt to insert into activity_logs table if it exists
            $sql = "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, metadata, payload, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $metaJson = $metadata !== null ? (is_string($metadata) ? $metadata : json_encode($metadata)) : null;
            $dataJson = $data !== null ? (is_string($data) ? $data : json_encode($data)) : null;
            try {
                $db->execute($sql, [$userId, $action, $entityType, $entityId, $metaJson, $dataJson]);
            } catch (Exception $e) {
                // Fallback to error_log if table missing or insert fails
                error_log("Activity: user={$userId} action={$action} entity_type={$entityType} entity_id={$entityId}");
            }
        } catch (Exception $e) {
            error_log("logActivity failure: " . $e->getMessage());
        }
    }
}

// Renamed from User to AdvancedUser to avoid collision with legacy User model.
class AdvancedUser {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Register a new user
     */
    public function register($userData) {
        try {
            // Check if username or email already exists
            if ($this->usernameExists($userData['username'])) {
                throw new Exception('Username already exists');
            }
            
            if ($this->emailExists($userData['email'])) {
                throw new Exception('Email already exists');
            }
            
            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);
            
            $sql = "INSERT INTO users (username, email, password, first_name, last_name, phone, gender, role, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $userData['username'],
                $userData['email'],
                $hashedPassword,
                $userData['first_name'],
                $userData['last_name'],
                $userData['phone'] ?? null,
                $userData['gender'] ?? null,
                $userData['role'] ?? 'customer',
                'active' // Auto-activate for now, can be changed to 'pending' for email verification
            ];
            
            $stmt = $this->db->execute($sql, $params);
            $userId = $this->db->lastInsertId();
            
            // Log activity
            logActivity($userId, 'register', 'user', $userId, null, $userData);
            
            return $userId;
        } catch (PDOException $e) {
            error_log("User registration failed: " . $e->getMessage());
            throw new Exception('Registration failed. Please try again.');
        }
    }
    
    /**
     * Login user
     */
    public function login($username, $password) {
        try {
            $sql = "SELECT user_id, username, email, password, first_name, last_name, role, status, 
                           login_attempts, locked_until 
                    FROM users 
                    WHERE (username = ? OR email = ?) AND status = 'active'";
            
            $user = $this->db->fetchRow($sql, [$username, $username]);
            
            if (!$user) {
                throw new Exception('Invalid credentials or account not active');
            }
            
            // Check if account is locked
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                throw new Exception('Account is temporarily locked. Please try again later.');
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->incrementLoginAttempts($user['user_id']);
                throw new Exception('Invalid credentials');
            }
            
            // Reset login attempts on successful login
            $this->resetLoginAttempts($user['user_id']);
            
            // Update last login
            $this->updateLastLogin($user['user_id']);
            
            // Create session
            $sessionId = $this->createSession($user['user_id']);
            
            // Log activity
            logActivity($user['user_id'], 'login', 'user', $user['user_id']);
            
            return [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $user['role'],
                'session_id' => $sessionId
            ];
        } catch (PDOException $e) {
            error_log("User login failed: " . $e->getMessage());
            throw new Exception('Login failed. Please try again.');
        }
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        $sql = "SELECT user_id, username, email, first_name, last_name, phone, date_of_birth, 
                       gender, profile_image, role, status, email_verified, phone_verified, 
                       two_factor_enabled, last_login, created_at, updated_at
                FROM users 
                WHERE user_id = ?";
        
        return $this->db->fetchRow($sql, [$userId]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $userData) {
        try {
            $allowedFields = ['first_name', 'last_name', 'phone', 'date_of_birth', 'gender'];
            $fields = [];
            $params = [];
            
            foreach ($allowedFields as $field) {
                if (isset($userData[$field])) {
                    $fields[] = "$field = ?";
                    $params[] = $userData[$field];
                }
            }
            
            if (empty($fields)) {
                throw new Exception('No valid fields to update');
            }
            
            $fields[] = "updated_at = CURRENT_TIMESTAMP";
            $params[] = $userId;
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
            
            $stmt = $this->db->execute($sql, $params);
            
            // Log activity
            logActivity($userId, 'update_profile', 'user', $userId, null, $userData);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Profile update failed: " . $e->getMessage());
            throw new Exception('Profile update failed. Please try again.');
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get current password
            $sql = "SELECT password FROM users WHERE user_id = ?";
            $user = $this->db->fetchRow($sql, [$userId]);
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                throw new Exception('Current password is incorrect');
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";
            
            $stmt = $this->db->execute($sql, [$hashedPassword, $userId]);
            
            // Log activity
            logActivity($userId, 'change_password', 'user', $userId);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Password change failed: " . $e->getMessage());
            throw new Exception('Password change failed. Please try again.');
        }
    }
    
    /**
     * Add user address
     */
    public function addAddress($userId, $addressData) {
        try {
            // If this is set as default, unset other defaults
            if (isset($addressData['is_default']) && $addressData['is_default']) {
                $this->db->execute("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ?", [$userId]);
            }
            
            $sql = "INSERT INTO user_addresses (user_id, address_type, address_label, street_address, 
                                              city, state, postal_code, country, landmark, is_default) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $userId,
                $addressData['address_type'] ?? 'home',
                $addressData['address_label'] ?? null,
                $addressData['street_address'],
                $addressData['city'],
                $addressData['state'],
                $addressData['postal_code'],
                $addressData['country'] ?? 'Nepal',
                $addressData['landmark'] ?? null,
                $addressData['is_default'] ?? false
            ];
            
            $stmt = $this->db->execute($sql, $params);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Add address failed: " . $e->getMessage());
            throw new Exception('Failed to add address. Please try again.');
        }
    }
    
    /**
     * Get user addresses
     */
    public function getUserAddresses($userId) {
        $sql = "SELECT * FROM user_addresses 
                WHERE user_id = ? 
                ORDER BY is_default DESC, created_at DESC";
        
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats($userId) {
        $sql = "SELECT * FROM user_stats_view WHERE user_id = ?";
        return $this->db->fetchRow($sql, [$userId]);
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 10, $unreadOnly = false) {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = ?" . 
                ($unreadOnly ? " AND is_read = FALSE" : "") . 
                " ORDER BY created_at DESC LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationRead($notificationId, $userId) {
        $sql = "UPDATE notifications 
                SET is_read = TRUE, read_at = CURRENT_TIMESTAMP 
                WHERE notification_id = ? AND user_id = ?";
        
        $stmt = $this->db->execute($sql, [$notificationId, $userId]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Check if username exists
     */
    private function usernameExists($username) {
        $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
        $result = $this->db->fetchRow($sql, [$username]);
        return $result['COUNT(*)'] > 0;
    }
    
    /**
     * Check if email exists
     */
    private function emailExists($email) {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $result = $this->db->fetchRow($sql, [$email]);
        return $result['COUNT(*)'] > 0;
    }
    
    /**
     * Increment login attempts
     */
    private function incrementLoginAttempts($userId) {
        $sql = "UPDATE users 
                SET login_attempts = login_attempts + 1,
                    locked_until = CASE 
                        WHEN login_attempts >= 4 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                        ELSE locked_until 
                    END
                WHERE user_id = ?";
        
        $this->db->execute($sql, [$userId]);
    }
    
    /**
     * Reset login attempts
     */
    private function resetLoginAttempts($userId) {
        $sql = "UPDATE users 
                SET login_attempts = 0, locked_until = NULL 
                WHERE user_id = ?";
        
        $this->db->execute($sql, [$userId]);
    }
    
    /**
     * Update last login
     */
    private function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?";
        $this->db->execute($sql, [$userId]);
    }
    
    /**
     * Create user session
     */
    private function createSession($userId) {
        $sessionId = bin2hex(random_bytes(32));
        
        $sql = "INSERT INTO user_sessions (session_id, user_id, ip_address, user_agent, expires_at) 
                VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))";
        
        $params = [
            $sessionId,
            $userId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            SESSION_TIMEOUT
        ];
        
        $this->db->execute($sql, $params);
        
        return $sessionId;
    }
    
    /**
     * Validate session
     */
    public function validateSession($sessionId) {
        $sql = "SELECT us.*, u.username, u.email, u.first_name, u.last_name, u.role 
                FROM user_sessions us
                JOIN users u ON us.user_id = u.user_id
                WHERE us.session_id = ? AND us.is_active = TRUE AND us.expires_at > NOW()";
        
        return $this->db->fetchRow($sql, [$sessionId]);
    }
    
    /**
     * Logout user (deactivate session)
     */
    public function logout($sessionId) {
        $sql = "UPDATE user_sessions SET is_active = FALSE WHERE session_id = ?";
        $stmt = $this->db->execute($sql, [$sessionId]);
        return $stmt->rowCount() > 0;
    }
}
?>

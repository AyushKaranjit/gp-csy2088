<?php
namespace Models;

/**
 * User Model
 * Handles all user-related database operations
 */
class User {
    private $db;
    
    public function __construct() {
        $this->db = new \Core\Database();
    }
    
    /**
     * Create new user
     */
    public function createUser($data) {
        $requiredFields = ['name', 'email', 'password'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Field {$field} is required");
            }
        }
        
        // Check if email already exists
        if ($this->getUserByEmail($data['email'])) {
            throw new \Exception("Email already exists");
        }
        
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'role' => $data['role'] ?? 'customer',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('users', $userData);
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email AND status = 'active'";
        return $this->db->fetch($sql, ['email' => $email]);
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id AND status = 'active'";
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($email, $password) {
        $user = $this->getUserByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->updateLastLogin($user['id']);
            
            // Remove password from returned data
            unset($user['password']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Update user
     */
    public function updateUser($id, $data) {
        // Remove sensitive fields that shouldn't be updated directly
        unset($data['password'], $data['id'], $data['created_at']);
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('users', $data, 'id = :id', ['id' => $id]);
    }
    
    /**
     * Update password
     */
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        return $this->db->update('users', 
            ['password' => $hashedPassword, 'updated_at' => date('Y-m-d H:i:s')], 
            'id = :id', 
            ['id' => $id]
        );
    }
    
    /**
     * Update last login
     */
    public function updateLastLogin($id) {
        return $this->db->update('users', 
            ['last_login' => date('Y-m-d H:i:s')], 
            'id = :id', 
            ['id' => $id]
        );
    }
    
    /**
     * Get all customers
     */
    public function getAllCustomers($limit = null, $offset = 0) {
        $sql = "SELECT id, name, email, phone, address, created_at, last_login,
                       (SELECT COUNT(*) FROM orders WHERE user_id = users.id) as total_orders,
                       (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = users.id) as total_spent
                FROM users 
                WHERE role = 'customer' AND status = 'active' 
                ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get admin users
     */
    public function getAllAdmins() {
        $sql = "SELECT id, name, email, role, created_at, last_login 
                FROM users 
                WHERE role IN ('admin', 'super_admin') AND status = 'active' 
                ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Delete user (soft delete)
     */
    public function deleteUser($id) {
        return $this->db->update('users', 
            ['status' => 'deleted', 'updated_at' => date('Y-m-d H:i:s')], 
            'id = :id', 
            ['id' => $id]
        );
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin($id) {
        $user = $this->getUserById($id);
        return $user && in_array($user['role'], ['admin', 'super_admin']);
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats() {
        $sql = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN created_at >= CURDATE() - INTERVAL 7 DAY THEN 1 ELSE 0 END) as new_this_week,
                    SUM(CASE WHEN last_login >= CURDATE() - INTERVAL 7 DAY THEN 1 ELSE 0 END) as active_this_week
                FROM users 
                WHERE role = 'customer' AND status = 'active'";
        
        return $this->db->fetch($sql);
    }
}
?>

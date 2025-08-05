<?php
/**
 * Authentication Middleware
 * DOKO Grocery E-commerce
 */

class AuthMiddleware {
    
    /**
     * Check if user is authenticated
     */
    public static function requireAuth() {
        session_start();
        
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required',
                'redirect' => 'login.php'
            ]);
            exit;
        }
        
        return true;
    }
    
    /**
     * Check if user has admin privileges
     */
    public static function requireAdmin() {
        self::requireAuth();
        
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Admin access required'
            ]);
            exit;
        }
        
        return true;
    }
    
    /**
     * Check if user has specific role
     */
    public static function requireRole($role) {
        self::requireAuth();
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient privileges'
            ]);
            exit;
        }
        
        return true;
    }
    
    /**
     * Get current user info
     */
    public static function getCurrentUser() {
        session_start();
        
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return null;
        }
        
        return [
            'user_id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'role' => $_SESSION['role'] ?? null,
            'full_name' => $_SESSION['full_name'] ?? null
        ];
    }
    
    /**
     * Log user activity
     */
    public static function logActivity($action, $entityType = null, $entityId = null, $oldValues = null, $newValues = null) {
        try {
            $user = self::getCurrentUser();
            if (!$user) return;
            
            require_once __DIR__ . '/../config/database.php';
            $database = Database::getInstance();
            $conn = $database->getConnection();
            
            $query = "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) 
                      VALUES (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address, :user_agent)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user['user_id']);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':entity_type', $entityType);
            $stmt->bindParam(':entity_id', $entityId);
            $stmt->bindParam(':old_values', $oldValues ? json_encode($oldValues) : null);
            $stmt->bindParam(':new_values', $newValues ? json_encode($newValues) : null);
            $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR'] ?? null);
            $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? null);
            $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Activity log error: " . $e->getMessage());
        }
    }
}
?>

<?php
/**
 * User Model Class for DOKO Grocery E-commerce
 * Handles all user-related database operations
 */

require_once '../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $user_id;
    public $name;
    public $email;
    public $password;
    public $phone;
    public $address;
    public $role;
    public $email_verified;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Register new user
    public function register() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET name=:name, email=:email, password=:password, phone=:phone, address=:address";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        
        if($stmt->execute()) {
            $this->user_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Login user
    public function login($email, $password) {
        $query = "SELECT user_id, name, email, password, phone, address, role, email_verified 
                 FROM " . $this->table_name . " 
                 WHERE email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if(password_verify($password, $row['password'])) {
                $this->user_id = $row['user_id'];
                $this->name = $row['name'];
                $this->email = $row['email'];
                $this->phone = $row['phone'];
                $this->address = $row['address'];
                $this->role = $row['role'];
                $this->email_verified = $row['email_verified'];
                return true;
            }
        }
        return false;
    }

    // Get user by ID
    public function getUserById($id) {
        $query = "SELECT user_id, name, email, phone, address, role, email_verified, created_at 
                 FROM " . $this->table_name . " 
                 WHERE user_id = :user_id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }

    // Update user profile
    public function updateProfile() {
        $query = "UPDATE " . $this->table_name . " 
                 SET name=:name, phone=:phone, address=:address 
                 WHERE user_id=:user_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":user_id", $this->user_id);
        
        return $stmt->execute();
    }

    // Check if email exists
    public function emailExists($email) {
        $query = "SELECT user_id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Get all users (admin only)
    public function getAllUsers($limit = 50, $offset = 0) {
        $query = "SELECT user_id, name, email, phone, role, email_verified, created_at 
                 FROM " . $this->table_name . " 
                 ORDER BY created_at DESC 
                 LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Delete user (admin only)
    public function deleteUser($user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        
        return $stmt->execute();
    }
}
?>

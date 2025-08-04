<?php
/**
 * Database Configuration for DOKO Grocery E-commerce
 * CSY2088 Project Configuration File
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'doko_grocery';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}

// Application Configuration
if (!defined('APP_NAME')) define('APP_NAME', 'DOKO');
if (!defined('APP_VERSION')) define('APP_VERSION', '1.0.0');
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', 'public/images/uploads/');

// Email Configuration
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USERNAME')) define('SMTP_USERNAME', 'your-email@gmail.com');
if (!defined('SMTP_PASSWORD')) define('SMTP_PASSWORD', 'your-app-password');

// Security Configuration
if (!defined('JWT_SECRET')) define('JWT_SECRET', 'your-jwt-secret-key-here');
if (!defined('BCRYPT_COST')) define('BCRYPT_COST', 12);
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 3600); // 1 hour

// Payment Configuration
if (!defined('ESEWA_MERCHANT_ID')) define('ESEWA_MERCHANT_ID', 'your-esewa-merchant-id');
if (!defined('KHALTI_SECRET_KEY')) define('KHALTI_SECRET_KEY', 'your-khalti-secret-key');

// Delivery Configuration
if (!defined('DELIVERY_CHARGE')) define('DELIVERY_CHARGE', 50.00);
if (!defined('FREE_DELIVERY_MINIMUM')) define('FREE_DELIVERY_MINIMUM', 1000.00);
if (!defined('TAX_RATE')) define('TAX_RATE', 0.13); // 13% VAT
?>

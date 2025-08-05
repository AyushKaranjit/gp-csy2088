<?php
/**
 * DOKO Grocery Database Configuration (New Schema)
 * Enhanced database connection with improved security and features
 * Created: August 5, 2025
 */

class Database {
    // Database configuration - Docker Environment
    private $host = 'mysql';  // Docker service name
    private $database = 'doko_ecommerce';  // Match Docker compose
    private $username = 'student';  // Match Docker compose
    private $password = 'student';  // Match Docker compose
    private $charset = 'utf8mb4';
    private $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    
    private $pdo = null;
    private static $instance = null;
    
    /**
     * Constructor - private to implement singleton pattern
     */
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            $this->pdo = new PDO($dsn, $this->username, $this->password, $this->options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    
    /**
     * Get database instance (singleton pattern)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Execute a prepared statement
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Database query failed. Please try again later.");
        }
    }
    
    /**
     * Fetch single row
     */
    public function fetchRow($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get last inserted ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Check if in transaction
     */
    public function inTransaction() {
        return $this->pdo->inTransaction();
    }
    
    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $stmt = $this->pdo->query("SELECT 1");
            return $stmt !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get database info
     */
    public function getDatabaseInfo() {
        try {
            $version = $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            
            return [
                'driver' => $driver,
                'version' => $version,
                'host' => $this->host,
                'database' => $this->database,
                'charset' => $this->charset
            ];
        } catch (PDOException $e) {
            return ['error' => 'Could not retrieve database info'];
        }
    }
    
    /**
     * Close connection
     */
    public function close() {
        $this->pdo = null;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Application Configuration Constants
define('APP_NAME', 'DOKO Grocery');
define('APP_VERSION', '2.0.0');
define('APP_URL', 'http://localhost:8080');

// Security Configuration
define('JWT_SECRET', 'your-super-secret-jwt-key-change-in-production');
define('JWT_EXPIRY', 3600); // 1 hour in seconds
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Email Configuration (Update with your SMTP settings)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@doko.com');
define('SMTP_FROM_NAME', 'DOKO Grocery');

// Payment Gateway Configuration
define('ESEWA_MERCHANT_CODE', 'your-esewa-merchant-code');
define('ESEWA_SECRET_KEY', 'your-esewa-secret-key');
define('KHALTI_PUBLIC_KEY', 'your-khalti-public-key');
define('KHALTI_SECRET_KEY', 'your-khalti-secret-key');

// Cache Configuration
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600); // 1 hour

// Pagination Configuration
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 10);
define('REVIEWS_PER_PAGE', 5);

// Business Configuration
define('CURRENCY', 'NPR');
define('CURRENCY_SYMBOL', 'Rs. ');
define('TAX_RATE', 13.00); // 13% VAT
define('FREE_SHIPPING_THRESHOLD', 2000.00);
define('DELIVERY_FEE', 100.00);
define('MIN_ORDER_AMOUNT', 500.00);

// Error Reporting (Set to false in production)
define('DEBUG_MODE', false);

// Set error reporting based on debug mode
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Kathmandu');

/**
 * Helper function to get database instance
 */
function getDatabase() {
    return Database::getInstance();
}

/**
 * Helper function to format currency
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

/**
 * Helper function to format date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if ($date instanceof DateTime) {
        return $date->format($format);
    }
    return date($format, strtotime($date));
}

/**
 * Helper function to generate unique order number
 */
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * Helper function to generate slug from string
 */
function generateSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Helper function to sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Helper function to validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Helper function to validate phone number (Nepal format)
 */
function isValidPhone($phone) {
    // Nepal phone number format: +977-9XXXXXXXXX or 9XXXXXXXXX
    $pattern = '/^(\+977[-\s]?)?[9][0-9]{9}$/';
    return preg_match($pattern, $phone);
}

/**
 * Helper function to check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Helper function to check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Helper function to set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Helper function to get flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Helper function to log activity
 */
function logActivity($user_id, $action, $entity_type, $entity_id = null, $old_values = null, $new_values = null) {
    try {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $user_id,
            $action,
            $entity_type,
            $entity_id,
            $old_values ? json_encode($old_values) : null,
            $new_values ? json_encode($new_values) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        $db->execute($sql, $params);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Helper function to send notification
 */
function sendNotification($user_id, $type, $title, $message, $data = null) {
    try {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO notifications (user_id, type, title, message, data) VALUES (?, ?, ?, ?, ?)";
        $params = [$user_id, $type, $title, $message, $data ? json_encode($data) : null];
        
        $db->execute($sql, $params);
        return true;
    } catch (Exception $e) {
        error_log("Failed to send notification: " . $e->getMessage());
        return false;
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Test database connection on include (only in debug mode)
if (DEBUG_MODE) {
    try {
        $db = Database::getInstance();
        if (!$db->testConnection()) {
            error_log("Database connection test failed");
        }
    } catch (Exception $e) {
        error_log("Database initialization failed: " . $e->getMessage());
    }
}

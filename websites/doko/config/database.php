<?php
/**
 * DOKO Grocery Database Configuration (Auto-detecting Environment)
 * Works with both local XAMPP/WAMP and Docker environments
 */

class Database {
    private $host;
    private $database;
    private $username;
    private $password;
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
        $this->detectEnvironment();
        $this->connect();
    }
    
    /**
     * Auto-detect environment and set database credentials
     */
    private function detectEnvironment() {
        // Highest priority: explicit environment variables (used by tests / CI)
        $envHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? null);
        $envName = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? null);
        $envUser = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? null);
        $envPass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? null);
        if ($envHost && $envName && $envUser !== null) {
            $this->host = $envHost;
            $this->database = $envName;
            $this->username = $envUser;
            $this->password = $envPass ?? '';
            return;
        }

        // Check if running in Docker environment
        if (getenv('DOCKER_ENV') === 'true' || file_exists('/.dockerenv')) {
            $this->host = 'mysql';
            $this->database = 'doko_ecommerce';
            $this->username = 'student';
            $this->password = 'student';
            return;
        }

        // XAMPP/WAMP local environment
        if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false) {
            $this->host = 'localhost';
            $this->database = 'doko_ecommerce';
            $this->username = 'root';
            $this->password = '';
            return;
        }

        // Default local development
        $this->host = 'localhost';
        $this->database = 'doko_ecommerce';
        $this->username = 'root';
        $this->password = '';
    }
    
    /**
     * Connect to database
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            $this->pdo = new PDO($dsn, $this->username, $this->password, $this->options);
            
            // Test the connection
            $this->pdo->query("SELECT 1");
            // Add legacy test columns if needed
            $this->ensureTestCompatibility();
            
        } catch (PDOException $e) {
            // If initial connection fails, try to create database
            if ($e->getCode() == 1049) { // Database doesn't exist
                $this->createDatabase();
            } else {
                error_log("Database connection failed: " . $e->getMessage());
                
                // Try alternative configurations
                $this->tryAlternativeConfigs($e);
            }
        }
    }
    
    /**
     * Try alternative database configurations
     */
    private function tryAlternativeConfigs($originalError) {
        $configs = [
            ['localhost', 'root', ''],
            ['localhost', 'root', 'root'],
            ['localhost', 'student', 'student'],
            ['127.0.0.1', 'root', ''],
            ['mysql', 'student', 'student'], // Docker fallback
        ];
        
        foreach ($configs as $config) {
            try {
                $dsn = "mysql:host={$config[0]};dbname={$this->database};charset={$this->charset}";
                $this->pdo = new PDO($dsn, $config[1], $config[2], $this->options);
                
                $this->host = $config[0];
                $this->username = $config[1];
                $this->password = $config[2];
                
                // Test the connection
                $this->pdo->query("SELECT 1");
                return; // Success!
                
            } catch (PDOException $e) {
                continue; // Try next configuration
            }
        }
        
        // If all configurations fail, throw the original error
        throw new Exception("Database connection failed. Please check your database configuration. Original error: " . $originalError->getMessage());
    }
    
    /**
     * Create database if it doesn't exist
     */
    private function createDatabase() {
        try {
            $dsn = "mysql:host={$this->host};charset={$this->charset}";
            $pdo = new PDO($dsn, $this->username, $this->password, $this->options);
            
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Now connect to the created database
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            $this->pdo = new PDO($dsn, $this->username, $this->password, $this->options);
            
            // Initialize database schema
            $this->initializeSchema();
            $this->ensureTestCompatibility();
            
        } catch (PDOException $e) {
            throw new Exception("Failed to create database: " . $e->getMessage());
        }
    }
    
    /**
     * Initialize database schema if tables don't exist
     */
    private function initializeSchema() {
        try {
            // Check if tables exist
            $stmt = $this->pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tables)) {
                // Load and execute schema
                $schemaFile = __DIR__ . '/../database/doko_schema.sql';
                if (file_exists($schemaFile)) {
                    $schema = file_get_contents($schemaFile);
                    // Sanitize: remove DROP/CREATE/USE statements to avoid dropping active DB connection
                    $filtered = [];
                    foreach (preg_split('/;\s*\n/', $schema) as $segment) {
                        $trim = trim($segment);
                        if ($trim === '') continue;
                        $prefix = strtoupper(substr($trim,0,20));
                        if (strpos($prefix, 'DROP DATABASE') === 0) continue;
                        if (strpos($prefix, 'CREATE DATABASE') === 0) continue;
                        if (preg_match('/^USE\s+/i', $trim)) continue;
                        $filtered[] = $trim;
                    }
                    foreach ($filtered as $sql) {
                        try {
                            $this->pdo->exec($sql . ';');
                        } catch (Throwable $e) {
                            // Log but continue so one failing statement (e.g., privilege issues) doesn't block others
                            error_log('Schema init statement failed: ' . $e->getMessage());
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Schema initialization error: " . $e->getMessage());
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
     * Provide direct prepare passthrough so tests using $this->db->prepare() work.
     */
    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }
    
    /**
     * Execute a query with parameters
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }

    /**
     * Convenience: fetch single row
     */
    public function fetchRow($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Convenience: fetch all rows
     */
    public function fetchAll($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get the last insert ID
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
     * Check if we're in a transaction
     */
    public function inTransaction() {
        return $this->pdo->inTransaction();
    }
    
    /**
     * Get database info for debugging
     */
    public function getInfo() {
        return [
            'host' => $this->host,
            'database' => $this->database,
            'username' => $this->username,
            'connected' => $this->pdo !== null
        ];
    }

    /**
     * Ensure legacy columns required by older test fixtures exist.
     */
    public function ensureTestCompatibility() {
        try {
            // Orders legacy shipping columns
            $cols = $this->pdo->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_COLUMN);
            $needed = ['shipping_address','shipping_city','shipping_state','shipping_zip'];
            foreach ($needed as $c) {
                if (!in_array($c, $cols)) {
                    $this->pdo->exec("ALTER TABLE orders ADD COLUMN $c VARCHAR(255) NULL AFTER status");
                }
            }
            // Categories legacy status column expected by some tests
            $catCols = $this->pdo->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('status', $catCols)) {
                $this->pdo->exec("ALTER TABLE categories ADD COLUMN status ENUM('active','inactive') DEFAULT 'active' AFTER description");
            }
        } catch (Throwable $e) {
            // ignore
        }
    }
}
?>

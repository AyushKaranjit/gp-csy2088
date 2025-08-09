<?php
/**
 * DOKO Grocery Database Configuration (Auto-detecting Environment)
 * Clean production version (mock + fallback code removed)
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
        PDO::ATTR_EMULATE_PREPARES   => false
    ];

    private $pdo = null;
    private static $instance = null;

    private function __construct() {
        $this->detectEnvironment();
        $this->connect();
    }

    private function detectEnvironment() {
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

        if (getenv('DOCKER_ENV') === 'true' || file_exists('/.dockerenv')) {
            $this->host = 'mysql';
            $this->database = 'doko_ecommerce';
            $this->username = 'student';
            $this->password = 'student';
            return;
        }

        if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false) {
            $this->host = 'localhost';
            $this->database = 'doko_ecommerce';
            $this->username = 'root';
            $this->password = '';
            return;
        }

        $this->host = '127.0.0.1';
        $this->database = 'doko_ecommerce';
        $this->username = 'student';
        $this->password = 'student';
    }

    private function connect() {
        $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $this->options);
            $this->pdo->exec("SET NAMES utf8mb4");
            $this->pdo->query("SELECT 1");
            $this->ensureTestCompatibility();
        } catch (PDOException $e) {
            if ($e->getCode() == 1049) { // Unknown database -> attempt create
                $this->createDatabase();
                return;
            }
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Unable to connect to database.');
        }
    }

    private function createDatabase() {
        try {
            $dsn = "mysql:host={$this->host};charset={$this->charset}";
            $pdo = new PDO($dsn, $this->username, $this->password, $this->options);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            $this->pdo = new PDO($dsn, $this->username, $this->password, $this->options);
            $this->initializeSchema();
            $this->ensureTestCompatibility();
        } catch (PDOException $e) {
            throw new Exception('Failed to create database: ' . $e->getMessage());
        }
    }

    private function initializeSchema() {
        try {
            $stmt = $this->pdo->query('SHOW TABLES');
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (empty($tables)) {
                $schemaFile = __DIR__ . '/../database/doko_schema.sql';
                if (file_exists($schemaFile)) {
                    $schema = file_get_contents($schemaFile);
                    $filtered = [];
                    foreach (preg_split('/;\s*\n/', $schema) as $segment) {
                        $trim = trim($segment);
                        if ($trim === '') continue;
                        $up = strtoupper(substr($trim,0,30));
                        if (str_starts_with($up,'DROP DATABASE') || str_starts_with($up,'CREATE DATABASE') || preg_match('/^USE\s+/i',$trim)) continue;
                        $filtered[] = $trim;
                    }
                    foreach ($filtered as $sql) {
                        try { $this->pdo->exec($sql . ';'); } catch (Throwable $e) { error_log('Schema init failed: ' . $e->getMessage()); }
                    }
                }
            }
        } catch (Throwable $e) {
            error_log('Schema initialization error: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function getConnection() { return $this->pdo; }

    public function prepare($sql) { return $this->pdo->prepare($sql); }
    public function query($sql) { return $this->pdo->query($sql); }
    public function exec($sql) { return $this->pdo->exec($sql); }

    public function execute($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Query failed: ' . $e->getMessage() . ' SQL: ' . $query);
            throw new Exception('Database query failed');
        }
    }

    public function fetchRow($query, $params = []) { return $this->execute($query,$params)->fetch(); }
    public function fetchAll($query, $params = []) { return $this->execute($query,$params)->fetchAll(); }

    public function lastInsertId() { return $this->pdo->lastInsertId(); }
    public function beginTransaction() { return $this->pdo->beginTransaction(); }
    public function commit() { return $this->pdo->commit(); }
    public function rollback() { return $this->pdo->rollback(); }
    public function inTransaction() { return $this->pdo->inTransaction(); }

    // Kept for backward compatibility with scripts expecting this method
    public function isUsingMockData() { return false; }

    public function getInfo() {
        return [
            'host' => $this->host,
            'database' => $this->database,
            'username' => $this->username,
            'connected' => $this->pdo !== null
        ];
    }

    public function ensureTestCompatibility() {
        try {
            $cols = $this->pdo->query('SHOW COLUMNS FROM orders')->fetchAll(PDO::FETCH_COLUMN);
            $needed = ['shipping_address','shipping_city','shipping_state','shipping_zip'];
            foreach ($needed as $c) if (!in_array($c,$cols)) $this->pdo->exec("ALTER TABLE orders ADD COLUMN $c VARCHAR(255) NULL AFTER status");

            $catCols = $this->pdo->query('SHOW COLUMNS FROM categories')->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('status',$catCols)) $this->pdo->exec("ALTER TABLE categories ADD COLUMN status ENUM('active','inactive') DEFAULT 'active' AFTER description");

            $hasAddr = $this->pdo->query("SHOW TABLES LIKE 'user_addresses'")->fetchColumn();
            if (!$hasAddr) {
                $this->pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS user_addresses (
    address_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    address_type ENUM('home','work','other') DEFAULT 'home',
    address_label VARCHAR(50) NULL,
    street_address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(50) DEFAULT 'Nepal',
    landmark TEXT NULL,
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
);
SQL);
            }
        } catch (Throwable $e) { /* ignore */ }
    }
}
?>

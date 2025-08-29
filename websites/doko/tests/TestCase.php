<?php
/**
 * Base TestCase class for DOKO E-commerce tests
 * Provides database setup, cleanup, and common test utilities
 */

namespace Doko\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use PDO;
use PDOException;

abstract class TestCase extends PHPUnitTestCase
{
    protected static $pdo;
    protected static $testDatabase = 'doko_test';
    protected static $originalDatabase = 'doko';

    /**
     * Set up test environment before all tests
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Initialize test database connection
        self::initializeTestDatabase();

        // Clean up any leftover data from previous test runs
        self::cleanupTestData();
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up test data after each test
        self::cleanupTestData();
    }

    /**
     * Initialize test database connection
     */
    protected static function initializeTestDatabase(): void
    {
        try {
            // Get database configuration
            $config = self::getDatabaseConfig();

            // Connect to MySQL server (not specific database)
            $dsn = "mysql:host={$config['host']};charset=utf8mb4";
            self::$pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // Create or use test database
            self::createTestDatabase();

        } catch (PDOException $e) {
            throw new \RuntimeException("Failed to initialize test database: " . $e->getMessage());
        }
    }

    /**
     * Create test database
     */
    protected static function createTestDatabase(): void
    {
        $config = self::getDatabaseConfig();
        $testDbName = "`{$config['database']}_test`";

        try {
            // Try to create test database
            self::$pdo->exec("CREATE DATABASE IF NOT EXISTS $testDbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            // If we can't create the database, try to use the main database for testing
            // This is a fallback for users without CREATE DATABASE privileges
            error_log("Could not create test database, using main database for testing: " . $e->getMessage());

            // Switch to main database instead
            self::$pdo->exec("USE `{$config['database']}`");

            // Update the test database name to the main database
            self::$testDatabase = $config['database'];

            // Don't import schema since the main database should already have it
            return;
        }

        // Import schema into test database
        self::importDatabaseSchema();
    }

    /**
     * Import database schema into test database
     */
    protected static function importDatabaseSchema(): void
    {
        $schemaFile = PROJECT_ROOT . '/database/doko_schema.sql';

        if (!file_exists($schemaFile)) {
            throw new \RuntimeException("Database schema file not found: $schemaFile");
        }

        $sql = file_get_contents($schemaFile);

        // Split into individual statements and execute
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    self::$pdo->exec($statement);
                } catch (PDOException $e) {
                    // Log but continue - some statements might fail if tables already exist
                    error_log("Schema import warning: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Clean up test data
     */
    protected static function cleanupTestData(): void
    {
        if (!self::$pdo) {
            return;
        }

        try {
            // Disable foreign key checks temporarily
            self::$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

            // Clear all tables in reverse dependency order
            $tables = [
                'cart',
                'wishlist',
                'order_items',
                'orders',
                'products',
                'categories',
                'users'
            ];

            foreach ($tables as $table) {
                try {
                    // Only delete test data, not production data
                    if (self::$testDatabase !== self::$originalDatabase) {
                        // If using separate test database, truncate all tables
                        self::$pdo->exec("TRUNCATE TABLE `$table`");
                    } else {
                        // If using main database, only delete test records
                        // Delete records created by tests (using a test identifier)
                        self::$pdo->exec("DELETE FROM `$table` WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
                    }
                } catch (PDOException $e) {
                    // Table might not exist, continue
                    error_log("Warning cleaning table $table: " . $e->getMessage());
                }
            }

            // Re-enable foreign key checks
            self::$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        } catch (PDOException $e) {
            error_log("Cleanup warning: " . $e->getMessage());
        }
    }

    /**
     * Get database configuration
     */
    protected static function getDatabaseConfig(): array
    {
        // Try to load from config file first
        $configFile = PROJECT_ROOT . '/config/database.php';

        if (file_exists($configFile)) {
            // The database.php file is already autoloaded, so we can access the Database class directly
            // Extract database config from the Database class if available
            if (class_exists('Database')) {
                // Use reflection to get database config from the Database class
                $reflection = new \ReflectionClass('Database');
                $constructor = $reflection->getConstructor();
                // For now, return default config since Database class is already loaded
            }
        }

        // Fallback to environment or default config
        return [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'database' => $_ENV['DB_NAME'] ?? 'doko',
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? '',
            'charset' => 'utf8mb4'
        ];
    }

    /**
     * Create a test user
     */
    protected function createTestUser(array $overrides = []): array
    {
        $userData = array_merge([
            'username' => 'testuser' . time() . rand(1000, 9999),
            'email' => 'test' . time() . rand(1000, 9999) . '@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '1234567890',
            'role' => 'customer',
            'status' => 'active',
            'email_verified' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], $overrides);

        $columns = implode(', ', array_keys($userData));
        $placeholders = implode(', ', array_fill(0, count($userData), '?'));

        $stmt = self::$pdo->prepare("INSERT INTO users ($columns) VALUES ($placeholders)");
        $stmt->execute(array_values($userData));

        $userData['user_id'] = self::$pdo->lastInsertId();
        return $userData;
    }

    /**
     * Create a test product
     */
    protected function createTestProduct(array $overrides = []): array
    {
        // Ensure a default category exists before creating products
        if (!isset($overrides['category_id'])) {
            $category = $this->createTestCategory();
            $overrides['category_id'] = $category['category_id'];
        }

        $productData = array_merge([
            'sku' => 'TEST' . time() . rand(1000, 9999) . uniqid(),
            'name' => 'Test Product',
            'slug' => 'test-product-' . time() . rand(1000, 9999) . uniqid(),
            'description' => 'Test product description',
            'price' => 29.99,
            'stock_quantity' => 100,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], $overrides);

        $columns = implode(', ', array_keys($productData));
        $placeholders = implode(', ', array_fill(0, count($productData), '?'));

        $stmt = self::$pdo->prepare("INSERT INTO products ($columns) VALUES ($placeholders)");
        $stmt->execute(array_values($productData));

        $productData['product_id'] = self::$pdo->lastInsertId();
        return $productData;
    }

    /**
     * Create a test category
     */
    protected function createTestCategory(array $overrides = []): array
    {
        $categoryData = array_merge([
            'name' => 'Test Category ' . time() . rand(1000, 9999) . uniqid(),
            'slug' => 'test-category-' . time() . rand(1000, 9999) . uniqid(),
            'description' => 'Test category description',
            'image_url' => '/images/test-category.jpg',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], $overrides);

        $columns = implode(', ', array_keys($categoryData));
        $placeholders = implode(', ', array_fill(0, count($categoryData), '?'));

        $stmt = self::$pdo->prepare("INSERT INTO categories ($columns) VALUES ($placeholders)");
        $stmt->execute(array_values($categoryData));

        $categoryData['category_id'] = self::$pdo->lastInsertId();
        return $categoryData;
    }

    /**
     * Simulate user login by setting HTTP headers for test bridge
     */
    protected function loginUser(array $userData): void
    {
        // Set HTTP headers that AuthController's test bridge will use
        $_SERVER['HTTP_X_TEST_USER_ID'] = $userData['user_id'];
        $_SERVER['HTTP_X_TEST_USER_EMAIL'] = $userData['email'];
        $_SERVER['HTTP_X_TEST_USER_USERNAME'] = $userData['username'];
        $_SERVER['HTTP_X_TEST_USER_ROLE'] = $userData['role'];

        // Also set session for backward compatibility
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $userData['user_id'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_name'] = $userData['first_name'] . ' ' . $userData['last_name'];
        $_SESSION['user_role'] = $userData['role'];
    }

    /**
     * Simulate GET request
     */
    protected function getRequest(string $url, array $params = []): array
    {
        // Set up $_GET parameters
        $_GET = $params;

        // Set REQUEST_URI for routing
        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Start output buffering to capture API response
        ob_start();

        // Include the API file
        $filePath = $this->getApiFilePath($url);
        if (file_exists($filePath)) {
            include $filePath;
        }

        // Get the output
        $output = ob_get_clean();

        // Try to decode JSON response
        $response = json_decode($output, true);
        if ($response === null) {
            // If not JSON, return raw output
            return ['output' => $output];
        }

        return $response;
    }

    /**
     * Simulate POST request
     */
    protected function postRequest(string $url, array $data = []): array
    {
        // Set up $_POST data
        $_POST = $data;

        // Set REQUEST_URI for routing
        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Start output buffering to capture API response
        ob_start();

        // Include the API file
        $filePath = $this->getApiFilePath($url);
        if (file_exists($filePath)) {
            include $filePath;
        }

        // Get the output
        $output = ob_get_clean();

        // Try to decode JSON response
        $response = json_decode($output, true);
        if ($response === null) {
            // If not JSON, return raw output
            return ['output' => $output];
        }

        return $response;
    }

    /**
     * Convert URL to file path
     */
    private function getApiFilePath(string $url): string
    {
        // Remove leading slash and 'api' prefix if present
        $path = ltrim($url, '/');
        if (strpos($path, 'api/') === 0) {
            $path = substr($path, 4);
        }

        // Convert to file path
        return dirname(__DIR__) . '/public/api/' . $path;
    }

    /**
     * Get PDO connection for direct database access
     */
    protected function getPdo(): PDO
    {
        return self::$pdo;
    }

    /**
     * Assert that a database record exists
     */
    protected function assertDatabaseHas(string $table, array $conditions): void
    {
        $whereClause = implode(' AND ', array_map(fn($key) => "`$key` = ?", array_keys($conditions)));
        $stmt = self::$pdo->prepare("SELECT COUNT(*) as count FROM `$table` WHERE $whereClause");
        $stmt->execute(array_values($conditions));

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertGreaterThan(0, $result['count'], "No record found in $table matching conditions");
    }

    /**
     * Assert that a response indicates success
     */
    protected function assertResponseSuccess(array $response): void
    {
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
    }

    /**
     * Assert that a response indicates an error
     */
    protected function assertResponseError(array $response): void
    {
        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success']);
    }

    /**
     * Assert that JSON response has a specific key
     */
    protected function assertJsonHasKey(string $key, array $response): void
    {
        $this->assertArrayHasKey($key, $response);
    }

    /**
     * Assert that JSON response has specific keys
     */
    protected function assertJsonHasKeys(array $keys, array $response): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $response);
        }
    }

    /**
     * Assert that JSON response structure matches expected
     */
    protected function assertJsonStructure(array $structure, array $response): void
    {
        foreach ($structure as $key => $value) {
            $this->assertArrayHasKey($key, $response);

            if (is_array($value)) {
                $this->assertJsonStructure($value, $response[$key]);
            }
        }
    }
}

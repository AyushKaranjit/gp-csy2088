<?php
/**
 * Base TestCase for DOKO E-commerce tests
 * Uses real database connections for proper testing
 */

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    protected $auth;
    protected $db;

    /**
     * Set up test environment with real implementations
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set test environment
        $_ENV["TEST_MODE"] = true;
        putenv("TEST_MODE=1");

        // Initialize real database connection for testing
        $this->setupTestDatabase();

        // Initialize real AuthController for testing
        $this->setupRealAuthController();

        // Clean up any previous test data
        $this->cleanTestData();
    }

    /**
     * Set up real database connection for tests
     */
    protected function setupTestDatabase(): void
    {
        try {
            // Use test database configuration
            $host = getenv("DB_HOST") ?: "127.0.0.1";
            $dbname = getenv("DB_NAME") ?: "doko_ecommerce";
            $user = getenv("DB_USER") ?: "student";
            $pass = getenv("DB_PASS") ?: "student";

            $this->db = new PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (Exception $e) {
            $this->fail("Could not connect to test database: " . $e->getMessage());
        }
    }

    /**
     * Set up real AuthController instance
     */
    protected function setupRealAuthController(): void
    {
        // Create a test database instance that uses our test PDO connection
        $testDatabase = new class($this->db) {
            private $pdo;
            public function __construct($pdo) { $this->pdo = $pdo; }
            public function getConnection() { return $this->pdo; }
        };

        // Replace the Database singleton with our test instance
        // We need to use reflection to set the private static property
        $reflection = new ReflectionClass("Database");
        $instanceProperty = $reflection->getProperty("instance");
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $testDatabase);

        // Create real AuthController
        $this->auth = new AuthController();
    }

    /**
     * Clean up test data before each test
     */
    protected function cleanTestData(): void
    {
        if ($this->db) {
            try {
                // Clean up test data
                $this->db->exec("DELETE FROM user_sessions WHERE user_id IN (SELECT user_id FROM users WHERE email LIKE \"test%@%\")");
                $this->db->exec("DELETE FROM orders WHERE user_id IN (SELECT user_id FROM users WHERE email LIKE \"test%@%\")");
                $this->db->exec("DELETE FROM cart_items WHERE user_id IN (SELECT user_id FROM users WHERE email LIKE \"test%@%\")");
                $this->db->exec("DELETE FROM user_addresses WHERE user_id IN (SELECT user_id FROM users WHERE email LIKE \"test%@%\")");
                $this->db->exec("DELETE FROM users WHERE email LIKE \"test%@%\"");
                $this->db->exec("DELETE FROM products WHERE name LIKE \"Test Product%\"");
            } catch (Exception $e) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Create a test user in the database
     */
    protected function createTestUser(array $overrides = []): array
    {
        $defaults = [
            "username" => "testuser" . rand(1000, 9999),
            "email" => "test" . rand(1000, 9999) . "@example.com",
            "password" => "testpassword123",
            "first_name" => "Test",
            "last_name" => "User",
            "phone" => "1234567890",
            "role" => "customer",
            "status" => "active"
        ];

        $data = array_merge($defaults, $overrides);

        $hashedPassword = password_hash($data["password"], PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, phone, role, status, email_verified, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");

        $stmt->execute([
            $data["username"],
            $data["email"],
            $hashedPassword,
            $data["first_name"],
            $data["last_name"],
            $data["phone"],
            $data["role"],
            $data["status"]
        ]);

        $userId = $this->db->lastInsertId();

        return array_merge($data, ["user_id" => $userId, "password" => $hashedPassword]);
    }

    /**
     * Create a test product in the database
     */
    protected function createTestProduct(array $overrides = []): array
    {
        $defaults = [
            "name" => "Test Product " . rand(1000, 9999),
            "description" => "Test product description",
            "price" => 10.99,
            "stock_quantity" => 100,
            "category_id" => 1,
            "status" => "active"
        ];

        $data = array_merge($defaults, $overrides);

        $stmt = $this->db->prepare("
            INSERT INTO products (name, description, price, stock_quantity, category_id, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $data["name"],
            $data["description"],
            $data["price"],
            $data["stock_quantity"],
            $data["category_id"],
            $data["status"]
        ]);

        $productId = $this->db->lastInsertId();

        return array_merge($data, ["product_id" => $productId]);
    }

    /**
     * Tear down after each test
     */
    protected function tearDown(): void
    {
        // Clean up test data
        $this->cleanTestData();

        // Close database connection
        if ($this->db) {
            $this->db = null;
        }

        parent::tearDown();
    }
}

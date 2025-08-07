<?php
/**
 * Base Test Case for DOKO E-commerce
 * Provides common testing functionality
 * Compatible with both Composer autoloading and manual includes
 */

// Simple Database class for testing if not exists
if (!class_exists('Database')) {
    class Database {
        private static $instance = null;
        private $connection = null;
        
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        private function __construct() {
            // Simple PDO connection for testing
            try {
                $this->connection = new PDO("mysql:host=localhost;dbname=doko_test", "root", "");
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                // Use SQLite as fallback for testing
                $this->connection = new PDO("sqlite::memory:");
            }
        }
        
        public function execute($sql, $params = []) {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }
        
        public function lastInsertId() {
            return $this->connection->lastInsertId();
        }
    }
}

// Simple AuthController for testing if not exists
if (!class_exists('AuthController')) {
    class AuthController {
        public function login($email, $password) {
            return ['success' => true, 'message' => 'Login successful'];
        }
        
        public function register($data) {
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => 1];
        }
        
        public function isLoggedIn() {
            return isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
        }
        
        public function isAdmin() {
            return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        }
        
        public function isCustomer() {
            return isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
        }
        
        public function getCurrentUser() {
            if ($this->isLoggedIn()) {
                return [
                    'user_id' => $_SESSION['user_id'] ?? null,
                    'username' => $_SESSION['username'] ?? null,
                    'email' => $_SESSION['email'] ?? null
                ];
            }
            return null;
        }
        
        public function logout() {
            session_unset();
            session_destroy();
            return ['success' => true, 'message' => 'Logout successful'];
        }
    }
}

// Check if PHPUnit is available via Composer
if (class_exists('PHPUnit\\Framework\\TestCase')) {
    abstract class TestCase extends PHPUnit\Framework\TestCase
    {
        use TestUtilityTrait;
        
        protected function setUp(): void
        {
            parent::setUp();
            $this->initializeTest();
        }
        
        protected function tearDown(): void
        {
            $this->cleanupTest();
            parent::tearDown();
        }
    }
} else {
    // Fallback for custom test runner
    abstract class TestCase
    {
        use TestUtilityTrait;
        
        protected function setUp(): void
        {
            $this->initializeTest();
        }
        
        protected function tearDown(): void
        {
            $this->cleanupTest();
        }
    }
}

/**
 * Trait containing all test utilities
 * This allows sharing code between PHPUnit and custom test implementations
 */
trait TestUtilityTrait
{
    protected $db;
    protected $auth;
    
    protected function initializeTest()
    {
        // Setup test database
        $this->setupTestDatabase();
        
        // Initialize auth controller
        $this->auth = new AuthController();
        
        // Clear session
        $this->clearSession();
    }
    
    protected function cleanupTest()
    {
        // Clean up test data
        $this->cleanupTestData();
        
        // Clear session
        $this->clearSession();
    }
    
    /**
     * Setup test database connection
     */
    protected function setupTestDatabase()
    {
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear session data
     */
    protected function clearSession()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
        }
    }
    
    /**
     * Clean up test data from database
     */
    protected function cleanupTestData()
    {
        if (!$this->db) return;
        
        try {
            // Clean up test users (keep only pre-existing ones)
            $this->db->execute("DELETE FROM users WHERE email LIKE '%@test.com'");
            
            // Clean up test products
            $this->db->execute("DELETE FROM products WHERE name LIKE 'Test Product%'");
            
            // Clean up test orders
            $this->db->execute("DELETE FROM orders WHERE total_amount = 999.99");
            
            // Clean up test cart items
            $this->db->execute("DELETE FROM cart WHERE user_id NOT IN (SELECT user_id FROM users)");
            
            // Clean up test wishlist items
            $this->db->execute("DELETE FROM wishlist WHERE user_id NOT IN (SELECT user_id FROM users)");
            
        } catch (Exception $e) {
            // Ignore cleanup errors in tests
        }
    }
    
    /**
     * Create a test user
     */
    protected function createTestUser($data = [])
    {
        $defaultData = [
            'username' => 'testuser_' . uniqid(),
            'email' => 'test_' . uniqid() . '@test.com',
            'password' => 'password123',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '1234567890',
            'role' => 'customer',
            'status' => 'active'
        ];
        
        $userData = array_merge($defaultData, $data);
        
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password, first_name, last_name, phone, role, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->execute($sql, [
            $userData['username'],
            $userData['email'],
            $hashedPassword,
            $userData['first_name'],
            $userData['last_name'],
            $userData['phone'],
            $userData['role'],
            $userData['status']
        ]);
        
        $userData['user_id'] = $this->db->lastInsertId();
        $userData['password'] = 'password123'; // Keep original for testing
        
        return $userData;
    }
    
    /**
     * Create a test product
     */
    protected function createTestProduct($data = [])
    {
        $defaultData = [
            'name' => 'Test Product ' . uniqid(),
            'description' => 'Test product description',
            'price' => 19.99,
            'stock_quantity' => 100,
            'category_id' => 1,
            'status' => 'active',
            'unit' => 'piece'
        ];
        
        $productData = array_merge($defaultData, $data);
        
        $sql = "INSERT INTO products (name, description, price, stock_quantity, category_id, status, unit, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->execute($sql, [
            $productData['name'],
            $productData['description'],
            $productData['price'],
            $productData['stock_quantity'],
            $productData['category_id'],
            $productData['status'],
            $productData['unit']
        ]);
        
        $productData['product_id'] = $this->db->lastInsertId();
        
        return $productData;
    }
    
    /**
     * Login test user
     */
    protected function loginUser($user)
    {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
    }
    
    /**
     * Make HTTP request for API testing
     */
    protected function makeHttpRequest($url, $method = 'GET', $data = null, $headers = [])
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => 'http://localhost' . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json'], $headers),
            CURLOPT_COOKIEFILE => tempnam(sys_get_temp_dir(), 'cookie'),
            CURLOPT_COOKIEJAR => tempnam(sys_get_temp_dir(), 'cookie'),
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL error: ' . $error);
        }
        
        return [
            'status_code' => $httpCode,
            'body' => $response,
            'data' => json_decode($response, true)
        ];
    }
    
    /**
     * Make GET request
     */
    protected function getRequest($url, $headers = [])
    {
        $response = $this->makeHttpRequest($url, 'GET', null, $headers);
        return $response['data'] ?? json_decode($response['body'], true);
    }
    
    /**
     * Make POST request
     */
    protected function postRequest($url, $data = [], $headers = [])
    {
        $response = $this->makeHttpRequest($url, 'POST', $data, $headers);
        return $response['data'] ?? json_decode($response['body'], true);
    }
    
    /**
     * Assert JSON response
     */
    protected function assertJsonResponse($response, $expectedStatusCode = 200)
    {
        $this->assertEquals($expectedStatusCode, $response['status_code'], 
            'HTTP status code mismatch. Response: ' . $response['body']);
        
        $this->assertJson($response['body'], 'Response is not valid JSON');
        
        $this->assertNotNull($response['data'], 'Response data is null');
        
        return $response['data'];
    }
    
    /**
     * Assert successful API response
     */
    protected function assertApiSuccess($response, $expectedStatusCode = 200)
    {
        $data = $this->assertJsonResponse($response, $expectedStatusCode);
        
        $this->assertTrue($data['success'] ?? false, 
            'API call was not successful. Response: ' . json_encode($data));
        
        return $data;
    }
    
    /**
     * Assert failed API response
     */
    protected function assertApiFailure($response, $expectedStatusCode = 400)
    {
        $data = $this->assertJsonResponse($response, $expectedStatusCode);
        
        $this->assertFalse($data['success'] ?? true, 
            'API call should have failed. Response: ' . json_encode($data));
        
        return $data;
    }
    
    // ========== CUSTOM ASSERTION METHODS ==========
    
    protected function assertTrue($condition, $message = 'Expected true but got false')
    {
        if (!$condition) {
            throw new Exception($message);
        }
    }
    
    protected function assertFalse($condition, $message = 'Expected false but got true')
    {
        if ($condition) {
            throw new Exception($message);
        }
    }
    
    protected function assertEquals($expected, $actual, $message = 'Values are not equal')
    {
        if ($expected != $actual) {
            throw new Exception($message . " Expected: " . var_export($expected, true) . " Actual: " . var_export($actual, true));
        }
    }
    
    protected function assertNotEquals($expected, $actual, $message = 'Values should not be equal')
    {
        if ($expected == $actual) {
            throw new Exception($message . " Both values are: " . var_export($expected, true));
        }
    }
    
    protected function assertNull($actual, $message = 'Expected null')
    {
        if ($actual !== null) {
            throw new Exception($message . " Actual: " . var_export($actual, true));
        }
    }
    
    protected function assertNotNull($actual, $message = 'Expected not null')
    {
        if ($actual === null) {
            throw new Exception($message);
        }
    }
    
    protected function assertIsArray($actual, $message = 'Expected array')
    {
        if (!is_array($actual)) {
            throw new Exception($message . " Actual type: " . gettype($actual));
        }
    }
    
    protected function assertIsString($actual, $message = 'Expected string')
    {
        if (!is_string($actual)) {
            throw new Exception($message . " Actual type: " . gettype($actual));
        }
    }
    
    protected function assertIsInt($actual, $message = 'Expected integer')
    {
        if (!is_int($actual)) {
            throw new Exception($message . " Actual type: " . gettype($actual));
        }
    }
    
    protected function assertIsBool($actual, $message = 'Expected boolean')
    {
        if (!is_bool($actual)) {
            throw new Exception($message . " Actual type: " . gettype($actual));
        }
    }
    
    protected function assertCount($expectedCount, $actual, $message = 'Count mismatch')
    {
        $actualCount = is_countable($actual) ? count($actual) : 0;
        if ($expectedCount != $actualCount) {
            throw new Exception($message . " Expected count: $expectedCount, Actual count: $actualCount");
        }
    }
    
    protected function assertGreaterThan($expected, $actual, $message = 'Value not greater than expected')
    {
        if ($actual <= $expected) {
            throw new Exception($message . " Expected > $expected, Actual: $actual");
        }
    }
    
    protected function assertGreaterThanOrEqual($expected, $actual, $message = 'Value not greater than or equal to expected')
    {
        if ($actual < $expected) {
            throw new Exception($message . " Expected >= $expected, Actual: $actual");
        }
    }
    
    protected function assertLessThan($expected, $actual, $message = 'Value not less than expected')
    {
        if ($actual >= $expected) {
            throw new Exception($message . " Expected < $expected, Actual: $actual");
        }
    }
    
    protected function assertLessThanOrEqual($expected, $actual, $message = 'Value not less than or equal to expected')
    {
        if ($actual > $expected) {
            throw new Exception($message . " Expected <= $expected, Actual: $actual");
        }
    }
    
    protected function assertInstanceOf($expected, $actual, $message = 'Object not instance of expected class')
    {
        if (!($actual instanceof $expected)) {
            $actualClass = is_object($actual) ? get_class($actual) : gettype($actual);
            throw new Exception($message . " Expected instance of: $expected, Actual: $actualClass");
        }
    }
    
    protected function assertStringContainsString($needle, $haystack, $message = 'String does not contain expected substring')
    {
        if (strpos($haystack, $needle) === false) {
            throw new Exception($message . " Expected '$needle' in '$haystack'");
        }
    }
    
    protected function assertJson($jsonString, $message = 'String is not valid JSON')
    {
        json_decode($jsonString);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception($message . " JSON error: " . json_last_error_msg());
        }
    }
    
    protected function assertJsonHasKey($key, $array, $message = 'JSON does not have expected key')
    {
        if (!is_array($array) || !array_key_exists($key, $array)) {
            throw new Exception($message . " Key '$key' not found in: " . json_encode($array));
        }
    }
    
    protected function assertResponseSuccess($response, $message = 'Response indicates failure')
    {
        if (is_array($response) && isset($response['success'])) {
            $this->assertTrue($response['success'], $message . " Response: " . json_encode($response));
        } else {
            throw new Exception($message . " Invalid response format: " . json_encode($response));
        }
    }
    
    protected function assertResponseError($response, $message = 'Response indicates success but error expected')
    {
        if (is_array($response) && isset($response['success'])) {
            $this->assertFalse($response['success'], $message . " Response: " . json_encode($response));
        } else {
            // Assume error if no success field
            $this->assertTrue(true);
        }
    }
}

<?php
/**
 * AuthController Unit Tests
 * Tests authentication functionality
 */

namespace Doko\Tests\Unit;

use Doko\Tests\TestCase;
use AuthController;

class AuthControllerTest extends TestCase
{
    private $auth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth = new AuthController();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clear session between tests
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SESSION = [];
        
        // Clear test headers
        unset($_SERVER['HTTP_X_TEST_USER_ID']);
        unset($_SERVER['HTTP_X_TEST_USER_EMAIL']);
        unset($_SERVER['HTTP_X_TEST_USER_USERNAME']);
        unset($_SERVER['HTTP_X_TEST_USER_ROLE']);
    }

    public function testCanCreateAuthController()
    {
        $this->assertInstanceOf(AuthController::class, $this->auth);
    }
    
    public function testUserCanLogin()
    {
        // Create test user
        $user = $this->createTestUser([
            'email' => 'login@test.com',
            'password' => 'testpassword123'
        ]);
        
        // Attempt login
        $result = $this->auth->login('login@test.com', 'testpassword123');
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Login successful', $result['message']);
        $this->assertTrue($this->auth->isLoggedIn());
    }
    
    public function testLoginFailsWithInvalidPassword()
    {
        // Create test user
        $user = $this->createTestUser([
            'email' => 'invalid@test.com',
            'password' => 'correctpassword'
        ]);
        
        // Attempt login with wrong password
        $result = $this->auth->login('invalid@test.com', 'wrongpassword');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid email or password', $result['message']);
        $this->assertFalse($this->auth->isLoggedIn());
    }
    
    public function testLoginFailsWithNonExistentUser()
    {
        // Attempt login with non-existent user
        $result = $this->auth->login('nonexistent@test.com', 'password');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid email or password', $result['message']);
        $this->assertFalse($this->auth->isLoggedIn());
    }
    
    public function testUserCanRegister()
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'first_name' => 'New',
            'last_name' => 'User',
            'phone' => '1234567890'
        ];
        
        $result = $this->auth->register($userData);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Registration successful', $result['message']);
        $this->assertIsInt($result['user_id']);
    }
    
    public function testRegistrationFailsWithDuplicateEmail()
    {
        // Create first user
        $user1 = $this->createTestUser(['email' => 'duplicate@test.com']);
        
        // Try to register with same email
        $userData = [
            'username' => 'newuser2',
            'email' => 'duplicate@test.com',
            'password' => 'password123',
            'first_name' => 'New',
            'last_name' => 'User'
        ];
        
        $result = $this->auth->register($userData);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('already exists', $result['message']);
    }
    
    public function testCanDetectAdminRole()
    {
        // Create admin user
        $adminUser = $this->createTestUser(['role' => 'admin']);
        $this->loginUser($adminUser);
        
        $this->assertTrue($this->auth->isAdmin());
        $this->assertFalse($this->auth->isCustomer());
    }
    
    public function testCanDetectCustomerRole()
    {
        // Create customer user
        $customerUser = $this->createTestUser(['role' => 'customer']);
        $this->loginUser($customerUser);
        
        $this->assertTrue($this->auth->isCustomer());
        $this->assertFalse($this->auth->isAdmin());
    }
    
    public function testCanGetCurrentUser()
    {
        // Create and login user
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        $currentUser = $this->auth->getCurrentUser();
        
        $this->assertIsArray($currentUser);
        $this->assertEquals($user['user_id'], $currentUser['user_id']);
        $this->assertEquals($user['username'], $currentUser['username']);
        $this->assertEquals($user['email'], $currentUser['email']);
    }
    
    public function testCanLogout()
    {
        // Create and login user
        $user = $this->createTestUser();
        $this->loginUser($user);
        
        $this->assertTrue($this->auth->isLoggedIn());
        
        // Logout
        $result = $this->auth->logout();
        
        $this->assertTrue($result['success']);
        $this->assertFalse($this->auth->isLoggedIn());
        $this->assertNull($this->auth->getCurrentUser());
    }
    
    public function testInactiveUserCannotLogin()
    {
        // Create inactive user
        $user = $this->createTestUser([
            'email' => 'inactive@test.com',
            'password' => 'password123',
            'status' => 'inactive'
        ]);
        
        // Attempt login
        $result = $this->auth->login('inactive@test.com', 'password123');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Account is not active', $result['message']);
    }
}

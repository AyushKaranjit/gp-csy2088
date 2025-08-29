<?php
/**
 * User Authentication Integration Tests
 * Tests complete user authentication workflow
 */

namespace Doko\Tests\Integration;

use Doko\Tests\TestCase;

class UserAuthIntegrationTest extends TestCase
{
    public function testCompleteUserRegistrationAndLoginFlow()
    {
        // 1. Register new user
        $userData = [
            'username' => 'integrationuser',
            'email' => 'integration@test.com',
            'password' => 'securepassword123',
            'first_name' => 'Integration',
            'last_name' => 'User',
            'phone' => '5551234567'
        ];
        
        $registerResponse = $this->postRequest('/api/users/auth-register.php', $userData);
        
        $this->assertResponseSuccess($registerResponse);
        $this->assertJsonHasKey('user_id', $registerResponse);
        $this->assertEquals('Registration successful', $registerResponse['message']);
        
        // 2. Login with registered credentials
        $loginData = [
            'email' => 'integration@test.com',
            'password' => 'securepassword123'
        ];
        
        $loginResponse = $this->postRequest('/api/users/auth-login.php', $loginData);
        
        $this->assertResponseSuccess($loginResponse);
        $this->assertEquals('Login successful', $loginResponse['message']);
        
        // 3. Verify user session is active
        $statusResponse = $this->getRequest('/api/users/auth-status.php');
        
        $this->assertResponseSuccess($statusResponse);
        $this->assertTrue($statusResponse['logged_in']);
        $this->assertEquals('integration@test.com', $statusResponse['user']['email']);
        
        // 4. Update user profile
        $profileData = [
            'first_name' => 'Updated Integration',
            'last_name' => 'Updated User',
            'phone' => '5559876543'
        ];
        
        $profileResponse = $this->postRequest('/api/users/auth-profile.php', $profileData);
        
        $this->assertResponseSuccess($profileResponse);
        $this->assertEquals('Profile updated successfully', $profileResponse['message']);
        
        // 5. Verify profile changes
        $updatedStatusResponse = $this->getRequest('/api/users/auth-status.php');
        $this->assertEquals('Updated Integration', $updatedStatusResponse['user']['first_name']);
        $this->assertEquals('Updated User', $updatedStatusResponse['user']['last_name']);
        
        // 6. Logout
        $logoutResponse = $this->postRequest('/api/users/auth-logout.php');
        
        $this->assertResponseSuccess($logoutResponse);
        $this->assertEquals('Logout successful', $logoutResponse['message']);
        
        // 7. Verify user is logged out
        $finalStatusResponse = $this->getRequest('/api/users/auth-status.php');
        $this->assertFalse($finalStatusResponse['logged_in']);
    }
    
    public function testPasswordChangeFlow()
    {
        // 1. Create and login user
        $user = $this->createTestUser([
            'email' => 'pwchange@test.com',
            'password' => 'oldpassword123'
        ]);
        $this->loginUser($user);
        
        // 2. Change password
        $changeData = [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword456',
            'confirm_password' => 'newpassword456'
        ];
        
        $changeResponse = $this->postRequest('/api/users/change-password.php', $changeData);
        
        $this->assertResponseSuccess($changeResponse);
        $this->assertEquals('Password changed successfully', $changeResponse['message']);
        
        // 3. Logout
        $this->postRequest('/api/users/auth-logout.php');
        
        // 4. Try login with old password (should fail)
        $oldLoginResponse = $this->postRequest('/api/users/auth-login.php', [
            'email' => 'pwchange@test.com',
            'password' => 'oldpassword123'
        ]);
        
        $this->assertResponseError($oldLoginResponse);
        
        // 5. Login with new password (should succeed)
        $newLoginResponse = $this->postRequest('/api/users/auth-login.php', [
            'email' => 'pwchange@test.com',
            'password' => 'newpassword456'
        ]);
        
        $this->assertResponseSuccess($newLoginResponse);
        $this->assertEquals('Login successful', $newLoginResponse['message']);
    }
    
    public function testUserRolePermissions()
    {
        // 1. Create customer user
        $customer = $this->createTestUser(['role' => 'customer']);
        $this->loginUser($customer);
        
        // 2. Try to access admin endpoint (should fail)
        $adminResponse = $this->getRequest('/api/admin/admin-users.php');
        $this->assertResponseError($adminResponse);
        $this->assertStringContainsString('access denied', strtolower($adminResponse['message']));
        
        // 3. Logout customer
        $this->postRequest('/api/users/auth-logout.php');
        
        // 4. Create and login admin user
        $admin = $this->createTestUser(['role' => 'admin']);
        $this->loginUser($admin);
        
        // 5. Access admin endpoint (should succeed)
        $adminResponse2 = $this->getRequest('/api/admin/admin-users.php');
        $this->assertResponseSuccess($adminResponse2);
        $this->assertJsonHasKey('users', $adminResponse2);
    }
    
    public function testAccountActivationFlow()
    {
        // 1. Create inactive user
        $inactiveUser = $this->createTestUser([
            'email' => 'inactive@test.com',
            'password' => 'password123',
            'status' => 'inactive'
        ]);
        
        // 2. Try to login (should fail)
        $loginResponse = $this->postRequest('/api/users/auth-login.php', [
            'email' => 'inactive@test.com',
            'password' => 'password123'
        ]);
        
        $this->assertResponseError($loginResponse);
        $this->assertStringContainsString('not active', strtolower($loginResponse['message']));
        
        // 3. Activate user (simulate admin action)
        $stmt = $this->getPdo()->prepare("UPDATE users SET status = 'active' WHERE user_id = ?");
        $stmt->execute([$inactiveUser['user_id']]);
        
        // 4. Try to login again (should succeed)
        $loginResponse2 = $this->postRequest('/api/users/auth-login.php', [
            'email' => 'inactive@test.com',
            'password' => 'password123'
        ]);
        
        $this->assertResponseSuccess($loginResponse2);
        $this->assertEquals('Login successful', $loginResponse2['message']);
    }
}

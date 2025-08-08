<?php
// Refactored auth status endpoint using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
require_method('GET');

try {
    $auth = auth_controller();
    if ($auth->isLoggedIn()) {
        $u = $auth->getCurrentUser();
        $role = $auth->getUserRole();
        $dashboard = match($role){
            'admin' => 'admin.php',
            'manager' => 'manager.php',
            'customer' => 'customer.php',
            default => 'index.php'
        };
        ApiResponse::success([
            'logged_in' => true,
            'user' => [
                'id' => $u['user_id'],
                'username' => $u['username'],
                'email' => $u['email'],
                'first_name' => $u['first_name'],
                'last_name' => $u['last_name'],
                'role' => $role,
                'full_name' => $u['first_name'].' '.$u['last_name']
            ],
            'dashboard_url' => $dashboard,
            'role_permissions' => [
                'is_admin' => $auth->isAdmin(),
                'is_customer' => $auth->isCustomer(),
                'has_admin_access' => $auth->hasAdminAccess()
            ]
        ]);
    } else {
        ApiResponse::success([
            'logged_in' => false,
            'user' => null,
            'message' => 'No active session'
        ]);
    }
} catch (Throwable $e) {
    error_log('status error: '.$e->getMessage());
    ApiResponse::error('Failed to check status', 500, ['exception' => $e->getMessage()]);
}

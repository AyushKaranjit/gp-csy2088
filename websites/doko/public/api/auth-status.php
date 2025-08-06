<?php
/**
 * Authentication Status API
 * Returns current user session information
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../template/config.php';
require_once '../../src/Controllers/AuthController.php';

try {
    $auth = new AuthController();
    
    if ($auth->isLoggedIn()) {
        $currentUser = $auth->getCurrentUser();
        $userRole = $auth->getUserRole();
        
        // Determine dashboard URL based on role
        $dashboardUrl = 'index.php'; // default
        switch ($userRole) {
            case 'admin':
                $dashboardUrl = 'admin.php';
                break;
            case 'manager': 
                $dashboardUrl = 'manager.php';
                break;
            case 'customer':
                $dashboardUrl = 'customer.php';
                break;
        }
        
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'id' => $currentUser['user_id'],
                'username' => $currentUser['username'],
                'email' => $currentUser['email'],
                'first_name' => $currentUser['first_name'],
                'last_name' => $currentUser['last_name'],
                'role' => $userRole,
                'full_name' => $currentUser['first_name'] . ' ' . $currentUser['last_name']
            ],
            'dashboard_url' => $dashboardUrl,
            'role_permissions' => [
                'is_admin' => $auth->isAdmin(),
                'is_manager' => $auth->isManager(),
                'is_customer' => $auth->isCustomer(),
                'has_admin_access' => $auth->hasAdminAccess(),
                'has_manager_access' => $auth->hasManagerAccess()
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'logged_in' => false,
            'user' => null,
            'message' => 'No active session'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error checking authentication status: ' . $e->getMessage()
    ]);
}
?>

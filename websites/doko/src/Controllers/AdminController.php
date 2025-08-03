<?php
namespace Controllers;

/**
 * Admin Controller
 * Handles admin-specific HTTP requests
 */
class AdminController {
    private $userModel;
    private $productModel;
    private $orderModel;
    private $authController;
    
    public function __construct() {
        $this->userModel = new \Models\User();
        $this->productModel = new \Models\Product();
        $this->orderModel = new \Models\Order();
        $this->authController = new AuthController();
    }
    
    /**
     * Get admin dashboard data
     */
    public function dashboard($params = []) {
        $this->authController->requireAdmin();
        
        try {
            $stats = [
                'users' => $this->userModel->getUserStats(),
                'orders' => $this->orderModel->getOrderStats(),
                'products' => $this->productModel->getProductCountByCategory()
            ];
            
            $recentOrders = $this->orderModel->getRecentOrders(10);
            $monthlyRevenue = $this->orderModel->getMonthlyRevenue();
            
            \Core\Router::json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_orders' => $recentOrders,
                    'monthly_revenue' => $monthlyRevenue
                ]
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get all users (Admin only)
     */
    public function users($params = []) {
        $this->authController->requireAdmin();
        
        try {
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 20;
            $offset = ($page - 1) * $limit;
            
            $users = $this->userModel->getAllCustomers($limit, $offset);
            
            \Core\Router::json([
                'success' => true,
                'data' => $users,
                'pagination' => [
                    'page' => (int)$page,
                    'limit' => (int)$limit
                ]
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get specific user (Admin only)
     */
    public function showUser($params = []) {
        $this->authController->requireAdmin();
        
        try {
            $userId = $params['id'] ?? null;
            
            if (!$userId) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'User ID is required'
                ], 400);
                return;
            }
            
            $user = $this->userModel->getUserById($userId);
            
            if (!$user) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'User not found'
                ], 404);
                return;
            }
            
            // Get user's orders
            $orders = $this->orderModel->getOrdersByUser($userId, 10);
            $user['orders'] = $orders;
            
            \Core\Router::json([
                'success' => true,
                'data' => $user
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update user (Admin only)
     */
    public function updateUser($params = []) {
        $this->authController->requireAdmin();
        
        try {
            $userId = $params['id'] ?? null;
            $input = \Core\Router::getInput();
            
            if (!$userId) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'User ID is required'
                ], 400);
                return;
            }
            
            $this->userModel->updateUser($userId, $input);
            $user = $this->userModel->getUserById($userId);
            
            \Core\Router::json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Delete user (Admin only)
     */
    public function deleteUser($params = []) {
        $this->authController->requireAdmin();
        
        try {
            $userId = $params['id'] ?? null;
            
            if (!$userId) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'User ID is required'
                ], 400);
                return;
            }
            
            // Don't allow deleting admin users
            $user = $this->userModel->getUserById($userId);
            if ($user && $this->userModel->isAdmin($userId)) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Cannot delete admin users'
                ], 403);
                return;
            }
            
            $this->userModel->deleteUser($userId);
            
            \Core\Router::json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
?>

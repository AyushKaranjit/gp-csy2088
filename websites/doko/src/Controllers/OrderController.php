<?php
namespace Controllers;

/**
 * Order Controller
 * Handles all order-related HTTP requests
 */
class OrderController {
    private $orderModel;
    private $productModel;
    private $authController;
    
    public function __construct() {
        $this->orderModel = new \Models\Order();
        $this->productModel = new \Models\Product();
        $this->authController = new AuthController();
    }
    
    /**
     * Create new order
     */
    public function create($params = []) {
        $this->authController->requireAuth();
        
        try {
            $input = \Core\Router::getInput();
            
            // Validate required fields
            $requiredFields = ['items', 'customer_name', 'customer_email', 'customer_phone', 'delivery_address'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    \Core\Router::json([
                        'success' => false,
                        'error' => "Field {$field} is required"
                    ], 400);
                    return;
                }
            }
            
            // Validate items
            if (!is_array($input['items']) || empty($input['items'])) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Order must contain at least one item'
                ], 400);
                return;
            }
            
            // Calculate total and validate items
            $totalAmount = 0;
            $validatedItems = [];
            
            foreach ($input['items'] as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity'])) {
                    \Core\Router::json([
                        'success' => false,
                        'error' => 'Each item must have product_id and quantity'
                    ], 400);
                    return;
                }
                
                $product = $this->productModel->getProductById($item['product_id']);
                if (!$product) {
                    \Core\Router::json([
                        'success' => false,
                        'error' => "Product not found: {$item['product_id']}"
                    ], 400);
                    return;
                }
                
                // Check stock
                if ($product['stock_quantity'] < $item['quantity']) {
                    \Core\Router::json([
                        'success' => false,
                        'error' => "Insufficient stock for product: {$product['name']}"
                    ], 400);
                    return;
                }
                
                $validatedItem = [
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'quantity' => (int)$item['quantity'],
                    'price' => (float)$product['price']
                ];
                
                $totalAmount += $validatedItem['quantity'] * $validatedItem['price'];
                $validatedItems[] = $validatedItem;
            }
            
            // Add delivery fee
            $deliveryFee = $input['delivery_fee'] ?? 50;
            $totalAmount += $deliveryFee;
            
            // Prepare order data
            session_start();
            $orderData = [
                'user_id' => $_SESSION['user_id'],
                'total_amount' => $totalAmount,
                'delivery_fee' => $deliveryFee,
                'customer_name' => $input['customer_name'],
                'customer_email' => $input['customer_email'],
                'customer_phone' => $input['customer_phone'],
                'delivery_address' => $input['delivery_address'],
                'payment_method' => $input['payment_method'] ?? 'cash_on_delivery'
            ];
            
            $orderId = $this->orderModel->createOrder($orderData, $validatedItems);
            $order = $this->orderModel->getOrderById($orderId);
            
            \Core\Router::json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ], 201);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Get user's orders
     */
    public function myOrders($params = []) {
        $this->authController->requireAuth();
        
        try {
            session_start();
            $userId = $_SESSION['user_id'];
            
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $offset = ($page - 1) * $limit;
            
            $orders = $this->orderModel->getOrdersByUser($userId, $limit, $offset);
            
            \Core\Router::json([
                'success' => true,
                'data' => $orders,
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
     * Get order details
     */
    public function show($params = []) {
        $this->authController->requireAuth();
        
        try {
            $orderId = $params['id'] ?? null;
            
            if (!$orderId) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Order ID is required'
                ], 400);
                return;
            }
            
            $order = $this->orderModel->getOrderById($orderId);
            
            if (!$order) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Order not found'
                ], 404);
                return;
            }
            
            // Check if user owns this order or is admin
            session_start();
            if ($order['user_id'] != $_SESSION['user_id'] && !$this->authController->checkAdmin()) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Access denied'
                ], 403);
                return;
            }
            
            // Get order items
            $orderItems = $this->orderModel->getOrderItems($orderId);
            $order['items'] = $orderItems;
            
            \Core\Router::json([
                'success' => true,
                'data' => $order
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Cancel order
     */
    public function cancel($params = []) {
        $this->authController->requireAuth();
        
        try {
            $orderId = $params['id'] ?? null;
            
            if (!$orderId) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Order ID is required'
                ], 400);
                return;
            }
            
            $order = $this->orderModel->getOrderById($orderId);
            
            if (!$order) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Order not found'
                ], 404);
                return;
            }
            
            // Check if user owns this order
            session_start();
            if ($order['user_id'] != $_SESSION['user_id'] && !$this->authController->checkAdmin()) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Access denied'
                ], 403);
                return;
            }
            
            // Check if order can be cancelled
            if (!in_array($order['status'], ['pending', 'processing'])) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Order cannot be cancelled'
                ], 400);
                return;
            }
            
            $this->orderModel->cancelOrder($orderId);
            
            \Core\Router::json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Get all orders (Admin only)
     */
    public function index($params = []) {
        $this->authController->requireAdmin();
        
        try {
            $status = $_GET['status'] ?? null;
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 20;
            $offset = ($page - 1) * $limit;
            
            $orders = $this->orderModel->getAllOrders($status, $limit, $offset);
            
            \Core\Router::json([
                'success' => true,
                'data' => $orders,
                'pagination' => [
                    'page' => (int)$page,
                    'limit' => (int)$limit,
                    'status' => $status
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
     * Update order status (Admin only)
     */
    public function updateStatus($params = []) {
        $this->authController->requireAdmin();
        
        try {
            $orderId = $params['id'] ?? null;
            $input = \Core\Router::getInput();
            
            if (!$orderId) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Order ID is required'
                ], 400);
                return;
            }
            
            if (!isset($input['status'])) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Status is required'
                ], 400);
                return;
            }
            
            $this->orderModel->updateOrderStatus($orderId, $input['status']);
            $order = $this->orderModel->getOrderById($orderId);
            
            \Core\Router::json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Get order statistics (Admin only)
     */
    public function stats($params = []) {
        $this->authController->requireAdmin();
        
        try {
            $stats = $this->orderModel->getOrderStats();
            $recentOrders = $this->orderModel->getRecentOrders(5);
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
}
?>

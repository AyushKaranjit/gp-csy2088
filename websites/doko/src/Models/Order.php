<?php
namespace Models;

use Core\Database;
use Models\Product;

/**
 * Order Model
 * Handles all order-related database operations
 */
class Order {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Create new order
     */
    public function createOrder($orderData, $orderItems) {
        try {
            $this->db->beginTransaction();
            
            // Create order
            $order = [
                'user_id' => $orderData['user_id'],
                'total_amount' => $orderData['total_amount'],
                'delivery_fee' => $orderData['delivery_fee'] ?? 50,
                'customer_name' => $orderData['customer_name'],
                'customer_email' => $orderData['customer_email'],
                'customer_phone' => $orderData['customer_phone'],
                'delivery_address' => $orderData['delivery_address'],
                'payment_method' => $orderData['payment_method'] ?? 'cash_on_delivery',
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $orderId = $this->db->insert('orders', $order);
            
            // Create order items
            foreach ($orderItems as $item) {
                $orderItem = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price']
                ];
                
                $this->db->insert('order_items', $orderItem);
                
                // Update product stock
                $product = new Product();
                if (!$product->decreaseStock($item['product_id'], $item['quantity'])) {
                    throw new \Exception("Insufficient stock for product: " . $item['product_name']);
                }
            }
            
            $this->db->commit();
            return $orderId;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get order by ID
     */
    public function getOrderById($id) {
        $sql = "SELECT o.*, u.name as user_name 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE o.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get order items
     */
    public function getOrderItems($orderId) {
        $sql = "SELECT oi.*, p.image_url 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :order_id 
                ORDER BY oi.id";
        
        return $this->db->fetchAll($sql, ['order_id' => $orderId]);
    }
    
    /**
     * Get orders by user
     */
    public function getOrdersByUser($userId, $limit = 10, $offset = 0) {
        $sql = "SELECT o.*, 
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                FROM orders o 
                WHERE o.user_id = :user_id 
                ORDER BY o.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }
    
    /**
     * Get all orders (admin)
     */
    public function getAllOrders($status = null, $limit = null, $offset = 0) {
        $sql = "SELECT o.*, u.name as customer_name,
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id";
        
        $params = [];
        
        if ($status) {
            $sql .= " WHERE o.status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Update order status
     */
    public function updateOrderStatus($id, $status) {
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \Exception("Invalid order status");
        }
        
        return $this->db->update('orders', 
            ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], 
            'id = :id', 
            ['id' => $id]
        );
    }
    
    /**
     * Cancel order
     */
    public function cancelOrder($id) {
        try {
            $this->db->beginTransaction();
            
            // Get order items to restore stock
            $orderItems = $this->getOrderItems($id);
            
            // Restore product stock
            $product = new Product();
            foreach ($orderItems as $item) {
                $currentProduct = $product->getProductById($item['product_id']);
                if ($currentProduct) {
                    $newStock = $currentProduct['stock_quantity'] + $item['quantity'];
                    $product->updateStock($item['product_id'], $newStock);
                }
            }
            
            // Update order status
            $this->updateOrderStatus($id, 'cancelled');
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Get order statistics
     */
    public function getOrderStats() {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue,
                    SUM(CASE WHEN created_at >= CURDATE() THEN 1 ELSE 0 END) as orders_today,
                    SUM(CASE WHEN created_at >= CURDATE() AND status = 'delivered' THEN total_amount ELSE 0 END) as revenue_today,
                    SUM(CASE WHEN created_at >= CURDATE() - INTERVAL 7 DAY THEN 1 ELSE 0 END) as orders_this_week,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                    SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders
                FROM orders";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Get recent orders
     */
    public function getRecentOrders($limit = 5) {
        $sql = "SELECT o.*, u.name as customer_name,
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get monthly revenue data
     */
    public function getMonthlyRevenue($year = null) {
        $year = $year ?: date('Y');
        
        $sql = "SELECT 
                    MONTH(created_at) as month,
                    SUM(total_amount) as revenue,
                    COUNT(*) as order_count
                FROM orders 
                WHERE YEAR(created_at) = :year AND status = 'delivered'
                GROUP BY MONTH(created_at)
                ORDER BY month";
        
        return $this->db->fetchAll($sql, ['year' => $year]);
    }
}
?>

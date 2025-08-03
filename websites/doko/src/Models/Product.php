<?php
namespace Models;

use Core\Database;

/**
 * Product Model
 * Handles all product-related database operations
 */
class Product {
    private $db;
    
    public function __construct() {
        $this->db = new \Core\Database();
    }
    
    /**
     * Get all products
     */
    public function getAllProducts($limit = null, $offset = 0) {
        $sql = "SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get products by category
     */
    public function getProductsByCategory($category, $limit = null) {
        $sql = "SELECT * FROM products WHERE category = :category AND status = 'active' ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql, ['category' => $category]);
    }
    
    /**
     * Get product by ID
     */
    public function getProductById($id) {
        $sql = "SELECT * FROM products WHERE id = :id AND status = 'active'";
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Search products
     */
    public function searchProducts($query, $limit = 20) {
        $sql = "SELECT * FROM products 
                WHERE (name LIKE :query OR description LIKE :query) 
                AND status = 'active' 
                ORDER BY name ASC 
                LIMIT {$limit}";
        
        $searchTerm = "%{$query}%";
        return $this->db->fetchAll($sql, ['query' => $searchTerm]);
    }
    
    /**
     * Get featured products
     */
    public function getFeaturedProducts($limit = 8) {
        $sql = "SELECT * FROM products 
                WHERE featured = 1 AND status = 'active' 
                ORDER BY created_at DESC 
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get daily best sell products
     */
    public function getDailyBestProducts($limit = 10) {
        $sql = "SELECT p.*, COALESCE(SUM(oi.quantity), 0) as total_sold
                FROM products p
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id
                WHERE p.status = 'active' 
                AND (o.created_at >= CURDATE() - INTERVAL 7 DAY OR o.created_at IS NULL)
                GROUP BY p.id
                ORDER BY total_sold DESC, p.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Create new product
     */
    public function createProduct($data) {
        $requiredFields = ['name', 'price', 'category', 'description'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Field {$field} is required");
            }
        }
        
        $productData = [
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'category' => $data['category'],
            'image_url' => $data['image_url'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'featured' => $data['featured'] ?? 0,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('products', $productData);
    }
    
    /**
     * Update product
     */
    public function updateProduct($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('products', $data, 'id = :id', ['id' => $id]);
    }
    
    /**
     * Delete product (soft delete)
     */
    public function deleteProduct($id) {
        return $this->db->update('products', 
            ['status' => 'deleted', 'updated_at' => date('Y-m-d H:i:s')], 
            'id = :id', 
            ['id' => $id]
        );
    }
    
    /**
     * Update stock quantity
     */
    public function updateStock($id, $quantity) {
        return $this->db->update('products', 
            ['stock_quantity' => $quantity, 'updated_at' => date('Y-m-d H:i:s')], 
            'id = :id', 
            ['id' => $id]
        );
    }
    
    /**
     * Decrease stock quantity
     */
    public function decreaseStock($id, $quantity) {
        $sql = "UPDATE products 
                SET stock_quantity = stock_quantity - :quantity, updated_at = NOW() 
                WHERE id = :id AND stock_quantity >= :quantity";
        
        $result = $this->db->query($sql, ['id' => $id, 'quantity' => $quantity]);
        return $result->rowCount() > 0;
    }
    
    /**
     * Get categories
     */
    public function getCategories() {
        $sql = "SELECT DISTINCT category FROM products WHERE status = 'active' ORDER BY category";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get product count by category
     */
    public function getProductCountByCategory() {
        $sql = "SELECT category, COUNT(*) as count 
                FROM products 
                WHERE status = 'active' 
                GROUP BY category 
                ORDER BY category";
        
        return $this->db->fetchAll($sql);
    }
}
?>

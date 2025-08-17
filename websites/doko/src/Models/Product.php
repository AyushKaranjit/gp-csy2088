<?php
/**
 * Product Model Class for DOKO Grocery E-commerce
 * Handles all product-related database operations
 */

// Correct relative path to config (was '../config/database.php' which was incorrect)
require_once __DIR__ . '/../../config/database.php';

class Product {
    private $conn;
    private $table_name = "products";

    public $product_id;
    public $name;
    public $description;
    public $price;
    public $original_price;
    public $category_id;
    public $stock_quantity;
    public $unit;
    public $weight;
    public $image_url;
    public $featured;
    public $is_active;
    public $nutritional_info;

    public function __construct() {
        // Use singleton accessor; direct instantiation would fail due to private constructor
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    // Get all products with pagination and filters
    public function getAllProducts($category_id = null, $search = null, $featured = null, $limit = 20, $offset = 0) {
    $query = "SELECT p.*, c.name as category_name, 
            (SELECT AVG(rating) FROM product_reviews WHERE product_id = p.product_id AND status = 'approved') as avg_rating,
            (SELECT COUNT(*) FROM product_reviews WHERE product_id = p.product_id AND status = 'approved') as review_count
         FROM " . $this->table_name . " p 
         LEFT JOIN categories c ON p.category_id = c.category_id 
         WHERE p.status = 'active'";

        $params = [];

        if ($category_id) {
            $query .= " AND p.category_id = :category_id";
            $params[':category_id'] = $category_id;
        }

        if ($search) {
            $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if ($featured !== null) {
            $query .= " AND p.featured = :featured";
            $params[':featured'] = $featured;
        }

        $query .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value);
        }
        
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Get product by ID
    public function getProductById($id) {
    $query = "SELECT p.*, c.name as category_name,
            (SELECT AVG(rating) FROM product_reviews WHERE product_id = p.product_id AND status = 'approved') as avg_rating,
            (SELECT COUNT(*) FROM product_reviews WHERE product_id = p.product_id AND status = 'approved') as review_count
         FROM " . $this->table_name . " p 
         LEFT JOIN categories c ON p.category_id = c.category_id 
         WHERE p.product_id = :product_id AND p.status = 'active' 
         LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }

    // Get featured products
    public function getFeaturedProducts($limit = 8) {
        return $this->getAllProducts(null, null, 1, $limit, 0);
    }

    // Get products by category
    public function getProductsByCategory($category_id, $limit = 20, $offset = 0) {
        return $this->getAllProducts($category_id, null, null, $limit, $offset);
    }

    // Search products
    public function searchProducts($search_term, $limit = 20, $offset = 0) {
        return $this->getAllProducts(null, $search_term, null, $limit, $offset);
    }

    // Add new product (admin only)
    public function addProduct() {
        // Use schema column names: stock_quantity and short_description exists in schema
        $query = "INSERT INTO " . $this->table_name . " 
                 SET name=:name, short_description=:short_description, description=:description, price=:price, original_price=:original_price,
                     category_id=:category_id, stock_quantity=:stock_quantity, unit=:unit, weight=:weight,
                     image_url=:image_url, featured=:featured, nutritional_info=:nutritional_info, status='active'";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":original_price", $this->original_price);
    $stmt->bindParam(":category_id", $this->category_id);
    // Support both legacy $this->stock and new $this->stock_quantity
    $stockVal = $this->stock_quantity ?? $this->stock ?? 0;
    $stmt->bindParam(":stock_quantity", $stockVal);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":weight", $this->weight);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":featured", $this->featured);
        $stmt->bindParam(":nutritional_info", $this->nutritional_info);

        if ($stmt->execute()) {
            $this->product_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Update product (admin only)
    public function updateProduct() {
        $query = "UPDATE " . $this->table_name . " 
                 SET name=:name, short_description=:short_description, description=:description, price=:price, original_price=:original_price,
                     category_id=:category_id, stock_quantity=:stock_quantity, unit=:unit, weight=:weight,
                     image_url=:image_url, featured=:featured, nutritional_info=:nutritional_info
                 WHERE product_id=:product_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":original_price", $this->original_price);
    $stmt->bindParam(":category_id", $this->category_id);
    $stockVal2 = $this->stock_quantity ?? $this->stock ?? 0;
    $stmt->bindParam(":stock_quantity", $stockVal2);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":weight", $this->weight);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":featured", $this->featured);
        $stmt->bindParam(":nutritional_info", $this->nutritional_info);
        $stmt->bindParam(":product_id", $this->product_id);

        return $stmt->execute();
    }

    // Delete product (admin only)
    public function deleteProduct($product_id) {
    // Mark as inactive using status enum
    $query = "UPDATE " . $this->table_name . " SET status = 'inactive' WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        
        return $stmt->execute();
    }

    // Update stock
    public function updateStock($product_id, $quantity) {
    $query = "UPDATE " . $this->table_name . " 
         SET stock_quantity = stock_quantity - :quantity 
         WHERE product_id = :product_id AND stock_quantity >= :quantity";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->bindParam(":quantity", $quantity);
        
        return $stmt->execute();
    }

    // Get low stock products (admin only)
    public function getLowStockProducts($threshold = 10) {
    $query = "SELECT * FROM " . $this->table_name . " 
         WHERE stock_quantity <= :threshold AND status = 'active' 
         ORDER BY stock_quantity ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":threshold", $threshold);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Get product count for pagination
    public function getProductCount($category_id = null, $search = null) {
    $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'active'";
        $params = [];

        if ($category_id) {
            $query .= " AND category_id = :category_id";
            $params[':category_id'] = $category_id;
        }

        if ($search) {
            $query .= " AND (name LIKE :search OR description LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value);
        }
        
        $stmt->execute();
        $row = $stmt->fetch();
        
        return $row['total'];
    }
}
?>

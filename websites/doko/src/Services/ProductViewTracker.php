<?php
/**
 * Product view tracking service
 * Tracks product views for analytics and recommendations
 */

class ProductViewTracker {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../../config/database.php';
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Track a product view
     */
    public function trackView($productId, $userId = null) {
        try {
            // Increment view counts
            $stmt = $this->db->prepare("
                UPDATE products SET 
                    view_count = view_count + 1,
                    daily_views = daily_views + 1,
                    weekly_views = weekly_views + 1,
                    monthly_views = monthly_views + 1
                WHERE product_id = ?
            ");
            
            return $stmt->execute([$productId]);
        } catch (Exception $e) {
            error_log("Product view tracking error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get trending products based on recent views
     */
    public function getTrendingProducts($limit = 10, $period = 'daily') {
        try {
            $viewColumn = match($period) {
                'weekly' => 'weekly_views',
                'monthly' => 'monthly_views',
                default => 'daily_views'
            };
            
            $stmt = $this->db->prepare("
                SELECT p.product_id, p.name, p.price, p.{$viewColumn} as views,
                       pi.image_url, c.name as category_name
                FROM products p
                LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.status = 'active' AND p.{$viewColumn} > 0
                ORDER BY p.{$viewColumn} DESC
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Trending products error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reset daily/weekly/monthly view counters (should be run via cron)
     */
    public function resetViewCounters($period = 'daily') {
        try {
            $column = match($period) {
                'weekly' => 'weekly_views',
                'monthly' => 'monthly_views',
                default => 'daily_views'
            };
            
            $stmt = $this->db->prepare("UPDATE products SET {$column} = 0");
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Reset view counters error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get product analytics
     */
    public function getProductAnalytics($productId) {
        try {
            $stmt = $this->db->prepare("
                SELECT view_count, daily_views, weekly_views, monthly_views,
                       total_sales, avg_rating, total_reviews
                FROM products 
                WHERE product_id = ?
            ");
            
            $stmt->execute([$productId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Product analytics error: " . $e->getMessage());
            return null;
        }
    }
}
?>

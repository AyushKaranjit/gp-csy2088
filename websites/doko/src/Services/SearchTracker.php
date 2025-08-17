<?php
/**
 * Search tracking service
 * Tracks search queries and results for analytics
 */

class SearchTracker {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../../config/database.php';
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Track a search query
     */
    public function trackSearch($query, $resultCount = 0) {
        try {
            $query = trim($query);
            if (empty($query)) return false;
            
            // Insert or update search stats
            $stmt = $this->db->prepare("
                INSERT INTO search_stats (query, results_count, search_date, search_count) 
                VALUES (?, ?, CURRENT_DATE, 1)
                ON DUPLICATE KEY UPDATE 
                search_count = search_count + 1,
                results_count = ?
            ");
            
            return $stmt->execute([$query, $resultCount, $resultCount]);
        } catch (Exception $e) {
            error_log("Search tracking error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get popular search terms
     */
    public function getPopularSearches($limit = 10, $days = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT query, SUM(search_count) as total_searches, 
                       AVG(results_count) as avg_results
                FROM search_stats 
                WHERE search_date >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
                GROUP BY query 
                ORDER BY total_searches DESC 
                LIMIT ?
            ");
            
            $stmt->execute([$days, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Popular searches error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get search suggestions based on popular queries
     */
    public function getSearchSuggestions($partial, $limit = 5) {
        try {
            $searchTerm = $partial . '%';
            $stmt = $this->db->prepare("
                SELECT query, SUM(search_count) as popularity
                FROM search_stats 
                WHERE query LIKE ? 
                AND search_date >= DATE_SUB(CURRENT_DATE, INTERVAL 90 DAY)
                GROUP BY query 
                ORDER BY popularity DESC 
                LIMIT ?
            ");
            
            $stmt->execute([$searchTerm, $limit]);
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'query');
        } catch (Exception $e) {
            error_log("Search suggestions error: " . $e->getMessage());
            return [];
        }
    }
}
?>

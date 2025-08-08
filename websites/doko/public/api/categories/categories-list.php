<?php
/** Categories List API (refactored) */
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method('GET');

try {
    // Get database connection
    $db = Database::getInstance();
    
    // Get all active categories with product count
    $query = "SELECT c.category_id, c.name, c.description, c.image_url, c.is_active,
                     c.created_at, COUNT(p.product_id) as product_count
              FROM categories c
              LEFT JOIN products p ON c.category_id = p.category_id AND p.status = 'active'
              WHERE c.is_active = 1
              GROUP BY c.category_id, c.name, c.description, c.image_url, c.is_active, c.created_at
              ORDER BY c.name ASC";
    
    $stmt = $db->execute($query, []);
    $categories = $stmt->fetchAll();
    
    $formatted_categories = [];
    foreach ($categories as $category) {
        $formatted_categories[] = [
            'category_id' => (int)$category['category_id'],
            'name' => $category['name'],
            'description' => $category['description'],
            'image_url' => $category['image_url'] ?: 'uploads/placeholder-category.jpg',
            'product_count' => (int)$category['product_count'],
            'created_at' => $category['created_at']
        ];
    }
    
    ApiResponse::success([
        'data' => $formatted_categories,
        'total' => count($formatted_categories)
    ]);
    
} catch (Exception $e) {
    error_log("Categories list API error: " . $e->getMessage());
    ApiResponse::error('An error occurred while fetching categories', 500);
}
?>

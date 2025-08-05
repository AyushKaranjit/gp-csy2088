<?php
/**
 * Categories List API
 * Get all categories
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../config/database.php';

try {
    // Get database connection
    $db = Database::getInstance();
    
    // Get all active categories with product count
    $query = "SELECT c.category_id, c.name, c.description, c.image, c.status,
                     c.created_at, COUNT(p.product_id) as product_count
              FROM categories c
              LEFT JOIN products p ON c.category_id = p.category_id AND p.status = 'active'
              WHERE c.status = 'active'
              GROUP BY c.category_id, c.name, c.description, c.image, c.status, c.created_at
              ORDER BY c.name ASC";
    
    $stmt = $db->execute($query, []);
    $categories = $stmt->fetchAll();
    
    $formatted_categories = [];
    foreach ($categories as $category) {
        $formatted_categories[] = [
            'category_id' => (int)$category['category_id'],
            'name' => $category['name'],
            'description' => $category['description'],
            'image' => $category['image'],
            'product_count' => (int)$category['product_count'],
            'created_at' => $category['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formatted_categories, // Changed from 'categories' to 'data'
        'total' => count($formatted_categories)
    ]);
    
} catch (Exception $e) {
    error_log("Categories list API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching categories'
    ]);
}
?>

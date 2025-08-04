<?php
/**
 * Categories API - List all categories
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get categories with product count
    $query = "SELECT c.*, COUNT(p.product_id) as product_count 
              FROM categories c 
              LEFT JOIN products p ON c.category_id = p.category_id AND p.is_active = 1
              WHERE c.is_active = 1 
              GROUP BY c.category_id 
              ORDER BY c.name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
    // Format category data
    $formattedCategories = array_map(function($c) {
        return [
            'category_id' => (int)$c['category_id'],
            'name' => $c['name'],
            'description' => $c['description'],
            'image_url' => $c['image_url'],
            'product_count' => (int)$c['product_count'],
            'is_active' => (bool)$c['is_active'],
            'created_at' => $c['created_at']
        ];
    }, $categories);
    
    $response = [
        'success' => true,
        'data' => $formattedCategories,
        'count' => count($formattedCategories)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching categories: ' . $e->getMessage()
    ]);
}
?>

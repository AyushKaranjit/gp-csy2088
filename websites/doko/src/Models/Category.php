<?php
namespace Models;

/**
 * Category Model
 * Handles all category-related database operations
 */
class Category {
    private $db;
    
    public function __construct() {
        $this->db = new \Core\Database();
    }
    
    /**
     * Get all categories
     */
    public function getAllCategories() {
        $sql = "SELECT c.*, 
                       COUNT(p.id) as product_count
                FROM categories c 
                LEFT JOIN products p ON c.name = p.category AND p.status = 'active'
                WHERE c.status = 'active'
                GROUP BY c.id
                ORDER BY c.sort_order ASC, c.name ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get category by name
     */
    public function getCategoryByName($name) {
        $sql = "SELECT * FROM categories WHERE name = :name AND status = 'active'";
        return $this->db->fetch($sql, ['name' => $name]);
    }
    
    /**
     * Get category by ID
     */
    public function getCategoryById($id) {
        $sql = "SELECT * FROM categories WHERE id = :id AND status = 'active'";
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Create new category
     */
    public function createCategory($data) {
        $requiredFields = ['name', 'display_name'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Field {$field} is required");
            }
        }
        
        // Check if category already exists
        if ($this->getCategoryByName($data['name'])) {
            throw new \Exception("Category already exists");
        }
        
        $categoryData = [
            'name' => strtolower($data['name']),
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? '#10b981',
            'sort_order' => $data['sort_order'] ?? 0,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('categories', $categoryData);
    }
    
    /**
     * Update category
     */
    public function updateCategory($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('categories', $data, 'id = :id', ['id' => $id]);
    }
    
    /**
     * Delete category (soft delete)
     */
    public function deleteCategory($id) {
        return $this->db->update('categories', 
            ['status' => 'deleted', 'updated_at' => date('Y-m-d H:i:s')], 
            'id = :id', 
            ['id' => $id]
        );
    }
    
    /**
     * Get featured categories
     */
    public function getFeaturedCategories($limit = 6) {
        $sql = "SELECT c.*, 
                       COUNT(p.id) as product_count
                FROM categories c 
                LEFT JOIN products p ON c.name = p.category AND p.status = 'active'
                WHERE c.status = 'active' AND c.featured = 1
                GROUP BY c.id
                ORDER BY c.sort_order ASC, c.name ASC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get categories with products
     */
    public function getCategoriesWithProducts() {
        $sql = "SELECT c.*, 
                       COUNT(p.id) as product_count
                FROM categories c 
                INNER JOIN products p ON c.name = p.category AND p.status = 'active'
                WHERE c.status = 'active'
                GROUP BY c.id
                HAVING product_count > 0
                ORDER BY c.sort_order ASC, c.name ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Initialize default categories
     */
    public function initializeDefaultCategories() {
        $defaultCategories = [
            [
                'name' => 'fruits',
                'display_name' => 'Fresh Fruits',
                'description' => 'Fresh and organic fruits',
                'icon' => 'fas fa-apple-alt',
                'color' => '#ff6b6b',
                'sort_order' => 1
            ],
            [
                'name' => 'vegetables',
                'display_name' => 'Vegetables',
                'description' => 'Fresh vegetables and greens',
                'icon' => 'fas fa-carrot',
                'color' => '#4ecdc4',
                'sort_order' => 2
            ],
            [
                'name' => 'meat',
                'display_name' => 'Meat & Poultry',
                'description' => 'Fresh meat and poultry products',
                'icon' => 'fas fa-drumstick-bite',
                'color' => '#ff9ff3',
                'sort_order' => 3
            ],
            [
                'name' => 'dairy',
                'display_name' => 'Dairy Products',
                'description' => 'Milk, cheese, and dairy products',
                'icon' => 'fas fa-cheese',
                'color' => '#54a0ff',
                'sort_order' => 4
            ],
            [
                'name' => 'snacks',
                'display_name' => 'Snacks',
                'description' => 'Healthy snacks and treats',
                'icon' => 'fas fa-cookie-bite',
                'color' => '#feca57',
                'sort_order' => 5
            ],
            [
                'name' => 'drinks',
                'display_name' => 'Beverages',
                'description' => 'Fresh juices and beverages',
                'icon' => 'fas fa-glass-whiskey',
                'color' => '#48dbfb',
                'sort_order' => 6
            ]
        ];
        
        foreach ($defaultCategories as $category) {
            if (!$this->getCategoryByName($category['name'])) {
                $this->createCategory($category);
            }
        }
    }
}
?>

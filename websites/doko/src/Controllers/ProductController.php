<?php
namespace Controllers;

/**
 * Product Controller
 * Handles all product-related HTTP requests
 */
class ProductController {
    private $productModel;
    private $template;
    
    public function __construct() {
        $this->productModel = new \Models\Product();
        $this->template = new \Core\Template();
    }
    
    /**
     * Get all products (API)
     */
    public function index($params = []) {
        try {
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 20;
            $category = $_GET['category'] ?? null;
            $search = $_GET['search'] ?? null;
            
            $offset = ($page - 1) * $limit;
            
            if ($search) {
                $products = $this->productModel->searchProducts($search, $limit);
            } elseif ($category) {
                $products = $this->productModel->getProductsByCategory($category, $limit);
            } else {
                $products = $this->productModel->getAllProducts($limit, $offset);
            }
            
            \Core\Router::json([
                'success' => true,
                'data' => $products,
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
     * Get featured products (API)
     */
    public function featured($params = []) {
        try {
            $limit = $_GET['limit'] ?? 8;
            $products = $this->productModel->getFeaturedProducts($limit);
            
            \Core\Router::json([
                'success' => true,
                'data' => $products
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get daily best products (API)
     */
    public function dailyBest($params = []) {
        try {
            $limit = $_GET['limit'] ?? 10;
            $products = $this->productModel->getDailyBestProducts($limit);
            
            \Core\Router::json([
                'success' => true,
                'data' => $products
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get products by category (API)
     */
    public function byCategory($params = []) {
        try {
            $category = $params['category'] ?? '';
            $limit = $_GET['limit'] ?? 20;
            
            if (empty($category)) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Category is required'
                ], 400);
                return;
            }
            
            $products = $this->productModel->getProductsByCategory($category, $limit);
            
            \Core\Router::json([
                'success' => true,
                'data' => $products,
                'category' => $category
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get single product (API)
     */
    public function show($params = []) {
        try {
            $id = $params['id'] ?? null;
            
            if (!$id) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Product ID is required'
                ], 400);
                return;
            }
            
            $product = $this->productModel->getProductById($id);
            
            if (!$product) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Product not found'
                ], 404);
                return;
            }
            
            \Core\Router::json([
                'success' => true,
                'data' => $product
            ]);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create new product (API)
     */
    public function store($params = []) {
        try {
            $input = \Core\Router::getInput();
            
            $productId = $this->productModel->createProduct($input);
            $product = $this->productModel->getProductById($productId);
            
            \Core\Router::json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
            
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Update product (API)
     */
    public function update($params = []) {
        try {
            $id = $params['id'] ?? null;
            
            if (!$id) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Product ID is required'
                ], 400);
                return;
            }
            
            $input = \Core\Router::getInput();
            
            $this->productModel->updateProduct($id, $input);
            $product = $this->productModel->getProductById($id);
            
            \Core\Router::json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ]);
        
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Delete product (API)
     */
    public function delete($params = []) {
        try {
            $id = $params['id'] ?? null;
            
            if (!$id) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Product ID is required'
                ], 400);
                return;
            }
            
            $this->productModel->deleteProduct($id);
            
            \Core\Router::json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Search products (API)
     */
    public function search($params = []) {
        try {
            $query = $_GET['q'] ?? '';
            $limit = $_GET['limit'] ?? 20;
            
            if (empty($query)) {
                \Core\Router::json([
                    'success' => false,
                    'error' => 'Search query is required'
                ], 400);
                return;
            }
            
            $products = $this->productModel->searchProducts($query, $limit);
            
            \Core\Router::json([
                'success' => true,
                'data' => $products,
                'query' => $query
            ]);
        
        } catch (\Exception $e) {
            \Core\Router::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get categories (API)
     */
    public function categories($params = []) {
        try {
            $categories = $this->productModel->getCategories();
            $categoryCounts = $this->productModel->getProductCountByCategory();
            
            \Core\Router::json([
                'success' => true,
                'data' => [
                    'categories' => $categories,
                    'counts' => $categoryCounts
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

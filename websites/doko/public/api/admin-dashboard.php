<?php
/**
 * Admin Dashboard API Endpoint
 * Get dashboard statistics and recent activity
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set proper CORS and JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session for authentication check
session_start();

// Include required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/AuthController.php';

try {
    // Check admin authentication
    $auth = new AuthController();
    if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        exit;
    }

    $db = Database::getInstance()->getConnection();
    
    // Get dashboard statistics
    $stats = getDashboardStats($db);
    $recentProducts = getRecentProducts($db);
    $topProducts = getTopProducts($db);
    $lowStockProducts = getLowStockProducts($db);

    echo json_encode([
        'success' => true,
        'data' => [
            'stats' => $stats,
            'recent_products' => $recentProducts,
            'top_products' => $topProducts,
            'low_stock_products' => $lowStockProducts
        ]
    ]);

} catch (Exception $e) {
    error_log("Admin Dashboard API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

function getDashboardStats($db) {
    try {
        $stats = [];
        
        // Total products
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
        $stmt->execute();
        $stats['total_products'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Active products
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM products WHERE is_active = 1 AND stock > 0");
        $stmt->execute();
        $stats['active_products'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Out of stock products
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM products WHERE stock <= 0");
        $stmt->execute();
        $stats['out_of_stock'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total categories
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM categories WHERE is_active = 1");
        $stmt->execute();
        $stats['total_categories'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total users
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
        $stmt->execute();
        $stats['total_users'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Admin users
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'admin' AND status = 'active'");
        $stmt->execute();
        $stats['admin_users'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Low stock alert (products with stock <= 10)
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM products WHERE stock <= 10 AND stock > 0");
        $stmt->execute();
        $stats['low_stock_alert'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Featured products
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM products WHERE featured = 1 AND is_active = 1");
        $stmt->execute();
        $stats['featured_products'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Get dashboard stats error: " . $e->getMessage());
        return [];
    }
}

function getRecentProducts($db, $limit = 5) {
    try {
        $sql = "SELECT 
                    p.product_id, p.name, p.price, p.stock, p.is_active,
                    p.created_at, c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                ORDER BY p.created_at DESC
                LIMIT ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$limit]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($products as &$product) {
            $product['price'] = (float)$product['price'];
            $product['stock'] = (int)$product['stock'];
            $product['is_active'] = (bool)$product['is_active'];
        }
        
        return $products;
        
    } catch (Exception $e) {
        error_log("Get recent products error: " . $e->getMessage());
        return [];
    }
}

function getTopProducts($db, $limit = 5) {
    try {
        // Since we don't have order data yet, we'll rank by featured products with good stock
        $sql = "SELECT 
                    p.product_id, p.name, p.price, p.stock, p.featured,
                    c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.is_active = 1
                ORDER BY p.featured DESC, p.stock DESC
                LIMIT ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$limit]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($products as &$product) {
            $product['price'] = (float)$product['price'];
            $product['stock'] = (int)$product['stock'];
            $product['featured'] = (bool)$product['featured'];
            // Mock sales data until we have actual orders
            $product['mock_sales'] = rand(50, 200);
        }
        
        return $products;
        
    } catch (Exception $e) {
        error_log("Get top products error: " . $e->getMessage());
        return [];
    }
}

function getLowStockProducts($db, $limit = 5) {
    try {
        $sql = "SELECT 
                    p.product_id, p.name, p.price, p.stock, p.unit,
                    c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.stock <= 10 AND p.stock > 0 AND p.is_active = 1
                ORDER BY p.stock ASC
                LIMIT ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$limit]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($products as &$product) {
            $product['price'] = (float)$product['price'];
            $product['stock'] = (int)$product['stock'];
        }
        
        return $products;
        
    } catch (Exception $e) {
        error_log("Get low stock products error: " . $e->getMessage());
        return [];
    }
}
?>

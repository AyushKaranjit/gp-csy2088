<?php
/**
 * API Entry Point
 * Main file to handle all API requests
 */

// Set content type to JSON
header('Content-Type: application/json');

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session
session_start();

// Include autoloader
require_once __DIR__ . '/../autoload.php';

try {
    // Get request path and method
    $requestUri = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Remove query string
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // Remove base path
    $basePath = '/websites/doko/public';
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
    
    // Parse path segments
    $segments = array_filter(explode('/', $path));
    $segments = array_values($segments); // Re-index array
    
    // Route API requests
    if (isset($segments[0]) && $segments[0] === 'api') {
        handleApiRequest($segments, $method);
    } else {
        // Serve static files or pages
        handlePageRequest($path);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}

/**
 * Handle API requests
 */
function handleApiRequest($segments, $method) {
    if (!isset($segments[1])) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'API endpoint not found']);
        return;
    }
    
    $endpoint = $segments[1];
    $id = $segments[2] ?? null;
    $action = $segments[3] ?? null;
    
    switch ($endpoint) {
        case 'products':
            handleProductsApi($method, $id, $action);
            break;
            
        case 'auth':
            handleAuthApi($method, $id);
            break;
            
        case 'orders':
            handleOrdersApi($method, $id, $action);
            break;
            
        case 'categories':
            handleCategoriesApi($method, $id);
            break;
            
        case 'users':
            handleUsersApi($method, $id);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'API endpoint not found']);
    }
}

/**
 * Handle products API
 */
function handleProductsApi($method, $id, $action) {
    $controller = new Controllers\ProductController();
    
    switch ($method) {
        case 'GET':
            if ($id) {
                if ($action === 'related') {
                    // Get related products
                    echo json_encode(['success' => true, 'data' => []]);
                } else {
                    $controller->show(['id' => $id]);
                }
            } else {
                // Check for special endpoints
                if (isset($_GET['featured'])) {
                    $controller->featured();
                } elseif (isset($_GET['daily-best'])) {
                    $controller->dailyBest();
                } elseif (isset($_GET['search'])) {
                    $controller->search();
                } else {
                    $controller->index();
                }
            }
            break;
            
        case 'POST':
            $controller->store();
            break;
            
        case 'PUT':
            if ($id) {
                $controller->update(['id' => $id]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Product ID required']);
            }
            break;
            
        case 'DELETE':
            if ($id) {
                $controller->delete(['id' => $id]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Product ID required']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
}

/**
 * Handle auth API
 */
function handleAuthApi($method, $action) {
    $controller = new Controllers\AuthController();
    
    switch ($action) {
        case 'login':
            if ($method === 'POST') {
                $controller->login();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case 'register':
            if ($method === 'POST') {
                $controller->register();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case 'logout':
            if ($method === 'POST') {
                $controller->logout();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case 'me':
            if ($method === 'GET') {
                $controller->me();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case 'profile':
            if ($method === 'PUT') {
                $controller->updateProfile();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Auth endpoint not found']);
    }
}

/**
 * Handle orders API
 */
function handleOrdersApi($method, $id, $action) {
    $controller = new Controllers\OrderController();
    
    switch ($method) {
        case 'GET':
            if ($id) {
                $controller->show(['id' => $id]);
            } else {
                if (isset($_GET['my'])) {
                    $controller->myOrders();
                } else {
                    $controller->index();
                }
            }
            break;
            
        case 'POST':
            $controller->create();
            break;
            
        case 'PUT':
            if ($id && $action === 'cancel') {
                $controller->cancel(['id' => $id]);
            } elseif ($id && $action === 'status') {
                $controller->updateStatus(['id' => $id]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid order action']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
}

/**
 * Handle categories API
 */
function handleCategoriesApi($method, $id) {
    if ($method === 'GET') {
        $controller = new Controllers\ProductController();
        $controller->categories();
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
}

/**
 * Handle users API
 */
function handleUsersApi($method, $id) {
    // This would require admin authentication
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access forbidden']);
}

/**
 * Handle page requests (serve HTML files)
 */
function handlePageRequest($path) {
    $controller = new Controllers\PageController();
    
    // Map paths to controller methods
    switch ($path) {
        case '/':
        case '/home':
        case '/index.html':
            $controller->home();
            break;
            
        case '/shop':
        case '/category':
        case '/category.html':
            $controller->category();
            break;
            
        case '/product':
        case '/product-detail':
        case '/product-detail.html':
            $controller->product();
            break;
            
        case '/cart':
        case '/cart.html':
            $controller->cart();
            break;
            
        case '/checkout':
        case '/payment':
        case '/payment.html':
            $controller->checkout();
            break;
            
        case '/login':
        case '/login.html':
            $controller->login();
            break;
            
        case '/signup':
        case '/signup.html':
            $controller->signup();
            break;
            
        case '/admin':
        case '/admin.html':
            $controller->admin();
            break;
            
        case '/about':
        case '/about.html':
            $controller->about();
            break;
            
        case '/wishlist':
        case '/wishlist.html':
            $controller->wishlist();
            break;
            
        default:
            // Try to serve static file
            $filePath = __DIR__ . $path;
            if (file_exists($filePath) && is_file($filePath)) {
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $mimeTypes = [
                    'css' => 'text/css',
                    'js' => 'application/javascript',
                    'html' => 'text/html',
                    'json' => 'application/json',
                    'png' => 'image/png',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'svg' => 'image/svg+xml'
                ];
                
                if (isset($mimeTypes[$extension])) {
                    header('Content-Type: ' . $mimeTypes[$extension]);
                }
                
                readfile($filePath);
            } else {
                http_response_code(404);
                echo "Page not found";
            }
    }
}
?>

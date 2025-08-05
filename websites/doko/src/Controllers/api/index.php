<?php
/**
 * DOKO API Documentation and Status
 * Main API endpoint that provides information about available APIs
 */

require_once 'ApiConfig.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method !== 'GET') {
        ApiResponse::error('Method not allowed', 405);
    }
    
    $requestPath = $_GET['endpoint'] ?? '';
    
    switch ($requestPath) {
        case 'status':
            handleApiStatus();
            break;
        case 'endpoints':
            handleEndpointsList();
            break;
        case '':
        default:
            handleApiInfo();
            break;
    }
    
} catch (Exception $e) {
    ApiLogger::error("API index error: " . $e->getMessage());
    ApiResponse::error('An error occurred: ' . $e->getMessage());
}

function handleApiInfo() {
    $apiInfo = [
        'name' => 'DOKO Grocery E-commerce API',
        'version' => ApiConfig::VERSION,
        'description' => 'RESTful API for DOKO online grocery store',
        'documentation' => [
            'base_url' => getBaseUrl(),
            'authentication' => 'Session-based authentication',
            'response_format' => 'JSON',
            'rate_limiting' => [
                'requests_per_hour' => ApiConfig::RATE_LIMIT_REQUESTS,
                'window_seconds' => ApiConfig::RATE_LIMIT_WINDOW
            ]
        ],
        'endpoints' => [
            'status' => getBaseUrl() . '/api/?endpoint=status',
            'endpoints_list' => getBaseUrl() . '/api/?endpoint=endpoints'
        ],
        'contact' => [
            'email' => 'api@doko.com',
            'website' => 'https://doko.com'
        ]
    ];
    
    ApiResponse::success($apiInfo, 'API information retrieved successfully');
}

function handleApiStatus() {
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    // Check database connection
    $dbStatus = 'connected';
    try {
        $conn->query('SELECT 1');
    } catch (Exception $e) {
        $dbStatus = 'error: ' . $e->getMessage();
    }
    
    // Check key tables
    $tables = ['users', 'products', 'orders', 'categories'];
    $tableStatus = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) FROM {$table}");
            $count = $stmt->fetchColumn();
            $tableStatus[$table] = [
                'status' => 'ok',
                'record_count' => (int)$count
            ];
        } catch (Exception $e) {
            $tableStatus[$table] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    // Check file upload directory
    $uploadDir = __DIR__ . '/../../public/uploads/';
    $uploadStatus = is_dir($uploadDir) && is_writable($uploadDir) ? 'writable' : 'not_writable';
    
    // System info
    $systemInfo = [
        'php_version' => PHP_VERSION,
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size')
    ];
    
    $status = [
        'api_version' => ApiConfig::VERSION,
        'timestamp' => date('c'),
        'uptime' => getUptime(),
        'database' => [
            'status' => $dbStatus,
            'tables' => $tableStatus
        ],
        'filesystem' => [
            'uploads_directory' => $uploadStatus
        ],
        'system' => $systemInfo,
        'health_check' => $dbStatus === 'connected' && $uploadStatus === 'writable' ? 'healthy' : 'issues_detected'
    ];
    
    ApiResponse::success($status, 'API status retrieved successfully');
}

function handleEndpointsList() {
    $endpoints = [
        'authentication' => [
            'login' => [
                'method' => 'POST',
                'url' => '/api/auth/login.php',
                'description' => 'User and admin login',
                'requires_auth' => false
            ],
            'logout' => [
                'method' => 'POST',
                'url' => '/api/auth/logout.php',
                'description' => 'User logout',
                'requires_auth' => true
            ],
            'register' => [
                'method' => 'POST',
                'url' => '/api/auth/register.php',
                'description' => 'User registration',
                'requires_auth' => false
            ],
            'profile' => [
                'method' => 'GET',
                'url' => '/api/auth/profile.php',
                'description' => 'Get user profile',
                'requires_auth' => true
            ]
        ],
        'products' => [
            'list' => [
                'method' => 'GET',
                'url' => '/api/products/list.php',
                'description' => 'Get products with filtering and pagination',
                'requires_auth' => false
            ],
            'detail' => [
                'method' => 'GET',
                'url' => '/api/products/detail.php',
                'description' => 'Get single product details',
                'requires_auth' => false
            ],
            'featured' => [
                'method' => 'GET',
                'url' => '/api/products/featured.php',
                'description' => 'Get featured products',
                'requires_auth' => false
            ],
            'search' => [
                'method' => 'GET',
                'url' => '/api/products/search.php',
                'description' => 'Search products',
                'requires_auth' => false
            ]
        ],
        'categories' => [
            'list' => [
                'method' => 'GET',
                'url' => '/api/categories/list.php',
                'description' => 'Get all categories',
                'requires_auth' => false
            ]
        ],
        'cart' => [
            'get' => [
                'method' => 'GET',
                'url' => '/api/cart/get.php',
                'description' => 'Get cart contents',
                'requires_auth' => false
            ],
            'add' => [
                'method' => 'POST',
                'url' => '/api/cart/add.php',
                'description' => 'Add item to cart',
                'requires_auth' => false
            ],
            'update' => [
                'method' => 'PUT',
                'url' => '/api/cart/update.php',
                'description' => 'Update cart item quantity',
                'requires_auth' => false
            ],
            'remove' => [
                'method' => 'DELETE',
                'url' => '/api/cart/remove.php',
                'description' => 'Remove item from cart',
                'requires_auth' => false
            ],
            'clear' => [
                'method' => 'DELETE',
                'url' => '/api/cart/clear.php',
                'description' => 'Clear entire cart',
                'requires_auth' => false
            ]
        ],
        'orders' => [
            'create' => [
                'method' => 'POST',
                'url' => '/api/orders/create.php',
                'description' => 'Create new order',
                'requires_auth' => true
            ]
        ],
        'admin' => [
            'dashboard' => [
                'method' => 'GET',
                'url' => '/api/admin/dashboard_v2.php',
                'description' => 'Get admin dashboard data',
                'requires_auth' => true,
                'requires_admin' => true
            ],
            'products_management' => [
                'method' => 'GET, POST, PUT, DELETE',
                'url' => '/api/admin/products.php',
                'description' => 'Manage products (admin only)',
                'requires_auth' => true,
                'requires_admin' => true
            ],
            'orders_management' => [
                'method' => 'GET, PUT',
                'url' => '/api/admin/orders.php',
                'description' => 'Manage orders (admin only)',
                'requires_auth' => true,
                'requires_admin' => true
            ],
            'users_management' => [
                'method' => 'GET, PUT, DELETE',
                'url' => '/api/admin/users.php',
                'description' => 'Manage users (admin only)',
                'requires_auth' => true,
                'requires_admin' => true
            ],
            'image_upload' => [
                'method' => 'POST',
                'url' => '/api/images/upload.php',
                'description' => 'Upload images (admin only)',
                'requires_auth' => true,
                'requires_admin' => true
            ],
            'image_management' => [
                'method' => 'GET, PUT, DELETE',
                'url' => '/api/images/manage.php',
                'description' => 'Manage uploaded images (admin only)',
                'requires_auth' => true,
                'requires_admin' => true
            ]
        ],
        'system' => [
            'settings' => [
                'method' => 'GET, PUT',
                'url' => '/api/settings/',
                'description' => 'Get/update system settings',
                'requires_auth' => false, // GET public settings only
                'requires_admin' => true  // For updates
            ],
            'activity_logs' => [
                'method' => 'GET, DELETE',
                'url' => '/api/logs/',
                'description' => 'View/manage activity logs (admin only)',
                'requires_auth' => true,
                'requires_admin' => true
            ]
        ]
    ];
    
    ApiResponse::success($endpoints, 'API endpoints list retrieved successfully');
}

function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['REQUEST_URI'] ?? '');
    
    return $protocol . $host . $path;
}

function getUptime() {
    // Simple uptime calculation (you might want to implement a more sophisticated method)
    $uptimeFile = sys_get_temp_dir() . '/doko_api_uptime';
    
    if (!file_exists($uptimeFile)) {
        file_put_contents($uptimeFile, time());
        return '0 seconds';
    }
    
    $startTime = (int)file_get_contents($uptimeFile);
    $uptime = time() - $startTime;
    
    $days = floor($uptime / 86400);
    $hours = floor(($uptime % 86400) / 3600);
    $minutes = floor(($uptime % 3600) / 60);
    $seconds = $uptime % 60;
    
    $parts = [];
    if ($days > 0) $parts[] = "{$days} days";
    if ($hours > 0) $parts[] = "{$hours} hours";
    if ($minutes > 0) $parts[] = "{$minutes} minutes";
    if ($seconds > 0 || empty($parts)) $parts[] = "{$seconds} seconds";
    
    return implode(', ', $parts);
}
?>

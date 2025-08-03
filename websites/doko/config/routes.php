<?php
/**
 * Routes Configuration
 * Define all API routes here
 */

// Include autoloader
require_once __DIR__ . '/../autoload.php';

// Initialize router
// use the correct Router class from the Core namespace
$router = new \Core\Router();

// Enable CORS for API requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Auth routes
$router->post('/api/auth/register', 'AuthController@register');
$router->post('/api/auth/login', 'AuthController@login');
$router->post('/api/auth/logout', 'AuthController@logout');
$router->get('/api/auth/me', 'AuthController@me');
$router->put('/api/auth/profile', 'AuthController@updateProfile');
$router->put('/api/auth/password', 'AuthController@changePassword');

// Product routes
$router->get('/api/products', 'ProductController@index');
$router->get('/api/products/featured', 'ProductController@featured');
$router->get('/api/products/daily-best', 'ProductController@dailyBest');
$router->get('/api/products/category/{category}', 'ProductController@byCategory');
$router->get('/api/products/search', 'ProductController@search');
$router->get('/api/products/{id}', 'ProductController@show');
$router->post('/api/products', 'ProductController@store');
$router->put('/api/products/{id}', 'ProductController@update');
$router->delete('/api/products/{id}', 'ProductController@delete');
$router->get('/api/categories', 'ProductController@categories');

// Order routes
$router->post('/api/orders', 'OrderController@create');
$router->get('/api/orders/my', 'OrderController@myOrders');
$router->get('/api/orders/{id}', 'OrderController@show');
$router->put('/api/orders/{id}/cancel', 'OrderController@cancel');

// Admin order routes
$router->get('/api/admin/orders', 'OrderController@index');
$router->put('/api/admin/orders/{id}/status', 'OrderController@updateStatus');
$router->get('/api/admin/orders/stats', 'OrderController@stats');

// Admin user routes
$router->get('/api/admin/users', 'AdminController@users');
$router->get('/api/admin/users/{id}', 'AdminController@showUser');
$router->put('/api/admin/users/{id}', 'AdminController@updateUser');
$router->delete('/api/admin/users/{id}', 'AdminController@deleteUser');

// Admin dashboard routes
$router->get('/api/admin/dashboard', 'AdminController@dashboard');
$router->get('/api/admin/analytics', 'AdminController@analytics');

// Page routes (serve HTML pages)
$router->get('/', 'PageController@home');
$router->get('/home', 'PageController@home');
$router->get('/shop', 'PageController@shop');
$router->get('/category', 'PageController@category');
$router->get('/product', 'PageController@product');
$router->get('/cart', 'PageController@cart');
$router->get('/checkout', 'PageController@checkout');
$router->get('/about', 'PageController@about');
$router->get('/login', 'PageController@login');
$router->get('/signup', 'PageController@signup');
$router->get('/admin', 'PageController@admin');

// Resolve the request
try {
    echo $router->resolve();
} catch (Exception $e) {
    // Log error in production
    error_log($e->getMessage());
    
    // Return error response
    $router->json([
        'success' => false,
        'error' => 'Internal server error'
    ], 500);
}
?>

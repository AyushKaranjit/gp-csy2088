<?php
namespace Core;

/**
 * Simple Router Class
 * Handles routing requests to appropriate controllers
 */
class Router {
    private $routes = [];
    
    /**
     * Add a GET route
     */
    public function get($path, $callback) {
        $this->addRoute('GET', $path, $callback);
    }
    
    /**
     * Add a POST route
     */
    public function post($path, $callback) {
        $this->addRoute('POST', $path, $callback);
    }
    
    /**
     * Add a PUT route
     */
    public function put($path, $callback) {
        $this->addRoute('PUT', $path, $callback);
    }
    
    /**
     * Add a DELETE route
     */
    public function delete($path, $callback) {
        $this->addRoute('DELETE', $path, $callback);
    }
    
    /**
     * Add route to routes array
     */
    private function addRoute($method, $path, $callback) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }
    
    /**
     * Resolve the current request
     */
    public function resolve() {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        // Remove base path if running in subdirectory
        $basePath = '/websites/doko/public';
        if (strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestUri)) {
                $params = $this->extractParams($route['path'], $requestUri);
                return $this->callCallback($route['callback'], $params);
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        return json_encode(['error' => 'Route not found']);
    }
    
    /**
     * Check if path matches route pattern
     */
    private function matchPath($routePath, $requestUri) {
        // Convert route path to regex pattern
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $requestUri);
    }
    
    /**
     * Extract parameters from URL
     */
    private function extractParams($routePath, $requestUri) {
        $params = [];
        
        // Get parameter names from route path
        preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
        
        // Get parameter values from request URI
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $requestUri, $matches)) {
            array_shift($matches); // Remove full match
            
            foreach ($paramNames[1] as $index => $name) {
                if (isset($matches[$index])) {
                    $params[$name] = $matches[$index];
                }
            }
        }
        
        return $params;
    }
    
    /**
     * Call the route callback
     */
    private function callCallback($callback, $params = []) {
        if (is_callable($callback)) {
            return call_user_func_array($callback, [$params]);
        }
        
        if (is_string($callback)) {
            $parts = explode('@', $callback);
            $controllerName = $parts[0];
            $methodName = $parts[1] ?? 'index';
            
            $controllerClass = "Controllers\\{$controllerName}";
            
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $methodName)) {
                    return $controller->$methodName($params);
                }
            }
        }
        
        throw new \Exception("Invalid callback");
    }
    
    /**
     * Send JSON response
     */
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Get request input data
     */
    public static function getInput() {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?: [];
    }
}
?>

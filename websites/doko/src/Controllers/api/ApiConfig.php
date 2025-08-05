<?php
/**
 * API Configuration and Base Classes
 * DOKO Grocery E-commerce
 */

// API Base Configuration
class ApiConfig {
    
    // API Version
    const VERSION = '1.0';
    
    // Response formats
    const RESPONSE_FORMAT_JSON = 'json';
    const RESPONSE_FORMAT_XML = 'xml';
    
    // Default pagination limits
    const DEFAULT_PAGE_SIZE = 20;
    const MAX_PAGE_SIZE = 100;
    
    // File upload limits
    const MAX_FILE_SIZE = 5242880; // 5MB
    const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    // Rate limiting
    const RATE_LIMIT_REQUESTS = 100;
    const RATE_LIMIT_WINDOW = 3600; // 1 hour
    
    // Cache settings
    const CACHE_ENABLED = true;
    const CACHE_TTL = 300; // 5 minutes
    
    // Error codes
    const ERROR_AUTHENTICATION = 401;
    const ERROR_AUTHORIZATION = 403;
    const ERROR_NOT_FOUND = 404;
    const ERROR_VALIDATION = 422;
    const ERROR_RATE_LIMIT = 429;
    const ERROR_SERVER = 500;
}

/**
 * Base API Response Handler
 */
class ApiResponse {
    
    public static function success($data = null, $message = 'Success', $statusCode = 200) {
        http_response_code($statusCode);
        
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('c'),
            'api_version' => ApiConfig::VERSION
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    public static function error($message = 'An error occurred', $statusCode = 500, $details = null) {
        http_response_code($statusCode);
        
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $statusCode,
            'timestamp' => date('c'),
            'api_version' => ApiConfig::VERSION
        ];
        
        if ($details !== null) {
            $response['details'] = $details;
        }
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    public static function paginated($data, $page, $totalItems, $itemsPerPage, $message = 'Success') {
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'current_page' => (int)$page,
                'total_pages' => (int)$totalPages,
                'total_items' => (int)$totalItems,
                'items_per_page' => (int)$itemsPerPage,
                'has_next' => $page < $totalPages,
                'has_previous' => $page > 1
            ],
            'timestamp' => date('c'),
            'api_version' => ApiConfig::VERSION
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * API Request Validator
 */
class ApiValidator {
    
    public static function validateRequired($data, $requiredFields) {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            ApiResponse::error(
                'Missing required fields: ' . implode(', ', $missing),
                ApiConfig::ERROR_VALIDATION,
                ['missing_fields' => $missing]
            );
        }
        
        return true;
    }
    
    public static function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            ApiResponse::error('Invalid email format', ApiConfig::ERROR_VALIDATION);
        }
        return true;
    }
    
    public static function validatePassword($password, $minLength = 6) {
        if (strlen($password) < $minLength) {
            ApiResponse::error(
                "Password must be at least {$minLength} characters long",
                ApiConfig::ERROR_VALIDATION
            );
        }
        return true;
    }
    
    public static function validateImage($file) {
        // Check if file was uploaded
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            ApiResponse::error('No file uploaded or upload error', ApiConfig::ERROR_VALIDATION);
        }
        
        // Check file size
        if ($file['size'] > ApiConfig::MAX_FILE_SIZE) {
            ApiResponse::error(
                'File size too large. Maximum ' . (ApiConfig::MAX_FILE_SIZE / 1024 / 1024) . 'MB allowed',
                ApiConfig::ERROR_VALIDATION
            );
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, ApiConfig::ALLOWED_IMAGE_TYPES)) {
            ApiResponse::error(
                'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed',
                ApiConfig::ERROR_VALIDATION
            );
        }
        
        return true;
    }
    
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * API Rate Limiter
 */
class ApiRateLimit {
    
    public static function check($identifier = null) {
        if (!$identifier) {
            $identifier = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        $cacheKey = 'rate_limit_' . md5($identifier);
        
        // Simple file-based rate limiting (in production, use Redis or Memcached)
        $cacheFile = sys_get_temp_dir() . '/' . $cacheKey;
        
        $currentTime = time();
        $windowStart = $currentTime - ApiConfig::RATE_LIMIT_WINDOW;
        
        // Read existing requests
        $requests = [];
        if (file_exists($cacheFile)) {
            $data = file_get_contents($cacheFile);
            $requests = json_decode($data, true) ?: [];
        }
        
        // Filter requests within the current window
        $requests = array_filter($requests, function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        // Check if limit exceeded
        if (count($requests) >= ApiConfig::RATE_LIMIT_REQUESTS) {
            ApiResponse::error(
                'Rate limit exceeded. Please try again later.',
                ApiConfig::ERROR_RATE_LIMIT
            );
        }
        
        // Add current request
        $requests[] = $currentTime;
        
        // Save updated requests
        file_put_contents($cacheFile, json_encode($requests));
        
        return true;
    }
}

/**
 * API Logger
 */
class ApiLogger {
    
    public static function log($level, $message, $context = []) {
        $logEntry = [
            'timestamp' => date('c'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ];
        
        $logLine = json_encode($logEntry) . PHP_EOL;
        
        $logFile = __DIR__ . '/../../logs/api_' . date('Y-m-d') . '.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    public static function info($message, $context = []) {
        self::log('info', $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::log('warning', $message, $context);
    }
    
    public static function error($message, $context = []) {
        self::log('error', $message, $context);
    }
}

/**
 * API Authentication Helper
 */
class ApiAuth {
    
    public static function requireAuth() {
        require_once __DIR__ . '/../Controllers/AuthController.php';
        
        $auth = new AuthController();
        if (!$auth->isLoggedIn()) {
            ApiResponse::error('Authentication required', ApiConfig::ERROR_AUTHENTICATION);
        }
        
        return $auth;
    }
    
    public static function requireAdmin() {
        $auth = self::requireAuth();
        
        if (!$auth->isAdmin()) {
            ApiResponse::error('Admin access required', ApiConfig::ERROR_AUTHORIZATION);
        }
        
        return $auth;
    }
    
    public static function getCurrentUser() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'role' => $_SESSION['role'] ?? 'customer',
            'full_name' => $_SESSION['full_name'] ?? null
        ];
    }
}

/**
 * API Utility Functions
 */
class ApiUtils {
    
    public static function generateSlug($string) {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        return trim($string, '-');
    }
    
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    public static function generateUniqueId($prefix = '') {
        return $prefix . uniqid() . '_' . time();
    }
    
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    public static function sanitizeFilename($filename) {
        // Remove path traversal attempts
        $filename = basename($filename);
        
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Limit length
        if (strlen($filename) > 255) {
            $pathinfo = pathinfo($filename);
            $extension = isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '';
            $filename = substr($pathinfo['filename'], 0, 255 - strlen($extension)) . $extension;
        }
        
        return $filename;
    }
}

// Set common headers for all API responses
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Enable error logging
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', false);
}

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set error handler for API
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    ApiLogger::error("PHP Error: $message", [
        'file' => $file,
        'line' => $line,
        'severity' => $severity
    ]);
    
    if (!DEBUG_MODE) {
        ApiResponse::error('Internal server error', ApiConfig::ERROR_SERVER);
    }
    
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Set exception handler for API
set_exception_handler(function($exception) {
    ApiLogger::error("Uncaught Exception: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    if (!DEBUG_MODE) {
        ApiResponse::error('Internal server error', ApiConfig::ERROR_SERVER);
    } else {
        ApiResponse::error($exception->getMessage(), ApiConfig::ERROR_SERVER, [
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ]);
    }
});
?>

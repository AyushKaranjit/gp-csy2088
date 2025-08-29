<?php
/**
 * PHPUnit Test Bootstrap
 * Sets up environment for DOKO E-commerce tests
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering & session early to avoid 'headers already sent' warnings later
if (!ob_get_level()) { ob_start(); }
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Set timezone
date_default_timezone_set('UTC');

// Define paths
define('TEST_ROOT', __DIR__);
define('PROJECT_ROOT', dirname(__DIR__));
define('CONFIG_DIR', PROJECT_ROOT . '/config');
define('SRC_DIR', PROJECT_ROOT . '/src');
define('PUBLIC_DIR', PROJECT_ROOT . '/public');

// Include composer autoloader first (preferred method)
if (file_exists(PROJECT_ROOT . '/vendor/autoload.php')) {
    require_once PROJECT_ROOT . '/vendor/autoload.php';
    // Explicitly include AuthController because it is global namespace (not PSR-4) so autoloader won't load it automatically
    $authFile = SRC_DIR . '/Controllers/AuthController.php';
    if (file_exists($authFile)) require_once $authFile;
} else {
    // Silent fallback include path
    
    // Fallback: Include project files manually
    if (file_exists(SRC_DIR . '/Controllers/AuthController.php')) {
        require_once SRC_DIR . '/Controllers/AuthController.php';
    }
}

// Set up test database configuration
class TestDatabaseConfig {
    public static function setup() {
        // Create test database configuration
        $_ENV['TEST_MODE'] = true;
        putenv('TEST_MODE=1');

        // Prefer docker network hostname, but fall back to localhost when running tests outside containers
        $preferredHost = 'mysql';
        $port = 3306;
        $resolved = @gethostbyname($preferredHost);
        $useHost = $preferredHost;
        $reachable = false;
        if ($resolved && $resolved !== $preferredHost) {
            // DNS resolved; verify port reachable
            $conn = @fsockopen($resolved, $port, $errno, $errstr, 0.5);
            if ($conn) { $reachable = true; fclose($conn); }
        }
        if (!$reachable) {
            // Try direct localhost / 127.0.0.1 (published port from docker-compose)
            $candidateHosts = ['127.0.0.1','localhost'];
            foreach ($candidateHosts as $cand) {
                $conn = @fsockopen($cand, $port, $errno, $errstr, 0.5);
                if ($conn) { $useHost = $cand; fclose($conn); break; }
            }
        }

        $_ENV['DB_HOST'] = $useHost;
        $_ENV['DB_NAME'] = 'doko_ecommerce';
        $_ENV['DB_USER'] = 'student';
        $_ENV['DB_PASS'] = 'student';
        // Export also via putenv for getenv() lookups
        putenv("DB_HOST={$useHost}");
        putenv('DB_NAME=doko_ecommerce');
        putenv('DB_USER=student');
        putenv('DB_PASS=student');
    // Session already started earlier
    }
}

// Initialize test configuration
TestDatabaseConfig::setup();

// Only mark docker env if 'web' service hostname resolves and port 80 reachable
$webResolved = @gethostbyname('web');
$webReachable = false;
if ($webResolved && $webResolved !== 'web') {
    $conn = @fsockopen($webResolved, 80, $e1, $e2, 0.3);
    if ($conn) { $webReachable = true; fclose($conn); }
}
if ($webReachable) { putenv('DOCKER_ENV=true'); } else { putenv('DOCKER_ENV=false'); }

// Capture test suite start time for deterministic filtering (e.g., product search scoping)
if (!defined('TEST_START_TIME')) {
    define('TEST_START_TIME', time());
}

// Keep bootstrap silent to prevent header issues

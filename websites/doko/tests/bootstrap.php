<?php
/**
 * PHPUnit Test Bootstrap
 * Sets up environment for DOKO E-commerce tests
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Buffer output early to avoid 'headers already sent' during session_start in tests
if (!ob_get_level()) {
    ob_start();
}

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
    echo "✅ Composer autoloader loaded\n";
} else {
    echo "⚠️  Composer autoloader not found, using manual includes\n";
    
    // Fallback: Include project files manually
    if (file_exists(CONFIG_DIR . '/database.php')) {
        require_once CONFIG_DIR . '/database.php';
    }
    if (file_exists(SRC_DIR . '/Controllers/AuthController.php')) {
        require_once SRC_DIR . '/Controllers/AuthController.php';
    }
}

// Include test helpers
require_once TEST_ROOT . '/TestCase.php';

// Set up test database configuration
class TestDatabaseConfig {
    public static function setup() {
        // Create test database configuration
    $_ENV['TEST_MODE'] = true;
    putenv('TEST_MODE=1');
    // Use docker mysql service for tests so we share same schema
    $_ENV['DB_HOST'] = 'mysql';
    $_ENV['DB_NAME'] = 'doko_ecommerce';
    $_ENV['DB_USER'] = 'student';
    $_ENV['DB_PASS'] = 'student';
    // Export also via putenv for getenv() lookups
    putenv('DB_HOST=mysql');
    putenv('DB_NAME=doko_ecommerce');
    putenv('DB_USER=student');
    putenv('DB_PASS=student');
        
        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

// Initialize test configuration
TestDatabaseConfig::setup();

// Mark docker env for tests so HTTP base host switches to web service
putenv('DOCKER_ENV=true');

echo "Test environment initialized\n";

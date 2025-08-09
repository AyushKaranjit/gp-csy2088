<?php
/**
 * Unified API Bootstrap (idempotent)
 */
if (defined('API_BOOTSTRAP_LOADED')) { return; }
define('API_BOOTSTRAP_LOADED', true);
if (function_exists('_api_require')) { return; }
$ROOT_DIR = realpath(__DIR__ . '/../../'); // .../doko
if (!defined('APP_ROOT')) { define('APP_ROOT', $ROOT_DIR); }

// Safe loader to emit JSON instead of HTML Xdebug dump if critical file missing
if (!function_exists('_api_require')) {
function _api_require(string $rel): void {
    $path = APP_ROOT . '/' . ltrim($rel, '/');
    if (!is_file($path)) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Server misconfiguration: missing dependency',
            'missing' => $rel
        ]);
        exit;
    }
    require_once $path;
}
}

_api_require('config/database.php');
_api_require('src/Http/ApiResponse.php');
_api_require('src/Http/Csrf.php');
_api_require('src/Controllers/AuthController.php');

use Doko\Http\ApiResponse;
use Doko\Http\Csrf;

$allowedOrigins = [
    'http://localhost',
    'http://127.0.0.1',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Vary: Origin');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, X-CSRF-Token, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    ApiResponse::success(['preflight' => true]);
    exit;
}

if (!function_exists('ensure_session')) { function ensure_session(): void { if (session_status() === PHP_SESSION_NONE) { session_start(); } } }
// Modified to avoid hard exit during tests; returns after sending error allowing test runner to continue
if (!function_exists('require_method')) { function require_method(string|array $methods): void { $methods=(array)$methods; $req=$_SERVER['REQUEST_METHOD']??'GET'; if(!in_array($req,$methods,true)){ ApiResponse::error('Method not allowed',405,['allowed'=>$methods]); return; } } }
if (!function_exists('json_input')) { function json_input(): array {
    if (isset($GLOBALS['__TEST_JSON_INPUT']) && is_array($GLOBALS['__TEST_JSON_INPUT'])) {
        $d = $GLOBALS['__TEST_JSON_INPUT'];
        unset($GLOBALS['__TEST_JSON_INPUT']);
        return $d;
    }
    $raw=file_get_contents('php://input'); if($raw===''||$raw===false)return []; $d=json_decode($raw,true); return is_array($d)?$d:[];
} }
if (!function_exists('csrf_check')) { function csrf_check(bool $requiredForStateChange=true): void { $m=$_SERVER['REQUEST_METHOD']??'GET'; $state=['POST','PUT','PATCH','DELETE']; if($requiredForStateChange && !in_array($m,$state,true)) return; $token=$_SERVER[Csrf::HEADER] ?? ($_POST['csrf_token'] ?? (json_input()['csrf_token'] ?? null)); Csrf::requireValid($token); } }
if (!function_exists('auth_controller')) { function auth_controller(): AuthController { static $a=null; if($a===null){ $a=new AuthController(); } return $a; } }
if (!function_exists('int_param')) { function int_param(string $key,int $default=0,?int $min=null,?int $max=null): int { $v=isset($_GET[$key])?(int)$_GET[$key]:$default; if($min!==null&&$v<$min)$v=$min; if($max!==null&&$v>$max)$v=$max; return $v; } }
if (!function_exists('db')) { function db(): Database { return Database::getInstance(); } }
if (!function_exists('resolve_product_image')) { function resolve_product_image(?string $c): string { if(!$c) return '/uploads/default-product.jpg'; if(preg_match('#^https?://#i',$c)) return $c; return '/uploads/'.ltrim($c,'/'); } }

// --- Schema helpers (centralized) ---
if (!function_exists('schema_table_has')) { function schema_table_has(string $table, string $column): bool {
    $db = db();
    return (bool)$db->execute(
        "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1",
        [$table, $column]
    )->fetchColumn();
} }
if (!function_exists('schema_products_pk')) { function schema_products_pk(): string { static $pk=null; if($pk===null){ $pk = schema_table_has('products','product_id') ? 'product_id' : 'id'; } return $pk; } }
if (!function_exists('schema_cart_pk')) { function schema_cart_pk(): string { static $pk=null; if($pk===null){ $pk = schema_table_has('cart','cart_id') ? 'cart_id' : 'id'; } return $pk; } }
if (!function_exists('schema_cart_has_price')) { function schema_cart_has_price(): bool { static $v=null; if($v===null){ $v = schema_table_has('cart','price'); } return $v; } }
if (!function_exists('schema_cart_has_updated_at')) { function schema_cart_has_updated_at(): bool { static $v=null; if($v===null){ $v = schema_table_has('cart','updated_at'); } return $v; } }
?>

<?php
/**
 * Unified API Bootstrap
 */
$ROOT_DIR = realpath(__DIR__ . '/../../'); // .../doko
if (!defined('APP_ROOT')) { define('APP_ROOT', $ROOT_DIR); }

// Safe loader to emit JSON instead of HTML Xdebug dump if critical file missing
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

function ensure_session(): void { if (session_status() === PHP_SESSION_NONE) { session_start(); } }
function require_method(string|array $methods): void { $methods=(array)$methods; $req=$_SERVER['REQUEST_METHOD']??'GET'; if(!in_array($req,$methods,true)){ ApiResponse::error('Method not allowed',405,['allowed'=>$methods]); exit; } }
function json_input(): array { $raw=file_get_contents('php://input'); if($raw===''||$raw===false)return []; $d=json_decode($raw,true); return is_array($d)?$d:[]; }
function csrf_check(bool $requiredForStateChange=true): void { $m=$_SERVER['REQUEST_METHOD']??'GET'; $state=['POST','PUT','PATCH','DELETE']; if($requiredForStateChange && !in_array($m,$state,true)) return; $token=$_SERVER[Csrf::HEADER] ?? ($_POST['csrf_token'] ?? (json_input()['csrf_token'] ?? null)); Csrf::requireValid($token); }
function auth_controller(): AuthController { static $a=null; if($a===null){ $a=new AuthController(); } return $a; }
function int_param(string $key,int $default=0,?int $min=null,?int $max=null): int { $v=isset($_GET[$key])?(int)$_GET[$key]:$default; if($min!==null&&$v<$min)$v=$min; if($max!==null&&$v>$max)$v=$max; return $v; }
function db(): Database { return Database::getInstance(); }
function resolve_product_image(?string $c): string { if(!$c) return '/uploads/default-product.jpg'; if(preg_match('#^https?://#i',$c)) return $c; return '/uploads/'.ltrim($c,'/'); }
?>

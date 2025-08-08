<?php
// Refactored register endpoint using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method('POST');

try {
    $auth = auth_controller();
    $input = json_input();
    if (!$input) { ApiResponse::error('Invalid JSON data', 400); }
    $required = ['username','email','password','first_name','last_name'];
    foreach ($required as $f) {
        if (!isset($input[$f]) || trim((string)$input[$f])==='') {
            ApiResponse::error("Field '$f' is required", 400);
        }
    }
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        ApiResponse::error('Invalid email format', 400);
    }
    if (strlen($input['password']) < 6) {
        ApiResponse::error('Password must be at least 6 characters long', 400);
    }
    $result = $auth->register($input);
    if (empty($result['success'])) { ApiResponse::error($result['message'] ?? 'Registration failed', 400, $result); }
    ApiResponse::success($result);
} catch (Throwable $e) {
    error_log('register error: '.$e->getMessage());
    ApiResponse::error('Registration failed', 500, ['exception' => $e->getMessage()]);
}

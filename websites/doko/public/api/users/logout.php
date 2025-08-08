<?php
// Refactored logout endpoint using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method('POST');

try {
    $auth = auth_controller();
    $result = $auth->logout();
    // Ensure standard shape
    if (!isset($result['success'])) { $result['success'] = true; }
    ApiResponse::success($result);
} catch (Throwable $e) {
    error_log('logout error: '.$e->getMessage());
    ApiResponse::error('Failed to logout', 500, ['exception' => $e->getMessage()]);
}

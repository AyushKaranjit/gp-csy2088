<?php
// Refactored user profile endpoint using bootstrap & ApiResponse
require __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

require_method('GET');

try {
    $auth = auth_controller();
    if (!$auth->isLoggedIn()) {
        ApiResponse::error('User not logged in', 401);
    }
    $profile = $auth->getCurrentUser();
    if (!$profile) {
        ApiResponse::error('User profile not found', 404);
    }
    ApiResponse::success(['user' => $profile]);
} catch (Throwable $e) {
    error_log('profile error: '.$e->getMessage());
    ApiResponse::error('Failed to fetch profile', 500, ['exception' => $e->getMessage()]);
}

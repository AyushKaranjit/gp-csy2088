<?php
// Deprecated legacy login endpoint. Use /api/users/auth-login.php instead.
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;

ApiResponse::error('Endpoint deprecated. Use /api/users/auth-login.php', 410, [
    'deprecated' => true,
    'replacement' => '/api/users/auth-login.php'
]);

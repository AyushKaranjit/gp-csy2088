<?php
/**
 * Simple logout script
 */
// Set session cookie parameters for better browser compatibility
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => false,
    'samesite' => 'Lax'
]);
session_start();
require_once __DIR__ . '/../src/Controllers/AuthController.php';

try {
    $auth = new AuthController();
    $auth->logout();
    header('Location: index.php?message=logged_out');
} catch (Exception $e) {
    session_destroy();
    header('Location: index.php');
}
exit;
?>

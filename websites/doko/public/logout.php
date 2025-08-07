<?php
/**
 * Simple logout script
 */
session_start();
require_once '../src/Controllers/AuthController.php';

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

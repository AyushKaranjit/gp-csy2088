<?php
/**
 * Manager Dashboard Entry Point
 * DOKO Grocery E-commerce Manager Panel
 */

require_once '../config/database.php';
require_once '../src/Controllers/AuthController.php';

// Check authentication - only managers and admins can access
$auth = new AuthController();
if (!$auth->hasManagerAccess()) {
    header('Location: login.php?error=unauthorized');
    exit;
}

// Redirect to manager dashboard
header('Location: manager/dashboard/');
exit;
?>

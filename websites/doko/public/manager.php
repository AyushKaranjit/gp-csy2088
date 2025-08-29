<?php
/**
 * DOKO E-Commerce Website - Manager Dashboard Entry Point
 *
 * This website was developed as part of an academic project for educational purposes.
 * All code was written by the student developer to demonstrate web development skills.
 *
 * @author Student Developer
 * @version 1.0
 * @date 2025
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

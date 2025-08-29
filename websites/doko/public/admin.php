<?php
/**
 * DOKO E-Commerce Website - Admin Panel Entry Point
 *
 * This website was developed as part of an academic project for educational purposes.
 * All code was written by the student developer to demonstrate web development skills.
 *
 * @author Student Developer
 * @version 1.0
 * @date 2025
 */

// Admin Panel Entry Point
// Redirects to the organized admin dashboard
// (No leading whitespace or closing PHP tag to avoid header issues)

// Absolute-safe redirect (relative to /public)
$target = 'admin/index.php';

if (!headers_sent()) {
    header('Location: ' . $target);
    exit;
}

// Fallback if headers already sent (very unlikely now)
echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target, ENT_QUOTES) . '"><title>Redirecting…</title></head><body>';
echo 'Redirecting to <a href="' . htmlspecialchars($target, ENT_QUOTES) . '">' . htmlspecialchars($target, ENT_QUOTES) . '</a>.';
echo '</body></html>';

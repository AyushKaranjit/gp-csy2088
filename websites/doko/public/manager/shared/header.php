<?php
/**
 * Manager Header Template
 * DOKO Grocery E-commerce Manager Panel - Admin Format
 */

if (!isset($page_title)) {
    $page_title = 'Manager Panel | DOKO';
}

if (!isset($current_page)) {
    $current_page = 'manager';
}

// Unified theme: storefront header + manager navigation
$ADMIN_UI = true; // suppress storefront header
$additional_css = $additional_css ?? [];
$additional_css[] = '/css/admin.css';
include '../../../template/header.php';
include '../../../template/manager-nav.php';
?>

<style>
/* Manager Dashboard Specific Styles */
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
    min-height: calc(100vh - 70px);
}
</style>

<div class="admin-container">

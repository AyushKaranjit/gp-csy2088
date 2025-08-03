<?php
/**
 * Base Layout Template
 * This file includes the header and footer templates
 */

// Set default values
$page_title = isset($page_title) ? $page_title : 'DOKO - Professional E-Commerce Platform';
$current_page = isset($current_page) ? $current_page : '';
$additional_css = isset($additional_css) ? $additional_css : [];
$additional_js = isset($additional_js) ? $additional_js : [];

// Include header
include_once 'header.php';
?>

<!-- Main Content Area -->
<main class="main-content">
    <?php if (isset($content)): ?>
        <?php echo $content; ?>
    <?php endif; ?>
</main>

<?php
// Include footer
include_once 'footer.php';
?>

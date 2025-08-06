<?php
/**
 * Breadcrumb Component Template
 * Usage: include 'template/breadcrumb.php';
 */

if (!isset($breadcrumb_items) || !is_array($breadcrumb_items)) {
    return;
}
?>

<nav class="breadcrumb">
    <div class="container">
        <ol class="breadcrumb-list">
            <?php foreach ($breadcrumb_items as $index => $item): ?>
                <?php $is_last = ($index === count($breadcrumb_items) - 1); ?>
                <?php $title = isset($item['title']) && $item['title'] !== null ? $item['title'] : ''; ?>
                <?php $url = isset($item['url']) && $item['url'] !== null ? $item['url'] : '#'; ?>
                <li class="breadcrumb-item <?php echo $is_last ? 'active' : ''; ?>">
                    <?php if ($is_last): ?>
                        <?php echo clean_output($title); ?>
                    <?php else: ?>
                        <a href="<?php echo clean_output($url); ?>">
                            <?php echo clean_output($title); ?>
                        </a>
                    <?php endif; ?>
                </li>
                <?php if (!$is_last): ?>
                    <li class="breadcrumb-separator">
                        <i class="fas fa-chevron-right"></i>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

<style>
.breadcrumb {
    background: var(--light-bg);
    padding: 1rem 0;
    border-bottom: 1px solid #e0e0e0;
}

.breadcrumb-list {
    display: flex;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 0.5rem;
}

.breadcrumb-item {
    font-size: 0.9rem;
}

.breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
    transition: var(--transition);
}

.breadcrumb-item a:hover {
    color: var(--accent-color);
}

.breadcrumb-item.active {
    color: var(--dark-text);
    font-weight: 500;
}

.breadcrumb-separator {
    color: var(--light-text);
    font-size: 0.7rem;
}
</style>

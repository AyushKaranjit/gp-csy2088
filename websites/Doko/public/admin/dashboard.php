<?php
// admin/dashboard.php - Admin Dashboard (alias for index.php)
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../functions/loadTemplate.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Dashboard stats
$stats = [
    'users' => 0,
    'products' => 0,
    'categories' => 0,
    'carts' => 0
];

// Get stats from DB
$stats['users'] = $conn->query('SELECT COUNT(*) FROM users')->fetch_row()[0];
$stats['products'] = $conn->query('SELECT COUNT(*) FROM products')->fetch_row()[0];
$stats['categories'] = $conn->query('SELECT COUNT(*) FROM categories')->fetch_row()[0];
$stats['carts'] = $conn->query('SELECT COUNT(*) FROM carts')->fetch_row()[0];

ob_start();
?>
<div class="section">
    <h2 class="section-title">Admin Dashboard</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:2rem;max-width:900px;margin:0 auto;">
        <div class="alert alert-info"><strong>Users:</strong> <?php echo $stats['users']; ?></div>
        <div class="alert alert-info"><strong>Products:</strong> <?php echo $stats['products']; ?></div>
        <div class="alert alert-info"><strong>Categories:</strong> <?php echo $stats['categories']; ?></div>
        <div class="alert alert-info"><strong>Carts:</strong> <?php echo $stats['carts']; ?></div>
    </div>
    <div style="margin-top:2.5rem;text-align:center;">
        <a href="profile.php" class="btn btn-primary" style="margin:0 1rem;">Profile</a>
        <a href="users.php" class="btn" style="margin:0 1rem;">Users</a>
        <a href="products.php" class="btn" style="margin:0 1rem;">Products</a>
        <a href="categories.php" class="btn" style="margin:0 1rem;">Categories</a>
        <a href="cart.php" class="btn" style="margin:0 1rem;">Carts</a>
    </div>
</div>
<?php
$content = ob_get_clean();
loadTemplate('Admin Dashboard', $content);
?>

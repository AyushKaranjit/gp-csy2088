<?php
/**
 * Admin Dashboard - Complete Rewrite
 * DOKO Grocery E-commerce Admin Panel
 */

require_once '../../../config/database.php';
require_once '../../../src/Controllers/AuthController.php';

// Check authentication
$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$page_title = 'Admin Dashboard | DOKO';
$current_page = 'admin';

// Get dashboard statistics
try {
    $database = Database::getInstance();
    $db = $database->getConnection();

    // Get total users count
    $userCountQuery = "SELECT COUNT(*) as total_users FROM users WHERE role = 'customer'";
    $stmt = $db->query($userCountQuery);
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Get total products count
    $productCountQuery = "SELECT COUNT(*) as total_products FROM products WHERE status = 'active'";
    $stmt = $db->query($productCountQuery);
    $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

    // Get total orders count
    $orderCountQuery = "SELECT COUNT(*) as total_orders FROM orders";
    $stmt = $db->query($orderCountQuery);
    $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

    // Get total revenue
    $revenueQuery = "SELECT COALESCE(SUM(total_amount), 0) as total_revenue FROM orders WHERE status IN ('completed', 'delivered')";
    $stmt = $db->query($revenueQuery);
    $totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'];

    // Get recent orders
    $recentOrdersQuery = "SELECT o.*, u.first_name, u.last_name, u.email 
                          FROM orders o 
                          LEFT JOIN users u ON o.user_id = u.user_id 
                          ORDER BY o.created_at DESC 
                          LIMIT 10";
    $stmt = $db->query($recentOrdersQuery);
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get low stock products
    $lowStockQuery = "SELECT * FROM products WHERE stock_quantity <= 10 AND status = 'active' ORDER BY stock_quantity ASC LIMIT 10";
    $stmt = $db->query($lowStockQuery);
    $lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = "Error loading dashboard data: " . $e->getMessage();
    $totalUsers = $totalProducts = $totalOrders = $totalRevenue = 0;
    $recentOrders = $lowStockProducts = [];
}

include '../../../template/admin-header.php';
?>

<style>
/* Admin Dashboard Specific Styles */
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
    min-height: calc(100vh - 70px);
}

/* Admin Header - Professional Design */
.admin-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 3.5rem 2.5rem;
    border-radius: 20px;
    margin-bottom: 3rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.1);
}

.admin-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.05) 0%, transparent 50%);
    opacity: 0.7;
}

.admin-header * {
    position: relative;
    z-index: 1;
}

.admin-header h1 {
    margin: 0;
    font-size: 3.25rem;
    font-weight: 800;
    letter-spacing: -0.05em;
    margin-bottom: 0.75rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.admin-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.2rem;
    font-weight: 400;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

/* Statistics Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    padding: 2.5rem;
    border-radius: 16px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--stat-gradient, linear-gradient(135deg, #16a34a 0%, #22c55e 100%));
}

.stat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.stat-card.users { 
    --stat-gradient: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
}
.stat-card.products { 
    --stat-gradient: linear-gradient(135deg, #16a34a 0%, #4ade80 100%);
}
.stat-card.orders { 
    --stat-gradient: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
}
.stat-card.revenue {
    --stat-gradient: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
}

.stat-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
}

.stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
    background: var(--stat-gradient, linear-gradient(135deg, #16a34a 0%, #22c55e 100%));
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.stat-label {
    color: #64748b;
    font-size: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Admin Content Layout */
.admin-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 2.5rem;
}

.main-content, .sidebar-content {
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
}

/* Admin Cards */
.admin-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    border: 1px solid #f1f5f9;
    overflow: hidden;
    transition: all 0.3s ease;
}

.admin-card:hover {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    transform: translateY(-2px);
}

.admin-card-header {
    padding: 2rem 2.5rem;
    border-bottom: 1px solid #f1f5f9;
    background: linear-gradient(135deg, #f1f5f9 0%, #f8fafc 100%);
    position: relative;
}

.admin-card-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
}

.admin-card-header h3 {
    margin: 0;
    color: #1e293b;
    font-size: 1.375rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.admin-card-header i {
    color: #16a34a;
    opacity: 0.8;
}

.admin-card-content {
    padding: 2.5rem;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, white 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    text-decoration: none;
    color: #1e293b;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    border-color: #16a34a;
    text-decoration: none;
    color: #16a34a;
}

.quick-action-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.quick-action-text h4 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
}

.quick-action-text p {
    margin: 0.25rem 0 0 0;
    font-size: 0.9rem;
    color: #64748b;
}

/* Order and Product Items */
.order-item, .product-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.order-item:hover, .product-item:hover {
    background: #f8fafc;
    padding-left: 1rem;
    padding-right: 1rem;
    margin-left: -1rem;
    margin-right: -1rem;
    border-radius: 8px;
}

.order-item:last-child, .product-item:last-child {
    border-bottom: none;
}

.order-info h4, .product-name {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    color: #1e293b;
    font-weight: 600;
}

.order-info p {
    margin: 0;
    font-size: 0.875rem;
    color: #64748b;
}

.order-status, .stock-level {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.status-pending, .stock-low { 
    background: #fef3c7; 
    color: #92400e; 
    border: 1px solid #fcd34d;
}
.status-processing { 
    background: #dbeafe; 
    color: #1e40af; 
    border: 1px solid #60a5fa;
}
.status-completed, .stock-good { 
    background: #d1fae5; 
    color: #065f46; 
    border: 1px solid #34d399;
}
.status-cancelled, .stock-critical { 
    background: #fee2e2; 
    color: #991b1b; 
    border: 1px solid #fca5a5;
}

.no-data {
    text-align: center;
    color: #64748b;
    padding: 3rem 2rem;
    font-style: italic;
    font-size: 1.1rem;
}

.no-data i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
    display: block;
}

/* Alert styles */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
}

.alert-danger {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .admin-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .sidebar-content {
        order: -1;
    }
}

@media (max-width: 768px) {
    .admin-container {
        padding: 1rem;
    }
    
    .admin-header {
        padding: 2.5rem 1.5rem;
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .admin-header h1 {
        font-size: 2.5rem;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .stat-card {
        padding: 2rem 1.5rem;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .admin-card-header, .admin-card-content {
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .admin-header h1 {
        font-size: 2rem;
    }
    
    .stat-number {
        font-size: 2.25rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .order-item, .product-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1.25rem 1rem;
    }
}
</style>

<!-- Admin Container -->
<div class="admin-container">

    <!-- Admin Header -->
    <div class="admin-header">
        <h1><i class="fas fa-cogs"></i> Admin Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($currentUser['first_name']); ?>! Manage your DOKO store efficiently.</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card users">
            <div class="stat-header">
                <div>
                    <div class="stat-number"><?php echo number_format($totalUsers); ?></div>
                    <div class="stat-label">Total Customers</div>
                </div>
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="stat-card products">
            <div class="stat-header">
                <div>
                    <div class="stat-number"><?php echo number_format($totalProducts); ?></div>
                    <div class="stat-label">Active Products</div>
                </div>
                <div class="stat-icon products">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>

        <div class="stat-card orders">
            <div class="stat-header">
                <div>
                    <div class="stat-number"><?php echo number_format($totalOrders); ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-icon orders">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>

        <div class="stat-card revenue">
            <div class="stat-header">
                <div>
                    <div class="stat-number">NPR <?php echo number_format($totalRevenue, 0); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-icon revenue">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Content -->
    <div class="admin-content">
        <!-- Main Content -->
        <div class="main-content">
            <!-- Quick Actions -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="admin-card-content">
                    <div class="quick-actions">
                        <a href="../products/" class="quick-action-btn">
                            <div class="quick-action-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="quick-action-text">
                                <h4>Manage Products</h4>
                                <p>Add, edit, or remove products</p>
                            </div>
                        </a>
                        <a href="../users/" class="quick-action-btn">
                            <div class="quick-action-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="quick-action-text">
                                <h4>User Management</h4>
                                <p>View and manage customers</p>
                            </div>
                        </a>
                        <a href="../orders/" class="quick-action-btn">
                            <div class="quick-action-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="quick-action-text">
                                <h4>Order Management</h4>
                                <p>Process and track orders</p>
                            </div>
                        </a>
                        <a href="#" class="quick-action-btn" onclick="alert('Analytics dashboard coming soon!')">
                            <div class="quick-action-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="quick-action-text">
                                <h4>Analytics</h4>
                                <p>View detailed reports</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Content -->
        <div class="sidebar-content">
            <!-- Recent Orders -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3><i class="fas fa-shopping-cart"></i> Recent Orders</h3>
                </div>
                <div class="admin-card-content">
                    <?php if (empty($recentOrders)): ?>
                        <div class="no-data">
                            <i class="fas fa-inbox"></i>
                            No recent orders found
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <h4>Order #<?php echo htmlspecialchars($order['order_id']); ?></h4>
                                    <p><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                                    <p>NPR <?php echo number_format($order['total_amount'], 2); ?></p>
                                </div>
                                <div class="order-status status-<?php echo htmlspecialchars($order['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h3>
                </div>
                <div class="admin-card-content">
                    <?php if (empty($lowStockProducts)): ?>
                        <div class="no-data">
                            <i class="fas fa-check-circle"></i>
                            All products are well stocked
                        </div>
                    <?php else: ?>
                        <?php foreach ($lowStockProducts as $product): ?>
                            <div class="product-item">
                                <div class="product-name">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </div>
                                <div class="stock-level <?php echo $product['stock_quantity'] <= 5 ? 'stock-critical' : ($product['stock_quantity'] <= 10 ? 'stock-low' : 'stock-good'); ?>">
                                    <?php echo $product['stock_quantity']; ?> left
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth animations to cards
    const cards = document.querySelectorAll('.stat-card, .admin-card, .quick-action-btn');
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
    });
    
    // Animate cards in sequence
    setTimeout(() => {
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }, 200);
});
</script>

<?php include '../../../template/footer.php'; ?>

<?php
/**
 * Manager Dashboard - Professional Design
 * DOKO Grocery E-commerce Manager Panel
 */

require_once '../../../config/database.php';
require_once '../../../src/Controllers/AuthController.php';

// Check authentication
$auth = new AuthController();
if (!$auth->hasManagerAccess()) {
    header('Location: ../../login.php?error=unauthorized');
    exit;
}

$currentUser = $auth->getCurrentUser();
$page_title = 'Manager Dashboard | DOKO';
$current_page = 'manager';

// Get dashboard statistics
try {
    $database = Database::getInstance();
    $db = $database->getConnection();

    // Get total products count
    $productCountQuery = "SELECT COUNT(*) as total_products FROM products WHERE status = 'active'";
    $stmt = $db->query($productCountQuery);
    $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

    // Get total orders count (all time)
    $orderCountQuery = "SELECT COUNT(*) as total_orders FROM orders";
    $stmt = $db->query($orderCountQuery);
    $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

    // Get pending orders count
    $pendingOrdersQuery = "SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'";
    $stmt = $db->query($pendingOrdersQuery);
    $pendingOrders = $stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];

    // Get processing orders count
    $processingOrdersQuery = "SELECT COUNT(*) as processing_orders FROM orders WHERE status = 'processing'";
    $stmt = $db->query($processingOrdersQuery);
    $processingOrders = $stmt->fetch(PDO::FETCH_ASSOC)['processing_orders'];

    // Get low stock products count (less than 10 items)
    $lowStockQuery = "SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity < 10 AND status = 'active'";
    $stmt = $db->query($lowStockQuery);
    $lowStockProducts = $stmt->fetch(PDO::FETCH_ASSOC)['low_stock'];

    // Get recent orders for manager review
    $recentOrdersQuery = "SELECT o.*, u.first_name, u.last_name, u.email 
                          FROM orders o 
                          LEFT JOIN users u ON o.user_id = u.user_id 
                          ORDER BY o.created_at DESC 
                          LIMIT 10";
    $stmt = $db->query($recentOrdersQuery);
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get top selling products this month (fixed column name)
    $topProductsQuery = "SELECT p.name as product_name, p.product_id, SUM(oi.quantity) as total_sold, p.price
                         FROM order_items oi
                         JOIN products p ON oi.product_id = p.product_id
                         JOIN orders o ON oi.order_id = o.order_id
                         WHERE MONTH(o.created_at) = MONTH(CURRENT_DATE()) 
                         AND YEAR(o.created_at) = YEAR(CURRENT_DATE())
                         AND o.status IN ('completed', 'delivered', 'pending', 'processing')
                         GROUP BY p.product_id
                         ORDER BY total_sold DESC
                         LIMIT 5";
    $stmt = $db->query($topProductsQuery);
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get low stock product details
    $lowStockProductsQuery = "SELECT product_id, name, stock_quantity FROM products WHERE stock_quantity <= 10 AND status = 'active' ORDER BY stock_quantity ASC LIMIT 5";
    $stmt = $db->query($lowStockProductsQuery);
    $lowStockProductsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $totalProducts = $totalOrders = $pendingOrders = $processingOrders = $lowStockProducts = 0;
    $recentOrders = $topProducts = $lowStockProductsList = [];
}

// Unified theme: manager header + navigation
$MANAGER_UI = true;
$additional_css = $additional_css ?? [];
$additional_css[] = '/css/manager.css';
include_once '../shared/header.php';
?>

<style>
/* Manager Dashboard Specific Styles - Admin Design Format */
.manager-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
    min-height: calc(100vh - 70px);
}

/* Manager Header - Professional Design */
.manager-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 3.5rem 2.5rem;
    border-radius: 20px;
    margin-bottom: 3rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.1);
}

.manager-header::before {
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

.manager-header * {
    position: relative;
    z-index: 1;
}

.manager-header h1 {
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

.manager-header p {
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

.stat-card.products { 
    --stat-gradient: linear-gradient(135deg, #16a34a 0%, #4ade80 100%);
}
.stat-card.orders { 
    --stat-gradient: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
}
.stat-card.pending { 
    --stat-gradient: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
}
.stat-card.processing {
    --stat-gradient: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);
}
.stat-card.warning {
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

/* Manager Content Layout */
.manager-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 2.5rem;
}

.main-content, .sidebar-content {
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
}

/* Manager Cards */
.manager-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    border: 1px solid #f1f5f9;
    overflow: hidden;
    transition: all 0.3s ease;
}

.manager-card:hover {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    transform: translateY(-2px);
}

.manager-card-header {
    padding: 2rem 2.5rem;
    border-bottom: 1px solid #f1f5f9;
    background: linear-gradient(135deg, #f1f5f9 0%, #f8fafc 100%);
    position: relative;
}

.manager-card-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.manager-card-header h3 {
    margin: 0;
    color: #1e293b;
    font-size: 1.375rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.manager-card-header i {
    color: #667eea;
    opacity: 0.8;
}

.manager-card-content {
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
    border-color: #667eea;
    text-decoration: none;
    color: #667eea;
}

.quick-action-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

/* Responsive Design */
@media (max-width: 1024px) {
    .manager-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .sidebar-content {
        order: -1;
    }
}

@media (max-width: 768px) {
    .manager-container {
        padding: 1rem;
    }
    
    .manager-header {
        padding: 2.5rem 1.5rem;
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .manager-header h1 {
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
    
    .manager-card-header, .manager-card-content {
        padding: 1.5rem;
    }
}
</style>

<!-- Manager Container -->
<div class="manager-container">

    <!-- Manager Header -->
    <div class="manager-header">
        <h1><i class="fas fa-users-cog"></i> Manager Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($currentUser['first_name']); ?>! Manage orders and products efficiently.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
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

        <div class="stat-card pending">
            <div class="stat-header">
                <div>
                    <div class="stat-number"><?php echo number_format($pendingOrders); ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="stat-card processing">
            <div class="stat-header">
                <div>
                    <div class="stat-number"><?php echo number_format($processingOrders); ?></div>
                    <div class="stat-label">Processing Orders</div>
                </div>
                <div class="stat-icon processing">
                    <i class="fas fa-cogs"></i>
                </div>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div>
                    <div class="stat-number"><?php echo number_format($lowStockProducts); ?></div>
                    <div class="stat-label">Low Stock Items</div>
                </div>
                <div class="stat-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Manager Content -->
    <div class="manager-content">
        <!-- Main Content -->
        <div class="main-content">
            <!-- Quick Actions -->
            <div class="manager-card">
                <div class="manager-card-header">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="manager-card-content">
                    <div class="quick-actions">
                        <a href="../orders/" class="quick-action-btn">
                            <div class="quick-action-icon">
                                <i class="fas fa-list"></i>
                            </div>
                            <div class="quick-action-text">
                                <h4>Manage Orders</h4>
                                <p>View and process all orders</p>
                            </div>
                        </a>
                        <a href="../products/" class="quick-action-btn">
                            <div class="quick-action-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="quick-action-text">
                                <h4>Manage Products</h4>
                                <p>Update product inventory</p>
                            </div>
                        </a>
                        <a href="../orders/?status=pending" class="quick-action-btn">
                            <div class="quick-action-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="quick-action-text">
                                <h4>Pending Orders</h4>
                                <p>Review orders awaiting action</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Top Selling Products This Month -->
            <div class="manager-card">
                <div class="manager-card-header">
                    <h3><i class="fas fa-trophy"></i> Top Selling Products This Month</h3>
                </div>
                <div class="manager-card-content">
                    <?php if (empty($topProducts)): ?>
                        <div class="no-data">
                            <i class="fas fa-chart-bar"></i>
                            No sales data for this month
                        </div>
                    <?php else: ?>
                        <?php foreach ($topProducts as $product): ?>
                            <div class="product-item">
                                <div class="product-name">
                                    <?php echo htmlspecialchars($product['product_name']); ?>
                                    <p>NPR <?php echo number_format($product['price'], 2); ?> • Sold: <?php echo number_format($product['total_sold']); ?> units</p>
                                </div>
                                <a href="../products/edit.php?id=<?php echo $product['product_id']; ?>" class="quick-action-btn" style="padding: 0.5rem; min-width: auto;">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Content -->
        <div class="sidebar-content">
            <!-- Recent Orders -->
            <div class="manager-card">
                <div class="manager-card-header">
                    <h3><i class="fas fa-shopping-cart"></i> Recent Orders</h3>
                </div>
                <div class="manager-card-content">
                    <?php if (empty($recentOrders)): ?>
                        <div class="no-data">
                            <i class="fas fa-inbox"></i>
                            No recent orders found
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($recentOrders, 0, 5) as $order): ?>
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
                        <div style="text-align: center; padding-top: 1rem;">
                            <a href="../orders/" class="quick-action-btn" style="padding: 0.75rem 1.5rem; display: inline-flex;">
                                <i class="fas fa-arrow-right"></i>
                                <span style="margin-left: 0.5rem;">View All Orders</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="manager-card">
                <div class="manager-card-header">
                    <h3><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h3>
                </div>
                <div class="manager-card-content">
                    <?php if (empty($lowStockProductsList)): ?>
                        <div class="no-data">
                            <i class="fas fa-check-circle"></i>
                            All products are well stocked
                        </div>
                    <?php else: ?>
                        <?php foreach ($lowStockProductsList as $product): ?>
                            <div class="product-item">
                                <div class="product-name">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </div>
                                <div class="stock-level <?php echo $product['stock_quantity'] <= 5 ? 'stock-critical' : ($product['stock_quantity'] <= 10 ? 'stock-low' : 'stock-good'); ?>">
                                    <?php echo $product['stock_quantity']; ?> left
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div style="text-align: center; padding-top: 1rem;">
                            <a href="../products/?stock=low" class="quick-action-btn" style="padding: 0.75rem 1.5rem; display: inline-flex;">
                                <i class="fas fa-arrow-right"></i>
                                <span style="margin-left: 0.5rem;">Manage Stock</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth animations to cards
    const cards = document.querySelectorAll('.stat-card, .manager-card, .quick-action-btn');
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

<?php include_once '../shared/footer.php'; ?>

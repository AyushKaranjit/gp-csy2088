<?php
/**
 * Admin Analytics Dashboard
 * Shows search analytics and product view statistics
 */

require_once '../../config/database.php';
require_once '../../src/Controllers/AuthController.php';
require_once '../../src/Services/SearchTracker.php';
require_once '../../src/Services/ProductViewTracker.php';

// Check admin access
$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$searchTracker = new SearchTracker();
$viewTracker = new ProductViewTracker();

// Get analytics data
$popularSearches = $searchTracker->getPopularSearches(20, 30);
$trendingProducts = $viewTracker->getTrendingProducts(15, 'weekly');

$currentUser = $auth->getCurrentUser();
$page_title = 'Analytics Dashboard | Admin';
$current_page = 'analytics';

// Unified theme: storefront header + admin navigation
$ADMIN_UI = true; // suppress storefront header
$additional_css = $additional_css ?? [];
$additional_css[] = '/css/admin.css';
include '../../template/header.php';
include '../../template/admin-nav.php';
?>

<style>
/* Analytics Dashboard Specific Styles */
.analytics-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.analytics-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    text-align: center;
}

.analytics-header h1 {
    margin: 0;
    font-size: 2.5rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.analytics-header p {
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.analytics-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.analytics-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.analytics-card h3 {
    margin: 0 0 1.5rem 0;
    color: #1e293b;
    font-size: 1.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 3px solid #16a34a;
    padding-bottom: 0.75rem;
}

.search-item, .product-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #f1f5f9;
    transition: background-color 0.2s ease;
}

.search-item:hover, .product-item:hover {
    background: #f8fafc;
    margin: 0 -1rem;
    padding: 1rem;
    border-radius: 8px;
}

.search-item:last-child, .product-item:last-child {
    border-bottom: none;
}

.search-query {
    font-weight: 600;
    color: #1e293b;
    font-size: 1rem;
}

.search-stats {
    display: flex;
    gap: 1.5rem;
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 500;
}

.product-name {
    font-weight: 600;
    color: #1e293b;
    max-width: 300px;
    font-size: 1rem;
}

.product-stats {
    display: flex;
    gap: 1.5rem;
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 500;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

.empty-state i {
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.stat-badge {
    background: #16a34a;
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
    text-decoration: none;
    color: white;
}

.btn-secondary {
    background: #64748b;
    color: white;
}

.btn-secondary:hover {
    background: #475569;
    transform: translateY(-1px);
    text-decoration: none;
    color: white;
}

.btn-info {
    background: #0ea5e9;
    color: white;
}

.btn-info:hover {
    background: #0284c7;
    transform: translateY(-1px);
    text-decoration: none;
    color: white;
}

@media (max-width: 768px) {
    .analytics-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .search-item, .product-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>

<div class="analytics-container">
    <!-- Analytics Header -->
    <div class="analytics-header">
        <div>
            <h1>
                <i class="fas fa-chart-line"></i>
                Analytics Dashboard
            </h1>
            <p>Search analytics and product view statistics for DOKO</p>
        </div>
    </div>
    
    <div class="analytics-grid">
        <!-- Popular Searches -->
        <div class="analytics-card">
            <h3><i class="fas fa-search"></i> Popular Searches (Last 30 Days)</h3>
            
            <?php if (empty($popularSearches)): ?>
                <div class="empty-state">
                    <i class="fas fa-chart-line fa-2x"></i>
                    <p>No search data available yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($popularSearches as $search): ?>
                    <div class="search-item">
                        <div class="search-query"><?php echo htmlspecialchars($search['query']); ?></div>
                        <div class="search-stats">
                            <span class="stat-badge"><?php echo number_format($search['total_searches']); ?> searches</span>
                                <span><?php echo number_format($search['avg_results'], 1); ?> avg results</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Trending Products -->
            <div class="analytics-card">
                <h3><i class="fas fa-trending-up"></i> Trending Products (This Week)</h3>
                
                <?php if (empty($trendingProducts)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box fa-2x"></i>
                        <p>No product view data available yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($trendingProducts as $product): ?>
                        <div class="product-item">
                            <div>
                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div style="font-size: 0.9rem; color: #666;">
                                    <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                </div>
                            </div>
                            <div class="product-stats">
                                <span class="stat-badge"><?php echo number_format($product['views']); ?> views</span>
                                <span>Rs. <?php echo number_format($product['price'], 2); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="analytics-card">
            <h3><i class="fas fa-tools"></i> Quick Actions</h3>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="../products/" class="btn btn-primary">
                    <i class="fas fa-box"></i> Manage Products
                </a>
                <a href="../orders/" class="btn btn-secondary">
                    <i class="fas fa-shopping-cart"></i> View Orders
                </a>
                <a href="../users/" class="btn btn-info">
                    <i class="fas fa-users"></i> Manage Users
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../../template/footer.php'; ?>

<?php
/**
 * Manager Dashboard
 * Inventory management and basic admin functions for managers
 */
session_start();
require_once '../template/config.php';
require_once '../src/Controllers/AuthController.php';

$auth = new AuthController();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode('manager-dashboard.php'));
    exit;
}

// Check if user is manager
if (!in_array($_SESSION['role'], ['manager', 'admin'])) {
    header('Location: index.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$page_title = 'Manager Dashboard - DOKO';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="manager-body">
    <!-- Manager Navigation -->
    <nav class="manager-nav">
        <div class="manager-nav-brand">
            <h1>🛒 DOKO Manager</h1>
        </div>
        <div class="manager-nav-menu">
            <a href="#dashboard" class="nav-link active" data-section="dashboard">
                <i class="fas fa-chart-dashboard"></i> Dashboard
            </a>
            <a href="#products" class="nav-link" data-section="products">
                <i class="fas fa-box"></i> Products
            </a>
            <a href="#inventory" class="nav-link" data-section="inventory">
                <i class="fas fa-warehouse"></i> Inventory
            </a>
            <a href="#orders" class="nav-link" data-section="orders">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
            <a href="#reports" class="nav-link" data-section="reports">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
        </div>
        <div class="manager-nav-user">
            <span>Welcome, <?php echo htmlspecialchars($currentUser['first_name'] ?? $currentUser['username']); ?> (Manager)</span>
            <a href="?logout=1" class="btn btn-outline btn-sm">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main Manager Content -->
    <main class="manager-main">
        <!-- Dashboard Section -->
        <section id="dashboard" class="manager-section active">
            <div class="manager-header">
                <h2>Manager Dashboard</h2>
                <p>Inventory and product management overview</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="total-products">0</h3>
                        <p>Total Products</p>
                        <small class="stat-change" id="products-change">Loading...</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="low-stock">0</h3>
                        <p>Low Stock Items</p>
                        <small class="stat-change negative" id="low-stock-change">Requires attention</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="pending-orders">0</h3>
                        <p>Pending Orders</p>
                        <small class="stat-change" id="orders-change">Loading...</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="daily-sales">Rs. 0</h3>
                        <p>Today's Sales</p>
                        <small class="stat-change positive" id="sales-change">Loading...</small>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="manager-grid">
                <div class="manager-card">
                    <h3>Quick Actions</h3>
                    <div class="quick-actions">
                        <button class="action-btn" onclick="showAddProductModal()">
                            <i class="fas fa-plus"></i>
                            Add Product
                        </button>
                        <button class="action-btn" onclick="updateInventory()">
                            <i class="fas fa-sync"></i>
                            Update Inventory
                        </button>
                        <button class="action-btn" onclick="viewReports()">
                            <i class="fas fa-chart-bar"></i>
                            View Reports
                        </button>
                        <button class="action-btn" onclick="exportData()">
                            <i class="fas fa-download"></i>
                            Export Data
                        </button>
                    </div>
                </div>
                
                <div class="manager-card">
                    <h3>Low Stock Alert</h3>
                    <div id="low-stock-items">
                        <p class="loading">Loading low stock items...</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section id="products" class="manager-section">
            <div class="manager-header">
                <h2>Product Management</h2>
                <button class="btn btn-primary" onclick="showAddProductModal()">
                    <i class="fas fa-plus"></i> Add New Product
                </button>
            </div>
            
            <div class="manager-card">
                <div class="card-header">
                    <h3>All Products</h3>
                    <div class="search-filters">
                        <input type="search" placeholder="Search products..." class="search-input" id="product-search" name="product_search" autocomplete="off">
                        <select class="filter-select" id="category-filter">
                            <option value="">All Categories</option>
                            <option value="1">Fruits & Vegetables</option>
                            <option value="2">Dairy & Eggs</option>
                            <option value="3">Meat & Seafood</option>
                            <option value="4">Bakery</option>
                            <option value="5">Pantry Staples</option>
                        </select>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="manager-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="products-tbody">
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">
                                    <i class="fas fa-spinner fa-spin"></i> Loading products...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Inventory Section -->
        <section id="inventory" class="manager-section">
            <div class="manager-header">
                <h2>Inventory Management</h2>
                <button class="btn btn-secondary" onclick="refreshInventory()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
            
            <div class="manager-card">
                <div class="inventory-summary">
                    <div class="inventory-stat">
                        <h4>Total Items</h4>
                        <span id="inventory-total">0</span>
                    </div>
                    <div class="inventory-stat">
                        <h4>Low Stock</h4>
                        <span id="inventory-low" class="warning">0</span>
                    </div>
                    <div class="inventory-stat">
                        <h4>Out of Stock</h4>
                        <span id="inventory-out" class="danger">0</span>
                    </div>
                </div>
                
                <div class="inventory-list" id="inventory-list">
                    <p class="loading">Loading inventory...</p>
                </div>
            </div>
        </section>
    </main>

    <style>
    .manager-body {
        font-family: 'Inter', sans-serif;
        background: #f8f9fa;
        margin: 0;
        padding: 0;
        min-height: 100vh;
    }

    .manager-nav {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .manager-nav-brand h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .manager-nav-menu {
        display: flex;
        gap: 2rem;
    }

    .manager-nav .nav-link {
        color: rgba(255,255,255,0.9);
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .manager-nav .nav-link:hover,
    .manager-nav .nav-link.active {
        background: rgba(255,255,255,0.2);
        color: white;
    }

    .manager-nav-user {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .manager-main {
        padding: 2rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .manager-section {
        display: none;
    }

    .manager-section.active {
        display: block;
    }

    .manager-header {
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .manager-header h2 {
        margin: 0;
        font-weight: 600;
        color: #495057;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 1rem;
        border-left: 4px solid #28a745;
    }

    .stat-card:nth-child(2) {
        border-left-color: #ffc107;
    }

    .stat-card:nth-child(3) {
        border-left-color: #007bff;
    }

    .stat-card:nth-child(4) {
        border-left-color: #17a2b8;
    }

    .stat-icon {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 10px;
        color: #28a745;
    }

    .stat-icon i {
        font-size: 2rem;
    }

    .stat-info h3 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
        color: #495057;
    }

    .stat-info p {
        margin: 0.5rem 0;
        color: #6c757d;
        font-weight: 500;
    }

    .stat-change {
        font-size: 0.85rem;
    }

    .stat-change.positive {
        color: #28a745;
    }

    .stat-change.negative {
        color: #dc3545;
    }

    .manager-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .manager-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }

    .manager-card h3 {
        margin: 0 0 1.5rem;
        font-weight: 600;
        color: #495057;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .action-btn {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        padding: 1rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        font-weight: 500;
    }

    .action-btn:hover {
        background: #28a745;
        color: white;
        border-color: #28a745;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40,167,69,0.3);
    }

    .action-btn i {
        display: block;
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .manager-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .manager-table th,
    .manager-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }

    .manager-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #495057;
    }

    .manager-table tbody tr:hover {
        background: #f8f9fa;
    }

    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #28a745;
        color: white;
    }

    .btn-primary:hover {
        background: #218838;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-outline {
        background: transparent;
        color: white;
        border: 1px solid rgba(255,255,255,0.5);
    }

    .btn-outline:hover {
        background: rgba(255,255,255,0.1);
    }

    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.875rem;
    }

    .search-filters {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .search-input, .filter-select {
        padding: 0.5rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .inventory-summary {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .inventory-stat {
        text-align: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .inventory-stat h4 {
        margin: 0 0 0.5rem;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .inventory-stat span {
        font-size: 2rem;
        font-weight: 700;
        color: #495057;
    }

    .inventory-stat .warning {
        color: #ffc107;
    }

    .inventory-stat .danger {
        color: #dc3545;
    }

    .loading {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        padding: 2rem;
    }

    @media (max-width: 768px) {
        .manager-nav {
            flex-direction: column;
            gap: 1rem;
            padding: 1rem;
        }

        .manager-nav-menu {
            gap: 1rem;
            flex-wrap: wrap;
        }

        .manager-main {
            padding: 1rem;
        }

        .manager-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .manager-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>

    <script>
    // Manager API class
    class ManagerAPI {
        static async call(endpoint, options = {}) {
            try {
                const response = await fetch(`/api/${endpoint}`, {
                    method: options.method || 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...options.headers
                    },
                    body: options.body ? JSON.stringify(options.body) : undefined
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                }
                
                return data;
            } catch (error) {
                console.error('Manager API Error:', error);
                throw error;
            }
        }
    }

    // Navigation functionality
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links and sections
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            document.querySelectorAll('.manager-section').forEach(s => s.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Show corresponding section
            const sectionId = this.getAttribute('data-section');
            document.getElementById(sectionId).classList.add('active');
            
            // Load section data
            loadSectionData(sectionId);
        });
    });

    // Load section-specific data
    async function loadSectionData(sectionId) {
        switch (sectionId) {
            case 'dashboard':
                await loadDashboardData();
                break;
            case 'products':
                await loadProductsData();
                break;
            case 'inventory':
                await loadInventoryData();
                break;
        }
    }

    // Load dashboard data
    async function loadDashboardData() {
        try {
            const response = await ManagerAPI.call('admin-dashboard.php');
            if (response.success) {
                updateDashboardStats(response.data.stats);
                updateLowStockItems(response.data.low_stock_products);
            }
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
        }
    }

    // Update dashboard statistics
    function updateDashboardStats(stats) {
        document.getElementById('total-products').textContent = stats.total_products || 0;
        document.getElementById('low-stock').textContent = stats.low_stock_alert || 0;
        document.getElementById('pending-orders').textContent = '0'; // Mock for now
        document.getElementById('daily-sales').textContent = 'Rs. 0'; // Mock for now
    }

    // Update low stock items display
    function updateLowStockItems(items) {
        const container = document.getElementById('low-stock-items');
        if (!items || items.length === 0) {
            container.innerHTML = '<p class="no-data">No low stock items</p>';
            return;
        }

        const html = items.map(item => `
            <div class="low-stock-item">
                <strong>${item.name}</strong>
                <span class="stock-level">${item.stock} ${item.unit}</span>
            </div>
        `).join('');
        
        container.innerHTML = html;
    }

    // Load products data
    async function loadProductsData() {
        try {
            const response = await ManagerAPI.call('admin-products.php');
            if (response.success) {
                updateProductsTable(response.data);
            }
        } catch (error) {
            console.error('Failed to load products data:', error);
        }
    }

    // Update products table
    function updateProductsTable(products) {
        const tbody = document.getElementById('products-tbody');
        if (!tbody) return;
        
        if (!products || products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="no-data">No products found</td></tr>';
            return;
        }
        
        const html = products.map(product => `
            <tr>
                <td>
                    <div class="product-cell">
                        <img src="${product.image_url}" alt="${product.name}" 
                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 0.5rem;">
                        <div>
                            <strong>${product.name}</strong>
                            <br><small>${product.unit}</small>
                        </div>
                    </div>
                </td>
                <td>${product.category_name || 'N/A'}</td>
                <td>Rs. ${product.price}</td>
                <td class="${product.stock <= 10 ? 'warning' : ''}">${product.stock} units</td>
                <td><span class="status ${product.is_active ? 'active' : 'inactive'}">${product.is_active ? 'Active' : 'Inactive'}</span></td>
                <td>
                    <button class="btn-icon" onclick="editProduct(${product.product_id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" onclick="updateStock(${product.product_id})" title="Update Stock">
                        <i class="fas fa-boxes"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        tbody.innerHTML = html;
    }

    // Manager functions
    function showAddProductModal() {
        alert('Add product modal - Manager functionality');
    }

    function editProduct(productId) {
        alert(`Edit product ${productId} - Manager functionality`);
    }

    function updateStock(productId) {
        alert(`Update stock for product ${productId} - Manager functionality`);
    }

    function updateInventory() {
        alert('Update inventory - Manager functionality');
    }

    function viewReports() {
        alert('View reports - Manager functionality');
    }

    function exportData() {
        alert('Export data - Manager functionality');
    }

    function refreshInventory() {
        loadInventoryData();
    }

    async function loadInventoryData() {
        try {
            const response = await fetch('api/products-list.php');
            const result = await response.json();
            
            if (result.success && result.data) {
                const products = result.data;
                
                // Calculate inventory stats
                const totalProducts = products.length;
                const lowStock = products.filter(p => p.stock_quantity <= 10).length;
                const outOfStock = products.filter(p => p.stock_quantity === 0).length;
                
                // Update inventory stats
                document.getElementById('inventory-total').textContent = totalProducts;
                document.getElementById('inventory-low').textContent = lowStock;
                document.getElementById('inventory-out').textContent = outOfStock;
                
                // Update inventory list
                const inventoryList = document.getElementById('inventory-list');
                inventoryList.innerHTML = '';
                
                if (products.length === 0) {
                    inventoryList.innerHTML = '<p class="empty-state">No products found</p>';
                    return;
                }
                
                // Show low stock and out of stock items first
                const criticalItems = products.filter(p => p.stock_quantity <= 10);
                
                criticalItems.forEach(product => {
                    const item = document.createElement('div');
                    item.className = `inventory-item ${product.stock_quantity === 0 ? 'out-of-stock' : 'low-stock'}`;
                    item.innerHTML = `
                        <div class="item-info">
                            <h4>${product.name}</h4>
                            <p>Stock: ${product.stock_quantity} units</p>
                            <p>Price: Rs. ${product.price}</p>
                        </div>
                        <div class="item-actions">
                            <button class="btn btn-sm btn-primary" onclick="updateStock(${product.product_id})">
                                <i class="fas fa-plus"></i> Update Stock
                            </button>
                        </div>
                    `;
                    inventoryList.appendChild(item);
                });
                
                if (criticalItems.length === 0) {
                    inventoryList.innerHTML = '<p class="success-state">All products have sufficient stock!</p>';
                }
                
            } else {
                document.getElementById('inventory-list').innerHTML = '<p class="error-state">Failed to load inventory data</p>';
            }
        } catch (error) {
            console.error('Inventory data error:', error);
            document.getElementById('inventory-list').innerHTML = '<p class="error-state">Error loading inventory data</p>';
        }
    }

    // Initialize manager dashboard
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboardData();
    });
    </script>

    <?php
    if (isset($_GET['logout'])) {
        $auth->logout();
        header('Location: login.php');
        exit;
    }
    ?>
</body>
</html>

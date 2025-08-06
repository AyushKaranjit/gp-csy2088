<?php
// Start session and include configuration
session_start();
require_once '../template/config.php';
require_once '../src/Controllers/AuthController.php';

// Check if user is logged in and has manager or admin privileges
$auth = new AuthController();

if (!$auth->isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode('manager.php'));
    exit;
}

if (!$auth->hasManagerAccess()) {
    http_response_code(403);
    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Access Denied - DOKO Manager</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; text-align: center; padding: 50px; }
        .error { color: #dc3545; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1 class='error'>Access Denied</h1>
    <p>You don't have permission to access the manager panel.</p>
    <a href='index.php' class='btn'>Go to Homepage</a>
</body>
</html>";
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    $auth->logout();
    header('Location: login.php');
    exit;
}

// Get current user info
$currentUser = $auth->getCurrentUser();
$userRole = $auth->getUserRole();

// Manager dashboard content
$page_title = 'DOKO Manager Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="manager-body">
    <!-- Manager Navigation -->
    <nav class="manager-nav">
        <div class="manager-nav-brand">
            <h1>📊 DOKO Manager</h1>
        </div>
        <div class="manager-nav-menu">
            <a href="#dashboard" class="nav-link active" data-section="dashboard">
                <i class="fas fa-chart-dashboard"></i> Dashboard
            </a>
            <a href="#products" class="nav-link" data-section="products">
                <i class="fas fa-box"></i> Products
            </a>
                        <input type="text" id="product-name" name="product_name" class="form-control" autocomplete="off" required>
                        <input type="number" id="product-price" name="product_price" class="form-control" autocomplete="off" required>
                        <input type="number" id="product-stock" name="product_stock" class="form-control" autocomplete="off" required>
                        <input type="text" id="product-category" name="product_category" class="form-control" autocomplete="off" required>
                        <input type="text" id="product-brand" name="product_brand" class="form-control" autocomplete="off" required>
                        <input type="file" id="product-image" name="product_image" class="form-control" autocomplete="off">
            <a href="#reports" class="nav-link" data-section="reports">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <!-- NOTE: No Users/Settings sections - that's ADMIN ONLY -->
        </div>
        <div class="manager-nav-user">
            <span class="user-role-badge manager">Manager</span>
            <span>Welcome, <?php echo htmlspecialchars($currentUser['first_name'] ?? $currentUser['username']); ?></span>
            <small style="color: #666; font-size: 0.875rem;">Limited Access - No User Management</small>
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
                <p>Store management and operations overview - Ayush Manager Access</p>
                
                <!-- Permission Notice -->
                <div class="permission-notice" style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 1rem; margin: 1rem 0; color: #856404;">
                    <i class="fas fa-info-circle"></i>
                    <strong>Manager Permissions:</strong> You can manage products, inventory, and orders. 
                    User management and system settings are restricted to Admin only.
                </div>
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
                        <small class="stat-change">Active inventory</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="low-stock-items">0</h3>
                        <p>Low Stock Items</p>
                        <small class="stat-change negative">Need attention</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="featured-products">0</h3>
                        <p>Featured Products</p>
                        <small class="stat-change positive">Promoted items</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="categories-count">0</h3>
                        <p>Categories</p>
                        <small class="stat-change">Product categories</small>
                    </div>
                </div>
            </div>
            
            <!-- Manager-specific content -->
            <div class="manager-grid">
                <div class="manager-card">
                    <h3>Inventory Management</h3>
                    <div class="quick-actions">
                        <button class="quick-action-btn" onclick="loadLowStockProducts()">
                            <i class="fas fa-exclamation-triangle"></i>
                            View Low Stock
                        </button>
                        <button class="quick-action-btn" onclick="showSection('products')">
                            <i class="fas fa-plus"></i>
                            Add Product
                        </button>
                        <button class="quick-action-btn" onclick="showSection('inventory')">
                            <i class="fas fa-warehouse"></i>
                            Stock Update
                        </button>
                    </div>
                </div>
                
                <div class="manager-card">
                    <h3>Recent Activity</h3>
                    <div id="recent-activity">
                        <div class="activity-item">
                            <i class="fas fa-plus-circle text-success"></i>
                            <span>New products can be added from Products section</span>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-edit text-info"></i>
                            <span>Stock levels can be updated in Inventory section</span>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-chart-bar text-primary"></i>
                            <span>View detailed reports in Reports section</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Section (Limited compared to admin) -->
        <section id="products" class="manager-section">
            <div class="manager-header">
                <h2>Product Management</h2>
                <button class="btn btn-primary" onclick="showAddProductModal()">
                    <i class="fas fa-plus"></i> Add New Product
                </button>
            </div>
            
            <div class="manager-card">
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
                        <tbody id="products-table-body">
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
                <p>Monitor and update stock levels</p>
            </div>
            
            <div class="manager-card">
                <h3>Low Stock Alert</h3>
                <div id="low-stock-products">
                    <p>Loading low stock items...</p>
                </div>
            </div>
        </section>

        <!-- Orders Section -->
        <section id="orders" class="manager-section">
            <div class="manager-header">
                <h2>Order Management</h2>
                <p>View and manage customer orders</p>
            </div>
            
            <div class="manager-card">
                <div class="table-responsive">
                    <table class="manager-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">
                                    <i class="fas fa-info-circle"></i> No orders available yet
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Reports Section -->
        <section id="reports" class="manager-section">
            <div class="manager-header">
                <h2>Reports & Analytics</h2>
                <p>Store performance and insights</p>
            </div>
            
            <div class="manager-grid">
                <div class="manager-card">
                    <h3>Product Performance</h3>
                    <p>Track which products are performing well</p>
                    <button class="btn btn-outline">Generate Report</button>
                </div>
                
                <div class="manager-card">
                    <h3>Stock Movement</h3>
                    <p>Monitor inventory changes over time</p>
                    <button class="btn btn-outline">View Movement</button>
                </div>
            </div>
        </section>
    </main>

    <style>
    /* Manager-specific styling */
    .manager-body {
        font-family: 'Inter', sans-serif;
        background: #f8fafc;
        margin: 0;
        padding: 0;
    }

    .manager-nav {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        font-weight: 600;
    }

    .manager-nav-menu {
        display: flex;
        gap: 2rem;
    }

    .manager-nav-menu .nav-link {
        color: rgba(255,255,255,0.9);
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .manager-nav-menu .nav-link:hover,
    .manager-nav-menu .nav-link.active {
        background: rgba(255,255,255,0.2);
        color: white;
    }

    .manager-nav-user {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .user-role-badge {
        background: rgba(255,255,255,0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
    }

    .user-role-badge.manager {
        background: #10b981;
    }

    .manager-main {
        padding: 2rem;
        max-width: 1400px;
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
    }

    .manager-header h2 {
        margin: 0 0 0.5rem 0;
        font-size: 2rem;
        font-weight: 600;
        color: #1a202c;
    }

    .manager-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }

    .manager-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .quick-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .quick-action-btn {
        background: #f7fafc;
        border: 2px solid #e2e8f0;
        padding: 1rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }

    .quick-action-btn:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .text-success { color: #10b981; }
    .text-info { color: #3b82f6; }
    .text-primary { color: #667eea; }

    .stat-icon.warning { background: #fbbf24; }
    .stat-icon.success { background: #10b981; }
    .stat-icon.info { background: #3b82f6; }

    /* Copy admin table styles for manager tables */
    .manager-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .manager-table th,
    .manager-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }

    .manager-table th {
        background: #f8fafc;
        font-weight: 600;
        color: #374151;
    }

    .table-responsive {
        overflow-x: auto;
    }
    </style>

    <!-- Manager JavaScript -->
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
                this.showNotification(error.message, 'error');
                throw error;
            }
        }
        
        static showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px; z-index: 9999;
                padding: 12px 24px; border-radius: 4px; color: white;
                background: ${type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#007bff'};
                animation: slideIn 0.3s ease-out;
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
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
                await loadManagerDashboard();
                break;
            case 'products':
                await loadProductsData();
                break;
            case 'inventory':
                await loadInventoryData();
                break;
        }
    }

    // Show specific section (utility function)
    function showSection(sectionId) {
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        document.querySelectorAll('.manager-section').forEach(s => s.classList.remove('active'));
        
        document.querySelector(`[data-section="${sectionId}"]`).classList.add('active');
        document.getElementById(sectionId).classList.add('active');
        
        loadSectionData(sectionId);
    }

    // Load manager dashboard data
    async function loadManagerDashboard() {
        try {
            const response = await ManagerAPI.call('admin-dashboard.php');
            if (response.success) {
                const stats = response.data.stats;
                
                document.getElementById('total-products').textContent = stats.total_products || 0;
                document.getElementById('low-stock-items').textContent = stats.low_stock_alert || 0;
                document.getElementById('featured-products').textContent = stats.featured_products || 0;
                document.getElementById('categories-count').textContent = stats.total_categories || 0;
            }
        } catch (error) {
            console.error('Failed to load manager dashboard:', error);
        }
    }

    // Load products data (limited manager view)
    async function loadProductsData() {
        try {
            const response = await ManagerAPI.call('admin-products.php');
            if (response.success) {
                updateProductsTable(response.data);
            }
        } catch (error) {
            console.error('Failed to load products:', error);
        }
    }

    // Update products table
    function updateProductsTable(products) {
        const tbody = document.getElementById('products-table-body');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        products.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><strong>${product.name}</strong></td>
                <td>${product.category_name || 'N/A'}</td>
                <td>Rs. ${product.price}</td>
                <td>${product.stock} ${product.unit}</td>
                <td><span class="status ${product.is_active ? 'active' : 'inactive'}">${product.is_active ? 'Active' : 'Inactive'}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline" onclick="editProduct(${product.product_id})">Edit</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // Load inventory data
    async function loadInventoryData() {
        try {
            const response = await ManagerAPI.call('admin-dashboard.php');
            if (response.success && response.data.low_stock_products) {
                const lowStockDiv = document.getElementById('low-stock-products');
                const products = response.data.low_stock_products;
                
                if (products.length === 0) {
                    lowStockDiv.innerHTML = '<p>✅ All products are well stocked!</p>';
                    return;
                }
                
                lowStockDiv.innerHTML = products.map(product => `
                    <div class="low-stock-item">
                        <strong>${product.name}</strong> - Only ${product.stock} ${product.unit} left
                        <button class="btn btn-sm btn-primary" onclick="updateStock(${product.product_id})">Update Stock</button>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Failed to load inventory:', error);
        }
    }

    // Manager functions
    function showAddProductModal() {
        ManagerAPI.showNotification('Add Product functionality - Contact admin for implementation', 'info');
    }

    function editProduct(productId) {
        // Create edit product modal
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.id = 'editProductModal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Product</h3>
                    <button type="button" class="close-btn" onclick="hideEditProductModal()">&times;</button>
                </div>
                <form id="editProductForm" class="modal-body">
                    <input type="hidden" id="edit-product-id" name="product_id">
                    <div class="form-group">
                        <label for="edit-product-name">Product Name *</label>
                        <input type="text" id="edit-product-name" name="name" required class="form-control">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-product-price">Price (Rs.) *</label>
                            <input type="number" id="edit-product-price" name="price" step="0.01" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit-product-stock">Stock Quantity *</label>
                            <input type="number" id="edit-product-stock" name="stock_quantity" required class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-product-description">Description</label>
                        <textarea id="edit-product-description" name="description" rows="3" class="form-control"></textarea>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideEditProductModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitEditProduct()">Update Product</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Load product data
        loadProductData(productId);
        modal.style.display = 'flex';
    }

    function hideEditProductModal() {
        const modal = document.getElementById('editProductModal');
        if (modal) {
            modal.remove();
        }
    }

    async function loadProductData(productId) {
        try {
            const response = await ManagerAPI.call(`api/product-detail.php?id=${productId}`);
            if (response.success && response.data) {
                const product = response.data;
                document.getElementById('edit-product-id').value = product.product_id;
                document.getElementById('edit-product-name').value = product.name || '';
                document.getElementById('edit-product-price').value = product.price || '';
                document.getElementById('edit-product-stock').value = product.stock_quantity || '';
                document.getElementById('edit-product-description').value = product.description || '';
            }
        } catch (error) {
            console.error('Load product data error:', error);
        }
    }

    async function submitEditProduct() {
        const form = document.getElementById('editProductForm');
        const formData = new FormData(form);
        
        const productData = {
            product_id: formData.get('product_id'),
            name: formData.get('name'),
            price: formData.get('price'),
            stock_quantity: formData.get('stock_quantity'),
            description: formData.get('description')
        };

        if (!productData.name || !productData.price || !productData.stock_quantity) {
            ManagerAPI.showNotification('Please fill in all required fields', 'error');
            return;
        }

        try {
            ManagerAPI.showNotification('Updating product...', 'info');
            
            // For managers, we'll use a limited product update API
            const response = await fetch('api/manager-update-product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(productData)
            });

            const result = await response.json();

            if (result.success) {
                ManagerAPI.showNotification('Product updated successfully!', 'success');
                hideEditProductModal();
                loadManagerData();
            } else {
                ManagerAPI.showNotification(result.message || 'Failed to update product', 'error');
            }
        } catch (error) {
            console.error('Update product error:', error);
            ManagerAPI.showNotification('Failed to update product', 'error');
        }
    }

    function updateStock(productId) {
        // Create stock update modal
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.id = 'updateStockModal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Update Stock</h3>
                    <button type="button" class="close-btn" onclick="hideUpdateStockModal()">&times;</button>
                </div>
                <form id="updateStockForm" class="modal-body">
                    <input type="hidden" id="stock-product-id" name="product_id" value="${productId}">
                    <div class="form-group">
                        <label for="current-stock">Current Stock</label>
                        <input type="number" id="current-stock" readonly class="form-control" style="background: #f5f5f5;">
                    </div>
                    <div class="form-group">
                        <label for="new-stock">New Stock Quantity *</label>
                        <input type="number" id="new-stock" name="stock_quantity" required class="form-control" min="0">
                    </div>
                    <div class="form-group">
                        <label for="stock-reason">Reason for Change</label>
                        <select id="stock-reason" name="reason" class="form-control">
                            <option value="restock">Restock</option>
                            <option value="correction">Inventory Correction</option>
                            <option value="damaged">Damaged Items</option>
                            <option value="returned">Returned Items</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="stock-notes">Notes</label>
                        <textarea id="stock-notes" name="notes" rows="2" class="form-control" placeholder="Optional notes"></textarea>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideUpdateStockModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitStockUpdate()">Update Stock</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Load current stock
        loadCurrentStock(productId);
        modal.style.display = 'flex';
    }

    function hideUpdateStockModal() {
        const modal = document.getElementById('updateStockModal');
        if (modal) {
            modal.remove();
        }
    }

    async function loadCurrentStock(productId) {
        try {
            const response = await ManagerAPI.call(`api/product-detail.php?id=${productId}`);
            if (response.success && response.data) {
                document.getElementById('current-stock').value = response.data.stock_quantity || 0;
                document.getElementById('new-stock').value = response.data.stock_quantity || 0;
            }
        } catch (error) {
            console.error('Load current stock error:', error);
        }
    }

    async function submitStockUpdate() {
        const form = document.getElementById('updateStockForm');
        const formData = new FormData(form);
        
        const stockData = {
            product_id: formData.get('product_id'),
            stock_quantity: formData.get('stock_quantity'),
            reason: formData.get('reason'),
            notes: formData.get('notes')
        };

        if (!stockData.stock_quantity) {
            ManagerAPI.showNotification('Please enter stock quantity', 'error');
            return;
        }

        try {
            ManagerAPI.showNotification('Updating stock...', 'info');
            
            const response = await fetch('api/manager-update-stock.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(stockData)
            });

            const result = await response.json();

            if (result.success) {
                ManagerAPI.showNotification('Stock updated successfully!', 'success');
                hideUpdateStockModal();
                loadManagerData();
            } else {
                ManagerAPI.showNotification(result.message || 'Failed to update stock', 'error');
            }
        } catch (error) {
            console.error('Update stock error:', error);
            ManagerAPI.showNotification('Failed to update stock', 'error');
        }
    }

    function loadLowStockProducts() {
        showSection('inventory');
    }

    // Initialize manager dashboard
    document.addEventListener('DOMContentLoaded', function() {
        loadManagerDashboard();
        ManagerAPI.showNotification('DOKO Manager Dashboard loaded!', 'success');
    });

    // CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .notification { animation: slideIn 0.3s ease-out; }
        .low-stock-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
        }
        .status { 
            padding: 0.25rem 0.75rem; 
            border-radius: 12px; 
            font-size: 0.75rem; 
            font-weight: 500; 
        }
        .status.active { background: #dcfce7; color: #166534; }
        .status.inactive { background: #fee2e2; color: #991b1b; }
        .btn { 
            padding: 0.5rem 1rem; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 500; 
            transition: all 0.3s;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-outline { background: transparent; border: 1px solid #d1d5db; }
        .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.875rem; }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>

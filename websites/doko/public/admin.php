<?php
// Start session and include configuration
session_start();
require_once '../template/config.php';
require_once '../src/Controllers/AuthController.php';

// Check if user is logged in and has admin privileges
$auth = new AuthController();

if (!$auth->isLoggedIn()) {
    // Redirect to login page
    header('Location: login.php?redirect=' . urlencode('admin.php'));
    exit;
}

if (!$auth->isAdmin()) {
    // Show access denied message
    http_response_code(403);
    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Access Denied - DOKO Admin</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .error { color: #dc3545; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1 class='error'>Access Denied</h1>
    <p>You don't have permission to access the admin panel.</p>
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

// Admin dashboard content
$page_title = 'DOKO Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="DOKO Admin Dashboard - Manage your e-commerce store">
</head>
<body class="admin-body">
    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="admin-nav-brand">
            <div class="logo admin-logo">
                <a href="index.php" style="display: flex; align-items: center; gap: 15px; text-decoration: none;">
                    <div class="logo-icon doko-basket-icon">
                        <div class="basket-container">
                            <div class="basket-body">
                                <div class="weave-pattern"></div>
                                <div class="weave-pattern"></div>
                                <div class="weave-pattern"></div>
                            </div>
                            <div class="basket-handles">
                                <div class="handle-left"></div>
                                <div class="handle-right"></div>
                            </div>
                        </div>
                    </div>
                    <div class="logo-text admin-logo-text">
                        <span class="logo-main">DOKO</span>
                        <span class="logo-tagline">Admin Dashboard</span>
                    </div>
                </a>
            </div>
        </div>
        <div class="admin-nav-menu">
            <a href="#dashboard" class="nav-link active" data-section="dashboard" aria-current="page">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="#products" class="nav-link" data-section="products">
                <i class="fas fa-box-open"></i> Products
            </a>
            <a href="#orders" class="nav-link" data-section="orders">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
            <a href="#customers" class="nav-link" data-section="customers">
                <i class="fas fa-users"></i> Customers
            </a>
            <a href="#settings" class="nav-link" data-section="settings">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
        <div class="admin-nav-user">
            <div class="user-info">
                <i class="fas fa-user-circle" style="font-size: 1.5rem; color: rgba(255,255,255,0.8);"></i>
                <span>Welcome, <?php echo htmlspecialchars($currentUser['first_name'] ?? $currentUser['username']); ?></span>
            </div>
            <a href="?logout=1" class="btn btn-outline btn-sm" onclick="return confirm('Are you sure you want to logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main Admin Content -->
    <main class="admin-main">
        <!-- Dashboard Section -->
        <section id="dashboard" class="admin-section active">
            <div class="admin-header">
                <div>
                    <h2>Dashboard Overview</h2>
                    <p>Welcome to your DOKO administration panel. Monitor your store's performance and manage operations efficiently.</p>
                </div>
                <div class="dashboard-actions">
                    <button class="btn btn-primary" onclick="loadDashboardData()">
                        <i class="fas fa-sync-alt"></i> Refresh Data
                    </button>
                </div>
            </div>
            
            <!-- Enhanced Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card" id="orders-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="total-orders">0</h3>
                        <p>Total Categories</p>
                        <small class="stat-change" id="orders-change">Loading...</small>
                    </div>
                </div>
                
                <div class="stat-card" id="users-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="total-users">0</h3>
                        <p>Registered Users</p>
                        <small class="stat-change positive" id="users-change">Loading...</small>
                    </div>
                </div>
                
                <div class="stat-card" id="products-card">
                    <div class="stat-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="total-products">0</h3>
                        <p>Total Products</p>
                        <small class="stat-change" id="products-change">Loading...</small>
                    </div>
                </div>
                
                <div class="stat-card" id="alerts-card">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="low-stock">0</h3>
                        <p>Low Stock Alerts</p>
                        <small class="stat-change negative" id="alerts-change">Requires attention</small>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity Grid -->
            <div class="admin-grid">
                <div class="admin-card">
                    <div class="card-header">
                        <h3><i class="fas fa-clock"></i> Recent Products</h3>
                        <a href="#products" class="nav-link" data-section="products" style="color: #ff6b35; font-weight: 500; text-decoration: none;">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Added</th>
                                </tr>
                            </thead>
                            <tbody id="recent-products-table">
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 2rem;">
                                        <i class="fas fa-spinner fa-spin" style="color: #64748b;"></i> Loading recent products...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="admin-card">
                    <div class="card-header">
                        <h3><i class="fas fa-star"></i> Top Products</h3>
                        <span class="badge" style="background: #dcfce7; color: #16a34a;">Featured Items</span>
                    </div>
                    <div class="top-products-list" id="top-products-list">
                        <div style="text-align: center; padding: 2rem; color: #64748b;">
                            <i class="fas fa-spinner fa-spin"></i> Loading top products...
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions Section -->
            <div class="admin-card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <div class="quick-actions-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <button class="btn btn-primary" onclick="showAddProductModal()" style="justify-self: start;">
                            <i class="fas fa-plus"></i> Add New Product
                        </button>
                        <button class="btn btn-secondary" onclick="document.querySelector('[data-section=\'customers\']').click()">
                            <i class="fas fa-user-plus"></i> View Customers
                        </button>
                        <button class="btn btn-outline" onclick="document.querySelector('[data-section=\'settings\']').click()">
                            <i class="fas fa-cog"></i> System Settings
                        </button>
                        <button class="btn btn-outline" onclick="exportData()" style="color: #16a34a; border-color: #16a34a;">
                            <i class="fas fa-download"></i> Export Data
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section id="products" class="admin-section">
            <div class="admin-header">
                <h2>Product Management</h2>
                <button class="btn btn-primary" onclick="showAddProductModal()">
                    <i class="fas fa-plus"></i> Add New Product
                </button>
            </div>
            
            <div class="admin-card">
                <div class="card-header">
                    <h3>All Products</h3>
                    <div class="search-filters">
                        <div class="search-group">
                            <label for="product-search" class="sr-only">Search products</label>
                            <input type="search" id="product-search" name="product_search" placeholder="Search products..." class="search-input" autocomplete="off">
                        </div>
                        <div class="filter-group">
                            <label for="category-filter" class="sr-only">Filter by category</label>
                            <select id="category-filter" name="category_filter" class="filter-select" autocomplete="off">
                                <option value="">All Categories</option>
                                <option value="1">Fruits & Vegetables</option>
                                <option value="2">Dairy & Eggs</option>
                                <option value="3">Meat & Seafood</option>
                                <option value="4">Bakery</option>
                                <option value="5">Pantry Staples</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="product-status-filter" class="sr-only">Filter by status</label>
                            <select id="product-status-filter" name="status_filter" class="filter-select" autocomplete="off">
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="admin-table">
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
                        <tbody>
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

        <!-- Add Product Modal -->
        <div id="addProductModal" class="modal" style="display: none;" role="dialog" aria-labelledby="addProductModalTitle">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="addProductModalTitle">Add New Product</h3>
                    <button class="close-btn" onclick="hideAddProductModal()" aria-label="Close modal">&times;</button>
                </div>
                <form id="addProductForm" class="modal-body" novalidate>

        <!-- Edit Product Modal -->
        <div id="editProductModal" class="modal" style="display: none;" role="dialog" aria-labelledby="editProductModalTitle">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="editProductModalTitle">Edit Product</h3>
                    <button class="close-btn" onclick="hideEditProductModal()" aria-label="Close modal">&times;</button>
                </div>
                <form id="editProductForm" class="modal-body" novalidate>
                    <input type="hidden" id="edit-product-id" name="product_id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-product-name">Product Name *</label>
                            <input type="text" id="edit-product-name" name="name" required class="form-control" autocomplete="name">
                        </div>
                        <div class="form-group">
                            <label for="edit-product-category">Category</label>
                            <select id="edit-product-category" name="category_id" class="form-control" autocomplete="off">
                                <option value="1">Fruits & Vegetables</option>
                                <option value="2">Dairy & Eggs</option>
                                <option value="3">Meat & Seafood</option>
                                <option value="4">Bakery</option>
                                <option value="5">Pantry Staples</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-product-price">Price (Rs.) *</label>
                            <input type="number" id="edit-product-price" name="price" required min="0" step="0.01" class="form-control" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="edit-product-original-price">Original Price (Rs.)</label>
                            <input type="number" id="edit-product-original-price" name="original_price" min="0" step="0.01" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-product-stock">Stock Quantity *</label>
                            <input type="number" id="edit-product-stock" name="stock" required min="0" class="form-control" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="edit-product-unit">Unit</label>
                            <select id="edit-product-unit" name="unit" class="form-control" autocomplete="off">
                                <option value="kg">Kilogram (kg)</option>
                                <option value="liter">Liter</option>
                                <option value="piece">Piece</option>
                                <option value="dozen">Dozen</option>
                                <option value="bag">Bag</option>
                                <option value="packet">Packet</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-product-description">Description</label>
                        <textarea id="edit-product-description" name="description" rows="3" class="form-control" autocomplete="off"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-product-image">Image</label>
                        <div class="image-input-group">
                            <input type="url" id="edit-product-image" name="image_url" placeholder="https://example.com/product.jpg or upload file below" class="form-control" autocomplete="off">
                            <div class="file-upload-section" style="margin-top: 10px;">
                                <input type="file" id="edit-product-image-file" accept="image/*" class="form-control" autocomplete="off" onchange="handleImageUpload(this, 'edit')">
                                <small class="form-help">Upload JPG, PNG, GIF, or WebP (max 5MB) or enter URL above</small>
                            </div>
                            <div id="current-image-preview" style="margin-top: 10px; display: none;">
                                <small>Current image:</small>
                                <img id="current-image" src="" alt="Current product image" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; margin-left: 10px;">
                            </div>
                            <div id="image-preview-edit" style="margin-top: 10px; display: none;">
                                <small>New image:</small>
                                <img id="preview-image-edit" src="" alt="New image preview" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-left: 10px;">
                                <button type="button" onclick="removeImagePreview('edit')" style="margin-left: 10px; padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Remove New</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="edit-product-featured" name="featured"> 
                                <span>Featured Product</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="edit-product-active" name="is_active"> 
                                <span>Active</span>
                            </label>
                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideEditProductModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateProduct()">Update Product</button>
                </div>
            </div>
        </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product-name">Product Name *</label>
                            <input type="text" id="product-name" name="name" required class="form-control" autocomplete="off" aria-describedby="product-name-help">
                            <small id="product-name-help" class="form-help">Enter the product name</small>
                        </div>
                        <div class="form-group">
                            <label for="product-category">Category</label>
                            <select id="product-category" name="category_id" class="form-control">
                                <option value="1">Fruits & Vegetables</option>
                                <option value="2">Dairy & Eggs</option>
                                <option value="3">Meat & Seafood</option>
                                <option value="4">Bakery</option>
                                <option value="5">Pantry Staples</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product-price">Price (Rs.) *</label>
                            <input type="number" id="product-price" name="price" required min="0" step="0.01" class="form-control" autocomplete="off" aria-describedby="product-price-help">
                            <small id="product-price-help" class="form-help">Enter price in Rupees</small>
                        </div>
                        <div class="form-group">
                            <label for="product-original-price">Original Price (Rs.)</label>
                            <input type="number" id="product-original-price" name="original_price" min="0" step="0.01" class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product-stock">Stock Quantity *</label>
                            <input type="number" id="product-stock" name="stock" required min="0" class="form-control" autocomplete="off" aria-describedby="product-stock-help">
                            <small id="product-stock-help" class="form-help">Available quantity</small>
                        </div>
                        <div class="form-group">
                            <label for="product-unit">Unit</label>
                            <select id="product-unit" name="unit" class="form-control">
                                <option value="kg">Kilogram (kg)</option>
                                <option value="liter">Liter</option>
                                <option value="piece">Piece</option>
                                <option value="dozen">Dozen</option>
                                <option value="bag">Bag</option>
                                <option value="packet">Packet</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product-image">Product Image</label>
                            <div class="image-upload-container">
                                <input type="file" id="product-image" name="image" class="form-control" accept="image/*">
                                <div class="image-preview" id="image-preview" style="display: none;">
                                    <img id="preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                                    <button type="button" class="btn btn-small btn-danger" onclick="removeImagePreview()">Remove</button>
                                </div>
                                <small class="form-help">Supported formats: JPEG, PNG, GIF, WebP. Max size: 5MB</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="product-description">Description</label>
                        <textarea id="product-description" name="description" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="product-image">Image</label>
                        <div class="image-input-group">
                            <input type="url" id="product-image" name="image_url" placeholder="https://example.com/product.jpg or upload file below" class="form-control" autocomplete="off">
                            <div class="file-upload-section" style="margin-top: 10px;">
                                <input type="file" id="product-image-file" accept="image/*" class="form-control" autocomplete="off" onchange="handleImageUpload(this, 'add')">
                                <small class="form-help">Upload JPG, PNG, GIF, or WebP (max 5MB) or enter URL above</small>
                            </div>
                            <div id="image-preview-add" style="margin-top: 10px; display: none;">
                                <img id="preview-image-add" src="" alt="Image preview" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                                <button type="button" onclick="removeImagePreview('add')" style="margin-left: 10px; padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Remove</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="product-featured" name="featured"> 
                                <span>Featured Product</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="product-active" name="is_active" checked> 
                                <span>Active</span>
                            </label>
                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideAddProductModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitNewProduct()">Add Product</button>
                </div>
            </div>
        </div>

        <!-- Orders Section -->
        <section id="orders" class="admin-section">
            <div class="admin-header">
                <h2>Order Management</h2>
                <div class="header-actions">
                    <select class="filter-select" id="orderStatusFilter" onchange="loadOrdersData()">
                        <option value="">All Orders</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button class="btn btn-secondary" onclick="exportOrdersData()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
            
            <div class="admin-card">
                <div class="card-header">
                    <div class="search-filters">
                        <input type="text" id="orderSearchInput" placeholder="Search orders..." autocomplete="off" onkeyup="filterOrders()">
                        <input type="date" id="orderDateFrom" placeholder="From Date" autocomplete="off" onchange="loadOrdersData()">
                        <input type="date" id="orderDateTo" placeholder="To Date" autocomplete="off" onchange="loadOrdersData()">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <!-- Orders will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination" id="ordersPagination">
                    <!-- Pagination will be added dynamically -->
                </div>
            </div>
        </section>

        <!-- Customers Section -->
        <section id="customers" class="admin-section">
            <div class="admin-header">
                <h2>User Management</h2>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="showAddUserModal()">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>
            </div>
            
            <div class="admin-card">
                <div class="card-header">
                    <h3>All Users</h3>
                    <div class="search-filters">
                        <div class="search-group">
                            <label for="customer-search" class="sr-only">Search users</label>
                            <input type="search" id="customer-search" name="customer_search" placeholder="Search users..." class="search-input">
                        </div>
                        <div class="filter-group">
                            <label for="role-filter" class="sr-only">Filter by role</label>
                            <select id="role-filter" name="role_filter" class="filter-select">
                                <option value="">All Roles</option>
                                <option value="customer">Customers</option>
                                <option value="manager">Managers</option>
                                <option value="admin">Admins</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="user-status-filter" class="sr-only">Filter by status</label>
                            <select id="user-status-filter" name="user_status_filter" class="filter-select">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-tbody">
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    <i class="fas fa-spinner fa-spin"></i> Loading users...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Add User Modal -->
        <div id="addUserModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add New User</h3>
                    <button type="button" class="close-btn" onclick="hideAddUserModal()">&times;</button>
                </div>
                <form id="addUserForm" class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="user-first-name">First Name *</label>
                            <input type="text" id="user-first-name" name="first_name" required class="form-control" aria-describedby="user-first-name-help">
                            <small id="user-first-name-help" class="form-help">Enter first name</small>
                        </div>
                        <div class="form-group">
                            <label for="user-last-name">Last Name *</label>
                            <input type="text" id="user-last-name" name="last_name" required class="form-control" aria-describedby="user-last-name-help">
                            <small id="user-last-name-help" class="form-help">Enter last name</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="user-username">Username *</label>
                            <input type="text" id="user-username" name="username" required class="form-control" aria-describedby="user-username-help">
                            <small id="user-username-help" class="form-help">Unique username for login</small>
                        </div>
                        <div class="form-group">
                            <label for="user-email">Email *</label>
                            <input type="email" id="user-email" name="email" required class="form-control" autocomplete="email" aria-describedby="user-email-help">
                            <small id="user-email-help" class="form-help">Valid email address</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="user-phone">Phone</label>
                            <input type="tel" id="user-phone" name="phone" class="form-control" aria-describedby="user-phone-help">
                            <small id="user-phone-help" class="form-help">Contact phone number</small>
                        </div>
                        <div class="form-group">
                            <label for="user-role">Role *</label>
                            <select id="user-role" name="role" required class="form-control" aria-describedby="user-role-help">
                                <option value="">Select Role</option>
                                <option value="customer">Customer</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                            <small id="user-role-help" class="form-help">Select user role</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="user-password">Password *</label>
                            <input type="password" id="user-password" name="password" required class="form-control" autocomplete="new-password" aria-describedby="user-password-help">
                            <small id="user-password-help" class="form-help">Minimum 6 characters</small>
                        </div>
                        <div class="form-group">
                            <label for="user-confirm-password">Confirm Password *</label>
                            <input type="password" id="user-confirm-password" name="confirm_password" required class="form-control" autocomplete="new-password" aria-describedby="user-confirm-password-help">
                            <small id="user-confirm-password-help" class="form-help">Re-enter password</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="user-address">Address</label>
                        <textarea id="user-address" name="address" rows="3" class="form-control" aria-describedby="user-address-help"></textarea>
                        <small id="user-address-help" class="form-help">Full address (optional)</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="user-active" name="is_active" checked> 
                                <span>Active User</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="user-email-verified" name="email_verified"> 
                                <span>Email Verified</span>
                            </label>
                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideAddUserModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitNewUser()">Create User</button>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div id="editUserModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit User</h3>
                    <button type="button" class="close-btn" onclick="hideEditUserModal()">&times;</button>
                </div>
                <form id="editUserForm" class="modal-body">
                    <input type="hidden" id="edit-user-id" name="user_id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-user-first-name">First Name *</label>
                            <input type="text" id="edit-user-first-name" name="first_name" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit-user-last-name">Last Name *</label>
                            <input type="text" id="edit-user-last-name" name="last_name" required class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-user-username">Username *</label>
                            <input type="text" id="edit-user-username" name="username" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit-user-email">Email *</label>
                            <input type="email" id="edit-user-email" name="email" required class="form-control" autocomplete="email">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-user-phone">Phone</label>
                            <input type="tel" id="edit-user-phone" name="phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit-user-role">Role *</label>
                            <select id="edit-user-role" name="role" required class="form-control">
                                <option value="customer">Customer</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-user-address">Address</label>
                        <textarea id="edit-user-address" name="address" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="edit-user-active" name="is_active"> 
                                <span>Active User</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="edit-user-email-verified" name="email_verified"> 
                                <span>Email Verified</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-user-password">New Password (optional)</label>
                        <input type="password" id="edit-user-password" name="password" class="form-control" autocomplete="current-password">
                        <small class="form-help">Leave blank to keep current password</small>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideEditUserModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateUser()">Update User</button>
                </div>
            </div>
        </div>

        <!-- Order Details Modal -->
        <div id="orderDetailsModal" class="modal" style="display: none;">
            <div class="modal-content" style="width: 90%; max-width: 800px;">
                <div class="modal-header">
                    <h3>Order Details</h3>
                    <button type="button" class="close-btn" onclick="hideOrderDetailsModal()">&times;</button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Order details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideOrderDetailsModal()">Close</button>
                </div>
            </div>
        </div>

        <!-- Edit Order Modal -->
        <div id="editOrderModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Order Status</h3>
                    <button type="button" class="close-btn" onclick="hideEditOrderModal()">&times;</button>
                </div>
                <form id="editOrderForm" class="modal-body">
                    <input type="hidden" id="edit-order-id" name="order_id">
                    
                    <div class="form-group">
                        <label for="edit-order-status">Order Status</label>
                        <select id="edit-order-status" name="status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-tracking-number">Tracking Number</label>
                        <input type="text" id="edit-tracking-number" name="tracking_number" class="form-control" placeholder="Enter tracking number (optional)">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-admin-notes">Admin Notes</label>
                        <textarea id="edit-admin-notes" name="admin_notes" class="form-control" rows="3" placeholder="Add notes for internal use"></textarea>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideEditOrderModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateOrder()">Update Order</button>
                </div>
            </div>
        </div>

        <!-- Settings Section -->
        <section id="settings" class="admin-section">
            <div class="admin-header">
                <h2>Settings</h2>
                <p>Configure your store settings</p>
            </div>
            
            <div class="settings-grid">
                <div class="admin-card">
                    <h3>Store Information</h3>
                    <form class="settings-form">
                        <div class="form-group">
                            <label>Store Name</label>
                            <input type="text" value="DOKO" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Store Description</label>
                            <textarea class="form-control" rows="3">Nepal's premier online grocery store</textarea>
                        </div>
                        <div class="form-group">
                            <label>Contact Email</label>
                            <input type="email" value="info@doko.com.np" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="tel" value="+977-1-4567890" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
                
                <div class="admin-card">
                    <h3>Delivery Settings</h3>
                    <form class="settings-form">
                        <div class="form-group">
                            <label>Minimum Order Amount</label>
                            <input type="number" value="500" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Standard Delivery Fee</label>
                            <input type="number" value="0" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Express Delivery Fee</label>
                            <input type="number" value="50" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Delivery Areas</label>
                            <textarea class="form-control" rows="3">Kathmandu, Lalitpur, Bhaktapur, Pokhara</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <!-- Admin Styles -->
    <style>
    .admin-body {
        margin: 0;
        background: #f8f9fa;
        font-family: inherit;
    }
    
    .admin-nav {
        background: var(--white);
        box-shadow: var(--shadow);
        padding: 1rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    
    .admin-nav-brand h1 {
        margin: 0;
        color: var(--primary-color);
        font-size: 1.5rem;
    }
    
    .admin-nav-menu {
        display: flex;
        gap: 2rem;
    }
    
    .admin-nav-menu .nav-link {
        color: var(--dark-text);
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .admin-nav-menu .nav-link:hover,
    .admin-nav-menu .nav-link.active {
        background: var(--primary-light);
        color: var(--primary-color);
    }
    
    .admin-nav-user {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .admin-main {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .admin-section {
        display: none;
    }
    
    .admin-section.active {
        display: block;
    }
    
    .admin-header {
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .admin-header h2 {
        margin: 0;
        color: var(--dark-text);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: var(--white);
        padding: 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: var(--primary-light);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .stat-info h3 {
        margin: 0 0 0.25rem 0;
        font-size: 1.8rem;
        color: var(--dark-text);
    }
    
    .stat-info p {
        margin: 0;
        color: var(--light-text);
        font-size: 0.9rem;
    }
    
    .stat-change {
        font-size: 0.8rem;
        margin-top: 0.25rem;
        display: block;
    }
    
    .stat-change.positive {
        color: var(--success-color);
    }
    
    .admin-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }
    
    .admin-card {
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    
    .admin-card h3 {
        margin: 0;
        padding: 1.5rem;
        background: var(--background-color);
        border-bottom: 1px solid var(--border-color);
        color: var(--dark-text);
    }
    
    .card-header {
        padding: 1.5rem;
        background: var(--background-color);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .search-filters {
        display: flex;
        gap: 1rem;
    }
    
    .search-input, .filter-select {
        padding: 0.5rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: 0.9rem;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .admin-table th,
    .admin-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    
    .admin-table th {
        background: var(--background-color);
        font-weight: 600;
        color: var(--dark-text);
    }
    
    .status {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status.delivered {
        background: #d4edda;
        color: #155724;
    }
    
    .status.processing {
        background: #fff3cd;
        color: #856404;
    }
    
    .status.shipped {
        background: #cce5ff;
        color: #0056b3;
    }
    
    .status.pending {
        background: #f8d7da;
        color: #721c24;
    }
    
    .status.cancelled {
        background: #f5c6cb;
        color: #721c24;
    }
    
    .status.active {
        background: #d4edda;
        color: #155724;
    }

    /* Order Management Styles */
    .order-details {
        padding: 1rem 0;
    }
    
    .detail-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .detail-col h4 {
        color: var(--primary-color);
        margin-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 0.5rem;
    }
    
    .detail-col p {
        margin: 0.5rem 0;
        display: flex;
        align-items: center;
    }
    
    .detail-col strong {
        min-width: 120px;
        color: var(--text-dark);
    }
    
    .admin-notes {
        margin-top: 2rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid var(--primary-color);
    }
    
    .admin-notes h4 {
        margin-top: 0;
        color: var(--primary-color);
    }
    
    .search-filters {
        display: flex;
        gap: 1rem;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    
    .search-filters input,
    .search-filters select {
        padding: 0.5rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.9rem;
    }
    
    .search-filters input[type="text"] {
        min-width: 200px;
    }
    
    .search-filters input[type="date"] {
        min-width: 150px;
    }
    
    .pagination {
        padding: 1rem;
        border-top: 1px solid var(--border-color);
    }
    
    .pagination-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .pagination-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .pagination-buttons button {
        padding: 0.5rem 1rem;
        border: 1px solid var(--border-color);
        background: white;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .pagination-buttons button:hover {
        background: var(--primary-color);
        color: white;
    }
    
    @media (max-width: 768px) {
        .detail-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .search-filters {
            flex-direction: column;
            align-items: stretch;
        }
        
        .search-filters input,
        .search-filters select {
            width: 100%;
        }
        
        .pagination-controls {
            flex-direction: column;
            gap: 1rem;
        }
    }
    
    .product-list {
        padding: 1.5rem;
    }
    
    .product-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    
    .product-item:last-child {
        border-bottom: none;
    }
    
    .product-item img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .product-info {
        flex: 1;
    }
    
    .product-info h4 {
        margin: 0 0 0.25rem 0;
        font-size: 0.9rem;
    }
    
    .product-info p {
        margin: 0;
        color: var(--light-text);
        font-size: 0.8rem;
    }
    
    .product-sales {
        font-weight: 600;
        color: var(--success-color);
    }
    
    .product-cell {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .product-cell img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 6px;
    }
    
    .btn-icon {
        background: none;
        border: none;
        color: var(--light-text);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 4px;
        transition: var(--transition);
    }
    
    .btn-icon:hover {
        background: var(--background-color);
        color: var(--primary-color);
    }
    
    .settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem;
    }
    
    .settings-form {
        padding: 1.5rem;
    }
    
    .settings-form .form-group {
        margin-bottom: 1.5rem;
    }
    
    .settings-form label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--dark-text);
    }
    
    .settings-form .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: 1rem;
        font-family: inherit;
    }
    
    .settings-form .form-control:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }
    
    @media (max-width: 768px) {
        .admin-nav {
            flex-direction: column;
            gap: 1rem;
            padding: 1rem;
        }
        
        .admin-nav-menu {
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .admin-main {
            padding: 1rem;
        }
        
        .admin-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .admin-grid {
            grid-template-columns: 1fr;
        }
        
        .settings-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Image Upload Styling */
    .image-upload-container {
        position: relative;
    }

    .image-upload-container input[type="file"] {
        width: 100%;
        padding: 20px;
        border: 2px dashed #cbd5e0;
        border-radius: 8px;
        background: #f7fafc;
        transition: all 0.3s ease;
        text-align: center;
        cursor: pointer;
    }

    .image-upload-container input[type="file"]:hover {
        border-color: #667eea;
        background: #edf2f7;
    }

    .image-preview {
        margin-top: 1rem;
        padding: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
        text-align: center;
    }

    .image-preview img {
        display: block;
        margin: 0 auto 1rem auto;
        border: 1px solid #e2e8f0;
        object-fit: cover;
        border-radius: 8px;
    }

    .btn-small {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
    </style>

    <!-- Admin JavaScript -->
    <script>
    // Admin API class
    class AdminAPI {
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
                console.error('Admin API Error:', error);
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
        
        static async uploadFile(endpoint, formData) {
            try {
                const response = await fetch(`/api/${endpoint}`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                }
                
                return data;
            } catch (error) {
                console.error('File Upload Error:', error);
                this.showNotification(error.message, 'error');
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
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Show corresponding section
            const sectionId = this.getAttribute('data-section');
            const section = document.getElementById(sectionId);
            section.classList.add('active');
            
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
            case 'orders':
                await loadOrdersData();
                break;
            case 'customers':
                await loadUsersData();
                break;
        }
    }

    // Load dashboard data
    async function loadDashboardData() {
        try {
            const response = await AdminAPI.call('admin-dashboard.php');
            if (response.success) {
                updateDashboardStats(response.data.stats);
                updateRecentActivity(response.data);
            }
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
        }
    }

    // Update dashboard statistics
    function updateDashboardStats(stats) {
        // Update stat cards with animation
        const updateStatCard = (id, value, label) => {
            const element = document.getElementById(id);
            if (element) {
                // Add loading animation
                element.style.opacity = '0.5';
                setTimeout(() => {
                    element.textContent = value;
                    element.style.opacity = '1';
                }, 200);
            }
        };

        updateStatCard('total-orders', stats.total_categories || 0);
        updateStatCard('total-users', stats.total_users || 0);
        updateStatCard('total-products', stats.total_products || 0);
        updateStatCard('low-stock', stats.low_stock_alert || 0);
        
        // Update change indicators
        document.getElementById('orders-change').textContent = `${stats.total_categories || 0} active categories`;
        document.getElementById('users-change').textContent = `${stats.admin_users || 0} admin users`;
        document.getElementById('products-change').textContent = `${stats.featured_products || 0} featured products`;
        
        // Update alert status
        const alertsChange = document.getElementById('alerts-change');
        if (stats.low_stock_alert > 5) {
            alertsChange.textContent = 'High alert - needs attention';
            alertsChange.className = 'stat-change negative';
        } else if (stats.low_stock_alert > 0) {
            alertsChange.textContent = 'Some items need restocking';
            alertsChange.className = 'stat-change';
        } else {
            alertsChange.textContent = 'All products well stocked';
            alertsChange.className = 'stat-change positive';
        }
    }

    // Update recent activity with real data
    function updateRecentActivity(data) {
        updateRecentProductsTable(data.recent_products || []);
        updateTopProductsList(data.top_products || []);
    }

    // Update recent products table
    function updateRecentProductsTable(products) {
        const tbody = document.getElementById('recent-products-table');
        if (!tbody) return;
        
        if (products.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem; color: #64748b;">
                        <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <br>No products found
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = '';
        products.forEach(product => {
            const row = document.createElement('tr');
            row.style.opacity = '0';
            row.innerHTML = `
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 32px; height: 32px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #64748b;">
                            <i class="fas fa-box"></i>
                        </div>
                        <div>
                            <strong style="color: #1e293b; font-weight: 600;">${product.name}</strong>
                            <br><small style="color: #64748b;">${product.category_name || 'Uncategorized'}</small>
                        </div>
                    </div>
                </td>
                <td style="font-weight: 600; color: #16a34a;">Rs. ${product.price}</td>
                <td>
                    <span style="color: ${product.stock > 10 ? '#16a34a' : product.stock > 0 ? '#d97706' : '#dc2626'}; font-weight: 500;">
                        ${product.stock} units
                    </span>
                </td>
                <td><span class="status ${product.is_active ? 'active' : 'inactive'}">${product.is_active ? 'Active' : 'Inactive'}</span></td>
                <td style="color: #64748b; font-size: 0.85rem;">
                    ${new Date(product.created_at).toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric',
                        year: 'numeric'
                    })}
                </td>
            `;
            tbody.appendChild(row);
            
            // Animate row appearance
            setTimeout(() => {
                row.style.transition = 'opacity 0.3s ease';
                row.style.opacity = '1';
            }, 100);
        });
    }

    // Update top products list
    function updateTopProductsList(products) {
        const container = document.getElementById('top-products-list');
        if (!container) return;
        
        if (products.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 2rem; color: #64748b;">
                    <i class="fas fa-star" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <br>No featured products found
                </div>
            `;
            return;
        }
        
        container.innerHTML = '';
        products.forEach((product, index) => {
            const item = document.createElement('div');
            item.style.cssText = `
                display: flex; 
                align-items: center; 
                gap: 1rem; 
                padding: 1rem; 
                border-bottom: 1px solid #f1f5f9; 
                transition: background-color 0.2s ease;
                opacity: 0;
                transform: translateY(10px);
            `;
            
            item.innerHTML = `
                <div style="flex-shrink: 0; width: 48px; height: 48px; background: linear-gradient(135deg, #ff6b35, #f7931e); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.85rem;">
                    #${index + 1}
                </div>
                <div style="flex: 1;">
                    <h4 style="margin: 0; font-size: 0.95rem; font-weight: 600; color: #1e293b;">${product.name}</h4>
                    <p style="margin: 0.25rem 0 0; font-size: 0.8rem; color: #64748b;">${product.category_name || 'Uncategorized'}</p>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                        ${product.featured ? '<span style="background: #dcfce7; color: #16a34a; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 500;">Featured</span>' : ''}
                        <span style="color: #64748b; font-size: 0.75rem;">${product.stock} in stock</span>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-weight: 600; color: #16a34a; font-size: 0.95rem;">Rs. ${product.price}</div>
                    <div style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem;">${product.mock_sales || 0} sold</div>
                </div>
            `;
            
            item.addEventListener('mouseenter', () => {
                item.style.backgroundColor = '#f8fafc';
            });
            
            item.addEventListener('mouseleave', () => {
                item.style.backgroundColor = 'transparent';
            });
            
            container.appendChild(item);
            
            // Animate item appearance
            setTimeout(() => {
                item.style.transition = 'opacity 0.3s ease, transform 0.3s ease, background-color 0.2s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }

    // Add export functionality
    function exportData() {
        const activeSection = document.querySelector('.admin-section.active');
        
        if (!activeSection) {
            AdminAPI.showNotification('No active section to export', 'error');
            return;
        }
        
        const sectionId = activeSection.id;
        
        switch (sectionId) {
            case 'products':
                exportProducts();
                break;
            case 'orders':
                exportOrders();
                break;
            case 'customers':
                exportUsers();
                break;
            default:
                AdminAPI.showNotification('Export not available for this section', 'info');
        }
    }

    async function exportProducts() {
        try {
            AdminAPI.showNotification('Exporting products...', 'info');
            const response = await AdminAPI.call('products-list.php?export=csv');
            
            if (response.success) {
                downloadCSV(response.data, 'products.csv');
                AdminAPI.showNotification('Products exported successfully!', 'success');
            } else {
                // Fallback: export current table data
                exportTableToCSV('products', 'products.csv');
            }
        } catch (error) {
            console.error('Export products error:', error);
            exportTableToCSV('products', 'products.csv');
        }
    }

    async function exportOrders() {
        try {
            AdminAPI.showNotification('Exporting orders...', 'info');
            const response = await AdminAPI.call('admin-orders.php?export=csv');
            
            if (response.success) {
                downloadCSV(response.data, 'orders.csv');
                AdminAPI.showNotification('Orders exported successfully!', 'success');
            } else {
                exportTableToCSV('orders', 'orders.csv');
            }
        } catch (error) {
            console.error('Export orders error:', error);
            exportTableToCSV('orders', 'orders.csv');
        }
    }

    async function exportUsers() {
        try {
            AdminAPI.showNotification('Exporting users...', 'info');
            const response = await AdminAPI.call('admin-users.php?export=csv');
            
            if (response.success) {
                downloadCSV(response.data, 'users.csv');
                AdminAPI.showNotification('Users exported successfully!', 'success');
            } else {
                exportTableToCSV('customers', 'users.csv');
            }
        } catch (error) {
            console.error('Export users error:', error);
            exportTableToCSV('customers', 'users.csv');
        }
    }

    function exportTableToCSV(sectionId, filename) {
        const section = document.getElementById(sectionId);
        const table = section.querySelector('.admin-table');
        
        if (!table) {
            AdminAPI.showNotification('No table data to export', 'error');
            return;
        }
        
        let csv = '';
        const rows = table.querySelectorAll('tr');
        
        rows.forEach((row, index) => {
            const cols = row.querySelectorAll('th, td');
            const rowData = [];
            
            cols.forEach(col => {
                // Clean text content
                let text = col.textContent.trim().replace(/\s+/g, ' ');
                // Escape quotes
                text = text.replace(/"/g, '""');
                rowData.push(`"${text}"`);
            });
            
            csv += rowData.join(',') + '\n';
        });
        
        downloadCSV(csv, filename);
        AdminAPI.showNotification(`${filename} exported successfully!`, 'success');
    }

    function downloadCSV(csvContent, filename) {
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            // Fallback for older browsers
            window.open('data:text/csv;charset=utf-8,' + encodeURI(csvContent));
        }
    }

    // Load products data
    async function loadProductsData() {
        try {
            console.log('Loading products data...');
            const response = await fetch('/api/admin-products.php', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            console.log('Products response status:', response.status);
            const data = await response.json();
            console.log('Products response data:', data);
            
            if (data.success) {
                updateProductsTable(data.data);
            } else {
                throw new Error(data.message || 'Failed to load products');
            }
        } catch (error) {
            console.error('Failed to load products data:', error);
            AdminAPI.showNotification('Failed to load products: ' + error.message, 'error');
            const tbody = document.querySelector('#products .admin-table tbody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: red;">Failed to load products: ' + error.message + '</td></tr>';
            }
        }
    }

    // Update products table
    function updateProductsTable(products) {
        const tbody = document.querySelector('#products .admin-table tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        products.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="product-cell">
                        <img src="${product.image_url}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                        <div>
                            <strong>${product.name}</strong>
                            <br><small>${product.unit}</small>
                        </div>
                    </div>
                </td>
                <td>${product.category_name || 'N/A'}</td>
                <td>Rs. ${product.price}</td>
                <td>${product.stock} units</td>
                <td><span class="status ${product.is_active ? 'active' : 'inactive'}">${product.is_active ? 'Active' : 'Inactive'}</span></td>
                <td>
                    <button class="btn-icon" onclick="editProduct(${product.product_id})" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn-icon" onclick="deleteProduct(${product.product_id})" title="Delete"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // Load customers data
    async function loadCustomersData() {
        try {
            const response = await AdminAPI.call('admin-users.php');
            if (response.success) {
                updateCustomersTable(response.data);
            }
        } catch (error) {
            console.error('Failed to load customers data:', error);
        }
    }

    // Update customers table
    function updateCustomersTable(users) {
        const tbody = document.querySelector('#customers .admin-table tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        users.forEach(user => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${user.full_name}</td>
                <td>${user.email}</td>
                <td>${user.phone || 'N/A'}</td>
                <td><span class="status ${user.role === 'admin' ? 'admin' : 'customer'}">${user.role}</span></td>
                <td><span class="status ${user.status}">${user.status}</span></td>
                <td>
                    <button class="btn-icon" onclick="editUser(${user.user_id})" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn-icon" onclick="deactivateUser(${user.user_id})" title="Deactivate"><i class="fas fa-user-times"></i></button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // Product management functions
    function showAddProductModal() {
        document.getElementById('addProductModal').style.display = 'block';
    }

    function hideAddProductModal() {
        document.getElementById('addProductModal').style.display = 'none';
        document.getElementById('addProductForm').reset();
        // Reset image preview
        removeImagePreview();
    }

    // Image upload functions
    function removeImagePreview() {
        const preview = document.getElementById('image-preview');
        const fileInput = document.getElementById('product-image');
        
        preview.style.display = 'none';
        fileInput.value = '';
    }

    async function uploadProductImage(file) {
        const formData = new FormData();
        formData.append('image', file);

        try {
            const response = await AdminAPI.uploadFile('product-image-upload.php', formData);
            return response.url;
        } catch (error) {
            console.error('Image upload failed:', error);
            AdminAPI.showNotification('Image upload failed: ' + error.message, 'error');
            return null;
        }
    }

    async function submitNewProduct() {
        const form = document.getElementById('addProductForm');
        const formData = new FormData(form);
        
        // Handle image upload first
        let imageUrl = 'uploads/placeholder-product.jpg';
        const imageFile = formData.get('image');
        
        if (imageFile && imageFile.size > 0) {
            imageUrl = await uploadProductImage(imageFile);
            if (!imageUrl) {
                return; // Upload failed, don't submit form
            }
        }
        
        const productData = {
            name: formData.get('name'),
            description: formData.get('description'),
            price: parseFloat(formData.get('price')),
            original_price: formData.get('original_price') ? parseFloat(formData.get('original_price')) : null,
            category_id: parseInt(formData.get('category_id')),
            stock: parseInt(formData.get('stock')),
            unit: formData.get('unit'),
            featured: formData.has('featured'),
            is_active: formData.has('is_active'),
            image_url: imageUrl
        };

        AdminAPI.call('admin-products.php', {
            method: 'POST',
            body: productData
        }).then(response => {
            if (response.success) {
                AdminAPI.showNotification('Product added successfully!', 'success');
                hideAddProductModal();
                loadProductsData(); // Reload products
            }
        }).catch(error => {
            console.error('Add product error:', error);
        });
    }

    function editProduct(productId) {
        // First get product data
        AdminAPI.call(`admin-products.php?product_id=${productId}`).then(response => {
            if (response.success && response.data && response.data.length > 0) {
                const product = response.data[0];
                populateEditModal(product);
                showEditProductModal();
            }
        }).catch(error => {
            console.error('Get product error:', error);
            AdminAPI.showNotification('Failed to load product data', 'error');
        });
    }

    function populateEditModal(product) {
        // Populate form fields
        document.getElementById('edit-product-id').value = product.product_id;
        document.getElementById('edit-product-name').value = product.name || '';
        document.getElementById('edit-product-category').value = product.category_id || 1;
        document.getElementById('edit-product-price').value = product.price || '';
        document.getElementById('edit-product-original-price').value = product.original_price || '';
        document.getElementById('edit-product-stock').value = product.stock || 0;
        document.getElementById('edit-product-unit').value = product.unit || 'piece';
        document.getElementById('edit-product-description').value = product.description || '';
        document.getElementById('edit-product-image').value = product.image_url || '';
        document.getElementById('edit-product-featured').checked = Boolean(product.featured);
        document.getElementById('edit-product-active').checked = Boolean(product.is_active);
        
        // Show current image preview if exists
        const currentImagePreview = document.getElementById('current-image-preview');
        const currentImage = document.getElementById('current-image');
        if (product.image_url && product.image_url !== 'uploads/placeholder-product.jpg') {
            currentImage.src = product.image_url;
            currentImagePreview.style.display = 'block';
        } else {
            currentImagePreview.style.display = 'none';
        }
    }

    function showEditProductModal() {
        document.getElementById('editProductModal').style.display = 'block';
    }

    function hideEditProductModal() {
        document.getElementById('editProductModal').style.display = 'none';
        document.getElementById('editProductForm').reset();
        document.getElementById('current-image-preview').style.display = 'none';
    }

    function updateProduct() {
        const form = document.getElementById('editProductForm');
        const formData = new FormData(form);
        
        const productData = {
            product_id: formData.get('product_id'),
            name: formData.get('name'),
            description: formData.get('description'),
            price: parseFloat(formData.get('price')),
            original_price: formData.get('original_price') ? parseFloat(formData.get('original_price')) : null,
            category_id: parseInt(formData.get('category_id')),
            stock: parseInt(formData.get('stock')),
            unit: formData.get('unit'),
            featured: formData.has('featured'),
            is_active: formData.has('is_active'),
            image_url: formData.get('image_url') || 'uploads/placeholder-product.jpg'
        };

        // Validation
        if (!productData.name.trim()) {
            AdminAPI.showNotification('Product name is required', 'error');
            return;
        }
        
        if (productData.price <= 0) {
            AdminAPI.showNotification('Price must be greater than 0', 'error');
            return;
        }
        
        if (productData.stock < 0) {
            AdminAPI.showNotification('Stock cannot be negative', 'error');
            return;
        }

        AdminAPI.call('admin-products.php', {
            method: 'PUT',
            body: productData
        }).then(response => {
            if (response.success) {
                AdminAPI.showNotification('Product updated successfully!', 'success');
                hideEditProductModal();
                loadProductsData(); // Reload products
            }
        }).catch(error => {
            console.error('Update product error:', error);
            AdminAPI.showNotification('Failed to update product', 'error');
        });
    }

    function deleteProduct(productId) {
        if (confirm('Are you sure you want to delete this product?')) {
            AdminAPI.call('admin-products.php', {
                method: 'DELETE',
                body: { product_id: productId }
            }).then(response => {
                if (response.success) {
                    AdminAPI.showNotification('Product deleted successfully!', 'success');
                    loadProductsData(); // Reload products
                }
            }).catch(error => {
                console.error('Delete product error:', error);
            });
        }
    }

    // User management functions
    async function editUser(userId) {
        try {
            AdminAPI.showNotification('Loading user data...', 'info');
            
            // Fetch user data
            const response = await AdminAPI.call(`admin-users.php?id=${userId}`);
            if (response.success && response.data) {
                const user = response.data;
                
                // Populate edit form
                document.getElementById('edit-user-id').value = user.user_id;
                document.getElementById('edit-user-first-name').value = user.first_name || '';
                document.getElementById('edit-user-last-name').value = user.last_name || '';
                document.getElementById('edit-user-username').value = user.username || '';
                document.getElementById('edit-user-email').value = user.email || '';
                document.getElementById('edit-user-phone').value = user.phone || '';
                document.getElementById('edit-user-role').value = user.role || 'customer';
                document.getElementById('edit-user-address').value = user.address || '';
                document.getElementById('edit-user-active').checked = user.status === 'active';
                document.getElementById('edit-user-email-verified').checked = user.email_verified == 1;
                
                // Show modal
                document.getElementById('editUserModal').style.display = 'flex';
            } else {
                AdminAPI.showNotification('Failed to load user data', 'error');
            }
        } catch (error) {
            console.error('Edit user error:', error);
            AdminAPI.showNotification('Failed to load user data', 'error');
        }
    }

    // Image upload functions
    function handleImageUpload(fileInput, modalType) {
        const file = fileInput.files[0];
        if (!file) return;
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            AdminAPI.showNotification('Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.', 'error');
            fileInput.value = '';
            return;
        }
        
        // Validate file size (max 5MB)
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            AdminAPI.showNotification('File size too large. Maximum size is 5MB.', 'error');
            fileInput.value = '';
            return;
        }
        
        // Show loading state
        AdminAPI.showNotification('Uploading image...', 'info');
        
        // Create FormData and upload
        const formData = new FormData();
        formData.append('image', file);
        
        fetch('api/admin-upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update image URL field
                const imageUrlField = document.getElementById(modalType === 'add' ? 'product-image' : 'edit-product-image');
                imageUrlField.value = data.image_url;
                
                // Show preview
                showImagePreview(data.image_url, modalType);
                
                AdminAPI.showNotification('Image uploaded successfully!', 'success');
            } else {
                throw new Error(data.message || 'Upload failed');
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            AdminAPI.showNotification(`Upload failed: ${error.message}`, 'error');
            fileInput.value = '';
        });
    }
    
    function showImagePreview(imageUrl, modalType) {
        const previewDiv = document.getElementById(`image-preview-${modalType}`);
        const previewImg = document.getElementById(`preview-image-${modalType}`);
        
        if (previewDiv && previewImg) {
            previewImg.src = imageUrl;
            previewDiv.style.display = 'block';
        }
    }
    
    function removeImagePreview(modalType) {
        const previewDiv = document.getElementById(`image-preview-${modalType}`);
        const imageUrlField = document.getElementById(modalType === 'add' ? 'product-image' : 'edit-product-image');
        const fileInput = document.getElementById(modalType === 'add' ? 'product-image-file' : 'edit-product-image-file');
        
        if (previewDiv) previewDiv.style.display = 'none';
        if (imageUrlField) imageUrlField.value = '';
        if (fileInput) fileInput.value = '';
    }

    function deactivateUser(userId) {
        if (confirm('Are you sure you want to deactivate this user?')) {
            AdminAPI.call('admin-users.php', {
                method: 'DELETE',
                body: { user_id: userId }
            }).then(response => {
                if (response.success) {
                    AdminAPI.showNotification('User deactivated successfully!', 'success');
                    loadCustomersData(); // Reload users
                }
            }).catch(error => {
                console.error('Deactivate user error:', error);
            });
        }
    }

    // Initialize admin panel
    document.addEventListener('DOMContentLoaded', function() {
        // Load initial dashboard data
        loadDashboardData();
        
        // Set up periodic updates
        setInterval(() => {
            const activeSection = document.querySelector('.admin-section.active');
            if (activeSection && activeSection.id === 'dashboard') {
                loadDashboardData();
            }
        }, 30000);
        
        // Set up image preview functionality
        const imageInput = document.getElementById('product-image');
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const preview = document.getElementById('image-preview');
                const previewImg = document.getElementById('preview-img');
                
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.style.display = 'none';
                }
            });
        }
        
        AdminAPI.showNotification('DOKO Admin Dashboard loaded successfully!', 'success');
    });

    // Order Management Functions
    async function loadOrdersData() {
        try {
            const statusFilter = document.getElementById('orderStatusFilter')?.value || '';
            const dateFrom = document.getElementById('orderDateFrom')?.value || '';
            const dateTo = document.getElementById('orderDateTo')?.value || '';
            const page = 1; // You can implement pagination later
            const limit = 20;

            let url = 'admin-orders.php';
            const params = new URLSearchParams();
            if (statusFilter) params.append('status', statusFilter);
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);
            params.append('page', page);
            params.append('limit', limit);

            if (params.toString()) {
                url += '?' + params.toString();
            }

            const response = await AdminAPI.call(url);
            if (response.success) {
                updateOrdersTable(response.data.orders);
                updateOrdersPagination(response.data.pagination);
            }
        } catch (error) {
            console.error('Failed to load orders data:', error);
            const tbody = document.getElementById('ordersTableBody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: red;">Failed to load orders</td></tr>';
            }
        }
    }

    function updateOrdersTable(orders) {
        const tbody = document.getElementById('ordersTableBody');
        if (!tbody) return;
        
        if (!orders || orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No orders found</td></tr>';
            return;
        }

        tbody.innerHTML = orders.map(order => `
            <tr>
                <td><strong>${order.order_number}</strong></td>
                <td>
                    <div>
                        <strong>${order.customer_name || 'Unknown'}</strong>
                        <br><small>${order.customer_email || ''}</small>
                    </div>
                </td>
                <td>${order.total_items || 0} items</td>
                <td>Rs. ${parseFloat(order.total_amount || 0).toLocaleString()}</td>
                <td><span class="status ${order.status}">${capitalizeFirst(order.status)}</span></td>
                <td>${formatDate(order.created_at)}</td>
                <td>
                    <button class="btn-icon" title="View Details" onclick="viewOrderDetails(${order.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon" title="Edit Status" onclick="editOrderStatus(${order.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    ${order.status !== 'cancelled' ? `
                    <button class="btn-icon" title="Cancel Order" onclick="cancelOrder(${order.id})" style="color: #dc3545;">
                        <i class="fas fa-times"></i>
                    </button>` : ''}
                </td>
            </tr>
        `).join('');
    }

    function updateOrdersPagination(pagination) {
        const paginationDiv = document.getElementById('ordersPagination');
        if (!paginationDiv || !pagination) return;

        let paginationHTML = '';
        if (pagination.total_pages > 1) {
            paginationHTML = `
                <div class="pagination-controls">
                    <span>Page ${pagination.current_page} of ${pagination.total_pages} (${pagination.total_records} total orders)</span>
                    <div class="pagination-buttons">
                        ${pagination.current_page > 1 ? `<button onclick="loadOrdersPage(${pagination.current_page - 1})">Previous</button>` : ''}
                        ${pagination.current_page < pagination.total_pages ? `<button onclick="loadOrdersPage(${pagination.current_page + 1})">Next</button>` : ''}
                    </div>
                </div>
            `;
        }
        paginationDiv.innerHTML = paginationHTML;
    }

    function filterOrders() {
        loadOrdersData();
    }

    function exportOrdersData() {
        exportOrders();
    }

    async function viewOrderDetails(orderId) {
        try {
            const response = await AdminAPI.call(`admin-orders.php?id=${orderId}`);
            if (response.success) {
                const order = response.data;
                showOrderDetailsModal(order);
            }
        } catch (error) {
            AdminAPI.showNotification('Failed to load order details', 'error');
        }
    }

    function showOrderDetailsModal(order) {
        const modal = document.getElementById('orderDetailsModal');
        const content = document.getElementById('orderDetailsContent');
        
        const orderItems = order.items || [];
        const itemsHTML = orderItems.map(item => `
            <tr>
                <td>${item.product_name}</td>
                <td>Rs. ${parseFloat(item.price).toLocaleString()}</td>
                <td>${item.quantity}</td>
                <td>Rs. ${(parseFloat(item.price) * parseInt(item.quantity)).toLocaleString()}</td>
            </tr>
        `).join('');

        content.innerHTML = `
            <div class="order-details">
                <div class="detail-row">
                    <div class="detail-col">
                        <h4>Order Information</h4>
                        <p><strong>Order ID:</strong> ${order.order_number}</p>
                        <p><strong>Status:</strong> <span class="status ${order.status}">${capitalizeFirst(order.status)}</span></p>
                        <p><strong>Order Date:</strong> ${formatDate(order.created_at)}</p>
                        <p><strong>Total Amount:</strong> Rs. ${parseFloat(order.total_amount).toLocaleString()}</p>
                        ${order.tracking_number ? `<p><strong>Tracking:</strong> ${order.tracking_number}</p>` : ''}
                    </div>
                    <div class="detail-col">
                        <h4>Customer Information</h4>
                        <p><strong>Name:</strong> ${order.customer_name}</p>
                        <p><strong>Email:</strong> ${order.customer_email}</p>
                        <p><strong>Phone:</strong> ${order.phone || 'N/A'}</p>
                        <p><strong>Address:</strong> ${order.shipping_address || 'N/A'}</p>
                    </div>
                </div>
                
                <h4>Order Items</h4>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHTML}
                    </tbody>
                </table>

                ${order.admin_notes ? `
                <div class="admin-notes">
                    <h4>Admin Notes</h4>
                    <p>${order.admin_notes}</p>
                </div>
                ` : ''}
            </div>
        `;
        
        modal.style.display = 'flex';
    }

    function hideOrderDetailsModal() {
        document.getElementById('orderDetailsModal').style.display = 'none';
    }

    async function editOrderStatus(orderId) {
        try {
            const response = await AdminAPI.call(`admin-orders.php?id=${orderId}`);
            if (response.success) {
                const order = response.data;
                showEditOrderModal(order);
            }
        } catch (error) {
            AdminAPI.showNotification('Failed to load order details', 'error');
        }
    }

    function showEditOrderModal(order) {
        document.getElementById('edit-order-id').value = order.id;
        document.getElementById('edit-order-status').value = order.status;
        document.getElementById('edit-tracking-number').value = order.tracking_number || '';
        document.getElementById('edit-admin-notes').value = order.admin_notes || '';
        
        document.getElementById('editOrderModal').style.display = 'flex';
    }

    function hideEditOrderModal() {
        document.getElementById('editOrderModal').style.display = 'none';
        document.getElementById('editOrderForm').reset();
    }

    async function updateOrder() {
        const form = document.getElementById('editOrderForm');
        const formData = new FormData(form);
        
        try {
            const response = await AdminAPI.call('admin-orders.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData))
            });
            
            if (response.success) {
                AdminAPI.showNotification('Order updated successfully!', 'success');
                hideEditOrderModal();
                await loadOrdersData();
            } else {
                AdminAPI.showNotification(response.message || 'Failed to update order', 'error');
            }
        } catch (error) {
            AdminAPI.showNotification('Failed to update order', 'error');
        }
    }

    async function cancelOrder(orderId) {
        if (!confirm('Are you sure you want to cancel this order?')) {
            return;
        }
        
        try {
            const response = await AdminAPI.call('admin-orders.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ order_id: orderId })
            });
            
            if (response.success) {
                AdminAPI.showNotification('Order cancelled successfully!', 'success');
                await loadOrdersData();
            } else {
                AdminAPI.showNotification(response.message || 'Failed to cancel order', 'error');
            }
        } catch (error) {
            AdminAPI.showNotification('Failed to cancel order', 'error');
        }
    }

    function loadOrdersPage(page) {
        // Implement pagination functionality
        loadOrdersData();
    }

    // Helper function for capitalizing first letter
    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Helper function for formatting dates
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    // User Management Functions
    async function loadUsersData() {
        try {
            console.log('Loading users data...');
            const response = await fetch('/api/admin-users.php', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                updateUsersTable(data.data);
            } else {
                throw new Error(data.message || 'Failed to load users');
            }
        } catch (error) {
            console.error('Failed to load users data:', error);
            AdminAPI.showNotification('Failed to load users: ' + error.message, 'error');
            const tbody = document.getElementById('users-tbody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: red;">Failed to load users: ' + error.message + '</td></tr>';
            }
        }
    }

    function updateUsersTable(users) {
        const tbody = document.getElementById('users-tbody');
        if (!tbody) return;
        
        if (!users || users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: #666;">No users found</td></tr>';
            return;
        }
        
        const html = users.map(user => `
            <tr>
                <td>
                    <div class="user-cell" style="display: flex; align-items: center; gap: 0.75rem;">
                        <div class="user-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #ff6b35, #f7931e); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">
                            ${user.first_name ? user.first_name[0].toUpperCase() : user.username[0].toUpperCase()}
                        </div>
                        <div>
                            <strong>${user.full_name || user.username}</strong>
                            <br><small style="color: #64748b;">@${user.username}</small>
                        </div>
                    </div>
                </td>
                <td>${user.email}</td>
                <td>${user.phone || 'N/A'}</td>
                <td><span class="role ${user.role}" style="padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500; text-transform: capitalize; background: ${user.role === 'admin' ? '#dc2626' : user.role === 'manager' ? '#ca8a04' : '#2563eb'}; color: white;">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span></td>
                <td><span class="status ${user.status || 'active'}" style="padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500; text-transform: capitalize; background: ${(user.status || 'active') === 'active' ? '#16a34a' : '#dc2626'}; color: white;">${(user.status || 'active').charAt(0).toUpperCase() + (user.status || 'active').slice(1)}</span></td>
                <td>${user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'}</td>
                <td>
                    <button class="btn-icon" onclick="editUser(${user.user_id})" title="Edit User" style="margin-right: 8px;">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon delete" onclick="deleteUser(${user.user_id}, '${(user.full_name || user.username).replace(/'/g, '\\\'')}')" title="Delete User" style="color: #dc2626;">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        tbody.innerHTML = html;
    }

    function showAddUserModal() {
        document.getElementById('addUserModal').style.display = 'flex';
        document.getElementById('addUserForm').reset();
    }

    function hideAddUserModal() {
        document.getElementById('addUserModal').style.display = 'none';
    }

    async function submitNewUser() {
        const form = document.getElementById('addUserForm');
        const formData = new FormData(form);
        
        const userData = {
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            username: formData.get('username'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            role: formData.get('role'),
            password: formData.get('password'),
            confirm_password: formData.get('confirm_password'),
            address: formData.get('address'),
            status: formData.has('is_active') ? 'active' : 'inactive',
            email_verified: formData.has('email_verified') ? 1 : 0
        };

        // Basic validation
        if (!userData.first_name || !userData.last_name || !userData.username || !userData.email || !userData.password || !userData.role) {
            AdminAPI.showNotification('Please fill in all required fields', 'error');
            return;
        }

        if (userData.password !== userData.confirm_password) {
            AdminAPI.showNotification('Passwords do not match', 'error');
            return;
        }

        if (userData.password.length < 6) {
            AdminAPI.showNotification('Password must be at least 6 characters long', 'error');
            return;
        }

        try {
            AdminAPI.showNotification('Creating user...', 'info');
            
            const response = await AdminAPI.call('admin-users.php', {
                method: 'POST',
                body: JSON.stringify(userData)
            });

            if (response.success) {
                AdminAPI.showNotification('User created successfully!', 'success');
                hideAddUserModal();
                await loadUsersData();
            } else {
                AdminAPI.showNotification(response.message || 'Failed to create user', 'error');
            }
        } catch (error) {
            console.error('Error creating user:', error);
            AdminAPI.showNotification('Failed to create user', 'error');
        }
    }

    async function editUser(userId) {
        try {
            AdminAPI.showNotification('Loading user data...', 'info');
            
            // Fetch user data
            const response = await AdminAPI.call(`admin-users.php?id=${userId}`);
            if (response.success && response.data) {
                const user = response.data;
                
                // Populate edit form
                document.getElementById('edit-user-id').value = user.user_id;
                document.getElementById('edit-user-first-name').value = user.first_name || '';
                document.getElementById('edit-user-last-name').value = user.last_name || '';
                document.getElementById('edit-user-username').value = user.username || '';
                document.getElementById('edit-user-email').value = user.email || '';
                document.getElementById('edit-user-phone').value = user.phone || '';
                document.getElementById('edit-user-role').value = user.role || 'customer';
                document.getElementById('edit-user-address').value = user.address || '';
                document.getElementById('edit-user-active').checked = user.status === 'active';
                document.getElementById('edit-user-email-verified').checked = user.email_verified == 1;
                
                // Show modal
                document.getElementById('editUserModal').style.display = 'flex';
            } else {
                AdminAPI.showNotification('Failed to load user data', 'error');
            }
        } catch (error) {
            console.error('Edit user error:', error);
            AdminAPI.showNotification('Failed to load user data', 'error');
        }
    }

    function hideEditUserModal() {
        document.getElementById('editUserModal').style.display = 'none';
    }

    async function updateUser() {
        try {
            const formData = new FormData(document.getElementById('editUserForm'));
            
            const userData = {
                user_id: formData.get('user_id'),
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                username: formData.get('username'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                role: formData.get('role'),
                address: formData.get('address'),
                status: formData.has('is_active') ? 'active' : 'inactive',
                email_verified: formData.has('email_verified') ? 1 : 0,
                password: formData.get('password') // Only if provided
            };

            // Basic validation
            if (!userData.first_name || !userData.last_name || !userData.username || !userData.email || !userData.role) {
                AdminAPI.showNotification('Please fill in all required fields', 'error');
                return;
            }

            AdminAPI.showNotification('Updating user...', 'info');
            
            const response = await AdminAPI.call('admin-users.php', {
                method: 'PUT',
                body: JSON.stringify(userData)
            });

            if (response.success) {
                AdminAPI.showNotification('User updated successfully!', 'success');
                hideEditUserModal();
                await loadUsersData();
            } else {
                AdminAPI.showNotification(response.message || 'Failed to update user', 'error');
            }
        } catch (error) {
            console.error('Update user error:', error);
            AdminAPI.showNotification('Failed to update user', 'error');
        }
    }

    async function deleteUser(userId, userName) {
        if (!confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
            return;
        }

        try {
            AdminAPI.showNotification('Deleting user...', 'info');
            // Use existing delete functionality
            const response = await AdminAPI.call('admin-users.php', {
                method: 'DELETE',
                body: { user_id: userId }
            });

            if (response.success) {
                await loadUsersData();
                AdminAPI.showNotification('User deleted successfully', 'success');
            } else {
                AdminAPI.showNotification(response.message || 'Failed to delete user', 'error');
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            AdminAPI.showNotification('Failed to delete user', 'error');
        }
    }

    // Add CSS animation for notifications
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .notification { animation: slideIn 0.3s ease-out; }
        .status.admin { background: #6f42c1; }
        .status.customer { background: #007bff; }
        .btn-icon:hover { background: #f8f9fa; }
        
        /* Modal styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .modal.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-content {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 650px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.05);
            transform: scale(0.9) translateY(20px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .modal.show .modal-content {
            transform: scale(1) translateY(0);
        }
        
        .modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            border-radius: 16px 16px 0 0;
        }
        
        .modal-header h3 {
            margin: 0;
            font-family: 'Inter', sans-serif;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            letter-spacing: -0.01em;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #64748b;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .close-btn:hover {
            background: #f1f5f9;
            color: #1e293b;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .modal-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            background: #f8fafc;
            border-radius: 0 0 16px 16px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .form-group label {
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            color: #374151;
            letter-spacing: -0.01em;
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            font-weight: 400;
            background: white;
            color: #1e293b;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #ff6b35;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }
        
        .form-help {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
            font-weight: 400;
        }
        
        .checkbox-label {
            flex-direction: row !important;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            cursor: pointer;
            padding: 0.5rem 0;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin: 0;
            accent-color: #ff6b35;
            cursor: pointer;
        }
        
        .checkbox-label span {
            font-weight: 500;
            color: #374151;
        }
        
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 20px;
                max-height: 85vh;
            }
            
            .modal-header, .modal-body, .modal-footer {
                padding: 1.25rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .modal-footer {
                flex-direction: column;
            }
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>

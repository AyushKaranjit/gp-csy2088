<?php
// Set page variables
$page_title = 'Admin Dashboard - DOKO Fresh Market';
$current_page = 'admin';
$additional_css = ['css/admin-dashboard.css'];
$additional_js = ['admin-dashboard.js'];

// Include header template
include_once '../template/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <div class="admin-logo">
            <img src="https://img.icons8.com/fluency/48/shopping-bag.png" alt="DOKO">
            <h2>DOKO Admin</h2>
        </div>
        
        <nav class="admin-nav">
            <div class="nav-section">
                <h3>Dashboard</h3>
                <a href="#overview" class="nav-link active" data-section="overview">
                    <i class="fas fa-chart-line"></i>
                    Overview
                </a>
            </div>
            
            <div class="nav-section">
                <h3>Products</h3>
                <a href="#products" class="nav-link" data-section="products">
                    <i class="fas fa-boxes"></i>
                    All Products
                </a>
                <a href="#add-product" class="nav-link" data-section="add-product">
                    <i class="fas fa-plus-circle"></i>
                    Add Product
                </a>
                <a href="#categories" class="nav-link" data-section="categories">
                    <i class="fas fa-tags"></i>
                    Categories
                </a>
            </div>
            
            <div class="nav-section">
                <h3>Orders</h3>
                <a href="#orders" class="nav-link" data-section="orders">
                    <i class="fas fa-shopping-cart"></i>
                    All Orders
                </a>
                <a href="#pending-orders" class="nav-link" data-section="pending-orders">
                    <i class="fas fa-clock"></i>
                    Pending Orders
                </a>
            </div>
            
            <div class="nav-section">
                <h3>Users</h3>
                <a href="#customers" class="nav-link" data-section="customers">
                    <i class="fas fa-users"></i>
                    Customers
                </a>
                <a href="#admins" class="nav-link" data-section="admins">
                    <i class="fas fa-user-shield"></i>
                    Admin Users
                </a>
            </div>
            
            <div class="nav-section">
                <h3>Settings</h3>
                <a href="#settings" class="nav-link" data-section="settings">
                    <i class="fas fa-cog"></i>
                    Site Settings
                </a>
                <a href="#reports" class="nav-link" data-section="reports">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </div>
        </nav>
    </div>
    
    <div class="admin-main">
        <div class="admin-header">
            <div class="admin-header-left">
                <h1 id="page-title">Dashboard Overview</h1>
                <p id="page-subtitle">Welcome to DOKO Admin Panel</p>
            </div>
            <div class="admin-header-right">
                <div class="admin-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <div class="admin-user">
                    <img src="https://img.icons8.com/fluency/32/user.png" alt="Admin">
                    <span>Admin User</span>
                </div>
            </div>
        </div>
        
        <div class="admin-content">
            <!-- Overview Section -->
            <div id="overview-section" class="content-section active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3>124</h3>
                            <p>Total Orders</p>
                            <span class="stat-change positive">+12% this month</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-info">
                            <h3>456</h3>
                            <p>Total Products</p>
                            <span class="stat-change positive">+8 new products</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>89</h3>
                            <p>Total Customers</p>
                            <span class="stat-change positive">+15 this week</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>$12,456</h3>
                            <p>Total Revenue</p>
                            <span class="stat-change positive">+18% this month</span>
                        </div>
                    </div>
                </div>
                
                <div class="charts-grid">
                    <div class="chart-card">
                        <h3>Recent Orders</h3>
                        <div class="orders-list">
                            <div class="order-item">
                                <div class="order-info">
                                    <span class="order-id">#12345</span>
                                    <span class="customer-name">John Doe</span>
                                </div>
                                <div class="order-status">
                                    <span class="status pending">Pending</span>
                                    <span class="order-amount">$45.99</span>
                                </div>
                            </div>
                            <div class="order-item">
                                <div class="order-info">
                                    <span class="order-id">#12346</span>
                                    <span class="customer-name">Jane Smith</span>
                                </div>
                                <div class="order-status">
                                    <span class="status processing">Processing</span>
                                    <span class="order-amount">$67.50</span>
                                </div>
                            </div>
                            <div class="order-item">
                                <div class="order-info">
                                    <span class="order-id">#12347</span>
                                    <span class="customer-name">Mike Johnson</span>
                                </div>
                                <div class="order-status">
                                    <span class="status delivered">Delivered</span>
                                    <span class="order-amount">$23.99</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <h3>Top Products</h3>
                        <div class="products-list">
                            <div class="product-item">
                                <img src="https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=50&h=50&fit=crop" alt="Product">
                                <div class="product-info">
                                    <span class="product-name">Fresh Apples</span>
                                    <span class="product-sales">156 sold</span>
                                </div>
                            </div>
                            <div class="product-item">
                                <img src="https://images.unsplash.com/photo-1550258987-190a2d41a8ba?w=50&h=50&fit=crop" alt="Product">
                                <div class="product-info">
                                    <span class="product-name">Organic Bananas</span>
                                    <span class="product-sales">134 sold</span>
                                </div>
                            </div>
                            <div class="product-item">
                                <img src="https://images.unsplash.com/photo-1574856344991-aaa31b6f4ce3?w=50&h=50&fit=crop" alt="Product">
                                <div class="product-info">
                                    <span class="product-name">Fresh Carrots</span>
                                    <span class="product-sales">98 sold</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Products Section -->
            <div id="products-section" class="content-section">
                <div class="section-header">
                    <h2>Products Management</h2>
                    <button class="btn-primary">
                        <i class="fas fa-plus"></i>
                        Add New Product
                    </button>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><img src="https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=40&h=40&fit=crop" alt="Product"></td>
                                <td>Fresh Red Apples</td>
                                <td>Fruits</td>
                                <td>$4.99</td>
                                <td>50</td>
                                <td><span class="status active">Active</span></td>
                                <td>
                                    <button class="btn-edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn-delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td><img src="https://images.unsplash.com/photo-1550258987-190a2d41a8ba?w=40&h=40&fit=crop" alt="Product"></td>
                                <td>Organic Bananas</td>
                                <td>Fruits</td>
                                <td>$3.49</td>
                                <td>75</td>
                                <td><span class="status active">Active</span></td>
                                <td>
                                    <button class="btn-edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn-delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Add more sections as needed -->
            <div id="orders-section" class="content-section">
                <div class="section-header">
                    <h2>Orders Management</h2>
                    <div class="filter-buttons">
                        <button class="filter-btn active">All</button>
                        <button class="filter-btn">Pending</button>
                        <button class="filter-btn">Processing</button>
                        <button class="filter-btn">Delivered</button>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#12345</td>
                                <td>John Doe</td>
                                <td>2024-01-15</td>
                                <td>$45.99</td>
                                <td><span class="status pending">Pending</span></td>
                                <td>
                                    <button class="btn-view"><i class="fas fa-eye"></i></button>
                                    <button class="btn-edit"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer template
include_once '../template/footer.php';
?>

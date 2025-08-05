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
$currentUser = [
    'user_id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'],
    'email' => $_SESSION['email'],
    'role' => $_SESSION['role'],
    'full_name' => $_SESSION['full_name']
];

// If not logged in, show login form
if (!$admin_logged_in) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>DOKO Admin Login</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <div class="admin-login">
            <div class="login-container">
                <div class="login-header">
                    <h1>🛒 DOKO Admin</h1>
                    <p>Administrator Login</p>
                </div>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <div class="login-info">
                    <p><strong>Demo Credentials:</strong></p>
                    <p>Username: admin<br>Password: doko123</p>
                </div>
            </div>
        </div>
        
        <style>
        .admin-login {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-container {
            background: var(--white);
            padding: 3rem;
            border-radius: var(--border-radius);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .login-form .form-group {
            margin-bottom: 1.5rem;
        }
        
        .login-form input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .login-form input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }
        
        .login-info {
            margin-top: 2rem;
            padding: 1rem;
            background: var(--background-color);
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            color: var(--light-text);
        }
        </style>
    </body>
    </html>
    <?php
    exit;
}

// Admin dashboard content
$page_title = 'DOKO Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="admin-nav-brand">
            <h1>🛒 DOKO Admin</h1>
        </div>
        <div class="admin-nav-menu">
            <a href="#dashboard" class="nav-link active" data-section="dashboard">
                <i class="fas fa-chart-dashboard"></i> Dashboard
            </a>
            <a href="#products" class="nav-link" data-section="products">
                <i class="fas fa-box"></i> Products
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
            <span>Welcome, Admin</span>
            <a href="?logout=1" class="btn btn-outline btn-sm">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main Admin Content -->
    <main class="admin-main">
        <!-- Dashboard Section -->
        <section id="dashboard" class="admin-section active">
            <div class="admin-header">
                <h2>Dashboard Overview</h2>
                <p>Welcome to DOKO administration panel</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>142</h3>
                        <p>Total Orders</p>
                        <small class="stat-change positive">+12% from last month</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>1,234</h3>
                        <p>Registered Users</p>
                        <small class="stat-change positive">+8% from last month</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3>856</h3>
                        <p>Products</p>
                        <small class="stat-change">No change</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Rs. 45,680</h3>
                        <p>Monthly Revenue</p>
                        <small class="stat-change positive">+15% from last month</small>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="admin-grid">
                <div class="admin-card">
                    <h3>Recent Orders</h3>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>DOKO-2024-12847</td>
                                    <td>Ram Sharma</td>
                                    <td>Rs. 1,250</td>
                                    <td><span class="status delivered">Delivered</span></td>
                                    <td>2024-01-15</td>
                                </tr>
                                <tr>
                                    <td>DOKO-2024-12846</td>
                                    <td>Sita Gurung</td>
                                    <td>Rs. 890</td>
                                    <td><span class="status processing">Processing</span></td>
                                    <td>2024-01-15</td>
                                </tr>
                                <tr>
                                    <td>DOKO-2024-12845</td>
                                    <td>Kumar Thapa</td>
                                    <td>Rs. 2,150</td>
                                    <td><span class="status shipped">Shipped</span></td>
                                    <td>2024-01-14</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="admin-card">
                    <h3>Top Products</h3>
                    <div class="product-list">
                        <div class="product-item">
                            <img src="<?php echo product_image('Fresh Tomatoes'); ?>" alt="Tomatoes">
                            <div class="product-info">
                                <h4>Fresh Tomatoes</h4>
                                <p>156 sold this month</p>
                            </div>
                            <div class="product-sales">Rs. 13,260</div>
                        </div>
                        <div class="product-item">
                            <img src="<?php echo product_image('Basmati Rice'); ?>" alt="Rice">
                            <div class="product-info">
                                <h4>Basmati Rice</h4>
                                <p>89 sold this month</p>
                            </div>
                            <div class="product-sales">Rs. 26,700</div>
                        </div>
                        <div class="product-item">
                            <img src="<?php echo product_image('Fresh Milk'); ?>" alt="Milk">
                            <div class="product-info">
                                <h4>Fresh Milk</h4>
                                <p>234 sold this month</p>
                            </div>
                            <div class="product-sales">Rs. 19,890</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section id="products" class="admin-section">
            <div class="admin-header">
                <h2>Product Management</h2>
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Product
                </button>
            </div>
            
            <div class="admin-card">
                <div class="card-header">
                    <h3>All Products</h3>
                    <div class="search-filters">
                        <input type="search" placeholder="Search products..." class="search-input">
                        <select class="filter-select">
                            <option>All Categories</option>
                            <option>Vegetables</option>
                            <option>Fruits</option>
                            <option>Dairy</option>
                            <option>Grains</option>
                        </select>
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
                                <td>
                                    <div class="product-cell">
                                        <img src="<?php echo product_image('Fresh Tomatoes'); ?>" alt="Tomatoes">
                                        <div>
                                            <strong>Fresh Tomatoes</strong>
                                            <br><small>1kg pack</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Vegetables</td>
                                <td>Rs. 85</td>
                                <td>450 units</td>
                                <td><span class="status active">Active</span></td>
                                <td>
                                    <button class="btn-icon" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon" title="Delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <img src="<?php echo product_image('Royal Banana'); ?>" alt="Banana">
                                        <div>
                                            <strong>Royal Banana</strong>
                                            <br><small>1 dozen</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Fruits</td>
                                <td>Rs. 120</td>
                                <td>280 units</td>
                                <td><span class="status active">Active</span></td>
                                <td>
                                    <button class="btn-icon" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon" title="Delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Orders Section -->
        <section id="orders" class="admin-section">
            <div class="admin-header">
                <h2>Order Management</h2>
                <div class="header-actions">
                    <select class="filter-select">
                        <option>All Orders</option>
                        <option>Pending</option>
                        <option>Processing</option>
                        <option>Shipped</option>
                        <option>Delivered</option>
                    </select>
                </div>
            </div>
            
            <div class="admin-card">
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
                        <tbody>
                            <tr>
                                <td><strong>DOKO-2024-12847</strong></td>
                                <td>
                                    <div>
                                        <strong>Ram Sharma</strong>
                                        <br><small>ram.sharma@email.com</small>
                                    </div>
                                </td>
                                <td>5 items</td>
                                <td>Rs. 1,250</td>
                                <td><span class="status delivered">Delivered</span></td>
                                <td>2024-01-15</td>
                                <td>
                                    <button class="btn-icon" title="View Details"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" title="Edit"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>DOKO-2024-12846</strong></td>
                                <td>
                                    <div>
                                        <strong>Sita Gurung</strong>
                                        <br><small>sita.gurung@email.com</small>
                                    </div>
                                </td>
                                <td>3 items</td>
                                <td>Rs. 890</td>
                                <td><span class="status processing">Processing</span></td>
                                <td>2024-01-15</td>
                                <td>
                                    <button class="btn-icon" title="View Details"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" title="Edit"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Customers Section -->
        <section id="customers" class="admin-section">
            <div class="admin-header">
                <h2>Customer Management</h2>
                <button class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add Customer
                </button>
            </div>
            
            <div class="admin-card">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Ram Sharma</strong></td>
                                <td>ram.sharma@email.com</td>
                                <td>+977-9841234567</td>
                                <td>12 orders</td>
                                <td>Rs. 15,680</td>
                                <td>2023-12-15</td>
                                <td>
                                    <button class="btn-icon" title="View Profile"><i class="fas fa-user"></i></button>
                                    <button class="btn-icon" title="Edit"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Sita Gurung</strong></td>
                                <td>sita.gurung@email.com</td>
                                <td>+977-9851234567</td>
                                <td>8 orders</td>
                                <td>Rs. 8,900</td>
                                <td>2024-01-02</td>
                                <td>
                                    <button class="btn-icon" title="View Profile"><i class="fas fa-user"></i></button>
                                    <button class="btn-icon" title="Edit"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

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
    
    .status.active {
        background: #d4edda;
        color: #155724;
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
    </style>

    <!-- Admin JavaScript -->
    <script>
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
            document.getElementById(sectionId).classList.add('active');
        });
    });

    // Mock data updates
    function updateStats() {
        // Dashboard stats update logic
    }

    // Simulate real-time updates
    setInterval(updateStats, 30000);

    // DOKO Admin Dashboard loaded
    </script>
</body>
</html>

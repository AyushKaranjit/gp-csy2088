<?php
/**
 * Customer Dashboard
 * Profile management and order history for customers
 */
require_once '../template/config.php';
require_once '../src/Controllers/AuthController.php';

$auth = new AuthController();

// Handle logout first
if (isset($_GET['logout'])) {
    $auth->logout();
    header('Location: index.php');
    exit;
}

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode('customer-dashboard.php'));
    exit;
}

// Check if user is customer
if (!in_array($_SESSION['role'], ['customer'])) {
    header('Location: index.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$page_title = 'Customer Dashboard - DOKO';
$current_page = 'dashboard';

// Include header
include_header($page_title, 'Manage your DOKO account, orders, and profile.', $current_page);
?>

<main class="main-content customer-dashboard-main">
    <div class="container">
        <div class="dashboard-layout">
            <!-- Sidebar Navigation -->
            <aside class="dashboard-sidebar">
                <div class="user-profile">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-info">
                        <h3 class="user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h3>
                        <p>Customer</p>
                    </div>
                </div>
                
                <nav class="dashboard-nav">
                    <a href="#overview" class="nav-item active" data-section="overview">
                        <i class="fas fa-chart-pie"></i>
                        <span>Overview</span>
                    </a>
                    <a href="#profile" class="nav-item" data-section="profile">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="#orders" class="nav-item" data-section="orders">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Order History</span>
                    </a>
                    <a href="#addresses" class="nav-item" data-section="addresses">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Addresses</span>
                    </a>
                    <a href="#wishlist" class="nav-item" data-section="wishlist">
                        <i class="fas fa-heart"></i>
                        <span>Wishlist</span>
                    </a>
                    <a href="#settings" class="nav-item" data-section="settings">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <a href="?logout=1" class="nav-item logout" onclick="return confirm('Are you sure you want to logout?')">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="dashboard-content">
                                        <input type="text" id="first_name" name="first_name" class="form-control" autocomplete="given-name" required
                <section id="overview" class="dashboard-section active">
                    <div class="section-header">
                        <h2>Dashboard Overview</h2>
                        <p>Welcome back, <?php echo htmlspecialchars($currentUser['first_name']); ?>!</p>
                                        <input type="text" id="last_name" name="last_name" class="form-control" autocomplete="family-name" required
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                        <input type="email" id="email" name="email" class="form-control" autocomplete="email" readonly required
                            </div>
                            <div class="stat-info">
                                <h3 id="total-orders">0</h3>
                                <p>Total Orders</p>
                            </div>
                        </div>
                                            <input type="tel" id="phone" name="phone" class="form-control" autocomplete="tel" required
                            <div class="stat-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="stat-info">
                                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" autocomplete="bday" required
                                <p>Wishlist Items</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="total-spent">Rs. 0</h3>
                                <p>Total Spent</p>
                                            <input type="text" id="city" name="city" class="form-control" autocomplete="address-level2" required
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                                            <input type="text" id="postal_code" name="postal_code" class="form-control" autocomplete="postal-code"
                            <div class="stat-info">
                                <h3 id="pending-orders">0</h3>
                                <p>Pending Orders</p>
                            </div>
                        </div>
                    </div>

                    <div class="recent-activity">
                        <div class="card">
                            <div class="card-header">
                                <h3>Recent Activity</h3>
                            </div>
                            <div class="card-body">
                                <p class="no-data">No recent activity</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Profile Section -->
                <section id="profile" class="dashboard-section">
                    <div class="section-header">
                        <h2>My Profile</h2>
                        <p>Manage your personal information and address details</p>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <form id="profile-form" class="profile-form">
                                <div class="form-section">
                                    <h3><i class="fas fa-user"></i> Personal Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="first_name">First Name</label>
                                            <input type="text" id="first_name" name="first_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="last_name">Last Name</label>
                                            <input type="text" id="last_name" name="last_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" id="email" name="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($currentUser['email']); ?>" readonly>
                                        <small class="form-help">Email cannot be changed. Contact support if needed.</small>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="phone">Phone Number</label>
                                            <input type="tel" id="phone" name="phone" class="form-control" 
                                                   value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="date_of_birth">Date of Birth</label>
                                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" 
                                                   value="<?php echo htmlspecialchars($currentUser['date_of_birth'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3><i class="fas fa-map-marker-alt"></i> Address Information</h3>
                                    <div class="form-group">
                                        <label for="address">Street Address</label>
                                        <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="city">City</label>
                                            <input type="text" id="city" name="city" class="form-control" 
                                                   value="<?php echo htmlspecialchars($currentUser['city'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="postal_code">Postal Code</label>
                                            <input type="text" id="postal_code" name="postal_code" class="form-control" 
                                                   value="<?php echo htmlspecialchars($currentUser['postal_code'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="showChangePasswordModal()">
                                        <i class="fas fa-key"></i> Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- Orders Section -->
                <section id="orders" class="dashboard-section">
                    <div class="section-header">
                        <h2>Order History</h2>
                        <p>Track and manage your orders</p>
                    </div>
                    
                    <div class="card">
                        <div id="orders-list" class="orders-container">
                            <div class="loading-spinner" id="orders-loading">
                                <i class="fas fa-spinner fa-spin"></i> Loading orders...
                            </div>
                            <p class="no-data" id="no-orders" style="display: none;">No orders found.</p>
                        </div>
                    </div>
                </section>

                <!-- Addresses Section -->
                <section id="addresses" class="dashboard-section">
                    <div class="section-header">
                        <h2>My Addresses</h2>
                        <p>Manage your delivery addresses</p>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="addresses-grid">
                                <!-- Default address -->
                                <div class="address-card">
                                    <div class="address-header">
                                        <h4>Default Address</h4>
                                        <span class="address-badge default">Primary</span>
                                    </div>
                                    <div class="address-content">
                                        <p><?php echo htmlspecialchars($currentUser['address'] ?? 'No address set'); ?></p>
                                        <p><?php echo htmlspecialchars($currentUser['city'] ?? ''); ?> <?php echo htmlspecialchars($currentUser['postal_code'] ?? ''); ?></p>
                                    </div>
                                    <div class="address-actions">
                                        <button class="btn btn-sm btn-outline" onclick="editAddress('default')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="add-address-card">
                                    <div class="add-address-content">
                                        <i class="fas fa-plus-circle"></i>
                                        <h4>Add New Address</h4>
                                        <p>Add another delivery address</p>
                                        <button class="btn btn-primary" onclick="showAddAddressModal()">
                                            <i class="fas fa-plus"></i> Add Address
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Wishlist Section -->
                <section id="wishlist" class="dashboard-section">
                    <div class="section-header">
                        <h2>My Wishlist</h2>
                        <p>Your saved favorite products</p>
                    </div>
                    
                    <div class="card">
                        <div id="wishlist-items" class="wishlist-container">
                            <div class="loading-spinner" id="wishlist-loading">
                                <i class="fas fa-spinner fa-spin"></i> Loading wishlist...
                            </div>
                            <p class="no-data" id="no-wishlist" style="display: none;">Your wishlist is empty.</p>
                        </div>
                    </div>
                </section>

                <!-- Settings Section -->
                <section id="settings" class="dashboard-section">
                    <div class="section-header">
                        <h2>Account Settings</h2>
                        <p>Manage your account preferences</p>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="settings-grid">
                                <div class="setting-item">
                                    <div class="setting-icon">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div class="setting-content">
                                        <h4>Notifications</h4>
                                        <p>Manage email and SMS notifications</p>
                                        <div class="setting-controls">
                                            <label class="switch">
                                                <input type="checkbox" name="email_notifications" checked>
                                                <span class="slider"></span>
                                                Email notifications for orders
                                            </label>
                                            <label class="switch">
                                                <input type="checkbox" name="sms_notifications">
                                                <span class="slider"></span>
                                                SMS notifications for deliveries
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="setting-item">
                                    <div class="setting-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="setting-content">
                                        <h4>Privacy & Security</h4>
                                        <p>Control your data and privacy settings</p>
                                        <div class="setting-controls">
                                            <button class="btn btn-outline" onclick="downloadData()">
                                                <i class="fas fa-download"></i> Download My Data
                                            </button>
                                            <button class="btn btn-danger" onclick="confirmDeleteAccount()">
                                                <i class="fas fa-trash"></i> Delete Account
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</main>

<!-- Change Password Modal -->
<div id="change-password-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Change Password</h3>
            <button type="button" class="close-btn" onclick="closeChangePasswordModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="change-password-form">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                    <small class="form-help">Password must be at least 8 characters long</small>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeChangePasswordModal()">Cancel</button>
            <button type="submit" form="change-password-form" class="btn btn-primary">
                <i class="fas fa-key"></i> Change Password
            </button>
        </div>
    </div>
</div>

<!-- Additional CSS for Customer Dashboard -->
<style>
/* Customer Dashboard Specific Styles */
.customer-dashboard-main {
    background: var(--light-bg);
    min-height: 100vh;
    padding-top: 2rem;
    padding-bottom: 2rem;
}

.dashboard-layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
}

.dashboard-sidebar {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    height: fit-content;
    box-shadow: var(--shadow);
    position: sticky;
    top: 2rem;
}

.user-profile {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border-color);
}

.user-avatar i {
    font-size: 4rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.user-info h3 {
    margin: 0 0 0.5rem;
    font-weight: 600;
    color: var(--dark-text);
}

.user-info p {
    color: var(--light-text);
    margin: 0;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.dashboard-nav .nav-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    color: var(--dark-text);
    text-decoration: none;
    margin-bottom: 0.5rem;
    transition: var(--transition);
    border: 1px solid transparent;
}

.dashboard-nav .nav-item:hover {
    background: var(--light-bg);
    border-color: var(--primary-color);
}

.dashboard-nav .nav-item.active {
    background: var(--gradient-primary);
    color: white;
    box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
}

.dashboard-nav .nav-item.logout:hover {
    background: var(--gradient-danger);
    color: white;
    border-color: #dc2626;
}

.dashboard-nav .nav-item i {
    width: 20px;
    text-align: center;
    font-size: 1.1rem;
}

.dashboard-content {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--shadow);
    min-height: 600px;
}

.dashboard-section {
    display: none;
}

.dashboard-section.active {
    display: block;
    animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.section-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.section-header h2 {
    margin: 0 0 0.5rem;
    font-weight: 600;
    color: var(--dark-text);
    font-size: 1.75rem;
}

.section-header p {
    color: var(--light-text);
    margin: 0;
    font-size: 1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--gradient-primary);
    color: white;
    padding: 2rem;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.stat-card:nth-child(2) {
    background: var(--gradient-secondary);
}

.stat-card:nth-child(3) {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.stat-card:nth-child(4) {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.stat-icon i {
    font-size: 2.5rem;
    opacity: 0.9;
}

.stat-info h3 {
    margin: 0 0 0.25rem;
    font-size: 2.25rem;
    font-weight: 700;
}

.stat-info p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.95rem;
}

/* Form Sections */
.form-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border-color);
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.form-section h3 {
    color: var(--dark-text);
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.form-section h3 i {
    color: var(--primary-color);
    font-size: 1.1rem;
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
}

.form-group label {
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--dark-text);
    font-size: 0.95rem;
}

.form-control {
    padding: 0.875rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 1rem;
    font-family: inherit;
    transition: var(--transition);
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
}

.form-control[readonly] {
    background: var(--light-bg);
    cursor: not-allowed;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.form-help {
    font-size: 0.875rem;
    color: var(--light-text);
    margin-top: 0.25rem;
}

.form-actions {
    margin-top: 2rem;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

/* Address Management */
.addresses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.address-card {
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    transition: var(--transition);
}

.address-card:hover {
    border-color: var(--primary-color);
    box-shadow: var(--shadow);
}

.address-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1rem;
}

.address-header h4 {
    margin: 0;
    color: var(--dark-text);
    font-weight: 600;
}

.address-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.address-badge.default {
    background: var(--gradient-secondary);
    color: white;
}

.address-content p {
    margin: 0 0 0.5rem;
    color: var(--dark-text);
}

.address-content p:last-child {
    margin-bottom: 0;
    color: var(--light-text);
}

.address-actions {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.add-address-card {
    border: 2px dashed var(--border-color);
    border-radius: var(--border-radius);
    padding: 2rem;
    text-align: center;
    transition: var(--transition);
}

.add-address-card:hover {
    border-color: var(--primary-color);
    background: var(--light-bg);
}

.add-address-content i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.add-address-content h4 {
    margin: 0 0 0.5rem;
    color: var(--dark-text);
}

.add-address-content p {
    margin: 0 0 1.5rem;
    color: var(--light-text);
}

/* Settings Grid */
.settings-grid {
    display: grid;
    gap: 2rem;
}

.setting-item {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.setting-item:hover {
    border-color: var(--primary-color);
    box-shadow: var(--shadow);
}

.setting-icon {
    flex-shrink: 0;
    width: 50px;
    height: 50px;
    background: var(--gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.setting-content h4 {
    margin: 0 0 0.5rem;
    color: var(--dark-text);
    font-weight: 600;
}

.setting-content p {
    margin: 0 0 1rem;
    color: var(--light-text);
}

.setting-controls {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Switch Toggle */
.switch {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
    font-size: 0.95rem;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: relative;
    width: 50px;
    height: 24px;
    background-color: #ccc;
    border-radius: 24px;
    transition: var(--transition);
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: var(--transition);
}

input:checked + .slider {
    background-color: var(--primary-color);
}

input:checked + .slider:before {
    transform: translateX(26px);
}

/* Loading States */
.loading-spinner {
    text-align: center;
    padding: 2rem;
    color: var(--light-text);
}

.loading-spinner i {
    font-size: 1.5rem;
    margin-right: 0.5rem;
}

/* Orders and Wishlist Containers */
.orders-container,
.wishlist-container {
    padding: 1.5rem;
}

.no-data {
    text-align: center;
    color: var(--light-text);
    font-style: italic;
    padding: 3rem;
    font-size: 1.1rem;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .dashboard-layout {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .dashboard-sidebar {
        position: static;
        order: 2;
    }
    
    .dashboard-content {
        order: 1;
    }
}

@media (max-width: 768px) {
    .customer-dashboard-main {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    
    .dashboard-layout {
        padding: 0 0.5rem;
    }
    
    .dashboard-sidebar,
    .dashboard-content {
        padding: 1.5rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stat-card {
        padding: 1.5rem;
    }
    
    .addresses-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Customer Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initializeDashboard();
    loadDashboardData();
});

// Dashboard navigation
function initializeDashboard() {
    document.querySelectorAll('.nav-item[data-section]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all nav items and sections
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            document.querySelectorAll('.dashboard-section').forEach(section => section.classList.remove('active'));
            
            // Add active class to clicked item and corresponding section
            this.classList.add('active');
            const sectionId = this.getAttribute('data-section');
            const section = document.getElementById(sectionId);
            if (section) {
                section.classList.add('active');
                
                // Load section-specific data
                loadSectionData(sectionId);
            }
        });
    });
}

// Load dashboard statistics
async function loadDashboardData() {
    try {
        // Load basic stats
        loadDashboardStats();
        
        // Load initial section data
        loadSectionData('overview');
    } catch (error) {
        console.error('Failed to load dashboard data:', error);
        showNotification('Failed to load dashboard data', 'error');
    }
}

function loadDashboardStats() {
    // Set initial values - these would be loaded from API in real implementation
    document.getElementById('total-orders').textContent = '0';
    document.getElementById('wishlist-count').textContent = '0';
    document.getElementById('total-spent').textContent = 'Rs. 0';
    document.getElementById('pending-orders').textContent = '0';
    
    // TODO: Implement API calls to get real data
    // fetchCustomerStats().then(updateStats);
}

// Load section-specific data
async function loadSectionData(sectionId) {
    switch(sectionId) {
        case 'orders':
            await loadOrderHistory();
            break;
        case 'wishlist':
            await loadWishlist();
            break;
        case 'addresses':
            // Address data is already loaded from PHP
            break;
        default:
            // No specific loading needed
            break;
    }
}

// Load order history
async function loadOrderHistory() {
    const loading = document.getElementById('orders-loading');
    const noOrders = document.getElementById('no-orders');
    const ordersList = document.getElementById('orders-list');
    
    try {
        loading.style.display = 'block';
        noOrders.style.display = 'none';
        
        const response = await fetch('/api/orders/customer-orders.php', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.orders && data.orders.length > 0) {
            displayOrders(data.orders);
            loading.style.display = 'none';
            noOrders.style.display = 'none';
        } else {
            loading.style.display = 'none';
            noOrders.style.display = 'block';
            noOrders.textContent = 'No orders found.';
        }
        
    } catch (error) {
        console.error('Failed to load orders:', error);
        showNotification('Failed to load orders', 'error');
        loading.style.display = 'none';
        noOrders.style.display = 'block';
        noOrders.textContent = 'Failed to load orders. Please try again.';
    }
}

// Load wishlist
async function loadWishlist() {
    const loading = document.getElementById('wishlist-loading');
    const noWishlist = document.getElementById('no-wishlist');
    
    try {
        loading.style.display = 'block';
        noWishlist.style.display = 'none';
        
        // TODO: Implement API call to get wishlist
        // const response = await fetch('api/customer-wishlist.php');
        // const data = await response.json();
        
        // Simulate loading delay
        setTimeout(() => {
            loading.style.display = 'none';
            noWishlist.style.display = 'block';
        }, 1000);
        
    } catch (error) {
        console.error('Failed to load wishlist:', error);
        loading.style.display = 'none';
        noWishlist.style.display = 'block';
        noWishlist.textContent = 'Failed to load wishlist. Please try again.';
    }
}

// Profile form submission
document.getElementById('profile-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    try {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        submitBtn.disabled = true;
        
        const response = await fetch('api/users/update-profile.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Profile updated successfully!', 'success');
            
            // Update sidebar display
            const profileName = document.querySelector('.user-name');
            if (profileName) {
                profileName.textContent = `${formData.get('first_name')} ${formData.get('last_name')}`;
            }
        } else {
            showNotification(result.message || 'Failed to update profile', 'error');
        }
    } catch (error) {
        console.error('Profile update error:', error);
        showNotification('Failed to update profile. Please try again.', 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// Change password functionality
function showChangePasswordModal() {
    document.getElementById('change-password-modal').style.display = 'flex';
}

function closeChangePasswordModal() {
    document.getElementById('change-password-modal').style.display = 'none';
    document.getElementById('change-password-form').reset();
}

// Change password form submission
document.getElementById('change-password-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const newPassword = formData.get('new_password');
    const confirmPassword = formData.get('confirm_password');
    
    if (newPassword !== confirmPassword) {
        showNotification('Passwords do not match', 'error');
        return;
    }
    
    if (newPassword.length < 8) {
        showNotification('Password must be at least 8 characters long', 'error');
        return;
    }
    
    try {
        const response = await fetch(getApiPath() + 'users/change-password.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Password changed successfully!', 'success');
            closeChangePasswordModal();
        } else {
            showNotification(result.message || 'Failed to change password', 'error');
        }
    } catch (error) {
        console.error('Change password error:', error);
        showNotification('Failed to change password. Please try again.', 'error');
    }
});

// Address management functions
function editAddress(addressId) {
    // TODO: Implement address editing
    showNotification('Address editing feature coming soon!', 'info');
}

function showAddAddressModal() {
    // TODO: Implement add address modal
    showNotification('Add address feature coming soon!', 'info');
}

// Settings functions
function downloadData() {
    showNotification('Data download feature coming soon!', 'info');
}

function confirmDeleteAccount() {
    if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
        showNotification('Account deletion feature coming soon!', 'info');
    }
}

// Logout confirmation
function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}

// Notification helper function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
    
    // Add click to dismiss
    notification.addEventListener('click', () => {
        notification.remove();
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});
</script>

<?php include_footer(); ?>

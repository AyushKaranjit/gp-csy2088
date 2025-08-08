<?php
// Start session and include configuration
session_start();
require_once '../template/config.php';
require_once '../src/Controllers/AuthController.php';

// Check if user is logged in
$auth = new AuthController();

// Handle logout first
if (isset($_GET['logout'])) {
    $auth->logout();
    header('Location: index.php');
    exit;
}

if (!$auth->isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode('customer.php'));
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

// Customer dashboard content
$page_title = 'My Account - DOKO';
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
<body class="customer-body">
    <!-- Include main header -->
    <?php include '../template/header.php'; ?>

    <!-- Customer Dashboard -->
    <div class="customer-dashboard">
        <div class="container">
            <div class="dashboard-layout">
                <!-- Sidebar -->
                <aside class="dashboard-sidebar">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="user-info">
                            <h3><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h3>
                            <p><?php echo htmlspecialchars($currentUser['email']); ?></p>
                            <span class="user-role-badge customer">Customer</span>
                        </div>
                    </div>
                    
                    <nav class="dashboard-nav">
                        <a href="#overview" class="nav-item active" data-section="overview">
                            <i class="fas fa-chart-pie"></i> Overview
                        </a>
                        <a href="#orders" class="nav-item" data-section="orders">
                            <i class="fas fa-shopping-bag"></i> My Orders
                        </a>
                        <a href="#wishlist" class="nav-item" data-section="wishlist">
                            <i class="fas fa-heart"></i> Wishlist
                        </a>
                        <a href="#addresses" class="nav-item" data-section="addresses">
                            <i class="fas fa-map-marker-alt"></i> Addresses
                        </a>
                        <a href="#profile" class="nav-item" data-section="profile">
                            <i class="fas fa-user-edit"></i> Profile Settings
                        </a>
                        <a href="?logout=1" class="nav-item logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </aside>

                <!-- Main Content -->
                <main class="dashboard-content">
                    <!-- Overview Section -->
                    <section id="overview" class="dashboard-section active">
                        <div class="section-header">
                            <h2>Account Overview</h2>
                            <p>Welcome back, <?php echo htmlspecialchars($currentUser['first_name']); ?>! Here's what's happening with your account.</p>
                        </div>

                        <div class="overview-stats">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="stat-info">
                                    <h4>Total Orders</h4>
                                    <span class="stat-number" id="total-orders">0</span>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-rupee-sign"></i>
                                </div>
                                <div class="stat-info">
                                    <h4>Total Spent</h4>
                                    <span class="stat-number" id="total-spent">Rs. 0</span>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="stat-info">
                                    <h4>Wishlist Items</h4>
                                    <span class="stat-number" id="wishlist-count">0</span>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="stat-info">
                                    <h4>Reviews Given</h4>
                                    <span class="stat-number" id="reviews-count">0</span>
                                </div>
                            </div>
                        </div>

                        <div class="quick-actions">
                            <h3>Quick Actions</h3>
                            <div class="action-buttons">
                                <a href="products.php" class="action-btn">
                                    <i class="fas fa-shopping-cart"></i>
                                    Continue Shopping
                                </a>
                                <a href="#orders" class="action-btn" onclick="showSection('orders')">
                                    <i class="fas fa-history"></i>
                                    View Order History
                                </a>
                                <a href="#wishlist" class="action-btn" onclick="showSection('wishlist')">
                                    <i class="fas fa-heart"></i>
                                    My Wishlist
                                </a>
                                <a href="#profile" class="action-btn" onclick="showSection('profile')">
                                    <i class="fas fa-user-edit"></i>
                                    Update Profile
                                </a>
                            </div>
                        </div>
                    </section>

                    <!-- Orders Section -->
                    <section id="orders" class="dashboard-section">
                        <div class="section-header">
                            <h2>My Orders</h2>
                            <p>Track your order history and current orders</p>
                        </div>

                        <div class="orders-container">
                            <div class="empty-state">
                                <i class="fas fa-shopping-bag"></i>
                                <h3>No orders yet</h3>
                                <p>When you place orders, they'll appear here</p>
                                <a href="products.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                        </div>
                    </section>

                    <!-- Wishlist Section -->
                    <section id="wishlist" class="dashboard-section">
                        <div class="section-header">
                            <h2>My Wishlist</h2>
                            <p>Items you've saved for later</p>
                        </div>

                        <div class="wishlist-container">
                            <div class="empty-state">
                                <i class="fas fa-heart"></i>
                                <h3>Your wishlist is empty</h3>
                                <p>Save items you love to buy them later</p>
                                <a href="products.php" class="btn btn-primary">Browse Products</a>
                            </div>
                        </div>
                    </section>

                    <!-- Addresses Section -->
                    <section id="addresses" class="dashboard-section">
                        <div class="section-header">
                            <h2>My Addresses</h2>
                            <button class="btn btn-primary" onclick="showAddAddressModal()">
                                <i class="fas fa-plus"></i> Add New Address
                            </button>
                        </div>

                        <div class="addresses-container">
                            <div class="empty-state">
                                <i class="fas fa-map-marker-alt"></i>
                                <h3>No addresses added</h3>
                                <p>Add delivery addresses for faster checkout</p>
                                <button class="btn btn-primary" onclick="showAddAddressModal()">Add Address</button>
                            </div>
                        </div>
                    </section>

                    <!-- Profile Section -->
                    <section id="profile" class="dashboard-section">
                        <div class="section-header">
                            <h2>Profile Settings</h2>
                            <p>Manage your personal information</p>
                        </div>

                        <div class="profile-form-container">
                            <form id="profile-form" class="profile-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="first-name">First Name</label>
                                        <input type="text" id="first-name" name="first_name" value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" class="form-control" autocomplete="given-name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="last-name">Last Name</label>
                                        <input type="text" id="last-name" name="last_name" value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" class="form-control" autocomplete="family-name" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" class="form-control" autocomplete="email" readonly required>
                                        <small class="form-help">Email cannot be changed. Contact support if needed.</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" placeholder="+977-9801234567" class="form-control" autocomplete="tel" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="date-of-birth">Date of Birth</label>
                                        <input type="date" id="date-of-birth" name="date_of_birth" class="form-control" autocomplete="bday" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="gender">Gender</label>
                                        <select id="gender" name="gender" class="form-control">
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                    <button type="button" class="btn btn-secondary" onclick="showChangePasswordModal()">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <!-- Include main footer -->
    <?php include '../template/footer.php'; ?>

    <style>
    /* Customer Dashboard Styling */
    .customer-body {
        font-family: 'Inter', sans-serif;
        background: #f8fafc;
    }

    .customer-dashboard {
        min-height: 80vh;
        padding: 2rem 0;
    }

    .dashboard-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .dashboard-sidebar {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        height: fit-content;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .user-profile {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .user-avatar {
        font-size: 4rem;
        color: #667eea;
        margin-bottom: 1rem;
    }

    .user-info h3 {
        margin: 0 0 0.5rem 0;
        font-size: 1.2rem;
        font-weight: 600;
        color: #1a202c;
    }

    .user-info p {
        margin: 0 0 1rem 0;
        color: #64748b;
        font-size: 0.875rem;
    }

    .user-role-badge {
        background: #3b82f6;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
    }

    .user-role-badge.customer {
        background: #10b981;
    }

    .dashboard-nav {
        display: flex;
        flex-direction: column;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        color: #64748b;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
        margin-bottom: 0.25rem;
    }

    .nav-item:hover,
    .nav-item.active {
        background: #f1f5f9;
        color: #1a202c;
    }

    .nav-item.logout {
        color: #dc2626;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
    }

    .nav-item.logout:hover {
        background: #fee2e2;
    }

    .dashboard-content {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .dashboard-section {
        display: none;
    }

    .dashboard-section.active {
        display: block;
    }

    .section-header {
        margin-bottom: 2rem;
    }

    .section-header h2 {
        margin: 0 0 0.5rem 0;
        font-size: 2rem;
        font-weight: 600;
        color: #1a202c;
    }

    .section-header p {
        margin: 0;
        color: #64748b;
    }

    .overview-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: #f8fafc;
        padding: 1.5rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon {
        background: #667eea;
        color: white;
        width: 3rem;
        height: 3rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .stat-info h4 {
        margin: 0 0 0.5rem 0;
        font-size: 0.875rem;
        font-weight: 500;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1a202c;
    }

    .quick-actions h3 {
        margin-bottom: 1rem;
        font-size: 1.25rem;
        font-weight: 600;
        color: #1a202c;
    }

    .action-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        text-decoration: none;
        color: #374151;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #64748b;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h3 {
        margin-bottom: 0.5rem;
        color: #374151;
    }

    .profile-form {
        max-width: 600px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #374151;
    }

    .form-control {
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-control[readonly] {
        background: #f9fafb;
        color: #6b7280;
    }

    .form-help {
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-primary:hover {
        background: #5a6fd8;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-layout {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .dashboard-sidebar {
            order: 2;
        }

        .dashboard-content {
            order: 1;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .overview-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .action-buttons {
            grid-template-columns: 1fr;
        }
    }
    </style>

    <script>
    // Customer Dashboard JavaScript
    class CustomerAPI {
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
                console.error('Customer API Error:', error);
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
    document.querySelectorAll('.nav-item[data-section]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const sectionId = this.getAttribute('data-section');
            showSection(sectionId);
        });
    });

    // Show section function
    function showSection(sectionId) {
        // Remove active class from all nav items and sections
        document.querySelectorAll('.nav-item').forEach(l => l.classList.remove('active'));
        document.querySelectorAll('.dashboard-section').forEach(s => s.classList.remove('active'));
        
        // Add active class to clicked nav item and corresponding section
        document.querySelector(`[data-section="${sectionId}"]`).classList.add('active');
        document.getElementById(sectionId).classList.add('active');
        
        // Load section data if needed
        loadSectionData(sectionId);
    }

    // Load section-specific data
    function loadSectionData(sectionId) {
        switch (sectionId) {
            case 'overview':
                loadCustomerOverview();
                break;
            case 'orders':
                loadCustomerOrders();
                break;
            case 'wishlist':
                loadCustomerWishlist();
                break;
            case 'addresses':
                loadCustomerAddresses();
                break;
        }
    }

    // Load customer overview data
    function loadCustomerOverview() {
        // Mock data for now
        document.getElementById('total-orders').textContent = '0';
        document.getElementById('total-spent').textContent = 'Rs. 0';
        document.getElementById('wishlist-count').textContent = '0';
        document.getElementById('reviews-count').textContent = '0';
    }

    function loadCustomerOrders() {
        // Orders will be loaded here
        console.log('Loading customer orders...');
    }

    function loadCustomerWishlist() {
        // Wishlist will be loaded here
        console.log('Loading customer wishlist...');
    }

    function loadCustomerAddresses() {
        // Addresses will be loaded here
        console.log('Loading customer addresses...');
    }

    function showAddAddressModal() {
        // Create address modal dynamically
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.id = 'addAddressModal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add New Address</h3>
                    <button type="button" class="close-btn" onclick="hideAddAddressModal()">&times;</button>
                </div>
                <form id="addAddressForm" class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="address-label">Address Label *</label>
                            <input type="text" id="address-label" name="label" placeholder="Home, Work, etc." required class="form-control" autocomplete="address-line1">
                        </div>
                        <div class="form-group">
                            <label for="address-type">Address Type</label>
                            <select id="address-type" name="type" class="form-control">
                                <option value="home">Home</option>
                                <option value="work">Work</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="full-address">Full Address *</label>
                        <textarea id="full-address" name="address" rows="3" required class="form-control" placeholder="Enter complete address"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="address-city">City *</label>
                            <input type="text" id="address-city" name="city" required class="form-control" autocomplete="address-level2">
                        </div>
                        <div class="form-group">
                            <label for="address-postal">Postal Code</label>
                            <input type="text" id="address-postal" name="postal_code" class="form-control" autocomplete="postal-code">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="is_default" name="is_default" autocomplete="off">
                            <span>Set as default address</span>
                        </label>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideAddAddressModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitAddAddress()">Add Address</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.style.display = 'flex';
    }

    function hideAddAddressModal() {
        const modal = document.getElementById('addAddressModal');
        if (modal) {
            modal.remove();
        }
    }

    async function submitAddAddress() {
        const form = document.getElementById('addAddressForm');
        const formData = new FormData(form);
        
        const addressData = {
            label: formData.get('label'),
            type: formData.get('type'),
            address: formData.get('address'),
            city: formData.get('city'),
            postal_code: formData.get('postal_code'),
            is_default: formData.has('is_default')
        };

        if (!addressData.label || !addressData.address || !addressData.city) {
            CustomerAPI.showNotification('Please fill in all required fields', 'error');
            return;
        }

        try {
            CustomerAPI.showNotification('Adding address...', 'info');
            
            // For now, simulate success and store in localStorage
            const addresses = JSON.parse(localStorage.getItem('doko_addresses') || '[]');
            addresses.push({
                id: Date.now(),
                ...addressData,
                created_at: new Date().toISOString()
            });
            localStorage.setItem('doko_addresses', JSON.stringify(addresses));
            
            CustomerAPI.showNotification('Address added successfully!', 'success');
            hideAddAddressModal();
            loadAddresses();
        } catch (error) {
            console.error('Add address error:', error);
            CustomerAPI.showNotification('Failed to add address', 'error');
        }
    }

    // Load and render stored addresses (currently from localStorage placeholder)
    function loadAddresses() {
        const container = document.querySelector('.addresses-container');
        if (!container) return;
        const addresses = JSON.parse(localStorage.getItem('doko_addresses') || '[]');
        if (!addresses.length) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>No addresses added</h3>
                    <p>Add delivery addresses for faster checkout</p>
                    <button class="btn btn-primary" onclick="showAddAddressModal()">Add Address</button>
                </div>`;
            return;
        }
        const listHtml = addresses.map(a => `
            <div class="address-card" data-id="${a.id}">
                <div class="address-header">
                    <h4>${escapeHtml(a.label || 'Address')}</h4>
                    ${a.is_default ? '<span class="badge badge-default">Default</span>' : ''}
                </div>
                <div class="address-body">
                    <p>${escapeHtml(a.address)}</p>
                    <p>${escapeHtml(a.city)} ${a.postal_code ? ', ' + escapeHtml(a.postal_code) : ''}</p>
                    <p class="address-meta">Added: ${new Date(a.created_at).toLocaleDateString()}</p>
                </div>
                <div class="address-actions">
                    <button class="btn btn-sm" onclick="editAddress(${a.id})"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteAddress(${a.id})"><i class="fas fa-trash"></i> Delete</button>
                    ${!a.is_default ? `<button class="btn btn-sm" onclick="setDefaultAddress(${a.id})"><i class="fas fa-star"></i> Set Default</button>` : ''}
                </div>
            </div>`).join('');
        container.innerHTML = `<div class="address-list">${listHtml}</div>`;
    }

    // Helper functions for address actions (local placeholders)
    function saveAddresses(list) {
        localStorage.setItem('doko_addresses', JSON.stringify(list));
    }
    function findAddressIndex(list, id) { return list.findIndex(a => a.id === id); }
    function deleteAddress(id) {
        const list = JSON.parse(localStorage.getItem('doko_addresses') || '[]');
        const idx = findAddressIndex(list, id);
        if (idx === -1) return;
        list.splice(idx, 1);
        saveAddresses(list);
        CustomerAPI.showNotification('Address deleted', 'success');
        loadAddresses();
    }
    function setDefaultAddress(id) {
        const list = JSON.parse(localStorage.getItem('doko_addresses') || '[]');
        list.forEach(a => a.is_default = (a.id === id));
        saveAddresses(list);
        CustomerAPI.showNotification('Default address updated', 'success');
        loadAddresses();
    }
    function editAddress(id) {
        CustomerAPI.showNotification('Edit address not implemented yet', 'info');
    }
    function escapeHtml(str) {
        return String(str).replace(/[&<>"]?/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]||c));
    }

    // Initial load
    document.addEventListener('DOMContentLoaded', loadAddresses);

    function showChangePasswordModal() {
        // Create password change modal
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.id = 'changePasswordModal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Change Password</h3>
                    <button type="button" class="close-btn" onclick="hideChangePasswordModal()">&times;</button>
                </div>
                <form id="changePasswordForm" class="modal-body">
                    <div class="form-group">
                        <label for="current-password">Current Password *</label>
                        <input type="password" id="current-password" name="current_password" required class="form-control" autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label for="new-password">New Password *</label>
                        <input type="password" id="new-password" name="new_password" required class="form-control" minlength="6" autocomplete="new-password">
                        <small class="form-help">Password must be at least 6 characters long</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirm New Password *</label>
                        <input type="password" id="confirm-password" name="confirm_password" required class="form-control" autocomplete="new-password">
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideChangePasswordModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitChangePassword()">Change Password</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.style.display = 'flex';
    }

    function hideChangePasswordModal() {
        const modal = document.getElementById('changePasswordModal');
        if (modal) {
            modal.remove();
        }
    }

    async function submitChangePassword() {
        const form = document.getElementById('changePasswordForm');
        const formData = new FormData(form);
        
        const passwordData = {
            current_password: formData.get('current_password'),
            new_password: formData.get('new_password'),
            confirm_password: formData.get('confirm_password')
        };

        if (!passwordData.current_password || !passwordData.new_password || !passwordData.confirm_password) {
            CustomerAPI.showNotification('Please fill in all fields', 'error');
            return;
        }

        if (passwordData.new_password !== passwordData.confirm_password) {
            CustomerAPI.showNotification('New passwords do not match', 'error');
            return;
        }

        if (passwordData.new_password.length < 6) {
            CustomerAPI.showNotification('Password must be at least 6 characters long', 'error');
            return;
        }

        try {
            CustomerAPI.showNotification('Changing password...', 'info');
            
            // Call API to change password
            const response = await fetch('api/users/change-password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(passwordData)
            });

            const result = await response.json();

            if (result.success) {
                CustomerAPI.showNotification('Password changed successfully!', 'success');
                hideChangePasswordModal();
            } else {
                CustomerAPI.showNotification(result.message || 'Failed to change password', 'error');
            }
        } catch (error) {
            console.error('Change password error:', error);
            CustomerAPI.showNotification('Failed to change password', 'error');
        }
    }

    // Profile form submission
    document.getElementById('profile-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const updateData = {
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            address: formData.get('address')
        };

        // Basic validation
        if (!updateData.first_name || !updateData.last_name || !updateData.email) {
            CustomerAPI.showNotification('Please fill in all required fields', 'error');
            return;
        }

        if (!isValidEmail(updateData.email)) {
            CustomerAPI.showNotification('Please enter a valid email address', 'error');
            return;
        }

        try {
            CustomerAPI.showNotification('Updating profile...', 'info');
            
            const response = await fetch('profile.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                CustomerAPI.showNotification('Profile updated successfully!', 'success');
                
                // Update displayed name in sidebar
                const profileName = document.querySelector('.profile-name');
                if (profileName) {
                    profileName.textContent = `${updateData.first_name} ${updateData.last_name}`;
                }
            } else {
                CustomerAPI.showNotification(result.message || 'Failed to update profile', 'error');
            }
        } catch (error) {
            console.error('Profile update error:', error);
            CustomerAPI.showNotification('Failed to update profile', 'error');
        }
    });

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Initialize customer dashboard
    document.addEventListener('DOMContentLoaded', function() {
        loadCustomerOverview();
        CustomerAPI.showNotification('Welcome to your DOKO account!', 'success');
    });

    // CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .notification { animation: slideIn 0.3s ease-out; }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>

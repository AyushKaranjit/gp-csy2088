<?php
// Start session and include configuration
session_start();
require_once '../template/config.php';
require_once '../src/Controllers/AuthController.php';

// Check if user is logged in
$auth = new AuthController();

if (!$auth->isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode('profile.php'));
    exit;
}

// Get current user info
$currentUser = $auth->getCurrentUser();
$userRole = $auth->getUserRole();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $updateData = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? '')
        ];
        
        // Basic validation
        if (empty($updateData['first_name']) || empty($updateData['last_name']) || empty($updateData['email'])) {
            throw new Exception('Please fill all required fields');
        }
        
        if (!filter_var($updateData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }
        
        // Update profile in database
        require_once '../config/database.php';
        $database = Database::getInstance();
        $pdo = $database->getConnection();
        
        $stmt = $pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, updated_at = NOW() WHERE user_id = ?');
        $result = $stmt->execute([
            $updateData['first_name'],
            $updateData['last_name'], 
            $updateData['email'],
            $updateData['phone'],
            $updateData['address'],
            $currentUser['user_id']
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            throw new Exception('Failed to update profile');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Page-specific variables
$page_title = page_title('My Profile');
$page_description = 'Manage your DOKO account profile, personal information and preferences.';
$current_page = 'profile';

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php 
$breadcrumb_items = [
    ['name' => 'Home', 'url' => 'index.php'],
    ['name' => 'My Profile', 'url' => '', 'active' => true]
];
include '../template/breadcrumb.php'; 
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="profile-layout">
            <!-- Profile Sidebar -->
            <aside class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h3>
                    <p class="profile-role"><?php echo ucfirst($userRole); ?></p>
                    
                    <nav class="profile-nav">
                        <a href="#profile-info" class="profile-nav-item active" data-section="profile-info">
                            <i class="fas fa-user-edit"></i> Personal Information
                        </a>
                        <a href="#order-history" class="profile-nav-item" data-section="order-history">
                            <i class="fas fa-shopping-bag"></i> Order History
                        </a>
                        <a href="#address-book" class="profile-nav-item" data-section="address-book">
                            <i class="fas fa-map-marker-alt"></i> Address Book
                        </a>
                        <a href="#security" class="profile-nav-item" data-section="security">
                            <i class="fas fa-shield-alt"></i> Security Settings
                        </a>
                        <?php if ($userRole === 'admin'): ?>
                            <a href="admin.php" class="profile-nav-item">
                                <i class="fas fa-cogs"></i> Admin Panel
                            </a>
                        <?php endif; ?>
                        <a href="?logout=1" class="profile-nav-item logout" onclick="return confirm('Are you sure you want to logout?')">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </div>
            </aside>

            <!-- Profile Content -->
            <div class="profile-content">
                <!-- Personal Information Section -->
                <section id="profile-info" class="profile-section active">
                    <div class="section-header">
                        <h2>Personal Information</h2>
                        <p>Update your personal details and contact information</p>
                    </div>
                    
                    <div class="profile-form-container">
                        <form id="profile-form" class="profile-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" autocomplete="given-name" required>
                                           value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" autocomplete="family-name" required>
                                           value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-control" autocomplete="email" required>
                                           value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" autocomplete="tel" required>
                                           value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>" 
                                           placeholder="+977-9851234567">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" class="form-control" rows="3" 
                                          placeholder="Enter your complete address"><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- Order History Section -->
                <section id="order-history" class="profile-section">
                    <div class="section-header">
                        <h2>Order History</h2>
                        <p>Track your past orders and their status</p>
                    </div>
                    
                    <div class="orders-container" id="orders-container">
                        <div class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i> Loading orders...
                        </div>
                    </div>
                </section>

                <!-- Address Book Section -->
                <section id="address-book" class="profile-section">
                    <div class="section-header">
                        <h2>Address Book</h2>
                        <p>Manage your delivery addresses</p>
                        <button class="btn btn-primary btn-sm" onclick="showAddAddressModal()">
                            <i class="fas fa-plus"></i> Add New Address
                        </button>
                    </div>
                    
                    <div class="address-list" id="address-list">
                        <!-- Addresses will be loaded here -->
                        <div class="empty-state">
                            <i class="fas fa-map-marker-alt"></i>
                            <h3>No addresses found</h3>
                            <p>Add your first delivery address to get started</p>
                        </div>
                    </div>
                </section>

                <!-- Security Settings Section -->
                <section id="security" class="profile-section">
                    <div class="section-header">
                        <h2>Security Settings</h2>
                        <p>Update your password and security preferences</p>
                    </div>
                    
                    <div class="security-form-container">
                        <form id="password-form" class="security-form">
                            <div class="form-group">
                                <label for="current_password">Current Password *</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password">
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password *</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required autocomplete="new-password">
                                <small class="form-help">Password must be at least 8 characters long</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required autocomplete="new-password">
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-lock"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>
</main>

<style>
.profile-layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.profile-sidebar {
    position: sticky;
    top: 2rem;
    height: fit-content;
}

.profile-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: 1px solid #e0e0e0;
}

.profile-avatar {
    text-align: center;
    margin-bottom: 1rem;
}

.profile-avatar i {
    font-size: 4rem;
    color: var(--primary-color);
}

.profile-card h3 {
    text-align: center;
    margin: 0 0 0.5rem 0;
    color: var(--text-dark);
}

.profile-role {
    text-align: center;
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    text-transform: uppercase;
    font-weight: 600;
}

.profile-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.profile-nav-item {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.profile-nav-item:hover {
    background: var(--light-bg);
    color: var(--primary-color);
    border-color: var(--primary-color);
}

.profile-nav-item.active {
    background: var(--primary-color);
    color: white;
}

.profile-nav-item.logout:hover {
    background: #dc3545;
    color: white;
    border-color: #dc3545;
}

.profile-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: 1px solid #e0e0e0;
}

.profile-section {
    display: none;
    padding: 2rem;
}

.profile-section.active {
    display: block;
}

.section-header {
    border-bottom: 2px solid var(--border-color);
    padding-bottom: 1rem;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-header h2 {
    margin: 0;
    color: var(--primary-color);
}

.section-header p {
    margin: 0.5rem 0 0 0;
    color: var(--text-muted);
}

.profile-form-container {
    max-width: 600px;
}

.profile-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text-dark);
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    font-size: 1rem;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
}

.form-help {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: #357a35;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.loading-spinner {
    text-align: center;
    padding: 2rem;
    color: var(--text-muted);
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.empty-state h3 {
    margin: 1rem 0 0.5rem 0;
    color: var(--text-dark);
}

@media (max-width: 768px) {
    .profile-layout {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .profile-form .form-row {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}
</style>

<script>
// Profile navigation
document.querySelectorAll('.profile-nav-item[data-section]').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Update nav active state
        document.querySelectorAll('.profile-nav-item').forEach(nav => nav.classList.remove('active'));
        this.classList.add('active');
        
        // Update section visibility
        const targetSection = this.getAttribute('data-section');
        document.querySelectorAll('.profile-section').forEach(section => section.classList.remove('active'));
        document.getElementById(targetSection).classList.add('active');
    });
});

// Profile form submission
document.getElementById('profile-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('profile.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('An error occurred. Please try again.', 'error');
    }
    
    submitBtn.innerHTML = originalText;
    submitBtn.disabled = false;
});

// Simple notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle"></i>
        ${message}
        <button onclick="this.parentElement.remove()" class="notification-close">&times;</button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}
</script>

<style>
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 6px;
    color: white;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    z-index: 1000;
    transition: all 0.3s;
    max-width: 400px;
}

.notification-success {
    background: #28a745;
}

.notification-error {
    background: #dc3545;
}

.notification-info {
    background: #17a2b8;
}

.notification-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    margin-left: auto;
}
</style>

<?php
// Handle logout
if (isset($_GET['logout'])) {
    $auth->logout();
    header('Location: index.php');
    exit;
}

// Include footer
require_once '../template/footer.php';
?>

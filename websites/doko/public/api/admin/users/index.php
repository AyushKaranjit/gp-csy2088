<?php
/**
 * Admin Users Management API
 * Handles all user-related admin operations
 */

require_once '../../../config/database.php';
require_once '../../../src/Controllers/AuthController.php';

// Check authentication
$auth = new AuthController();
if (!$auth->isAdmin()) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['api'])) {
        header('Location: ../../../login.php');
        exit;
    } else {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized access']);
        exit;
    }
}

// Handle API requests
if (isset($_GET['api']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
    handleApiRequest();
    exit;
}

// Handle web page request
$currentUser = $auth->getCurrentUser();
$page_title = 'User Management | Admin';
$current_page = 'admin';

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    // Get users with order statistics
    $query = "
        SELECT 
            u.*,
            COUNT(o.order_id) as total_orders,
            COALESCE(SUM(o.total_amount), 0) as total_spent,
            MAX(o.created_at) as last_order_date
        FROM users u
        LEFT JOIN orders o ON u.user_id = o.user_id
        WHERE u.role != 'admin'
        GROUP BY u.user_id
        ORDER BY u.created_at DESC
    ";
    $stmt = $db->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = "Error loading users: " . $e->getMessage();
    $users = [];
}

include '../../../template/admin-header.php';
?>

<!-- Link to main stylesheet for footer and other styles -->
<link rel="stylesheet" href="../css/style.css">

<!-- Immediate admin ready class to prevent flashing -->
<script>
document.documentElement.classList.add('admin-ready');
document.body.classList.add('admin-ready');
</script>

<style>
/* Import Inter font family - must be at the top */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

/* DOKO Traditional Basket Logo Styles for Admin */
.doko-basket-icon {
    width: 48px;
    height: 48px;
    position: relative;
    display: inline-block;
}

.basket-container {
    position: relative;
    width: 100%;
    height: 100%;
}

.basket-body {
    width: 32px;
    height: 24px;
    background: linear-gradient(145deg, #8B4513 0%, #A0522D 30%, #D2691E 70%, #A0522D 100%);
    border-radius: 4px 4px 16px 16px;
    position: absolute;
    top: 14px;
    left: 8px;
    box-shadow: 
        inset 0 2px 0 rgba(210, 105, 30, 0.6),
        inset 0 -2px 4px rgba(101, 67, 33, 0.8),
        0 3px 8px rgba(0,0,0,0.25),
        0 1px 2px rgba(0,0,0,0.15);
    border: 1px solid #654321;
    overflow: hidden;
}

.basket-body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 50%;
    background: linear-gradient(180deg, rgba(210, 105, 30, 0.4) 0%, transparent 100%);
}

.weave-pattern {
    position: absolute;
    width: 28px;
    height: 2px;
    background: linear-gradient(90deg, #654321 0%, #8B4513 20%, #A0522D 50%, #8B4513 80%, #654321 100%);
    border-radius: 1px;
    box-shadow: inset 0 1px 0 rgba(160, 82, 45, 0.5), 0 1px 1px rgba(0,0,0,0.2);
}

.weave-pattern:nth-child(1) { top: 3px; left: 2px; }
.weave-pattern:nth-child(2) { top: 8px; left: 2px; }
.weave-pattern:nth-child(3) { top: 13px; left: 2px; }

.basket-handles {
    position: absolute;
    top: 9px;
    left: 0;
    right: 0;
    height: 18px;
}

.handle-left, .handle-right {
    position: absolute;
    width: 14px;
    height: 18px;
    border: 3px solid #8B4513;
    border-bottom: none;
    border-radius: 14px 14px 0 0;
    background: transparent;
    box-shadow: 
        inset 0 1px 0 rgba(160, 82, 45, 0.6),
        0 2px 4px rgba(0,0,0,0.2);
}

.handle-left {
    left: -7px;
    border-right: none;
    transform: rotate(-12deg);
}

.handle-right {
    right: -7px;
    border-left: none;
    transform: rotate(12deg);
}

/* Hide ALL regular header elements for admin pages */
.header,
.navbar,
.header-main,
.header-actions,
.header-action,
.navigation,
.nav {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    overflow: hidden !important;
    position: absolute !important;
    top: -9999px !important;
}

/* Prevent flash of unstyled content */
body:not(.admin-ready) {
    opacity: 0;
    transition: opacity 0.2s ease;
}

body.admin-ready {
    opacity: 1;
}

/* Hide regular navigation and header actions for admin pages */
.navbar,
.header-actions,
.header-action {
    display: none !important;
}

/* Override font family to match home page */
* {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif !important;
}

/* Adjust body padding since we have fixed admin nav */
body {
    padding-top: 80px !important;
    background: #f8fafc;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
    min-height: 100vh;
}

/* Ensure proper font loading */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

/* Admin container spacing */
.users-container {
    padding-top: 2rem;
}

/* Admin Navigation - Modern Professional Design */
.admin-nav {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    padding: 1rem 0;
    margin-bottom: 0;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.admin-nav .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
}

.admin-nav-brand {
    color: white;
    font-size: 1.25rem;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
}

.admin-nav-brand a {
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 12px;
}

.admin-nav-brand a:hover {
    color: rgba(255,255,255,0.9);
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.admin-nav-links {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.admin-nav-link {
    color: rgba(255,255,255,0.9);
    text-decoration: none;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    overflow: hidden;
    border: 1px solid transparent;
}

.admin-nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.1);
    transition: all 0.3s ease;
}

.admin-nav-link:hover::before {
    left: 0;
}

.admin-nav-link:hover {
    color: white;
    transform: translateY(-1px);
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    border-color: rgba(255,255,255,0.2);
    background: rgba(255,255,255,0.05);
}

.admin-nav-link.active {
    background: rgba(255,255,255,0.15);
    color: white;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-color: rgba(255,255,255,0.3);
}

.admin-nav-link i {
    font-size: 1rem;
    opacity: 0.9;
}

/* Logo text styling to match homepage */
.logo-text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1;
}

.logo-main {
    font-size: 1.5rem;
    font-weight: 800;
    color: white;
    letter-spacing: -0.025em;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.logo-tagline {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.8);
    font-weight: 500;
    letter-spacing: 0.05em;
    margin-top: -2px;
}

/* Ensure footer displays properly with better styling */
.footer {
    margin-top: 4rem !important;
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
    color: white !important;
    padding: 3rem 0 2rem 0 !important;
    border-top: 1px solid rgba(148, 163, 184, 0.2) !important;
}

.footer * {
    color: white !important;
}

.footer a {
    color: rgba(255,255,255,0.8) !important;
    transition: color 0.3s ease !important;
}

.footer a:hover {
    color: #3b82f6 !important;
}

.footer h3 {
    color: white !important;
    font-weight: 600 !important;
}

.footer-content {
    border-bottom: 1px solid rgba(148, 163, 184, 0.2) !important;
    padding-bottom: 2rem !important;
}

.footer-bottom {
    margin-top: 2rem !important;
    padding-top: 2rem !important;
    border-top: 1px solid rgba(148, 163, 184, 0.2) !important;
    text-align: center !important;
}

.footer-bottom p {
    color: rgba(255,255,255,0.8) !important;
    margin: 0 !important;
}

/* Admin Users Styles */
.users-container {
    max-width: 1400px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.users-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.btn-add-user {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 1rem 2rem;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    cursor: pointer;
}

.btn-add-user:hover {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
    transform: translateY(-2px);
}

.users-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.search-box {
    flex: 1;
    max-width: 300px;
}

.search-box input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9rem;
}

.filter-select {
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9rem;
    background: white;
}

.users-table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.table-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 1rem 1.5rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

th {
    background: #f8fafc;
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
}

tbody tr:hover {
    background: #f9fafb;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: #6b7280;
}

.user-details h4 {
    margin: 0;
    font-size: 0.9rem;
    color: #111827;
}

.user-details p {
    margin: 0;
    font-size: 0.8rem;
    color: #6b7280;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active { background: #d1fae5; color: #065f46; }
.status-inactive { background: #f3f4f6; color: #374151; }
.status-suspended { background: #fee2e2; color: #991b1b; }
.status-pending { background: #fef3c7; color: #92400e; }

.role-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.role-customer { background: #dbeafe; color: #1e40af; }
.role-admin { background: #fee2e2; color: #991b1b; }

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    padding: 0.5rem 0.75rem;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 0 2px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
}

.btn-action i {
    font-size: 0.9rem;
}

.btn-edit {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: 1px solid #2563eb;
}

.btn-edit:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-view {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
    border: 1px solid #4b5563;
}

.btn-view:hover {
    background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(107, 114, 128, 0.4);
}

.btn-suspend {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    border: 1px solid #d97706;
}

.btn-suspend:hover {
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.btn-activate {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: 1px solid #059669;
}

.btn-activate:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-archive {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    border: 1px solid #7c3aed;
}

.btn-archive:hover {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
}

.btn-delete:hover {
    background: #dc2626;
}

.stats-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-item {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    text-align: center;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

/* Modal and Form Improvements */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    transform: scale(0.95);
    opacity: 0;
    transition: all 0.3s ease;
}

.modal.active .modal-content {
    transform: scale(1);
    opacity: 1;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: #f3f4f6;
    color: #374151;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.btn-cancel {
    background: #6b7280;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-cancel:hover {
    background: #4b5563;
}

.btn-save {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-save:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .users-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .users-actions {
        flex-direction: column;
    }
    
    .search-box {
        max-width: none;
    }
    
    .table-wrapper {
        font-size: 0.8rem;
    }
    
    th, td {
        padding: 0.75rem;
    }
    
    .user-info {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
}
</style>

<div class="users-container">
    <!-- Users Header -->
    <div class="users-header">
        <div>
            <h1><i class="fas fa-users"></i> User Management</h1>
            <p>Manage customer accounts and permissions</p>
        </div>
        <button class="btn-add-user" onclick="openAddUserModal()">
            <i class="fas fa-user-plus"></i> Add New User
        </button>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Stats Summary -->
    <div class="stats-summary">
        <div class="stat-item">
            <div class="stat-number"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'customer')); ?></div>
            <div class="stat-label">Total Customers</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo count(array_filter($users, fn($u) => $u['status'] === 'active')); ?></div>
            <div class="stat-label">Active Users</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo count(array_filter($users, fn($u) => $u['created_at'] >= date('Y-m-d', strtotime('-30 days')))); ?></div>
            <div class="stat-label">New This Month</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo count(array_filter($users, fn($u) => $u['total_orders'] > 0)); ?></div>
            <div class="stat-label">With Orders</div>
        </div>
    </div>

    <!-- Users Actions -->
    <div class="users-actions">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search users..." autocomplete="off" onkeyup="filterUsers()">
        </div>
        <select class="filter-select" id="statusFilter" autocomplete="off" onchange="filterUsers()">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
            <option value="pending">Pending</option>
        </select>
        <select class="filter-select" id="roleFilter" autocomplete="off" onchange="filterUsers()">
            <option value="">All Roles</option>
            <option value="customer">Customer</option>
        </select>
    </div>

    <!-- Users Table -->
    <div class="users-table">
        <div class="table-header">
            <h3>All Users</h3>
            <span><?php echo count($users); ?> users total</span>
        </div>
        
        <div class="table-wrapper">
            <table id="usersTable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Role</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr data-status="<?php echo $user['status']; ?>" data-role="<?php echo $user['role']; ?>">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                        </div>
                                        <div class="user-details">
                                            <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                            <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div><?php echo htmlspecialchars($user['email']); ?></div>
                                        <?php if ($user['phone']): ?>
                                            <div style="font-size: 0.8rem; color: #6b7280;"><?php echo htmlspecialchars($user['phone']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo $user['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo $user['role']; ?>
                                    </span>
                                </td>
                                <td><?php echo $user['total_orders']; ?></td>
                                <td>NPR <?php echo number_format($user['total_spent'], 0); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-edit" onclick="editUser(<?php echo $user['user_id']; ?>)" title="Edit User Profile">
                                            <i class="fas fa-user-edit"></i>
                                        </button>
                                        <button class="btn-action btn-view" onclick="viewUser(<?php echo $user['user_id']; ?>)" title="View User Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($user['status'] === 'active'): ?>
                                            <button class="btn-action btn-suspend" onclick="changeUserStatus(<?php echo $user['user_id']; ?>, 'suspended')" title="Suspend User Account">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-activate" onclick="changeUserStatus(<?php echo $user['user_id']; ?>, 'active')" title="Activate User Account">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn-action btn-archive" onclick="archiveUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')" title="Archive User Account">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 2rem;">
                                <i class="fas fa-users" style="font-size: 2rem; color: #d1d5db; margin-bottom: 1rem;"></i><br>
                                No users found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Edit User</h3>
            <button class="modal-close" onclick="closeUserModal()">&times;</button>
        </div>
        
        <form id="userForm" onsubmit="saveUser(event)">
            <input type="hidden" id="userId" name="user_id">
            
            <div class="form-group">
                <label for="firstName">First Name *</label>
                <input type="text" id="firstName" name="first_name" autocomplete="given-name" required>
            </div>
            
            <div class="form-group">
                <label for="lastName">Last Name *</label>
                <input type="text" id="lastName" name="last_name" autocomplete="family-name" required>
            </div>
            
            <div class="form-group">
                <label for="userEmail">Email *</label>
                <input type="email" id="userEmail" name="email" autocomplete="email" required>
            </div>
            
            <div class="form-group">
                <label for="userPhone">Phone</label>
                <input type="tel" id="userPhone" name="phone" autocomplete="tel">
            </div>
            
            <div class="form-group" id="usernameGroup" style="display: none;">
                <label for="userUsername">Username *</label>
                <input type="text" id="userUsername" name="username" autocomplete="username">
            </div>
            
            <div class="form-group" id="passwordGroup" style="display: none;">
                <label for="userPassword">Password *</label>
                <input type="password" id="userPassword" name="password" autocomplete="new-password">
            </div>
            
            <div class="form-group">
                <label for="userStatus">Status</label>
                <select id="userStatus" name="status" autocomplete="off">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeUserModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
let allUsers = <?php echo json_encode($users); ?>;

function filterUsers() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const roleFilter = document.getElementById('roleFilter').value;
    
    const userRows = document.querySelectorAll('#usersTable tbody tr');
    
    userRows.forEach(row => {
        if (row.cells.length === 1) return; // Skip "no users" row
        
        const userName = row.cells[0].textContent.toLowerCase();
        const userEmail = row.cells[1].textContent.toLowerCase();
        const userStatus = row.dataset.status;
        const userRole = row.dataset.role;
        
        const matchesSearch = userName.includes(searchTerm) || userEmail.includes(searchTerm);
        const matchesStatus = !statusFilter || userStatus === statusFilter;
        const matchesRole = !roleFilter || userRole === roleFilter;
        
        if (matchesSearch && matchesStatus && matchesRole) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
}

function editUser(userId) {
    const user = allUsers.find(u => u.user_id == userId);
    if (!user) return;
    
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('userId').value = user.user_id;
    document.getElementById('firstName').value = user.first_name;
    document.getElementById('lastName').value = user.last_name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userPhone').value = user.phone || '';
    document.getElementById('userStatus').value = user.status;
    
    // Hide username and password fields for existing users
    document.getElementById('usernameGroup').style.display = 'none';
    document.getElementById('passwordGroup').style.display = 'none';
    document.getElementById('userUsername').required = false;
    document.getElementById('userPassword').required = false;
    
    document.getElementById('userModal').classList.add('active');
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('active');
}

function saveUser(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('userForm'));
    const userId = document.getElementById('userId').value;
    const action = userId ? 'update' : 'add';
    
    formData.append('action', action);
    formData.append('api', '1');
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`User ${action === 'add' ? 'added' : 'updated'} successfully!`);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert(`Error ${action === 'add' ? 'adding' : 'updating'} user: ` + error.message);
    });
}

function changeUserStatus(userId, newStatus) {
    const statusText = newStatus === 'active' ? 'activate' : 'suspend';
    
    if (!confirm(`Are you sure you want to ${statusText} this user?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'change_status');
    formData.append('user_id', userId);
    formData.append('status', newStatus);
    formData.append('api', '1');
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function openAddUserModal() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    
    // Show username and password fields for new users
    document.getElementById('usernameGroup').style.display = 'block';
    document.getElementById('passwordGroup').style.display = 'block';
    document.getElementById('userUsername').required = true;
    document.getElementById('userPassword').required = true;
    
    document.getElementById('userModal').classList.add('active');
}

function viewUser(userId) {
    const user = allUsers.find(u => u.user_id == userId);
    if (!user) return;
    
    // Create a simple modal or redirect to a detailed view
    alert(`User Details:\n\nName: ${user.first_name} ${user.last_name}\nEmail: ${user.email}\nPhone: ${user.phone || 'Not provided'}\nStatus: ${user.status}\nRole: ${user.role}\nTotal Orders: ${user.total_orders}\nTotal Spent: NPR ${parseInt(user.total_spent).toLocaleString()}\nJoined: ${new Date(user.created_at).toLocaleDateString()}`);
}

function archiveUser(userId, userName) {
    if (!confirm(`Are you sure you want to archive "${userName}"?\n\nThis will:\n• Suspend their account\n• Hide them from active user lists\n• Preserve all their data and order history\n• Allow restoration later if needed`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'archive');
    formData.append('user_id', userId);
    formData.append('api', '1');
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User archived successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error archiving user: ' + error.message);
    });
}

// Close modal when clicking outside
document.getElementById('userModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeUserModal();
    }
});
</script>

<?php include '../../../template/footer.php'; ?>

<?php
function handleApiRequest() {
    header('Content-Type: application/json');
    
    try {
        $database = Database::getInstance();
        $db = $database->getConnection();
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'add':
                handleAddUser($db);
                break;
                
            case 'update':
                handleUpdateUser($db);
                break;
                
            case 'change_status':
                handleChangeStatus($db);
                break;
                
            case 'archive':
                handleArchiveUser($db);
                break;
                
            case 'delete':
                handleDeleteUser($db);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleUpdateUser($db) {
    $user_id = $_POST['user_id'] ?? 0;
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $status = $_POST['status'] ?? '';
    
    if (!$user_id || empty($first_name) || empty($last_name) || empty($email)) {
        throw new Exception('User ID, first name, last name, and email are required');
    }
    
    if (!in_array($status, ['active', 'inactive', 'suspended', 'pending'])) {
        throw new Exception('Invalid status');
    }
    
    // Check if email is already used by another user
    $checkQuery = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$email, $user_id]);
    
    if ($checkStmt->rowCount() > 0) {
        throw new Exception('Email is already in use by another user');
    }
    
    $query = "UPDATE users SET first_name=?, last_name=?, email=?, phone=?, status=?, updated_at=NOW() WHERE user_id=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$first_name, $last_name, $email, $phone, $status, $user_id]);
    
    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
}

function handleChangeStatus($db) {
    $user_id = $_POST['user_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if (!$user_id || !in_array($status, ['active', 'inactive', 'suspended', 'pending'])) {
        throw new Exception('Valid user ID and status are required');
    }
    
    $query = "UPDATE users SET status = ?, updated_at = NOW() WHERE user_id = ? AND role != 'admin'";
    $stmt = $db->prepare($query);
    $stmt->execute([$status, $user_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('User not found or cannot modify admin users');
    }
    
    echo json_encode(['success' => true, 'message' => 'User status updated successfully']);
}

function handleDeleteUser($db) {
    $user_id = $_POST['user_id'] ?? 0;
    
    if (!$user_id) {
        throw new Exception('User ID is required');
    }
    
    // Check if user is admin (prevent deletion of admin users)
    $checkQuery = "SELECT role FROM users WHERE user_id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$user_id]);
    $user = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    if ($user['role'] === 'admin') {
        throw new Exception('Cannot delete admin users');
    }
    
    // Delete user (this should cascade to orders and other related data)
    $query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    
    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
}

function handleAddUser($db) {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $role = 'customer'; // Always create as customer
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password)) {
        throw new Exception('First name, last name, email, username, and password are required');
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Check if email or username already exists
    $checkQuery = "SELECT COUNT(*) as count FROM users WHERE email = ? OR username = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$email, $username]);
    $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exists['count'] > 0) {
        throw new Exception('Email or username already exists');
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $query = "INSERT INTO users (first_name, last_name, email, phone, username, password, role, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($query);
    $stmt->execute([$first_name, $last_name, $email, $phone, $username, $hashed_password, $role, $status]);
    
    echo json_encode(['success' => true, 'message' => 'User added successfully']);
}

function handleArchiveUser($db) {
    $user_id = $_POST['user_id'] ?? 0;
    
    if (!$user_id) {
        throw new Exception('User ID is required');
    }
    
    // Check if user exists and is not admin
    $checkQuery = "SELECT role FROM users WHERE user_id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$user_id]);
    $user = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    if ($user['role'] === 'admin') {
        throw new Exception('Cannot archive admin users');
    }
    
    // Archive user by setting status to 'archived' and adding archive timestamp
    $query = "UPDATE users SET status = 'archived', updated_at = NOW() WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    
    echo json_encode(['success' => true, 'message' => 'User archived successfully']);
}
?>

<?php
/**
 * DOKO E-Commerce Website - User Profile Page
 *
 * This website was developed as part of an academic project for educational purposes.
 * All code was written by the student developer to demonstrate web development skills.
 *
 * @author Student Developer
 * @version 1.0
 * @date 2025
 */

// Start session and include configuration
// Set session cookie parameters for better browser compatibility
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => false,
    'samesite' => 'Lax'
]);
session_start();
require_once __DIR__ . '/../template/config.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';

// Check if user is logged in
$auth = new AuthController();

if (!$auth->isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode('profile.php'));
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    $auth->logout();
    header('Location: index.php');
    exit;
}

// Get current user info
$currentUser = $auth->getCurrentUser();
$userRole = $auth->getUserRole();

// Always attempt to read the latest user record from the database so UI shows persisted values
try {
    require_once __DIR__ . '/../config/database.php';
    $database = Database::getInstance();
    $pdo = $database->getConnection();
    if (!empty($currentUser['user_id'])) {
        $stmtUser = $pdo->prepare('SELECT user_id, username, email, first_name, last_name, phone, address, profile_image, role FROM users WHERE user_id = ? LIMIT 1');
        $stmtUser->execute([$currentUser['user_id']]);
        $fresh = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if ($fresh) {
            // Merge fresh DB values into $currentUser and session
            foreach (['user_id','username','email','first_name','last_name','phone','address','profile_image','role'] as $k) {
                if (array_key_exists($k, $fresh) && $fresh[$k] !== null) {
                    $currentUser[$k] = $fresh[$k];
                    $_SESSION[$k] = $fresh[$k];
                }
            }
            $userRole = $currentUser['role'] ?? $userRole;
        }
    }
} catch (Throwable $e) {
    // Non-fatal; continue with session values
}

// Helper function to normalize image paths
function normalizeImagePath($imagePath) {
    if (empty($imagePath)) return '';
    // If it starts with /, remove it for consistency
    if (substr($imagePath, 0, 1) === '/') {
        $imagePath = substr($imagePath, 1);
    }
    // If it doesn't start with uploads/, add it
    if (substr($imagePath, 0, 8) !== 'uploads/') {
        $imagePath = 'uploads/' . $imagePath;
    }
    return '/' . $imagePath;
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
include __DIR__ . '/../template/breadcrumb.php'; 
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="profile-layout">
            <!-- Profile Sidebar -->
            <aside class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <?php if (!empty($currentUser['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars(normalizeImagePath($currentUser['profile_image'])); ?>?t=<?php echo time(); ?>" alt="Profile Picture" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
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
                        <form id="profile-form" class="profile-form" enctype="multipart/form-data" onsubmit="return false;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" autocomplete="given-name" required value="<?php echo htmlspecialchars($currentUser['first_name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" autocomplete="family-name" required value="<?php echo htmlspecialchars($currentUser['last_name']); ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="profile_image">Profile Image</label>
                                    <input type="file" id="profile_image" name="profile_image" class="form-control" accept="image/*">
                                    <small class="form-help">JPG/PNG/WebP up to 2MB</small>
                                </div>
                                <div class="form-group" id="profile-image-preview-wrapper" style="display:flex;align-items:center;gap:1rem;">
                                    <div id="profile-image-preview" style="width:80px;height:80px;border:1px solid #ddd;border-radius:50%;background:#fafafa;background-size:cover;background-position:center;overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:.75rem;color:#666;">
                                        <?php if (!empty($currentUser['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars(normalizeImagePath($currentUser['profile_image'])); ?>?t=<?php echo time(); ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;" />
                                        <?php else: ?>No Image<?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-control" autocomplete="email" required value="<?php echo htmlspecialchars($currentUser['email']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" autocomplete="tel" inputmode="numeric" pattern="^[0-9]+$" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>" placeholder="9851234567">
                                    <small class="form-text">Digits only, e.g. 9851234567</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" class="form-control" rows="3" autocomplete="street-address" placeholder="Enter your complete address"><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
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
                
                                    <script>
                                    // Lightweight order history loader for profile page
                                    (function(){
                                        const container = document.getElementById('orders-container');
                                        if(!container) return;
                                        function fmtDate(ts){ if(!ts) return ''; const d=new Date(ts.replace(' ','T')); return d.toLocaleDateString(undefined,{year:'numeric',month:'short',day:'numeric'}); }
                                        function renderOrders(list){
                                            if(!list || !list.length){ container.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><h3>No orders yet</h3><p>Your placed orders will appear here.</p></div>'; return; }
                                            const rows = list.map(o=>`<div class="order-row">
                                                <div class="order-col id">${o.order_number?('#'+o.order_number):('#'+(o.order_id||o.id))}</div>
                                                    <div class="order-col date">${fmtDate(o.created_at||o.ordered_at)}</div>
                                                    <div class="order-col total">Rs. ${(parseFloat(o.total_amount||o.total||0)).toFixed(2)}</div>
                                                    <div class="order-col status ${ (o.status||'pending').toLowerCase() }">${o.status||'pending'}</div>
                                                    <div class="order-col action"><a class="btn btn-sm btn-primary" href="order-confirmation.php?order_id=${o.order_id||o.id}">View</a></div>
                                            </div>`).join('');
                                            container.innerHTML = `<div class="orders-list">${rows}</div>`;
                                        }
                                        function showLoading(){ container.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Loading orders...</div>'; }
                                        
                                        // Check if logout is in progress
                                        if (window.location.search.includes('logout=1')) {
                                            container.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><h3>Logging out...</h3><p>Please wait...</p></div>';
                                            return;
                                        }
                                        
                                        showLoading();
                                        fetch('api/users/customer-orders.php',{headers:{'X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'})
                                            .then(async (r) => {
                                                if (!r.ok) {
                                                    if (r.status === 401) {
                                                        // Not authenticated
                                                        renderOrders([]);
                                                        console.warn('Order history: not authenticated (401)');
                                                        return;
                                                    }
                                                    const ct = r.headers.get('content-type') || '';
                                                    const text = await r.text().catch(()=>null);
                                                    console.error('Order history load failed', r.status, text);
                                                    renderOrders([]);
                                                    return;
                                                }
                                                const ct = r.headers.get('content-type') || '';
                                                if (!ct.includes('application/json')) {
                                                    const text = await r.text().catch(()=>null);
                                                    console.error('Order history unexpected response', text);
                                                    renderOrders([]);
                                                    return;
                                                }
                                                const j = await r.json().catch(e=>{ console.error('Order history parse error', e); return null; });
                                                if (j && j.success && j.orders) { renderOrders(j.orders); } else { renderOrders([]); }
                                            }).catch(e=>{ console.warn('Order history load failed',e); renderOrders([]); });
                                    })();
                                    </script>
                
                                    <style>
                                    .orders-list { display:flex; flex-direction:column; gap:.75rem; }
                                    /* Make grid items able to shrink; allow long text to wrap safely */
                                    .order-row { display:grid; grid-template-columns: 120px 1fr 120px 120px 100px; gap:.5rem; align-items:center; background:#fff; padding:.75rem 1rem; border:1px solid var(--border-color); border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.06); min-width:0; }
                                    .order-col { min-width:0; }
                                    .order-col.id { font-weight:600; word-break:break-word; overflow-wrap:anywhere; max-width:100%; }
                                    .order-col.total { font-weight:600; color:var(--primary-color); }
                                    .order-col.status { text-transform:capitalize; font-size:.85rem; font-weight:600; padding:.35rem .6rem; border-radius:999px; justify-self:start; background:#eef; }
                                    .order-col.status.pending { background:#fff3cd; color:#946200; }
                                    .order-col.status.processing { background:#cfe2ff; color:#084298; }
                                    .order-col.status.delivered { background:#d1e7dd; color:#0f5132; }
                                    .order-col.status.cancelled { background:#f8d7da; color:#842029; }
                                    @media (max-width:700px){
                                        .order-row { grid-template-columns: 1fr 1fr; grid-template-areas:"id status" "date total" "action action"; }
                                        .order-col.id{grid-area:id;} .order-col.status{grid-area:status;} .order-col.date{grid-area:date;} .order-col.total{grid-area:total;} .order-col.action{grid-area:action;}
                                        .order-col.action { justify-self:end; }
                                        .order-col.action .btn { white-space: nowrap; }
                                    }
                                    </style>

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
                            <!-- Hidden username field for accessibility and better password manager compatibility -->
                            <input type="text" name="username" value="<?php echo htmlspecialchars($currentUser['username'] ?? ''); ?>" autocomplete="username" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;" aria-hidden="true" tabindex="-1">
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

<!-- Add/Edit Address Modal -->
<div class="modal" id="add-address-modal" style="display:none;align-items:center;justify-content:center;">
    <div class="modal-content" style="background:#fff;border-radius:10px;max-width:520px;width:95%;padding:1.25rem;box-shadow:0 10px 30px rgba(0,0,0,0.2);position:relative;">
        <button class="close-btn" onclick="closeModal('add-address-modal')" style="position:absolute;right:12px;top:10px;border:none;background:transparent;font-size:1.25rem;cursor:pointer">&times;</button>
        <h3 id="address-modal-title" style="margin-top:0;margin-bottom:1rem;">Add New Address</h3>
        <form id="add-address-form" data-mode="add">
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div class="form-group">
                    <label>Type</label>
                    <select name="address_type" class="form-control">
                        <option value="home">Home</option>
                        <option value="work">Work</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Label</label>
                    <input type="text" name="address_label" class="form-control" placeholder="e.g., My Place">
                </div>
            </div>
            <div class="form-group">
                <label>Street Address *</label>
                <input type="text" name="street_address" class="form-control" required minlength="3">
            </div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div class="form-group">
                    <label>City *</label>
                    <input type="text" name="city" class="form-control" required minlength="2">
                </div>
                <div class="form-group">
                    <label>State *</label>
                    <input type="text" name="state" class="form-control" required minlength="2">
                </div>
            </div>
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div class="form-group">
                    <label>Postal Code *</label>
                    <input type="text" name="postal_code" class="form-control" required minlength="3" maxlength="10" pattern="[A-Za-z0-9\-\s]+" placeholder="e.g., 44600">
                </div>
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" class="form-control" value="Nepal">
                </div>
            </div>
            <div class="form-group">
                <label>Landmark</label>
                <input type="text" name="landmark" class="form-control" placeholder="Nearby landmark (optional)">
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" id="is_default" name="is_default" value="1">
                <label for="is_default" style="margin:0;">Set as default address</label>
            </div>
            <div class="form-group" style="display:flex;gap:0.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('add-address-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Address</button>
            </div>
        </form>
    </div>
    <style>
        /* Scoped modal fallback styles to avoid conflicts */
        #add-address-modal.modal { position: fixed; inset: 0; background: rgba(0,0,0,0.35); z-index: 10000; }
        #add-address-modal.modal.active { display: flex !important; }
    </style>
    <script>
        // Local cache for addresses to support quick edit
        window.__addressesCache = window.__addressesCache || [];

        function showAddAddressModal() {
            const title = document.getElementById('address-modal-title');
            const form = document.getElementById('add-address-form');
            if (form) {
                form.reset();
                form.dataset.mode = 'add';
                form.dataset.id = '';
            }
            if (title) title.textContent = 'Add New Address';
            if (typeof openModal === 'function') { openModal('add-address-modal'); } else { document.getElementById('add-address-modal').style.display = 'flex'; document.body.style.overflow='hidden'; }
        }
        async function loadAddresses() {
            const list = document.getElementById('address-list');
            if (!list) return;
            list.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Loading addresses...</div>';
            try {
                const resp = await fetch('/api/users/addresses.php', { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                    if (!resp.ok) {
                        if (resp.status === 401) {
                            console.warn('Addresses: not authenticated (401)');
                            list.innerHTML = '<div class="empty-state"><i class="fas fa-map-marker-alt"></i><h3>No addresses found</h3><p>Please log in to manage addresses.</p></div>';
                            return;
                        }
                        const txt = await resp.text().catch(()=>null);
                        console.error('Addresses fetch failed', resp.status, txt);
                        throw new Error('Failed to load');
                    }
                    const ct = resp.headers.get('content-type') || '';
                    if (!ct.includes('application/json')) {
                        const text = await resp.text().catch(()=>null);
                        console.error('Addresses unexpected response', text);
                        throw new Error('Failed to load');
                    }
                    const data = await resp.json();
                    if (!data.success) throw new Error(data.message || 'Failed to load');
                window.__addressesCache = Array.isArray(data.addresses) ? data.addresses : [];
                if (!data.addresses || data.addresses.length === 0) {
                    list.innerHTML = '<div class="empty-state"><i class="fas fa-map-marker-alt"></i><h3>No addresses found</h3><p>Add your first delivery address to get started</p></div>';
                    return;
                }
                list.innerHTML = data.addresses.map(a => `
                    <div class="address-card" style="border:1px solid #e0e0e0;border-radius:8px;padding:1rem;margin-bottom:0.75rem;">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;">
                            <div>
                                <strong>${a.address_label || a.address_type || 'Address'}</strong>
                                ${a.is_default ? '<span class="badge" style="background:#10b981;color:#fff;border-radius:4px;padding:2px 6px;margin-left:6px;font-size:.75rem;">Default</span>' : ''}
                                <div style="color:#555;margin-top:4px;">
                                    ${a.street_address}, ${a.city}, ${a.state} ${a.postal_code}, ${a.country || ''}
                                </div>
                                ${a.landmark ? `<div style="color:#777;margin-top:2px;">Landmark: ${a.landmark}</div>` : ''}
                            </div>
                            <div style="display:flex;gap:0.5rem;">
                                <button class="btn btn-sm" style="background:#6b7280;color:#fff;" onclick="editAddress(${a.address_id})">Edit</button>
                                ${a.is_default ? '' : `<button class="btn btn-sm" style="background:#3b82f6;color:#fff;" onclick="setDefaultAddress(${a.address_id})">Set Default</button>`}
                                <button class="btn btn-sm btn-secondary" onclick="deleteAddress(${a.address_id})">Delete</button>
                            </div>
                        </div>
                    </div>
                `).join('');
            } catch (e) {
                console.error('Load addresses failed', e);
                if (typeof showNotification === 'function') showNotification('Failed to load addresses', 'error');
                list.innerHTML = '<div class="empty-state">Failed to load addresses</div>';
            }
        }
        function editAddress(id) {
            const address = (window.__addressesCache || []).find(a => String(a.address_id) === String(id));
            if (!address) { if (typeof showNotification==='function') showNotification('Address not found', 'error'); return; }
            const form = document.getElementById('add-address-form');
            const title = document.getElementById('address-modal-title');
            if (form) {
                form.dataset.mode = 'edit';
                form.dataset.id = String(id);
                form.address_type.value = address.address_type || 'home';
                form.address_label.value = address.address_label || '';
                form.street_address.value = address.street_address || '';
                form.city.value = address.city || '';
                form.state.value = address.state || '';
                form.postal_code.value = address.postal_code || '';
                form.country.value = address.country || 'Nepal';
                form.landmark.value = address.landmark || '';
                form.is_default.checked = !!address.is_default;
            }
            if (title) title.textContent = 'Edit Address';
            if (typeof openModal === 'function') { openModal('add-address-modal'); } else { document.getElementById('add-address-modal').style.display = 'flex'; document.body.style.overflow='hidden'; }
        }
        async function setDefaultAddress(id) {
            try {
                const resp = await fetch(`/api/users/addresses.php?id=${id}`, { method: 'PATCH', headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                const data = await resp.json();
                if (!data.success) throw new Error(data.message || 'Failed');
                if (typeof showNotification === 'function') showNotification('Default address updated', 'success');
                loadAddresses();
            } catch (e) { if (typeof showNotification === 'function') showNotification('Failed to update default address', 'error'); }
        }
        async function deleteAddress(id) {
            if (!confirm('Delete this address?')) return;
            try {
                const resp = await fetch(`/api/users/addresses.php?id=${id}`, { method: 'DELETE', headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                const data = await resp.json();
                if (!data.success) throw new Error(data.message || 'Failed');
                if (typeof showNotification === 'function') showNotification('Address deleted', 'success');
                loadAddresses();
            } catch (e) { if (typeof showNotification === 'function') showNotification('Failed to delete address', 'error'); }
        }
        function validateAddressPayload(p) {
            if (!p.street_address || p.street_address.trim().length < 3) return { ok:false, msg:'Street address is too short' };
            if (!p.city || p.city.trim().length < 2) return { ok:false, msg:'City is required' };
            if (!p.state || p.state.trim().length < 2) return { ok:false, msg:'State is required' };
            if (!p.postal_code || p.postal_code.trim().length < 3) return { ok:false, msg:'Postal code is required' };
            if (!/^[A-Za-z0-9\-\s]{3,10}$/.test(p.postal_code)) return { ok:false, msg:'Postal code format is invalid' };
            return { ok:true };
        }
        async function submitAddAddressForm(e) {
            e.preventDefault();
            const form = e.target;
            const payload = {
                address_type: form.address_type.value || 'home',
                address_label: form.address_label.value || null,
                street_address: form.street_address.value.trim(),
                city: form.city.value.trim(),
                state: form.state.value.trim(),
                postal_code: form.postal_code.value.trim(),
                country: form.country.value.trim() || 'Nepal',
                landmark: form.landmark.value.trim() || null,
                is_default: form.is_default.checked ? 1 : 0
            };
            const v = validateAddressPayload(payload);
            if (!v.ok) { if (typeof showNotification==='function') showNotification(v.msg, 'error'); return; }
            try {
                const mode = form.dataset.mode || 'add';
                const id = form.dataset.id || '';
                const isEdit = mode === 'edit' && id;
                const endpoint = isEdit ? `/api/users/addresses.php?id=${encodeURIComponent(id)}` : '/api/users/addresses.php';
                const method = isEdit ? 'PUT' : 'POST';
                const resp = await fetch(endpoint, { method, headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', body: JSON.stringify(payload) });
                const data = await resp.json();
                if (!data.success) throw new Error(data.message || 'Failed to save');
                if (typeof showNotification === 'function') showNotification(isEdit ? 'Address updated' : 'Address added', 'success');
                if (typeof closeModal === 'function') closeModal('add-address-modal'); else document.getElementById('add-address-modal').style.display='none';
                form.reset();
                loadAddresses();
            } catch (e) {
                console.error('Save address error', e);
                if (typeof showNotification === 'function') showNotification(e.message || 'Failed to save address', 'error');
            }
        }
        document.addEventListener('DOMContentLoaded', function(){
            try { loadAddresses(); } catch(e){}
            const f = document.getElementById('add-address-form');
            if (f) { f.addEventListener('submit', submitAddAddressForm); }
        });
    </script>
</div>

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
    display: flex;
    justify-content: center;
    align-items: center;
}

.profile-avatar i {
    font-size: 4rem;
    color: var(--primary-color);
}

.profile-avatar img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary-color);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
// AJAX profile form submission including image
document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('profile-form');
    if(!form) return;
    form.addEventListener('submit', async function(e){
        e.preventDefault();

        // Client-side file size validation (match preview limit)
        const fileInput = document.getElementById('profile_image');
        if (fileInput && fileInput.files && fileInput.files[0]) {
            const f = fileInput.files[0];
            const MAX_BYTES = 2 * 1024 * 1024; // 2MB
            if (f.size > MAX_BYTES) {
                showNotification('Selected image exceeds 2 MB limit. Please choose a smaller file.', 'error');
                return;
            }
        }

        const fd = new FormData(form);
        const btn = form.querySelector('button[type="submit"]');
        const orig = btn.innerHTML; btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Saving...';
        try {
            const resp = await fetch('api/users/profile-update.php', { method:'POST', body: fd, credentials:'same-origin' });

            // Handle non-JSON responses and HTTP errors
            if (!resp.ok) {
                if (resp.status === 401) {
                    showNotification('You are not authenticated. Please log in and try again.', 'error');
                    btn.disabled = false; btn.innerHTML = orig;
                    return;
                }
                if (resp.status === 413) {
                    showNotification('Upload failed: file too large. Please reduce the file size and try again.', 'error');
                    btn.disabled = false; btn.innerHTML = orig;
                    return;
                }
                // Try to read JSON error message if returned
                const ct = resp.headers.get('content-type') || '';
                if (ct.includes('application/json')) {
                    const errJson = await resp.json().catch(()=>null);
                    const msg = errJson && errJson.message ? errJson.message : 'Server error during profile update.';
                    showNotification(msg, 'error');
                } else {
                    const text = await resp.text().catch(()=>null);
                    console.error('Non-JSON error response from profile-update:', text);
                    showNotification('Server error. Please try again or check the console for details.', 'error');
                }
                btn.disabled = false; btn.innerHTML = orig;
                return;
            }

            const ct = resp.headers.get('content-type') || '';
            let data = null;
            if (ct.includes('application/json')) {
                data = await resp.json().catch(err => { console.error('Invalid JSON from profile-update:', err); return null; });
            } else {
                const text = await resp.text().catch(()=>null);
                console.error('Expected JSON but received:', text);
                showNotification('Unexpected server response. See console for details.', 'error');
                return;
            }

            if (!data || !data.success) {
                throw new Error((data && data.message) ? data.message : 'Update failed');
            }

            showNotification('Profile updated', 'success');
            if(data.user && data.user.profile_image){
                // Normalize image path to ensure it starts with /
                let imagePath = data.user.profile_image;
                if (imagePath && !imagePath.startsWith('/')) {
                    imagePath = '/' + imagePath;
                }
                
                // Add timestamp to prevent caching
                const timestamp = Date.now();
                imagePath += (imagePath.includes('?') ? '&' : '?') + 't=' + timestamp;
                
                // Update preview
                const prev = document.getElementById('profile-image-preview');
                if(prev){ 
                    prev.innerHTML = '<img src="'+imagePath+'" alt="Profile" style="width:100%;height:100%;object-fit:cover;" />'; 
                }
                // Update sidebar avatar
                const sidebarAvatar = document.querySelector('.profile-avatar');
                if(sidebarAvatar){ 
                    sidebarAvatar.innerHTML = '<img src="'+imagePath+'" alt="Profile Picture" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-color); box-shadow: 0 2px 8px rgba(0,0,0,0.1);">'; 
                }
                // Update header avatar if present
                const headerImg = document.querySelector('.user-info img');
                if(headerImg){ 
                    headerImg.src = imagePath; 
                }
                // Update dropdown avatar if present
                const dropdownAvatar = document.querySelector('.dropdown-avatar');
                if(dropdownAvatar){ 
                    dropdownAvatar.src = imagePath; 
                }
            }
        } catch(err){
            console.error(err); showNotification(err.message || 'Profile update failed','error');
        } finally { btn.disabled=false; btn.innerHTML=orig; }
    });
});
// Profile image live preview
const profileImageInput = document.getElementById('profile_image');
if (profileImageInput) {
    profileImageInput.addEventListener('change', function(){
        const preview = document.getElementById('profile-image-preview');
        if (!preview) return;
        const file = this.files && this.files[0];
        if (!file) { return; }
        if (file.size > 2*1024*1024) {
            alert('Image exceeds 2MB limit');
            this.value='';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML='';
            const img=document.createElement('img');
            img.src=e.target.result; img.style.cssText='width:100%;height:100%;object-fit:cover;';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
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

// Activate section based on URL hash (e.g., #order-history, #address-book, #security)
document.addEventListener('DOMContentLoaded', function() {
    const hash = (window.location.hash || '').replace('#','');
    if (!hash) return;
    const link = document.querySelector(`.profile-nav-item[data-section="${hash}"]`);
    if (link) link.click();
});

// Profile form submission handled by main.js via apiFetch to /api/users/profile-update.php

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



<script>
document.addEventListener('DOMContentLoaded', function(){
    // Password change validation
    const pwdForm = document.getElementById('password-form');
    if (pwdForm) {
        let pwdSubmitting = false;
        pwdForm.addEventListener('submit', function(e){
            e.preventDefault();
            if (pwdSubmitting) return;
            const current = (this.querySelector('#current_password') || {}).value || '';
            const nw = (this.querySelector('#new_password') || {}).value || '';
            const cf = (this.querySelector('#confirm_password') || {}).value || '';
            if (!current || !nw || !cf) { alert('Please fill all password fields.'); return; }
            if (nw.length < 8) { alert('New password must be at least 8 characters.'); return; }
            if (nw !== cf) { alert('New password and confirmation do not match.'); return; }
            pwdSubmitting = true;
            const btn = this.querySelector('button[type="submit"]');
            const orig = btn.innerHTML; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...'; btn.disabled = true;
            // Post to server (example) - replace with real endpoint when available
            fetch('/api/users/change-password.php', { method: 'POST', headers: { 'Content-Type':'application/json' }, body: JSON.stringify({ current_password: current, new_password: nw }), credentials: 'same-origin' })
            .then(async r => {
                const ct = r.headers.get('content-type') || '';
                let j = {};
                try { if (ct.includes('application/json')) j = await r.json(); }
                catch(e){ console.warn('password change response parse failed', e); }
                if (r.ok && j.success) { alert('Password updated successfully.'); window.location.reload(); }
                else { alert(j.message || 'Failed to update password.'); }
            })
            .catch(err => { console.error(err); alert('Network error.'); })
            .finally(()=>{ pwdSubmitting = false; btn.innerHTML = orig; btn.disabled = false; });
        });
    }

    // Add/Edit address validation
    const addrForm = document.getElementById('add-address-form');
    if (addrForm) {
        let addrSubmitting = false;
        addrForm.addEventListener('submit', function(e){
            e.preventDefault();
            if (addrSubmitting) return;
            const street = (this.querySelector('input[name="street_address"]') || {}).value || '';
            const city = (this.querySelector('input[name="city"]') || {}).value || '';
            const state = (this.querySelector('input[name="state"]') || {}).value || '';
            const postal = (this.querySelector('input[name="postal_code"]') || {}).value || '';
            if (street.trim().length < 3) { alert('Please provide a valid street address.'); return; }
            if (city.trim().length < 2) { alert('Please provide a valid city.'); return; }
            if (state.trim().length < 2) { alert('Please provide a valid state.'); return; }
            if (!/^[A-Za-z0-9\-\s]{3,10}$/.test(postal)) { alert('Please provide a valid postal code.'); return; }
            addrSubmitting = true;
            const btn = this.querySelector('button[type="submit"]');
            const orig = btn.innerHTML; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'; btn.disabled = true;
            // Submit via fetch to addresses API
            const fd = new FormData(this);
            fetch('/api/users/addresses.php', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(async r => {
                const ct = r.headers.get('content-type') || '';
                let j = {};
                try { if (ct.includes('application/json')) j = await r.json(); }
                catch(e){ console.warn('address save parse failed', e); }
                if (r.ok && j.success) {
                    alert('Address saved');
                    if (typeof closeModal === 'function') closeModal('add-address-modal'); else document.getElementById('add-address-modal').style.display='none';
                    loadAddresses();
                } else {
                    alert(j.message || 'Failed to save address');
                }
            })
            .catch(err => { console.error(err); alert('Network error while saving address'); })
            .finally(()=>{ addrSubmitting = false; btn.innerHTML = orig; btn.disabled = false; });
        });
    }
});
</script>
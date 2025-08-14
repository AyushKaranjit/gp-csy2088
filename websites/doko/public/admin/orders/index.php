<?php
/**
 * Admin Orders Management API
 * Handles all order-related admin operations
 */

require_once '../../../config/database.php';
require_once '../../../src/Controllers/AuthController.php';

// Check authentication
$auth = new AuthController();
if (!$auth->isAdmin()) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['api'])) {
        header('Location: ../../login.php');
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
$page_title = 'Order Management | Admin';
$current_page = 'admin';

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    // Get orders with user information
    $query = "
        SELECT 
            o.*,
            u.first_name,
            u.last_name,
            u.email,
            u.phone,
            COUNT(oi.order_item_id) as item_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        GROUP BY o.order_id
        ORDER BY o.created_at DESC
    ";
    $stmt = $db->query($query);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = "Error loading orders: " . $e->getMessage();
    $orders = [];
}

// Unified theme: storefront header + admin navigation
$ADMIN_UI = true; // suppress storefront header
$additional_css = $additional_css ?? [];
$additional_css[] = '/css/admin.css';
include '../../../template/header.php';
include '../../../template/admin-nav.php';
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
}

/* Prevent flash of unstyled content */
body:not(.admin-ready) {
    opacity: 0;
    transition: opacity 0.3s ease;
}

body.admin-ready {
    opacity: 1;
}

/* Override font family to match home page */
* {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif !important;
}

/* Adjust body padding since we're hiding everything */
body {
    padding-top: 0 !important;
    background: #f8fafc;
    font-family: 'Inter', sans-serif;
}

/* Admin Orders Styles */
.orders-container {
    max-width: 1400px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.orders-header {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.orders-actions {
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

.orders-table {
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

.order-id {
    font-weight: 600;
    color: #1f2937;
}

.customer-info h4 {
    margin: 0;
    font-size: 0.9rem;
    color: #111827;
}

.customer-info p {
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

.status-pending { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-shipped { background: #e0e7ff; color: #3730a3; }
.status-delivered { background: #d1fae5; color: #065f46; }
.status-completed { background: #d1fae5; color: #065f46; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.status-refunded { background: #fef3c7; color: #92400e; }

.amount {
    font-weight: 600;
    color: #059669;
}

.item-count {
    color: #6b7280;
    font-size: 0.85rem;
}

.order-date {
    color: #6b7280;
    font-size: 0.85rem;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-action {
    padding: 0.25rem 0.5rem;
    border: none;
    border-radius: 4px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn-view {
    background: #3b82f6;
    color: white;
}

.btn-view:hover {
    background: #2563eb;
}

.btn-update {
    background: #10b981;
    color: white;
}

.btn-update:hover {
    background: #059669;
}

.btn-cancel {
    background: #ef4444;
    color: white;
}

.btn-cancel:hover {
    background: #dc2626;
}

.stats-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    max-width: 800px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
}

.order-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.detail-section {
    background: #f8fafc;
    padding: 1rem;
    border-radius: 8px;
}

.detail-section h4 {
    margin: 0 0 0.75rem 0;
    color: #374151;
    font-size: 0.9rem;
    font-weight: 600;
}

.detail-section p {
    margin: 0.25rem 0;
    font-size: 0.85rem;
    color: #6b7280;
}

.order-items-table {
    margin-top: 1rem;
}

.order-items-table table {
    font-size: 0.85rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.9rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

.btn-save {
    background: #10b981;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

@media (max-width: 768px) {
    .orders-header {
        text-align: center;
    }
    
    .orders-actions {
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
    
    .action-buttons {
        flex-direction: column;
    }
    
    .stats-summary {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<!-- Immediate admin ready class to prevent flashing -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.body.classList.add('admin-ready');
});
</script>

<div class="orders-container">
    <!-- Orders Header -->
    <div class="orders-header">
        <h1><i class="fas fa-clipboard-list"></i> Order Management</h1>
        <p>Monitor and manage customer orders</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Stats Summary -->
    <div class="stats-summary">
        <div class="stat-item">
            <div class="stat-number"><?php echo count($orders); ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'pending')); ?></div>
            <div class="stat-label">Pending Orders</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'processing')); ?></div>
            <div class="stat-label">Processing</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">NPR <?php echo number_format(array_sum(array_column($orders, 'total_amount')), 0); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'completed')); ?></div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo count(array_filter($orders, fn($o) => date('Y-m-d', strtotime($o['created_at'])) === date('Y-m-d'))); ?></div>
            <div class="stat-label">Today's Orders</div>
        </div>
    </div>

    <!-- Orders Actions -->
    <div class="orders-actions">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search orders..." onkeyup="filterOrders()">
        </div>
        <select class="filter-select" id="statusFilter" onchange="filterOrders()">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
            <option value="refunded">Refunded</option>
        </select>
        <select class="filter-select" id="dateFilter" onchange="filterOrders()">
            <option value="">All Time</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
        </select>
    </div>

    <!-- Orders Table -->
    <div class="orders-table">
        <div class="table-header">
            <h3>All Orders</h3>
            <span><?php echo count($orders); ?> orders total</span>
        </div>
        
        <div class="table-wrapper">
            <table id="ordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr data-status="<?php echo $order['status']; ?>" data-date="<?php echo $order['created_at']; ?>">
                                <td>
                                    <div class="order-id">#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></div>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <h4><?php echo htmlspecialchars(($order['first_name'] ?? 'Unknown') . ' ' . ($order['last_name'] ?? 'Customer')); ?></h4>
                                        <p><?php echo htmlspecialchars($order['email'] ?? 'No email'); ?></p>
                                    </div>
                                </td>
                                <td>
                                    <div class="item-count"><?php echo $order['item_count']; ?> items</div>
                                </td>
                                <td>
                                    <div class="amount">NPR <?php echo number_format($order['total_amount'], 0); ?></div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="order-date">
                                        <?php echo date('M d, Y', strtotime($order['created_at'])); ?><br>
                                        <small><?php echo date('H:i', strtotime($order['created_at'])); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-view" onclick="viewOrder(<?php echo $order['order_id']; ?>)" title="View Details">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                                            <button class="btn-action btn-update" onclick="updateOrderStatus(<?php echo $order['order_id']; ?>)" title="Update Status">
                                                <i class="fas fa-edit"></i> Update
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($order['status'] !== 'cancelled'): ?>
                                            <button class="btn-action btn-cancel" onclick="cancelOrder(<?php echo $order['order_id']; ?>)" title="Cancel Order">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 2rem;">
                                <i class="fas fa-clipboard-list" style="font-size: 2rem; color: #d1d5db; margin-bottom: 1rem;"></i><br>
                                No orders found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal" id="orderModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Order Details</h3>
            <button class="modal-close" onclick="closeOrderModal()">&times;</button>
        </div>
        
        <div id="orderDetailsContent">
            <!-- Order details will be loaded here -->
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal" id="statusModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Order Status</h3>
            <button class="modal-close" onclick="closeStatusModal()">&times;</button>
        </div>
        
        <form id="statusForm" onsubmit="saveOrderStatus(event)">
            <input type="hidden" id="statusOrderId" name="order_id">
            
            <div class="form-group">
                <label for="newStatus">New Status</label>
                <select id="newStatus" name="status" required>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeStatusModal()">Cancel</button>
                <button type="submit" class="btn-save">Update Status</button>
            </div>
        </form>
    </div>
</div>

<script>
let allOrders = <?php echo json_encode($orders); ?>;

function filterOrders() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    
    const orderRows = document.querySelectorAll('#ordersTable tbody tr');
    
    orderRows.forEach(row => {
        if (row.cells.length === 1) return; // Skip "no orders" row
        
        const orderId = row.cells[0].textContent.toLowerCase();
        const customerName = row.cells[1].textContent.toLowerCase();
        const orderStatus = row.dataset.status;
        const orderDate = new Date(row.dataset.date);
        
        const matchesSearch = orderId.includes(searchTerm) || customerName.includes(searchTerm);
        const matchesStatus = !statusFilter || orderStatus === statusFilter;
        
        let matchesDate = true;
        if (dateFilter) {
            const today = new Date();
            const todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate());
            
            switch (dateFilter) {
                case 'today':
                    matchesDate = orderDate >= todayStart;
                    break;
                case 'week':
                    const weekStart = new Date(todayStart);
                    weekStart.setDate(weekStart.getDate() - 7);
                    matchesDate = orderDate >= weekStart;
                    break;
                case 'month':
                    const monthStart = new Date(todayStart.getFullYear(), todayStart.getMonth(), 1);
                    matchesDate = orderDate >= monthStart;
                    break;
            }
        }
        
        if (matchesSearch && matchesStatus && matchesDate) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
}

function viewOrder(orderId) {
    // Show loading state
    document.getElementById('orderDetailsContent').innerHTML = '<div style="text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Loading order details...</div>';
    document.getElementById('orderModal').classList.add('active');
    
    // Fetch order details
    fetch(`admin-orders.php?api=1&action=get_details&order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOrderDetails(data.data);
            } else {
                document.getElementById('orderDetailsContent').innerHTML = '<div class="alert alert-danger">Error loading order details: ' + data.message + '</div>';
            }
        })
        .catch(error => {
            document.getElementById('orderDetailsContent').innerHTML = '<div class="alert alert-danger">Error loading order details: ' + error.message + '</div>';
        });
}

function displayOrderDetails(order) {
    const content = `
        <div class="order-details-grid">
            <div class="detail-section">
                <h4>Order Information</h4>
                <p><strong>Order ID:</strong> #${String(order.order_id).padStart(6, '0')}</p>
                <p><strong>Status:</strong> <span class="status-badge status-${order.status}">${order.status}</span></p>
                <p><strong>Total Amount:</strong> NPR ${Number(order.total_amount).toLocaleString()}</p>
                <p><strong>Order Date:</strong> ${new Date(order.created_at).toLocaleDateString()} ${new Date(order.created_at).toLocaleTimeString()}</p>
            </div>
            
            <div class="detail-section">
                <h4>Customer Information</h4>
                <p><strong>Name:</strong> ${order.first_name || 'N/A'} ${order.last_name || ''}</p>
                <p><strong>Email:</strong> ${order.email || 'N/A'}</p>
                <p><strong>Phone:</strong> ${order.phone || 'N/A'}</p>
            </div>
            
            <div class="detail-section">
                <h4>Shipping Address</h4>
                <p>${order.shipping_address || 'No shipping address provided'}</p>
            </div>
        </div>
        
        <div class="order-items-table">
            <h4>Order Items</h4>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${order.items ? order.items.map(item => `
                        <tr>
                            <td>${item.product_name || item.name || 'Unknown Product'}</td>
                            <td>NPR ${Number(item.price).toLocaleString()}</td>
                            <td>${item.quantity}</td>
                            <td>NPR ${Number(item.price * item.quantity).toLocaleString()}</td>
                        </tr>
                    `).join('') : '<tr><td colspan="4">No items found</td></tr>'}
                </tbody>
            </table>
        </div>
    `;
    
    document.getElementById('orderDetailsContent').innerHTML = content;
}

function closeOrderModal() {
    document.getElementById('orderModal').classList.remove('active');
}

function updateOrderStatus(orderId) {
    const order = allOrders.find(o => o.order_id == orderId);
    if (!order) return;
    
    document.getElementById('statusOrderId').value = orderId;
    document.getElementById('newStatus').value = order.status;
    document.getElementById('statusModal').classList.add('active');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.remove('active');
}

function saveOrderStatus(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('statusForm'));
    formData.append('action', 'update_status');
    formData.append('api', '1');
    
    fetch('admin-orders.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order status updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error updating order status: ' + error.message);
    });
}

function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'cancel');
    formData.append('order_id', orderId);
    formData.append('api', '1');
    
    fetch('admin-orders.php', {
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

// Close modals when clicking outside
document.getElementById('orderModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeOrderModal();
    }
});

document.getElementById('statusModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeStatusModal();
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
            case 'get_details':
                handleGetOrderDetails($db);
                break;
                
            case 'update_status':
                handleUpdateStatus($db);
                break;
                
            case 'cancel':
                handleCancelOrder($db);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetOrderDetails($db) {
    $order_id = $_GET['order_id'] ?? 0;
    
    if (!$order_id) {
        throw new Exception('Order ID is required');
    }
    
    // Get order with user information
    $orderQuery = "
        SELECT 
            o.*,
            u.first_name,
            u.last_name,
            u.email,
            u.phone
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = ?
    ";
    
    $stmt = $db->prepare($orderQuery);
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // Get order items
    $itemsQuery = "
        SELECT 
            oi.*,
            p.name as product_name
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ";
    
    $stmt = $db->prepare($itemsQuery);
    $stmt->execute([$order_id]);
    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $order]);
}

function handleUpdateStatus($db) {
    $order_id = $_POST['order_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if (!$order_id || empty($status)) {
        throw new Exception('Order ID and status are required');
    }
    
    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded'];
    
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status');
    }
    
    $query = "UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$status, $order_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Order not found or status not changed');
    }
    
    echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
}

function handleCancelOrder($db) {
    $order_id = $_POST['order_id'] ?? 0;
    
    if (!$order_id) {
        throw new Exception('Order ID is required');
    }
    
    $query = "UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$order_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Order not found');
    }
    
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
}
?>

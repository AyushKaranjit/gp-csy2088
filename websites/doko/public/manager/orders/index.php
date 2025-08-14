<?php
/**
 * Manager Orders - View and Manage Orders
 * DOKO Grocery E-commerce Manager Panel
 */

require_once '../../../config/database.php';
require_once '../../../src/Controllers/AuthController.php';

// Check authentication
$auth = new AuthController();
if (!$auth->hasManagerAccess()) {
    header('Location: ../../login.php?error=unauthorized');
    exit;
}

$currentUser = $auth->getCurrentUser();
$page_title = 'Manage Orders | DOKO Manager';
$current_page = 'orders';
$show_breadcrumb = true;
$breadcrumb_items = [
    ['title' => 'Orders']
];

// Handle status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    if ($_POST['action'] === 'update_status' && isset($_POST['order_id']) && isset($_POST['new_status'])) {
        try {
            $order_id = (int)$_POST['order_id'];
            $new_status = $_POST['new_status'];
            
            // Validate status
            $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (in_array($new_status, $valid_statuses)) {
                $updateQuery = "UPDATE orders SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE order_id = :order_id";
                $stmt = $db->prepare($updateQuery);
                $stmt->bindParam(':status', $new_status);
                $stmt->bindParam(':order_id', $order_id);
                
                if ($stmt->execute()) {
                    $success_message = "Order #$order_id status updated to " . ucfirst($new_status);
                } else {
                    $error_message = "Failed to update order status";
                }
            } else {
                $error_message = "Invalid order status";
            }
        } catch (Exception $e) {
            $error_message = "Error updating order: " . $e->getMessage();
        }
    }
}

// Get orders data
try {
    $database = Database::getInstance();
    $db = $database->getConnection();

    // Build query with filters
    $whereConditions = [];
    $params = [];
    
    if (!empty($status_filter)) {
        $whereConditions[] = "o.status = :status";
        $params[':status'] = $status_filter;
    }
    
    if (!empty($search_query)) {
        $whereConditions[] = "(u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search OR o.order_id LIKE :search)";
        $params[':search'] = "%$search_query%";
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get orders with pagination
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    $ordersQuery = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone,
                           COUNT(oi.order_item_id) as item_count
                    FROM orders o 
                    LEFT JOIN users u ON o.user_id = u.user_id 
                    LEFT JOIN order_items oi ON o.order_id = oi.order_id
                    $whereClause
                    GROUP BY o.order_id
                    ORDER BY o.created_at DESC 
                    LIMIT $per_page OFFSET $offset";
    
    $stmt = $db->prepare($ordersQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    $countQuery = "SELECT COUNT(DISTINCT o.order_id) as total
                   FROM orders o 
                   LEFT JOIN users u ON o.user_id = u.user_id 
                   $whereClause";
    
    $stmt = $db->prepare($countQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_orders / $per_page);

    // Get order statistics
    $statsQuery = "SELECT 
                       status,
                       COUNT(*) as count,
                       SUM(total_amount) as total_value
                   FROM orders 
                   GROUP BY status";
    $stmt = $db->query($statsQuery);
    $order_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Orders Error: " . $e->getMessage());
    $orders = [];
    $order_stats = [];
    $total_orders = 0;
    $total_pages = 1;
}

include_once '../shared/header.php';
?>

<div class="orders-container">
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Order Statistics -->
    <div class="order-stats">
        <h2>Order Overview</h2>
        <div class="stats-grid">
            <?php 
            $status_colors = [
                'pending' => '#FF9800',
                'processing' => '#2196F3', 
                'shipped' => '#9C27B0',
                'delivered' => '#4CAF50',
                'cancelled' => '#f44336'
            ];
            
            foreach ($order_stats as $stat): 
            ?>
                <div class="stat-card" style="border-left-color: <?php echo $status_colors[$stat['status']] ?? '#666'; ?>">
                    <div class="stat-content">
                        <h3><?php echo number_format($stat['count']); ?></h3>
                        <p><?php echo ucfirst($stat['status']); ?> Orders</p>
                        <small>NPR <?php echo number_format($stat['total_value'], 2); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="filters-section">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label for="status">Filter by Status:</label>
                <select name="status" id="status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="search">Search Orders:</label>
                <input type="text" name="search" id="search" placeholder="Order ID, customer name, email..." value="<?php echo htmlspecialchars($search_query); ?>">
            </div>
            
            <div class="filter-group">
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i>
                    Apply Filters
                </button>
                <a href="?" class="btn-clear">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="orders-section">
        <div class="section-header">
            <h2>Orders Management</h2>
            <div class="results-info">
                Showing <?php echo count($orders); ?> of <?php echo number_format($total_orders); ?> orders
            </div>
        </div>

        <div class="table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($order['order_id']); ?></td>
                                <td class="customer-info">
                                    <div class="customer-name">
                                        <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                    </div>
                                    <div class="customer-email">
                                        <?php echo htmlspecialchars($order['email']); ?>
                                    </div>
                                    <?php if ($order['phone']): ?>
                                        <div class="customer-phone">
                                            <?php echo htmlspecialchars($order['phone']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="order-date">
                                        <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                    </div>
                                    <div class="order-time">
                                        <?php echo date('g:i A', strtotime($order['created_at'])); ?>
                                    </div>
                                </td>
                                <td><?php echo $order['item_count']; ?> items</td>
                                <td class="order-total">NPR <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <form method="POST" class="status-form" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <select name="new_status" class="status-select" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="actions">
                                    <a href="view.php?id=<?php echo $order['order_id']; ?>" class="btn-view" title="View Order">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="print.php?id=<?php echo $order['order_id']; ?>" class="btn-print" title="Print Order" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data">
                                <?php if (!empty($search_query) || !empty($status_filter)): ?>
                                    No orders found matching your criteria.
                                <?php else: ?>
                                    No orders found.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="page-btn">
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                       class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="page-btn">
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.orders-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.alert i {
    margin-right: 10px;
    font-size: 1.2rem;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.order-stats {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.order-stats h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    border-left: 4px solid #4CAF50;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-content h3 {
    margin: 0;
    font-size: 2rem;
    font-weight: bold;
    color: #333;
}

.stat-content p {
    margin: 5px 0;
    color: #666;
    font-weight: 500;
}

.stat-content small {
    color: #999;
    font-size: 0.9rem;
}

.filters-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.filter-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.filter-group select,
.filter-group input {
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.filter-group select:focus,
.filter-group input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.btn-filter,
.btn-clear {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s;
    display: inline-flex;
    align-items: center;
    margin-right: 10px;
}

.btn-filter {
    background-color: #007bff;
    color: white;
}

.btn-filter:hover {
    background-color: #0056b3;
}

.btn-clear {
    background-color: #6c757d;
    color: white;
}

.btn-clear:hover {
    background-color: #545b62;
    text-decoration: none;
    color: white;
}

.btn-filter i,
.btn-clear i {
    margin-right: 8px;
}

.orders-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h2 {
    margin: 0;
    color: #333;
}

.results-info {
    color: #666;
    font-size: 0.9rem;
}

.table-container {
    overflow-x: auto;
    margin-bottom: 20px;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th,
.orders-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.orders-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
    position: sticky;
    top: 0;
}

.orders-table tr:hover {
    background-color: #f8f9fa;
}

.customer-info {
    min-width: 200px;
}

.customer-name {
    font-weight: 500;
    color: #333;
}

.customer-email {
    font-size: 0.9rem;
    color: #666;
    margin-top: 2px;
}

.customer-phone {
    font-size: 0.9rem;
    color: #666;
    margin-top: 2px;
}

.order-date {
    font-weight: 500;
    color: #333;
}

.order-time {
    font-size: 0.9rem;
    color: #666;
    margin-top: 2px;
}

.order-total {
    font-weight: 600;
    color: #28a745;
    font-size: 1.1rem;
}

.status-select {
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
    cursor: pointer;
}

.status-select:focus {
    outline: none;
    border-color: #007bff;
}

.actions {
    display: flex;
    gap: 5px;
}

.btn-view,
.btn-print {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 4px;
    text-decoration: none;
    color: white;
    transition: opacity 0.3s;
}

.btn-view {
    background-color: #007bff;
}

.btn-print {
    background-color: #28a745;
}

.btn-view:hover,
.btn-print:hover {
    opacity: 0.8;
    text-decoration: none;
    color: white;
}

.no-data {
    text-align: center;
    color: #666;
    font-style: italic;
    padding: 40px;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
}

.page-btn {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
    display: flex;
    align-items: center;
}

.page-btn:hover {
    background-color: #007bff;
    color: white;
    text-decoration: none;
}

.page-btn.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.page-btn i {
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .orders-container {
        padding: 10px;
    }
    
    .filter-form {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .orders-table {
        font-size: 0.9rem;
    }
    
    .orders-table th,
    .orders-table td {
        padding: 8px;
    }
}
</style>

<?php include_once '../shared/footer.php'; ?>

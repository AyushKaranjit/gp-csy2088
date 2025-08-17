<?php
/**
 * Admin Order View - View Individual Order Details
 * DOKO Grocery E-commerce Admin Panel
 */

require_once '../../../config/database.php';
require_once '../../../src/Controllers/AuthController.php';

// Check authentication
$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: ../../login.php?error=unauthorized');
    exit;
}

$currentUser = $auth->getCurrentUser();
$page_title = 'Order Details | DOKO Admin';
$current_page = 'orders';
$show_breadcrumb = true;

// Get order ID
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    header('Location: index.php?error=invalid_order');
    exit;
}

$breadcrumb_items = [
    ['title' => 'Orders', 'url' => '../orders/'],
    ['title' => 'Order #' . $order_id]
];

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    if ($_POST['action'] === 'update_status' && isset($_POST['new_status'])) {
        try {
            $new_status = $_POST['new_status'];
            
            // Validate status
            $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (in_array($new_status, $valid_statuses)) {
                $updateQuery = "UPDATE orders SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE order_id = :order_id";
                $stmt = $db->prepare($updateQuery);
                $stmt->bindParam(':status', $new_status);
                $stmt->bindParam(':order_id', $order_id);
                
                if ($stmt->execute()) {
                    $success_message = "Order status updated to " . ucfirst($new_status);
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

// Get order details
try {
    $database = Database::getInstance();
    $db = $database->getConnection();

    // Get order with customer details (fixed to use correct address columns)
    $orderQuery = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone, u.date_of_birth, u.gender, u.created_at as user_created_at
                   FROM orders o 
                   LEFT JOIN users u ON o.user_id = u.user_id 
                   WHERE o.order_id = :order_id";
    
    $stmt = $db->prepare($orderQuery);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header('Location: index.php?error=order_not_found');
        exit;
    }

    // Get order items (using stored product info from order_items table)
    $itemsQuery = "SELECT oi.*
                   FROM order_items oi
                   WHERE oi.order_id = :order_id
                   ORDER BY oi.order_item_id";
    
    $stmt = $db->prepare($itemsQuery);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get order status history (if you have this table)
    // This is optional - you may need to create this table or remove this section
    try {
        $historyQuery = "SELECT * FROM order_status_history 
                         WHERE order_id = :order_id 
                         ORDER BY created_at DESC";
        $stmt = $db->prepare($historyQuery);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        $status_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Table might not exist, continue without history
        $status_history = [];
    }

} catch (Exception $e) {
    error_log("Order Details Error: " . $e->getMessage());
    header('Location: index.php?error=database_error');
    exit;
}

// Unified theme: storefront header + admin navigation
$ADMIN_UI = true; // suppress storefront header
$additional_css = $additional_css ?? [];
$additional_css[] = '/css/admin.css';
include_once '../../../template/header.php';
include_once '../../../template/admin-nav.php';
?>

<div class="order-details-container">
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

    <!-- Order Header -->
    <div class="order-header">
        <div class="order-info">
            <h1>Order #<?php echo htmlspecialchars($order['order_id']); ?></h1>
            <p class="order-date">
                <i class="fas fa-calendar"></i>
                Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
            </p>
        </div>
        
        <div class="order-actions">
            <form method="POST" class="status-update-form">
                <input type="hidden" name="action" value="update_status">
                <label for="new_status">Update Status:</label>
                <select name="new_status" id="new_status">
                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <button type="submit" class="btn-update">Update</button>
            </form>
            
            <a href="print.php?id=<?php echo $order['order_id']; ?>" class="btn-print" target="_blank">
                <i class="fas fa-print"></i>
                Print Order
            </a>
        </div>
    </div>

    <!-- Order Status -->
    <div class="order-status-section">
        <div class="current-status">
            <span class="status-badge status-<?php echo $order['status']; ?>">
                <?php echo ucfirst($order['status']); ?>
            </span>
        </div>
        
        <div class="order-summary">
            <div class="summary-item">
                <strong>Total Amount:</strong>
                <span class="amount">NPR <?php echo number_format($order['total_amount'], 2); ?></span>
            </div>
            <div class="summary-item">
                <strong>Payment Method:</strong>
                <span><?php echo ucfirst($order['payment_method'] ?? 'Not specified'); ?></span>
            </div>
            <div class="summary-item">
                <strong>Payment Status:</strong>
                <span class="payment-status <?php echo $order['payment_status'] ?? 'pending'; ?>">
                    <?php echo ucfirst($order['payment_status'] ?? 'Pending'); ?>
                </span>
            </div>
        </div>
    </div>

    <div class="order-content">
        <!-- Customer Information -->
        <div class="customer-section">
            <h2><i class="fas fa-user"></i> Customer Information</h2>
            <div class="customer-details">
                <div class="customer-info">
                    <h3><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></h3>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($order['email']); ?></p>
                    <?php if ($order['phone']): ?>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <?php endif; ?>
                    <?php if ($order['date_of_birth']): ?>
                        <p><i class="fas fa-birthday-cake"></i> Born: <?php echo date('M d, Y', strtotime($order['date_of_birth'])); ?></p>
                    <?php endif; ?>
                    <?php if ($order['gender']): ?>
                        <p><i class="fas fa-user"></i> Gender: <?php echo ucfirst($order['gender']); ?></p>
                    <?php endif; ?>
                    <p><i class="fas fa-calendar"></i> Customer since: <?php echo date('M d, Y', strtotime($order['user_created_at'])); ?></p>
                    
                    <!-- Customer Order History -->
                    <?php
                    // Get customer's order count and total spent
                    $customerStatsQuery = "SELECT COUNT(*) as order_count, COALESCE(SUM(total_amount), 0) as total_spent 
                                          FROM orders 
                                          WHERE user_id = :user_id AND status IN ('completed', 'delivered', 'pending', 'processing')";
                    $stmt = $db->prepare($customerStatsQuery);
                    $stmt->bindParam(':user_id', $order['user_id']);
                    $stmt->execute();
                    $customerStats = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="customer-stats">
                        <h4>Order History</h4>
                        <p><i class="fas fa-shopping-bag"></i> Total Orders: <?php echo $customerStats['order_count']; ?></p>
                        <p><i class="fas fa-rupee-sign"></i> Total Spent: NPR <?php echo number_format($customerStats['total_spent'], 2); ?></p>
                    </div>
                </div>
                
                <div class="shipping-address">
                    <h4>Shipping Address</h4>
                    <?php if ($order['shipping_address']): ?>
                        <address>
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </address>
                    <?php else: ?>
                        <p class="no-address">No shipping address available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="items-section">
            <h2><i class="fas fa-shopping-cart"></i> Order Items</h2>
            <div class="items-table-container">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        foreach ($order_items as $item): 
                            $item_total = $item['unit_price'] * $item['quantity'];
                            $subtotal += $item_total;
                        ?>
                            <tr>
                                <td class="product-info">
                                    <div class="product-details">
                                        <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($item['product_sku'] ?? 'N/A'); ?></td>
                                <td>NPR <?php echo number_format($item['unit_price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td class="item-total">NPR <?php echo number_format($item_total, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="subtotal-row">
                            <td colspan="4"><strong>Subtotal:</strong></td>
                            <td><strong>NPR <?php echo number_format($subtotal, 2); ?></strong></td>
                        </tr>
                        <?php if (isset($order['shipping_cost']) && $order['shipping_cost'] > 0): ?>
                        <tr class="shipping-row">
                            <td colspan="4">Shipping:</td>
                            <td>NPR <?php echo number_format($order['shipping_cost'], 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (isset($order['tax_amount']) && $order['tax_amount'] > 0): ?>
                        <tr class="tax-row">
                            <td colspan="4">Tax:</td>
                            <td>NPR <?php echo number_format($order['tax_amount'], 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="total-row">
                            <td colspan="4"><strong>Total:</strong></td>
                            <td><strong>NPR <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Order Notes -->
        <?php if (!empty($order['notes'])): ?>
        <div class="notes-section">
            <h2><i class="fas fa-sticky-note"></i> Order Notes</h2>
            <div class="notes-content">
                <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Status History -->
        <?php if (!empty($status_history)): ?>
        <div class="history-section">
            <h2><i class="fas fa-history"></i> Status History</h2>
            <div class="history-timeline">
                <?php foreach ($status_history as $history): ?>
                    <div class="history-item">
                        <div class="history-date">
                            <?php echo date('M j, Y g:i A', strtotime($history['created_at'])); ?>
                        </div>
                        <div class="history-status">
                            Status changed to: <strong><?php echo ucfirst($history['status']); ?></strong>
                        </div>
                        <?php if (!empty($history['notes'])): ?>
                            <div class="history-notes">
                                <?php echo htmlspecialchars($history['notes']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.order-details-container {
    padding: 20px;
    max-width: 1200px;
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

.order-header {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-info h1 {
    margin: 0;
    color: #333;
    font-size: 2rem;
}

.order-date {
    margin: 10px 0 0 0;
    color: #666;
    display: flex;
    align-items: center;
}

.order-date i {
    margin-right: 8px;
}

.order-actions {
    display: flex;
    gap: 20px;
    align-items: center;
}

.status-update-form {
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-update-form label {
    font-weight: 500;
    color: #333;
}

.status-update-form select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.btn-update,
.btn-print {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s;
    display: inline-flex;
    align-items: center;
}

.btn-update {
    background-color: #007bff;
    color: white;
}

.btn-update:hover {
    background-color: #0056b3;
}

.btn-print {
    background-color: #28a745;
    color: white;
}

.btn-print:hover {
    background-color: #1e7e34;
    text-decoration: none;
    color: white;
}

.btn-print i {
    margin-right: 8px;
}

.order-status-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.current-status {
    display: flex;
    align-items: center;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 1rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background-color: #fff3cd;
    color: #856404;
}

.status-processing {
    background-color: #cce5ff;
    color: #004085;
}

.status-shipped {
    background-color: #e6ccff;
    color: #5a1a6b;
}

.status-delivered {
    background-color: #d4edda;
    color: #155724;
}

.status-cancelled {
    background-color: #f8d7da;
    color: #721c24;
}

.order-summary {
    display: flex;
    gap: 30px;
}

.summary-item {
    text-align: right;
}

.summary-item strong {
    display: block;
    margin-bottom: 5px;
}

.amount {
    font-size: 1.2rem;
    font-weight: bold;
    color: #28a745;
}

.payment-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9rem;
    font-weight: 500;
}

.payment-status.paid {
    background-color: #d4edda;
    color: #155724;
}

.payment-status.pending {
    background-color: #fff3cd;
    color: #856404;
}

.order-content {
    display: grid;
    gap: 20px;
}

.customer-section,
.items-section,
.notes-section,
.history-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.customer-section h2,
.items-section h2,
.notes-section h2,
.history-section h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
    display: flex;
    align-items: center;
}

.customer-section h2 i,
.items-section h2 i,
.notes-section h2 i,
.history-section h2 i {
    margin-right: 10px;
}

.customer-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.customer-info h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.customer-info p {
    margin: 8px 0;
    color: #666;
    display: flex;
    align-items: center;
}

.customer-info i {
    margin-right: 8px;
    width: 16px;
}

.shipping-address h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.shipping-address address {
    font-style: normal;
    line-height: 1.6;
    color: #666;
}

.no-address {
    color: #999;
    font-style: italic;
}

.items-table-container {
    overflow-x: auto;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th,
.items-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.items-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.product-info {
    display: flex;
    align-items: center;
    min-width: 250px;
}

.product-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 5px;
    margin-right: 15px;
}

.product-details h4 {
    margin: 0;
    color: #333;
    font-size: 1rem;
}

.item-total {
    font-weight: 600;
    color: #333;
}

.items-table tfoot td {
    padding: 15px 12px;
    border-top: 2px solid #ddd;
}

.subtotal-row,
.shipping-row,
.tax-row {
    border-bottom: 1px solid #eee !important;
}

.total-row {
    background-color: #f8f9fa;
    font-size: 1.1rem;
}

.notes-content {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #007bff;
    line-height: 1.6;
}

.history-timeline {
    border-left: 2px solid #ddd;
    padding-left: 20px;
}

.history-item {
    margin-bottom: 20px;
    position: relative;
}

.history-item::before {
    content: '';
    position: absolute;
    left: -26px;
    top: 5px;
    width: 8px;
    height: 8px;
    background-color: #007bff;
    border-radius: 50%;
}

.history-date {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 5px;
}

.history-status {
    font-weight: 500;
    color: #333;
    margin-bottom: 5px;
}

.history-notes {
    font-size: 0.9rem;
    color: #666;
    margin-left: 20px;
}

@media (max-width: 768px) {
    .order-details-container {
        padding: 10px;
    }
    
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }
    
    .order-status-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }
    
    .order-summary {
        align-self: stretch;
        justify-content: space-between;
    }
    
    .customer-details {
        grid-template-columns: 1fr;
    }
    
    .status-update-form {
        flex-wrap: wrap;
    }
    
    .order-actions {
        flex-direction: column;
        align-items: stretch;
        width: 100%;
    }
}
</style>

<?php include_once '../../../template/footer.php'; ?>

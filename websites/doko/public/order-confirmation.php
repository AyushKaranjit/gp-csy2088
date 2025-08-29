<?php
/**
 * DOKO E-Commerce Website - Order Confirmation Page
 *
 * This website was developed as part of an academic project for educational purposes.
 * All code was written by the student developer to demonstrate web development skills.
 *
 * @author Student Developer
 * @version 1.0
 * @date 2025
 */

// Start session and include configuration
session_start();
require_once __DIR__ . '/../template/config.php';

// Check if order data exists
$order_data = null;
if (isset($_SESSION['order_data'])) {
    $order_data = $_SESSION['order_data'];
    unset($_SESSION['order_data']); // Remove after use
} elseif (isset($_GET['session'])) {
    // Check session storage through JavaScript
    // Order data will be loaded via JavaScript
}

// Generate order ID
$order_id = 'DOKO' . date('Ymd') . strtoupper(substr(uniqid(), -6));

// Page-specific variables
$page_title = page_title('Order Confirmation');
$page_description = 'Your order has been confirmed and will be delivered soon.';
$current_page = 'order-confirmation';

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Checkout', 'url' => 'checkout.php'],
    ['title' => 'Order Confirmation', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php include __DIR__ . '/../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="confirmation-container">
            <!-- Success Message -->
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Order Confirmed!</h1>
                <p>Thank you for shopping with DOKO. Your order has been successfully placed.</p>
            </div>

            <!-- Order Details -->
            <div class="order-details-card">
                <div class="order-header">
                    <h2>Order Details</h2>
                    <div class="order-id">Order ID: <strong><?php echo $order_id; ?></strong></div>
                </div>

                <div class="order-content">
                    <!-- Order Items -->
                    <div class="order-section">
                        <h3><i class="fas fa-shopping-bag"></i> Items Ordered</h3>
                        <div id="order-items-list">
                            <!-- Items will be loaded via JavaScript -->
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="order-section">
                        <h3><i class="fas fa-user"></i> Customer Information</h3>
                        <div id="customer-info">
                            <!-- Customer info will be loaded via JavaScript -->
                        </div>
                    </div>

                    <!-- Delivery Information -->
                    <div class="order-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Delivery Information</h3>
                        <div id="delivery-info">
                            <!-- Delivery info will be loaded via JavaScript -->
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="order-section">
                        <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                        <div id="payment-info">
                            <!-- Payment info will be loaded via JavaScript -->
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="order-section">
                        <h3><i class="fas fa-calculator"></i> Order Summary</h3>
                        <div class="summary-table">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="conf-subtotal">Rs. 0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Delivery Charge:</span>
                                <span id="conf-delivery">Rs. 0.00</span>
                            </div>
                            <div class="summary-row discount-row" id="conf-discount-row" style="display: none;">
                                <span>Discount:</span>
                                <span id="conf-discount">- Rs. 0.00</span>
                            </div>
                            <div class="summary-row total-row">
                                <span>Total Amount:</span>
                                <span id="conf-total">Rs. 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- What's Next -->
            <div class="next-steps-card">
                <h2>What happens next?</h2>
                <div class="steps-timeline">
                    <div class="step active">
                        <div class="step-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="step-content">
                            <h4>Order Confirmed</h4>
                            <p>Your order has been received and confirmed</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="step-content">
                            <h4>Preparing Your Order</h4>
                            <p>We're carefully selecting and packing your items</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="step-content">
                            <h4>Out for Delivery</h4>
                            <p>Your order is on its way to your doorstep</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="step-content">
                            <h4>Delivered</h4>
                            <p>Enjoy your fresh groceries!</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="contact-card">
                <h3>Need Help?</h3>
                <p>If you have any questions about your order, feel free to contact us:</p>
                <div class="contact-methods">
                    <div class="contact-method">
                        <i class="fas fa-phone"></i>
                        <span>+977-1-4444555</span>
                    </div>
                    <div class="contact-method">
                        <i class="fas fa-envelope"></i>
                        <span>support@doko.com.np</span>
                    </div>
                    <div class="contact-method">
                        <i class="fab fa-whatsapp"></i>
                        <span>+977-9851234567</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button onclick="window.print()" class="btn btn-outline">
                    <i class="fas fa-print"></i> Print Order
                </button>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</main>

<style>
.confirmation-container {
    max-width: 800px;
    margin: 0 auto;
}

.success-header {
    text-align: center;
    margin-bottom: 2rem;
    padding: 2rem;
    background: linear-gradient(135deg, var(--success-color), #27ae60);
    border-radius: var(--border-radius);
    color: white;
}

.success-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.success-header h1 {
    margin-bottom: 0.5rem;
    font-size: 2.5rem;
}

.success-header p {
    font-size: 1.2rem;
    opacity: 0.9;
}

.order-details-card,
.next-steps-card,
.contact-card {
    background: var(--white);
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.order-id {
    background: rgba(45, 90, 39, 0.1);
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    font-size: 0.9rem;
}

.order-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border-color);
}

.order-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.order-section h3 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.order-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color);
}

.order-item:last-child {
    border-bottom: none;
}

.order-item-image {
    width: 60px;
    height: 60px;
    border-radius: var(--border-radius);
    overflow: hidden;
    margin-right: 1rem;
}

.order-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-item-details {
    flex: 1;
}

.order-item-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.order-item-quantity {
    color: var(--light-text);
    font-size: 0.9rem;
}

.order-item-price {
    font-weight: 600;
    color: var(--primary-color);
    font-size: 1.1rem;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.info-item {
    margin-bottom: 0.75rem;
}

.info-label {
    font-weight: 600;
    color: var(--dark-text);
    margin-bottom: 0.25rem;
}

.info-value {
    color: var(--light-text);
}

.summary-table {
    background: rgba(45, 90, 39, 0.05);
    padding: 1.5rem;
    border-radius: var(--border-radius);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    font-size: 1rem;
}

.total-row {
    font-size: 1.2rem;
    font-weight: 600;
    padding-top: 1rem;
    border-top: 2px solid var(--primary-color);
    margin-top: 1rem;
    color: var(--primary-color);
}

.steps-timeline {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-top: 2rem;
}

.steps-timeline::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 10%;
    right: 10%;
    height: 2px;
    background: var(--border-color);
    z-index: 1;
}

.step {
    text-align: center;
    flex: 1;
    position: relative;
    z-index: 2;
}

.step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--border-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    transition: var(--transition);
}

.step.active .step-icon {
    background: var(--success-color);
}

.step-content h4 {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.step-content p {
    font-size: 0.8rem;
    color: var(--light-text);
}

.contact-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.contact-method {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: rgba(45, 90, 39, 0.05);
    border-radius: var(--border-radius);
}

.contact-method i {
    color: var(--primary-color);
    width: 20px;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .confirmation-container {
        padding: 0 1rem;
    }
    
    .order-details-card,
    .next-steps-card,
    .contact-card {
        padding: 1.5rem;
    }
    
    .order-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .steps-timeline {
        flex-direction: column;
        gap: 1rem;
    }
    
    .steps-timeline::before {
        display: none;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .success-header h1 {
        font-size: 2rem;
    }
}

/* Print Styles */
@media print {
    .action-buttons,
    .next-steps-card {
        display: none;
    }
    
    .order-details-card {
        box-shadow: none;
        border: 1px solid #ddd;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const orderIdParam = params.get('order_id');
    let orderData = null;

    // Check if we have order data from PHP session
    <?php if ($order_data): ?>
    orderData = <?php echo json_encode($order_data); ?>;
    hydrateFromSession(orderData);
    return;
    <?php endif; ?>

    function showFallbackRedirect(){
        window.location.href = 'index.php';
    }

    // Primary: if order_id present, fetch from API
    if (orderIdParam) {
        fetch('api/users/order-detail.php?order_id=' + encodeURIComponent(orderIdParam), {credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest'}})
            .then(r=>r.json())
            .then(j=>{
                if(j && j.success && j.order){
                    hydrateFromApi(j.order);
                } else {
                    // fallback to sessionStorage
                    loadFromSession();
                }
            })
            .catch(()=>{ loadFromSession(); });
    } else {
        loadFromSession();
    }

    function loadFromSession(){
        const sessionOrderData = sessionStorage.getItem('order_data');
        if (sessionOrderData) {
            try { orderData = JSON.parse(sessionOrderData); } catch(e){}
            sessionStorage.removeItem('order_data');
            if(orderData){ hydrateFromSession(orderData); return; }
        }
        showFallbackRedirect();
    }

    function hydrateFromApi(order){
        // order from API has different structure than session simulation
        orderData = order;
        // Items
        const itemsContainer = document.getElementById('order-items-list');
        itemsContainer.innerHTML = (order.items||[]).map(it=>`
            <div class="order-item">
                <div class="order-item-image">
                    <img src="${it.image}" alt="${it.name}" loading="lazy" onerror="handleImageError(this)">
                </div>
                <div class="order-item-details">
                    <div class="order-item-name">${it.name}</div>
                    <div class="order-item-quantity">Quantity: ${it.quantity} × Rs. ${(+it.price).toFixed(2)}</div>
                </div>
                <div class="order-item-price">Rs. ${(it.line_total).toFixed(2)}</div>
            </div>`).join('') || '<p>No items found.</p>';
        // Customer info not embedded; show minimal
        document.getElementById('customer-info').innerHTML = '<p>Thank you for your purchase! (Account order)</p>';
        // Delivery info
        const addr = order.shipping_address || {};
        document.getElementById('delivery-info').innerHTML = `
            <div class="info-grid">
                <div class="info-item"><div class="info-label">Address:</div><div class="info-value">${addr.address||'-'}</div></div>
                <div class="info-item"><div class="info-label">City:</div><div class="info-value">${(addr.city||'').toString()}</div></div>
                <div class="info-item"><div class="info-label">State:</div><div class="info-value">${(addr.state||'').toString()}</div></div>
                <div class="info-item"><div class="info-label">Postal Code:</div><div class="info-value">${(addr.zip||'').toString()}</div></div>
            </div>`;
        // Payment
        document.getElementById('payment-info').innerHTML = `<div class="info-item"><span style="font-weight:600">Method:</span> ${order.payment_method}</div>`;
        // Totals
        document.getElementById('conf-subtotal').textContent = 'Rs. ' + (order.totals.subtotal).toFixed(2);
        document.getElementById('conf-delivery').textContent = (order.totals.shipping === 0 ? 'FREE' : 'Rs. ' + (order.totals.shipping).toFixed(2));
        document.getElementById('conf-total').textContent = 'Rs. ' + (order.totals.total).toFixed(2);
    }

    function hydrateFromSession(data){
        orderData = data;
        // Items
        const container = document.getElementById('order-items-list');
        container.innerHTML = (orderData.items||[]).map(item=>`
            <div class="order-item">
                <div class="order-item-image">
                    <img src="${item.image}" alt="${item.name}" loading="lazy" onerror="handleImageError(this)">
                </div>
                <div class="order-item-details">
                    <div class="order-item-name">${item.name}</div>
                    <div class="order-item-quantity">Quantity: ${item.quantity} × Rs. ${(item.price).toFixed(2)}</div>
                </div>
                <div class="order-item-price">Rs. ${(item.price * item.quantity).toFixed(2)}</div>
            </div>`).join('');
        // Customer info
        const c = orderData.customer||{};
        document.getElementById('customer-info').innerHTML = `
            <div class="info-grid">
                <div class="info-item"><div class="info-label">Name:</div><div class="info-value">${c.first_name||''} ${c.last_name||''}</div></div>
                <div class="info-item"><div class="info-label">Email:</div><div class="info-value">${c.email||''}</div></div>
                <div class="info-item"><div class="info-label">Phone:</div><div class="info-value">${c.phone||''}</div></div>
            </div>`;
        const d = orderData.delivery||{};
        document.getElementById('delivery-info').innerHTML = `
            <div class="info-grid">
                <div class="info-item"><div class="info-label">Address:</div><div class="info-value">${d.address||''}</div></div>
                <div class="info-item"><div class="info-label">City:</div><div class="info-value">${(d.city||'').toString()}</div></div>
                <div class="info-item"><div class="info-label">Area:</div><div class="info-value">${d.area||''}</div></div>
                ${(d.landmark?`<div class=\"info-item\"><div class=\"info-label\">Landmark:</div><div class=\"info-value\">${d.landmark}</div></div>`:'')}
            </div>`;
        document.getElementById('payment-info').innerHTML = `<div class="info-item"><span style="font-weight:600">Method:</span> ${orderData.payment_method}</div>`;
        const subtotal = (orderData.items||[]).reduce((t,i)=>t + (i.price * i.quantity),0);
        const deliveryCharge = subtotal >= 1000 ? 0 : 50;
        document.getElementById('conf-subtotal').textContent = 'Rs. ' + subtotal.toFixed(2);
        document.getElementById('conf-delivery').textContent = deliveryCharge===0 ? 'FREE' : 'Rs. ' + deliveryCharge.toFixed(2);
        document.getElementById('conf-total').textContent = 'Rs. ' + (orderData.total|| (subtotal+deliveryCharge)).toFixed(2);
    }
});
</script>

<?php
// Include footer
include_footer();
?>

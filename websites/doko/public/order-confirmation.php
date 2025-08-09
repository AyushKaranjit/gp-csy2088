<?php
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
    let orderData = null;
    
    // Try to get order data from sessionStorage
    const sessionOrderData = sessionStorage.getItem('order_data');
    if (sessionOrderData) {
        orderData = JSON.parse(sessionOrderData);
        sessionStorage.removeItem('order_data'); // Remove after use
    }
    
    if (!orderData) {
        // If no order data, redirect to home
        window.location.href = 'index.php';
        return;
    }
    
    // Load order items
    function loadOrderItems() {
        const container = document.getElementById('order-items-list');
        let itemsHTML = '';
        
        orderData.items.forEach(item => {
            itemsHTML += `
                <div class="order-item">
                    <div class="order-item-image">
                        <img src="${item.image}" alt="${item.name}" loading="lazy">
                    </div>
                    <div class="order-item-details">
                        <div class="order-item-name">${item.name}</div>
                        <div class="order-item-quantity">Quantity: ${item.quantity} × Rs. ${item.price.toFixed(2)}</div>
                    </div>
                    <div class="order-item-price">Rs. ${(item.price * item.quantity).toFixed(2)}</div>
                </div>
            `;
        });
        
        container.innerHTML = itemsHTML;
    }
    
    // Load customer information
    function loadCustomerInfo() {
        const container = document.getElementById('customer-info');
        const customer = orderData.customer;
        
        container.innerHTML = `
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Name:</div>
                    <div class="info-value">${customer.first_name} ${customer.last_name}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email:</div>
                    <div class="info-value">${customer.email}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Phone:</div>
                    <div class="info-value">${customer.phone}</div>
                </div>
            </div>
        `;
    }
    
    // Load delivery information
    function loadDeliveryInfo() {
        const container = document.getElementById('delivery-info');
        const delivery = orderData.delivery;
        
        container.innerHTML = `
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Address:</div>
                    <div class="info-value">${delivery.address}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">City:</div>
                    <div class="info-value">${delivery.city.charAt(0).toUpperCase() + delivery.city.slice(1)}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Area:</div>
                    <div class="info-value">${delivery.area}</div>
                </div>
                ${delivery.landmark ? `
                <div class="info-item">
                    <div class="info-label">Landmark:</div>
                    <div class="info-value">${delivery.landmark}</div>
                </div>
                ` : ''}
                <div class="info-item">
                    <div class="info-label">Delivery Date:</div>
                    <div class="info-value">${new Date(delivery.delivery_date).toLocaleDateString()}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Time Slot:</div>
                    <div class="info-value">${getTimeSlotText(delivery.delivery_time)}</div>
                </div>
                ${delivery.delivery_notes ? `
                <div class="info-item" style="grid-column: 1 / -1;">
                    <div class="info-label">Special Instructions:</div>
                    <div class="info-value">${delivery.delivery_notes}</div>
                </div>
                ` : ''}
            </div>
        `;
    }
    
    // Load payment information
    function loadPaymentInfo() {
        const container = document.getElementById('payment-info');
        const paymentMethod = orderData.payment_method;
        
        const paymentMethods = {
            'cod': { name: 'Cash on Delivery', icon: 'fas fa-money-bill-wave' },
            'esewa': { name: 'eSewa', icon: 'fab fa-cc-visa' },
            'khalti': { name: 'Khalti', icon: 'fas fa-mobile-alt' }
        };
        
        const method = paymentMethods[paymentMethod];
        
        container.innerHTML = `
            <div class="info-item">
                <i class="${method.icon}"></i>
                <span style="margin-left: 0.5rem;">${method.name}</span>
            </div>
        `;
    }
    
    // Load order summary
    function loadOrderSummary() {
        const subtotal = orderData.items.reduce((total, item) => total + (item.price * item.quantity), 0);
        const deliveryCharge = subtotal >= 1000 ? 0 : 50;
        const total = orderData.total;
        
        document.getElementById('conf-subtotal').textContent = `Rs. ${subtotal.toFixed(2)}`;
        document.getElementById('conf-delivery').textContent = deliveryCharge === 0 ? 'FREE' : `Rs. ${deliveryCharge.toFixed(2)}`;
        document.getElementById('conf-total').textContent = `Rs. ${total.toFixed(2)}`;
    }
    
    // Helper function for time slot text
    function getTimeSlotText(timeSlot) {
        const timeSlots = {
            'morning': 'Morning (8:00 AM - 12:00 PM)',
            'afternoon': 'Afternoon (12:00 PM - 5:00 PM)',
            'evening': 'Evening (5:00 PM - 8:00 PM)'
        };
        return timeSlots[timeSlot] || timeSlot;
    }
    
    // Initialize all sections
    loadOrderItems();
    loadCustomerInfo();
    loadDeliveryInfo();
    loadPaymentInfo();
    loadOrderSummary();
});
</script>

<?php
// Include footer
include_footer();
?>

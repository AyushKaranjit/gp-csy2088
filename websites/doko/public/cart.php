<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../template/config.php';

// Page-specific variables
$page_title = page_title('Shopping Cart');
$page_description = 'Review your selected items and proceed to checkout at DOKO.';
$current_page = 'cart';

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Shopping Cart', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);
?>
<?php
// Start session and include configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../template/config.php';

// Page-specific variables
$page_title = page_title('Shopping Cart');
$page_description = 'Review your selected items and proceed to checkout at DOKO.';
$current_page = 'cart';

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Shopping Cart', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php include __DIR__ . '/../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>

        <div class="cart-layout">
            <!-- Cart Items -->
            <div class="cart-items-section">
                <div id="cart-items-container">
                    <!-- Cart items will be loaded here by JavaScript -->
                    <div class="empty-cart" id="empty-cart">
                        <div class="empty-cart-content">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Your cart is empty</h3>
                            <p>Add some delicious items to your cart to get started!</p>
                            <a href="products.php" class="btn btn-primary">Start Shopping</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary-section">
                <div class="cart-summary" id="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="cart-subtotal">Rs. 0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Charge:</span>
                        <span id="delivery-charge">Rs. 50.00</span>
                    </div>
                    <div class="summary-row discount-row" id="discount-row" style="display: none;">
                        <span>Discount:</span>
                        <span id="discount-amount">- Rs. 0.00</span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total:</span>
                        <span id="cart-total">Rs. 50.00</span>
                    </div>
                    
                    <div class="promo-code-section">
                        <input type="text" id="promo-code" name="promo_code" placeholder="Enter promo code" class="promo-input" autocomplete="off">
                        <button id="apply-promo" class="btn btn-outline btn-sm" type="button">Apply</button>
                    </div>
                    
                    <div class="cart-actions">
                        <button id="proceed-checkout" class="btn btn-primary btn-lg" type="button">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </button>
                        <button id="clear-cart" class="btn btn-outline" type="button">Clear Cart</button>
                        <a href="products.php" class="btn btn-outline">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.cart-header {
    text-align: center;
    margin-bottom: 2rem;
}

.cart-header h1 {
    margin-bottom: 0.5rem;
}

.cart-header p {
    color: var(--light-text);
}

.cart-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
    align-items: start;
}

.cart-items-section {
    background: var(--white);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
}

.cart-item {
    display: flex;
    align-items: center;
    padding: 1.5rem 0;
    border-bottom: 1px solid var(--border-color);
}

.cart-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 80px;
    height: 80px;
    border-radius: var(--border-radius);
    overflow: hidden;
    margin-right: 1rem;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    flex: 1;
    margin-right: 1rem;
}

.item-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: var(--dark-text);
}

.item-price {
    color: var(--primary-color);
    font-weight: 600;
    font-size: 1.1rem;
}

.item-quantity {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0.5rem 0;
}

.qty-btn {
    width: 30px;
    height: 30px;
    border: 1px solid var(--border-color);
    background: var(--white);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
}

.qty-btn:hover {
    background: var(--primary-color);
    color: var(--white);
    border-color: var(--primary-color);
}

.qty-input {
    width: 60px;
    text-align: center;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    padding: 0.25rem;
}

.item-total {
    font-weight: 600;
    color: var(--dark-text);
    margin-right: 1rem;
}

.remove-item {
    color: var(--danger-color);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    transition: var(--transition);
}

.remove-item:hover {
    background: rgba(220, 38, 46, 0.1);
}

.empty-cart {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-cart-content i {
    font-size: 4rem;
    color: var(--light-text);
    margin-bottom: 1rem;
}

.empty-cart-content h3 {
    margin-bottom: 0.5rem;
    color: var(--dark-text);
}

.empty-cart-content p {
    color: var(--light-text);
    margin-bottom: 2rem;
}

.cart-summary {
    background: var(--white);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    position: sticky;
    top: 100px;
}

.cart-summary h3 {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
}

.total-row {
    font-size: 1.1rem;
    font-weight: 600;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
    margin-top: 1rem;
}

.discount-row {
    color: var(--success-color);
}

.promo-code-section {
    display: flex;
    gap: 0.5rem;
    margin: 1.5rem 0;
}

.promo-input {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    font-size: 0.9rem;
}

.cart-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.delivery-info {
    background: var(--light-bg);
    padding: 1rem;
    border-radius: var(--border-radius);
    margin: 1rem 0;
    font-size: 0.9rem;
}

.delivery-info i {
    color: var(--success-color);
    margin-right: 0.5rem;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .cart-layout {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .cart-item {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }
    
    .item-image {
        margin-bottom: 1rem;
        margin-right: 0;
    }
    
    .item-details {
        width: 100%;
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .item-quantity {
        justify-content: center;
        margin: 1rem 0;
    }
    
    .cart-summary {
        position: static;
    }
}

/* Checkout Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
}

.checkout-modal-content {
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-hover);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: var(--primary-color);
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--light-text);
}

.close-btn:hover {
    color: var(--danger-color);
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--dark-text);
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
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
}

.order-summary {
    background: var(--light-bg);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    margin-top: 1rem;
}

.order-summary h4 {
    margin: 0 0 1rem 0;
    color: var(--primary-color);
}

.checkout-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-color);
}

.checkout-item:last-child {
    border-bottom: none;
}

.summary-totals {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid var(--border-color);
}

.summary-totals .summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.25rem 0;
}

.summary-totals .summary-row.total {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--primary-color);
    border-top: 1px solid var(--border-color);
    margin-top: 0.5rem;
    padding-top: 0.5rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: var(--secondary-color);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<script>
// Legacy cart logic removed; CartModule below is the single source of truth.
    // Promo code functionality
    document.getElementById('apply-promo').addEventListener('click', function() {
        const promoCode = document.getElementById('promo-code').value.trim().toUpperCase();
        
        // Mock promo codes
        const promoCodes = {
            'WELCOME15': { type: 'percentage', value: 15, minOrder: 500 },
            'VEGGIE30': { type: 'percentage', value: 30, minOrder: 500 },
            'DAIRY321': { type: 'fixed', value: 100, minOrder: 300 },
            'WEEKEND': { type: 'free_delivery', value: 0, minOrder: 0 }
        };
        
        if (promoCodes[promoCode]) {
            alert('Promo code applied successfully!');
            // Apply discount logic here
        } else {
            alert('Invalid promo code');
        }
    });
    
    // Checkout button
    document.getElementById('proceed-checkout').addEventListener('click', async function() {
        const currentCart = Array.isArray(window.__dokoCartData) ? window.__dokoCartData : (typeof cart !== 'undefined' ? cart : []);
        if (currentCart.length === 0) {
            alert('Your cart is empty!');
            return;
        }
        
        // Check if user is logged in
        try {
            const authResponse = await fetch('api/users/auth-status.php', { headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin' });
            const authResult = await authResponse.json();
            const logged = authResult && authResult.success && (authResult.isLoggedIn || authResult.logged_in || authResult.is_logged_in || (authResult.user && authResult.user.id));
            if (!logged) {
                if (confirm('Please login to place an order. Would you like to login now?')) {
                    window.location.href = 'login.php?redirect=' + encodeURIComponent('cart.php');
                }
                return;
            }
            
            // User is logged in, show checkout modal
            showCheckoutModal();
            
        } catch (error) {
            console.error('Error checking authentication:', error);
            if (confirm('Please login to place an order. Would you like to login now?')) {
                window.location.href = 'login.php?redirect=' + encodeURIComponent('cart.php');
            }
        }
    });
    
    // Show checkout modal
    function showCheckoutModal() {
        const modal = document.getElementById('checkout-modal');
        if (!modal) {
            createCheckoutModal();
        }
        document.getElementById('checkout-modal').style.display = 'flex';
        
        // Load user profile data for pre-filling
        loadUserProfile();
    }
    
    // Create checkout modal
    function createCheckoutModal() {
        const modal = document.createElement('div');
        modal.id = 'checkout-modal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content checkout-modal-content">
                <div class="modal-header">
                    <h3>Place Your Order</h3>
                    <button type="button" class="close-btn" onclick="hideCheckoutModal()">&times;</button>
                </div>
        <form id="checkout-form" class="modal-body">
                    <div class="form-group">
            <label for="checkout_delivery_address">Delivery Address *</label>
            <textarea id="checkout_delivery_address" name="delivery_address" class="form-control" rows="3" 
                                  placeholder="Enter your complete delivery address" required></textarea>
                    </div>
                    
                    <div class="form-group">
            <label for="checkout_phone">Phone Number *</label>
            <input type="tel" id="checkout_phone" name="phone" class="form-control" 
                               placeholder="+977-9851234567" required>
                    </div>
                    
                    <div class="form-group">
            <label for="checkout_payment_method">Payment Method</label>
            <select id="checkout_payment_method" name="payment_method" class="form-control" onchange="handlePaymentMethodChange()">
                            <option value="cash_on_delivery">Cash on Delivery</option>
                            <option value="online_payment">Online Payment (Digital Wallet/Bank Transfer)</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                        <div id="payment-details" style="display: none; margin-top: 1rem;">
                            <div class="payment-info">
                                <h5>Payment Details</h5>
                                <div id="online-payment-info" style="display: none;">
                                    <p><strong>For Online Payment:</strong></p>
                                    <ul>
                                        <li>eSewa: <code>9876543210</code></li>
                                        <li>Khalti: <code>9876543210</code></li>
                                        <li>IME Pay: <code>9876543210</code></li>
                                    </ul>
                                    <p><small>Please complete payment and provide transaction ID below.</small></p>
                                </div>
                                <div id="bank-transfer-info" style="display: none;">
                                    <p><strong>Bank Transfer Details:</strong></p>
                                    <ul>
                                        <li>Bank: Nepal Investment Bank</li>
                                        <li>A/C Name: DOKO Grocery Store</li>
                                        <li>A/C Number: <code>1234567890</code></li>
                                    </ul>
                                    <p><small>Please transfer amount and provide reference number.</small></p>
                                </div>
                                <div class="form-group" style="margin-top: 1rem;">
                                    <label for="checkout_transaction_id">Transaction ID / Reference Number</label>
                                    <input type="text" id="checkout_transaction_id" name="transaction_id" class="form-control" 
                                           placeholder="Enter transaction ID or reference number">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="checkout_special_instructions">Special Instructions (Optional)</label>
                        <textarea id="checkout_special_instructions" name="special_instructions" class="form-control" rows="2" 
                                  placeholder="Any special delivery instructions..."></textarea>
                    </div>
                    
                    <div class="order-summary">
                        <h4>Order Summary</h4>
                        <div class="summary-items" id="checkout-items"></div>
                        <div class="summary-totals">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="checkout-subtotal">Rs. 0</span>
                            </div>
                            <div class="summary-row">
                                <span>Delivery Charge:</span>
                                <span id="checkout-delivery">Rs. 50</span>
                            </div>
                            <div class="summary-row total">
                                <span>Total:</span>
                                <span id="checkout-total">Rs. 50</span>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideCheckoutModal()">Cancel</button>
                        <button type="button" class="btn btn-primary" id="place-order-btn">
                        <i class="fas fa-shopping-bag"></i> Place Order
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    // Hide checkout modal
    function hideCheckoutModal() {
        document.getElementById('checkout-modal').style.display = 'none';
    }
    
    // Load user profile for pre-filling
    async function loadUserProfile() {
        try {
            // Correct endpoint for fetching profile data is profile.php (GET)
            const response = await fetch('api/users/profile.php', {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'});
            let result=null; try { result = await response.json(); } catch(parseErr){ console.warn('Profile JSON parse failed', parseErr); }
            if (result && result.success && (result.user || result.data)) {
                const user = result.user || result.data;
                // We only have first_name/last_name/phone normally; address might come later
                if (user.phone) {
                    const phoneEl = document.getElementById('checkout_phone'); if(phoneEl && !phoneEl.value) phoneEl.value = user.phone;
                }
                // If future API returns address fields, combine them
                const addrEl = document.getElementById('checkout_delivery_address');
                if (addrEl && !addrEl.value) {
                    if (user.address) addrEl.value = user.address;
                    else if (user.street_address) {
                        const composed = [user.street_address, user.city, user.state].filter(Boolean).join(', ');
                        if (composed) addrEl.value = composed;
                    }
                }
            }
        } catch (error) {
            console.error('Error loading user profile:', error);
        }
        updateCheckoutSummary();
    }
    
    // Update checkout summary
    function updateCheckoutSummary() {
        const itemsContainer = document.getElementById('checkout-items');
        const subtotalEl = document.getElementById('checkout-subtotal');
        const deliveryEl = document.getElementById('checkout-delivery');
        const totalEl = document.getElementById('checkout-total');
        
        let itemsHtml = '';
        let subtotal = 0;
        
    (Array.isArray(window.__dokoCartData) ? window.__dokoCartData : []).forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            itemsHtml += `
                <div class="checkout-item">
                    <span>${item.name} x ${item.quantity}</span>
                    <span>Rs. ${itemTotal.toFixed(2)}</span>
                </div>
            `;
        });
        
        const deliveryCharge = subtotal >= 1000 ? 0 : 50;
        const total = subtotal + deliveryCharge;
        
        itemsContainer.innerHTML = itemsHtml;
        subtotalEl.textContent = `Rs. ${subtotal.toFixed(2)}`;
        deliveryEl.textContent = subtotal >= 1000 ? 'FREE' : 'Rs. 50';
        totalEl.textContent = `Rs. ${total.toFixed(2)}`;
    }
    
    // Place order
    async function placeOrder() {
        const form = document.getElementById('checkout-form');
        const formData = new FormData(form);
        
        // Validate required fields
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const currentCart = (typeof CartModule !== 'undefined' && CartModule && typeof CartModule.getItems === 'function')
            ? CartModule.getItems()
            : (Array.isArray(window.__dokoCartData) ? window.__dokoCartData : []);
        const orderData = {
            delivery_address: formData.get('delivery_address'),
            phone: formData.get('phone'),
            payment_method: formData.get('payment_method'),
            special_instructions: formData.get('special_instructions'),
            cart_items: currentCart.map(item => ({
                product_id: item.id,
                quantity: item.quantity,
                price: item.price
            }))
        };
        
        const submitBtn = document.querySelector('.modal-footer .btn-primary');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing Order...';
        
        try {
            // Primary: use legacy consolidated endpoint /api/orders/orders.php (expects shipping_* fields)
            let payload = {
                shipping_address: orderData.delivery_address,
                shipping_city: 'Kathmandu',
                shipping_state: 'Bagmati',
                shipping_zip: '00000',
                payment_method: (orderData.payment_method === 'online_payment') ? 'cash_on_delivery' : (orderData.payment_method||'cash_on_delivery')
            };
            const response = await fetch('api/orders/orders.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json','X-Requested-With':'XMLHttpRequest' },
                body: JSON.stringify(payload)
            });
            let text = await response.text();
            let result; try { result = JSON.parse(text); } catch(parseErr){ console.warn('Order JSON parse failed', parseErr, text); result = null; }
            // If deprecated (410) or not found, fallback to users/customer-orders.php contract
            if (!result || !result.success) {
                if (result && result.status === 410 || (response.status === 404 || response.status === 410)) {
                    const fallbackResp = await fetch('api/users/customer-orders.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json','X-Requested-With':'XMLHttpRequest' },
                        body: JSON.stringify(orderData)
                    });
                    const fbText = await fallbackResp.text();
                    try { result = JSON.parse(fbText); } catch(e){ console.warn('Fallback order parse failed', e, fbText); }
                }
            }
            if (result && result.success) {
                if (typeof CartModule !== 'undefined' && CartModule && typeof CartModule.clearAll === 'function') {
                    CartModule.clearAll();
                } else {
                    window.__dokoCartData = [];
                    try { localStorage.setItem(window.GUEST_CART_KEY||'doko_guest_cart_v1','[]'); } catch(e) {}
                }
                hideCheckoutModal();
                const on = (result.data && (result.data.order_number || result.data.order_id)) ? (result.data.order_number || 'ORDER-'+result.data.order_id) : 'ORDER';
                alert('Order placed successfully! Order Number: ' + on);
                window.location.href = 'profile.php?section=order-history';
            } else {
                alert('Error placing order: ' + (result ? result.message : 'Unknown server response'));
            }
        } catch (error) {
            console.error('Error placing order:', error);
            alert('Error placing order. Please try again.');
        }
        
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-shopping-bag"></i> Place Order';
    }
    
    // Make functions global
    window.hideCheckoutModal = hideCheckoutModal;
    window.placeOrder = placeOrder;
    window.handlePaymentMethodChange = handlePaymentMethodChange;
    
    function handlePaymentMethodChange() {
    const paymentMethod = document.getElementById('checkout_payment_method').value;
        const paymentDetails = document.getElementById('payment-details');
        const onlineInfo = document.getElementById('online-payment-info');
        const bankInfo = document.getElementById('bank-transfer-info');
        
        if (paymentMethod === 'cash_on_delivery') {
            paymentDetails.style.display = 'none';
        } else {
            paymentDetails.style.display = 'block';
            
            if (paymentMethod === 'online_payment') {
                onlineInfo.style.display = 'block';
                bankInfo.style.display = 'none';
            } else if (paymentMethod === 'bank_transfer') {
                onlineInfo.style.display = 'none';
                bankInfo.style.display = 'block';
            }
        }
    }
    
// (Obsolete legacy doko_cart code removed in favour of unified logic above)
</script>

<?php
// Include footer
include_footer();
?>
<script>
// Cart module injected after full HTML to avoid header emission issues
const CartModule = (function(){
    const API_BASE = (typeof getApiPath === 'function') ? getApiPath() : 'api/';
    const container = document.getElementById('cart-items-container');
    const emptyCartEl = document.getElementById('empty-cart');
    let cart = []; let loggedIn = false; window.__dokoCartData = cart;
    function getGuestCartRaw(){try{return JSON.parse(localStorage.getItem(window.GUEST_CART_KEY||'doko_guest_cart_v1'))||[]}catch(e){return[]}}
    function normalizeGuestCart(raw){return raw.map(it=>({product_id:it.product_id,id:it.product_id,quantity:it.quantity,name:it.name||'Item #'+it.product_id,price:parseFloat(it.price||0),image:it.image||'images/default-product.jpg'}));}
    async function isLoggedInFast(){
        try {
            const r = await fetch(API_BASE+'users/auth-status.php',{headers:{'X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'});
            const j = await r.json();
            // Support multiple possible flag names for backwards compatibility
            const flag = j && j.success && (j.isLoggedIn || j.logged_in || j.is_logged_in || (j.user && j.user.id));
            return !!flag;
        } catch(e){
            console.warn('isLoggedInFast failed', e);
            return false;
        }
    }
    async function fetchServerCart(){try{const r=await fetch(API_BASE+'cart/get.php',{headers:{'X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'});const j=await r.json();if(j.success&&Array.isArray(j.items)){return j.items.map(it=>({id:it.product_id,product_id:it.product_id,name:it.name,quantity:it.quantity,price:it.price,image:it.image||'images/default-product.jpg'}))}}catch(e){console.warn('Server cart fetch failed',e);}return[]}
    async function hydrateGuestDetails(items){if(!items.length)return items;const needs=items.some(it=>!it.name||!it.price||it.price===0);if(!needs)return items;try{const resp=await fetch(API_BASE+'products/bulk-details.php',{method:'POST',headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({product_ids:items.map(i=>i.product_id)})});const data=await resp.json();if(data.success&&data.items){const map=new Map(data.items.map(d=>[d.product_id,d]));return items.map(it=>{const d=map.get(it.product_id);return d?{...it,name:d.name,price:d.price,image:d.image}:it;})}}catch(e){console.warn('Guest hydrate failed',e);}return items}
    function escapeHtml(str){return(str||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]));}
    function updateCartSummary(){const subtotal=cart.reduce((s,it)=>s+(Number(it.price)||0)*it.quantity,0);const deliveryCharge=subtotal>=1000?0:50;const total=subtotal+deliveryCharge;const sEl=document.getElementById('cart-subtotal');if(!sEl)return; sEl.textContent=`Rs. ${subtotal.toFixed(2)}`;document.getElementById('delivery-charge').textContent=deliveryCharge===0?'FREE':`Rs. ${deliveryCharge.toFixed(2)}`;document.getElementById('cart-total').textContent=`Rs. ${total.toFixed(2)}`;if(typeof updateCartCount==='function')updateCartCount();}
    function renderCart(){
        if(!container||!emptyCartEl)return;
        if(!cart.length){
            emptyCartEl.style.display='block';
            container.querySelectorAll('.cart-item').forEach(el=>el.remove());
            updateCartSummary();
            return;
        }
        emptyCartEl.style.display='none';
        container.querySelectorAll('.cart-item').forEach(el=>el.remove());
        const frag=document.createDocumentFragment();
        cart.forEach((item,idx)=>{
            const div=document.createElement('div');
            div.className='cart-item';
            div.dataset.index=idx;
            const qtyId=`cart-qty-${item.product_id}-${idx}`; // guaranteed unique even if duplicate product ids appear
            div.innerHTML=`<div class=\"item-image\"><img src=\"${item.image}\" alt=\"${escapeHtml(item.name)}\" loading=\"lazy\"></div><div class=\"item-details\"><div class=\"item-name\">${escapeHtml(item.name)}</div><div class=\"item-price\">Rs. ${Number(item.price).toFixed(2)}</div><div class=\"item-quantity\"><button class=\"qty-btn qty-decrease\" data-index=\"${idx}\" type=\"button\" aria-label=\"Decrease quantity for ${escapeHtml(item.name)}\"><i class=\"fas fa-minus\"></i></button><input type=\"number\" class=\"qty-input\" id=\"${qtyId}\" name=\"cart_quantities[${item.product_id}]\" value=\"${item.quantity}\" min=\"1\" data-index=\"${idx}\" aria-label=\"Quantity for ${escapeHtml(item.name)}\" autocomplete=\"off\"><button class=\"qty-btn qty-increase\" data-index=\"${idx}\" type=\"button\" aria-label=\"Increase quantity for ${escapeHtml(item.name)}\"><i class=\"fas fa-plus\"></i></button></div></div><div class=\"item-total\">Rs. ${(Number(item.price)*item.quantity).toFixed(2)}</div><div class=\"remove-item\" data-index=\"${idx}\" role=\"button\" aria-label=\"Remove ${escapeHtml(item.name)}\"><i class=\"fas fa-trash\"></i></div>`;
            frag.appendChild(div);
        });
        if(emptyCartEl.parentNode){emptyCartEl.parentNode.insertBefore(frag, emptyCartEl);}else{emptyCartEl.before(frag);} 
        updateCartSummary();
    }
    function saveGuestCartBack(){if(loggedIn)return;try{localStorage.setItem(window.GUEST_CART_KEY||'doko_guest_cart_v1',JSON.stringify(cart.map(it=>({product_id:it.product_id,quantity:it.quantity,name:it.name,price:it.price,image:it.image}))))}catch(e){}}
    function qtyChange(idx,delta){if(!cart[idx])return;const n=cart[idx].quantity+delta;if(n<1)return;cart[idx].quantity=n;window.__dokoCartData=cart;saveGuestCartBack();renderCart();}
    function setQty(idx,val){if(!cart[idx])return;cart[idx].quantity=Math.max(1,val|0);window.__dokoCartData=cart;saveGuestCartBack();renderCart();}
    function removeItem(idx){cart.splice(idx,1);window.__dokoCartData=cart;saveGuestCartBack();renderCart();}
    function bind(){if(!container)return;container.addEventListener('click',e=>{const dec=e.target.closest('.qty-decrease');const inc=e.target.closest('.qty-increase');const rem=e.target.closest('.remove-item');if(dec)qtyChange(parseInt(dec.dataset.index,10),-1);if(inc)qtyChange(parseInt(inc.dataset.index,10),1);if(rem)removeItem(parseInt(rem.dataset.index,10));});container.addEventListener('change',e=>{if(e.target.classList.contains('qty-input'))setQty(parseInt(e.target.dataset.index,10),parseInt(e.target.value,10)||1);});document.body.addEventListener('click',e=>{if(e.target.id==='place-order-btn'&&typeof window.placeOrder==='function'){window.placeOrder();}});}
    async function init(){loggedIn=await isLoggedInFast();cart=loggedIn?await fetchServerCart():normalizeGuestCart(getGuestCartRaw());if(!loggedIn&&cart.some(it=>!it.name||!it.price))cart=await hydrateGuestDetails(cart);window.__dokoCartData=cart;bind();renderCart();}
    function getItems(){ return cart.slice(); }
    async function clearAll(){
        if(loggedIn){
            try { await fetch(API_BASE+'cart/clear.php',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'}); } catch(e){ console.warn('Server clear failed', e); }
        } else {
            try { localStorage.setItem(window.GUEST_CART_KEY||'doko_guest_cart_v1','[]'); } catch(e){}
        }
        cart = []; window.__dokoCartData = cart; renderCart();
    }
    return { init, getItems, clearAll };
})();
document.addEventListener('DOMContentLoaded',()=>{
    CartModule.init();
    // Wire clear cart button
    const clearBtn=document.getElementById('clear-cart');
    if(clearBtn){ clearBtn.addEventListener('click',()=>{ if(confirm('Clear all items from cart?')) CartModule.clearAll(); }); }
    // Debug helper: detect duplicate IDs (dev only)
    (function(){const seen=new Map();const dups=[];document.querySelectorAll('[id]').forEach(el=>{const id=el.id;if(seen.has(id)){dups.push(id);}else{seen.set(id,el);}});if(dups.length){console.warn('Duplicate IDs detected:',dups);} });
});
</script>

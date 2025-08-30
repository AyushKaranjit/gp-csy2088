<?php
/**
 * DOKO E-Commerce Website - Shopping Cart Page
 *
 * This website was developed as part of an academic project for educational purposes.
 * All code was written by the student developer to demonstrate web development skills.
 *
 * @author Student Developer
 * @version 1.0
 * @date 2025
 */

if (session_status() === PHP_SESSION_NONE) {
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

/* Validation Styles */
.form-group .error {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.field-error {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    font-weight: 500;
}

.validation-summary {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 0.375rem;
    padding: 0.75rem;
    margin-bottom: 1rem;
    color: #721c24;
}

.validation-summary strong {
    display: block;
    margin-bottom: 0.5rem;
}

.validation-summary ul {
    margin: 0.5rem 0 0 1.5rem;
    padding: 0;
}

.validation-summary li {
    margin-bottom: 0.25rem;
}

/* Enhanced form styling */
.checkout-modal-content .form-group {
    margin-bottom: 1.5rem;
}

.checkout-modal-content .form-control {
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.checkout-modal-content .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.checkout-modal-content .form-control.error:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>

<script>
// Legacy cart logic removed; CartModule below is the single source of truth.
    // Promo code functionality
    document.getElementById('apply-promo').addEventListener('click', function() {
        const promoCode = document.getElementById('promo-code').value.trim().toUpperCase();
        
        // Mock promo codes
        const promoCodes = {
            'VEGGIE30': { type: 'percentage', value: 30, minOrder: 500 },
            'DAIRY321': { type: 'fixed', value: 100, minOrder: 300 },
            'WEEKEND': { type: 'free_delivery', value: 0, minOrder: 0 },
            'RICE20': { type: 'percentage', value: 20, minOrder: 1000 }
        };
        
        if (promoCodes[promoCode]) {
            const code = promoCodes[promoCode];

            // Get current cart from CartModule or fallback
            const currentCart = (typeof CartModule !== 'undefined' && CartModule && typeof CartModule.getItems === 'function')
                ? CartModule.getItems()
                : (Array.isArray(window.__dokoCartData) ? window.__dokoCartData : []);

            const subtotal = currentCart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            // Check minimum order requirement
            if (subtotal < code.minOrder) {
                alert(`Minimum order of Rs. ${code.minOrder} required for this promo code.`);
                return;
            }

            // Apply discount
            let discount = 0;
            if (code.type === 'percentage') {
                discount = (subtotal * code.value) / 100;
            } else if (code.type === 'fixed') {
                discount = Math.min(code.value, subtotal);
            }

            // Store promo code info
            window.appliedPromoCode = {
                code: promoCode,
                discount: discount,
                type: code.type,
                value: code.value
            };

            // Update UI
            const discountEl = document.getElementById('discount-amount');
            const discountRowEl = document.getElementById('discount-row');
            if (discountEl) {
                discountEl.textContent = '-Rs. ' + discount.toFixed(2);
            }
            if (discountRowEl) {
                discountRowEl.style.display = 'flex';
            }

            document.getElementById('promo-code').value = promoCode;
            document.getElementById('promo-code').disabled = true;
            document.getElementById('apply-promo').textContent = 'Applied';
            document.getElementById('apply-promo').disabled = true;

            // Update totals if function exists
            if (typeof updateCartSummary === 'function') {
                updateCartSummary();
            }

            alert(`Promo code applied! You saved Rs. ${discount.toFixed(2)}`);
        } else {
            alert('Invalid promo code. Please check the offers page for valid codes.');
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
                               placeholder="+977 9851234567" required>
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
                                  placeholder="Any special delivery instructions..." maxlength="200"></textarea>
                        <small class="text-muted" id="instructions-counter" style="float: right; margin-top: 0.25rem;">0/200 characters</small>
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

        // Add phone number formatting after profile is loaded
        setupPhoneNumberFormatting();
    }

    // Setup phone number formatting
    function setupPhoneNumberFormatting() {
        const phoneInput = document.getElementById('checkout_phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Remove non-digits

                // Format as Nepali phone number
                if (value.length >= 10) {
                    if (value.startsWith('977')) {
                        // Already has country code, remove it for formatting
                        const withoutCountry = value.substring(3);
                        if (withoutCountry.length >= 10) {
                            e.target.value = `+977 ${withoutCountry.substring(0, 3)} ${withoutCountry.substring(3, 6)} ${withoutCountry.substring(6, 10)}`;
                        }
                    } else if (value.startsWith('0')) {
                        // Remove leading 0 and format
                        const withoutZero = value.substring(1);
                        if (withoutZero.length >= 9) {
                            e.target.value = `+977 ${withoutZero.substring(0, 3)} ${withoutZero.substring(3, 6)} ${withoutZero.substring(6, 9)}`;
                        }
                    } else {
                        // Direct 10-digit number
                        if (value.length >= 10) {
                            e.target.value = `+977 ${value.substring(0, 3)} ${value.substring(3, 6)} ${value.substring(6, 9)}`;
                        }
                    }
                } else if (value.length > 0) {
                    e.target.value = value; // Show as-is for incomplete numbers
                }
            });

            phoneInput.addEventListener('blur', function(e) {
                const value = e.target.value.trim();
                if (value) {
                    const cleanPhone = value.replace(/[\s\-\(\)\+]/g, '');
                    const phoneRegex = /^(\+977|977|0)?9[6789]\d{8}$/;

                    if (!phoneRegex.test(cleanPhone)) {
                        showFieldError(e.target, 'Please enter a valid Nepali phone number (e.g., +977 9851234567)');
                        // Don't clear the field, just show error
                    }
                }
            });
        }
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

    // Comprehensive form validation
    function validateCheckoutForm() {
        const deliveryAddress = document.getElementById('checkout_delivery_address');
        const phoneNumber = document.getElementById('checkout_phone');
        const paymentMethod = document.getElementById('checkout_payment_method');
        const specialInstructions = document.getElementById('checkout_special_instructions');
        const transactionId = document.getElementById('checkout_transaction_id');

        let isValid = true;
        let errors = [];

        // Clear previous error states
        clearValidationErrors();

        // 1. Delivery Address Validation
        const address = deliveryAddress.value.trim();
        if (!address) {
            showFieldError(deliveryAddress, 'Delivery address is required');
            errors.push('Delivery address is required');
            isValid = false;
        } else if (address.length < 10) {
            showFieldError(deliveryAddress, 'Please provide a complete delivery address (minimum 10 characters)');
            errors.push('Delivery address is too short');
            isValid = false;
        } else if (address.length > 500) {
            showFieldError(deliveryAddress, 'Delivery address is too long (maximum 500 characters)');
            errors.push('Delivery address is too long');
            isValid = false;
        }

        // 2. Phone Number Validation
        const phone = phoneNumber.value.trim();
        if (!phone) {
            showFieldError(phoneNumber, 'Phone number is required');
            errors.push('Phone number is required');
            isValid = false;
        } else {
            // Nepali phone number validation - more comprehensive pattern
            const phoneRegex = /^(\+977[\s\-]?|977[\s\-]?|0)?9[6789]\d{8}$/;
            const cleanPhone = phone.replace(/[\s\-\(\)\+]/g, '');

            if (!phoneRegex.test(cleanPhone)) {
                showFieldError(phoneNumber, 'Please enter a valid Nepali phone number (e.g., +977 9851234567, 9851234567, or 09851234567)');
                errors.push('Invalid phone number format');
                isValid = false;
            }
        }

        // 3. Payment Method Validation
        const selectedPayment = paymentMethod.value;
        if (!selectedPayment) {
            showFieldError(paymentMethod, 'Please select a payment method');
            errors.push('Payment method is required');
            isValid = false;
        }

        // 4. Transaction ID validation for online/bank transfer
        if ((selectedPayment === 'online_payment' || selectedPayment === 'bank_transfer') && transactionId) {
            const txId = transactionId.value.trim();
            if (!txId) {
                showFieldError(transactionId, 'Transaction ID/Reference number is required for this payment method');
                errors.push('Transaction ID is required');
                isValid = false;
            } else if (txId.length < 3) {
                showFieldError(transactionId, 'Transaction ID must be at least 3 characters long');
                errors.push('Transaction ID is too short');
                isValid = false;
            } else if (txId.length > 50) {
                showFieldError(transactionId, 'Transaction ID is too long (maximum 50 characters)');
                errors.push('Transaction ID is too long');
                isValid = false;
            }
        }

        // 5. Special Instructions Validation (optional but with limits)
        const instructions = specialInstructions.value.trim();
        if (instructions && instructions.length > 200) {
            showFieldError(specialInstructions, 'Special instructions are too long (maximum 200 characters)');
            errors.push('Special instructions are too long');
            isValid = false;
        }

        // 6. Check if cart is empty
        const currentCart = (typeof CartModule !== 'undefined' && CartModule && typeof CartModule.getItems === 'function')
            ? CartModule.getItems()
            : (Array.isArray(window.__dokoCartData) ? window.__dokoCartData : []);

        if (!currentCart || currentCart.length === 0) {
            alert('Your cart is empty. Please add some items before placing an order.');
            isValid = false;
        }

        // Show summary of errors if any
        if (!isValid && errors.length > 0) {
            showValidationSummary(errors);
        }

        return isValid;
    }

    // Show field-specific error
    function showFieldError(field, message) {
        field.classList.add('error');
        field.style.borderColor = '#dc3545';

        // Remove existing error message
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }

        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '0.25rem';
        errorDiv.textContent = message;

        field.parentNode.appendChild(errorDiv);
    }

    // Clear all validation errors
    function clearValidationErrors() {
        // Clear field errors
        const errorFields = document.querySelectorAll('.modal-body .error');
        errorFields.forEach(field => {
            field.classList.remove('error');
            field.style.borderColor = '';
        });

        // Remove error messages
        const errorMessages = document.querySelectorAll('.modal-body .field-error');
        errorMessages.forEach(msg => msg.remove());
    }

    // Show validation summary
    function showValidationSummary(errors) {
        // Remove existing summary
        const existingSummary = document.querySelector('.validation-summary');
        if (existingSummary) {
            existingSummary.remove();
        }

        // Create summary
        const summaryDiv = document.createElement('div');
        summaryDiv.className = 'validation-summary';
        summaryDiv.style.backgroundColor = '#f8d7da';
        summaryDiv.style.border = '1px solid #f5c6cb';
        summaryDiv.style.borderRadius = '0.375rem';
        summaryDiv.style.padding = '0.75rem';
        summaryDiv.style.marginBottom = '1rem';
        summaryDiv.style.color = '#721c24';

        summaryDiv.innerHTML = `
            <strong>Please fix the following errors:</strong>
            <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                ${errors.map(error => `<li>${error}</li>`).join('')}
            </ul>
        `;

        // Insert at the top of the form
        const form = document.getElementById('checkout-form');
        form.insertBefore(summaryDiv, form.firstChild);
    }
    
    // Place order
    async function placeOrder() {
        const form = document.getElementById('checkout-form');

        // Check if cart has items
        const currentCart = (typeof CartModule !== 'undefined' && CartModule && typeof CartModule.getItems === 'function')
            ? CartModule.getItems()
            : (Array.isArray(window.__dokoCartData) ? window.__dokoCartData : []);
            
        if (!currentCart || currentCart.length === 0) {
            alert('Your cart is empty. Please add items before placing an order.');
            return;
        }

        // Comprehensive validation
        if (!validateCheckoutForm()) {
            return;
        }

        // Collect form data
        const formData = new FormData(form);
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
        
        // Prevent multiple submissions
        if (submitBtn.disabled) {
            return; // Already processing
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing Order...';
        
        try {
            // Get user information first
            const userResponse = await fetch('api/users/profile.php', {
                headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                credentials:'same-origin'
            });
            const userResult = await userResponse.json();
            const user = userResult.success ? userResult.user || userResult.data : null;

            // Prepare order data in the correct format for place-order.php
            const orderPayload = {
                customer: {
                    first_name: user?.first_name || 'Customer',
                    last_name: user?.last_name || '',
                    email: user?.email || '',
                    phone: orderData.phone
                },
                delivery: {
                    address: orderData.delivery_address,
                    city: 'Kathmandu',
                    area: 'Kathmandu Valley',
                    delivery_date: new Date().toISOString().split('T')[0],
                    delivery_time: 'Anytime',
                    landmark: '',
                    delivery_notes: orderData.special_instructions || ''
                },
                payment_method: orderData.payment_method === 'online_payment' ? 'esewa' :
                              orderData.payment_method === 'bank_transfer' ? 'esewa' : 'cod',
                items: orderData.cart_items.map(item => ({
                    id: item.product_id,
                    quantity: item.quantity,
                    price: item.price
                })),
                total: currentCart.reduce((sum, item) => sum + (item.price * item.quantity), 0) +
                       (currentCart.reduce((sum, item) => sum + (item.price * item.quantity), 0) >= 1000 ? 0 : 50)
            };

            // Use the proper place-order endpoint
            console.log('Sending order payload:', orderPayload); // Debug log
            const response = await fetch('api/orders/place-order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json','X-Requested-With':'XMLHttpRequest' },
                body: JSON.stringify(orderPayload)
            });

            console.log('Response status:', response.status); // Debug log
            const result = await response.json();
            console.log('API response:', result); // Debug log
            if (result && result.success) {
                if (typeof CartModule !== 'undefined' && CartModule && typeof CartModule.clearAll === 'function') {
                    CartModule.clearAll();
                } else {
                    window.__dokoCartData = [];
                    try { localStorage.setItem(window.GUEST_CART_KEY||'doko_guest_cart_v1','[]'); } catch(e) {}
                }
                hideCheckoutModal();
                const orderId = result.order_id || (result.data && (result.data.order_id || result.data.id)) || 'N/A';
                
                // Store order data in sessionStorage as backup
                try {
                    sessionStorage.setItem('order_data', JSON.stringify({
                        order_id: orderId,
                        order_number: result.order_number || 'DOKO' + Date.now(),
                        customer: orderPayload.customer,
                        delivery: orderPayload.delivery,
                        payment_method: orderPayload.payment_method,
                        items: orderPayload.items,
                        total: orderPayload.total,
                        ordered_at: new Date().toISOString()
                    }));
                } catch(e) {
                    console.warn('Failed to store order data in sessionStorage:', e);
                }
                
                alert('Order placed successfully! Order ID: ' + orderId);
                window.location.href = 'order-confirmation.php?order_id=' + orderId;
            } else {
                const errorMessage = result ? (result.message || 'Unknown error occurred') : 'Server returned invalid response';
                console.error('Order placement failed:', errorMessage, result);
                alert('Error placing order: ' + errorMessage + '. Please try again.');
            }
        } catch (error) {
            console.error('Error placing order:', error);
            
            let errorMessage = 'Error placing order. Please try again.';
            
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                errorMessage = 'Network error. Please check your internet connection and try again.';
            } else if (error.message.includes('JSON')) {
                errorMessage = 'Server response error. Please try again or contact support.';
            } else if (error.message) {
                errorMessage = 'Error: ' + error.message;
            }
            
            alert(errorMessage);
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
include_footer([], "document.addEventListener('DOMContentLoaded',function(){const f=document.querySelector('.newsletter-form-element');if(f){f.addEventListener('submit',function(e){e.preventDefault();const email=this.querySelector('input[name=email]').value.trim();if(!email)return;fetch('api/newsletter.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email})}).then(r=>r.json()).then(d=>{alert(d.success?'Thank you for subscribing!':'Error: '+d.message);if(d.success) this.reset();}).catch(err=>{console.error('Newsletter error',err);alert('An error occurred.');});});}});");
?>
<script>
// Cart module injected after full HTML to avoid header emission issues
const CartModule = (function(){
    const API_BASE = (typeof getApiPath === 'function') ? getApiPath() : 'api/';
    const container = document.getElementById('cart-items-container');
    const emptyCartEl = document.getElementById('empty-cart');
    let cart = []; let loggedIn = false; window.__dokoCartData = cart;
    function getGuestCartRaw(){try{return JSON.parse(localStorage.getItem(window.GUEST_CART_KEY||'doko_guest_cart_v1'))||[]}catch(e){return[]}}
    function normalizeGuestCart(raw){return raw.map(it=>({product_id:it.product_id,id:it.product_id,quantity:it.quantity,name:it.name||'Item #'+it.product_id,price:parseFloat(it.price||0),image:it.image||'/images/default-product.jpg'}));}
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
    async function fetchServerCart(){try{const r=await fetch(API_BASE+'cart/get.php',{headers:{'X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'});const j=await r.json();if(j.success&&Array.isArray(j.items)){return j.items.map(it=>({id:it.product_id,product_id:it.product_id,name:it.name,quantity:it.quantity,price:it.price,image:it.image||'/images/default-product.jpg'}))}}catch(e){console.warn('Server cart fetch failed',e);}return[]}
    async function hydrateGuestDetails(items){if(!items.length)return items;const needs=items.some(it=>!it.name||!it.price||it.price===0);if(!needs)return items;try{const resp=await fetch(API_BASE+'products/bulk-details.php',{method:'POST',headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({product_ids:items.map(i=>i.product_id)})});const data=await resp.json();if(data.success&&data.items){const map=new Map(data.items.map(d=>[d.product_id,d]));return items.map(it=>{const d=map.get(it.product_id);return d?{...it,name:d.name,price:d.price,image:d.image}:it;})}}catch(e){console.warn('Guest hydrate failed',e);}return items}
    function escapeHtml(str){return(str||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]));}
    function updateCartSummary(){
        const subtotal = cart.reduce((s,it) => s + (Number(it.price)||0) * it.quantity, 0);
        const deliveryCharge = subtotal >= 1000 ? 0 : 50;
        const discount = window.appliedPromoCode ? window.appliedPromoCode.discount : 0;
        const total = subtotal + deliveryCharge - discount;

        const sEl = document.getElementById('cart-subtotal');
        if(!sEl) return;

        sEl.textContent = `Rs. ${subtotal.toFixed(2)}`;
        document.getElementById('delivery-charge').textContent = deliveryCharge === 0 ? 'FREE' : `Rs. ${deliveryCharge.toFixed(2)}`;

        // Update discount display
        const discountEl = document.getElementById('discount-amount');
        const discountRowEl = document.getElementById('discount-row');
        if (discount > 0 && discountEl && discountRowEl) {
            discountEl.textContent = `-Rs. ${discount.toFixed(2)}`;
            discountRowEl.style.display = 'flex';
        } else if (discountRowEl) {
            discountRowEl.style.display = 'none';
        }

        document.getElementById('cart-total').textContent = `Rs. ${total.toFixed(2)}`;
        if(typeof updateCartCount === 'function') updateCartCount();
    }
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
            div.innerHTML=`<div class=\"item-image\"><img src=\"${item.image}\" alt=\"${escapeHtml(item.name)}\" loading=\"lazy\" onerror=\"this.onerror=null;this.src='/images/default-product.jpg';\"></div><div class=\"item-details\"><div class=\"item-name\">${escapeHtml(item.name)}</div><div class=\"item-price\">Rs. ${Number(item.price).toFixed(2)}</div><div class=\"item-quantity\"><button class=\"qty-btn qty-decrease\" data-index=\"${idx}\" type=\"button\" aria-label=\"Decrease quantity for ${escapeHtml(item.name)}\"><i class=\"fas fa-minus\"></i></button><input type=\"number\" class=\"qty-input\" id=\"${qtyId}\" name=\"cart_quantities[${item.product_id}]\" value=\"${item.quantity}\" min=\"1\" data-index=\"${idx}\" aria-label=\"Quantity for ${escapeHtml(item.name)}\" autocomplete=\"off\"><button class=\"qty-btn qty-increase\" data-index=\"${idx}\" type=\"button\" aria-label=\"Increase quantity for ${escapeHtml(item.name)}\"><i class=\"fas fa-plus\"></i></button></div></div><div class=\"item-total\">Rs. ${(Number(item.price)*item.quantity).toFixed(2)}</div><div class=\"remove-item\" data-index=\"${idx}\" role=\"button\" aria-label=\"Remove ${escapeHtml(item.name)}\"><i class=\"fas fa-trash\"></i></div>`;
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
    return { init, getItems, clearAll, refresh: function(){ init(); } };
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

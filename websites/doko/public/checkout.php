<?php
/**
 * DOKO E-Commerce Website - Checkout Page
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
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../template/config.php';

// Check if user is logged in
$auth = new AuthController();
if (!$auth->isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode('checkout.php'));
    exit;
}

$currentUser = $auth->getCurrentUser();

// Page-specific variables
$page_title = page_title('Checkout');
$page_description = 'Complete your order and get fresh groceries delivered to your doorstep.';
$current_page = 'checkout';

$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Shopping Cart', 'url' => 'cart.php'],
    ['title' => 'Checkout', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php include __DIR__ . '/../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="checkout-header">
            <h1>Checkout</h1>
            <p>Complete your order details</p>
        </div>

        <div class="checkout-layout">
            <!-- Checkout Form -->
            <div class="checkout-form-section">
                <form id="checkout-form" class="checkout-form">
                    <!-- Customer Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Customer Information</h3>
                        <div class="customer-info-display">
                            <div class="customer-info-card">
                                <div class="customer-name">
                                    <strong><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></strong>
                                </div>
                                <div class="customer-email">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($currentUser['email']); ?>
                                </div>
                                <?php if (!empty($currentUser['phone'])): ?>
                                <div class="customer-phone">
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($currentUser['phone']); ?>
                                </div>
                                <?php endif; ?>
                                <a href="profile.php" class="edit-profile-link">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </a>
                            </div>
                        </div>
                        
                        <!-- Hidden fields for form submission -->
                        <input type="hidden" name="first_name" value="<?php echo htmlspecialchars($currentUser['first_name']); ?>">
                        <input type="hidden" name="last_name" value="<?php echo htmlspecialchars($currentUser['last_name']); ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>">
                        <input type="hidden" name="phone" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                    </div>

                    <!-- Delivery Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Delivery Address</h3>
                        <div class="form-group">
                            <label for="address">Street Address *</label>
                            <input type="text" id="address" name="address" placeholder="House/Building name, Street" required autocomplete="address-line1">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <select id="city" name="city" required>
                                    <option value="">Select City</option>
                                    <option value="kathmandu">Kathmandu</option>
                                    <option value="lalitpur">Lalitpur</option>
                                    <option value="bhaktapur">Bhaktapur</option>
                                    <option value="pokhara">Pokhara</option>
                                    <option value="chitwan">Chitwan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="area">Area/Locality *</label>
                                <input type="text" id="area" name="area" placeholder="e.g. Thamel, New Baneshwor" required autocomplete="address-level2">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="landmark">Landmark (Optional)</label>
                            <input type="text" id="landmark" name="landmark" placeholder="Nearby landmark for easy delivery" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="delivery_notes">Delivery Instructions (Optional)</label>
                            <textarea id="delivery_notes" name="delivery_notes" rows="3" placeholder="Any special instructions for delivery"></textarea>
                        </div>
                    </div>

                    <!-- Delivery Time -->
                    <div class="form-section">
                        <h3><i class="fas fa-clock"></i> Preferred Delivery Time</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="delivery_date">Delivery Date *</label>
                                <input type="date" id="delivery_date" name="delivery_date" required autocomplete="bday">
                            </div>
                            <div class="form-group">
                                <label for="delivery_time">Time Slot *</label>
                                <select id="delivery_time" name="delivery_time" required>
                                    <option value="">Select Time</option>
                                    <option value="morning">Morning (8:00 AM - 12:00 PM)</option>
                                    <option value="afternoon">Afternoon (12:00 PM - 5:00 PM)</option>
                                    <option value="evening">Evening (5:00 PM - 8:00 PM)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="form-section">
                        <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                        <div class="payment-methods">
                            <div class="payment-option">
                                <input type="radio" id="cod" name="payment_method" value="cod" checked>
                                <label for="cod">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Cash on Delivery</span>
                                    <small>Pay when you receive your order</small>
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="esewa" name="payment_method" value="esewa">
                                <label for="esewa">
                                    <i class="fab fa-cc-visa"></i>
                                    <span>eSewa</span>
                                    <small>Pay securely with eSewa</small>
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="khalti" name="payment_method" value="khalti">
                                <label for="khalti">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span>Khalti</span>
                                    <small>Digital wallet payment</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="order-summary-section">
                <div class="order-summary">
                    <h3>Order Summary</h3>
                    <div id="order-items"></div>
                    
                    <div class="summary-calculations">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="order-subtotal">Rs. 0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery Charge:</span>
                            <span id="order-delivery">Rs. 50.00</span>
                        </div>
                        <div class="summary-row discount-row" id="order-discount-row" style="display: none;">
                            <span>Discount:</span>
                            <span id="order-discount">- Rs. 0.00</span>
                        </div>
                        <div class="summary-row total-row">
                            <span>Total Amount:</span>
                            <span id="order-total">Rs. 50.00</span>
                        </div>
                    </div>
                    
                    <div class="promo-section">
                        <input type="text" id="checkout-promo" placeholder="Promo code" class="promo-input">
                        <button type="button" id="apply-checkout-promo" class="btn btn-outline btn-sm">Apply</button>
                    </div>
                    
                    <button type="submit" form="checkout-form" class="btn btn-primary btn-lg place-order-btn">
                        <i class="fas fa-check"></i> Place Order
                    </button>
                    
                    <div class="security-info">
                        <i class="fas fa-shield-alt"></i>
                        <span>Your order is secured with SSL encryption</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.checkout-header {
    text-align: center;
    margin-bottom: 2rem;
}

.checkout-header h1 {
    margin-bottom: 0.5rem;
}

.checkout-header p {
    color: var(--light-text);
}

.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
    align-items: start;
}

.checkout-form-section {
    background: var(--white);
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--shadow);
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border-color);
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section h3 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    color: var(--text-color);
}

.customer-info-card {
    background: var(--background-light);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.customer-name {
    font-size: 1.1rem;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.customer-email,
.customer-phone {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    color: var(--light-text);
    font-size: 0.9rem;
}

.edit-profile-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.9rem;
    margin-top: 0.75rem;
    transition: color 0.3s;
}

.edit-profile-link:hover {
    color: var(--primary-dark);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--dark-text);
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    font-size: 1rem;
    transition: var(--transition);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(45, 90, 39, 0.1);
}

.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.payment-option {
    position: relative;
}

.payment-option input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.payment-option label {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
}

.payment-option input[type="radio"]:checked + label {
    border-color: var(--primary-color);
    background: rgba(45, 90, 39, 0.05);
}

.payment-option label i {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.payment-option label span {
    font-weight: 600;
    color: var(--dark-text);
}

.payment-option label small {
    color: var(--light-text);
    margin-left: auto;
}

.order-summary {
    background: var(--white);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    position: sticky;
    top: 100px;
}

.order-summary h3 {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
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
    width: 50px;
    height: 50px;
    border-radius: var(--border-radius);
    overflow: hidden;
    margin-right: 0.75rem;
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
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.order-item-quantity {
    font-size: 0.8rem;
    color: var(--light-text);
}

.order-item-price {
    font-weight: 600;
    color: var(--primary-color);
}

.summary-calculations {
    margin: 1.5rem 0;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
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

.promo-section {
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

.place-order-btn {
    width: 100%;
    margin-bottom: 1rem;
}

.security-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: var(--light-text);
    justify-content: center;
}

.security-info i {
    color: var(--success-color);
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .checkout-layout {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .checkout-form-section {
        padding: 1.5rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .payment-option label {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .payment-option label small {
        margin-left: 0;
    }
    
    .order-summary {
        position: static;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load cart items from the global cart data
    const cart = window.__dokoCartData || [];
    
    // Set minimum delivery date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('delivery_date').min = tomorrow.toISOString().split('T')[0];
    
    // Validation functions
    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    function validatePhone(phone) {
        const cleanPhone = phone.replace(/[^\d+]/g, '');
        return /^(\+977|977|0)?[0-9]{7,10}$/.test(cleanPhone) && cleanPhone.replace(/[^\d]/g, '').length >= 10;
    }
    
    function validateAddress(address) {
        return address.trim().length >= 10 && address.trim().length <= 200;
    }
    
    function validateArea(area) {
        return area.trim().length >= 3 && area.trim().length <= 50;
    }
    
    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const existingError = field.parentElement.querySelector('.field-error');
        if (existingError) existingError.remove();
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.cssText = 'color: #dc3545; font-size: 0.875rem; margin-top: 0.5rem;';
        errorDiv.textContent = message;
        field.parentElement.appendChild(errorDiv);
        field.style.borderColor = '#dc3545';
    }
    
    function clearFieldError(fieldId) {
        const fieldElement = document.getElementById(fieldId);
        const existingError = fieldElement.parentElement.querySelector('.field-error');
        if (existingError) existingError.remove();
        fieldElement.style.borderColor = '#ddd';
    }

// Validation functions
function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validatePhone(phone) {
    const cleanPhone = phone.replace(/[^\d+]/g, '');
    return /^(\+977|977|0)?[0-9]{7,10}$/.test(cleanPhone) && cleanPhone.replace(/[^\d]/g, '').length >= 10;
}

function validateAddress(address) {
    return address.trim().length >= 10 && address.trim().length <= 200;
}

function validateArea(area) {
    return area.trim().length >= 3 && area.trim().length <= 50;
}

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const existingError = field.parentElement.querySelector('.field-error');
    if (existingError) existingError.remove();

    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.cssText = 'color: #dc3545; font-size: 0.875rem; margin-top: 0.5rem;';
    errorDiv.textContent = message;
    field.parentElement.appendChild(errorDiv);
    field.style.borderColor = '#dc3545';
}

function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    const error = field.parentElement.querySelector('.field-error');
    if (error) error.remove();
    field.style.borderColor = '#ddd';
}
    
    // Load order items
    function loadOrderItems() {
        const container = document.getElementById('order-items');
        
        if (cart.length === 0) {
            window.location.href = 'cart.php';
            return;
        }
        
        let itemsHTML = '';
        cart.forEach(item => {
            itemsHTML += `
                <div class="order-item">
                    <div class="order-item-image">
                        <img src="${item.image}" alt="${item.name}" loading="lazy">
                    </div>
                    <div class="order-item-details">
                        <div class="order-item-name">${item.name}</div>
                        <div class="order-item-quantity">Qty: ${item.quantity}</div>
                    </div>
                    <div class="order-item-price">Rs. ${(item.price * item.quantity).toFixed(2)}</div>
                </div>
            `;
        });
        
        container.innerHTML = itemsHTML;
        updateOrderSummary();
    }
    
    // Update order summary
    function updateOrderSummary() {
        const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        const deliveryCharge = subtotal >= 1000 ? 0 : 50;
        const discount = 0; // Will be calculated based on promo codes
        const total = subtotal + deliveryCharge - discount;
        
        document.getElementById('order-subtotal').textContent = `Rs. ${subtotal.toFixed(2)}`;
        document.getElementById('order-delivery').textContent = deliveryCharge === 0 ? 'FREE' : `Rs. ${deliveryCharge.toFixed(2)}`;
        document.getElementById('order-total').textContent = `Rs. ${total.toFixed(2)}`;
    }
    
    // Form submission with validation
    let checkoutSubmitting = false;
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        e.preventDefault();
        if (checkoutSubmitting) return;
        checkoutSubmitting = true;

        // Collect form data
        const formData = new FormData(this);
        const orderData = {
            customer: {
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                email: formData.get('email'),
                phone: formData.get('phone')
            },
            delivery: {
                address: formData.get('address'),
                city: formData.get('city'),
                area: formData.get('area'),
                landmark: formData.get('landmark'),
                delivery_notes: formData.get('delivery_notes'),
                delivery_date: formData.get('delivery_date'),
                delivery_time: formData.get('delivery_time')
            },
            payment_method: formData.get('payment_method'),
            items: cart.map(item => ({
                id: item.product_id || item.id,
                name: item.name,
                quantity: item.quantity,
                price: item.price,
                image: item.image
            })),
            total: parseFloat(document.getElementById('order-total').textContent.replace('Rs. ', ''))
        };
        
        // Enhanced client-side validation
        const required = [
            { el: 'address', name: 'Address', validator: validateAddress },
            { el: 'city', name: 'City' },
            { el: 'area', name: 'Area', validator: validateArea },
            { el: 'delivery_date', name: 'Delivery Date' },
            { el: 'delivery_time', name: 'Delivery Time' }
        ];
        
        for (const r of required) {
            const v = (formData.get(r.el) || '').toString().trim();
            if (!v) { 
                showFieldError(r.el, `${r.name} is required.`);
                checkoutSubmitting = false; 
                return; 
            }
            
            // Additional validation for specific fields
            if (r.validator && !r.validator(v)) {
                let message = `${r.name} is invalid.`;
                if (r.el === 'address') message = 'Address must be 10-200 characters long.';
                if (r.el === 'area') message = 'Area must be 3-50 characters long.';
                showFieldError(r.el, message);
                checkoutSubmitting = false;
                return;
            }
        }

        // Validate delivery date is not in the past
        const deliveryDate = formData.get('delivery_date');
        if (deliveryDate) {
            const sel = new Date(deliveryDate);
            const today = new Date(); today.setHours(0,0,0,0);
            if (sel < today) { alert('Delivery date cannot be in the past.'); checkoutSubmitting = false; return; }
        }

        // Ensure payment method selected
        if (!formData.get('payment_method')) { alert('Please select a payment method.'); checkoutSubmitting = false; return; }

        const submitBtn = document.querySelector('.place-order-btn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        submitBtn.disabled = true;

        // Send order data to API
        fetch('api/orders/place-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear cart using CartModule if available
                if (typeof CartModule !== 'undefined' && CartModule && typeof CartModule.clearAll === 'function') {
                    CartModule.clearAll();
                } else {
                    // Fallback: clear localStorage
                    localStorage.removeItem('doko_cart');
                    window.__dokoCartData = [];
                }
                // Redirect to order confirmation
                window.location.href = data.data.redirect || 'order-confirmation.php';
            } else {
                throw new Error(data.message || 'Failed to place order');
            }
        })
        .catch(error => {
            console.error('Order placement error:', error);
            alert('Error placing order. Please try again.');
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            checkoutSubmitting = false;
        });
    });
    
    // Promo code functionality
    document.getElementById('apply-checkout-promo').addEventListener('click', function() {
        const promoCode = document.getElementById('checkout-promo').value.trim().toUpperCase();
        
        // Mock promo codes (same as cart page)
        const promoCodes = {
            'VEGGIE30': { type: 'percentage', value: 30, minOrder: 500 },
            'DAIRY321': { type: 'fixed', value: 100, minOrder: 300 },
            'WEEKEND': { type: 'free_delivery', value: 0, minOrder: 0 },
            'RICE20': { type: 'percentage', value: 20, minOrder: 1000 }
        };
        
        if (promoCodes[promoCode]) {
            const code = promoCodes[promoCode];
            const subtotalEl = document.getElementById('checkout-subtotal');
            const subtotal = subtotalEl ? parseFloat(subtotalEl.textContent.replace('Rs. ', '')) || 0 : 0;

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
            const discountEl = document.getElementById('checkout-discount');
            if (discountEl) {
                discountEl.textContent = '-Rs. ' + discount.toFixed(2);
            }

            document.getElementById('checkout-promo').value = promoCode;
            document.getElementById('checkout-promo').disabled = true;
            document.getElementById('apply-checkout-promo').textContent = 'Applied';
            document.getElementById('apply-checkout-promo').disabled = true;

            // Update totals
            updateCheckoutTotals();

            alert(`Promo code applied! You saved Rs. ${discount.toFixed(2)}`);
        } else {
            alert('Invalid promo code. Please check the offers page for valid codes.');
        }
    });
    
    // Function to update checkout totals
    function updateCheckoutTotals() {
        const subtotalEl = document.getElementById('order-subtotal');
        const deliveryEl = document.getElementById('order-delivery');
        const discountEl = document.getElementById('order-discount');
        const discountRowEl = document.getElementById('order-discount-row');
        const totalEl = document.getElementById('order-total');
        
        if (!subtotalEl || !totalEl) return;
        
        const subtotal = parseFloat(subtotalEl.textContent.replace('Rs. ', '')) || 0;
        const deliveryCharge = subtotal >= 1000 ? 0 : 50;
        const discount = window.appliedPromoCode ? window.appliedPromoCode.discount : 0;
        const total = subtotal + deliveryCharge - discount;
        
        // Update delivery charge
        if (deliveryEl) {
            deliveryEl.textContent = deliveryCharge === 0 ? 'FREE' : 'Rs. ' + deliveryCharge.toFixed(2);
        }
        
        // Update discount display
        if (discount > 0 && discountEl && discountRowEl) {
            discountEl.textContent = '-Rs. ' + discount.toFixed(2);
            discountRowEl.style.display = 'flex';
        } else if (discountRowEl) {
            discountRowEl.style.display = 'none';
        }
        
        // Update total
        totalEl.textContent = 'Rs. ' + total.toFixed(2);
    }
    
    // Initialize
    loadOrderItems();
});
</script>

<?php
// Include footer
include_footer([], "document.addEventListener('DOMContentLoaded',function(){const f=document.querySelector('.newsletter-form-element');if(f){f.addEventListener('submit',function(e){e.preventDefault();const email=this.querySelector('input[name=email]').value.trim();if(!email)return;fetch('api/newsletter.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email})}).then(r=>r.json()).then(d=>{alert(d.success?'Thank you for subscribing!':'Error: '+d.message);if(d.success) this.reset();}).catch(err=>{console.error('Newsletter error',err);alert('An error occurred.');});});}});");
?>

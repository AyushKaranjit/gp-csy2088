<?php
// Set page variables
$page_title = 'Checkout - DOKO';
$current_page = 'checkout';
$additional_css = ['css/payment.css'];
$additional_js = ['payment.js'];

// Include header template
include_once '../template/header.php';
?>

    <div class="checkout-container">
        <div class="container">
            <div class="checkout-header">
                <h1>Checkout</h1>
                <div class="checkout-steps">
                    <div class="step active">
                        <span class="step-number">1</span>
                        <span class="step-label">Delivery</span>
                    </div>
                    <div class="step">
                        <span class="step-number">2</span>
                        <span class="step-label">Payment</span>
                    </div>
                    <div class="step">
                        <span class="step-number">3</span>
                        <span class="step-label">Confirmation</span>
                    </div>
                </div>
            </div>
            
            <div class="checkout-content">
                <div class="checkout-form">
                    <form id="checkout-form">
                        <!-- Delivery Information -->
                        <div class="form-section">
                            <h3>Delivery Information</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" id="first_name" name="first_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Delivery Address</label>
                                <textarea id="address" name="address" rows="3" placeholder="House/Apartment number, Street name, Area" required></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <select id="city" name="city" required>
                                        <option value="">Select City</option>
                                        <option value="kathmandu">Kathmandu</option>
                                        <option value="lalitpur">Lalitpur</option>
                                        <option value="bhaktapur">Bhaktapur</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="area">Area</label>
                                    <input type="text" id="area" name="area" placeholder="e.g., Thamel, Patan, etc." required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Delivery Time -->
                        <div class="form-section">
                            <h3>Delivery Time</h3>
                            <div class="delivery-options">
                                <label class="delivery-option">
                                    <input type="radio" name="delivery_time" value="asap" checked>
                                    <div class="option-content">
                                        <strong>As Soon As Possible</strong>
                                        <span>2-4 hours</span>
                                    </div>
                                </label>
                                <label class="delivery-option">
                                    <input type="radio" name="delivery_time" value="scheduled">
                                    <div class="option-content">
                                        <strong>Scheduled Delivery</strong>
                                        <span>Choose your preferred time</span>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="scheduled-options" style="display: none;">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="delivery_date">Delivery Date</label>
                                        <input type="date" id="delivery_date" name="delivery_date">
                                    </div>
                                    <div class="form-group">
                                        <label for="delivery_time_slot">Time Slot</label>
                                        <select id="delivery_time_slot" name="delivery_time_slot">
                                            <option value="">Select Time</option>
                                            <option value="morning">Morning (8 AM - 12 PM)</option>
                                            <option value="afternoon">Afternoon (12 PM - 4 PM)</option>
                                            <option value="evening">Evening (4 PM - 8 PM)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="form-section">
                            <h3>Payment Method</h3>
                            <div class="payment-methods">
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="cod" checked>
                                    <div class="method-content">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <div>
                                            <strong>Cash on Delivery</strong>
                                            <span>Pay when you receive your order</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="esewa">
                                    <div class="method-content">
                                        <i class="fas fa-mobile-alt"></i>
                                        <div>
                                            <strong>eSewa</strong>
                                            <span>Pay securely with eSewa</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="khalti">
                                    <div class="method-content">
                                        <i class="fas fa-credit-card"></i>
                                        <div>
                                            <strong>Khalti</strong>
                                            <span>Pay with Khalti wallet</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Order Notes -->
                        <div class="form-section">
                            <h3>Order Notes (Optional)</h3>
                            <div class="form-group">
                                <textarea id="order_notes" name="order_notes" rows="3" placeholder="Any special instructions for delivery..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-card">
                        <h3>Order Summary</h3>
                        
                        <div class="order-items" id="checkout-items">
                            <!-- Order items will be loaded here -->
                        </div>
                        
                        <div class="summary-calculations">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="checkout-subtotal">रू 0</span>
                            </div>
                            <div class="summary-row">
                                <span>Delivery Fee:</span>
                                <span id="checkout-delivery-fee">रू 50</span>
                            </div>
                            <div class="summary-row total">
                                <span>Total:</span>
                                <span id="checkout-total">रू 0</span>
                            </div>
                        </div>
                        
                        <button type="button" class="place-order-btn" onclick="placeOrder()">
                            Place Order
                        </button>
                    </div>
                    
                    <div class="delivery-guarantee">
                        <h4><i class="fas fa-shield-alt"></i> Our Guarantee</h4>
                        <ul>
                            <li>Fresh products or money back</li>
                            <li>Same day delivery</li>
                            <li>Secure payment</li>
                            <li>24/7 customer support</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadCheckoutItems();
            updateCheckoutSummary();
            
            // Handle delivery time radio buttons
            document.querySelectorAll('input[name="delivery_time"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const scheduledOptions = document.querySelector('.scheduled-options');
                    if (this.value === 'scheduled') {
                        scheduledOptions.style.display = 'block';
                    } else {
                        scheduledOptions.style.display = 'none';
                    }
                });
            });
        });

        function loadCheckoutItems() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const checkoutItems = document.getElementById('checkout-items');
            
            if (cart.length === 0) {
                window.location.href = 'cart.php';
                return;
            }
            
            checkoutItems.innerHTML = '';
            
            cart.forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.className = 'order-item';
                itemElement.innerHTML = `
                    <div class="item-image">
                        <img src="${item.image || 'https://via.placeholder.com/60'}" alt="${item.name}">
                    </div>
                    <div class="item-details">
                        <h5>${item.name}</h5>
                        <p>Qty: ${item.quantity}</p>
                    </div>
                    <div class="item-price">
                        रू ${item.price * item.quantity}
                    </div>
                `;
                checkoutItems.appendChild(itemElement);
            });
        }

        function updateCheckoutSummary() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            let subtotal = 0;
            
            cart.forEach(item => {
                subtotal += item.price * item.quantity;
            });
            
            const deliveryFee = subtotal >= 1000 ? 0 : 50;
            const total = subtotal + deliveryFee;
            
            document.getElementById('checkout-subtotal').textContent = `रू ${subtotal}`;
            document.getElementById('checkout-delivery-fee').textContent = deliveryFee === 0 ? 'Free' : `रू ${deliveryFee}`;
            document.getElementById('checkout-total').textContent = `रू ${total}`;
        }

        function placeOrder() {
            const form = document.getElementById('checkout-form');
            const formData = new FormData(form);
            
            // Validate required fields
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Get cart data
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const orderData = {
                items: cart,
                customer: Object.fromEntries(formData),
                timestamp: new Date().toISOString(),
                orderId: 'DOKO' + Date.now()
            };
            
            // In a real application, this would be sent to the server
            console.log('Order placed:', orderData);
            
            // Store order for confirmation page
            localStorage.setItem('lastOrder', JSON.stringify(orderData));
            
            // Clear cart
            localStorage.removeItem('cart');
            
            // Redirect to confirmation
            window.location.href = 'order-confirmation.php?order=' + orderData.orderId;
        }
    </script>

<?php
// Include footer template
include_once '../template/footer.php';
?>

<?php
// Set page variables
$page_title = 'Shopping Cart - DOKO';
$current_page = 'cart';
$additional_css = ['css/cart.css'];
$additional_js = ['cart.js'];

// Include header template
include_once '../template/header.php';
?>

    <div class="cart-container">
        <div class="container">
            <div class="cart-header">
                <h1>Shopping Cart</h1>
                <p class="cart-subtitle">Review your items before checkout</p>
            </div>
            
            <div class="cart-content">
                <div class="cart-items">
                    <div class="cart-item" id="empty-cart" style="display: none;">
                        <div class="empty-cart-content">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Your cart is empty</h3>
                            <p>Add some fresh groceries to get started!</p>
                            <a href="category.php" class="continue-shopping-btn">Continue Shopping</a>
                        </div>
                    </div>
                    
                    <!-- Cart items will be dynamically loaded here -->
                    <div id="cart-items-list">
                        <!-- Dynamic content -->
                    </div>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-card">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="cart-subtotal">रू 0</span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Delivery Fee:</span>
                            <span id="delivery-fee">रू 50</span>
                        </div>
                        
                        <div class="summary-row discount" style="display: none;">
                            <span>Discount:</span>
                            <span id="discount-amount">रू 0</span>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span id="cart-total">रू 0</span>
                        </div>
                        
                        <div class="promo-section">
                            <input type="text" placeholder="Enter promo code" id="promo-input">
                            <button type="button" id="apply-promo">Apply</button>
                        </div>
                        
                        <button class="checkout-btn" id="checkout-btn" disabled>
                            Proceed to Checkout
                        </button>
                        
                        <a href="category.php" class="continue-shopping">Continue Shopping</a>
                    </div>
                    
                    <div class="delivery-info-card">
                        <h4><i class="fas fa-truck"></i> Delivery Information</h4>
                        <p>Free delivery on orders over रू 1000</p>
                        <p>Same day delivery available</p>
                        <p>Delivery time: 2-4 hours</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Cart functionality will be handled by cart.js
        document.addEventListener('DOMContentLoaded', function() {
            loadCart();
            updateCartSummary();
        });

        function loadCart() {
            // Load cart items from localStorage or session
            const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            const cartItemsList = document.getElementById('cart-items-list');
            const emptyCart = document.getElementById('empty-cart');
            
            if (cartItems.length === 0) {
                emptyCart.style.display = 'block';
                cartItemsList.style.display = 'none';
            } else {
                emptyCart.style.display = 'none';
                cartItemsList.style.display = 'block';
                renderCartItems(cartItems);
            }
        }

        function renderCartItems(items) {
            const cartItemsList = document.getElementById('cart-items-list');
            cartItemsList.innerHTML = '';
            
            items.forEach((item, index) => {
                const cartItem = createCartItemElement(item, index);
                cartItemsList.appendChild(cartItem);
            });
        }

        function createCartItemElement(item, index) {
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.innerHTML = `
                <div class="item-image">
                    <img src="${item.image || 'https://via.placeholder.com/80'}" alt="${item.name}">
                </div>
                <div class="item-details">
                    <h4>${item.name}</h4>
                    <p class="item-price">रू ${item.price}</p>
                </div>
                <div class="item-quantity">
                    <button onclick="updateQuantity(${index}, ${item.quantity - 1})">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="updateQuantity(${index}, ${item.quantity + 1})">+</button>
                </div>
                <div class="item-total">
                    रू ${item.price * item.quantity}
                </div>
                <button class="remove-item" onclick="removeFromCart(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            return cartItem;
        }

        function updateQuantity(index, newQuantity) {
            if (newQuantity <= 0) {
                removeFromCart(index);
                return;
            }
            
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart[index].quantity = newQuantity;
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCart();
            updateCartSummary();
        }

        function removeFromCart(index) {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCart();
            updateCartSummary();
        }

        function updateCartSummary() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            let subtotal = 0;
            
            cart.forEach(item => {
                subtotal += item.price * item.quantity;
            });
            
            const deliveryFee = subtotal >= 1000 ? 0 : 50;
            const total = subtotal + deliveryFee;
            
            document.getElementById('cart-subtotal').textContent = `रू ${subtotal}`;
            document.getElementById('delivery-fee').textContent = deliveryFee === 0 ? 'Free' : `रू ${deliveryFee}`;
            document.getElementById('cart-total').textContent = `रू ${total}`;
            
            const checkoutBtn = document.getElementById('checkout-btn');
            checkoutBtn.disabled = cart.length === 0;
            
            if (cart.length > 0) {
                checkoutBtn.onclick = () => window.location.href = 'payment.php';
            }
        }
    </script>

<?php
// Include footer template
include_once '../template/footer.php';
?>

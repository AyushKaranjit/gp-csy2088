// Enhanced Shopping Cart JavaScript with API Integration

// Load enhanced cart manager if available
function loadEnhancedCart() {
    const script = document.createElement('script');
    script.src = 'enhanced-cart.js';
    script.onload = function() {
        if (typeof cartManager !== 'undefined') {
            console.log('Enhanced cart manager loaded');
            // Initialize enhanced cart
            cartManager.updateCartDisplay();
            cartManager.updateCartCount();
        }
    };
    document.head.appendChild(script);
}

// Check if enhanced cart is already available
if (typeof cartManager === 'undefined') {
    loadEnhancedCart();
}

// Cart data storage (fallback for compatibility)
let cart = JSON.parse(localStorage.getItem('doko-cart')) || [];

// DOM elements
const cartItemsList = document.getElementById('cart-items-list');
const cartSubtotal = document.getElementById('cart-subtotal');
const cartTotal = document.getElementById('cart-total');
const cartCountDisplay = document.querySelector('.cart-count');
const deliveryFee = 50;

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        updateCartDisplay();
        updateCartCount();
    }, 100); // Small delay to ensure enhanced cart is loaded
});

// Enhanced Add item to cart with API integration
function addToCart(product, price, quantity = 1, productId = null, imageUrl = '') {
    // Use enhanced cart manager if available
    if (typeof cartManager !== 'undefined') {
        cartManager.addToCart(
            productId || generateProductId(product), 
            product, 
            price, 
            quantity, 
            imageUrl || getProductImage(product)
        );
        return;
    }
    
    // Fallback to simple cart functionality
    const existingItem = cart.find(item => item.product === product);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: Date.now(),
            product: product,
            price: parseFloat(price),
            quantity: quantity,
            image: imageUrl || getProductImage(product),
            productId: productId || generateProductId(product)
        });
    }
    
    saveCart();
    updateCartDisplay();
    updateCartCount();
    showCartNotification('Item added to cart!');
}

// Generate product ID from name
function generateProductId(productName) {
    return productName.toLowerCase().replace(/[^a-z0-9]/g, '');
}

// Remove item from cart
function removeFromCart(itemId) {
    if (typeof cartManager !== 'undefined') {
        cartManager.removeFromCart(itemId);
        return;
    }
    
    cart = cart.filter(item => item.id !== itemId);
    saveCart();
    updateCartDisplay();
    updateCartCount();
}

// Update item quantity
function updateQuantity(itemId, newQuantity) {
    if (typeof cartManager !== 'undefined') {
        cartManager.updateQuantity(itemId, newQuantity);
        return;
    }
    
    const item = cart.find(item => item.id === itemId);
    if (item) {
        if (newQuantity <= 0) {
            removeFromCart(itemId);
        } else {
            item.quantity = newQuantity;
            saveCart();
            updateCartDisplay();
            updateCartCount();
        }
    }
}

// Clear entire cart
function clearCart() {
    if (typeof cartManager !== 'undefined') {
        cartManager.clearCart();
        return;
    }
    
    if (confirm('Are you sure you want to clear your cart?')) {
        cart = [];
        saveCart();
        updateCartDisplay();
        updateCartCount();
        showCartNotification('Cart cleared!');
    }
}

// Save cart to localStorage
function saveCart() {
    localStorage.setItem('doko-cart', JSON.stringify(cart));
}

// Update cart display
function updateCartDisplay() {
    if (typeof cartManager !== 'undefined') {
        cartManager.updateCartDisplay();
        return;
    }
    
    if (!cartItemsList) return;

    if (cart.length === 0) {
        cartItemsList.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart empty-cart-icon"></i>
                <h3>Your cart is empty</h3>
                <p>Add some products to get started!</p>
                <a href="category.html" class="continue-shopping-btn">Continue Shopping</a>
            </div>
        `;
    } else {
        cartItemsList.innerHTML = cart.map(item => `
            <div class="cart-item" data-id="${item.id}">
                <div class="cart-item-image">
                    <img src="${item.image}" alt="${item.product}" onerror="this.src='https://via.placeholder.com/80x80?text=Product'">
                </div>
                <div class="cart-item-details">
                    <h3 class="cart-item-name">${item.product}</h3>
                    <p class="cart-item-price">रू ${item.price}</p>
                </div>
                <div class="cart-item-quantity">
                    <button class="quantity-btn minus" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                    <span class="quantity-display">${item.quantity}</span>
                    <button class="quantity-btn plus" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                </div>
                <div class="cart-item-total">
                    <span class="item-total">रू ${(item.price * item.quantity).toFixed(0)}</span>
                </div>
                <button class="remove-item-btn" onclick="removeFromCart(${item.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `).join('');
    }
    
    updateCartSummary();
}

// Update cart summary
function updateCartSummary() {
    const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    const total = subtotal + (cart.length > 0 ? deliveryFee : 0);
    
    if (cartSubtotal) cartSubtotal.textContent = `रू ${subtotal.toFixed(0)}`;
    if (cartTotal) cartTotal.textContent = `रू ${total.toFixed(0)}`;
}

// Update cart count in header
function updateCartCount() {
    if (typeof cartManager !== 'undefined') {
        cartManager.updateCartCount();
        return;
    }
    
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    const countElements = document.querySelectorAll('.cart-count');
    
    countElements.forEach(element => {
        element.textContent = totalItems;
        element.style.display = totalItems > 0 ? 'block' : 'none';
    });
}

// Get product image based on product name
function getProductImage(productName) {
    const imageMap = {
        'Apple': 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=300&h=300&fit=crop&crop=center',
        'Fresh Apple': 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=300&h=300&fit=crop&crop=center',
        'Banana': 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=300&h=300&fit=crop&crop=center',
        'Kiwi': 'https://images.unsplash.com/photo-1585059895524-72359e06133a?w=300&h=300&fit=crop&crop=center',
        'Kiwi Fruit': 'https://images.unsplash.com/photo-1585059895524-72359e06133a?w=300&h=300&fit=crop&crop=center',
        'Tomato': 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=300&h=300&fit=crop&crop=center',
        'Fresh Tomato': 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=300&h=300&fit=crop&crop=center',
        'Spices': 'https://images.unsplash.com/photo-1506976785307-8732e854ad03?w=300&h=300&fit=crop&crop=center',
        'Milk': 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=300&h=300&fit=crop&crop=center',
        'Fresh Milk': 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=300&h=300&fit=crop&crop=center',
        'Cheese': 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=300&h=300&fit=crop&crop=center',
        'Water': 'https://images.unsplash.com/photo-1523362628745-0c100150b504?w=300&h=300&fit=crop&crop=center',
        'Carrot': 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=300&h=300&fit=crop&crop=center',
        'Broccoli': 'https://images.unsplash.com/photo-1459411621453-7b03977f4bfc?w=300&h=300&fit=crop&crop=center',
        'Pepper': 'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?w=300&h=300&fit=crop&crop=center',
        'Onion': 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=300&h=300&fit=crop&crop=center',
        'Potato': 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=300&h=300&fit=crop&crop=center',
        'Orange': 'https://images.unsplash.com/photo-1547036967-23d11aacaee0?w=300&h=300&fit=crop&crop=center',
        'Grapes': 'https://images.unsplash.com/photo-1537640538966-79f369143f8f?w=300&h=300&fit=crop&crop=center',
        'Strawberry': 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=300&h=300&fit=crop&crop=center'
    };
    return imageMap[productName] || 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=300&h=300&fit=crop&crop=center';
}

// Apply promo code
function applyPromoCode() {
    const promoInput = document.getElementById('promo-input');
    const promoCode = promoInput.value.trim().toUpperCase();
    const discountElement = document.getElementById('discount');
    
    const promoCodes = {
        'SAVE10': 10,
        'WELCOME': 50,
        'DOKO20': 20,
        'NEWUSER': 30
    };
    
    if (promoCodes[promoCode]) {
        const discount = promoCodes[promoCode];
        if (discountElement) {
            discountElement.textContent = `- रू ${discount}`;
            discountElement.style.color = '#27ae60';
        }
        
        // Recalculate total with discount
        const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        const total = subtotal + (cart.length > 0 ? deliveryFee : 0) - discount;
        if (cartTotal) cartTotal.textContent = `रू ${Math.max(0, total).toFixed(0)}`;
        
        showCartNotification(`Promo code applied! रू ${discount} discount`);
        promoInput.value = '';
    } else {
        showCartNotification('Invalid promo code', 'error');
        if (discountElement) {
            discountElement.textContent = '- रू 0';
            discountElement.style.color = '#e74c3c';
        }
    }
}

// Enhanced Proceed to checkout with API integration
async function proceedToCheckout() {
    if (cart.length === 0) {
        showCartNotification('Your cart is empty!', 'error');
        return;
    }
    
    // Check if user is logged in (enhanced functionality)
    if (typeof api !== 'undefined') {
        try {
            const userResponse = await api.getCurrentUser();
            if (userResponse.success) {
                // User is logged in, proceed to payment
                window.location.href = 'payment.html';
            } else {
                // User not logged in, ask to login first
                if (confirm('Please log in to continue with checkout. Would you like to go to the login page?')) {
                    window.location.href = 'login.html';
                }
            }
        } catch (error) {
            // API not available or error, proceed to payment page
            window.location.href = 'payment.html';
        }
    } else {
        // Simple checkout without API
        window.location.href = 'payment.html';
    }
}

// Show cart notification
function showCartNotification(message, type = 'success') {
    // Use enhanced notification if available
    if (typeof cartManager !== 'undefined') {
        cartManager.showNotification(message, type);
        return;
    }
    
    // Fallback notification
    const notification = document.createElement('div');
    notification.className = `cart-notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Enhanced Add to Cart for product pages with better data extraction
document.addEventListener('click', function(e) {
    if (e.target.closest('.add-to-cart-btn')) {
        const btn = e.target.closest('.add-to-cart-btn');
        const productCard = btn.closest('.product-card') || btn.closest('.product-detail');
        
        if (productCard) {
            // Extract product data
            const productName = (productCard.querySelector('.product-name') || productCard.querySelector('h1')).textContent.trim();
            const priceElement = productCard.querySelector('.product-price') || productCard.querySelector('.price');
            const productPrice = priceElement.textContent.replace(/[^0-9.]/g, '');
            
            // Get quantity
            const quantityDisplay = productCard.querySelector('.quantity-display');
            const quantity = quantityDisplay ? parseInt(quantityDisplay.textContent) : 1;
            
            // Get product ID and image
            const productId = btn.dataset.productId || generateProductId(productName);
            const imageElement = productCard.querySelector('.product-image img') || productCard.querySelector('img');
            const imageUrl = imageElement ? imageElement.src : '';
            
            addToCart(productName, productPrice, quantity, productId, imageUrl);
            
            // Visual feedback
            btn.classList.add('added');
            btn.textContent = 'Added!';
            setTimeout(() => {
                btn.classList.remove('added');
                btn.textContent = 'Add to Cart';
            }, 1000);
        }
    }
});

// Update cart icon link to cart page
document.addEventListener('DOMContentLoaded', function() {
    const cartIcon = document.querySelector('.cart-icon-container');
    if (cartIcon && !cartIcon.hasAttribute('data-listener-added')) {
        cartIcon.style.cursor = 'pointer';
        cartIcon.addEventListener('click', function() {
            window.location.href = 'cart.html';
        });
        cartIcon.setAttribute('data-listener-added', 'true');
    }
});

// Quick add to cart functionality for product lists
function quickAddToCart(productName, price, productId = null, imageUrl = '') {
    addToCart(productName, price, 1, productId, imageUrl);
}

// Get cart data for external use
function getCartData() {
    if (typeof cartManager !== 'undefined') {
        return {
            items: cartManager.cart,
            total: cartManager.getCartTotal(),
            count: cartManager.getCartItemCount()
        };
    }
    
    return {
        items: cart,
        total: cart.reduce((total, item) => total + (item.price * item.quantity), 0),
        count: cart.reduce((total, item) => total + item.quantity, 0)
    };
}

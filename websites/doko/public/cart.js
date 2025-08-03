// Shopping Cart JavaScript

// Cart data storage
let cart = JSON.parse(localStorage.getItem('doko-cart')) || [];

// DOM elements
const cartItemsList = document.getElementById('cart-items-list');
const cartSubtotal = document.getElementById('cart-subtotal');
const cartTotal = document.getElementById('cart-total');
const cartCountDisplay = document.querySelector('.cart-count');
const deliveryFee = 50;

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartDisplay();
    updateCartCount();
});

// Add item to cart
function addToCart(product, price, quantity = 1) {
    const existingItem = cart.find(item => item.product === product);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: Date.now(),
            product: product,
            price: parseFloat(price),
            quantity: quantity,
            image: getProductImage(product)
        });
    }
    
    saveCart();
    updateCartDisplay();
    updateCartCount();
    showCartNotification('Item added to cart!');
}

// Remove item from cart
function removeFromCart(itemId) {
    cart = cart.filter(item => item.id !== itemId);
    saveCart();
    updateCartDisplay();
    updateCartCount();
}

// Update item quantity
function updateQuantity(itemId, newQuantity) {
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
                    <img src="${item.image}" alt="${item.product}">
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
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    if (cartCountDisplay) {
        cartCountDisplay.textContent = totalItems;
    }
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
        'DOKO20': 20
    };
    
    if (promoCodes[promoCode]) {
        const discount = promoCodes[promoCode];
        discountElement.textContent = `- रू ${discount}`;
        discountElement.style.color = '#27ae60';
        
        // Recalculate total with discount
        const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        const total = subtotal + (cart.length > 0 ? deliveryFee : 0) - discount;
        cartTotal.textContent = `रू ${Math.max(0, total).toFixed(0)}`;
        
        showCartNotification(`Promo code applied! रू ${discount} discount`);
        promoInput.value = '';
    } else {
        showCartNotification('Invalid promo code', 'error');
        discountElement.textContent = '- रू 0';
        discountElement.style.color = '#e74c3c';
    }
}

// Proceed to checkout
function proceedToCheckout() {
    if (cart.length === 0) {
        showCartNotification('Your cart is empty!', 'error');
        return;
    }
    
    // Redirect to payment page
    window.location.href = 'payment.html';
}

// Show cart notification
function showCartNotification(message, type = 'success') {
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
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Enhanced Add to Cart for product pages
document.addEventListener('click', function(e) {
    if (e.target.closest('.add-to-cart-btn')) {
        const btn = e.target.closest('.add-to-cart-btn');
        const productCard = btn.closest('.product-card');
        
        if (productCard) {
            const productName = productCard.querySelector('.product-name').textContent;
            const productPrice = productCard.querySelector('.product-price').textContent.replace('रू ', '');
            const quantityDisplay = productCard.querySelector('.quantity-display');
            const quantity = quantityDisplay ? parseInt(quantityDisplay.textContent) : 1;
            
            addToCart(productName, productPrice, quantity);
            
            // Visual feedback
            btn.classList.add('added');
            setTimeout(() => btn.classList.remove('added'), 500);
        }
    }
});

// Update cart icon link to cart page
document.addEventListener('DOMContentLoaded', function() {
    const cartIcon = document.querySelector('.cart-icon-container');
    if (cartIcon) {
        cartIcon.style.cursor = 'pointer';
        cartIcon.addEventListener('click', function() {
            window.location.href = 'cart.html';
        });
    }
});

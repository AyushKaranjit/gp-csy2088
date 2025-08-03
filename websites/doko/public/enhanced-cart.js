// Enhanced API Client for DOKO Grocery Store
// Handles all API communications with the backend

class DokoAPI {
    constructor() {
        this.baseURL = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '');
        this.apiURL = this.baseURL + '/api.php';
    }

    // Generic API request method - DISABLED FOR STATIC MODE
    async request(endpoint, options = {}) {
        console.log('API request disabled - static mode:', endpoint);
        // Return mock response to prevent breaking the application
        return { success: false, error: 'Static mode - API disabled' };
    }

    // Authentication methods
    async login(email, password) {
        return this.request('/auth/login', {
            method: 'POST',
            body: { email, password }
        });
    }

    async register(userData) {
        return this.request('/auth/register', {
            method: 'POST',
            body: userData
        });
    }

    async logout() {
        return this.request('/auth/logout', {
            method: 'POST'
        });
    }

    async getCurrentUser() {
        return this.request('/auth/me');
    }

    async updateProfile(userData) {
        return this.request('/auth/profile', {
            method: 'PUT',
            body: userData
        });
    }

    // Product methods
    async getProducts(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/products${queryString ? '?' + queryString : ''}`);
    }

    async getFeaturedProducts(limit = 8) {
        return this.request(`/products?featured=1&limit=${limit}`);
    }

    async getDailyBestProducts(limit = 10) {
        return this.request(`/products?daily-best=1&limit=${limit}`);
    }

    async getProductsByCategory(category, limit = 20) {
        return this.request(`/products?category=${category}&limit=${limit}`);
    }

    async getProduct(id) {
        return this.request(`/products/${id}`);
    }

    async searchProducts(query, limit = 20) {
        return this.request(`/products?search=1&q=${encodeURIComponent(query)}&limit=${limit}`);
    }

    async getCategories() {
        return this.request('/categories');
    }

    // Order methods
    async createOrder(orderData) {
        return this.request('/orders', {
            method: 'POST',
            body: orderData
        });
    }

    async getMyOrders(params = {}) {
        const queryString = new URLSearchParams({ my: 1, ...params }).toString();
        return this.request(`/orders?${queryString}`);
    }

    async getOrder(id) {
        return this.request(`/orders/${id}`);
    }

    async cancelOrder(id) {
        return this.request(`/orders/${id}/cancel`, {
            method: 'PUT'
        });
    }

    // Admin methods (require admin access)
    async getAllOrders(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/orders${queryString ? '?' + queryString : ''}`);
    }

    async updateOrderStatus(id, status) {
        return this.request(`/orders/${id}/status`, {
            method: 'PUT',
            body: { status }
        });
    }

    async createProduct(productData) {
        return this.request('/products', {
            method: 'POST',
            body: productData
        });
    }

    async updateProduct(id, productData) {
        return this.request(`/products/${id}`, {
            method: 'PUT',
            body: productData
        });
    }

    async deleteProduct(id) {
        return this.request(`/products/${id}`, {
            method: 'DELETE'
        });
    }
}

// Initialize API client
const api = new DokoAPI();

// Enhanced Cart Management with API Integration
class CartManager {
    constructor() {
        this.cart = JSON.parse(localStorage.getItem('doko-cart')) || [];
        this.deliveryFee = 50;
        this.user = null;
        
        this.initializeUser();
    }

    initializeUser() {
        // Static mode - no user authentication
        console.log('Static mode - user authentication disabled');
        this.user = null;
    }

    addToCart(productId, productName, price, quantity = 1, imageUrl = '') {
        const existingItem = this.cart.find(item => item.productId === productId);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.cart.push({
                id: Date.now(),
                productId: productId,
                productName: productName,
                price: parseFloat(price),
                quantity: quantity,
                imageUrl: imageUrl || this.getDefaultProductImage(productName)
            });
        }
        
        this.saveCart();
        this.updateCartDisplay();
        this.updateCartCount();
        this.showNotification('Item added to cart!', 'success');
    }

    removeFromCart(itemId) {
        this.cart = this.cart.filter(item => item.id !== itemId);
        this.saveCart();
        this.updateCartDisplay();
        this.updateCartCount();
        this.showNotification('Item removed from cart', 'info');
    }

    updateQuantity(itemId, newQuantity) {
        const item = this.cart.find(item => item.id === itemId);
        if (item) {
            if (newQuantity <= 0) {
                this.removeFromCart(itemId);
            } else {
                item.quantity = newQuantity;
                this.saveCart();
                this.updateCartDisplay();
                this.updateCartCount();
            }
        }
    }

    clearCart() {
        this.cart = [];
        this.saveCart();
        this.updateCartDisplay();
        this.updateCartCount();
        this.showNotification('Cart cleared', 'info');
    }

    saveCart() {
        localStorage.setItem('doko-cart', JSON.stringify(this.cart));
    }

    getCartTotal() {
        return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    getCartItemCount() {
        return this.cart.reduce((count, item) => count + item.quantity, 0);
    }

    updateCartCount() {
        const countElements = document.querySelectorAll('.cart-count');
        const count = this.getCartItemCount();
        
        countElements.forEach(element => {
            element.textContent = count;
            element.style.display = count > 0 ? 'block' : 'none';
        });
    }

    updateCartDisplay() {
        const cartItemsList = document.getElementById('cart-items-list');
        const cartSubtotal = document.getElementById('cart-subtotal');
        const cartTotal = document.getElementById('cart-total');
        
        if (!cartItemsList) return;

        if (this.cart.length === 0) {
            cartItemsList.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart empty-cart-icon"></i>
                    <h3>Your cart is empty</h3>
                    <p>Add some products to get started!</p>
                    <a href="category.html" class="continue-shopping-btn">Continue Shopping</a>
                </div>
            `;
        } else {
            cartItemsList.innerHTML = this.cart.map(item => `
                <div class="cart-item" data-id="${item.id}">
                    <div class="cart-item-image">
                        <img src="${item.imageUrl}" alt="${item.productName}" onerror="this.src='https://via.placeholder.com/80x80?text=Product'">
                    </div>
                    <div class="cart-item-details">
                        <h3 class="cart-item-name">${item.productName}</h3>
                        <p class="cart-item-price">रू ${item.price.toFixed(2)}</p>
                    </div>
                    <div class="cart-item-controls">
                        <div class="quantity-controls">
                            <button class="quantity-btn minus" onclick="cartManager.updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                            <span class="quantity-display">${item.quantity}</span>
                            <button class="quantity-btn plus" onclick="cartManager.updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                        </div>
                        <button class="remove-item-btn" onclick="cartManager.removeFromCart(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="cart-item-total">
                        रू ${(item.price * item.quantity).toFixed(2)}
                    </div>
                </div>
            `).join('');
        }

        // Update totals
        const subtotal = this.getCartTotal();
        const total = subtotal + this.deliveryFee;

        if (cartSubtotal) cartSubtotal.textContent = `रू ${subtotal.toFixed(2)}`;
        if (cartTotal) cartTotal.textContent = `रू ${total.toFixed(2)}`;
    }

    async checkout(customerData) {
        if (this.cart.length === 0) {
            throw new Error('Cart is empty');
        }

        const orderData = {
            items: this.cart.map(item => ({
                product_id: item.productId,
                quantity: item.quantity
            })),
            customer_name: customerData.name,
            customer_email: customerData.email,
            customer_phone: customerData.phone,
            delivery_address: customerData.address,
            payment_method: customerData.paymentMethod || 'cash_on_delivery',
            delivery_fee: this.deliveryFee
        };

        try {
            const response = await api.createOrder(orderData);
            
            if (response.success) {
                this.clearCart();
                this.showNotification('Order placed successfully!', 'success');
                return response.data;
            } else {
                throw new Error(response.error);
            }
        } catch (error) {
            this.showNotification('Failed to place order: ' + error.message, 'error');
            throw error;
        }
    }

    getDefaultProductImage(productName) {
        // Generate a placeholder image based on product name
        const hash = productName.toLowerCase().split('').reduce((a, b) => {
            a = ((a << 5) - a) + b.charCodeAt(0);
            return a & a;
        }, 0);
        
        const colors = ['ff6b6b', '4ecdc4', '45b7d1', '96ceb4', 'ffd93d', 'ff8a80'];
        const color = colors[Math.abs(hash) % colors.length];
        
        return `https://via.placeholder.com/200x200/${color}/ffffff?text=${encodeURIComponent(productName.charAt(0))}`;
    }

    showNotification(message, type = 'info') {
        // Create or update notification element
        let notification = document.getElementById('cart-notification');
        
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'cart-notification';
            notification.className = 'cart-notification';
            document.body.appendChild(notification);
        }

        notification.className = `cart-notification ${type} show`;
        notification.textContent = message;

        // Auto-hide after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }
}

// Initialize cart manager
const cartManager = new CartManager();

// Backward compatibility functions
function addToCart(productName, price, quantity = 1, productId = null, imageUrl = '') {
    // If productId is not provided, generate one from the name
    if (!productId) {
        productId = productName.toLowerCase().replace(/[^a-z0-9]/g, '');
    }
    
    cartManager.addToCart(productId, productName, price, quantity, imageUrl);
}

function updateCartCount() {
    cartManager.updateCartCount();
}

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', function() {
    cartManager.updateCartDisplay();
    cartManager.updateCartCount();
});

// Export for use in other scripts
window.api = api;
window.cartManager = cartManager;

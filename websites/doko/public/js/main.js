/**
 * DOKO Grocery E-commerce - Main JavaScript
 * Professional, feature-rich JavaScript functionality
 * CSY2088 Project
 */

// Global Configuration
const DOKO = {
    baseURL: 'http://localhost/doko/',
    apiURL: 'http://localhost/doko/api/',
    cart: JSON.parse(localStorage.getItem('doko_cart')) || [],
    wishlist: JSON.parse(localStorage.getItem('doko_wishlist')) || [],
    user: JSON.parse(localStorage.getItem('doko_user')) || null,
    
    // Configuration
    config: {
        itemsPerPage: 12,
        maxCartItems: 99,
        deliveryCharge: 50,
        freeDeliveryMinimum: 1000,
        taxRate: 0.13
    }
};

// Utility Functions
const Utils = {
    // Format currency to Nepali Rupees
    formatCurrency: (amount) => {
        return `Rs. ${parseFloat(amount).toLocaleString('en-NP', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}`;
    },

    // Format date
    formatDate: (date) => {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    },

    // Show notification
    showNotification: (message, type = 'info', duration = 3000) => {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} notification`;
        notification.innerHTML = `
            <span>${message}</span>
            <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
        `;
        
        // Add styles for notification
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after duration
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, duration);
    },

    // Debounce function for search
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Validate email
    isValidEmail: (email) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Validate phone number (Nepal)
    isValidPhone: (phone) => {
        const phoneRegex = /^(98|97)\d{8}$/;
        return phoneRegex.test(phone.replace(/[\s-]/g, ''));
    },

    // Generate unique ID
    generateId: () => {
        return '_' + Math.random().toString(36).substr(2, 9);
    }
};

// API Service
const API = {
    // Generic API call function
    call: async (endpoint, method = 'GET', data = null, headers = {}) => {
        try {
            const config = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    ...headers
                }
            };

            if (data && (method === 'POST' || method === 'PUT')) {
                config.body = JSON.stringify(data);
            }

            const response = await fetch(DOKO.apiURL + endpoint, config);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'API request failed');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            Utils.showNotification(error.message, 'danger');
            throw error;
        }
    },

    // Authentication APIs
    auth: {
        login: (email, password) => API.call('auth/login.php', 'POST', { email, password }),
        register: (userData) => API.call('auth/register.php', 'POST', userData),
        logout: () => API.call('auth/logout.php', 'POST'),
        getProfile: () => API.call('auth/profile.php')
    },

    // Product APIs
    products: {
        getAll: (params = {}) => {
            const queryString = new URLSearchParams(params).toString();
            return API.call(`products/list.php?${queryString}`);
        },
        getById: (id) => API.call(`products/detail.php?id=${id}`),
        getFeatured: () => API.call('products/featured.php'),
        search: (query) => API.call(`products/search.php?q=${encodeURIComponent(query)}`)
    },

    // Category APIs
    categories: {
        getAll: () => API.call('categories/list.php'),
        getById: (id) => API.call(`categories/detail.php?id=${id}`)
    },

    // Cart APIs
    cart: {
        get: () => API.call('cart/get.php'),
        add: (productId, quantity) => API.call('cart/add.php', 'POST', { product_id: productId, quantity }),
        update: (productId, quantity) => API.call('cart/update.php', 'PUT', { product_id: productId, quantity }),
        remove: (productId) => API.call('cart/remove.php', 'DELETE', { product_id: productId }),
        clear: () => API.call('cart/clear.php', 'DELETE')
    },

    // Order APIs
    orders: {
        create: (orderData) => API.call('orders/create.php', 'POST', orderData),
        getAll: () => API.call('orders/list.php'),
        getById: (id) => API.call(`orders/detail.php?id=${id}`)
    }
};

// Cart Management
const CartManager = {
    // Initialize cart manager
    init: () => {
        // Load cart from localStorage
        DOKO.cart = JSON.parse(localStorage.getItem('doko_cart')) || [];
        // Update UI
        CartManager.updateCartUI();
    },

    // Add item to cart
    addItem: (product, quantity = 1) => {
        const existingItem = DOKO.cart.find(item => item.product_id === product.product_id);
        
        if (existingItem) {
            existingItem.quantity += quantity;
            if (existingItem.quantity > DOKO.config.maxCartItems) {
                existingItem.quantity = DOKO.config.maxCartItems;
                Utils.showNotification(`Maximum ${DOKO.config.maxCartItems} items allowed per product`, 'warning');
            }
        } else {
            DOKO.cart.push({
                product_id: product.product_id,
                name: product.name,
                price: product.price,
                image_url: product.image_url,
                quantity: quantity,
                unit: product.unit
            });
        }
        
        CartManager.saveCart();
        CartManager.updateCartUI();
        Utils.showNotification(`${product.name} added to cart!`, 'success');
    },

    // Update item quantity
    updateQuantity: (productId, quantity) => {
        const item = DOKO.cart.find(item => item.product_id === productId);
        if (item) {
            if (quantity <= 0) {
                CartManager.removeItem(productId);
            } else {
                item.quantity = Math.min(quantity, DOKO.config.maxCartItems);
                CartManager.saveCart();
                CartManager.updateCartUI();
            }
        }
    },

    // Remove item from cart
    removeItem: (productId) => {
        const index = DOKO.cart.findIndex(item => item.product_id === productId);
        if (index > -1) {
            const item = DOKO.cart[index];
            DOKO.cart.splice(index, 1);
            CartManager.saveCart();
            CartManager.updateCartUI();
            Utils.showNotification(`${item.name} removed from cart`, 'info');
        }
    },

    // Clear cart
    clearCart: () => {
        DOKO.cart = [];
        CartManager.saveCart();
        CartManager.updateCartUI();
        Utils.showNotification('Cart cleared', 'info');
    },

    // Get cart total
    getTotal: () => {
        return DOKO.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    },

    // Get cart count
    getCount: () => {
        return DOKO.cart.reduce((count, item) => count + item.quantity, 0);
    },

    // Save cart to localStorage
    saveCart: () => {
        localStorage.setItem('doko_cart', JSON.stringify(DOKO.cart));
    },

    // Update cart UI elements
    updateCartUI: () => {
        // Update cart count badge
        const cartCountElements = document.querySelectorAll('.cart-count');
        const count = CartManager.getCount();
        cartCountElements.forEach(element => {
            element.textContent = count;
            element.style.display = count > 0 ? 'flex' : 'none';
        });

        // Update cart total
        const cartTotalElements = document.querySelectorAll('.cart-total');
        cartTotalElements.forEach(element => {
            element.textContent = Utils.formatCurrency(CartManager.getTotal());
        });

        // Update cart sidebar if exists
        CartManager.updateCartSidebar();
    },

    // Update cart sidebar
    updateCartSidebar: () => {
        const cartSidebar = document.getElementById('cart-sidebar');
        if (!cartSidebar) return;

        const cartItems = cartSidebar.querySelector('.cart-items');
        if (!cartItems) return;

        if (DOKO.cart.length === 0) {
            cartItems.innerHTML = '<div class="empty-cart">Your cart is empty</div>';
            return;
        }

        cartItems.innerHTML = DOKO.cart.map(item => `
            <div class="cart-item" data-product-id="${item.product_id}">
                <img src="${item.image_url}" alt="${item.name}" class="cart-item-image">
                <div class="cart-item-details">
                    <h4 class="cart-item-name">${item.name}</h4>
                    <div class="cart-item-price">${Utils.formatCurrency(item.price)} / ${item.unit}</div>
                    <div class="cart-item-controls">
                        <button class="btn btn-sm quantity-btn" onclick="CartManager.updateQuantity(${item.product_id}, ${item.quantity - 1})">-</button>
                        <span class="quantity">${item.quantity}</span>
                        <button class="btn btn-sm quantity-btn" onclick="CartManager.updateQuantity(${item.product_id}, ${item.quantity + 1})">+</button>
                        <button class="btn btn-sm btn-danger remove-btn" onclick="CartManager.removeItem(${item.product_id})">Remove</button>
                    </div>
                </div>
                <div class="cart-item-total">${Utils.formatCurrency(item.price * item.quantity)}</div>
            </div>
        `).join('');
    }
};

// Wishlist Management
const WishlistManager = {
    // Add item to wishlist
    addItem: (product) => {
        const exists = DOKO.wishlist.find(item => item.product_id === product.product_id);
        if (!exists) {
            DOKO.wishlist.push({
                product_id: product.product_id,
                name: product.name,
                price: product.price,
                image_url: product.image_url
            });
            this.saveWishlist();
            Utils.showNotification(`${product.name} added to wishlist!`, 'success');
        } else {
            Utils.showNotification('Item already in wishlist', 'info');
        }
        this.updateWishlistUI();
    },

    // Remove item from wishlist
    removeItem: (productId) => {
        const index = DOKO.wishlist.findIndex(item => item.product_id === productId);
        if (index > -1) {
            const item = DOKO.wishlist[index];
            DOKO.wishlist.splice(index, 1);
            this.saveWishlist();
            Utils.showNotification(`${item.name} removed from wishlist`, 'info');
        }
        this.updateWishlistUI();
    },

    // Check if item is in wishlist
    isInWishlist: (productId) => {
        return DOKO.wishlist.some(item => item.product_id === productId);
    },

    // Save wishlist to localStorage
    saveWishlist: () => {
        localStorage.setItem('doko_wishlist', JSON.stringify(DOKO.wishlist));
    },

    // Update wishlist UI
    updateWishlistUI: () => {
        const wishlistButtons = document.querySelectorAll('.wishlist-btn');
        wishlistButtons.forEach(button => {
            const productId = parseInt(button.getAttribute('data-product-id'));
            if (this.isInWishlist(productId)) {
                button.classList.add('active');
                button.innerHTML = '❤️';
            } else {
                button.classList.remove('active');
                button.innerHTML = '🤍';
            }
        });
    }
};

// Search Functionality
const SearchManager = {
    init: () => {
        const searchBox = document.getElementById('search-box');
        const searchResults = document.getElementById('search-results');
        
        if (searchBox) {
            const debouncedSearch = Utils.debounce(this.performSearch, 300);
            searchBox.addEventListener('input', (e) => {
                const query = e.target.value.trim();
                if (query.length >= 2) {
                    debouncedSearch(query);
                } else {
                    this.hideResults();
                }
            });

            // Hide results when clicking outside
            document.addEventListener('click', (e) => {
                if (!searchBox.contains(e.target) && !searchResults?.contains(e.target)) {
                    this.hideResults();
                }
            });
        }
    },

    performSearch: async (query) => {
        try {
            const results = await API.products.search(query);
            this.displayResults(results.data || []);
        } catch (error) {
            console.error('Search error:', error);
        }
    },

    displayResults: (products) => {
        let searchResults = document.getElementById('search-results');
        if (!searchResults) {
            searchResults = document.createElement('div');
            searchResults.id = 'search-results';
            searchResults.className = 'search-results';
            document.querySelector('.search-container').appendChild(searchResults);
        }

        if (products.length === 0) {
            searchResults.innerHTML = '<div class="no-results">No products found</div>';
        } else {
            searchResults.innerHTML = products.slice(0, 5).map(product => `
                <div class="search-result-item" onclick="window.location.href='product-details.html?id=${product.product_id}'">
                    <img src="${product.image_url}" alt="${product.name}">
                    <div class="result-info">
                        <h4>${product.name}</h4>
                        <div class="result-price">${Utils.formatCurrency(product.price)}</div>
                    </div>
                </div>
            `).join('');
        }

        searchResults.style.display = 'block';
    },

    hideResults: () => {
        const searchResults = document.getElementById('search-results');
        if (searchResults) {
            searchResults.style.display = 'none';
        }
    }
};

// Product Management
const ProductManager = {
    // Load products for listing page
    loadProducts: async (params = {}) => {
        try {
            const response = await API.products.getAll(params);
            this.displayProducts(response.data || []);
            this.updatePagination(response.pagination || {});
        } catch (error) {
            console.error('Error loading products:', error);
        }
    },

    // Display products in grid
    displayProducts: (products) => {
        const productGrid = document.getElementById('products-grid');
        if (!productGrid) return;

        if (products.length === 0) {
            productGrid.innerHTML = '<div class="no-products">No products found</div>';
            return;
        }

        productGrid.innerHTML = products.map(product => this.createProductCard(product)).join('');
        
        // Update wishlist UI after products are loaded
        WishlistManager.updateWishlistUI();
    },

    // Create product card HTML
    createProductCard: (product) => {
        const discountPercent = product.original_price ? 
            Math.round(((product.original_price - product.price) / product.original_price) * 100) : 0;

        return `
            <div class="product-card card">
                <div class="product-image">
                    <img src="${product.image_url}" alt="${product.name}" loading="lazy">
                    ${product.featured ? '<div class="product-badge">Featured</div>' : ''}
                    ${discountPercent > 0 ? `<div class="product-badge discount">${discountPercent}% OFF</div>` : ''}
                </div>
                <div class="product-info card-body">
                    <h3 class="product-name">${product.name}</h3>
                    <p class="product-description">${product.description || ''}</p>
                    
                    <div class="product-price">
                        <span class="current-price">${Utils.formatCurrency(product.price)}</span>
                        ${product.original_price ? `<span class="original-price">${Utils.formatCurrency(product.original_price)}</span>` : ''}
                        <span class="unit">/${product.unit}</span>
                    </div>
                    
                    ${product.avg_rating ? `
                        <div class="product-rating">
                            <span class="stars">${'★'.repeat(Math.floor(product.avg_rating))}${'☆'.repeat(5 - Math.floor(product.avg_rating))}</span>
                            <span class="rating-count">(${product.review_count || 0})</span>
                        </div>
                    ` : ''}
                    
                    <div class="product-actions">
                        <button class="btn btn-primary add-to-cart" onclick="ProductManager.addToCart(${product.product_id})">
                            Add to Cart
                        </button>
                        <button class="wishlist-btn" data-product-id="${product.product_id}" onclick="ProductManager.toggleWishlist(${product.product_id})">
                            🤍
                        </button>
                    </div>
                    
                    <div class="product-details-link">
                        <a href="product-details.html?id=${product.product_id}" class="btn btn-outline btn-sm">View Details</a>
                    </div>
                </div>
            </div>
        `;
    },

    // Add product to cart
    addToCart: async (productId) => {
        try {
            // Get product details first
            const response = await API.products.getById(productId);
            const product = response.data;
            
            if (product.stock <= 0) {
                Utils.showNotification('Product is out of stock', 'warning');
                return;
            }
            
            CartManager.addItem(product, 1);
        } catch (error) {
            console.error('Error adding to cart:', error);
        }
    },

    // Toggle wishlist
    toggleWishlist: async (productId) => {
        try {
            if (WishlistManager.isInWishlist(productId)) {
                WishlistManager.removeItem(productId);
            } else {
                const response = await API.products.getById(productId);
                const product = response.data;
                WishlistManager.addItem(product);
            }
        } catch (error) {
            console.error('Error toggling wishlist:', error);
        }
    },

    // Update pagination
    updatePagination: (pagination) => {
        const paginationContainer = document.getElementById('pagination');
        if (!paginationContainer || !pagination.totalPages) return;

        const currentPage = pagination.currentPage || 1;
        const totalPages = pagination.totalPages;

        let paginationHTML = '';

        // Previous button
        if (currentPage > 1) {
            paginationHTML += `<button class="pagination-btn" data-page="${currentPage - 1}">Previous</button>`;
        }

        // Page numbers
        for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
            paginationHTML += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }

        // Next button
        if (currentPage < totalPages) {
            paginationHTML += `<button class="pagination-btn" data-page="${currentPage + 1}">Next</button>`;
        }

        paginationContainer.innerHTML = paginationHTML;

        // Add click event listeners
        paginationContainer.querySelectorAll('.pagination-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const page = parseInt(e.target.getAttribute('data-page'));
                this.loadProducts({ page });
            });
        });
    }
};

// Authentication Manager
const AuthManager = {
    // Check if user is logged in
    isLoggedIn: () => {
        return DOKO.user !== null;
    },

    // Login user
    login: async (email, password) => {
        try {
            const response = await API.auth.login(email, password);
            DOKO.user = response.user;
            localStorage.setItem('doko_user', JSON.stringify(DOKO.user));
            Utils.showNotification('Login successful!', 'success');
            this.updateAuthUI();
            return true;
        } catch (error) {
            Utils.showNotification('Invalid email or password', 'danger');
            return false;
        }
    },

    // Register user
    register: async (userData) => {
        try {
            const response = await API.auth.register(userData);
            Utils.showNotification('Registration successful! Please login.', 'success');
            return true;
        } catch (error) {
            Utils.showNotification(error.message || 'Registration failed', 'danger');
            return false;
        }
    },

    // Logout user
    logout: () => {
        DOKO.user = null;
        localStorage.removeItem('doko_user');
        Utils.showNotification('Logged out successfully', 'info');
        this.updateAuthUI();
        window.location.href = 'index.html';
    },

    // Update authentication UI
    updateAuthUI: () => {
        const loginLinks = document.querySelectorAll('.login-link');
        const logoutLinks = document.querySelectorAll('.logout-link');
        const userNameElements = document.querySelectorAll('.user-name');

        if (AuthManager.isLoggedIn()) {
            loginLinks.forEach(el => el.style.display = 'none');
            logoutLinks.forEach(el => el.style.display = 'block');
            userNameElements.forEach(el => el.textContent = DOKO.user.name);
        } else {
            loginLinks.forEach(el => el.style.display = 'block');
            logoutLinks.forEach(el => el.style.display = 'none');
        }
    }
};

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize managers
    SearchManager.init();
    CartManager.init(); // Initialize cart manager here
    CartManager.updateCartUI();
    WishlistManager.updateWishlistUI();
    AuthManager.updateAuthUI();

    // Initialize hero background slider if container exists
    if (document.getElementById('hero-slide-track')) {
        HeroBackgroundSlider.init();
    }

    // Initialize scroll-based navbar
    initScrollNavbar();

    // Initialize page-specific functionality
    const currentPage = window.location.pathname.split('/').pop();
    
    switch (currentPage) {
        case 'index.html':
        case '':
            initHomePage();
            break;
        case 'products.html':
            initProductsPage();
            break;
        case 'product-details.html':
            initProductDetailsPage();
            break;
        case 'cart.html':
            initCartPage();
            break;
        case 'login.html':
            initLoginPage();
            break;
        case 'register.html':
            initRegisterPage();
            break;
    }
});

// Scroll-based Navbar Functionality
function initScrollNavbar() {
    const header = document.querySelector('.header');
    let lastScrollTop = 0;
    let scrollTimeout;

    if (!header) return;

    window.addEventListener('scroll', function() {
        // Clear previous timeout
        clearTimeout(scrollTimeout);
        
        // Add a small delay to prevent excessive calls
        scrollTimeout = setTimeout(() => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Remove existing classes
            header.classList.remove('scrolled-up', 'scrolled-down', 'at-top');
            
            if (scrollTop === 0) {
                // At the top of the page
                header.classList.add('at-top');
            } else if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling down - hide navbar
                header.classList.add('scrolled-down');
            } else if (scrollTop < lastScrollTop) {
                // Scrolling up - show navbar
                header.classList.add('scrolled-up');
            }
            
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // For Mobile or negative scrolling
        }, 10);
    }, { passive: true });

    // Initialize with current position
    if (window.pageYOffset === 0) {
        header.classList.add('at-top');
    } else {
        header.classList.add('scrolled-up');
    }
}

// Page-specific initialization functions
function initHomePage() {
    // Load featured products
    ProductManager.loadProducts({ featured: 1, limit: 8 });
    
    // Load categories
    loadCategories();
}

function initProductsPage() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const category = urlParams.get('category');
    const search = urlParams.get('search');
    
    // Load products based on parameters
    const params = {};
    if (category) params.category_id = category;
    if (search) params.search = search;
    
    ProductManager.loadProducts(params);
    
    // Initialize filters
    initFilters();
}

function initProductDetailsPage() {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    
    if (productId) {
        loadProductDetails(productId);
    }
}

function initCartPage() {
    displayCartItems();
    calculateCartTotal();
}

function initLoginPage() {
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
}

function initRegisterPage() {
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
}

// Event handlers
async function handleLogin(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const email = formData.get('email');
    const password = formData.get('password');
    
    const success = await AuthManager.login(email, password);
    if (success) {
        window.location.href = 'index.html';
    }
}

async function handleRegister(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    const userData = {
        name: formData.get('name'),
        email: formData.get('email'),
        password: formData.get('password'),
        phone: formData.get('phone'),
        address: formData.get('address')
    };
    
    // Validate data
    if (!Utils.isValidEmail(userData.email)) {
        Utils.showNotification('Please enter a valid email address', 'danger');
        return;
    }
    
    if (!Utils.isValidPhone(userData.phone)) {
        Utils.showNotification('Please enter a valid phone number', 'danger');
        return;
    }
    
    const success = await AuthManager.register(userData);
    if (success) {
        window.location.href = 'login.html';
    }
}

// Additional utility functions
async function loadCategories() {
    try {
        const response = await API.categories.getAll();
        displayCategories(response.data || []);
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

function displayCategories(categories) {
    const categoriesGrid = document.getElementById('categories-grid');
    if (!categoriesGrid) return;
    
    categoriesGrid.innerHTML = categories.map(category => `
        <div class="category-card">
            <a href="products.html?category=${category.category_id}">
                <div class="category-image">
                    <img src="${category.image_url}" alt="${category.name}">
                </div>
                <h3 class="category-name">${category.name}</h3>
                <p class="category-count">${category.product_count || 0} items</p>
            </a>
        </div>
    `).join('');
}

async function loadProductDetails(productId) {
    try {
        const response = await API.products.getById(productId);
        const product = response.data;
        displayProductDetails(product);
    } catch (error) {
        console.error('Error loading product details:', error);
        Utils.showNotification('Product not found', 'danger');
    }
}

function displayProductDetails(product) {
    // This would be implemented based on the product details page structure
}

function initFilters() {
    // Initialize filter functionality
    const filterForm = document.getElementById('filters-form');
    if (filterForm) {
        filterForm.addEventListener('change', handleFiltersChange);
    }
}

function handleFiltersChange(e) {
    const formData = new FormData(e.target.form);
    const params = {};
    
    for (let [key, value] of formData.entries()) {
        if (value) params[key] = value;
    }
    
    ProductManager.loadProducts(params);
}

function displayCartItems() {
    const cartContainer = document.getElementById('cart-items');
    if (!cartContainer) return;
    
    if (DOKO.cart.length === 0) {
        cartContainer.innerHTML = '<div class="empty-cart">Your cart is empty</div>';
        return;
    }
    
    cartContainer.innerHTML = DOKO.cart.map(item => `
        <div class="cart-item">
            <img src="${item.image_url}" alt="${item.name}">
            <div class="item-details">
                <h3>${item.name}</h3>
                <p>${Utils.formatCurrency(item.price)} / ${item.unit}</p>
            </div>
            <div class="quantity-controls">
                <button onclick="CartManager.updateQuantity(${item.product_id}, ${item.quantity - 1})">-</button>
                <span>${item.quantity}</span>
                <button onclick="CartManager.updateQuantity(${item.product_id}, ${item.quantity + 1})">+</button>
            </div>
            <div class="item-total">${Utils.formatCurrency(item.price * item.quantity)}</div>
            <button onclick="CartManager.removeItem(${item.product_id})" class="remove-btn">Remove</button>
        </div>
    `).join('');
}

function calculateCartTotal() {
    const subtotal = CartManager.getTotal();
    const deliveryCharge = subtotal >= DOKO.config.freeDeliveryMinimum ? 0 : DOKO.config.deliveryCharge;
    const tax = subtotal * DOKO.config.taxRate;
    const total = subtotal + deliveryCharge + tax;
    
    // Update UI elements
    const elements = {
        'cart-subtotal': Utils.formatCurrency(subtotal),
        'delivery-charge': Utils.formatCurrency(deliveryCharge),
        'tax-amount': Utils.formatCurrency(tax),
        'cart-total': Utils.formatCurrency(total)
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    });
}

// Export for global access
window.DOKO = DOKO;
window.CartManager = CartManager;
window.WishlistManager = WishlistManager;
window.ProductManager = ProductManager;
window.AuthManager = AuthManager;

// Hero Background Slider Manager
const HeroBackgroundSlider = {
    // High-quality grocery image categories for hero
    heroGroceryCategories: [
        'fresh-vegetables-market', 'organic-fruits-display', 'colorful-produce-stand',
        'farmers-market-vegetables', 'fresh-fruit-basket', 'organic-leafy-greens',
        'seasonal-vegetables', 'tropical-fruits-display', 'dairy-products-fresh',
        'whole-grains-organic', 'spices-herbs-market', 'farm-fresh-produce',
        'healthy-food-ingredients', 'grocery-shopping-fresh', 'supermarket-produce',
        'organic-food-market', 'clean-eating-ingredients', 'kitchen-fresh-ingredients'
    ],
    
    // Generate high-quality hero background images
    generateHeroImages: function(count = 20) {
        const images = [];
        const categories = this.heroGroceryCategories;
        
        // High-quality, reliable image sources for hero section
        const imageSources = [
            {
                type: 'unsplash-hero',
                images: [
                    'https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1563636619-e9143da7973b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1506976785307-8732e854ad03?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1488459716781-31db52582fe9?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1552944150-6dd1180e5999?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1574484284002-952d92456975?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1594736797933-d0ce9e3089df?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1568702846914-96b305d2aaeb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1586201375761-83865001e31c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95',
                    'https://images.unsplash.com/photo-1540420773420-3366772f4999?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&h=1000&q=95'
                ]
            }
        ];
        
        for (let i = 0; i < count; i++) {
            const imageUrl = imageSources[0].images[i % imageSources[0].images.length];
            
            images.push({
                url: imageUrl,
                category: categories[i % categories.length],
                id: `hero-slide-${i}`,
                fallback: imageSources[0].images[0]
            });
        }
        
        return images;
    },
    
    // Create hero slide element
    createHeroSlide: function(image) {
        const slide = document.createElement('div');
        slide.className = 'hero-slide';
        slide.id = image.id;
        
        const fallbackImage = 'https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&h=600&q=90';
        
        slide.innerHTML = `
            <img src="${image.url}" 
                 alt="${image.category.replace('-', ' ')}" 
                 loading="lazy" 
                 onerror="this.onerror=null; this.src='${fallbackImage}';">
        `;
        
        return slide;
    },
    
    // Initialize hero background slider
    init: function() {
        const heroSlideTrack = document.getElementById('hero-slide-track');
        if (!heroSlideTrack) {
            console.log('Hero slide track not found');
            return;
        }
        
        console.log('Initializing hero background slider...');
        
        // Generate fewer hero images for ultra-smooth animation
        const images = this.generateHeroImages(16);
        console.log('Generated', images.length, 'hero background images');
        
        // Create slides for seamless loop (triple for smooth continuous effect)
        const slides = [...images, ...images, ...images];
        slides.forEach((image, index) => {
            const slide = this.createHeroSlide(image);
            heroSlideTrack.appendChild(slide);
            
            // Log first few images for debugging
            if (index < 5) {
                console.log(`Hero slide ${index}:`, image.url);
            }
        });
        
        // Set track width for smooth animation
        const slideCount = slides.length;
        heroSlideTrack.style.width = `${slideCount * 25}%`;
        
        console.log(`Hero slide track width set to: ${slideCount * 25}%`);
        
        // Start constant speed animation
        const animationDuration = 800; // 800 seconds for very slow, constant speed
        heroSlideTrack.style.animation = `heroSlide ${animationDuration}s infinite linear`;
        
        // Ensure animation consistency by preventing browser optimization interference
        heroSlideTrack.style.animationFillMode = 'none';
        heroSlideTrack.style.animationPlayState = 'running';
        
        // Force browser to maintain constant timing
        setInterval(() => {
            const computedStyle = window.getComputedStyle(heroSlideTrack);
            if (computedStyle.animationPlayState !== 'running') {
                heroSlideTrack.style.animationPlayState = 'running';
            }
        }, 5000);
        
        // Preload additional images
        setTimeout(() => {
            this.preloadHeroImages();
        }, 2000);
        
        console.log('Hero background slider initialized with', slideCount, 'slides at', animationDuration, 'seconds duration');
    },
    
    // Preload additional hero images
    preloadHeroImages: function() {
        const additionalImages = this.generateHeroImages(8);
        additionalImages.forEach(image => {
            const img = new Image();
            img.src = image.url;
        });
        console.log('Preloaded additional hero images');
    }
};

// Background Image Carousel Manager
const BackgroundCarousel = {
    // Grocery-focused image categories
    groceryCategories: [
        'fresh-vegetables', 'organic-vegetables', 'leafy-greens', 'root-vegetables',
        'fresh-fruits', 'seasonal-fruits', 'citrus-fruits', 'tropical-fruits',
        'dairy-products', 'milk', 'cheese', 'yogurt',
        'whole-grains', 'rice', 'wheat', 'quinoa',
        'spices', 'herbs', 'organic-spices', 'indian-spices',
        'fresh-produce', 'farmers-market', 'organic-food', 'healthy-food',
        'farm-fresh', 'natural-food', 'clean-eating', 'grocery-shopping',
        'supermarket', 'food-market', 'fresh-ingredients', 'cooking-ingredients',
        'green-vegetables', 'colorful-vegetables', 'seasonal-produce', 'local-produce',
        'breakfast-foods', 'pantry-staples', 'kitchen-essentials', 'meal-prep'
    ],
    
    // Generate grocery-focused image URLs
    generateGroceryImages: function(count = 40) {
        const images = [];
        const categories = this.groceryCategories;
        
        // Use multiple image sources for better reliability
        const imageSources = [
            {
                type: 'unsplash',
                baseUrl: 'https://source.unsplash.com/200x300/?',
                fallback: 'https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&h=300&q=80'
            },
            {
                type: 'picsum',
                baseUrl: 'https://picsum.photos/200/300?random=',
                fallback: 'https://picsum.photos/200/300?grayscale'
            },
            {
                type: 'unsplash-direct',
                images: [
                    'https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&h=300&q=80', // vegetables
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&h=300&q=80', // fruits
                    'https://images.unsplash.com/photo-1563636619-e9143da7973b?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&h=300&q=80', // produce
                    'https://images.unsplash.com/photo-1506976785307-8732e854ad03?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&h=300&q=80', // market
                ]
            }
        ];
        
        for (let i = 0; i < count; i++) {
            const sourceIndex = i % imageSources.length;
            const source = imageSources[sourceIndex];
            let imageUrl;
            
            switch (source.type) {
                case 'unsplash':
                    const category = categories[i % categories.length];
                    imageUrl = `${source.baseUrl}${category}&sig=${i}`;
                    break;
                case 'picsum':
                    imageUrl = `${source.baseUrl}${i + 1}`;
                    break;
                case 'unsplash-direct':
                    imageUrl = source.images[i % source.images.length];
                    break;
                default:
                    imageUrl = source.fallback;
            }
            
            images.push({
                url: imageUrl,
                category: categories[i % categories.length],
                id: `bg-slide-${i}`,
                fallback: source.fallback || imageSources[0].fallback
            });
        }
        
        return images;
    },
    
    // Create background slide element
    createBackgroundSlide: function(image) {
        const slide = document.createElement('div');
        slide.className = 'background-slide';
        slide.id = image.id;
        
        // Create a more sophisticated fallback system
        const fallbackImages = [
            'https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&h=300&q=80',
            'https://images.unsplash.com/photo-1610832958506-aa56368176cf?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&h=300&q=80',
            'https://images.unsplash.com/photo-1563636619-e9143da7973b?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&h=300&q=80',
            'https://picsum.photos/200/300?grayscale'
        ];
        
        const randomFallback = fallbackImages[Math.floor(Math.random() * fallbackImages.length)];
        
        slide.innerHTML = `
            <img src="${image.url}" 
                 alt="${image.category.replace('-', ' ')}" 
                 loading="lazy" 
                 onerror="this.onerror=null; this.src='${randomFallback}';">
        `;
        
        return slide;
    },
    
    // Initialize background carousel
    init: function() {
        const backgroundTrack = document.getElementById('background-carousel-track');
        if (!backgroundTrack) {
            console.log('Background carousel container not found');
            return;
        }
        
        console.log('Initializing background carousel...');
        
        // Generate grocery images
        const images = this.generateGroceryImages(50);
        console.log('Generated', images.length, 'background images');
        
        // Create slides for seamless loop (duplicate for continuous effect)
        [...images, ...images].forEach((image, index) => {
            const slide = this.createBackgroundSlide(image);
            backgroundTrack.appendChild(slide);
            
            // Log first few images for debugging
            if (index < 5) {
                console.log(`Background slide ${index}:`, image.url);
            }
        });
        
        // Update track width based on slide count
        const slideCount = images.length * 2;
        backgroundTrack.style.width = `calc(200px * ${slideCount})`;
        
        console.log(`Background carousel track width set to: calc(200px * ${slideCount})`);
        
        // Start animation
        backgroundTrack.style.animation = `backgroundSlide 60s infinite linear`;
        
        // Preload images for better performance
        setTimeout(() => {
            this.preloadBackgroundImages();
        }, 1000);
        
        console.log('Background grocery carousel initialized with', slideCount, 'slides');
    },
    
    // Preload background images
    preloadBackgroundImages: function() {
        const additionalImages = this.generateGroceryImages(20);
        additionalImages.forEach(image => {
            const img = new Image();
            img.src = image.url;
        });
        console.log('Preloaded additional background images');
    },
    
    // Add multiple background layers for depth
    initMultipleBackgrounds: function() {
        const backgroundContainer = document.querySelector('.background-carousel');
        if (!backgroundContainer) return;
        
        // Create 3 background layers for depth effect
        for (let layer = 1; layer <= 3; layer++) {
            const layerTrack = document.createElement('div');
            layerTrack.className = 'background-carousel-track';
            layerTrack.id = `background-carousel-track-${layer}`;
            layerTrack.style.animationDuration = `${60 + (layer * 20)}s`;
            layerTrack.style.opacity = `${0.3 / layer}`;
            layerTrack.style.animationDirection = layer % 2 === 0 ? 'reverse' : 'normal';
            
            const images = this.generateGroceryImages(30 + (layer * 10));
            [...images, ...images].forEach(image => {
                const slide = this.createBackgroundSlide(image);
                layerTrack.appendChild(slide);
            });
            
            backgroundContainer.appendChild(layerTrack);
        }
        
        console.log('Multi-layer background carousel initialized');
    }
};

// Export for global access
window.BackgroundCarousel = BackgroundCarousel;
window.HeroBackgroundSlider = HeroBackgroundSlider;

// Enhanced DOKO Grocery JS with API Integration

// Load enhanced cart functionality
document.addEventListener('DOMContentLoaded', function() {
    // Ensure enhanced cart is available
    if (typeof cartManager === 'undefined') {
        const script = document.createElement('script');
        script.src = 'enhanced-cart.js';
        script.onload = initializeApp;
        document.head.appendChild(script);
    } else {
        initializeApp();
    }
});

// Initialize application
function initializeApp() {
    startFlashSaleTimer();
    initializeQuantityControls();
    loadFeaturedProducts();
    loadTrendingProducts();
    updateCartCountFromStorage();
    setupProductFilters();
    setupEventListeners();
}

// Enhanced Product Loading with API
async function loadFeaturedProducts() {
    try {
        if (typeof api !== 'undefined') {
            const response = await api.getFeaturedProducts(8);
            if (response.success) {
                displayProducts(response.data, 'featured-products-grid');
            }
        } else {
            console.log('API not available, using static products');
        }
    } catch (error) {
        console.error('Failed to load featured products:', error);
    }
}

// Load trending products
async function loadTrendingProducts() {
    try {
        const response = await fetch('/api/products?limit=8');
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                displayProducts(data.data, 'trendingProducts');
            }
        }
    } catch (error) {
        console.error('Failed to load trending products:', error);
        // Fallback to static products if API fails
        loadStaticTrendingProducts();
    }
}

// Fallback static trending products
function loadStaticTrendingProducts() {
    const staticProducts = [
        {
            product_id: 1,
            name: "iPhone 15 Pro Max",
            price: 149999,
            rating: 4.8,
            primary_image: "https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=500&h=500&fit=crop"
        },
        {
            product_id: 2,
            name: "Samsung Galaxy S24 Ultra",
            price: 129999,
            rating: 4.7,
            primary_image: "https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=500&h=500&fit=crop"
        }
    ];
    displayProducts(staticProducts, 'trendingProducts');
}

async function loadProductsByCategory(category, containerId = 'category-products-grid') {
    try {
        if (typeof api !== 'undefined') {
            const response = await api.getProductsByCategory(category);
            if (response.success) {
                displayProducts(response.data, containerId);
                
                // Update page title if available
                const titleElement = document.getElementById('category-title');
                if (titleElement) {
                    titleElement.textContent = category.charAt(0).toUpperCase() + category.slice(1);
                }
            }
        }
    } catch (error) {
        console.error('Failed to load products by category:', error);
    }
}

// Display products in grid
function displayProducts(products, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = products.map(product => `
        <div class="product-card" data-category="${product.category || 'other'}" data-product-id="${product.product_id}">
            <div class="product-image">
                <img src="${product.primary_image || getProductImageForCart(product.name)}" alt="${product.name}" 
                     onerror="this.src='https://via.placeholder.com/200x200?text=Product'">
                <div class="product-badges">
                    ${product.rating >= 4.5 ? '<span class="badge-new">Featured</span>' : ''}
                    ${product.discount_percentage ? `<span class="badge-sale">${product.discount_percentage}% OFF</span>` : ''}
                </div>
                <i class="fas fa-heart wishlist-icon" onclick="toggleWishlist('${product.product_id}', this)"></i>
            </div>
            <div class="product-info">
                <h3 class="product-name">${product.name}</h3>
                <div class="product-rating">
                    ${'★'.repeat(Math.floor(product.rating || 4))}${'☆'.repeat(5 - Math.floor(product.rating || 4))}
                    <span class="rating-count">(${product.review_count || 0})</span>
                </div>
                <div class="product-price">
                    ${product.original_price && product.original_price > product.price ? 
                        `<span class="original-price">रू ${product.original_price}</span>` : ''}
                    <span class="current-price">रू ${product.price}</span>
                </div>
                <div class="product-controls">
                    <div class="quantity-controls">
                        <button class="quantity-btn minus-btn" type="button">-</button>
                        <input type="number" class="quantity-display" value="1" min="1" max="20" readonly>
                        <button class="quantity-btn plus-btn" type="button">+</button>
                    </div>
                    <button class="add-to-cart-btn" data-product-id="${product.id}" 
                            onclick="addProductToCart('${product.id}', '${product.name}', '${product.price}', this)">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    // Re-initialize quantity controls for new products
    initializeQuantityControls();
}

// Enhanced Add to Cart with product ID
function addProductToCart(productId, productName, price, buttonElement) {
    const productCard = buttonElement.closest('.product-card');
    const quantityInput = productCard.querySelector('.quantity-display');
    const quantity = parseInt(quantityInput.value) || 1;
    const imageUrl = productCard.querySelector('.product-image img').src;
    
    // Use enhanced cart manager if available
    if (typeof cartManager !== 'undefined') {
        cartManager.addToCart(productId, productName, price, quantity, imageUrl);
    } else if (typeof addToCart === 'function') {
        addToCart(productName, price, quantity, productId, imageUrl);
    } else {
        // Fallback implementation
        addToCartFallback(productName, price, quantity, productId, imageUrl);
    }
    
    // Visual feedback
    buttonElement.classList.add('added');
    const originalText = buttonElement.innerHTML;
    buttonElement.innerHTML = '<i class="fas fa-check"></i> Added!';
    
    setTimeout(() => {
        buttonElement.classList.remove('added');
        buttonElement.innerHTML = originalText;
    }, 1500);
}

// Fallback cart implementation
function addToCartFallback(productName, price, quantity, productId, imageUrl) {
    let cart = JSON.parse(localStorage.getItem('doko-cart')) || [];
    const existingItem = cart.find(item => item.productId === productId);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: Date.now(),
            productId: productId,
            productName: productName,
            price: parseFloat(price),
            quantity: quantity,
            imageUrl: imageUrl || getProductImageForCart(productName)
        });
    }
    
    localStorage.setItem('doko-cart', JSON.stringify(cart));
    updateCartCountFromStorage();
    showCartNotificationLocal(`${quantity} x ${productName} added to cart!`);
}

// Wishlist functionality
function toggleWishlist(productId, iconElement) {
    iconElement.classList.toggle('active');
    
    // Get current wishlist from localStorage
    let wishlist = JSON.parse(localStorage.getItem('doko-wishlist')) || [];
    
    if (iconElement.classList.contains('active')) {
        // Add to wishlist
        if (!wishlist.includes(productId)) {
            wishlist.push(productId);
            iconElement.style.color = '#e74c3c';
            showCartNotificationLocal('Added to wishlist!');
        }
    } else {
        // Remove from wishlist
        wishlist = wishlist.filter(id => id !== productId);
        iconElement.style.color = '#bdc3c7';
        showCartNotificationLocal('Removed from wishlist');
    }
    
    localStorage.setItem('doko-wishlist', JSON.stringify(wishlist));
}

// Product Filter Tabs with API Integration
function setupProductFilters() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    const productCards = document.querySelectorAll('.product-card');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', async () => {
            // Update active tab
            document.querySelector('.filter-tab.active')?.classList.remove('active');
            tab.classList.add('active');
            
            const filter = tab.getAttribute('data-filter');
            
            if (filter === 'all') {
                // Show all products
                productCards.forEach(card => {
                    card.style.display = 'block';
                });
            } else {
                // Filter by category
                if (typeof api !== 'undefined') {
                    // Load products from API
                    try {
                        const response = await api.getProductsByCategory(filter);
                        if (response.success) {
                            displayProducts(response.data, 'products-grid');
                        }
                    } catch (error) {
                        console.error('Failed to filter products:', error);
                        // Fallback to client-side filtering
                        filterProductsLocally(filter);
                    }
                } else {
                    // Client-side filtering
                    filterProductsLocally(filter);
                }
            }
        });
    });
}

function filterProductsLocally(filter) {
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        if (card.getAttribute('data-category') === filter) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Quantity Controls
const quantityControls = document.querySelectorAll('.quantity-controls');
quantityControls.forEach(control => {
    const minusBtn = control.querySelector('.minus-btn');
    const plusBtn = control.querySelector('.plus-btn');
    const display = control.querySelector('.quantity-display');
    
    let quantity = 1;
    
    plusBtn.addEventListener('click', () => {
        quantity++;
        display.textContent = quantity;
        minusBtn.disabled = false;
    });
    
    minusBtn.addEventListener('click', () => {
        if (quantity > 1) {
            quantity--;
            display.textContent = quantity;
        }
        if (quantity === 1) {
            minusBtn.disabled = true;
        }
    });
    
    // Initialize minus button state
    minusBtn.disabled = true;
});

// Add to Cart Button
const cartCount = document.querySelector('.cart-count');
const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');

// Enhanced Add to Cart functionality
addToCartBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent card click
        
        const productCard = btn.closest('.product-card');
        if (!productCard) return;
        
        const productName = productCard.querySelector('.product-name')?.textContent || 'Unknown Product';
        const productPriceText = productCard.querySelector('.product-price')?.textContent || 'रू 0';
        const productPrice = productPriceText.replace('रू ', '').trim();
        const quantityDisplay = productCard.querySelector('.quantity-display');
        const quantity = quantityDisplay ? parseInt(quantityDisplay.textContent) : 1;
        
        // Add to cart using cart.js function if available
        if (typeof addToCart === 'function') {
            addToCart(productName, productPrice, quantity);
        } else {
            // Fallback: update local cart count
            let currentCount = parseInt(cartCount.textContent) || 0;
            currentCount += quantity;
            cartCount.textContent = currentCount;
            
            // Show visual feedback
            btn.classList.add('added');
            setTimeout(() => btn.classList.remove('added'), 500);
            
            // Store in localStorage for persistence
            let cart = JSON.parse(localStorage.getItem('doko-cart')) || [];
            const existingItem = cart.find(item => item.product === productName);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    id: Date.now(),
                    product: productName,
                    price: parseFloat(productPrice),
                    quantity: quantity,
                    image: getProductImageForCart(productName)
                });
            }
            
            localStorage.setItem('doko-cart', JSON.stringify(cart));
            showCartNotificationLocal('Item added to cart!');
        }
    });
});

// Enhanced Quantity Controls
function initializeQuantityControls() {
    const quantityControls = document.querySelectorAll('.quantity-controls');
    
    quantityControls.forEach(control => {
        const minusBtn = control.querySelector('.minus-btn');
        const plusBtn = control.querySelector('.plus-btn');
        const quantityInput = control.querySelector('.quantity-display');
        
        if (!minusBtn || !plusBtn || !quantityInput) return;
        
        // Remove existing listeners to prevent duplicates
        const newMinusBtn = minusBtn.cloneNode(true);
        const newPlusBtn = plusBtn.cloneNode(true);
        minusBtn.parentNode.replaceChild(newMinusBtn, minusBtn);
        plusBtn.parentNode.replaceChild(newPlusBtn, plusBtn);
        
        // Plus button click
        newPlusBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            let currentValue = parseInt(quantityInput.value) || 1;
            const maxValue = parseInt(quantityInput.max) || 20;
            if (currentValue < maxValue) {
                quantityInput.value = currentValue + 1;
                newMinusBtn.disabled = false;
            }
            if (currentValue + 1 >= maxValue) {
                newPlusBtn.disabled = true;
            }
        });
        
        // Minus button click
        newMinusBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            let currentValue = parseInt(quantityInput.value) || 1;
            const minValue = parseInt(quantityInput.min) || 1;
            if (currentValue > minValue) {
                quantityInput.value = currentValue - 1;
                newPlusBtn.disabled = false;
            }
            if (currentValue - 1 <= minValue) {
                newMinusBtn.disabled = true;
            }
        });
        
        // Input change validation
        quantityInput.addEventListener('change', (e) => {
            const value = parseInt(e.target.value);
            const minValue = parseInt(e.target.min) || 1;
            const maxValue = parseInt(e.target.max) || 20;
            
            if (isNaN(value) || value < minValue) {
                e.target.value = minValue;
            } else if (value > maxValue) {
                e.target.value = maxValue;
            }
            
            // Update button states
            newMinusBtn.disabled = (parseInt(e.target.value) <= minValue);
            newPlusBtn.disabled = (parseInt(e.target.value) >= maxValue);
        });
        
        // Initialize button states
        const initialValue = parseInt(quantityInput.value) || 1;
        const minValue = parseInt(quantityInput.min) || 1;
        const maxValue = parseInt(quantityInput.max) || 20;
        
        newMinusBtn.disabled = (initialValue <= minValue);
        newPlusBtn.disabled = (initialValue >= maxValue);
    });
}

// Helper function to get product image
function getProductImageForCart(productName) {
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

// Show cart notification (fallback)
function showCartNotificationLocal(message, type = 'success') {
    // Use enhanced notification if available
    if (typeof cartManager !== 'undefined') {
        cartManager.showNotification(message, type);
        return;
    }
    
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : '#e74c3c'};
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 10000;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Animate out and remove
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Update cart count from localStorage
function updateCartCountFromStorage() {
    const cart = JSON.parse(localStorage.getItem('doko-cart')) || [];
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    const cartCountElements = document.querySelectorAll('.cart-count');
    
    cartCountElements.forEach(element => {
        element.textContent = totalItems;
        element.style.display = totalItems > 0 ? 'block' : 'none';
    });
}

// Flash Sale Countdown Timer
function startFlashSaleTimer() {
    const hoursElement = document.getElementById('hours');
    const minutesElement = document.getElementById('minutes');
    const secondsElement = document.getElementById('seconds');
    
    if (!hoursElement || !minutesElement || !secondsElement) return;
    
    // Set timer for 6 hours from now
    let endTime = new Date().getTime() + (6 * 60 * 60 * 1000);
    
    function updateTimer() {
        const now = new Date().getTime();
        const timeLeft = endTime - now;
        
        if (timeLeft <= 0) {
            // Reset timer when it reaches zero
            endTime = new Date().getTime() + (6 * 60 * 60 * 1000);
            return;
        }
        
        const hours = Math.floor(timeLeft / (1000 * 60 * 60));
        const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
        
        hoursElement.textContent = hours.toString().padStart(2, '0');
        minutesElement.textContent = minutes.toString().padStart(2, '0');
        secondsElement.textContent = seconds.toString().padStart(2, '0');
    }
    
    // Update immediately and then every second
    updateTimer();
    setInterval(updateTimer, 1000);
}

// Category Filter Function for category.html
function filterProducts(category) {
    loadProductsByCategory(category);
}

// Learn More Modal Functions
function openLearnMoreModal() {
    const modal = document.getElementById('learnMoreModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeLearnMoreModal() {
    const modal = document.getElementById('learnMoreModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('learnMoreModal');
    if (event.target === modal) {
        closeLearnMoreModal();
    }
}

// Show More Products Button
const showMoreBtn = document.querySelector('.show-more-btn');
if (showMoreBtn) {
    showMoreBtn.addEventListener('click', () => {
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            card.style.display = 'block';
        });
        
        // Reset filter tabs
        document.querySelector('.filter-tab.active')?.classList.remove('active');
        const allTab = document.querySelector('.filter-tab[data-filter="all"]');
        if (allTab) allTab.classList.add('active');
    });
}

// Category Navigation (Demo functionality)
function initializeCategoryNavigation() {
    const categoryCards = document.querySelectorAll('.category-card');
    const prevCategoryBtn = document.getElementById('prevCategory');
    const nextCategoryBtn = document.getElementById('nextCategory');
    
    let currentCategory = 0;
    
    function showCategory(index) {
        categoryCards.forEach((card, i) => {
            card.style.display = i === index ? 'block' : 'none';
        });
    }
    
    if (categoryCards.length > 0) {
        showCategory(currentCategory);
        
        if (prevCategoryBtn) {
            prevCategoryBtn.addEventListener('click', () => {
                currentCategory = (currentCategory - 1 + categoryCards.length) % categoryCards.length;
                showCategory(currentCategory);
            });
        }
        
        if (nextCategoryBtn) {
            nextCategoryBtn.addEventListener('click', () => {
                currentCategory = (currentCategory + 1) % categoryCards.length;
                showCategory(currentCategory);
            });
        }
    }
}

// User Authentication State Management
async function initializeUserState() {
    try {
        if (typeof api !== 'undefined') {
            const response = await api.getCurrentUser();
            if (response.success) {
                updateUIForLoggedInUser(response.user);
            } else {
                updateUIForGuestUser();
            }
        }
    } catch (error) {
        console.log('User not logged in or API unavailable');
        updateUIForGuestUser();
    }
}

function updateUIForLoggedInUser(user) {
    // Update user name in header if element exists
    const userNameElement = document.querySelector('.user-name');
    if (userNameElement) {
        userNameElement.textContent = user.name;
    }
    
    // Show/hide login/logout buttons
    const loginBtn = document.querySelector('.login-btn');
    const logoutBtn = document.querySelector('.logout-btn');
    const userMenu = document.querySelector('.user-menu');
    
    if (loginBtn) loginBtn.style.display = 'none';
    if (logoutBtn) logoutBtn.style.display = 'block';
    if (userMenu) userMenu.style.display = 'block';
}

function updateUIForGuestUser() {
    const loginBtn = document.querySelector('.login-btn');
    const logoutBtn = document.querySelector('.logout-btn');
    const userMenu = document.querySelector('.user-menu');
    
    if (loginBtn) loginBtn.style.display = 'block';
    if (logoutBtn) logoutBtn.style.display = 'none';
    if (userMenu) userMenu.style.display = 'none';
}

// Export functions for global use
window.addProductToCart = addProductToCart;
window.toggleWishlist = toggleWishlist;
window.filterProducts = filterProducts;
window.openLearnMoreModal = openLearnMoreModal;
window.closeLearnMoreModal = closeLearnMoreModal;
window.performSearch = performSearch;

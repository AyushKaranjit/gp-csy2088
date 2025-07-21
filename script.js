// DOKO Grocery JS
// Product Filter Tabs
const filterTabs = document.querySelectorAll('.filter-tab');
const productCards = document.querySelectorAll('.product-card');

filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelector('.filter-tab.active').classList.remove('active');
        tab.classList.add('active');
        const filter = tab.getAttribute('data-filter');
        productCards.forEach(card => {
            if (filter === 'all' || card.getAttribute('data-category') === filter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

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

// Helper function to get product image
function getProductImageForCart(productName) {
    const imageMap = {
        'Apple': 'images/apple.png',
        'Banana': 'images/banana.png',
        'Kiwi': 'images/kiwi.png',
        'Tomato': 'images/tomato.png',
        'Spices': 'images/spices.png',
        'Milk': 'images/milk.png',
        'Cheese': 'images/cheese.png',
        'Water': 'images/water.png'
    };
    return imageMap[productName] || 'images/fruits.png';
}

// Show cart notification (fallback)
function showCartNotificationLocal(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #10b981;
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 10000;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        document.body.removeChild(notification);
    }, 3000);
}

// Product Click for Detail View
productCards.forEach(card => {
    card.addEventListener('click', (e) => {
        // Don't navigate if clicking on buttons or controls
        if (e.target.closest('.add-to-cart-btn') || e.target.closest('.quantity-controls') || e.target.closest('.wishlist-icon')) {
            return;
        }
        
        // Get product data
        const productName = card.querySelector('.product-name')?.textContent || 'Unknown Product';
        const productPrice = card.querySelector('.product-price')?.textContent || 'रू 0';
        const productImage = card.querySelector('.product-image img')?.src || '';
        const productCategory = card.getAttribute('data-category') || 'other';
        
        // Create URL with product data
        const params = new URLSearchParams({
            name: productName,
            price: productPrice,
            image: productImage,
            category: productCategory
        });
        
        // Navigate to product detail page
        window.location.href = `product-detail.html?${params.toString()}`;
    });
    
    // Add cursor pointer to indicate clickability
    card.style.cursor = 'pointer';
});

// Wishlist Icon Toggle
const wishlistIcons = document.querySelectorAll('.wishlist-icon');
wishlistIcons.forEach(icon => {
    icon.addEventListener('click', () => {
        icon.classList.toggle('active');
        if (icon.classList.contains('active')) {
            icon.style.color = '#c0392b';
        } else {
            icon.style.color = '#e74c3c';
        }
    });
});

// Show More Products Button (Demo: just toggles visibility)
const showMoreBtn = document.querySelector('.show-more-btn');
showMoreBtn && showMoreBtn.addEventListener('click', () => {
    productCards.forEach(card => {
        card.style.display = 'block';
    });
    document.querySelector('.filter-tab.active').classList.remove('active');
    filterTabs[0].classList.add('active');
});

// Newsletter Form Submission
const newsletterForm = document.querySelector('.newsletter-form');
newsletterForm && newsletterForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const emailInput = newsletterForm.querySelector('.newsletter-input');
    if (emailInput.value) {
        alert('Thank you for subscribing!');
        emailInput.value = '';
    }
});

// Category Navigation Arrows (Demo: cycles through categories)
const categoryCards = document.querySelectorAll('.category-card');
let currentCategory = 0;
const prevCategoryBtn = document.getElementById('prevCategory');
const nextCategoryBtn = document.getElementById('nextCategory');
function showCategory(index) {
    categoryCards.forEach((card, i) => {
        card.style.display = i === index ? 'block' : 'none';
    });
}
if (categoryCards.length > 0) {
    showCategory(currentCategory);
    prevCategoryBtn && prevCategoryBtn.addEventListener('click', () => {
        currentCategory = (currentCategory - 1 + categoryCards.length) % categoryCards.length;
        showCategory(currentCategory);
    });
    nextCategoryBtn && nextCategoryBtn.addEventListener('click', () => {
        currentCategory = (currentCategory + 1) % categoryCards.length;
        showCategory(currentCategory);
    });
}

// Learn More Modal Functions
function openLearnMoreModal() {
    document.getElementById('learnMoreModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeLearnMoreModal() {
    document.getElementById('learnMoreModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('learnMoreModal');
    if (event.target === modal) {
        closeLearnMoreModal();
    }
}

// Category Filter Function for category.html
function filterProducts(category) {
    const titleElement = document.getElementById('category-title');
    const productGrid = document.getElementById('category-products-grid');
    
    if (titleElement) {
        titleElement.textContent = category.charAt(0).toUpperCase() + category.slice(1);
    }
    
    // Here you would normally filter and display products based on category
    // For now, we'll just update the title
    console.log('Filtering products by:', category);
}

// Responsive Menu (if needed)
// Add your mobile menu JS here if you implement one

// Make cart icon clickable
document.addEventListener('DOMContentLoaded', function() {
    const cartIcon = document.querySelector('.cart-icon-container');
    if (cartIcon) {
        cartIcon.style.cursor = 'pointer';
        cartIcon.addEventListener('click', function() {
            window.location.href = 'cart.html';
        });
    }
    
    // Update cart count from localStorage
    updateCartCountFromStorage();
});

function updateCartCountFromStorage() {
    const cart = JSON.parse(localStorage.getItem('doko-cart')) || [];
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    const cartCountDisplay = document.querySelector('.cart-count');
    if (cartCountDisplay) {
        cartCountDisplay.textContent = totalItems;
    }
}

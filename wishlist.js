// Wishlist JavaScript

// Wishlist data storage
let wishlist = JSON.parse(localStorage.getItem('doko-wishlist')) || [];

// DOM elements
const wishlistItemsList = document.getElementById('wishlist-grid');
const wishlistCountDisplay = document.querySelector('.wishlist-count');

// Initialize wishlist on page load
document.addEventListener('DOMContentLoaded', function() {
    updateWishlistDisplay();
    updateWishlistCount();
    updateWishlistIcons();
});

// Add item to wishlist
function addToWishlist(product, price, image = null) {
    const existingItem = wishlist.find(item => item.product === product);
    
    if (!existingItem) {
        wishlist.push({
            id: Date.now(),
            product: product,
            price: parseFloat(price),
            image: image || getProductImage(product),
            dateAdded: new Date()
        });
        
        saveWishlist();
        updateWishlistDisplay();
        updateWishlistCount();
        updateWishlistIcons();
        showWishlistNotification('Added to wishlist!');
        return true;
    } else {
        showWishlistNotification('Item already in wishlist!', 'info');
        return false;
    }
}

// Remove item from wishlist
function removeFromWishlist(itemId) {
    wishlist = wishlist.filter(item => item.id !== itemId);
    saveWishlist();
    updateWishlistDisplay();
    updateWishlistCount();
    updateWishlistIcons();
    showWishlistNotification('Removed from wishlist!');
}

// Toggle wishlist item
function toggleWishlist(productName, price, image = null) {
    const existingItem = wishlist.find(item => item.product === productName);
    
    if (existingItem) {
        removeFromWishlist(existingItem.id);
        return false; // Removed
    } else {
        addToWishlist(productName, price, image);
        return true; // Added
    }
}

// Check if item is in wishlist
function isInWishlist(productName) {
    return wishlist.some(item => item.product === productName);
}

// Clear entire wishlist
function clearWishlist() {
    if (confirm('Are you sure you want to clear your wishlist?')) {
        wishlist = [];
        saveWishlist();
        updateWishlistDisplay();
        updateWishlistCount();
        updateWishlistIcons();
        showWishlistNotification('Wishlist cleared!');
    }
}

// Save wishlist to localStorage
function saveWishlist() {
    localStorage.setItem('doko-wishlist', JSON.stringify(wishlist));
}

// Update wishlist display
function updateWishlistDisplay() {
    if (!wishlistItemsList) return;
    
    if (wishlist.length === 0) {
        wishlistItemsList.innerHTML = `
            <div class="empty-wishlist">
                <i class="fas fa-heart empty-wishlist-icon"></i>
                <h3>Your wishlist is empty</h3>
                <p>Add items to your wishlist to see them here</p>
                <a href="category.html" class="continue-shopping-btn">Continue Shopping</a>
            </div>
        `;
    } else {
        wishlistItemsList.innerHTML = wishlist.map(item => `
            <div class="product-card wishlist-item" data-id="${item.id}">
                <div class="product-image">
                    <img src="${item.image}" alt="${item.product}">
                    <button class="remove-wishlist-btn" onclick="removeFromWishlist(${item.id})" title="Remove from wishlist">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="product-details">
                    <h3 class="product-name">${item.product}</h3>
                    <p class="product-price">रू ${item.price}</p>
                    <div class="product-actions">
                        <button class="add-to-cart-btn" onclick="addToCartFromWishlist('${item.product}', '${item.price}')">
                            <i class="fas fa-shopping-cart"></i>
                            Add to Cart
                        </button>
                        <button class="view-product-btn" onclick="viewProduct('${item.product}', '${item.price}', '${item.image}')">
                            <i class="fas fa-eye"></i>
                            View
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }
}

// Update wishlist count in header
function updateWishlistCount() {
    if (wishlistCountDisplay) {
        wishlistCountDisplay.textContent = wishlist.length;
    }
}

// Update wishlist icons on all pages
function updateWishlistIcons() {
    const wishlistIcons = document.querySelectorAll('.wishlist-icon');
    wishlistIcons.forEach(icon => {
        const productCard = icon.closest('.product-card');
        if (productCard) {
            const productName = productCard.querySelector('.product-name')?.textContent;
            if (productName && isInWishlist(productName)) {
                icon.classList.add('active');
                icon.style.color = '#e74c3c';
            } else {
                icon.classList.remove('active');
                icon.style.color = '';
            }
        }
    });
}

// Add to cart from wishlist
function addToCartFromWishlist(productName, price) {
    // Use the cart function if available
    if (typeof addToCart === 'function') {
        addToCart(productName, price, 1);
    } else {
        // Fallback implementation
        let cart = JSON.parse(localStorage.getItem('doko-cart')) || [];
        const existingItem = cart.find(item => item.product === productName);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                id: Date.now(),
                product: productName,
                price: parseFloat(price),
                quantity: 1,
                image: getProductImage(productName)
            });
        }
        
        localStorage.setItem('doko-cart', JSON.stringify(cart));
        showWishlistNotification('Added to cart!');
    }
}

// View product details
function viewProduct(productName, price, image) {
    const params = new URLSearchParams({
        name: productName,
        price: price,
        image: image
    });
    window.location.href = `product-detail.html?${params.toString()}`;
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

// Show wishlist notification
function showWishlistNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `wishlist-notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'info' ? 'fa-info-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'info' ? '#3b82f6' : '#ef4444'};
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 10000;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        transform: translateX(400px);
        transition: transform 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Enhanced wishlist icon click handler
document.addEventListener('click', function(e) {
    if (e.target.closest('.wishlist-icon')) {
        e.stopPropagation();
        const icon = e.target.closest('.wishlist-icon');
        const productCard = icon.closest('.product-card');
        
        if (productCard) {
            const productName = productCard.querySelector('.product-name')?.textContent;
            const productPrice = productCard.querySelector('.product-price')?.textContent?.replace('रू ', '');
            const productImage = productCard.querySelector('.product-image img')?.src;
            
            if (productName && productPrice) {
                const isAdded = toggleWishlist(productName, productPrice, productImage);
                
                // Update icon appearance
                if (isAdded) {
                    icon.classList.add('active');
                    icon.style.color = '#e74c3c';
                } else {
                    icon.classList.remove('active');
                    icon.style.color = '';
                }
            }
        }
    }
});

// Initialize wishlist icons when page loads
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        updateWishlistIcons();
    }, 100);
});

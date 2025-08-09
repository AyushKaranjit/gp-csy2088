<?php
// Start session and include configuration
session_start();
require_once __DIR__ . '/../template/config.php';

// Page-specific variables
$page_title = page_title('My Wishlist');
$page_description = 'Your saved favorite products at DOKO. Keep track of items you want to buy later.';
$current_page = 'wishlist';

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'My Wishlist', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php include __DIR__ . '/../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="wishlist-header">
            <h1>My Wishlist</h1>
            <p>Your saved favorite products</p>
        </div>

        <div class="wishlist-content">
            <div id="wishlist-items-container">
                <!-- Wishlist items will be loaded here by JavaScript -->
                <div class="empty-wishlist" id="empty-wishlist">
                    <div class="empty-state">
                        <i class="fas fa-heart"></i>
                        <h3>Your wishlist is empty</h3>
                        <p>Save your favorite products to buy them later</p>
                        <a href="products.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.wishlist-header {
    text-align: center;
    margin-bottom: 2rem;
}

.wishlist-header h1 {
    margin-bottom: 0.5rem;
    color: var(--primary-color);
}

.wishlist-header p {
    color: var(--light-text);
}

.empty-wishlist {
    text-align: center;
    padding: 4rem 0;
}

.empty-state i {
    font-size: 4rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.empty-state h3 {
    margin-bottom: 1rem;
    color: var(--dark-text);
}

.empty-state p {
    color: var(--light-text);
    margin-bottom: 2rem;
}

.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.wishlist-item {
    background: var(--white);
    border-radius: var(--border-radius);
    padding: 1rem;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.wishlist-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.wishlist-item-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
}

.wishlist-item-content h3 {
    margin-bottom: 0.5rem;
    color: var(--dark-text);
}

.wishlist-item-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.wishlist-item-actions {
    display: flex;
    gap: 0.5rem;
}

.wishlist-item-actions .btn {
    flex: 1;
}

.remove-wishlist {
    background: #dc3545;
    color: white;
    border: none;
    padding: 0.5rem;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
}

.remove-wishlist:hover {
    background: #c82333;
}

@media (max-width: 768px) {
    .wishlist-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }
}

.loading {
    text-align: center;
    padding: 2rem;
    font-size: 1.1rem;
    color: var(--text-muted);
}

.error-message {
    text-align: center;
    padding: 2rem;
    color: #dc3545;
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: var(--border-radius);
    margin: 1rem 0;
}
</style>

<script>
// DOKO configuration
const DOKO = {
    baseURL: window.location.origin
};

document.addEventListener('DOMContentLoaded', function() {
    loadWishlistItems();
});

function loadWishlistItems() {
    const container = document.getElementById('wishlist-items-container');
    const emptyState = document.getElementById('empty-wishlist');
    
    // Get wishlist from localStorage
    const wishlist = JSON.parse(localStorage.getItem('doko_wishlist') || '[]');
    
    if (wishlist.length === 0) {
        emptyState.style.display = 'block';
        container.innerHTML = '<div class="empty-wishlist" id="empty-wishlist"><div class="empty-state"><i class="fas fa-heart"></i><h3>Your wishlist is empty</h3><p>Save your favorite products to buy them later</p><a href="products.php" class="btn btn-primary">Start Shopping</a></div></div>';
        return;
    }
    
    emptyState.style.display = 'none';
    
    // Show loading state
    container.innerHTML = '<div class="loading">Loading wishlist items...</div>';
    
    // For each item in wishlist, fetch product details from API
    Promise.all(wishlist.map(item => 
        fetch(`api/products/product-detail.php?id=${item.product_id}`)
            .then(async response => {
                let json;
                try { json = await response.json(); } catch(e) { return { _bad: true }; }
                if (!response.ok) {
                    // If product truly gone (404) mark for cleanup
                    if (response.status === 404) return { _missing: true, product_id: item.product_id };
                    return { _bad: true };
                }
                if (json && (json.product || json.data)) {
                    const p = json.product || json.data;
                    return p;
                }
                return { _bad: true };
            })
            .catch(error => {
                console.warn('Wishlist fetch error for product', item.product_id, error);
                return { _bad: true };
            })
    )).then(results => {
        // Remove missing products from localStorage automatically
        const missingIds = results.filter(r => r && r._missing).map(r => r.product_id);
        if (missingIds.length) {
            let wl = JSON.parse(localStorage.getItem('doko_wishlist') || '[]');
            wl = wl.filter(entry => !missingIds.includes(parseInt(entry.product_id)));
            localStorage.setItem('doko_wishlist', JSON.stringify(wl));
            console.info('Removed missing wishlist product IDs:', missingIds.join(','));
        }
        const validProducts = results.filter(r => r && !r._bad && !r._missing);
        if (validProducts.length === 0) {
            container.innerHTML = '<div class="empty-wishlist"><div class="empty-state"><i class="fas fa-heart"></i><h3>Your wishlist items are no longer available</h3><p>Browse products and add new favorites.</p><a href="products.php" class="btn btn-primary">Shop Products</a></div></div>';
        } else {
            displayWishlistItems(validProducts);
        }
    }).catch(error => {
        console.error('Error loading wishlist items:', error);
        container.innerHTML = '<div class="error-message">Error loading wishlist items</div>';
    });
}

function displayWishlistItems(products) {
    const container = document.getElementById('wishlist-items-container');
    
    if (products.length === 0) {
        container.innerHTML = '<div class="error-message">Some wishlist items could not be loaded</div>';
        return;
    }
    
    const html = `
        <div class="wishlist-grid">
            ${products.map(product => {
                // Normalize image URL from API (may already start with /uploads)
                let img = product.image_url || 'uploads/default-product.jpg';
                if (img.startsWith('/')) {
                    img = img; // already absolute relative to origin
                } else if (!/^https?:/i.test(img)) {
                    // If not absolute, prepend /uploads/ unless already has uploads/
                    if (!img.startsWith('uploads/')) img = 'uploads/' + img;
                    img = '/' + img.replace(/^\/+/, '');
                }
                return `
                <div class="wishlist-item" data-id="${product.product_id}">
                    <img src="${img}" alt="${product.name}" class="wishlist-item-image" onerror="this.onerror=null;this.src='/uploads/default-product.jpg';">
                    <div class="wishlist-item-content">
                        <h3>${product.name}</h3>
                        <div class="wishlist-item-price">Rs. ${parseFloat(product.price).toFixed(2)}</div>
                        <div class="wishlist-item-actions">
                            <button class="btn btn-primary add-to-cart" data-id="${product.product_id}">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                            <button class="remove-wishlist" data-id="${product.product_id}" title="Remove from wishlist">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>`;}).join('')}
        </div>`;
    
    container.innerHTML = html;
    
    // Add event listeners
    container.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            addToCart(productId);
        });
    });
    
    container.querySelectorAll('.remove-wishlist').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            removeFromWishlist(productId);
        });
    });
}

function addToCart(productId) {
    // Delegate to global unified addToCart if present (handles guest cart, locks, notifications)
    if (typeof window.addToCart === 'function') {
        window.addToCart(productId, 1, 'Product');
        return;
    }
    fetch(`${DOKO.baseURL}/api/cart/add.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ product_id: parseInt(productId), quantity: 1 })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart!', 'success');
            if (typeof updateCartCount === 'function') { updateCartCount(); }
        } else {
            showNotification('Error adding to cart: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(err => {
        console.error('Error adding to cart:', err);
        showNotification('Error adding to cart', 'error');
    });
}

function removeFromWishlist(productId) {
    let wishlist = JSON.parse(localStorage.getItem('doko_wishlist') || '[]');
    wishlist = wishlist.filter(item => item.product_id != productId);
    localStorage.setItem('doko_wishlist', JSON.stringify(wishlist));
    
    // Reload wishlist display
    loadWishlistItems();
    
    // Update wishlist count if function exists
    if (typeof WishlistManager !== 'undefined' && WishlistManager.updateWishlistCount) {
        WishlistManager.updateWishlistCount();
    }
    
    showNotification('Item removed from wishlist', 'success');
}

function showNotification(message, type = 'info') {
    // Simple notification - you can enhance this
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        z-index: 9999;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

<?php
// Include footer
include_footer();
?>

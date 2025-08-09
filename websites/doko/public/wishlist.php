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
            .then(response => response.json())
            .then(data => {
                if (data.success && data.product) {
                    return data.product;
                }
                return null;
            })
            .catch(error => {
                console.error('Error fetching product:', item.product_id, error);
                return null;
            })
    )).then(products => {
        const validProducts = products.filter(p => p !== null);
        if (validProducts.length === 0) {
            container.innerHTML = '<div class="error-message">No valid wishlist items found</div>';
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
            ${products.map(product => `
                <div class="wishlist-item" data-id="${product.product_id}">
                    <img src="${DOKO.baseURL}/uploads/products/${product.image_url || 'default.jpg'}" 
                         alt="${product.name}" class="wishlist-item-image">
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
                </div>
            `).join('')}
        </div>
    `;
    
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
    fetch(`${DOKO.baseURL}/api/cart/cart-add.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('Product added to cart!', 'success');
            // Update cart count if function exists
            if (typeof CartManager !== 'undefined' && CartManager.updateCartCount) {
                CartManager.updateCartCount();
            }
        } else {
            showNotification('Error adding to cart: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
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

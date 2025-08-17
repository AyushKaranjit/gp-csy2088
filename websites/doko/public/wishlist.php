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

document.addEventListener('DOMContentLoaded', function() { loadUnifiedWishlist(); });

async function loadUnifiedWishlist(){
    const container = document.getElementById('wishlist-items-container');
    const emptyState = document.getElementById('empty-wishlist');
        // Do not show a loading placeholder here to avoid flicker — render results or empty state directly
    let serverIds = [];
    let isLoggedIn = false;
    try {
        const resp = await fetch('api/wishlist/wishlist.php', {credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest'}});
        if (resp.status === 200) {
            const data = await resp.json();
            if (data && data.success) { serverIds = (data.items||[]).map(i=>parseInt(i.product_id)); isLoggedIn = true; }
        }
    } catch(e){ /* ignore */ }
    // Local storage fallback (legacy) - merge
    let localList = [];
    try { localList = JSON.parse(localStorage.getItem('doko_wishlist')||'[]').map(i=>parseInt(i.product_id)); } catch(e) { localList=[]; }
    const allIdsSet = new Set([ ...localList, ...serverIds ]);
    const allIds = Array.from(allIdsSet);
    if (allIds.length === 0){
        emptyState.style.display='block';
        // Avoid duplicating the same element id in DOM by appending a clone
        container.innerHTML = '';
        container.appendChild(emptyState.cloneNode(true));
        return;
    }
    emptyState.style.display='none';
    // Fetch product details in parallel (limit concurrency if large)
    const detailPromises = allIds.map(pid => fetch(`api/products/product-detail.php?id=${pid}`).then(r=>r.json()).catch(()=>null));
    const results = await Promise.all(detailPromises);
    const products = [];
    results.forEach((res,idx)=>{
        const pid = allIds[idx];
        if(res && (res.product||res.data)){
            const p = res.product||res.data; products.push(p);
        } else {
            // If missing remove from local storage & server (best effort)
            let wl = JSON.parse(localStorage.getItem('doko_wishlist')||'[]');
            wl = wl.filter(entry => parseInt(entry.product_id)!==pid); localStorage.setItem('doko_wishlist', JSON.stringify(wl));
        }
    });
    if (products.length === 0){
        container.innerHTML = '<div class="empty-wishlist"><div class="empty-state"><i class="fas fa-heart"></i><h3>Your wishlist items are no longer available</h3><p>Browse products and add new favorites.</p><a href="products.php" class="btn btn-primary">Shop Products</a></div></div>';
        return;
    }
    displayWishlistItems(products, isLoggedIn);
}

function displayWishlistItems(products, isLoggedIn) {
    const container = document.getElementById('wishlist-items-container');
    
    if (products.length === 0) {
        container.innerHTML = '<div class="error-message">Some wishlist items could not be loaded</div>';
        return;
    }
    
    const html = `
        <div class="wishlist-grid">
            ${products.map(product => {
                // Normalize image URL from API (may already start with /uploads)
                let img = product.image_url || '';
                if(!img || /default-product/.test(img)){ img = '/api/image-proxy.php?url='+encodeURIComponent('https://images.unsplash.com/photo-1567306226416-28f0efdc88ce?auto=format&fit=crop&w=600&q=60'); }
                if (img.startsWith('/')) {
                    img = img; // already absolute relative to origin
                } else if (!/^https?:/i.test(img)) {
                    // If not absolute, prepend /uploads/ unless already has uploads/
                    if (!img.startsWith('uploads/')) img = 'uploads/' + img;
                    img = '/' + img.replace(/^\/+/, '');
                }
                return `
                <div class="wishlist-item" data-id="${product.product_id}">
                    <img src="${img}" alt="${product.name}" class="wishlist-item-image" onerror="this.onerror=null;this.src='/api/image-proxy.php?url='+encodeURIComponent('https://images.unsplash.com/photo-1567303316750-b7a0c0f7b411?auto=format&fit=crop&w=600&q=60');">
                    <div class="wishlist-item-content">
                        <h3>${product.name}</h3>
                        <div class="wishlist-item-price">Rs. ${parseFloat(product.price).toFixed(2)}</div>
                        <div class="wishlist-item-actions">
                            <button class="btn btn-primary add-to-cart" data-id="${product.product_id}">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                            <button class="remove-wishlist" data-id="${product.product_id}" title="Remove from wishlist" data-logged-in="${isLoggedIn}">
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
        btn.addEventListener('click', async function() {
            const productId = this.dataset.id;
            const logged = this.getAttribute('data-logged-in') === 'true';
            if (logged){
                try {
                    await fetch(`api/wishlist/wishlist.php?product_id=${productId}`, { method:'DELETE', credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest'} });
                } catch(e){}
            }
            removeFromWishlist(productId);
            // Ensure header/count is refreshed after deletion
            if (typeof updateWishlistCount === 'function') {
                try { updateWishlistCount(); } catch(e){}
            }
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
    // Update local storage list
    let wishlist = [];
    try { wishlist = JSON.parse(localStorage.getItem('doko_wishlist')||'[]'); } catch(e){ wishlist=[]; }
    wishlist = wishlist.filter(item => parseInt(item.product_id) !== parseInt(productId));
    localStorage.setItem('doko_wishlist', JSON.stringify(wishlist));

    // Optimistically remove DOM node
    const itemEl = document.querySelector(`.wishlist-item[data-id="${productId}"]`);
    if (itemEl && itemEl.parentElement) {
        const grid = itemEl.parentElement;
        itemEl.remove();
        // If grid now empty, reload fully to show empty state template
        if (!grid.querySelector('.wishlist-item')) {
            loadUnifiedWishlist(true); // skip loading spinner, will show empty state
        }
    } else {
        // Fallback full reload if structure unexpected
        loadUnifiedWishlist(true);
    }

    // Update header wishlist count if available
    if (typeof updateWishlistCount === 'function') { try { updateWishlistCount(); } catch(e){} }
    const badge = document.getElementById('wishlist-count');
    if (badge) { badge.textContent = wishlist.length.toString(); }

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

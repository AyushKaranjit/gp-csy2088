<?php
// Start session and include configuration
session_start();
require_once '../template/config.php';

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
<?php include '../template/breadcrumb.php'; ?>

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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadWishlistItems();
});

function loadWishlistItems() {
    // This would typically load from localStorage or server
    const wishlistItems = JSON.parse(localStorage.getItem('wishlist') || '[]');
    
    if (wishlistItems.length === 0) {
        document.getElementById('empty-wishlist').style.display = 'block';
        return;
    }
    
    document.getElementById('empty-wishlist').style.display = 'none';
    displayWishlistItems(wishlistItems);
}

function displayWishlistItems(items) {
    const container = document.getElementById('wishlist-items-container');
    
    const html = `
        <div class="wishlist-grid">
            ${items.map(item => `
                <div class="wishlist-item" data-id="${item.id}">
                    <img src="${item.image}" alt="${item.name}" class="wishlist-item-image">
                    <div class="wishlist-item-content">
                        <h3>${item.name}</h3>
                        <div class="wishlist-item-price">Rs. ${item.price}</div>
                        <div class="wishlist-item-actions">
                            <button class="btn btn-primary add-to-cart" data-id="${item.id}">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                            <button class="remove-wishlist" data-id="${item.id}" title="Remove from wishlist">
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
            // Add to cart logic here
            console.log('Adding to cart:', productId);
        });
    });
    
    container.querySelectorAll('.remove-wishlist').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            removeFromWishlist(productId);
        });
    });
}

function removeFromWishlist(productId) {
    let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    wishlist = wishlist.filter(item => item.id !== productId);
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    loadWishlistItems();
}
</script>

<?php
// Include footer
include_footer();
?>

<?php
// Set page variables
$page_title = 'Wishlist - DOKO';
$current_page = 'wishlist';
$additional_css = ['css/wishlist.css'];
$additional_js = ['wishlist.js'];

// Include header template
include_once '../template/header.php';
?>

    <div class="wishlist-container">
        <div class="container">
            <div class="wishlist-header">
                <h1>My Wishlist</h1>
                <p class="wishlist-subtitle">Your saved items</p>
            </div>
            
            <div class="wishlist-content">
                <div id="empty-wishlist" style="display: none;">
                    <div class="empty-wishlist-content">
                        <i class="far fa-heart"></i>
                        <h3>Your wishlist is empty</h3>
                        <p>Save items you love for later!</p>
                        <a href="category.php" class="browse-products-btn">Browse Products</a>
                    </div>
                </div>
                
                <div class="wishlist-grid" id="wishlist-items">
                    <!-- Wishlist items will be dynamically loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadWishlist();
        });

        function loadWishlist() {
            const wishlistItems = JSON.parse(localStorage.getItem('wishlist')) || [];
            const wishlistGrid = document.getElementById('wishlist-items');
            const emptyWishlist = document.getElementById('empty-wishlist');
            
            if (wishlistItems.length === 0) {
                emptyWishlist.style.display = 'block';
                wishlistGrid.style.display = 'none';
            } else {
                emptyWishlist.style.display = 'none';
                wishlistGrid.style.display = 'grid';
                renderWishlistItems(wishlistItems);
            }
        }

        function renderWishlistItems(items) {
            const wishlistGrid = document.getElementById('wishlist-items');
            wishlistGrid.innerHTML = '';
            
            items.forEach((item, index) => {
                const wishlistItem = createWishlistItemElement(item, index);
                wishlistGrid.appendChild(wishlistItem);
            });
        }

        function createWishlistItemElement(item, index) {
            const wishlistItem = document.createElement('div');
            wishlistItem.className = 'wishlist-item';
            wishlistItem.innerHTML = `
                <div class="wishlist-item-image">
                    <img src="${item.image || 'https://via.placeholder.com/200'}" alt="${item.name}">
                    <button class="remove-wishlist" onclick="removeFromWishlist(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="wishlist-item-details">
                    <h4>${item.name}</h4>
                    <p class="wishlist-item-price">रू ${item.price}</p>
                    <button class="add-to-cart-btn" onclick="addToCartFromWishlist(${index})">
                        <i class="fas fa-shopping-cart"></i>
                        Add to Cart
                    </button>
                </div>
            `;
            return wishlistItem;
        }

        function removeFromWishlist(index) {
            const wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
            wishlist.splice(index, 1);
            localStorage.setItem('wishlist', JSON.stringify(wishlist));
            loadWishlist();
            updateWishlistCount();
        }

        function addToCartFromWishlist(index) {
            const wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
            const item = wishlist[index];
            
            // Add to cart
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const existingItem = cart.find(cartItem => cartItem.name === item.name);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({...item, quantity: 1});
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            
            // Show success message
            showNotification('Item added to cart!');
            
            // Update cart count if function exists
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
        }

        function updateWishlistCount() {
            const wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
            const wishlistBadges = document.querySelectorAll('.wishlist-count, .action-count');
            wishlistBadges.forEach(badge => {
                badge.textContent = wishlist.length;
            });
        }

        function showNotification(message) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'notification success';
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #27ae60;
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                z-index: 1000;
                font-weight: 500;
            `;
            
            document.body.appendChild(notification);
            
            // Remove notification after 3 seconds
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 3000);
        }
    </script>

<?php
// Include footer template
include_once '../template/footer.php';
?>

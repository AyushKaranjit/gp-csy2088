/**
 * Product Actions JavaScript
 * Handles product detail page interactions
 */

// Product Manager for handling product-related actions
const ProductManager = {
    // Add to cart with specific quantity
    addToCartWithQuantity: function(productId, quantity = 1, productName = 'Product') {
        quantity = parseInt(quantity) || 1;
        // Reuse global addToCart if present (has guest cart + locking logic)
        if (typeof window.addToCart === 'function') {
            window.addToCart(productId, quantity, productName);
            return;
        }
        // Fallback minimal implementation
        fetch('/api/cart/add.php', {
            method: 'POST',
            headers: { 'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest' },
            credentials:'same-origin',
            body: JSON.stringify({ product_id: productId, quantity })
        })
        .then(r=>r.json())
        .then(data=>{
            if(data.success){
                this.showNotification('Product added to cart!', 'success');
                if (typeof updateCartCount==='function') updateCartCount();
            } else if (data.message && /auth|login/i.test(data.message)) {
                this.showNotification('Please login to add items to cart', 'warning');
                setTimeout(()=>{ window.location.href='/login.php?redirect='+encodeURIComponent(location.pathname); },1200);
            } else {
                this.showNotification(data.message||'Failed to add to cart','error');
            }
        })
        .catch(e=>{ console.error('AddToCart fallback error', e); this.showNotification('Network error adding to cart','error'); });
    },
    
    // Toggle wishlist
    toggleWishlist: function(productId, ev) {
        const eventRef = ev || window.event; // support inline onclick
        fetch('/api/wishlist/wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                action: 'toggle',
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification(data.message, 'success');
                // Update wishlist icon
                let wishlistBtn = null;
                if (eventRef && eventRef.target) {
                    wishlistBtn = eventRef.target.closest('button');
                } else {
                    // attempt to locate by data attribute fallback
                    wishlistBtn = document.querySelector(`[data-wishlist-btn="${productId}"]`);
                }
                if (wishlistBtn) {
                    const icon = wishlistBtn.querySelector('i');
                    if (icon) {
                        if (data.in_wishlist) {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                        } else {
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                        }
                    }
                }
                // Update wishlist count if function exists
                if (typeof updateWishlistCount === 'function') {
                    updateWishlistCount();
                }
            } else {
                if (data.message && data.message.includes('log in')) {
                    this.showNotification('Please login to use wishlist', 'warning');
                    setTimeout(() => {
                        window.location.href = '/login.php?redirect=' + encodeURIComponent(window.location.pathname);
                    }, 1500);
                } else {
                    this.showNotification(data.message || 'Failed to update wishlist', 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error updating wishlist:', error);
            this.showNotification('An error occurred. Please try again.', 'error');
        });
    },
    
    // Show notification
    showNotification: function(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.product-notification');
        existingNotifications.forEach(n => n.remove());
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `product-notification notification-${type}`;
        
        // Set icon based on type
        let icon = 'info-circle';
        if (type === 'success') icon = 'check-circle';
        if (type === 'error') icon = 'exclamation-circle';
        if (type === 'warning') icon = 'exclamation-triangle';
        
        notification.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
        `;
        
        // Style the notification
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            animation: slideIn 0.3s ease;
            max-width: 400px;
        `;
        
        // Set colors based on type
        if (type === 'success') {
            notification.style.background = '#10b981';
            notification.style.color = 'white';
        } else if (type === 'error') {
            notification.style.background = '#ef4444';
            notification.style.color = 'white';
        } else if (type === 'warning') {
            notification.style.background = '#f59e0b';
            notification.style.color = 'white';
        } else {
            notification.style.background = '#3b82f6';
            notification.style.color = 'white';
        }
        
        // Add to body
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
};

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .product-notification {
        transition: all 0.3s ease;
    }
    
    .product-notification:hover {
        transform: translateX(-5px);
    }
`;
document.head.appendChild(style);

// Export for global use
window.ProductManager = ProductManager;

// --- Global convenience wrappers expected by inline HTML onclick attributes ---
// Safely define only if not already present to avoid clobbering any advanced implementation.
if (typeof window.addToCartWithQuantity !== 'function') {
    window.addToCartWithQuantity = function(productId, productName) {
        // Try to locate a quantity input: specific id pattern qty-<id> else generic 'quantity'
        let qtyInput = document.getElementById('qty-' + productId) || document.getElementById('quantity');
        let quantity = 1;
        if (qtyInput) {
            let parsed = parseInt(qtyInput.value, 10);
            if (!isNaN(parsed) && parsed > 0) quantity = parsed;
        }
        ProductManager.addToCartWithQuantity(productId, quantity, productName || 'Product');
    };
}

if (typeof window.toggleWishlist !== 'function') {
    window.toggleWishlist = function(productId, ev){
        ProductManager.toggleWishlist(productId, ev);
    };
}

// Remove item from wishlist (expects element with data-wishlist-item)
if (typeof window.removeFromWishlist !== 'function') {
    window.removeFromWishlist = async function(productId, ev){
        try {
            await fetch(`/api/wishlist/wishlist.php?product_id=${productId}`, { method:'DELETE', credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest'} });
        } catch(e) { /* ignore network error for UX resilience */ }
        // Update count if exposed
        if (typeof updateWishlistCount === 'function') { try { updateWishlistCount(); } catch(e){} }
        // Remove DOM node if present
        const item = document.querySelector(`[data-wishlist-item='${productId}']`) || document.querySelector(`.wishlist-item[data-id='${productId}']`);
        if (item) item.remove();
        // Provide minimal feedback
        if (ProductManager && typeof ProductManager.showNotification === 'function') {
            ProductManager.showNotification('Item removed from wishlist', 'success');
        }
    };
}

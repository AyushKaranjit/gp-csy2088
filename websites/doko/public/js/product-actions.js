/**
 * Product Actions JavaScript
 * Handles product detail page interactions
 */

// Product Manager for handling product-related actions
const ProductManager = {
    // Add to cart with specific quantity
    addToCartWithQuantity: function(productId, quantity = 1) {
        // Ensure quantity is a number
        quantity = parseInt(quantity) || 1;
        
        fetch('/api/cart/cart-add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success notification
                this.showNotification('Product added to cart!', 'success');
                // Update cart count if function exists
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
            } else {
                if (data.redirect) {
                    // User needs to login
                    this.showNotification('Please login to add items to cart', 'warning');
                    setTimeout(() => {
                        window.location.href = data.redirect + '?redirect=' + encodeURIComponent(window.location.pathname);
                    }, 1500);
                } else {
                    this.showNotification(data.message || 'Failed to add to cart', 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error adding to cart:', error);
            this.showNotification('An error occurred. Please try again.', 'error');
        });
    },
    
    // Toggle wishlist
    toggleWishlist: function(productId) {
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
                const wishlistBtn = event.target.closest('button');
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

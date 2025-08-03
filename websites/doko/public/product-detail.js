// Enhanced Product Detail Page JavaScript
// Complete implementation with tabs, reviews, and Daraz-like functionality

class ProductDetailManager {
    constructor() {
        this.productElements = null;
        this.quantityControls = null;
        this.productData = null;
        this.QUANTITY_LIMITS = { min: 1, max: 99 };
        
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.cacheElements();
            this.loadProductData();
            this.setupQuantityControls();
            this.setupWeightOptions();
            this.setupTabs();
            this.setupAddToCart();
            this.setupWishlist();
            this.setupThumbnails();
            this.initializeCartCount();
            this.populateProductDetails();
        });
    }

    cacheElements() {
        // Cache DOM elements for better performance
        this.productElements = {
            name: document.getElementById('product-name'),
            price: document.getElementById('product-price'),
            image: document.getElementById('product-image'),
            description: document.getElementById('product-description'),
            breadcrumb: document.getElementById('breadcrumb-product'),
            thumbnails: document.querySelectorAll('.thumbnail')
        };

        this.quantityControls = {
            input: document.querySelector('.quantity-display'),
            minusBtn: document.querySelector('.minus-btn'),
            plusBtn: document.querySelector('.plus-btn'),
            addToCartBtn: document.querySelector('.add-to-cart-btn'),
            buyNowBtn: document.querySelector('.buy-now-btn'),
            wishlistBtn: document.getElementById('wishlist-btn')
        };

        this.weightButtons = document.querySelectorAll('.weight-btn');
        this.tabHeaders = document.querySelectorAll('.tab-header');
        this.tabContents = document.querySelectorAll('.tab-content');
    }

    loadProductData() {
        // Parse URL parameters or use default data
        const urlParams = new URLSearchParams(window.location.search);
        this.productData = {
            name: urlParams.get('name') || 'Premium Organic Apple',
            price: urlParams.get('price') || 'रू 180',
            image: urlParams.get('image') || 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=450&h=450&fit=crop&crop=center',
            category: urlParams.get('category') || 'fruit'
        };
    }

    populateProductDetails() {
        const { name, price, image, category } = this.productData;
        
        // Update DOM elements efficiently
        if (this.productElements.name) {
            this.productElements.name.textContent = name;
        }
        
        if (this.productElements.price) {
            this.productElements.price.textContent = price;
        }
        
        if (this.productElements.image) {
            this.productElements.image.src = image;
            this.productElements.image.alt = name;
        }
        
        // Update thumbnails
        this.productElements.thumbnails.forEach(thumb => {
            thumb.src = image;
            thumb.alt = name;
        });
        
        // Update page title
        document.title = `${name} - DOKO`;
        
        // Update breadcrumb
        if (this.productElements.breadcrumb) {
            this.productElements.breadcrumb.textContent = name;
        }
        
        // Set description based on category
        const description = this.getCategoryDescription(category);
        if (this.productElements.description) {
            this.productElements.description.textContent = description;
        }
    }

    getCategoryDescription(category) {
        const descriptions = {
            fruit: "Premium organic apples sourced directly from the pristine valleys of Mustang, Nepal. These apples are grown using traditional organic farming methods without any pesticides or chemical fertilizers. Each apple is hand-picked at the perfect ripeness to ensure maximum sweetness and nutritional value.",
            vegetables: "Farm-fresh vegetables grown with care. Perfect for healthy meals and nutritious cooking.",
            meat: "Premium quality meat, carefully selected and processed. Fresh, tender, and perfect for all your cooking needs.",
            dairy: "Fresh dairy products from trusted sources. Creamy, nutritious, and perfect for your daily needs.",
            snacks: "Delicious and healthy snacks perfect for any time of the day. Made with quality ingredients.",
            drinks: "Refreshing beverages and healthy drinks. Perfect for hydration and nutrition.",
            default: "High-quality product carefully selected for our customers. Fresh, reliable, and delivered with care."
        };
        
        return descriptions[category] || descriptions.default;
    }

    setupQuantityControls() {
        if (!this.quantityControls.input) return;

        // Utility function to update quantity
        const updateQuantity = (newValue) => {
            const clampedValue = Math.max(this.QUANTITY_LIMITS.min, Math.min(this.QUANTITY_LIMITS.max, newValue));
            this.quantityControls.input.value = clampedValue;
            return clampedValue;
        };

        // Event listeners for quantity buttons
        if (this.quantityControls.minusBtn) {
            this.quantityControls.minusBtn.addEventListener('click', () => {
                updateQuantity(parseInt(this.quantityControls.input.value) - 1);
            });
        }

        if (this.quantityControls.plusBtn) {
            this.quantityControls.plusBtn.addEventListener('click', () => {
                updateQuantity(parseInt(this.quantityControls.input.value) + 1);
            });
        }

        // Input validation
        this.quantityControls.input.addEventListener('input', function() {
            const value = parseInt(this.value);
            if (isNaN(value) || value < 1) {
                updateQuantity(1);
            } else if (value > 99) {
                updateQuantity(99);
            }
        });

        // Prevent invalid characters
        this.quantityControls.input.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                e.preventDefault();
            }
        });
    }

    setupWeightOptions() {
        this.weightButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                this.weightButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                button.classList.add('active');
                
                // Update price based on weight
                const newPrice = button.dataset.price;
                if (newPrice && this.productElements.price) {
                    this.productElements.price.textContent = `रू ${newPrice}`;
                }
                
                // Update add to cart button data
                if (this.quantityControls.addToCartBtn) {
                    this.quantityControls.addToCartBtn.dataset.price = newPrice;
                }
            });
        });
    }

    setupTabs() {
        this.tabHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const targetTab = header.dataset.tab;
                
                // Remove active class from all headers and contents
                this.tabHeaders.forEach(h => h.classList.remove('active'));
                this.tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked header and corresponding content
                header.classList.add('active');
                const targetContent = document.getElementById(targetTab);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    }

    setupThumbnails() {
        this.productElements.thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', () => {
                // Remove active class from all thumbnails
                this.productElements.thumbnails.forEach(thumb => thumb.classList.remove('active'));
                
                // Add active class to clicked thumbnail
                thumbnail.classList.add('active');
                
                // Update main image
                if (this.productElements.image) {
                    this.productElements.image.src = thumbnail.src;
                }
            });
        });
    }

    setupAddToCart() {
        if (this.quantityControls.addToCartBtn) {
            this.quantityControls.addToCartBtn.addEventListener('click', (e) => {
                this.handleAddToCart(e.target);
            });
        }

        if (this.quantityControls.buyNowBtn) {
            this.quantityControls.buyNowBtn.addEventListener('click', (e) => {
                this.handleBuyNow(e.target);
            });
        }
    }

    setupWishlist() {
        if (this.quantityControls.wishlistBtn) {
            this.quantityControls.wishlistBtn.addEventListener('click', () => {
                const productName = this.productElements.name?.textContent || 'Product';
                const productPrice = this.productElements.price?.textContent.replace(/[^\d]/g, '') || '0';
                const productImage = this.productElements.image?.src || '';
                
                if (typeof addToWishlist === 'function') {
                    addToWishlist(productName, productPrice, productImage);
                    this.showWishlistFeedback();
                } else {
                    console.warn('Wishlist functionality not loaded');
                }
            });
        }
    }

    handleAddToCart(button) {
        const quantity = parseInt(this.quantityControls.input?.value || 1);
        const productName = this.productElements.name?.textContent || 'Product';
        const productPrice = this.productElements.price?.textContent.replace(/[^\d]/g, '') || '0';
        
        // Use external cart.js function if available
        if (typeof addToCart === 'function') {
            addToCart(productName, productPrice, quantity);
            this.showAddToCartFeedback(button);
        } else {
            console.warn('Cart functionality not loaded');
            this.showError('Unable to add to cart. Please refresh the page.');
        }
    }

    handleBuyNow(button) {
        // First add to cart
        this.handleAddToCart(this.quantityControls.addToCartBtn);
        
        // Then redirect to cart page after a brief delay
        setTimeout(() => {
            window.location.href = 'cart.html';
        }, 1000);
        
        // Show buy now feedback
        this.showBuyNowFeedback(button);
    }

    showAddToCartFeedback(button) {
        if (!button) return;
        
        const originalText = button.innerHTML;
        const originalBackground = button.style.backgroundColor;
        
        button.style.backgroundColor = '#28a745';
        button.innerHTML = '<i class="fas fa-check"></i> Added to Cart!';
        button.disabled = true;
        
        setTimeout(() => {
            button.style.backgroundColor = originalBackground;
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
    }

    showBuyNowFeedback(button) {
        if (!button) return;
        
        const originalText = button.textContent;
        
        button.textContent = 'Redirecting to Cart...';
        button.disabled = true;
        
        setTimeout(() => {
            button.textContent = originalText;
            button.disabled = false;
        }, 3000);
    }

    showWishlistFeedback() {
        const wishlistBtn = this.quantityControls.wishlistBtn;
        if (!wishlistBtn) return;
        
        const icon = wishlistBtn.querySelector('i');
        if (icon) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            icon.style.color = '#e74c3c';
        }
        
        // Show notification
        this.showNotification('Added to Wishlist!', 'success');
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : '#007bff'};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 10000;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateX(400px);
            transition: transform 0.3s ease;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Animate out and remove
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    showError(message) {
        this.showNotification(message, 'error');
        console.error(message);
    }

    initializeCartCount() {
        // Initialize cart count using external cart.js functionality
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        }
    }
}

// Review functionality
class ReviewManager {
    constructor() {
        this.setupReviewActions();
    }

    setupReviewActions() {
        document.addEventListener('DOMContentLoaded', () => {
            // Load more reviews button
            const loadMoreBtn = document.querySelector('.load-more-reviews');
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', this.loadMoreReviews.bind(this));
            }

            // Review action buttons (like, dislike, reply)
            const reviewActions = document.querySelectorAll('.review-action');
            reviewActions.forEach(action => {
                action.addEventListener('click', this.handleReviewAction.bind(this));
            });

            // Review images
            const reviewImages = document.querySelectorAll('.review-images img');
            reviewImages.forEach(img => {
                img.addEventListener('click', this.showImageModal.bind(this));
            });
        });
    }

    loadMoreReviews() {
        // Simulate loading more reviews
        console.log('Loading more reviews...');
        // In a real implementation, this would fetch more reviews from the server
    }

    handleReviewAction(event) {
        const button = event.target;
        const action = button.textContent.trim();
        
        if (action.includes('👍')) {
            this.handleLike(button);
        } else if (action.includes('👎')) {
            this.handleDislike(button);
        } else if (action === 'Reply') {
            this.handleReply(button);
        }
    }

    handleLike(button) {
        const currentText = button.textContent;
        const currentCount = parseInt(currentText.match(/\d+/)?.[0] || 0);
        button.textContent = `👍 ${currentCount + 1}`;
        button.style.color = '#007bff';
    }

    handleDislike(button) {
        const currentText = button.textContent;
        const currentCount = parseInt(currentText.match(/\d+/)?.[0] || 0);
        button.textContent = `👎 ${currentCount + 1}`;
        button.style.color = '#6c757d';
    }

    handleReply(button) {
        // In a real implementation, this would open a reply form
        console.log('Reply functionality would be implemented here');
    }

    showImageModal(event) {
        const img = event.target;
        // Create modal to show full-size image
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            cursor: pointer;
        `;
        
        const modalImg = document.createElement('img');
        modalImg.src = img.src;
        modalImg.style.cssText = `
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
        `;
        
        modal.appendChild(modalImg);
        document.body.appendChild(modal);
        
        modal.addEventListener('click', () => {
            document.body.removeChild(modal);
        });
    }
}

// Initialize managers
new ProductDetailManager();
new ReviewManager();

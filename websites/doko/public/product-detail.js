// Product Detail Page JavaScript
// Optimized and clean implementation

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
            this.setupAddToCart();
            this.initializeCartCount();
        });
    }

    cacheElements() {
        // Cache DOM elements for better performance
        this.productElements = {
            name: document.getElementById('product-name'),
            price: document.getElementById('product-price'),
            image: document.getElementById('product-image'),
            description: document.getElementById('product-description'),
            breadcrumb: document.getElementById('breadcrumb-product')
        };

        this.quantityControls = {
            input: document.querySelector('.quantity-display'),
            minusBtn: document.querySelector('.minus-btn'),
            plusBtn: document.querySelector('.plus-btn'),
            addToCartBtn: document.querySelector('.add-to-cart-btn')
        };
    }

    loadProductData() {
        // Parse URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        this.productData = {
            name: urlParams.get('name'),
            price: urlParams.get('price'),
            image: urlParams.get('image'),
            category: urlParams.get('category')
        };

        if (this.productData.name) {
            this.populateProductDetails();
        }
    }

    populateProductDetails() {
        const { name, price, image, category } = this.productData;
        
        // Update DOM elements efficiently
        this.productElements.name.textContent = name;
        this.productElements.price.textContent = price;
        this.productElements.image.src = image;
        this.productElements.image.alt = name;
        
        // Update page title
        document.title = `${name} - DOKO`;
        
        // Update breadcrumb
        if (this.productElements.breadcrumb) {
            this.productElements.breadcrumb.textContent = name;
        }
        
        // Set description based on category
        const description = this.getCategoryDescription(category);
        this.productElements.description.textContent = description;
    }

    getCategoryDescription(category) {
        const descriptions = {
            fruits: "Fresh, hand-picked fruits delivered straight from the farm. Rich in vitamins, minerals, and natural goodness.",
            vegetables: "Farm-fresh vegetables grown with care. Perfect for healthy meals and nutritious cooking.",
            meat: "Premium quality meat, carefully selected and processed. Fresh, tender, and perfect for all your cooking needs.",
            dairy: "Fresh dairy products from trusted sources. Creamy, nutritious, and perfect for your daily needs.",
            snacks: "Delicious and healthy snacks perfect for any time of the day. Made with quality ingredients.",
            default: "High-quality product carefully selected for our customers. Fresh, reliable, and delivered with care."
        };
        
        return descriptions[category] || descriptions.default;
    }

    setupQuantityControls() {
        // Utility function to update quantity
        const updateQuantity = (newValue) => {
            const clampedValue = Math.max(this.QUANTITY_LIMITS.min, Math.min(this.QUANTITY_LIMITS.max, newValue));
            this.quantityControls.input.value = clampedValue;
            return clampedValue;
        };

        // Event listeners for quantity buttons
        this.quantityControls.minusBtn.addEventListener('click', () => {
            updateQuantity(parseInt(this.quantityControls.input.value) - 1);
        });

        this.quantityControls.plusBtn.addEventListener('click', () => {
            updateQuantity(parseInt(this.quantityControls.input.value) + 1);
        });

        // Input validation
        this.quantityControls.input.addEventListener('input', function() {
            const value = parseInt(this.value);
            if (isNaN(value) || value < this.QUANTITY_LIMITS.min) {
                updateQuantity(this.QUANTITY_LIMITS.min);
            } else if (value > this.QUANTITY_LIMITS.max) {
                updateQuantity(this.QUANTITY_LIMITS.max);
            }
        }.bind(this));

        // Prevent invalid characters
        this.quantityControls.input.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                e.preventDefault();
            }
        });
    }

    setupAddToCart() {
        this.quantityControls.addToCartBtn.addEventListener('click', (e) => {
            this.handleAddToCart(e.target);
        });
    }

    handleAddToCart(button) {
        const quantity = parseInt(this.quantityControls.input.value);
        const productName = this.productElements.name.textContent;
        const productPrice = this.productElements.price.textContent.replace(/[^\d]/g, '');
        
        // Use external cart.js function if available
        if (typeof addToCart === 'function') {
            addToCart(productName, productPrice, quantity);
            this.showAddToCartFeedback(button);
        } else {
            console.warn('Cart functionality not loaded');
            this.showError('Unable to add to cart. Please refresh the page.');
        }
    }

    showAddToCartFeedback(button) {
        const originalText = button.textContent;
        const originalBackground = button.style.backgroundColor;
        
        button.style.backgroundColor = '#28a745';
        button.textContent = '✓ Added to Cart!';
        button.disabled = true;
        
        setTimeout(() => {
            button.style.backgroundColor = originalBackground;
            button.textContent = originalText;
            button.disabled = false;
        }, 2000);
    }

    showError(message) {
        // Simple error display - could be enhanced with a toast notification
        console.error(message);
        alert(message);
    }

    initializeCartCount() {
        // Initialize cart count using external cart.js functionality
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        }
    }
}

// Initialize the product detail manager
new ProductDetailManager();

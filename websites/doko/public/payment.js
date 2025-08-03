// Enhanced Payment Processing for DOKO Grocery Store

class PaymentManager {
    constructor() {
        this.cart = JSON.parse(localStorage.getItem('doko-cart')) || [];
        this.deliveryFee = 50;
        this.isGuestCheckout = new URLSearchParams(window.location.search).get('guest') === 'true';
        this.initializePayment();
    }

    async initializePayment() {
        await this.loadCartSummary();
        this.setupPaymentForm();
        this.setupAddressForm();
        
        // Check if user is logged in (unless guest checkout)
        if (!this.isGuestCheckout) {
            await this.loadUserProfile();
        }
    }

    async loadCartSummary() {
        const summaryContainer = document.getElementById('order-summary');
        if (!summaryContainer) return;

        if (this.cart.length === 0) {
            summaryContainer.innerHTML = `
                <div class="empty-cart-message">
                    <h3>Your cart is empty</h3>
                    <p>Add some products before checkout</p>
                    <a href="category.html" class="continue-shopping-btn">Continue Shopping</a>
                </div>
            `;
            return;
        }

        const subtotal = this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        const total = subtotal + this.deliveryFee;

        summaryContainer.innerHTML = `
            <h3>Order Summary</h3>
            <div class="order-items">
                ${this.cart.map(item => `
                    <div class="order-item">
                        <div class="item-image">
                            <img src="${item.imageUrl || item.image}" alt="${item.productName || item.product}" 
                                 onerror="this.src='https://via.placeholder.com/60x60?text=Product'">
                        </div>
                        <div class="item-details">
                            <h4>${item.productName || item.product}</h4>
                            <p>Quantity: ${item.quantity}</p>
                            <p class="item-price">रू ${(item.price * item.quantity).toFixed(0)}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
            <div class="order-totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>रू ${subtotal.toFixed(0)}</span>
                </div>
                <div class="total-row">
                    <span>Delivery Fee:</span>
                    <span>रू ${this.deliveryFee}</span>
                </div>
                <div class="total-row total-final">
                    <span>Total:</span>
                    <span>रू ${total.toFixed(0)}</span>
                </div>
            </div>
        `;
    }

    async loadUserProfile() {
        try {
            if (typeof api !== 'undefined' && typeof authManager !== 'undefined' && authManager.isLoggedIn()) {
                const user = authManager.getCurrentUser();
                if (user) {
                    this.prefillUserData(user);
                }
            }
        } catch (error) {
            console.error('Failed to load user profile:', error);
        }
    }

    prefillUserData(user) {
        const nameInput = document.getElementById('customer-name');
        const emailInput = document.getElementById('customer-email');
        const phoneInput = document.getElementById('customer-phone');

        if (nameInput && user.name) nameInput.value = user.name;
        if (emailInput && user.email) emailInput.value = user.email;
        if (phoneInput && user.phone) phoneInput.value = user.phone;
    }

    setupPaymentForm() {
        const paymentForm = document.getElementById('payment-form');
        if (paymentForm) {
            paymentForm.addEventListener('submit', this.handlePaymentSubmission.bind(this));
        }

        // Payment method selection
        const paymentMethods = document.querySelectorAll('input[name="payment-method"]');
        paymentMethods.forEach(method => {
            method.addEventListener('change', this.handlePaymentMethodChange.bind(this));
        });

        // Default to cash on delivery
        const codOption = document.querySelector('input[value="cash_on_delivery"]');
        if (codOption) {
            codOption.checked = true;
            this.handlePaymentMethodChange({ target: codOption });
        }
    }

    setupAddressForm() {
        // Add address validation and formatting
        const addressInput = document.getElementById('delivery-address');
        if (addressInput) {
            addressInput.addEventListener('blur', this.validateAddress.bind(this));
        }

        // Phone number formatting
        const phoneInput = document.getElementById('customer-phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9\-\+\(\)\s]/g, '');
            });
        }
    }

    handlePaymentMethodChange(e) {
        const paymentMethod = e.target.value;
        const cardDetails = document.getElementById('card-details');
        const bankDetails = document.getElementById('bank-details');
        const codNote = document.getElementById('cod-note');

        // Hide all payment details
        if (cardDetails) cardDetails.style.display = 'none';
        if (bankDetails) bankDetails.style.display = 'none';
        if (codNote) codNote.style.display = 'none';

        // Show relevant payment details
        switch (paymentMethod) {
            case 'credit_card':
            case 'debit_card':
                if (cardDetails) cardDetails.style.display = 'block';
                break;
            case 'bank_transfer':
                if (bankDetails) bankDetails.style.display = 'block';
                break;
            case 'cash_on_delivery':
                if (codNote) codNote.style.display = 'block';
                break;
        }
    }

    validateAddress(e) {
        const address = e.target.value.trim();
        if (address.length < 10) {
            e.target.setCustomValidity('Please provide a detailed delivery address');
            e.target.classList.add('invalid');
        } else {
            e.target.setCustomValidity('');
            e.target.classList.remove('invalid');
        }
    }

    async handlePaymentSubmission(e) {
        e.preventDefault();

        if (this.cart.length === 0) {
            this.showMessage('Your cart is empty', 'error');
            return;
        }

        // Get form data
        const formData = new FormData(e.target);
        const orderData = {
            customer_name: formData.get('customer-name'),
            customer_email: formData.get('customer-email'),
            customer_phone: formData.get('customer-phone'),
            delivery_address: formData.get('delivery-address'),
            payment_method: formData.get('payment-method'),
            special_instructions: formData.get('special-instructions') || '',
            items: this.cart.map(item => ({
                product_id: item.productId || item.id,
                product_name: item.productName || item.product,
                quantity: item.quantity,
                price: item.price
            })),
            delivery_fee: this.deliveryFee
        };

        // Validation
        if (!orderData.customer_name || !orderData.customer_email || 
            !orderData.customer_phone || !orderData.delivery_address) {
            this.showMessage('Please fill in all required fields', 'error');
            return;
        }

        // Show loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Processing Order...';
        submitBtn.disabled = true;

        try {
            // Process payment based on method
            let paymentResult;
            switch (orderData.payment_method) {
                case 'credit_card':
                case 'debit_card':
                    paymentResult = await this.processCardPayment(formData);
                    break;
                case 'bank_transfer':
                    paymentResult = await this.processBankTransfer(formData);
                    break;
                case 'cash_on_delivery':
                    paymentResult = await this.processCashOnDelivery(orderData);
                    break;
                default:
                    throw new Error('Invalid payment method');
            }

            if (paymentResult.success) {
                // Clear cart and redirect to success page
                localStorage.removeItem('doko-cart');
                this.showMessage('Order placed successfully!', 'success');
                
                setTimeout(() => {
                    window.location.href = `order-success.html?order=${paymentResult.orderId}`;
                }, 2000);
            } else {
                throw new Error(paymentResult.error || 'Payment failed');
            }

        } catch (error) {
            this.showMessage(error.message, 'error');
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    }

    async processCardPayment(formData) {
        // In a real implementation, this would integrate with a payment gateway
        // For demo purposes, we'll simulate card payment
        
        const cardNumber = formData.get('card-number');
        const expiryDate = formData.get('expiry-date');
        const cvv = formData.get('cvv');

        if (!cardNumber || !expiryDate || !cvv) {
            throw new Error('Please fill in all card details');
        }

        // Simulate payment processing delay
        await new Promise(resolve => setTimeout(resolve, 2000));

        // For demo, randomly succeed or fail
        if (Math.random() > 0.1) { // 90% success rate
            return {
                success: true,
                orderId: 'ORD' + Date.now(),
                transactionId: 'TXN' + Date.now()
            };
        } else {
            throw new Error('Card payment failed. Please try again.');
        }
    }

    async processBankTransfer(formData) {
        // Simulate bank transfer processing
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        return {
            success: true,
            orderId: 'ORD' + Date.now(),
            paymentStatus: 'pending_verification'
        };
    }

    async processCashOnDelivery(orderData) {
        try {
            if (typeof api !== 'undefined') {
                // Use API to create order
                const response = await api.createOrder(orderData);
                if (response.success) {
                    return {
                        success: true,
                        orderId: response.data.order_id
                    };
                } else {
                    throw new Error(response.error);
                }
            } else {
                // Fallback: simulate order creation
                await new Promise(resolve => setTimeout(resolve, 1000));
                return {
                    success: true,
                    orderId: 'ORD' + Date.now()
                };
            }
        } catch (error) {
            throw new Error('Failed to create order: ' + error.message);
        }
    }

    showMessage(message, type = 'info') {
        // Use existing notification system if available
        if (typeof cartManager !== 'undefined') {
            cartManager.showNotification(message, type);
            return;
        }

        // Fallback notification
        const notification = document.createElement('div');
        notification.className = `payment-notification ${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        `;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);

        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }
}

// Initialize Payment Manager
document.addEventListener('DOMContentLoaded', function() {
    const paymentManager = new PaymentManager();
    
    // Make available globally
    window.paymentManager = paymentManager;
});

// Utility functions for payment page
function formatCardNumber(input) {
    let value = input.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    input.value = formattedValue;
}

function formatExpiryDate(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    input.value = value;
}

function validateCVV(input) {
    input.value = input.value.replace(/[^0-9]/g, '').substring(0, 4);
}

// Export for global use
window.formatCardNumber = formatCardNumber;
window.formatExpiryDate = formatExpiryDate;
window.validateCVV = validateCVV;

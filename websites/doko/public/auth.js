// Enhanced Authentication for DOKO Grocery Store

class AuthManager {
    constructor() {
        this.currentUser = null;
        this.initializeAuth();
    }

    async initializeAuth() {
        try {
            if (typeof api !== 'undefined') {
                const response = await api.getCurrentUser();
                if (response.success) {
                    this.currentUser = response.user;
                    this.updateAuthUI(true);
                }
            }
        } catch (error) {
            console.log('User not logged in');
            this.updateAuthUI(false);
        }
    }

    async login(email, password) {
        try {
            if (typeof api === 'undefined') {
                throw new Error('API not available');
            }

            const response = await api.login(email, password);
            
            if (response.success) {
                this.currentUser = response.user;
                this.updateAuthUI(true);
                this.showMessage('Login successful!', 'success');
                
                // Redirect after successful login
                setTimeout(() => {
                    const redirect = new URLSearchParams(window.location.search).get('redirect');
                    window.location.href = redirect || 'index.html';
                }, 1500);
                
                return true;
            } else {
                throw new Error(response.error || 'Login failed');
            }
        } catch (error) {
            this.showMessage(error.message, 'error');
            return false;
        }
    }

    async register(userData) {
        try {
            if (typeof api === 'undefined') {
                throw new Error('API not available');
            }

            const response = await api.register(userData);
            
            if (response.success) {
                this.showMessage('Registration successful! Please login.', 'success');
                
                // Redirect to login page
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 1500);
                
                return true;
            } else {
                throw new Error(response.error || 'Registration failed');
            }
        } catch (error) {
            this.showMessage(error.message, 'error');
            return false;
        }
    }

    async logout() {
        try {
            if (typeof api !== 'undefined') {
                await api.logout();
            }
            
            this.currentUser = null;
            this.updateAuthUI(false);
            this.showMessage('Logged out successfully', 'success');
            
            // Redirect to homepage
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1000);
            
        } catch (error) {
            // Force logout even if API call fails
            this.currentUser = null;
            this.updateAuthUI(false);
            window.location.href = 'index.html';
        }
    }

    updateAuthUI(isLoggedIn) {
        // Update login/logout buttons
        const loginButtons = document.querySelectorAll('.login-btn');
        const logoutButtons = document.querySelectorAll('.logout-btn');
        const userMenus = document.querySelectorAll('.user-menu');
        const guestMenus = document.querySelectorAll('.guest-menu');

        loginButtons.forEach(btn => {
            btn.style.display = isLoggedIn ? 'none' : 'block';
        });

        logoutButtons.forEach(btn => {
            btn.style.display = isLoggedIn ? 'block' : 'none';
        });

        userMenus.forEach(menu => {
            menu.style.display = isLoggedIn ? 'block' : 'none';
        });

        guestMenus.forEach(menu => {
            menu.style.display = isLoggedIn ? 'none' : 'block';
        });

        // Update user name displays
        if (isLoggedIn && this.currentUser) {
            const userNameElements = document.querySelectorAll('.user-name');
            userNameElements.forEach(element => {
                element.textContent = this.currentUser.name;
            });
        }
    }

    showMessage(message, type = 'info') {
        // Use cart manager notification if available
        if (typeof cartManager !== 'undefined') {
            cartManager.showNotification(message, type);
            return;
        }

        // Fallback notification
        const notification = document.createElement('div');
        notification.className = `auth-notification ${type}`;
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

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);

        // Animate out and remove
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    isLoggedIn() {
        return this.currentUser !== null;
    }

    getCurrentUser() {
        return this.currentUser;
    }
}

// Initialize Auth Manager
const authManager = new AuthManager();

// Login Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    const signupForm = document.getElementById('signup-form');
    if (signupForm) {
        signupForm.addEventListener('submit', handleSignup);
    }

    // Logout buttons
    const logoutButtons = document.querySelectorAll('.logout-btn');
    logoutButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            authManager.logout();
        });
    });

    // Form validation
    setupFormValidation();
});

async function handleLogin(e) {
    e.preventDefault();
    
    const email = e.target.email.value.trim();
    const password = e.target.password.value;
    
    if (!email || !password) {
        authManager.showMessage('Please fill in all fields', 'error');
        return;
    }

    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Logging in...';
    submitBtn.disabled = true;

    const success = await authManager.login(email, password);

    // Reset button state
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;

    if (!success) {
        // Clear password field on failed login
        e.target.password.value = '';
    }
}

async function handleSignup(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const userData = {
        name: formData.get('name').trim(),
        email: formData.get('email').trim(),
        phone: formData.get('phone').trim(),
        password: formData.get('password'),
        confirm_password: formData.get('confirm_password')
    };

    // Validation
    if (!userData.name || !userData.email || !userData.phone || !userData.password) {
        authManager.showMessage('Please fill in all fields', 'error');
        return;
    }

    if (userData.password !== userData.confirm_password) {
        authManager.showMessage('Passwords do not match', 'error');
        return;
    }

    if (userData.password.length < 6) {
        authManager.showMessage('Password must be at least 6 characters', 'error');
        return;
    }

    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Creating account...';
    submitBtn.disabled = true;

    const success = await authManager.register(userData);

    // Reset button state
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
}

function setupFormValidation() {
    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const email = this.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.setCustomValidity('Please enter a valid email address');
                this.classList.add('invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('invalid');
            }
        });
    });

    // Phone validation
    const phoneInputs = document.querySelectorAll('input[name="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Allow only numbers, spaces, and common phone characters
            this.value = this.value.replace(/[^0-9\s\-\+\(\)]/g, '');
        });

        input.addEventListener('blur', function() {
            const phone = this.value.replace(/\D/g, '');
            if (phone && phone.length < 10) {
                this.setCustomValidity('Please enter a valid phone number');
                this.classList.add('invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('invalid');
            }
        });
    });

    // Password strength indicator
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        if (input.name === 'password') {
            input.addEventListener('input', function() {
                showPasswordStrength(this);
            });
        }
    });

    // Confirm password validation
    const confirmPasswordInputs = document.querySelectorAll('input[name="confirm_password"]');
    confirmPasswordInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const password = document.querySelector('input[name="password"]').value;
            if (this.value && this.value !== password) {
                this.setCustomValidity('Passwords do not match');
                this.classList.add('invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('invalid');
            }
        });
    });
}

function showPasswordStrength(passwordInput) {
    const password = passwordInput.value;
    let strength = 0;
    let feedback = [];

    // Check password criteria
    if (password.length >= 8) strength++;
    else feedback.push('At least 8 characters');

    if (/[a-z]/.test(password)) strength++;
    else feedback.push('Lowercase letter');

    if (/[A-Z]/.test(password)) strength++;
    else feedback.push('Uppercase letter');

    if (/[0-9]/.test(password)) strength++;
    else feedback.push('Number');

    if (/[^A-Za-z0-9]/.test(password)) strength++;
    else feedback.push('Special character');

    // Create or update strength indicator
    let strengthIndicator = passwordInput.parentNode.querySelector('.password-strength');
    if (!strengthIndicator) {
        strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength';
        passwordInput.parentNode.appendChild(strengthIndicator);
    }

    const strengthLevels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    const strengthColors = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#16a34a'];

    strengthIndicator.innerHTML = `
        <div class="strength-bar">
            <div class="strength-fill" style="width: ${strength * 20}%; background-color: ${strengthColors[strength - 1] || '#ef4444'}"></div>
        </div>
        <div class="strength-text" style="color: ${strengthColors[strength - 1] || '#ef4444'}">
            ${strengthLevels[strength - 1] || 'Very Weak'}
        </div>
        ${feedback.length > 0 ? `<div class="strength-feedback">Missing: ${feedback.join(', ')}</div>` : ''}
    `;
}

// Guest checkout handler
function handleGuestCheckout() {
    const cartData = typeof getCartData === 'function' ? getCartData() : {
        items: JSON.parse(localStorage.getItem('doko-cart') || '[]'),
        total: 0,
        count: 0
    };

    if (cartData.items.length === 0) {
        authManager.showMessage('Your cart is empty', 'error');
        return;
    }

    // Redirect to payment page for guest checkout
    window.location.href = 'payment.html?guest=true';
}

// Social login handlers (for future implementation)
function handleGoogleLogin() {
    authManager.showMessage('Google login coming soon!', 'info');
}

function handleFacebookLogin() {
    authManager.showMessage('Facebook login coming soon!', 'info');
}

// Export for global use
window.authManager = authManager;
window.handleGuestCheckout = handleGuestCheckout;
window.handleGoogleLogin = handleGoogleLogin;
window.handleFacebookLogin = handleFacebookLogin;

<?php
// Start session and include configuration
session_start();
require_once '../template/config.php';

// Page-specific variables
$page_title = page_title('Login');
$page_description = 'Login to your DOKO account for faster checkout and order tracking.';
$current_page = 'login';

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Login', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php include '../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Welcome Back</h1>
                    <p>Login to your DOKO account</p>
                </div>

                <div class="auth-content">
                    <form id="login-form" class="auth-form">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Enter your email" autocomplete="email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember_me">
                                <span class="checkmark"></span>
                                Remember me
                            </label>
                            <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg auth-submit">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>

                    <div class="auth-divider">
                        <span>or</span>
                    </div>

                    <div class="social-login">
                        <button class="btn btn-social google-btn">
                            <i class="fab fa-google"></i>
                            Continue with Google
                        </button>
                        <button class="btn btn-social facebook-btn">
                            <i class="fab fa-facebook-f"></i>
                            Continue with Facebook
                        </button>
                    </div>

                    <div class="auth-footer">
                        <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                    </div>
                </div>
            </div>

            <!-- Benefits Section -->
            <div class="benefits-card">
                <h3>Why Login to DOKO?</h3>
                <div class="benefits-list">
                    <div class="benefit-item">
                        <i class="fas fa-shipping-fast"></i>
                        <div>
                            <h4>Faster Checkout</h4>
                            <p>Save your address and payment details for quick orders</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-history"></i>
                        <div>
                            <h4>Order History</h4>
                            <p>Track your orders and reorder your favorites easily</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-star"></i>
                        <div>
                            <h4>Exclusive Offers</h4>
                            <p>Get access to member-only deals and discounts</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-heart"></i>
                        <div>
                            <h4>Wishlist</h4>
                            <p>Save your favorite products for later purchase</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.auth-container {
    max-width: 1000px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    align-items: start;
}

.auth-card {
    background: var(--white);
    border-radius: var(--border-radius);
    padding: 2.5rem;
    box-shadow: var(--shadow);
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h1 {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: var(--light-text);
}

.auth-form {
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--dark-text);
}

.input-group {
    position: relative;
}

.input-group i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--light-text);
    z-index: 2;
}

.input-group input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 3rem;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius);
    font-size: 1rem;
    transition: var(--transition);
}

.input-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(45, 90, 39, 0.1);
}

.password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--light-text);
    cursor: pointer;
    padding: 0.5rem;
    z-index: 2;
}

.password-toggle:hover {
    color: var(--primary-color);
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 0.9rem;
    color: var(--dark-text);
}

.checkbox-label input[type="checkbox"] {
    position: absolute;
    opacity: 0;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid var(--border-color);
    border-radius: 3px;
    margin-right: 0.5rem;
    position: relative;
    transition: var(--transition);
}

.checkbox-label input[type="checkbox"]:checked + .checkmark {
    background: var(--primary-color);
    border-color: var(--primary-color);
}

.checkbox-label input[type="checkbox"]:checked + .checkmark::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    color: white;
    font-size: 10px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.forgot-link {
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.9rem;
    transition: var(--transition);
}

.forgot-link:hover {
    text-decoration: underline;
}

.auth-submit {
    width: 100%;
    margin-bottom: 1rem;
}

.auth-divider {
    text-align: center;
    position: relative;
    margin: 2rem 0;
}

.auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: var(--border-color);
}

.auth-divider span {
    background: var(--white);
    padding: 0 1rem;
    color: var(--light-text);
    font-size: 0.9rem;
}

.social-login {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.btn-social {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    border: 2px solid var(--border-color);
    background: var(--white);
    color: var(--dark-text);
    border-radius: var(--border-radius);
    font-weight: 500;
    transition: var(--transition);
    text-decoration: none;
}

.btn-social:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.google-btn:hover {
    border-color: #db4437;
    color: #db4437;
}

.facebook-btn:hover {
    border-color: #3b5998;
    color: #3b5998;
}

.auth-footer {
    text-align: center;
}

.auth-footer p {
    color: var(--light-text);
}

.auth-footer a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.benefits-card {
    background: var(--white);
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--shadow);
}

.benefits-card h3 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    text-align: center;
}

.benefits-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.benefit-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.benefit-item i {
    color: var(--primary-color);
    font-size: 1.5rem;
    margin-top: 0.25rem;
    flex-shrink: 0;
}

.benefit-item h4 {
    margin-bottom: 0.25rem;
    color: var(--dark-text);
}

.benefit-item p {
    color: var(--light-text);
    font-size: 0.9rem;
    line-height: 1.4;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .auth-container {
        grid-template-columns: 1fr;
        padding: 0 1rem;
    }
    
    .auth-card {
        padding: 2rem 1.5rem;
    }
    
    .form-options {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .social-login {
        gap: 0.75rem;
    }
    
    .btn-social {
        font-size: 0.9rem;
    }
}

/* Loading state */
.auth-submit.loading {
    pointer-events: none;
    opacity: 0.7;
}

.auth-submit.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script>
// Password toggle functionality
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.querySelector('.password-toggle i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.classList.remove('fa-eye');
        toggleBtn.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleBtn.classList.remove('fa-eye-slash');
        toggleBtn.classList.add('fa-eye');
    }
}

// Form submission
document.addEventListener('DOMContentLoaded', function() {
    // Safe notification function that works even if Utils is not loaded
    function safeShowNotification(message, type) {
        if (typeof Utils !== 'undefined' && Utils.showNotification) {
            Utils.showNotification(message, type);
        } else {
            // Fallback notification
            alert(message);
        }
    }
    
    document.getElementById('login-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.querySelector('.auth-submit');
        const originalContent = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        // Get form data
        const formData = new FormData(this);
        const email = formData.get('email');
        const password = formData.get('password');
        const rememberMe = formData.get('remember_me');
        
        try {
            // Send login request to API
            const response = await fetch('/api/auth-login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: email,
                    password: password
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Store user session data if remember me is checked
                if (rememberMe) {
                    localStorage.setItem('doko_remember', 'true');
                }
                
                // Show success message
                safeShowNotification('Login successful! Welcome back to DOKO.', 'success');
                
                // Redirect based on user role
                setTimeout(() => {
                    if (result.redirect_url) {
                        window.location.href = result.redirect_url;
                    } else {
                        // Fallback redirect
                        const redirectUrl = sessionStorage.getItem('redirect_after_login') || 'index.php';
                        sessionStorage.removeItem('redirect_after_login');
                        window.location.href = redirectUrl;
                    }
                }, 1000);
                
            } else {
                // Show error message
                safeShowNotification(result.message || 'Login failed. Please try again.', 'error');
                
                // Restore button state
                submitBtn.innerHTML = originalContent;
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
            
        } catch (error) {
            console.error('Login error:', error);
            safeShowNotification('Network error. Please check your connection and try again.', 'error');
            
            // Restore button state
            submitBtn.innerHTML = originalContent;
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    });

    // Social login handlers
    document.querySelector('.google-btn').addEventListener('click', function() {
        alert('Google login integration would be implemented here');
    });

    document.querySelector('.facebook-btn').addEventListener('click', function() {
        alert('Facebook login integration would be implemented here');
    });
});

// Check if user is already logged in - this can run immediately
document.addEventListener('DOMContentLoaded', function() {
    const userData = localStorage.getItem('doko_user') || sessionStorage.getItem('doko_user');
    
    if (userData) {
        // User is already logged in, redirect to home
        window.location.href = 'index.php';
    }
});
</script>

<?php
// Include footer
include_footer();
?>

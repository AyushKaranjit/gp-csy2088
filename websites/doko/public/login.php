<?php
// Start session and include configuration
session_start();
require_once __DIR__ . '/../template/config.php';
require_once __DIR__ . '/../config/env.php'; // Load environment variables

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

<!-- OAuth SDK Scripts -->
<script src="https://accounts.google.com/gsi/client" async defer></script>

<!-- OAuth Configuration -->
<script>
const OAUTH_CONFIG = {
    google: {
        client_id: '<?php echo env('GOOGLE_CLIENT_ID', 'demo-google-client-id'); ?>',
        redirect_uri: '<?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']); ?>/api/oauth/google.php'
    }
};

// Debug: Log the configuration to verify it's loaded
console.log('OAUTH_CONFIG loaded:', OAUTH_CONFIG);
</script>

<!-- Breadcrumb -->
<?php include __DIR__ . '/../template/breadcrumb.php'; ?>

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
                        <button id="google-signin-button" class="btn btn-social google-btn" onclick="handleGoogleButtonClick()">
                            <i class="fab fa-google"></i>
                            Continue with Google
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

// Google OAuth Functions
function initGoogleLogin() {
    if (typeof google === 'undefined') {
        console.log('Google SDK not loaded yet, will initialize on button click');
        return;
    }

    // Check for valid client ID
    const clientId = OAUTH_CONFIG.google.client_id;
    if (!clientId || clientId === 'demo-google-client-id') {
        console.warn('Google Client ID not properly configured');
        return;
    }

    try {
        console.log('Initializing Google OAuth with Client ID:', clientId.substring(0, 20) + '...');
        
        google.accounts.id.initialize({
            client_id: clientId,
            callback: handleGoogleCallback,
            auto_select: false,
            cancel_on_tap_outside: true,
            itp_support: true,
            // Enable FedCM for future compatibility
            use_fedcm_for_prompt: true
        });

        window.googleOAuthInitialized = true;
        console.log('Google OAuth initialized successfully on page load');
    } catch (error) {
        console.error('Google OAuth initialization error:', error);
        // Don't show notification here, wait for user action
    }
}

function handleGoogleButtonClick() {
    if (typeof google === 'undefined') {
        safeShowNotification('Google login is not available. Please refresh the page and try again.', 'error');
        return;
    }

    try {
        // Check if Google OAuth is already initialized, if not initialize it
        if (!window.googleOAuthInitialized) {
            google.accounts.id.initialize({
                client_id: OAUTH_CONFIG.google.client_id,
                callback: handleGoogleCallback,
                auto_select: false,
                cancel_on_tap_outside: true,
                itp_support: true,
                use_fedcm_for_prompt: true
            });
            window.googleOAuthInitialized = true;
            console.log('Google OAuth initialized on button click');
        }

        // Directly show the Google Sign-In modal to avoid FedCM issues
        // This provides a consistent experience regardless of browser FedCM settings
        console.log('Showing Google Sign-In modal');
        showGoogleSignInModal();
        
    } catch (error) {
        console.error('Google login error:', error);
        safeShowNotification('Google login failed. Please try again.', 'error');
    }
}

function showGoogleSignInModal() {
    // Remove any existing modal
    const existing = document.getElementById('google-temp-signin');
    if (existing) existing.remove();
    
    const existingBackdrop = document.querySelector('.google-signin-backdrop');
    if (existingBackdrop) existingBackdrop.remove();

    // Use renderButton instead of prompt to avoid popup/FedCM issues
    const tempDiv = document.createElement('div');
    tempDiv.id = 'google-temp-signin';
    tempDiv.style.cssText = `
        position: fixed; 
        top: 50%; 
        left: 50%; 
        transform: translate(-50%, -50%); 
        z-index: 10000; 
        background: white; 
        padding: 30px; 
        border-radius: 12px; 
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        border: 1px solid #e1e5e9;
        min-width: 300px;
        text-align: center;
    `;
    
    // Add backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'google-signin-backdrop';
    backdrop.style.cssText = `
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(0,0,0,0.5); 
        z-index: 9999;
    `;
    
    // Close button
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = `
        position: absolute; 
        top: 15px; 
        right: 20px; 
        background: none; 
        border: none; 
        font-size: 28px; 
        cursor: pointer; 
        color: #666;
    `;
    
    // Title
    const title = document.createElement('h3');
    title.textContent = 'Sign in with Google';
    title.style.cssText = 'margin: 0 0 15px 0; color: #333; font-family: Arial, sans-serif;';
    
    // Info text
    const infoText = document.createElement('p');
    infoText.textContent = 'Click the Google button below to sign in';
    infoText.style.cssText = 'margin: 0 0 20px 0; color: #666; font-size: 14px; font-family: Arial, sans-serif;';
    
    // Button container
    const buttonContainer = document.createElement('div');
    buttonContainer.style.cssText = 'margin: 20px 0;';
    
    // Cleanup function
    const cleanup = () => {
        if (document.body.contains(backdrop)) document.body.removeChild(backdrop);
        if (document.body.contains(tempDiv)) document.body.removeChild(tempDiv);
    };
    
    closeBtn.onclick = cleanup;
    backdrop.onclick = cleanup;
    
    tempDiv.appendChild(closeBtn);
    tempDiv.appendChild(title);
    tempDiv.appendChild(infoText);
    tempDiv.appendChild(buttonContainer);
    
    document.body.appendChild(backdrop);
    document.body.appendChild(tempDiv);
    
    // Render the Google sign-in button (this should now work since initialize was called)
    try {
        google.accounts.id.renderButton(buttonContainer, {
            theme: 'filled_blue',
            size: 'large',
            text: 'signin_with',
            shape: 'rectangular',
            width: 250
        });
    } catch (renderError) {
        console.error('Error rendering Google button:', renderError);
        cleanup();
        safeShowNotification('Unable to load Google sign-in. Please try again.', 'error');
    }
}

function handleGoogleCallback(response) {
    console.log('Google OAuth callback received:', response);
    
    if (response.credential) {
        console.log('Credential length:', response.credential.length);
        console.log('Credential preview:', response.credential.substring(0, 50) + '...');
        
        // Send the credential to our backend
        fetch('api/oauth/google.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                credential: response.credential
            })
        })
        .then(res => {
            console.log('API response status:', res.status);
            console.log('API response headers:', res.headers);
            
            // Always try to parse JSON, regardless of status
            return res.json().then(data => {
                return { data, status: res.status };
            });
        })
        .then(result => {
            console.log('API response data:', result.data);
            
            if (result.status === 200 && result.data.success) {
                safeShowNotification('Google login successful! Welcome to DOKO.', 'success');
                setTimeout(() => {
                    window.location.href = result.data.redirect_url || 'index.php';
                }, 1000);
            } else {
                // Show the actual error message from the API
                const errorMessage = result.data.error || result.data.message || 'Google login failed.';
                console.error('API Error:', errorMessage);
                safeShowNotification('Login failed: ' + errorMessage, 'error');
            }
        })
        .catch(error => {
            console.error('Google login error:', error);
            safeShowNotification('Google login failed. Please try again.', 'error');
        });
    } else {
        safeShowNotification('Google login failed - no credential received.', 'error');
    }
}

// Safe notification function that works even if Utils is not loaded
function safeShowNotification(message, type) {
    if (typeof Utils !== 'undefined' && Utils.showNotification) {
        Utils.showNotification(message, type);
    } else {
        // Fallback notification
        alert(message);
    }
}

// Form submission
document.addEventListener('DOMContentLoaded', function() {
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
            const response = await fetch('api/users/auth-login.php', {
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

    // Social login handlers - initialize Google OAuth when page loads
    // Wait a bit for Google SDK to fully load
    setTimeout(() => {
        initGoogleLogin();
        
        // Add GSI event listeners to capture and log warnings
        if (typeof google !== 'undefined' && google.accounts && google.accounts.id) {
            // Override console methods to capture GSI warnings
            const originalWarn = console.warn;
            const originalLog = console.log;
            
            console.warn = function(...args) {
                if (args.length > 0 && typeof args[0] === 'string' && args[0].includes('GSI')) {
                    console.log('🔍 GSI Warning captured:', ...args);
                }
                originalWarn.apply(console, args);
            };
            
            console.log = function(...args) {
                if (args.length > 0 && typeof args[0] === 'string' && args[0].includes('[GSI_LOGGER]')) {
                    console.log('🔍 GSI Logger captured:', ...args);
                }
                originalLog.apply(console, args);
            };
        }
    }, 1000);
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
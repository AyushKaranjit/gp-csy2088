<?php
/**
 * DOKO E-Commerce Website - Registration Page
 *
 * This website was developed as part of an academic project for educational purposes.
 * All code was written by the student developer to demonstrate web development skills.
 *
 * @author Student Developer
 * @version 1.0
 * @date 2025
 */

// Start session and include configuration
// Set session cookie parameters for better browser compatibility
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => false,
    'samesite' => 'Lax'
]);
session_start();
require_once __DIR__ . '/../template/config.php';

// Page-specific variables
$page_title = page_title('Register');
$page_description = 'Create your DOKO account and start shopping for fresh groceries online.';
$current_page = 'register';

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Register', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);

// Server-side: if user already authenticated, redirect them to their dashboard
if (!empty($_SESSION['logged_in'])) {
    $role = $_SESSION['role'] ?? 'customer';
    if ($role === 'admin') {
        header('Location: admin/index.php');
        exit;
    } elseif ($role === 'manager') {
        header('Location: manager/index.php');
        exit;
    } else {
        header('Location: profile.php');
        exit;
    }
}
?>

<!-- Breadcrumb -->
<?php include __DIR__ . '/../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Join DOKO</h1>
                    <p>Create your account and start shopping</p>
                </div>

                <div class="auth-content">
                    <form id="register-form" class="auth-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="first_name" name="first_name" autocomplete="given-name" placeholder="First Name" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="last_name" name="last_name" autocomplete="family-name" placeholder="Last Name" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" autocomplete="email" placeholder="Email Address" required>
                            </div>
                            <div class="input-feedback" id="email-feedback"></div>
                        </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <div class="input-group">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" id="phone" name="phone" placeholder="98XXXXXXXX" pattern="[\+]?[0-9\s\-\(\)]*" inputmode="numeric" autocomplete="tel" oninput="formatPhoneNumber(this)">
                                </div>
                                <small class="form-help">Format: +977-98XXXXXXXX or 98XXXXXXXX</small>
                            </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Create a password" required autocomplete="new-password">
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="password-strength"></div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required autocomplete="new-password">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="input-feedback" id="confirm-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label for="city">City</label>
                            <div class="input-group">
                                <i class="fas fa-map-marker-alt"></i>
                                <select id="city" name="city" required>
                                    <option value="">Select your city</option>
                                    <option value="kathmandu">Kathmandu</option>
                                    <option value="lalitpur">Lalitpur</option>
                                    <option value="bhaktapur">Bhaktapur</option>
                                    <option value="pokhara">Pokhara</option>
                                    <option value="chitwan">Chitwan</option>
                                    <option value="butwal">Butwal</option>
                                    <option value="biratnagar">Biratnagar</option>
                                    <option value="dharan">Dharan</option>
                                </select>
                            </div>
                        </div>

                        <div class="terms-agreement">
                            <label class="checkbox-label">
                                <input type="checkbox" id="agree_terms" name="agree_terms" required autocomplete="off">
                                <span class="checkmark"></span>
                                I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
                            </label>
                        </div>

                        <div class="newsletter-signup">
                            <label class="checkbox-label">
                                <input type="checkbox" id="newsletter" name="newsletter" checked autocomplete="off">
                                <span class="checkmark"></span>
                                Send me offers, product updates, and promotional emails
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg auth-submit">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </form>

                    <div class="auth-divider">
                        <span>or</span>
                    </div>

                    <div class="social-login">
                        <button class="btn btn-social google-btn">
                            <i class="fab fa-google"></i>
                            Sign up with Google
                        </button>
                    </div>

                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>

            <!-- Why Join Section -->
            <div class="benefits-card">
                <h3>Why Join DOKO?</h3>
                <div class="benefits-list">
                    <div class="benefit-item">
                        <i class="fas fa-leaf"></i>
                        <div>
                            <h4>Fresh Groceries</h4>
                            <p>Get farm-fresh vegetables, fruits, and daily essentials delivered to your door</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-truck"></i>
                        <div>
                            <h4>Fast Delivery</h4>
                            <p>Same-day delivery available in Kathmandu Valley and quick delivery nationwide</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-tag"></i>
                        <div>
                            <h4>Great Prices</h4>
                            <p>Competitive prices with regular discounts and member-only offers</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <h4>Secure Shopping</h4>
                            <p>Safe and secure payment options with money-back guarantee</p>
                        </div>
                    </div>
                </div>

                <div class="special-offer">
                    <h4>🎉 Welcome Offer</h4>
                    <p>Get <strong>15% OFF</strong> on your first order with code <code>WELCOME15</code></p>
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

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
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

.input-group input,
.input-group select {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 3rem;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius);
    font-size: 1rem;
    transition: var(--transition);
}

.input-group input:focus,
.input-group select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(45, 90, 39, 0.1);
}

.input-group.valid input {
    border-color: var(--success-color);
}

.input-group.invalid input {
    border-color: var(--error-color);
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

.input-feedback {
    font-size: 0.85rem;
    margin-top: 0.5rem;
    min-height: 1.2em;
}

.input-feedback.success {
    color: var(--success-color);
}

.input-feedback.error {
    color: var(--error-color);
}

.password-strength {
    margin-top: 0.5rem;
}

.strength-meter {
    height: 4px;
    background: var(--border-color);
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.strength-bar {
    height: 100%;
    transition: var(--transition);
    border-radius: 2px;
}

.strength-bar.weak {
    width: 25%;
    background: var(--error-color);
}

.strength-bar.fair {
    width: 50%;
    background: orange;
}

.strength-bar.good {
    width: 75%;
    background: #3498db;
}

.strength-bar.strong {
    width: 100%;
    background: var(--success-color);
}

.strength-text {
    font-size: 0.85rem;
}

.terms-agreement,
.newsletter-signup {
    margin-bottom: 1.5rem;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    font-size: 0.9rem;
    color: var(--dark-text);
    line-height: 1.4;
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
    margin-right: 0.75rem;
    margin-top: 0.1rem;
    flex-shrink: 0;
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

.checkbox-label a {
    color: var(--primary-color);
    text-decoration: none;
}

.checkbox-label a:hover {
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
    margin-bottom: 2rem;
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

.special-offer {
    background: linear-gradient(135deg, var(--primary-color), #27ae60);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    color: white;
    text-align: center;
}

.special-offer h4 {
    margin-bottom: 0.5rem;
}

.special-offer code {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: bold;
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
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .social-login {
        gap: 0.75rem;
    }
    
    .btn-social {
        font-size: 0.9rem;
    }
}
</style>

<script>
// Password toggle functionality
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const toggleBtn = passwordInput.parentElement.querySelector('.password-toggle i');
    
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

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    const checks = {
        length: password.length >= 8,
        lowercase: /[a-z]/.test(password),
        uppercase: /[A-Z]/.test(password),
        numbers: /\d/.test(password),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };
    
    strength = Object.values(checks).filter(Boolean).length;
    
    const levels = ['', 'weak', 'fair', 'good', 'strong'];
    const texts = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    
    return {
        level: levels[strength] || 'weak',
        text: texts[strength] || 'Weak',
        score: strength
    };
}

// Email validation
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Phone validation for Nepal
function validatePhone(phone) {
    // Remove all spaces and special characters except + and digits
    const cleanPhone = phone.replace(/[^\d+]/g, '');
    // Check for valid Nepali phone number formats
    const phoneRegex = /^(\+977|977|0)?[0-9]{7,10}$/;
    return phoneRegex.test(cleanPhone) && cleanPhone.replace(/[^\d]/g, '').length >= 10;
}

// Real-time phone number formatting
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.startsWith('977')) {
        value = '+' + value;
    } else if (value.startsWith('0')) {
        value = '+977' + value.substring(1);
    } else if (!value.startsWith('+')) {
        value = '+977' + value;
    }

    // Format as +977-XXXXXXXXXX
    if (value.length >= 13) {
        value = value.substring(0, 13);
        input.value = value.replace(/(\+977)(\d{0,10})/, '$1-$2');
    } else {
        input.value = value;
    }
}

// Form validation and submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('register-form');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    const emailField = document.getElementById('email');
    
    // Password strength indicator
    passwordField.addEventListener('input', function() {
        const password = this.value;
        const strength = checkPasswordStrength(password);
        const strengthContainer = document.getElementById('password-strength');
        
        if (password.length > 0) {
            strengthContainer.innerHTML = `
                <div class="strength-meter">
                    <div class="strength-bar ${strength.level}"></div>
                </div>
                <div class="strength-text">Password strength: ${strength.text}</div>
            `;
        } else {
            strengthContainer.innerHTML = '';
        }
        
        // Update input styling
        const inputGroup = this.parentElement;
        if (strength.score >= 3) {
            inputGroup.classList.add('valid');
            inputGroup.classList.remove('invalid');
        } else if (password.length > 0) {
            inputGroup.classList.add('invalid');
            inputGroup.classList.remove('valid');
        } else {
            inputGroup.classList.remove('valid', 'invalid');
        }
    });
    
    // Confirm password validation
    confirmPasswordField.addEventListener('input', function() {
        const password = passwordField.value;
        const confirmPassword = this.value;
        const feedback = document.getElementById('confirm-feedback');
        const inputGroup = this.parentElement;
        
        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                feedback.innerHTML = '✓ Passwords match';
                feedback.className = 'input-feedback success';
                inputGroup.classList.add('valid');
                inputGroup.classList.remove('invalid');
            } else {
                feedback.innerHTML = '✗ Passwords do not match';
                feedback.className = 'input-feedback error';
                inputGroup.classList.add('invalid');
                inputGroup.classList.remove('valid');
            }
        } else {
            feedback.innerHTML = '';
            feedback.className = 'input-feedback';
            inputGroup.classList.remove('valid', 'invalid');
        }
    });
    
    // Email validation
    emailField.addEventListener('blur', function() {
        const email = this.value;
        const feedback = document.getElementById('email-feedback');
        const inputGroup = this.parentElement;
        
        if (email.length > 0) {
            if (validateEmail(email)) {
                feedback.innerHTML = '✓ Valid email address';
                feedback.className = 'input-feedback success';
                inputGroup.classList.add('valid');
                inputGroup.classList.remove('invalid');
            } else {
                feedback.innerHTML = '✗ Please enter a valid email address';
                feedback.className = 'input-feedback error';
                inputGroup.classList.add('invalid');
                inputGroup.classList.remove('valid');
            }
        } else {
            feedback.innerHTML = '';
            feedback.className = 'input-feedback';
            inputGroup.classList.remove('valid', 'invalid');
        }
    });
    
    // Form submission with final validations
    let registerSubmitting = false;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (registerSubmitting) return;
        registerSubmitting = true;

        const submitBtn = document.querySelector('.auth-submit');
        const originalContent = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
        submitBtn.disabled = true;

        const formData = new FormData(this);
        const email = (formData.get('email') || '').trim();
        const password = formData.get('password') || '';
        const confirm = formData.get('confirm_password') || '';
        const phone = (formData.get('phone') || '').trim();

        if (!validateEmail(email)) {
            alert('Please enter a valid email address.');
            submitBtn.innerHTML = originalContent; submitBtn.disabled = false; registerSubmitting = false; return;
        }
        if (password.length < 8) {
            alert('Password must be at least 8 characters long.');
            submitBtn.innerHTML = originalContent; submitBtn.disabled = false; registerSubmitting = false; return;
        }
        if (password !== confirm) {
            alert('Passwords do not match.');
            submitBtn.innerHTML = originalContent; submitBtn.disabled = false; registerSubmitting = false; return;
        }
        if (phone && !validatePhone(phone)) {
            alert('Please enter a valid Nepali phone number (e.g., +977-9851234567 or 9851234567).');
            submitBtn.innerHTML = originalContent; submitBtn.disabled = false; registerSubmitting = false; return;
        }

        // Submit registration data to API
        fetch('api/users/auth-register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                username: email, // Use email as username for simplicity
                email: email,
                password: password,
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                phone: phone
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Subscribe to newsletter if requested
                if (formData.get('newsletter')) {
                    fetch('/api/newsletter.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'include',
                        body: JSON.stringify({ email: email })
                    })
                    .then(response => response.json())
                    .then(newsletterResult => {
                        if (!newsletterResult.success) {
                            console.warn('Newsletter subscription failed:', newsletterResult.message);
                        }
                    })
                    .catch(error => {
                        console.warn('Newsletter subscription error:', error);
                    });
                }
                
                alert('Account created successfully! Welcome to DOKO family. Please login with your credentials.');
                window.location.href = 'login.php?registered=true';
            } else {
                alert('Registration failed: ' + (result.message || 'Unknown error'));
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
                registerSubmitting = false;
            }
        })
        .catch(error => {
            console.error('Registration error:', error);
            alert('Network error. Please try again.');
            submitBtn.innerHTML = originalContent;
            submitBtn.disabled = false;
            registerSubmitting = false;
        });
    });
    
    // Social login handlers
        // Reuse Google OAuth handlers from login page
        (function(){
            const googleBtn = document.querySelector('.google-btn');
            if (googleBtn) {
                googleBtn.addEventListener('click', function(evt){
                    // If login page functions are available, delegate to them
                    if (typeof handleGoogleButtonClick === 'function') {
                        handleGoogleButtonClick(evt);
                        return;
                    }
                    // Fallback: initialize Google login from this page
                    if (typeof initGoogleLogin === 'function') { initGoogleLogin(); handleGoogleButtonClick(evt); return; }
                    alert('Google signup is not available. Please use the login page to sign up with Google.');
                });
            }
        })();

        // Try to initialize Google OAuth if the login.js functions are available on this page
        try { if (typeof initGoogleLogin === 'function') { initGoogleLogin(); } } catch(e) {}
});
</script>

<?php
// Include footer
include_footer();
?>

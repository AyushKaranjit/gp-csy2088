<?php
// Set page variables
$page_title = 'Sign Up - DOKO Fresh Market';
$current_page = 'signup';
$additional_css = ['css/auth-new.css'];
$additional_js = ['auth.js'];

// Include header template
include_once '../template/header.php';
?>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <img src="https://img.icons8.com/fluency/48/shopping-bag.png" alt="DOKO">
                </div>
                <h1>Join DOKO</h1>
                <p>Create your account for fresh groceries delivered fast</p>
            </div>
            
            <form id="signupForm" class="auth-form" method="POST" action="signup_process.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="firstName" name="firstName" placeholder="First name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="lastName" name="lastName" placeholder="Last name" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="input-group">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-meter">
                            <div class="strength-fill"></div>
                        </div>
                        <p class="strength-text">Password strength</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="terms" required>
                        <span class="checkmark"></span>
                        I agree to the <a href="terms.php" class="terms-link">Terms of Service</a> and <a href="#" class="terms-link">Privacy Policy</a>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="newsletter">
                        <span class="checkmark"></span>
                        Subscribe to our newsletter for exclusive deals and updates
                    </label>
                </div>
                
                <button type="submit" class="auth-btn">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>
                
                <div class="auth-divider">
                    <span>or sign up with</span>
                </div>
                
                <div class="social-login">
                    <button type="button" class="social-btn google">
                        <i class="fab fa-google"></i>
                        Google
                    </button>
                    <button type="button" class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                        Facebook
                    </button>
                </div>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Sign in here</a></p>
                </div>
            </form>
        </div>
    </div>

<?php
// Include footer template
include_once '../template/footer.php';
?>

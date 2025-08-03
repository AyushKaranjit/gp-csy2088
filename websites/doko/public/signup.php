<?php
// Set page variables
$page_title = 'Sign Up - DOKO';
$current_page = 'signup';
$additional_css = ['css/auth.css'];

// Include header template
include_once '../template/header.php';
?>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Join DOKO</h2>
                <p>Create your account for fresh groceries</p>
            </div>
            
            <form class="auth-form" method="POST" action="signup_process.php">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="terms" required>
                        <span>I agree to the <a href="terms.php">Terms & Conditions</a></span>
                    </label>
                </div>
                
                <button type="submit" class="auth-btn">Create Account</button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>
        </div>
    </div>

<?php
// Include footer template
include_once '../template/footer.php';
?>

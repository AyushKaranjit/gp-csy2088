<?php
// Set page variables
$page_title = 'Login - DOKO Fresh Market';
$current_page = 'login';
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
                <h1>Welcome Back</h1>
                <p>Sign in to continue to DOKO Fresh Market</p>
            </div>
            
            <form id="loginForm" class="auth-form" method="POST" action="login_process.php">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="auth-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
                
                <div class="auth-divider">
                    <span>or continue with</span>
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
                    <p>Don't have an account? <a href="signup.php">Create account</a></p>
                </div>
            </form>
        </div>
    </div>

<?php
// Include footer template
include_once '../template/footer.php';
?>

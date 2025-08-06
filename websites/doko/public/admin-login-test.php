<?php
session_start();
require_once '../src/Controllers/AuthController.php';

$auth = new AuthController();

echo "<h1>DOKO Admin Login Test</h1>";

// Check if already logged in
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Already Logged In</h3>";
    echo "<p><strong>User:</strong> " . $user['email'] . "</p>";
    echo "<p><strong>Role:</strong> " . $user['role'] . "</p>";
    echo "<p><strong>Is Admin:</strong> " . ($auth->isAdmin() ? 'Yes' : 'No') . "</p>";
    echo "<p><a href='admin.php'>Go to Admin Panel</a></p>";
    echo "<p><a href='logout.php'>Logout</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p>❌ Not logged in</p>";
    echo "</div>";
}

// Handle login form submission
if ($_POST['email'] ?? false) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    echo "<h3>Attempting Login...</h3>";
    $result = $auth->login($email, $password);
    
    if ($result['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p>✅ Login successful!</p>";
        echo "<p><a href='admin.php'>Go to Admin Panel</a></p>";
        echo "</div>";
        
        // Show user details
        $user = $auth->getCurrentUser();
        echo "<h3>User Details:</h3>";
        echo "<pre>" . json_encode($user, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p>❌ Login failed: " . $result['message'] . "</p>";
        echo "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        form { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
        input[type="submit"] { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        input[type="submit"]:hover { background: #0056b3; }
        .test-info { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>

<div class="test-info">
    <h3>Default Admin Credentials</h3>
    <p><strong>Email:</strong> admin@doko.com</p>
    <p><strong>Password:</strong> password</p>
</div>

<form method="POST">
    <h3>Login Form</h3>
    <input type="email" name="email" placeholder="Email" value="admin@doko.com" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="submit" value="Login">
</form>

<div class="test-info">
    <h3>Quick Tests</h3>
    <p><a href="admin.php">Test Admin Panel Access</a></p>
    <p><a href="test-admin-api.php">Test Admin API</a></p>
    <p><a href="direct-admin-test.php">Direct API Test</a></p>
    <p><a href="index.php">Go to Homepage</a></p>
</div>

</body>
</html>

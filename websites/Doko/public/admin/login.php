<?php
// admin/login.php - Admin Login
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../functions/loadTemplate.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username && $password) {
        $stmt = $conn->prepare('SELECT id, password FROM admins WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->bind_result($admin_id, $hash);
        if ($stmt->fetch() && password_verify($password, $hash)) {
            $_SESSION['admin_id'] = $admin_id;
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
        $stmt->close();
    } else {
        $error = 'Please enter both username and password.';
    }
}

ob_start();
?>
<div class="section">
    <h2 class="section-title">Admin Login</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" class="form-container" style="max-width:400px;">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
<?php
$content = ob_get_clean();
loadTemplate('Admin Login', $content);

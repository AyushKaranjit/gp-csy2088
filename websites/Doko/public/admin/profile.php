<?php
// admin/profile.php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../functions/loadTemplate.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Fetch admin info
$stmt = $conn->prepare('SELECT username, email, created_at FROM admins WHERE id = ?');
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$stmt->bind_result($username, $email, $created_at);
$stmt->fetch();
$stmt->close();

// Handle profile update
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    if ($new_username && $new_email) {
        $stmt = $conn->prepare('UPDATE admins SET username = ?, email = ? WHERE id = ?');
        $stmt->bind_param('ssi', $new_username, $new_email, $admin_id);
        if ($stmt->execute()) {
            $success = 'Profile updated successfully.';
            $username = $new_username;
            $email = $new_email;
        } else {
            $error = 'Failed to update profile.';
        }
        $stmt->close();
    } else {
        $error = 'Username and email cannot be empty.';
    }
}

// Render profile page
ob_start();
?>
<div class="section">
    <h2 class="section-title">Admin Profile</h2>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" class="form-container" style="max-width:400px;">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="form-group">
            <label>Created At</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($created_at); ?>" disabled>
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>
<?php
$content = ob_get_clean();
loadTemplate('Admin Profile', $content);

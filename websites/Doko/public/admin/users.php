<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireLogin();
if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}
$pageTitle = 'Manage Users';
include '../../includes/header.php';
$db = (new Database())->getConnection();
// Search and pagination
$search = isset($_GET['search']) ? $db->real_escape_string($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$where = $search ? "WHERE username LIKE '%$search%' OR email LIKE '%$search%' OR full_name LIKE '%$search%'" : '';
// Handle delete and edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
            $id = (int)$_POST['id'];
            $db->query("DELETE FROM users WHERE id=$id");
        }
        if ($_POST['action'] === 'edit' && !empty($_POST['id'])) {
            $id = (int)$_POST['id'];
            $username = $db->real_escape_string($_POST['username']);
            $email = $db->real_escape_string($_POST['email']);
            $full_name = $db->real_escape_string($_POST['full_name']);
            $role = $db->real_escape_string($_POST['role']);
            $db->query("UPDATE users SET username='$username', email='$email', full_name='$full_name', role='$role' WHERE id=$id");
        }
    }
}
// Fetch users
$sql = "SELECT * FROM users $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$users = $db->query($sql);
$totalRows = $db->query("SELECT COUNT(*) as cnt FROM users $where")->fetch_assoc()['cnt'];
$totalPages = ceil($totalRows / $limit);
?>
<main class="container">
    <h2>Manage Users</h2>
    <form method="get" class="mb-2 p-1" style="background:#f8f9fa;border-radius:10px;display:flex;gap:1rem;align-items:center;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search users..." class="form-control" style="max-width:300px;">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
    <table class="table" style="width:100%;background:white;border-radius:10px;box-shadow:0 2px 8px #eee;">
        <thead>
            <tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Role</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php while($user = $users->fetch_assoc()): ?>
            <tr>
                <form method="post">
                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                    <td><?php echo $user['id']; ?></td>
                    <td><input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="form-control" required></td>
                    <td><input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required></td>
                    <td><input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="form-control" required></td>
                    <td>
                        <select name="role" class="form-control" required>
                            <option value="customer" <?php if($user['role']==='customer') echo 'selected'; ?>>Customer</option>
                            <option value="admin" <?php if($user['role']==='admin') echo 'selected'; ?>>Admin</option>
                        </select>
                    </td>
                    <td>
                        <button type="submit" name="action" value="edit" class="btn btn-primary">Save</button>
                        <button type="submit" name="action" value="delete" class="btn btn-error" onclick="return confirm('Delete this user?')">Delete</button>
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <div class="d-flex justify-center mt-2" style="gap:0.5rem;">
        <?php for($i=1;$i<=$totalPages;$i++): ?>
            <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>" class="btn<?php echo $i==$page?' btn-primary':' btn-info'; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
</main>
<?php include '../../includes/footer.php'; ?>

<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireLogin();
if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}
$pageTitle = 'Manage Categories';
include '../../includes/header.php';
$db = (new Database())->getConnection();

// Search and pagination
$search = isset($_GET['search']) ? $db->real_escape_string($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$where = $search ? "WHERE name LIKE '%$search%' OR description LIKE '%$search%'" : '';

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && !empty($_POST['name'])) {
            $name = $db->real_escape_string($_POST['name']);
            $desc = $db->real_escape_string($_POST['description']);
            $db->query("INSERT INTO categories (name, description) VALUES ('$name', '$desc')");
        }
        if ($_POST['action'] === 'edit' && !empty($_POST['id'])) {
            $id = (int)$_POST['id'];
            $name = $db->real_escape_string($_POST['name']);
            $desc = $db->real_escape_string($_POST['description']);
            $db->query("UPDATE categories SET name='$name', description='$desc' WHERE id=$id");
        }
        if ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
            $id = (int)$_POST['id'];
            $db->query("DELETE FROM categories WHERE id=$id");
        }
    }
}

// Fetch categories
$sql = "SELECT * FROM categories $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$categories = $db->query($sql);
$totalRows = $db->query("SELECT COUNT(*) as cnt FROM categories $where")->fetch_assoc()['cnt'];
$totalPages = ceil($totalRows / $limit);
?>
<main class="container">
    <h2>Manage Categories</h2>
    <form method="get" class="mb-2 p-1" style="background:#f8f9fa;border-radius:10px;display:flex;gap:1rem;align-items:center;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search categories..." class="form-control" style="max-width:300px;">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
    <form method="post" class="mb-2 p-1" style="background:#f8f9fa;border-radius:10px;">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label for="catname">Category Name</label>
            <input type="text" name="name" id="catname" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="catdesc">Description</label>
            <input type="text" name="description" id="catdesc" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Add Category</button>
    </form>
    <table class="table" style="width:100%;background:white;border-radius:10px;box-shadow:0 2px 8px #eee;">
        <thead>
            <tr><th>ID</th><th>Name</th><th>Description</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php while($cat = $categories->fetch_assoc()): ?>
            <tr>
                <form method="post">
                    <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                    <td><?php echo $cat['id']; ?></td>
                    <td><input type="text" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>" class="form-control" required></td>
                    <td><input type="text" name="description" value="<?php echo htmlspecialchars($cat['description']); ?>" class="form-control"></td>
                    <td>
                        <button type="submit" name="action" value="edit" class="btn btn-primary">Save</button>
                        <button type="submit" name="action" value="delete" class="btn btn-error" onclick="return confirm('Delete this category?')">Delete</button>
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

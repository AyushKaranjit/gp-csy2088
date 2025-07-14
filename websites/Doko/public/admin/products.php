<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireLogin();
if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}
$pageTitle = 'Manage Products';
include '../../includes/header.php';
$db = (new Database())->getConnection();
// Fetch categories for dropdown
$catRes = $db->query("SELECT id, name FROM categories ORDER BY name");
$categories = [];
while($row = $catRes->fetch_assoc()) $categories[] = $row;
// Search and pagination
$search = isset($_GET['search']) ? $db->real_escape_string($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$where = $search ? "WHERE p.name LIKE '%$search%' OR p.description LIKE '%$search%'" : '';
// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && !empty($_POST['name'])) {
            $name = $db->real_escape_string($_POST['name']);
            $desc = $db->real_escape_string($_POST['description']);
            $price = (float)$_POST['price'];
            $stock = (int)$_POST['stock_quantity'];
            $catid = (int)$_POST['category_id'];
            $db->query("INSERT INTO products (name, description, price, stock_quantity, category_id) VALUES ('$name', '$desc', $price, $stock, $catid)");
        }
        if ($_POST['action'] === 'edit' && !empty($_POST['id'])) {
            $id = (int)$_POST['id'];
            $name = $db->real_escape_string($_POST['name']);
            $desc = $db->real_escape_string($_POST['description']);
            $price = (float)$_POST['price'];
            $stock = (int)$_POST['stock_quantity'];
            $catid = (int)$_POST['category_id'];
            $db->query("UPDATE products SET name='$name', description='$desc', price=$price, stock_quantity=$stock, category_id=$catid WHERE id=$id");
        }
        if ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
            $id = (int)$_POST['id'];
            $db->query("DELETE FROM products WHERE id=$id");
        }
    }
}
// Fetch products
$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
$products = $db->query($sql);
$totalRows = $db->query("SELECT COUNT(*) as cnt FROM products p $where")->fetch_assoc()['cnt'];
$totalPages = ceil($totalRows / $limit);
?>
<main class="container">
    <h2>Manage Products</h2>
    <form method="get" class="mb-2 p-1" style="background:#f8f9fa;border-radius:10px;display:flex;gap:1rem;align-items:center;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products..." class="form-control" style="max-width:300px;">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
    <form method="post" class="mb-2 p-1" style="background:#f8f9fa;border-radius:10px;">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label for="prodname">Product Name</label>
            <input type="text" name="name" id="prodname" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="proddesc">Description</label>
            <input type="text" name="description" id="proddesc" class="form-control">
        </div>
        <div class="form-group">
            <label for="prodprice">Price</label>
            <input type="number" step="0.01" name="price" id="prodprice" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="prodstock">Stock Quantity</label>
            <input type="number" name="stock_quantity" id="prodstock" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="prodcat">Category</label>
            <select name="category_id" id="prodcat" class="form-control" required>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Product</button>
    </form>
    <table class="table" style="width:100%;background:white;border-radius:10px;box-shadow:0 2px 8px #eee;">
        <thead>
            <tr><th>ID</th><th>Name</th><th>Description</th><th>Price</th><th>Stock</th><th>Category</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php while($prod = $products->fetch_assoc()): ?>
            <tr>
                <form method="post">
                    <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                    <td><?php echo $prod['id']; ?></td>
                    <td><input type="text" name="name" value="<?php echo htmlspecialchars($prod['name']); ?>" class="form-control" required></td>
                    <td><input type="text" name="description" value="<?php echo htmlspecialchars($prod['description']); ?>" class="form-control"></td>
                    <td><input type="number" step="0.01" name="price" value="<?php echo $prod['price']; ?>" class="form-control" required></td>
                    <td><input type="number" name="stock_quantity" value="<?php echo $prod['stock_quantity']; ?>" class="form-control" required></td>
                    <td>
                        <select name="category_id" class="form-control" required>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php if($prod['category_id']==$cat['id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <button type="submit" name="action" value="edit" class="btn btn-primary">Save</button>
                        <button type="submit" name="action" value="delete" class="btn btn-error" onclick="return confirm('Delete this product?')">Delete</button>
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

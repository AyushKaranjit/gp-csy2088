<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
requireLogin();
if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}
$pageTitle = 'View All Carts';
include '../../includes/header.php';
$db = (new Database())->getConnection();
// Search and pagination
$search = isset($_GET['search']) ? $db->real_escape_string($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$where = $search ? "WHERE users.username LIKE '%$search%' OR products.name LIKE '%$search%'" : '';
// Fetch carts
$sql = "SELECT cart.*, users.username, products.name AS product_name FROM cart JOIN users ON cart.user_id = users.id JOIN products ON cart.product_id = products.id $where ORDER BY cart.created_at DESC LIMIT $limit OFFSET $offset";
$carts = $db->query($sql);
$totalRows = $db->query("SELECT COUNT(*) as cnt FROM cart JOIN users ON cart.user_id = users.id JOIN products ON cart.product_id = products.id $where")->fetch_assoc()['cnt'];
$totalPages = ceil($totalRows / $limit);
?>
<main class="container">
    <h2>View All User Carts</h2>
    <form method="get" class="mb-2 p-1" style="background:#f8f9fa;border-radius:10px;display:flex;gap:1rem;align-items:center;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search carts..." class="form-control" style="max-width:300px;">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
    <table class="table" style="width:100%;background:white;border-radius:10px;box-shadow:0 2px 8px #eee;">
        <thead>
            <tr><th>Cart ID</th><th>User</th><th>Product</th><th>Quantity</th><th>Added</th></tr>
        </thead>
        <tbody>
        <?php while($cart = $carts->fetch_assoc()): ?>
            <tr>
                <td><?php echo $cart['id']; ?></td>
                <td><?php echo htmlspecialchars($cart['username']); ?></td>
                <td><?php echo htmlspecialchars($cart['product_name']); ?></td>
                <td><?php echo $cart['quantity']; ?></td>
                <td><?php echo $cart['created_at']; ?></td>
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

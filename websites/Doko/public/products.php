<?php
session_start();
// src/products.php
require_once '../config/database.php';
require_once '../config/auth.php';

$database = new Database();
$db = $database->getConnection();

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active'";

$params = [];

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category_filter)) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_filter;
}

$query .= " ORDER BY p.name";

$stmt = $db->prepare($query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);

// Get categories for filter
$query = "SELECT * FROM categories ORDER BY name";
$result = $db->query($query);
$categories = $result->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Products - Graduation Grocery Store';
include '../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container">
            <h1 class="section-title">Our Products</h1>
            
            <!-- Search and Filter Section -->
            <div style="background: white; padding: 2rem; border-radius: 15px; margin-bottom: 3rem; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                    <div style="flex: 1; min-width: 200px;">
                        <input type="text" 
                               name="search" 
                               id="searchInput"
                               placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="form-control"
                               style="margin-bottom: 0;">
                    </div>
                    <div style="min-width: 200px;">
                        <select name="category" id="categoryFilter" class="form-control" style="margin-bottom: 0;">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo ($category_filter == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if (!empty($search) || !empty($category_filter)): ?>
                        <a href="products.php" class="btn" style="background-color: #6c757d;">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Products Grid -->
            <?php if (empty($products)): ?>
                <div style="text-align: center; padding: 3rem;">
                    <i class="fas fa-search" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <h3>No products found</h3>
                    <p>Try adjusting your search or filter criteria</p>
                    <a href="products.php" class="btn btn-primary">View All Products</a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
    <div class="product-card" data-category="<?php echo $product['category_id']; ?>">
        <div class="product-image">
            <i class="fas fa-shopping-basket"></i>
        </div>
        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
        <div class="product-price">Rs. <?php echo number_format($product['price'], 2); ?></div>
        <p style="color: #666; margin-bottom: 0.5rem;">
            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?>
        </p>
        <p style="color: <?php echo $product['stock_quantity'] > 0 ? '#28a745' : '#dc3545'; ?>; margin-bottom: 1rem;">
            <i class="fas fa-box"></i> 
            <?php echo $product['stock_quantity'] > 0 ? "In Stock ({$product['stock_quantity']})" : "Out of Stock"; ?>
        </p>
        <?php if (isLoggedIn()): ?>
            <?php if ($product['stock_quantity'] > 0): ?>
                <form method="POST" action="cart.php" style="margin-bottom:0;">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </form>
            <?php else: ?>
                <button class="btn" style="background-color: #6c757d; cursor: not-allowed;" disabled>
                    <i class="fas fa-times"></i> Out of Stock
                </button>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php" class="btn">Login to Buy</a>
        <?php endif; ?>
    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Results Summary -->
                <div style="text-align: center; margin-top: 3rem; padding: 2rem; background: white; border-radius: 15px;">
                    <p style="color: #666; font-size: 1.1rem;">
                        Showing <?php echo count($products); ?> product(s)
                        <?php if (!empty($search)): ?>
                            for "<?php echo htmlspecialchars($search); ?>"
                        <?php endif; ?>
                        <?php if (!empty($category_filter)): ?>
                            <?php 
                            $cat_name = array_filter($categories, function($cat) use ($category_filter) {
                                return $cat['id'] == $category_filter;
                            });
                            $cat_name = reset($cat_name);
                            ?>
                            in <?php echo htmlspecialchars($cat_name['name']); ?>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

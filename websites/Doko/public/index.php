<?php
session_start();
// src/index.php
require_once '../config/database.php';
require_once '../config/auth.php';

$database = new Database();
$db = $database->getConnection();

// Get featured products
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active' 
          ORDER BY p.created_at DESC LIMIT 6";
$result = $db->query($query);
$featured_products = $result->fetch_all(MYSQLI_ASSOC);

// Get categories
$query = "SELECT * FROM categories ORDER BY name";
$result = $db->query($query);
$categories = $result->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Home - Graduation Grocery Store';
include '../includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2>Welcome to Graduation Grocery</h2>
            <p>Your one-stop shop for fresh groceries, delivered to your doorstep. Quality products, unbeatable prices!</p>
            <a href="products.php" class="btn btn-primary">Shop Now</a>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <div class="product-grid">
                <?php foreach ($featured_products as $product): ?>
                    <div class="product-card" data-category="<?php echo $product['category_id']; ?>">
                        <div class="product-image">
                            <i class="fas fa-shopping-basket"></i>
                        </div>
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-price">Rs. <?php echo number_format($product['price'], 2); ?></div>
                        <p class="product-stock">Stock: <?php echo $product['stock_quantity']; ?> items</p>
                        <?php if (isLoggedIn()): ?>
                            <button class="btn add-to-cart" 
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-product-price="<?php echo $product['price']; ?>">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        <?php else: ?>
                            <a href="login.php" class="btn">Login to Buy</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="section" style="background-color: white;">
        <div class="container">
            <h2 class="section-title">Shop by Category</h2>
            <div class="product-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <i class="fas fa-list"></i>
                        </div>
                        <h3 class="product-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars($category['description']); ?></p>
                        <a href="products.php?category=<?php echo $category['id']; ?>" class="btn">Browse Products</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">About Graduation Grocery</h2>
            <div style="max-width: 800px; margin: 0 auto; text-align: center;">
                <p style="font-size: 1.2rem; margin-bottom: 2rem; color: #666;">
                    Graduation Grocery is a student project developed by a dedicated team of computer science students 
                    from NAMI College. Our mission is to provide fresh, quality groceries with convenient online shopping 
                    and reliable delivery service.
                </p>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-top: 3rem;">
                    <div style="text-align: center;">
                        <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3>Fast Delivery</h3>
                        <p>Quick and reliable delivery to your doorstep</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h3>Fresh Products</h3>
                        <p>Always fresh and quality guaranteed</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3>Secure Payment</h3>
                        <p>Safe and secure payment options</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

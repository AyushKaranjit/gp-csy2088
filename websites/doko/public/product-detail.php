<?php
/**
 * Product Detail Page
 * DOKO Grocery E-commerce
 */

// Start session safely - BEFORE any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../template/config.php';
require_once __DIR__ . '/../config/database.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

// Get product details from database
try {
    $db = Database::getInstance();
    
    // Get product with category info (using actual database schema)
    $query = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                     b.name as brand_name,
                     COALESCE(pi.image_url, 'uploads/products/default.svg') as main_image
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id
              LEFT JOIN brands b ON p.brand_id = b.brand_id
              LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
              WHERE p.product_id = ? AND p.status = 'active'";
    
    $stmt = $db->execute($query, [$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: products.php');
        exit;
    }
    
    // Set default values for missing fields
    $product['category_slug'] = $product['category_slug'] ?? 'general';
    $product['brand_name'] = $product['brand_name'] ?? 'Generic';
    $product['average_rating'] = $product['average_rating'] ?? 0.0;
    $product['rating_count'] = $product['rating_count'] ?? 0;
    $product['main_image'] = $product['main_image'] ?: 'uploads/products/default.svg';
    
    // For now, we'll skip product images and related products since they may not exist in current schema
    $product_images = [];
    
    // Get related products from same category
    $related_query = "SELECT * FROM products WHERE category_id = ? AND product_id != ? AND status = 'active' LIMIT 4";
    $related_stmt = $db->execute($related_query, [$product['category_id'], $product_id]);
    $related_products = $related_stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Product detail error: " . $e->getMessage());
    header('Location: products.php');
    exit;
}

// Set page variables
$page_title = htmlspecialchars($product['name']) . ' - DOKO';
$page_description = $product['short_description'] ?? $product['description'];
$current_page = 'products';

// Include header only once
require_once __DIR__ . '/../template/header.php';
?>

<main class="main-content">
    <!-- Breadcrumb -->
    <section class="breadcrumb-section">
        <div class="container">
            <nav class="breadcrumb">
                <a href="index.php">Home</a>
                <span>›</span>
                <a href="products.php">Products</a>
                <?php if ($product['category_name']): ?>
                <span>›</span>
                <a href="products.php?category=<?php echo $product['category_slug']; ?>">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a>
                <?php endif; ?>
                <span>›</span>
                <span class="current"><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>
        </div>
    </section>

    <!-- Product Detail -->
    <section class="product-detail-section">
        <div class="container">
            <div class="product-detail-grid">
                <!-- Product Images -->
                <div class="product-images">
                    <div class="main-image">
                        <?php 
                        $main_image = !empty($product_images) ? $product_images[0]['image_url'] : 'images/placeholder-product.jpg';
                        ?>
                        <img id="main-product-image" 
                             src="<?php echo htmlspecialchars($main_image); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    
                    <?php if (count($product_images) > 1): ?>
                    <div class="thumbnail-images">
                        <?php foreach ($product_images as $index => $image): ?>
                        <img class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                             src="<?php echo htmlspecialchars($image['image_url']); ?>"
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onclick="changeMainImage('<?php echo htmlspecialchars($image['image_url']); ?>')">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <div class="product-header">
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        <div class="product-meta">
                            <?php if ($product['brand_name']): ?>
                            <span class="brand">Brand: <strong><?php echo htmlspecialchars($product['brand_name']); ?></strong></span>
                            <?php endif; ?>
                            <span class="sku">SKU: <strong><?php echo htmlspecialchars($product['sku']); ?></strong></span>
                            <?php if ($product['average_rating'] > 0): ?>
                            <div class="rating">
                                <div class="stars">
                                    <?php
                                    $rating = $product['average_rating'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= floor($rating)) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($i <= $rating) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <span class="rating-text">(<?php echo number_format($rating, 1); ?>) - <?php echo $product['review_count']; ?> reviews</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="product-price">
                        <span class="current-price">Rs. <?php echo number_format($product['price'], 2); ?></span>
                        <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                            <span class="original-price">Rs. <?php echo number_format($product['original_price'], 2); ?></span>
                            <span class="discount-badge">
                                <?php echo round((($product['original_price'] - $product['price']) / $product['original_price']) * 100); ?>% OFF
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($product['short_description']): ?>
                    <div class="product-summary">
                        <p><?php echo htmlspecialchars($product['short_description']); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Stock Status -->
                    <div class="stock-info">
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="in-stock">
                                <i class="fas fa-check-circle"></i>
                                In Stock (<?php echo $product['stock_quantity']; ?> available)
                            </span>
                        <?php else: ?>
                            <span class="out-of-stock">
                                <i class="fas fa-times-circle"></i>
                                Out of Stock
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Add to Cart Form -->
                    <?php if ($product['stock_quantity'] > 0): ?>
                    <div class="add-to-cart-form">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <div class="quantity-input">
                                <button type="button" class="qty-btn minus" onclick="changeQuantity(<?php echo $product['product_id']; ?>, -1)">-</button>
                                <input type="number" id="qty-<?php echo $product['product_id']; ?>" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" autocomplete="off">
                                <button type="button" class="qty-btn plus" onclick="changeQuantity(<?php echo $product['product_id']; ?>, 1)">+</button>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                <button class="btn btn-primary btn-lg add-to-cart" 
                        onclick="addToCartWithQuantity(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>')">
                    <i class="fas fa-shopping-cart"></i>
                    Add to Cart
                </button>
                            <button class="btn btn-outline btn-lg add-to-wishlist" 
                                    onclick="toggleWishlist(<?php echo $product['product_id']; ?>)">
                                <i class="fas fa-heart"></i>
                                Add to Wishlist
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Product Details -->
                    <div class="product-details">
                        <div class="detail-item">
                            <strong>Unit:</strong> <?php echo htmlspecialchars($product['unit']); ?>
                        </div>
                        <?php if ($product['weight']): ?>
                        <div class="detail-item">
                            <strong>Weight:</strong> <?php echo $product['weight']; ?> <?php echo $product['weight_unit']; ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($product['category_name']): ?>
                        <div class="detail-item">
                            <strong>Category:</strong> 
                            <a href="products.php?category=<?php echo $product['category_slug']; ?>">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Product Description -->
            <?php if ($product['description']): ?>
            <div class="product-description">
                <h3>Description</h3>
                <div class="description-content">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Additional Information -->
            <div class="product-tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" onclick="showTab('info')">Product Information</button>
                    <?php if ($product['ingredients']): ?>
                    <button class="tab-btn" onclick="showTab('ingredients')">Ingredients</button>
                    <?php endif; ?>
                    <?php if ($product['nutritional_info']): ?>
                    <button class="tab-btn" onclick="showTab('nutrition')">Nutrition Facts</button>
                    <?php endif; ?>
                </div>

                <div class="tab-content">
                    <div id="info-tab" class="tab-pane active">
                        <div class="info-grid">
                            <?php if ($product['storage_instructions']): ?>
                            <div class="info-item">
                                <strong>Storage:</strong>
                                <p><?php echo htmlspecialchars($product['storage_instructions']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($product['allergen_info']): ?>
                            <div class="info-item">
                                <strong>Allergen Information:</strong>
                                <p><?php echo htmlspecialchars($product['allergen_info']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($product['expiry_days']): ?>
                            <div class="info-item">
                                <strong>Shelf Life:</strong>
                                <p><?php echo $product['expiry_days']; ?> days</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($product['ingredients']): ?>
                    <div id="ingredients-tab" class="tab-pane">
                        <p><?php echo nl2br(htmlspecialchars($product['ingredients'])); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($product['nutritional_info']): ?>
                    <div id="nutrition-tab" class="tab-pane">
                        <?php
                        $nutrition = json_decode($product['nutritional_info'], true);
                        if ($nutrition && is_array($nutrition)) {
                            echo '<div class="nutrition-table">';
                            foreach ($nutrition as $key => $value) {
                                echo '<div class="nutrition-row">';
                                echo '<span class="nutrient">' . htmlspecialchars(ucfirst($key)) . ':</span>';
                                echo '<span class="value">' . htmlspecialchars($value) . '</span>';
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Related Products -->
            <?php if (!empty($related_products)): ?>
            <div class="related-products">
                <h3>Related Products</h3>
                <div class="products-grid">
                    <?php foreach ($related_products as $related_product): ?>
                        <?php 
                        $product = $related_product; // For the template
                        include __DIR__ . '/../template/product-card.php'; 
                        ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<style>
.product-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    margin-bottom: 3rem;
}

.product-images .main-image {
    margin-bottom: 1rem;
}

.product-images .main-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: var(--border-radius);
    border: 1px solid var(--light-gray);
}

.thumbnail-images {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.thumbnail {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: var(--border-radius);
    border: 2px solid transparent;
    cursor: pointer;
    transition: var(--transition);
}

.thumbnail.active,
.thumbnail:hover {
    border-color: var(--primary);
}

.product-header {
    margin-bottom: 1.5rem;
}

.product-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.product-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    color: var(--gray);
    font-size: 0.9rem;
}

.product-price {
    margin-bottom: 1.5rem;
}

.current-price {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
}

.original-price {
    font-size: 1.2rem;
    color: var(--gray);
    text-decoration: line-through;
    margin-left: 0.5rem;
}

.discount-badge {
    background: var(--success);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius);
    font-size: 0.8rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

.stock-info {
    margin-bottom: 2rem;
}

.in-stock {
    color: var(--success);
    font-weight: 600;
}

.out-of-stock {
    color: var(--danger);
    font-weight: 600;
}

.quantity-selector {
    margin-bottom: 1.5rem;
}

.quantity-selector label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.quantity-input {
    display: flex;
    align-items: center;
    border: 1px solid var(--light-gray);
    border-radius: var(--border-radius);
    overflow: hidden;
    width: fit-content;
}

.qty-btn {
    background: var(--light-gray);
    border: none;
    padding: 0.75rem 1rem;
    cursor: pointer;
    font-size: 1.2rem;
    font-weight: 600;
    transition: var(--transition);
}

.qty-btn:hover {
    background: var(--primary);
    color: white;
}

.quantity-input input {
    border: none;
    padding: 0.75rem 1rem;
    text-align: center;
    width: 80px;
    font-size: 1rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.product-details {
    border-top: 1px solid var(--light-gray);
    padding-top: 1.5rem;
}

.detail-item {
    margin-bottom: 0.5rem;
}

.product-description {
    margin-bottom: 3rem;
}

.product-description h3 {
    margin-bottom: 1rem;
    color: var(--dark);
}

.description-content {
    color: var(--gray);
    line-height: 1.6;
}

.product-tabs {
    margin-bottom: 3rem;
}

.tab-buttons {
    display: flex;
    border-bottom: 1px solid var(--light-gray);
    margin-bottom: 1.5rem;
}

.tab-btn {
    background: none;
    border: none;
    padding: 1rem 1.5rem;
    cursor: pointer;
    font-weight: 600;
    color: var(--gray);
    border-bottom: 2px solid transparent;
    transition: var(--transition);
}

.tab-btn.active,
.tab-btn:hover {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

.nutrition-table {
    display: grid;
    gap: 0.5rem;
}

.nutrition-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--light-gray);
}

.related-products h3 {
    margin-bottom: 2rem;
    text-align: center;
}

@media (max-width: 768px) {
    .product-detail-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .tab-buttons {
        flex-wrap: wrap;
    }
}
</style>

<script>
function changeMainImage(src) {
    document.getElementById('main-product-image').src = src;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    event.target.classList.add('active');
}

function changeQuantity(delta) {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    const newValue = currentValue + delta;
    const min = parseInt(input.min);
    const max = parseInt(input.max);
    
    if (newValue >= min && newValue <= max) {
        input.value = newValue;
    }
}

function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
}

function addProductToCart(productId) {
    const quantity = parseInt(document.getElementById('quantity').value);
    ProductManager.addToCartWithQuantity(productId, quantity);
}

function toggleWishlist(productId) {
    ProductManager.toggleWishlist(productId);
}
</script>

<script src="js/product-actions.js?v=<?php echo time(); ?>"></script>
<script src="js/main.js?v=<?php echo time(); ?>"></script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>

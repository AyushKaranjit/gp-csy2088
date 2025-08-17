<?php
// Start session and include configuration
session_start();
require_once __DIR__ . '/../template/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../template/image-service.php';

// Get filter parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 10000;

// Page-specific variables
$page_title = page_title('Products');
if ($category_id && isset($product_categories[$category_id])) {
    $page_title = page_title($product_categories[$category_id]['name']);
}
$page_description = 'Browse our wide selection of fresh groceries, vegetables, fruits, dairy products, and daily essentials at DOKO.';
$current_page = 'products';

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Products', 'url' => 'products.php']
];

if ($category_id && isset($product_categories[$category_id])) {
    $breadcrumb_items[] = ['title' => $product_categories[$category_id]['name'], 'url' => ''];
}

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php include __DIR__ . '/../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <?php 
                if ($category_id && isset($product_categories[$category_id])) {
                    echo clean_output($product_categories[$category_id]['name']);
                } elseif ($search_query) {
                    echo 'Search Results for "' . clean_output($search_query) . '"';
                } else {
                    echo 'All Products';
                }
                ?>
            </h1>
            <p class="page-subtitle">Fresh, quality products delivered to your doorstep</p>
        </div>

        <div class="products-layout">
            <!-- Sidebar Filters -->
            <aside class="products-sidebar">
                <div class="filter-section">
                    <h3>Filter by Category</h3>
                    <ul class="category-filter">
                        <li class="<?php echo !$category_id ? 'active' : ''; ?>">
                            <a href="products.php">All Categories</a>
                        </li>
                        <?php foreach ($product_categories as $id => $category): ?>
                        <li class="<?php echo $category_id == $id ? 'active' : ''; ?>">
                            <a href="products.php?category=<?php echo $id; ?>">
                                <i class="<?php echo $category['icon']; ?>"></i>
                                <?php echo clean_output($category['name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="filter-section">
                    <h3>Price Range</h3>
                    <div class="price-filter">
                        <div class="price-inputs">
                            <input type="number" id="min-price" name="min-price" placeholder="Min" value="<?php echo $min_price; ?>" min="0" autocomplete="off">
                            <span>to</span>
                            <input type="number" id="max-price" name="max-price" placeholder="Max" value="<?php echo $max_price; ?>" min="0" autocomplete="off">
                        </div>
                        <button id="apply-price-filter" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                </div>

                <div class="filter-section">
                    <h3>Special Offers</h3>
                    <div class="offer-filters">
                        <label class="filter-checkbox">
                            <input type="checkbox" id="on_sale" name="on_sale" autocomplete="off"> On Sale
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" id="free_delivery" name="free_delivery" autocomplete="off"> Free Delivery
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="new_arrivals"> New Arrivals
                        </label>
                    </div>
                </div>
            </aside>

            <!-- Products Content -->
            <div class="products-content">
                <!-- Products Header -->
                <div class="products-header">
                    <div class="products-count">
                        <span id="products-count">Showing 24 products</span>
                    </div>
                    
                    <div class="products-controls">
                        <div class="view-toggle">
                            <button class="view-btn active" data-view="grid">
                                <i class="fas fa-th"></i>
                            </button>
                            <button class="view-btn" data-view="list">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                        
                        <div class="sort-dropdown">
                            <select id="sort-select" name="sort">
                                <option value="name" <?php echo $sort_by == 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
                                <option value="name_desc" <?php echo $sort_by == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                                <option value="price_asc" <?php echo $sort_by == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                                <option value="price_desc" <?php echo $sort_by == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                                <option value="rating" <?php echo $sort_by == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                                <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="products-grid" id="products-grid">
                    <?php
                    // DB-driven product retrieval
                    $filtered_products = [];
                    try {
                        $db = Database::getInstance();
                                            // Some pages include only config.php (not api/_bootstrap.php) so helper
                                            // functions might not be loaded. Provide a lightweight fallback.
                                            if (!function_exists('schema_products_pk')) {
                                                function schema_products_pk(): string { return 'product_id'; }
                                            }
                                            $pk = schema_products_pk();
                        $where = ["p.status = 'active'"];
                        $params = [];
                        if ($category_id) { $where[] = 'p.category_id = ?'; $params[] = $category_id; }
                        if ($search_query) { $where[] = '(p.name LIKE ? OR p.description LIKE ?)'; $params[] = '%'.$search_query.'%'; $params[] = '%'.$search_query.'%'; }
                        if ($min_price > 0) { $where[] = 'p.price >= ?'; $params[] = $min_price; }
                        if ($max_price > 0) { $where[] = 'p.price <= ?'; $params[] = $max_price; }
                        $whereSql = $where ? ('WHERE '.implode(' AND ',$where)) : '';
                        // Sorting
                        $order = 'p.name ASC';
                        switch ($sort_by) {
                            case 'name_desc': $order = 'p.name DESC'; break;
                            case 'price_asc': $order = 'p.price ASC'; break;
                            case 'price_desc': $order = 'p.price DESC'; break;
                            case 'rating': $order = 'p.rating DESC'; break; // if rating column exists
                            case 'newest': $order = 'p.created_at DESC'; break;
                        }
                        $sql = "SELECT p.$pk AS id, p.name, p.price, p.stock_quantity, p.status, p.created_at,
                                       COALESCE(pi.image_url, '') AS image,
                                       c.name AS category,
                                       p.description,
                                       p.original_price
                                FROM products p
                                LEFT JOIN product_images pi ON pi.product_id = p.$pk AND pi.is_primary = 1
                                LEFT JOIN categories c ON c.category_id = p.category_id
                                $whereSql
                                ORDER BY $order
                                LIMIT 120"; // safety limit
                        $stmt = $db->execute($sql, $params);
                        $rows = $stmt->fetchAll();
                        foreach ($rows as $r) {
                            $filtered_products[] = [
                                'id' => (int)$r['id'],
                                'name' => $r['name'],
                                'price' => (float)$r['price'],
                                'original_price' => isset($r['original_price']) ? (float)$r['original_price'] : null,
                                'image' => resolve_display_product_image($r['image'], $r['name']),
                                'category_id' => null, // category id optional
                                'category' => $r['category'] ?? 'General',
                                'rating' => isset($r['rating']) ? (float)$r['rating'] : 0,
                                'in_stock' => ($r['stock_quantity'] ?? 0) > 0 && strtolower($r['status']) === 'active',
                                'is_new' => false,
                                'description' => $r['description'] ?? '',
                                'stock_quantity' => (int)($r['stock_quantity'] ?? 0),
                                'status' => $r['status']
                            ];
                        }
                    } catch (Throwable $e) {
                        error_log('products.php query failed: '.$e->getMessage());
                    }

                    // Fallback: if DB empty, show a friendly message
                    if (!$filtered_products) {
                        echo '<p>No products found. Please add products in the admin panel.</p>';
                    }

                    // Manual PHP-side price range filtering if DB lacks conditions (already applied above though)
                    $filtered_products = array_filter($filtered_products, function($p) use ($min_price, $max_price) {
                        return $p['price'] >= $min_price && $p['price'] <= $max_price;
                    });

                    // Sorting already handled in SQL; optional fallback removed.

                    // Simple server-side pagination (slice the in-memory results)
                    $perPage = 12;
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    if ($page < 1) $page = 1;
                    $totalProducts = count($filtered_products);
                    $totalPages = $totalProducts > 0 ? (int)ceil($totalProducts / $perPage) : 1;
                    if ($page > $totalPages) $page = $totalPages;
                    $offset = ($page - 1) * $perPage;
                    $paged_products = array_slice(array_values($filtered_products), $offset, $perPage);

                    // Display products
                    if (empty($paged_products)): ?>
                        <div class="no-products">
                            <div class="no-products-content">
                                <i class="fas fa-search"></i>
                                <h3>No products found</h3>
                                <p>We couldn't find any products matching your criteria. Try adjusting your filters or search terms.</p>
                                <a href="products.php" class="btn btn-primary">View All Products</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($paged_products as $product): ?>
                            <?php include __DIR__ . '/../template/product-card.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalProducts > $perPage): ?>
                <div class="pagination-wrapper">
                    <nav class="pagination" aria-label="Products pagination">
                        <?php
                        // Build base query preserving filters
                        $baseQuery = $_GET;
                        // Previous button
                        $prevDisabled = $page <= 1;
                        $baseQuery['page'] = max(1, $page - 1);
                        $prevUrl = htmlspecialchars('products.php?' . http_build_query($baseQuery));
                        ?>
                        <a class="pagination-btn<?php echo $prevDisabled ? ' disabled' : ''; ?>" href="<?php echo $prevDisabled ? '#' : $prevUrl; ?>" aria-label="Previous page">
                            <i class="fas fa-chevron-left"></i>
                        </a>

                        <?php
                        // Render page links (limit to reasonable window)
                        $window = 5;
                        $startPage = max(1, $page - floor($window / 2));
                        $endPage = min($totalPages, $startPage + $window - 1);
                        if ($endPage - $startPage + 1 < $window) {
                            $startPage = max(1, $endPage - $window + 1);
                        }
                        for ($i = $startPage; $i <= $endPage; $i++):
                            $baseQuery['page'] = $i;
                            $url = htmlspecialchars('products.php?' . http_build_query($baseQuery));
                        ?>
                            <a class="pagination-btn<?php echo $i === $page ? ' active' : ''; ?>" href="<?php echo $url; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>

                        <?php
                        // Next button
                        $nextDisabled = $page >= $totalPages;
                        $baseQuery['page'] = min($totalPages, $page + 1);
                        $nextUrl = htmlspecialchars('products.php?' . http_build_query($baseQuery));
                        ?>
                        <a class="pagination-btn<?php echo $nextDisabled ? ' disabled' : ''; ?>" href="<?php echo $nextDisabled ? '#' : $nextUrl; ?>" aria-label="Next page">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Update products count -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productCards = document.querySelectorAll('.product-card');
    const productsCount = document.getElementById('products-count');
    
    if (productsCount) {
        productsCount.textContent = `Showing ${productCards.length} product${productCards.length !== 1 ? 's' : ''}`;
    }

    // Handle price filter
    document.getElementById('apply-price-filter').addEventListener('click', function() {
        const minPrice = document.getElementById('min-price').value;
        const maxPrice = document.getElementById('max-price').value;
        
        const url = new URL(window.location);
        if (minPrice) url.searchParams.set('min_price', minPrice);
        if (maxPrice) url.searchParams.set('max_price', maxPrice);
        
        window.location.href = url.toString();
    });

    // Handle sort change
    document.getElementById('sort-select').addEventListener('change', function() {
        const url = new URL(window.location);
        url.searchParams.set('sort', this.value);
        window.location.href = url.toString();
    });

    // Handle view toggle
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const grid = document.getElementById('products-grid');
            const view = this.dataset.view;
            
            grid.className = view === 'list' ? 'products-list' : 'products-grid';
        });
    });
});
</script>
<!-- Core product actions (wishlist, add to cart) -->
<!-- Main global helpers -->
<script src="js/main.js?v=<?php echo time(); ?>"></script>

<style>
.products-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.products-sidebar {
    background: var(--white);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    height: fit-content;
    box-shadow: var(--shadow);
}

.filter-section {
    margin-bottom: 2rem;
}

.filter-section h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.category-filter {
    list-style: none;
    padding: 0;
    margin: 0;
}

.category-filter li {
    margin-bottom: 0.5rem;
}

.category-filter a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    color: var(--dark-text);
    text-decoration: none;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.category-filter a:hover,
.category-filter .active a {
    background: var(--primary-color);
    color: white;
}

.price-filter {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.price-inputs {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.price-inputs input {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
}

.offer-filters {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.products-content {
    min-height: 500px;
}

.products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.products-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.view-toggle {
    display: flex;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.view-btn {
    padding: 0.5rem 1rem;
    border: none;
    background: white;
    color: var(--dark-text);
    cursor: pointer;
    transition: var(--transition);
}

.view-btn.active,
.view-btn:hover {
    background: var(--primary-color);
    color: white;
}

.sort-dropdown select {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    background: white;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}

.products-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.products-list .product-card {
    display: flex;
    max-height: 200px;
}

.products-list .product-image {
    width: 200px;
    flex-shrink: 0;
}

.products-list .product-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.no-products {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
}

.no-products-content i {
    font-size: 4rem;
    color: var(--light-text);
    margin-bottom: 1rem;
}

.pagination-wrapper {
    margin-top: 3rem;
    display: flex;
    justify-content: center;
}

.pagination {
    display: flex;
    gap: 0.5rem;
}

.pagination-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    background: white;
    color: var(--dark-text);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
}

.pagination-btn:hover,
.pagination-btn.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .products-layout {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .products-sidebar {
        order: 2;
    }
    
    .products-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .products-controls {
        justify-content: space-between;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .price-inputs {
        flex-direction: column;
    }
}
</style>

<?php
// Include footer
include_footer();
?>

<!-- Add global changeQuantity function for product-card.php -->
<script>
function changeQuantity(productId, delta) {
    var input = document.getElementById('qty-' + productId);
    if (!input) return;
    var min = parseInt(input.min) || 1;
    var max = parseInt(input.max) || 99;
    var value = parseInt(input.value) || min;
    var newValue = value + delta;
    if (newValue < min) newValue = min;
    if (newValue > max) newValue = max;
    input.value = newValue;
}
</script>

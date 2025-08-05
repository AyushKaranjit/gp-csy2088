<?php
// Start session and include configuration
session_start();
require_once '../template/config.php';
require_once 'config/database.php';

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
<?php include '../template/breadcrumb.php'; ?>

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
                            <input type="number" id="min-price" placeholder="Min" value="<?php echo $min_price; ?>" min="0">
                            <span>to</span>
                            <input type="number" id="max-price" placeholder="Max" value="<?php echo $max_price; ?>" min="0">
                        </div>
                        <button id="apply-price-filter" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                </div>

                <div class="filter-section">
                    <h3>Special Offers</h3>
                    <div class="offer-filters">
                        <label class="filter-checkbox">
                            <input type="checkbox" name="on_sale"> On Sale
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="free_delivery"> Free Delivery
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
                    // Sample products (in real application, fetch from database based on filters)
                    $all_products = [
                        [
                            'id' => 1,
                            'name' => 'Fresh Tomatoes (1kg)',
                            'price' => 85.00,
                            'original_price' => 95.00,
                            'image' => product_image('Fresh Tomatoes'),
                            'category_id' => 1,
                            'category' => 'Fresh Vegetables',
                            'rating' => 4.5,
                            'in_stock' => true,
                            'is_new' => false,
                            'description' => 'Fresh local tomatoes, handpicked daily from local farms'
                        ],
                        [
                            'id' => 2,
                            'name' => 'Royal Banana (1 dozen)',
                            'price' => 120.00,
                            'original_price' => null,
                            'image' => product_image('Royal Banana'),
                            'category_id' => 2,
                            'category' => 'Fresh Fruits',
                            'rating' => 4.8,
                            'in_stock' => true,
                            'is_new' => true,
                            'description' => 'Premium quality ripe bananas, sweet and nutritious'
                        ],
                        [
                            'id' => 3,
                            'name' => 'DDC Fresh Milk (1L)',
                            'price' => 85.00,
                            'original_price' => null,
                            'image' => product_image('DDC Fresh Milk'),
                            'category_id' => 3,
                            'category' => 'Dairy Products',
                            'rating' => 4.7,
                            'in_stock' => true,
                            'is_new' => false,
                            'description' => 'Pure pasteurized milk from DDC, rich in calcium and vitamins'
                        ],
                        [
                            'id' => 4,
                            'name' => 'Basmati Rice (5kg)',
                            'price' => 1250.00,
                            'original_price' => 1350.00,
                            'image' => product_image('Basmati Rice'),
                            'category_id' => 4,
                            'category' => 'Grains & Pulses',
                            'rating' => 4.6,
                            'in_stock' => true,
                            'is_new' => false,
                            'description' => 'Premium aged basmati rice, aromatic and long grain'
                        ],
                        [
                            'id' => 5,
                            'name' => 'Green Apples (1kg)',
                            'price' => 380.00,
                            'original_price' => null,
                            'image' => product_image('Green Apples'),
                            'category_id' => 2,
                            'category' => 'Fresh Fruits',
                            'rating' => 4.4,
                            'in_stock' => true,
                            'is_new' => true,
                            'description' => 'Crisp and juicy green apples imported from Kashmir'
                        ],
                        [
                            'id' => 6,
                            'name' => 'Everest Garam Masala (100g)',
                            'price' => 85.00,
                            'original_price' => 95.00,
                            'image' => product_image('Everest Garam Masala'),
                            'category_id' => 5,
                            'category' => 'Spices & Herbs',
                            'rating' => 4.9,
                            'in_stock' => true,
                            'is_new' => false,
                            'description' => 'Authentic Nepali garam masala blend for traditional cooking'
                        ],
                        [
                            'id' => 7,
                            'name' => 'Fresh Palungo (500g)',
                            'price' => 45.00,
                            'original_price' => null,
                            'image' => product_image('Fresh Palungo'),
                            'category_id' => 1,
                            'category' => 'Fresh Vegetables',
                            'rating' => 4.3,
                            'in_stock' => true,
                            'is_new' => true,
                            'description' => 'Fresh spinach leaves, rich in iron and vitamins'
                        ],
                        [
                            'id' => 8,
                            'name' => 'Juju Dhau (200ml)',
                            'price' => 95.00,
                            'original_price' => 110.00,
                            'image' => product_image('Juju Dhau'),
                            'category_id' => 3,
                            'category' => 'Dairy Products',
                            'rating' => 4.6,
                            'in_stock' => true,
                            'is_new' => false,
                            'description' => 'Traditional Bhaktapur yogurt, creamy and delicious'
                        ],
                        [
                            'id' => 9,
                            'name' => 'Red Onions (1kg)',
                            'price' => 65.00,
                            'original_price' => null,
                            'image' => product_image('Red Onions'),
                            'category_id' => 1,
                            'category' => 'Fresh Vegetables',
                            'rating' => 4.1,
                            'in_stock' => true,
                            'is_new' => false,
                            'description' => 'Fresh red onions, essential for Nepali cooking'
                        ],
                        [
                            'id' => 10,
                            'name' => 'Real Juice Orange (1L)',
                            'price' => 165.00,
                            'original_price' => null,
                            'image' => product_image('Real Juice Orange'),
                            'category_id' => 6,
                            'category' => 'Snacks & Beverages',
                            'rating' => 4.0,
                            'in_stock' => true,
                            'is_new' => true,
                            'description' => 'Pure orange juice with pulp, no artificial flavors'
                        ],
                        [
                            'id' => 11,
                            'name' => 'Bhatbhateni Brown Bread',
                            'price' => 75.00,
                            'original_price' => 85.00,
                            'image' => product_image('Bhatbhateni Brown Bread'),
                            'category_id' => 6,
                            'category' => 'Snacks & Beverages',
                            'rating' => 4.4,
                            'in_stock' => true,
                            'is_new' => false,
                            'description' => 'Healthy whole wheat brown bread, soft and nutritious'
                        ],
                        [
                            'id' => 12,
                            'name' => 'Mountain Honey (500g)',
                            'price' => 650.00,
                            'original_price' => null,
                            'image' => product_image('Mountain Honey'),
                            'category_id' => 6,
                            'category' => 'Snacks & Beverages',
                            'rating' => 4.8,
                            'in_stock' => true,
                            'is_new' => true,
                            'description' => 'Pure wildflower honey from Himalayan regions'
                        ]
                    ];

                    // Filter products based on criteria
                    $filtered_products = $all_products;

                    // Filter by category
                    if ($category_id) {
                        $filtered_products = array_filter($filtered_products, function($product) use ($category_id) {
                            return $product['category_id'] == $category_id;
                        });
                    }

                    // Filter by search query
                    if ($search_query) {
                        $filtered_products = array_filter($filtered_products, function($product) use ($search_query) {
                            return stripos($product['name'], $search_query) !== false || 
                                   stripos($product['description'], $search_query) !== false ||
                                   stripos($product['category'], $search_query) !== false;
                        });
                    }

                    // Filter by price range
                    $filtered_products = array_filter($filtered_products, function($product) use ($min_price, $max_price) {
                        return $product['price'] >= $min_price && $product['price'] <= $max_price;
                    });

                    // Sort products
                    switch ($sort_by) {
                        case 'name_desc':
                            usort($filtered_products, function($a, $b) {
                                return strcmp($b['name'], $a['name']);
                            });
                            break;
                        case 'price_asc':
                            usort($filtered_products, function($a, $b) {
                                return $a['price'] <=> $b['price'];
                            });
                            break;
                        case 'price_desc':
                            usort($filtered_products, function($a, $b) {
                                return $b['price'] <=> $a['price'];
                            });
                            break;
                        case 'rating':
                            usort($filtered_products, function($a, $b) {
                                return $b['rating'] <=> $a['rating'];
                            });
                            break;
                        case 'newest':
                            usort($filtered_products, function($a, $b) {
                                return ($b['is_new'] ?? 0) <=> ($a['is_new'] ?? 0);
                            });
                            break;
                        default: // name
                            usort($filtered_products, function($a, $b) {
                                return strcmp($a['name'], $b['name']);
                            });
                    }

                    // Display products
                    if (empty($filtered_products)): ?>
                        <div class="no-products">
                            <div class="no-products-content">
                                <i class="fas fa-search"></i>
                                <h3>No products found</h3>
                                <p>We couldn't find any products matching your criteria. Try adjusting your filters or search terms.</p>
                                <a href="products.php" class="btn btn-primary">View All Products</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($filtered_products as $product): ?>
                            <?php include '../template/product-card.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if (!empty($filtered_products) && count($filtered_products) > 12): ?>
                <div class="pagination-wrapper">
                    <nav class="pagination">
                        <button class="pagination-btn" disabled>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="pagination-btn active">1</button>
                        <button class="pagination-btn">2</button>
                        <button class="pagination-btn">3</button>
                        <button class="pagination-btn">
                            <i class="fas fa-chevron-right"></i>
                        </button>
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

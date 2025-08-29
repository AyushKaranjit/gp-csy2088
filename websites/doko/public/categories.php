<?php
// Start session and include configuration
session_start();
require_once __DIR__ . '/../template/config.php                <div class="subcategory-item">
                    <div class="subcategory-icon">🌾</div>
                    <h4>Pantry Staples</h4>
                    <a href="products.php?category_id=6">Shop Now</a>
                </div>/ Page-specific variables
$page_title = page_title('Product Categories');
$page_description = 'Browse our wide selection of fresh groceries by category. Find everything from vegetables to snacks at DOKO.';
$current_page = 'categories';

// Get categories from database
try {
    $db = Database::getInstance();
    $stmt = $db->execute("
        SELECT c.category_id, c.name, c.slug, c.description, COUNT(p.product_id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.category_id = p.category_id AND p.status = 'active'
        WHERE c.is_active = 1
        GROUP BY c.category_id, c.name, c.slug, c.description
        ORDER BY c.sort_order
    ");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
    error_log('Categories page error: ' . $e->getMessage());
}

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Categories', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php include __DIR__ . '/../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <!-- Categories Hero Section -->
    <section class="categories-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Shop by Category</h1>
                <p>Discover fresh, quality products organized for your convenience</p>
            </div>
        </div>
    </section>

    <!-- Categories Grid -->
    <section class="section">
        <div class="container">
            <div class="categories-grid">
                <?php if (empty($categories)): ?>
                    <div class="no-categories">
                        <p>No categories available at the moment. Please check back later.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card<?php echo $category['category_id'] <= 2 ? ' featured' : ''; ?>">
                            <div class="category-image">
                                <img src="<?php echo product_image($category['slug']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                <div class="category-overlay">
                                    <div class="category-info">
                                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                                        <p><?php echo htmlspecialchars(isset($category['description']) ? $category['description'] : 'Quality products for your daily needs'); ?></p>
                                        <div class="product-count"><?php echo htmlspecialchars($category['product_count']); ?> Products</div>
                                    </div>
                                </div>
                            </div>
                            <div class="category-footer">
                                <a href="products.php?category_id=<?php echo $category['category_id']; ?>" class="btn btn-primary">Shop Now</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Popular Categories -->
    <section class="section subcategories-section">
        <div class="container">
            <h2 class="section-title">Popular Categories</h2>
            <div class="subcategories-grid">
                <div class="subcategory-item">
                    <div class="subcategory-icon">🥕</div>
                    <h4>Fruits & Vegetables</h4>
                    <a href="products.php?category_id=1">Shop Now</a>
                </div>
                <div class="subcategory-item">
                    <div class="subcategory-icon">🥛</div>
                    <h4>Dairy Products</h4>
                    <a href="products.php?category_id=2">Shop Now</a>
                </div>
                <div class="subcategory-item">
                    <div class="subcategory-icon">🍞</div>
                    <h4>Bakery</h4>
                    <a href="products.php?category_id=3">Shop Now</a>
                </div>
                <div class="subcategory-item">
                    <div class="subcategory-icon">🧃</div>
                    <h4>Beverages</h4>
                    <a href="products.php?category_id=4">Shop Now</a>
                </div>
                <div class="subcategory-item">
                    <div class="subcategory-icon">🥩</div>
                    <h4>Meat & Seafood</h4>
                    <a href="products.php?category_id=5">Shop Now</a>
                </div>
                <div class="subcategory-item">
                    <div class="subcategory-icon">�</div>
                    <h4>Pantry Staples</h4>
                    <a href="products.php?category_id=6">Shop Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="section cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Can't Find What You're Looking For?</h2>
                <p>Browse all our products or use our search feature to find exactly what you need</p>
                <div class="cta-actions">
                    <a href="products.php" class="btn btn-primary">View All Products</a>
                    <a href="contact.php" class="btn btn-outline">Contact Us</a>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
.categories-hero {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: var(--white);
    padding: 4rem 0;
    text-align: center;
}

.categories-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.categories-hero p {
    font-size: 1.1rem;
    opacity: 0.9;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.category-card {
    background: var(--white);
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
    position: relative;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.category-card.featured {
    border: 2px solid var(--primary-color);
}

.category-card.featured::before {
    content: 'Featured';
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: var(--primary-color);
    color: var(--white);
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    z-index: 2;
}

.category-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.category-card:hover .category-image img {
    transform: scale(1.1);
}

.category-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,107,53,0.8), rgba(229,90,43,0.8));
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
}

.category-card:hover .category-overlay {
    opacity: 1;
}

.category-info {
    text-align: center;
    color: var(--white);
    padding: 1rem;
}

.category-info h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.category-info p {
    margin-bottom: 1rem;
    opacity: 0.9;
}

.product-count {
    background: rgba(255,255,255,0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.9rem;
    display: inline-block;
}

.category-footer {
    padding: 1.5rem;
    text-align: center;
}

.subcategories-section {
    background: var(--light-bg);
}

.subcategories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.subcategory-item {
    background: var(--white);
    padding: 2rem 1rem;
    border-radius: var(--border-radius);
    text-align: center;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.subcategory-item:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
}

.subcategory-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.subcategory-item h4 {
    margin-bottom: 1rem;
    color: var(--dark-text);
}

.subcategory-item a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
}

.subcategory-item a:hover {
    color: var(--accent-color);
}

.cta-section {
    background: var(--gradient-primary);
    color: var(--white);
    text-align: center;
}

.cta-content h2 {
    margin-bottom: 1rem;
}

.cta-content p {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.cta-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-actions .btn-outline {
    border-color: var(--white);
    color: var(--white);
}

.cta-actions .btn-outline:hover {
    background: var(--white);
    color: var(--primary-color);
}

@media (max-width: 768px) {
    .categories-hero h1 {
        font-size: 2rem;
    }
    
    .categories-grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .category-image {
        height: 200px;
    }
    
    .subcategories-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }
    
    .subcategory-item {
        padding: 1.5rem 0.75rem;
    }
    
    .cta-actions {
        flex-direction: column;
        align-items: center;
    }
}

@media (max-width: 480px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .subcategories-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add loading animation
    const categoryCards = document.querySelectorAll('.category-card');
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    categoryCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>

<?php
// Include footer
include_footer();
?>

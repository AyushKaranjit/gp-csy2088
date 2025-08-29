<?php
/**
 * DOKO E-Commerce Website - Special Offers Page
 *
 * This website was developed as part of an academic project for educational purposes.
 * All code was written by the student developer to demonstrate web development skills.
 *
 * @author Student Developer
 * @version 1.0
 * @date 2025
 */

// Start session and include configuration
session_start();
require_once __DIR__ . '/../template/config.php';
require_once __DIR__ . '/../config/database.php';

// Page-specific variables
$page_title = page_title('Special Offers');
$page_description = 'Amazing deals and special offers on fresh groceries at DOKO. Save money on your favorite products and discover new ones.';
$current_page = 'offers';

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Special Offers', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php include __DIR__ . '/../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <!-- Offers Hero Section -->
    <section class="offers-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Special Offers & Deals</h1>
                <p>Save big on your favorite groceries with our amazing deals and limited-time offers!</p>
            </div>
        </div>
    </section>

    <!-- Featured Offers Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Today's Best Deals</h2>
                <p class="section-subtitle">Limited time offers - grab them before they're gone!</p>
            </div>

            <div class="offers-grid">
                <?php
                $featured_offers = [
                    [
                        'id' => 1,
                        'title' => 'Fruits & Vegetables Sale',
                        'description' => 'Up to 30% off on all fresh fruits and vegetables',
                        'discount' => '30% OFF',
                        'image' => product_image('fruits-vegetables'),
                        'valid_until' => '2025-08-10',
                        'code' => 'VEGGIE30',
                        'min_order' => 500
                    ],
                    [
                        'id' => 2,
                        'title' => 'Dairy Products Special',
                        'description' => 'Buy 2 Get 1 Free on all dairy products',
                        'discount' => 'Buy 2 Get 1',
                        'image' => product_image('dairy-products'),
                        'valid_until' => '2025-08-15',
                        'code' => 'DAIRY321',
                        'min_order' => 300
                    ],
                    [
                        'id' => 3,
                        'title' => 'Free Delivery Weekend',
                        'description' => 'Free delivery on all orders this weekend',
                        'discount' => 'FREE DELIVERY',
                        'image' => product_image('Grocery Delivery'),
                        'valid_until' => '2025-08-06',
                        'code' => 'WEEKEND',
                        'min_order' => 0
                    ],
                    [
                        'id' => 4,
                        'title' => 'Pantry Staples Discount',
                        'description' => '20% off on rice orders above 10kg',
                        'discount' => '20% OFF',
                        'image' => product_image('Basmati Rice'),
                        'valid_until' => '2025-08-20',
                        'code' => 'RICE20',
                        'min_order' => 1000
                    ]
                ];

                foreach ($featured_offers as $offer): ?>
                <div class="offer-card featured">
                    <div class="offer-image">
                        <img src="<?php echo $offer['image']; ?>" alt="<?php echo clean_output($offer['title']); ?>" loading="lazy">
                        <div class="discount-badge"><?php echo $offer['discount']; ?></div>
                    </div>
                    <div class="offer-content">
                        <h3><?php echo clean_output($offer['title']); ?></h3>
                        <p><?php echo clean_output($offer['description']); ?></p>
                        <div class="offer-details">
                            <div class="offer-code">
                                <span class="label">Code:</span>
                                <span class="code"><?php echo $offer['code']; ?></span>
                            </div>
                            <div class="offer-validity">
                                <span class="label">Valid until:</span>
                                <span class="date"><?php echo date('M d, Y', strtotime($offer['valid_until'])); ?></span>
                            </div>
                            <?php if ($offer['min_order'] > 0): ?>
                            <div class="min-order">
                                <span class="label">Min order:</span>
                                <span class="amount">Rs. <?php echo number_format($offer['min_order']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="offer-actions">
                            <button class="btn btn-primary copy-code" data-code="<?php echo $offer['code']; ?>">
                                <i class="fas fa-copy"></i> Copy Code
                            </button>
                            <a href="products.php?offer=<?php echo $offer['id']; ?>" class="btn btn-outline">Shop Now</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Category Offers Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Category Wise Offers</h2>
                <p class="section-subtitle">Special discounts across different product categories</p>
            </div>

            <div class="category-offers-grid">
                <?php
                $category_offers = [
                    ['category' => 'Fruits & Vegetables', 'discount' => '25% OFF', 'code' => 'FRUIT25', 'url' => 'products.php?category_id=1'],
                    ['category' => 'Spices', 'discount' => '15% OFF', 'code' => 'SPICE15', 'url' => 'products.php?category_id=7'],
                    ['category' => 'Pantry Staples', 'discount' => '10% OFF', 'code' => 'GRAIN10', 'url' => 'products.php?category_id=6'],
                    ['category' => 'Snacks', 'discount' => 'Buy 3 Get 1', 'code' => 'SNACK31', 'url' => 'products.php?category_id=8']
                ];

                foreach ($category_offers as $offer): ?>
                <div class="category-offer-card">
                    <div class="category-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <h4><?php echo $offer['category']; ?></h4>
                    <div class="discount"><?php echo $offer['discount']; ?></div>
                    <div class="offer-code">Code: <strong><?php echo $offer['code']; ?></strong></div>
                    <a href="<?php echo $offer['url']; ?>" class="btn btn-accent btn-sm">Shop Category</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- How to Use Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">How to Use Offer Codes</h2>
                <p class="section-subtitle">Follow these simple steps to apply your discount codes</p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Copy Code</h3>
                        <p>Click on the "Copy Code" button to copy the offer code to your clipboard.</p>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Shop Products</h3>
                        <p>Add your favorite products to the cart and proceed to checkout.</p>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Apply Code</h3>
                        <p>Paste the code in the "Promo Code" field during checkout.</p>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Save Money</h3>
                        <p>Enjoy your discount and get fresh groceries at great prices!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
.offers-hero {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: var(--white);
    padding: 3rem 0;
    text-align: center;
}

.offers-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.offers-hero p {
    font-size: 1.2rem;
    opacity: 0.9;
}

.offers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.offer-card {
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
}

.offer-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.offer-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.offer-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.discount-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: var(--accent-color);
    color: var(--white);
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: bold;
    font-size: 0.9rem;
}

.offer-content {
    padding: 1.5rem;
}

.offer-content h3 {
    margin-bottom: 0.5rem;
    color: var(--dark-text);
}

.offer-content p {
    color: var(--light-text);
    margin-bottom: 1rem;
}

.offer-details {
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

.offer-details > div {
    margin-bottom: 0.5rem;
}

.offer-details .label {
    color: var(--light-text);
    margin-right: 0.5rem;
}

.offer-details .code {
    background: var(--light-bg);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: monospace;
    font-weight: bold;
}

.offer-actions {
    display: flex;
    gap: 1rem;
}

.copy-code {
    flex: 1;
}

.category-offers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.category-offer-card {
    background: var(--white);
    padding: 2rem 1.5rem;
    border-radius: var(--border-radius);
    text-align: center;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.category-offer-card:hover {
    transform: translateY(-3px);
}

.category-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: var(--white);
    font-size: 1.5rem;
}

.category-offer-card h4 {
    margin-bottom: 0.5rem;
    color: var(--dark-text);
}

.category-offer-card .discount {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--accent-color);
    margin-bottom: 0.5rem;
}

.category-offer-card .offer-code {
    font-size: 0.9rem;
    color: var(--light-text);
    margin-bottom: 1rem;
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.step-card {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.step-number {
    width: 50px;
    height: 50px;
    background: var(--primary-color);
    color: var(--white);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: bold;
    flex-shrink: 0;
}

.step-content h3 {
    margin-bottom: 0.5rem;
    color: var(--dark-text);
}

.step-content p {
    color: var(--light-text);
    font-size: 0.95rem;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .offers-hero h1 {
        font-size: 2rem;
    }
    
    .offers-grid {
        grid-template-columns: 1fr;
    }
    
    .offer-actions {
        flex-direction: column;
    }
    
    .category-offers-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .step-card {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy code functionality
    document.querySelectorAll('.copy-code').forEach(button => {
        button.addEventListener('click', function() {
            const code = this.getAttribute('data-code');
            
            // Copy to clipboard
            navigator.clipboard.writeText(code).then(() => {
                // Show success message
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i> Copied!';
                this.style.background = 'var(--success-color)';
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.style.background = '';
                }, 2000);
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = code;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i> Copied!';
                this.style.background = 'var(--success-color)';
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.style.background = '';
                }, 2000);
            });
        });
    });
});
</script>

<?php
// Include footer
include_footer();
?>

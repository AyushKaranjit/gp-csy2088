<?php
// Clean rebuilt index removing duplicated nested sections
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__.'/../template/config.php';
require_once __DIR__.'/../config/database.php';
$page_title = page_title('Home');
$page_description = 'Fresh groceries delivered to your doorstep across Nepal. Quality products, competitive prices, and reliable service at DOKO.';
$current_page = 'home';
include_header($page_title, $page_description, $current_page);
?>

<style>
.hero-with-slider { position: relative; overflow:hidden; }
.hero-with-slider .hero-background-slider { position:absolute; inset:0; width:100%; height:100%; z-index:0; }
.hero-with-slider .hero-background-slider .hero-slide-track { position:absolute; inset:0; }
.hero-with-slider .hero-background-slider .hero-slide { position:absolute; inset:0; background: #1f1f1f center/cover no-repeat; opacity:0; transition:opacity 1.2s ease, transform 7s linear; will-change:opacity,transform; }
.hero-with-slider .hero-background-slider .hero-slide::before { content:""; position:absolute; inset:0; background: var(--gradient-overlay, linear-gradient(135deg,rgba(20,45,25,0.65),rgba(35,70,40,0.55))); }
.hero-with-slider .hero-background-slider .hero-slide { background-image: var(--bg-url); transform: scale(1.05); }
.hero-with-slider .hero-background-slider .hero-slide.active { opacity:1; transform: scale(1); z-index:1; }
.hero-with-slider .hero-background-slider .hero-slide-track.scroll-mode { display:flex; width:calc(200%); animation:heroScroll 40s linear infinite; }
.hero-with-slider .hero-background-slider .hero-slide-track.scroll-mode .hero-slide { position:relative; flex:0 0 20%; min-width:20%; opacity:1; transform:scale(1); transition:none; }
@keyframes heroScroll { 0% { transform:translateX(0); } 100% { transform:translateX(-50%); } }
.hero-with-slider .hero-overlay { position:absolute; inset:0; background:linear-gradient(180deg,rgba(0,0,0,0.15),rgba(0,0,0,0.55)); pointer-events:none; }
.hero-with-slider .hero-content { position:relative; z-index:2; padding:4.5rem 0 4rem; }
@media (max-width:768px){ .hero-with-slider .hero-content{ padding:3rem 0 3rem; } }
/* Button styling adjustments for contrast */
.hero-with-slider .hero-buttons .btn-outline { background:rgba(255,255,255,0.1); backdrop-filter: blur(4px); }
.hero-with-slider .hero-buttons .btn-outline:hover { background:rgba(255,255,255,0.2); }
</style>

<!-- Hero Section -->
<section class="hero hero-with-slider">
    <div class="hero-background-slider"><div class="hero-slide-track" id="hero-slide-track"></div><div class="hero-overlay"></div></div>
    <div class="container">
        <div class="hero-content"><div class="hero-text">
            <h1>Fresh Groceries <br><span class="highlight">Delivered to Your Door</span></h1>
            <p>Nepal's most trusted online grocery store. Farm-fresh vegetables, fruits, dairy and essentials delivered with guaranteed quality.</p>
            <div class="hero-buttons">
                <a href="products.php" class="btn btn-primary btn-lg">Shop Now</a>
                <a href="about.php" class="btn btn-outline btn-lg">Learn More</a>
            </div>
            <div class="hero-features">
                <div class="feature"><i class="fas fa-truck"></i><span>Free Delivery over Rs. 1000</span></div>
                <div class="feature"><i class="fas fa-clock"></i><span>Same Day Delivery</span></div>
                <div class="feature"><i class="fas fa-leaf"></i><span>100% Fresh & Organic</span></div>
            </div>
        </div></div>
    </div>
</section>

<!-- Categories Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Shop by Category</h2>
            <p class="section-subtitle">Discover fresh, premium quality products across all categories</p>
        </div>
        <div class="categories-grid">
            <?php 
            require_once __DIR__ . '/../template/image-service.php';
            $category_images = [];
            foreach($product_categories as $cid=>$cinfo){ $category_images[$cid] = getCategoryImage($cid); }
            ?>
            <?php foreach ($product_categories as $id => $category): ?>
                <div class="category-card">
                    <a href="products.php?category=<?php echo $id; ?>" class="category-link">
                        <div class="category-image">
                            <img src="<?php echo $category_images[$id] ?? 'uploads/default-product.jpg'; ?>" alt="<?php echo clean_output($category['name']); ?>" loading="lazy" onerror="handleImageError(this);">
                            <div class="category-overlay"><i class="<?php echo $category['icon']; ?>"></i></div>
                        </div>
                        <div class="category-info">
                            <h3><?php echo clean_output($category['name']); ?></h3>
                            <p>Fresh & Premium Quality</p>
                            <span class="shop-now">Shop Now <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="section bg-light">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Featured Products</h2>
            <p class="section-subtitle">Handpicked fresh products just for you</p>
        </div>
        <div class="products-grid">
            <?php
            $featured_products = [];
            try {
                $db = Database::getInstance();
                $sql = "SELECT p.product_id AS id, p.name, p.price, NULL AS original_price, COALESCE(pi.image_url, '') AS image, c.name AS category, 4.5 AS rating, (p.stock_quantity > 0) AS in_stock FROM products p LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.status='active' ORDER BY p.created_at DESC LIMIT 8";
                $stmt = $db->execute($sql);
                $featured_products = $stmt->fetchAll();
            } catch (Exception $e) { error_log('Featured products query failed: '.$e->getMessage()); }
            if (!$featured_products) { $featured_products = [[ 'id'=>0,'name'=>'Sample Product','price'=>100.00,'original_price'=>null,'image'=>'uploads/default-product.jpg','category'=>'General','rating'=>4.5,'in_stock'=>false ]]; }
            foreach ($featured_products as $p) {
                $pid = (int)$p['id'];
                $pnameAttr = htmlspecialchars($p['name'], ENT_QUOTES);
                $pname = clean_output($p['name']);
                // Prefer mapped external image when DB image missing or default
                $img = htmlspecialchars(resolve_display_product_image($p['image'],$p['name']));
                $cat = clean_output($p['category']);
                $rating = (float)$p['rating'];
                $price = (float)$p['price'];
                $orig = $p['original_price'];
                $inStock = !empty($p['in_stock']);
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo $img; ?>" alt="<?php echo $pname; ?>" loading="lazy" onerror="handleImageError(this)">
                        <?php if ($orig): ?><div class="product-badge">Sale</div><?php endif; ?>
                        <div class="product-actions">
                            <button class="btn-icon btn-wishlist" onclick="toggleWishlist(<?php echo $pid; ?>)" data-product-id="<?php echo $pid; ?>" title="Add to Wishlist"><i class="fa fa-heart-o"></i></button>
                            <button class="btn-icon btn-quick-view" title="Quick View"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?php echo $cat; ?></div>
                        <h3 class="product-name"><a href="product-detail.php?id=<?php echo $pid; ?>"><?php echo $pname; ?></a></h3>
                        <div class="product-rating"><div class="stars">
                            <?php for($i=1;$i<=5;$i++){ if($i<=floor($rating)) echo '<i class=\'fas fa-star\'></i>'; elseif($i<=$rating) echo '<i class=\'fas fa-star-half-alt\'></i>'; else echo '<i class=\'far fa-star\'></i>'; } ?>
                        </div><span class="rating-text">(<?php echo $rating; ?>)</span></div>
                        <div class="product-price"><span class="current-price"><?php echo format_price($price); ?></span><?php if($orig): ?><span class="original-price"><?php echo format_price($orig); ?></span><?php endif; ?></div>
                        <div class="product-actions">
                            <div class="quantity-selector-mini">
                                <button type="button" class="qty-btn-mini minus" onclick="changeQuantity(<?php echo $pid; ?>,-1)">-</button>
                                <input type="number" id="qty-<?php echo $pid; ?>" name="qty-<?php echo $pid; ?>" class="qty-input-mini" value="1" min="1" max="99" autocomplete="off">
                                <button type="button" class="qty-btn-mini plus" onclick="changeQuantity(<?php echo $pid; ?>,1)">+</button>
                            </div>
                            <button class="btn btn-primary add-to-cart flex-grow" onclick="addToCartWithQuantity(<?php echo $pid; ?>,'<?php echo $pnameAttr; ?>')" data-product-id="<?php echo $pid; ?>" data-product-name="<?php echo $pnameAttr; ?>" data-product-price="<?php echo $price; ?>" <?php if(!$inStock) echo 'disabled title=\'Out of stock\''; ?>>
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="section-footer"><a href="products.php" class="btn btn-outline btn-lg">View All Products</a></div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="section bg-light">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Why Choose DOKO?</h2>
            <p class="section-subtitle">We're committed to bringing you the best grocery shopping experience</p>
        </div>
        
        <div class="features-grid">
            <?php
            $features = [
                [
                    'icon' => 'fas fa-leaf',
                    'title' => 'Fresh & Organic',
                    'description' => 'Directly sourced from local farms ensuring maximum freshness and quality.'
                ],
                [
                    'icon' => 'fas fa-truck',
                    'title' => 'Fast Delivery',
                    'description' => 'Same-day delivery available with our efficient logistics network.'
                ],
                [
                    'icon' => 'fas fa-money-bill-wave',
                    'title' => 'Best Prices',
                    'description' => 'Competitive pricing with regular discounts and special offers.'
                ],
                [
                    'icon' => 'fas fa-headset',
                    'title' => '24/7 Support',
                    'description' => 'Round-the-clock customer service for all your needs and queries.'
                ]
            ];

            foreach ($features as $feature): ?>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="<?php echo $feature['icon']; ?>"></i>
                </div>
                <h3><?php echo clean_output($feature['title']); ?></h3>
                <p><?php echo clean_output($feature['description']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">What Our Customers Say</h2>
            <p class="section-subtitle">Real feedback from our valued customers</p>
        </div>
        
        <div class="testimonials-grid">
            <?php
            $testimonials = [
                [
                    'name' => 'Sunita Maharjan',
                    'location' => 'Lalitpur',
                    'rating' => 5,
                    'comment' => 'DOKO has made grocery shopping so convenient! Fresh products delivered right to my doorstep. Highly recommended!',
                    'image' => 'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&h=150&q=80'
                ],
                [
                    'name' => 'Ramesh Adhikari',
                    'location' => 'Kathmandu',
                    'rating' => 5,
                    'comment' => 'Amazing service and quality products. The vegetables are always fresh and the delivery is always on time.',
                    'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&h=150&q=80'
                ],
                [
                    'name' => 'Maya Shrestha',
                    'location' => 'Bhaktapur',
                    'rating' => 4,
                    'comment' => 'Great variety of products and competitive prices. DOKO has become my go-to for all grocery needs.',
                    'image' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&h=150&q=80'
                ]
            ];

                        foreach ($testimonials as $testimonial): ?>
                        <div class="testimonial-card">
                                <div class="testimonial-header">
                                        <div class="testimonial-avatar">
                                                <img src="<?php echo $testimonial['image']; ?>" alt="<?php echo clean_output($testimonial['name']); ?>" loading="lazy" onerror="this.onerror=null;this.src='uploads/default-product.jpg';">
                                        </div>
                                        <div class="testimonial-info">
                                                <h4 class="testimonial-name"><?php echo clean_output($testimonial['name']); ?></h4>
                                                <p class="testimonial-location"><?php echo clean_output($testimonial['location']); ?></p>
                                                <div class="testimonial-rating">
                                                        <?php for($i=1;$i<=5;$i++): ?>
                                                                <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'active' : ''; ?>"></i>
                                                        <?php endfor; ?>
                                                </div>
                                        </div>
                                </div>
                                <div class="testimonial-content">
                                        <p class="testimonial-quote">"<?php echo clean_output($testimonial['comment']); ?>"</p>
                                </div>
                        </div>
                        <?php endforeach; ?>
                </div>
        </div>
</section>

<!-- Newsletter Section -->
<section class="section newsletter-section">
    <div class="container">
        <div class="newsletter-content">
            <div class="newsletter-text">
                <h2>Stay Updated with DOKO</h2>
                <p>Get the latest offers, new product arrivals, and grocery tips delivered to your inbox.</p>
            </div>
            <div class="newsletter-form">
                <form action="api/newsletter.php" method="POST" class="newsletter-form-element">
                    <input type="email" name="email" placeholder="Enter your email address" required autocomplete="email">
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
// Footer with inline JS init
include_footer([], "document.addEventListener('DOMContentLoaded',function(){if(typeof HeroBackgroundSlider!=='undefined'){HeroBackgroundSlider.init();}const f=document.querySelector('.newsletter-form-element');if(f){f.addEventListener('submit',function(e){e.preventDefault();const email=this.querySelector('input[name=email]').value.trim();if(!email)return;fetch('api/newsletter.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email})}).then(r=>r.json()).then(d=>{alert(d.success?'Thank you for subscribing!':'Error: '+d.message);if(d.success) this.reset();}).catch(err=>{console.error('Newsletter error',err);alert('An error occurred.');});});}});");
?>

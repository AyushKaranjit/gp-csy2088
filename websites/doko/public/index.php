<?php
// Set page variables
$page_title = 'DOKO - Fresh Groceries & Daily Essentials';
$current_page = 'home';
$additional_css = ['css/homepage.css', 'css/grocery-theme.css'];
$additional_js = ['script.js'];

// Include header template
include_once '../template/header.php';
?>

    <!-- Flash Sale Section -->
    <section class="flash-sale-section">
        <div class="container">
            <div class="flash-sale-header">
                <div class="flash-sale-title">
                    <i class="fas fa-bolt"></i>
                    <h2>Flash Sale</h2>
                    <span class="sale-badge">UP TO 70% OFF</span>
                </div>
                <div class="flash-sale-timer">
                    <span>Ends in:</span>
                    <div class="timer">
                        <div class="time-unit">
                            <span class="time-value" id="hours">12</span>
                            <span class="time-label">Hours</span>
                        </div>
                        <div class="time-unit">
                            <span class="time-value" id="minutes">34</span>
                            <span class="time-label">Minutes</span>
                        </div>
                        <div class="time-unit">
                            <span class="time-value" id="seconds">56</span>
                            <span class="time-label">Seconds</span>
                        </div>
                    </div>
                </div>
                <a href="category.php?sale=flash" class="view-all-btn">View All</a>
            </div>
            
            <div class="flash-sale-products">
                <div class="flash-product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=300&h=300&fit=crop" alt="Fresh Red Apples">
                        <div class="discount-badge">-30%</div>
                    </div>
                    <div class="product-info">
                        <h4>Fresh Red Apples (1kg)</h4>
                        <div class="price">
                            <span class="current-price">रू 280</span>
                            <span class="original-price">रू 400</span>
                        </div>
                        <div class="sold-count">156 sold</div>
                    </div>
                </div>
                
                <div class="flash-product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1481070555726-e2fe8357725c?w=300&h=300&fit=crop" alt="Fresh Dairy Milk">
                        <div class="discount-badge">-20%</div>
                    </div>
                    <div class="product-info">
                        <h4>Fresh Dairy Milk (1L)</h4>
                        <div class="price">
                            <span class="current-price">रू 80</span>
                            <span class="original-price">रू 100</span>
                        </div>
                        <div class="sold-count">289 sold</div>
                    </div>
                </div>
                
                <div class="flash-product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1518843875459-f738682238a6?w=300&h=300&fit=crop" alt="Mixed Vegetables Pack">
                        <div class="discount-badge">-25%</div>
                    </div>
                    <div class="product-info">
                        <h4>Mixed Vegetables Pack</h4>
                        <div class="price">
                            <span class="current-price">रू 450</span>
                            <span class="original-price">रू 600</span>
                        </div>
                        <div class="sold-count">78 sold</div>
                    </div>
                </div>
                
                <div class="flash-product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=300&h=300&fit=crop" alt="Fresh Whole Wheat Bread">
                        <div class="discount-badge">-15%</div>
                    </div>
                    <div class="product-info">
                        <h4>Fresh Whole Wheat Bread</h4>
                        <div class="price">
                            <span class="current-price">रू 85</span>
                            <span class="original-price">रू 100</span>
                        </div>
                        <div class="sold-count">234 sold</div>
                    </div>
                </div>
                
                <div class="flash-product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1586201375761-83865001e31c?w=300&h=300&fit=crop" alt="Premium Basmati Rice">
                        <div class="discount-badge">-10%</div>
                    </div>
                    <div class="product-info">
                        <h4>Basmati Rice (5kg)</h4>
                        <div class="price">
                            <span class="current-price">रू 900</span>
                            <span class="original-price">रू 1000</span>
                        </div>
                        <div class="sold-count">167 sold</div>
                    </div>
                </div>
                
                <div class="flash-product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=300&h=300&fit=crop" alt="Fresh Chicken">
                        <div class="discount-badge">-18%</div>
                    </div>
                    <div class="product-info">
                        <h4>Fresh Chicken (1kg)</h4>
                        <div class="price">
                            <span class="current-price">रू 410</span>
                            <span class="original-price">रू 500</span>
                        </div>
                        <div class="sold-count">92 sold</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-slider">
                <div class="hero-slide active">
                    <div class="hero-content">
                        <div class="hero-text">
                            <h1 class="hero-title">Nepal's Freshest <span class="highlight">Grocery Marketplace</span></h1>
                            <p class="hero-subtitle">Get fresh fruits, vegetables, dairy, meat, and all your daily essentials delivered to your doorstep</p>
                            <div class="hero-stats">
                                <div class="stat">
                                    <span class="stat-number">5K+</span>
                                    <span class="stat-label">Fresh Products</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-number">50K+</span>
                                    <span class="stat-label">Happy Customers</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-number">2Hr</span>
                                    <span class="stat-label">Express Delivery</span>
                                </div>
                            </div>
                            <div class="hero-actions">
                                <a href="category.php" class="btn btn-primary btn-large">
                                    <i class="fas fa-shopping-bag"></i>
                                    Start Shopping
                                </a>
                                <a href="#features" class="btn btn-outline btn-large">
                                    <i class="fas fa-play"></i>
                                    Watch Demo
                                </a>
                            </div>
                        </div>
                        <div class="hero-image">
                            <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&h=400&fit=crop&crop=center" alt="Shopping Experience" class="hero-img">
                            <div class="floating-cards">
                                <div class="floating-card">
                                    <i class="fas fa-shipping-fast"></i>
                                    <span>2-Hour Delivery</span>
                                </div>
                                <div class="floating-card">
                                    <i class="fas fa-leaf"></i>
                                    <span>Fresh & Organic</span>
                                </div>
                                <div class="floating-card">
                                    <i class="fas fa-thermometer-half"></i>
                                    <span>Temperature Controlled</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section" id="categories">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Shop by Category</h2>
                <p class="section-subtitle">Browse our fresh groceries and daily essentials organized by category for easy shopping</p>
            </div>
            
            <div class="categories-grid">
                <div class="category-card" onclick="window.location.href='category.php?category=fruits-vegetables'">
                    <div class="category-icon">
                        <i class="fas fa-apple-alt"></i>
                    </div>
                    <h3 class="category-name">Fruits & Vegetables</h3>
                    <p class="category-description">Fresh seasonal fruits and organic vegetables delivered daily</p>
                    <span class="category-products-count">500+ Products</span>
                </div>
                
                <div class="category-card" onclick="window.location.href='category.php?category=dairy-eggs'">
                    <div class="category-icon">
                        <i class="fas fa-cheese"></i>
                    </div>
                    <h3 class="category-name">Dairy & Eggs</h3>
                    <p class="category-description">Fresh milk, cheese, yogurt, butter and farm-fresh eggs</p>
                    <span class="category-products-count">200+ Products</span>
                </div>
                
                <div class="category-card" onclick="window.location.href='category.php?category=meat-seafood'">
                    <div class="category-icon">
                        <i class="fas fa-fish"></i>
                    </div>
                    <h3 class="category-name">Meat & Seafood</h3>
                    <p class="category-description">Fresh chicken, mutton, fish and seafood with quality assurance</p>
                    <span class="category-products-count">150+ Products</span>
                </div>
                
                <div class="category-card" onclick="window.location.href='category.php?category=pantry-staples'">
                    <div class="category-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3 class="category-name">Pantry & Staples</h3>
                    <p class="category-description">Rice, dal, flour, oil, spices and cooking essentials</p>
                    <span class="category-products-count">800+ Products</span>
                </div>
                
                <div class="category-card" onclick="window.location.href='category.php?category=beverages'">
                    <div class="category-icon">
                        <i class="fas fa-coffee"></i>
                    </div>
                    <h3 class="category-name">Beverages</h3>
                    <p class="category-description">Tea, coffee, juices, soft drinks and healthy beverages</p>
                    <span class="category-products-count">300+ Products</span>
                </div>
                
                <div class="category-card" onclick="window.location.href='category.php?category=snacks-confectionery'">
                    <div class="category-icon">
                        <i class="fas fa-cookie-bite"></i>
                    </div>
                    <h3 class="category-name">Snacks & Confectionery</h3>
                    <p class="category-description">Biscuits, chips, chocolates, sweets and healthy snacks</p>
                    <span class="category-products-count">400+ Products</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Why Choose DOKO Grocery?</h2>
                <p class="section-subtitle">We provide the freshest groceries with unmatched quality and fastest delivery service</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3 class="feature-title">Fresh & Organic</h3>
                    <p class="feature-description">Handpicked fresh fruits, vegetables and organic products sourced directly from farms daily.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3 class="feature-title">Express Delivery</h3>
                    <p class="feature-description">Get your groceries delivered within 2 hours in Kathmandu Valley with temperature-controlled delivery.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-thermometer-half"></i>
                    </div>
                    <h3 class="feature-title">Cold Chain</h3>
                    <p class="feature-description">Temperature controlled storage and delivery to maintain freshness of dairy, meat and frozen items.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">Quality Guarantee</h3>
                    <p class="feature-description">100% money-back guarantee on quality. If you're not satisfied, we'll replace or refund your order.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">24/7 Support</h3>
                    <p class="feature-description">Round-the-clock customer support through phone, chat, and email for all your queries and concerns.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="feature-title">Mobile App</h3>
                    <p class="feature-description">Order on-the-go with our user-friendly mobile app for iOS and Android with exclusive app-only deals.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Best Selling Products Section -->
    <section class="best-selling-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Best Selling Products</h2>
                <p class="section-subtitle">Most loved products by our customers</p>
                <a href="category.php" class="view-all-link">View All Products <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="products-container" id="bestSellingProducts">
                <!-- Products will be loaded here by JavaScript -->
            </div>
        </div>
    </section>

<?php
// Include footer template
include_once '../template/footer.php';
?>

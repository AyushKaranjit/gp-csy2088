<?php
// Set page variables
$page_title = 'DOKO - Professional E-Commerce Platform';
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
                        <img src="https://images.unsplash.com/photo-1574484284002-952d92456975?w=300&h=300&fit=crop" alt="Fresh Bananas">
                        <div class="discount-badge">-40%</div>
                    </div>
                    <div class="product-info">
                        <h4>Fresh Bananas (1 dozen)</h4>
                        <div class="price">
                            <span class="current-price">रू 120</span>
                            <span class="original-price">रू 200</span>
                        </div>
                        <div class="sold-count">89 sold</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">Fresh & Organic Groceries</h1>
                    <p class="hero-subtitle">Get the freshest produce, dairy, and daily essentials delivered to your doorstep in Kathmandu</p>
                    <div class="hero-features">
                        <div class="feature">
                            <i class="fas fa-truck"></i>
                            <span>Free Delivery Over रू 1000</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-clock"></i>
                            <span>Same Day Delivery</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-leaf"></i>
                            <span>100% Organic Options</span>
                        </div>
                    </div>
                    <div class="hero-cta">
                        <a href="category.php" class="btn-primary">Shop Now</a>
                        <a href="#categories" class="btn-secondary">Browse Categories</a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=600&h=500&fit=crop" alt="Fresh Groceries">
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section" id="categories">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Shop by Category</h2>
                <p class="section-subtitle">Discover fresh products across all categories</p>
            </div>
            
            <div class="categories-grid">
                <div class="category-card featured">
                    <div class="category-image">
                        <img src="https://images.unsplash.com/photo-1610348725531-843dff563e2c?w=300&h=200&fit=crop" alt="Fresh Fruits">
                    </div>
                    <div class="category-content">
                        <h3>Fresh Fruits</h3>
                        <p>Premium quality seasonal fruits</p>
                        <span class="item-count">25+ items</span>
                    </div>
                </div>
                
                <div class="category-card">
                    <div class="category-image">
                        <img src="https://images.unsplash.com/photo-1540420773420-3366772f4999?w=300&h=200&fit=crop" alt="Vegetables">
                    </div>
                    <div class="category-content">
                        <h3>Fresh Vegetables</h3>
                        <p>Farm-fresh daily vegetables</p>
                        <span class="item-count">30+ items</span>
                    </div>
                </div>
                
                <div class="category-card">
                    <div class="category-image">
                        <img src="https://images.unsplash.com/photo-1559561853-08451507cbe7?w=300&h=200&fit=crop" alt="Dairy Products">
                    </div>
                    <div class="category-content">
                        <h3>Dairy & Eggs</h3>
                        <p>Fresh milk, yogurt & eggs</p>
                        <span class="item-count">15+ items</span>
                    </div>
                </div>
                
                <div class="category-card">
                    <div class="category-image">
                        <img src="https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=300&h=200&fit=crop" alt="Bakery">
                    </div>
                    <div class="category-content">
                        <h3>Bakery & Bread</h3>
                        <p>Fresh baked goods daily</p>
                        <span class="item-count">12+ items</span>
                    </div>
                </div>
                
                <div class="category-card">
                    <div class="category-image">
                        <img src="https://images.unsplash.com/photo-1594736797933-d0f3cf47b9e6?w=300&h=200&fit=crop" alt="Meat & Seafood">
                    </div>
                    <div class="category-content">
                        <h3>Meat & Seafood</h3>
                        <p>Premium quality proteins</p>
                        <span class="item-count">18+ items</span>
                    </div>
                </div>
                
                <div class="category-card">
                    <div class="category-image">
                        <img src="https://images.unsplash.com/photo-1586201375761-83865001e31c?w=300&h=200&fit=crop" alt="Pantry Staples">
                    </div>
                    <div class="category-content">
                        <h3>Pantry Staples</h3>
                        <p>Rice, lentils & essentials</p>
                        <span class="item-count">40+ items</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Best Sellers Section -->
    <section class="best-sellers-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Best Sellers</h2>
                <p class="section-subtitle">Most loved products by our customers</p>
                <a href="category.php?filter=bestsellers" class="view-all-link">View All</a>
            </div>
            
            <div class="products-grid">
                <div class="product-card bestseller">
                    <div class="product-badge">Best Seller</div>
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=300&h=300&fit=crop" alt="Fresh Red Apples">
                    </div>
                    <div class="product-info">
                        <h4 class="product-name">Fresh Red Apples</h4>
                        <div class="product-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="rating-count">(256)</span>
                        </div>
                        <div class="product-price">
                            <span class="current-price">रू 400</span>
                            <span class="unit-price">per kg</span>
                        </div>
                        <div class="product-actions">
                            <button class="quantity-btn minus">-</button>
                            <span class="quantity">1</span>
                            <button class="quantity-btn plus">+</button>
                            <button class="add-to-cart-btn">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1481070555726-e2fe8357725c?w=300&h=300&fit=crop" alt="Fresh Dairy Milk">
                    </div>
                    <div class="product-info">
                        <h4 class="product-name">Fresh Dairy Milk</h4>
                        <div class="product-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <span class="rating-count">(189)</span>
                        </div>
                        <div class="product-price">
                            <span class="current-price">रू 100</span>
                            <span class="unit-price">per liter</span>
                        </div>
                        <div class="product-actions">
                            <button class="quantity-btn minus">-</button>
                            <span class="quantity">1</span>
                            <button class="quantity-btn plus">+</button>
                            <button class="add-to-cart-btn">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="product-card organic">
                    <div class="product-badge organic">Organic</div>
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1518843875459-f738682238a6?w=300&h=300&fit=crop" alt="Organic Mixed Vegetables">
                    </div>
                    <div class="product-info">
                        <h4 class="product-name">Organic Mixed Vegetables</h4>
                        <div class="product-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="rating-count">(142)</span>
                        </div>
                        <div class="product-price">
                            <span class="current-price">रू 600</span>
                            <span class="unit-price">per pack</span>
                        </div>
                        <div class="product-actions">
                            <button class="quantity-btn minus">-</button>
                            <span class="quantity">1</span>
                            <button class="quantity-btn plus">+</button>
                            <button class="add-to-cart-btn">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=300&h=300&fit=crop" alt="Fresh Whole Wheat Bread">
                    </div>
                    <div class="product-info">
                        <h4 class="product-name">Fresh Whole Wheat Bread</h4>
                        <div class="product-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span class="rating-count">(97)</span>
                        </div>
                        <div class="product-price">
                            <span class="current-price">रू 100</span>
                            <span class="unit-price">per loaf</span>
                        </div>
                        <div class="product-actions">
                            <button class="quantity-btn minus">-</button>
                            <span class="quantity">1</span>
                            <button class="quantity-btn plus">+</button>
                            <button class="add-to-cart-btn">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Free Delivery</h4>
                        <p>Free delivery on orders over रू 1000 across Kathmandu</p>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Same Day Delivery</h4>
                        <p>Order before 12 PM for same day delivery</p>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div class="feature-content">
                        <h4>100% Fresh</h4>
                        <p>Fresh products sourced directly from farms</p>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Quality Guarantee</h4>
                        <p>Money back guarantee on all products</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
// Include footer template
include_once '../template/footer.php';
?>

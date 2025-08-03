<?php
// Set page variables
$page_title = 'DOKO - Shop Categories';
$current_page = 'shop';
$additional_css = ['css/homepage.css', 'css/grocery-theme.css', 'css/category.css'];
$additional_js = ['enhanced-cart.js'];

// Include header template
include_once '../template/header.php';
?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1 class="main-title">Shop Categories</h1>
            <p class="main-subtitle">Fresh groceries delivered to your doorstep</p>
        </div>
    </section>

    <!-- Quick Category Pills Section -->
    <section class="category-pills-section">
        <div class="container">
            <div class="category-pills">
                <button class="pill active" data-target="daily-best-products">⭐ Daily Best</button>
                <button class="pill" data-target="fruits-products">🍎 Fruits</button>
                <button class="pill" data-target="vegetables-products">🥕 Vegetables</button>
                <button class="pill" data-target="meat-products">🥩 Meat & Fish</button>
                <button class="pill" data-target="dairy-products">🥛 Dairy</button>
                <button class="pill" data-target="bakery-products">🍞 Bakery</button>
                <span class="more-categories">
                    <button class="pill" data-target="snacks-products">🍿 Snacks</button>
                    <button class="pill" data-target="beverages-products">🧃 Beverages</button>
                    <button class="pill" data-target="spices-products">🌶️ Spices</button>
                    <button class="pill" data-target="grains-products">🌾 Grains & Rice</button>
                </span>
                <button class="pill more-categories-btn" onclick="showMoreCategories()">More <i class="fas fa-chevron-down"></i></button>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="shop-container">
        <div class="container">
            <div class="shop-layout">
                <!-- Main Content -->
                <main class="main-content full-width">
                    <!-- Daily Best Sell Products -->
                    <div class="category-block" id="daily-best-products">
                        <div class="category-block-header">
                            <div class="header-left">
                                <h2 class="block-title">⭐ Daily Best Sell</h2>
                                <span class="block-subtitle">Top selling items today</span>
                            </div>
                            <div class="header-right">
                                <span class="item-counter">10 items</span>
                                <button class="view-more-btn" onclick="viewMoreItems('daily-best')">View All</button>
                            </div>
                        </div>
                        <div class="products-grid">
                            <div class="product-card featured" data-category="fruit" onclick="openProductDetail('Apple')">
                                <div class="product-badge">Hot</div>
                                <div class="product-image apple-image"><img src="https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=300&h=300&fit=crop&crop=center" alt="Apple"></div>
                                <h3 class="product-name">Apple</h3>
                                <p class="product-price">रू 150 <span class="price-unit">per kg</span></p>
                                <div class="product-actions" onclick="event.stopPropagation()">
                                    <i class="far fa-heart wishlist-icon"></i>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn minus-btn">-</button>
                                        <input type="number" class="quantity-display" value="1" min="1" max="99">
                                        <button class="quantity-btn plus-btn">+</button>
                                    </div>
                                    <button class="add-to-cart-btn" data-product="Apple" data-price="150">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card" data-category="fruit" onclick="openProductDetail('Banana')">
                                <div class="product-image banana-image"><img src="https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=300&h=300&fit=crop&crop=center" alt="Banana"></div>
                                <h3 class="product-name">Banana</h3>
                                <p class="product-price">रू 140 <span class="price-unit">per dozen</span></p>
                                <div class="product-actions" onclick="event.stopPropagation()">
                                    <i class="far fa-heart wishlist-icon"></i>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn minus-btn">-</button>
                                        <input type="number" class="quantity-display" value="1" min="1" max="99">
                                        <button class="quantity-btn plus-btn">+</button>
                                    </div>
                                    <button class="add-to-cart-btn" data-product="Banana" data-price="140">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card" data-category="vegetables" onclick="openProductDetail('Tomato')">
                                <div class="product-badge">Fresh</div>
                                <div class="product-image tomato-image"><img src="https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=300&h=300&fit=crop&crop=center" alt="Tomato"></div>
                                <h3 class="product-name">Tomato</h3>
                                <p class="product-price">रू 130 <span class="price-unit">per kg</span></p>
                                <div class="product-actions">
                                    <i class="far fa-heart wishlist-icon"></i>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn minus-btn">-</button>
                                        <input type="number" class="quantity-display" value="1" min="1" max="99">
                                        <button class="quantity-btn plus-btn">+</button>
                                    </div>
                                    <button class="add-to-cart-btn" data-product="Tomato" data-price="130">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card" data-category="dairy" onclick="openProductDetail('Milk')">
                                <div class="product-image milk-image"><img src="https://images.unsplash.com/photo-1563636619-e9143da7973b?w=300&h=300&fit=crop&crop=center" alt="Milk"></div>
                                <h3 class="product-name">Fresh Milk</h3>
                                <p class="product-price">रू 90 <span class="price-unit">per liter</span></p>
                                <div class="product-actions">
                                    <i class="far fa-heart wishlist-icon"></i>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn minus-btn">-</button>
                                        <input type="number" class="quantity-display" value="1" min="1" max="99">
                                        <button class="quantity-btn plus-btn">+</button>
                                    </div>
                                    <button class="add-to-cart-btn" data-product="Milk" data-price="90">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card" data-category="vegetables" onclick="openProductDetail('Potato')">
                                <div class="product-image potato-image"><img src="https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=300&h=300&fit=crop&crop=center" alt="Potato"></div>
                                <h3 class="product-name">Potato</h3>
                                <p class="product-price">रू 80 <span class="price-unit">per kg</span></p>
                                <div class="product-actions">
                                    <i class="far fa-heart wishlist-icon"></i>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn minus-btn">-</button>
                                        <input type="number" class="quantity-display" value="1" min="1" max="99">
                                        <button class="quantity-btn plus-btn">+</button>
                                    </div>
                                    <button class="add-to-cart-btn" data-product="Potato" data-price="80">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fruits Section -->
                    <div class="category-block" id="fruits-products" style="display: none;">
                        <div class="category-block-header">
                            <div class="header-left">
                                <h2 class="block-title">🍎 Fresh Fruits</h2>
                                <span class="block-subtitle">Farm fresh seasonal fruits</span>
                            </div>
                            <div class="header-right">
                                <span class="item-counter">15 items</span>
                                <button class="view-more-btn" onclick="viewMoreItems('fruits')">View All</button>
                            </div>
                        </div>
                        <div class="products-grid">
                            <div class="product-card" data-category="fruit" onclick="openProductDetail('Apple')">
                                <div class="product-image apple-image"><img src="https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=300&h=300&fit=crop&crop=center" alt="Apple"></div>
                                <h3 class="product-name">Apple</h3>
                                <p class="product-price">रू 150 <span class="price-unit">per kg</span></p>
                                <div class="product-actions" onclick="event.stopPropagation()">
                                    <i class="far fa-heart wishlist-icon"></i>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn minus-btn">-</button>
                                        <input type="number" class="quantity-display" value="1" min="1" max="99">
                                        <button class="quantity-btn plus-btn">+</button>
                                    </div>
                                    <button class="add-to-cart-btn" data-product="Apple" data-price="150">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card" data-category="fruit" onclick="openProductDetail('Banana')">
                                <div class="product-image banana-image"><img src="https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=300&h=300&fit=crop&crop=center" alt="Banana"></div>
                                <h3 class="product-name">Banana</h3>
                                <p class="product-price">रू 140 <span class="price-unit">per dozen</span></p>
                                <div class="product-actions" onclick="event.stopPropagation()">
                                    <i class="far fa-heart wishlist-icon"></i>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn minus-btn">-</button>
                                        <input type="number" class="quantity-display" value="1" min="1" max="99">
                                        <button class="quantity-btn plus-btn">+</button>
                                    </div>
                                    <button class="add-to-cart-btn" data-product="Banana" data-price="140">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card" data-category="fruit" onclick="openProductDetail('Orange')">
                                <div class="product-image orange-image"><img src="https://images.unsplash.com/photo-1547514701-42782101795e?w=300&h=300&fit=crop&crop=center" alt="Orange"></div>
                                <h3 class="product-name">Orange</h3>
                                <p class="product-price">रू 180 <span class="price-unit">per kg</span></p>
                                <div class="product-actions" onclick="event.stopPropagation()">
                                    <i class="far fa-heart wishlist-icon"></i>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn minus-btn">-</button>
                                        <input type="number" class="quantity-display" value="1" min="1" max="99">
                                        <button class="quantity-btn plus-btn">+</button>
                                    </div>
                                    <button class="add-to-cart-btn" data-product="Orange" data-price="180">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card" data-category="fruit" onclick="openProductDetail('Mango')">
                                <div class="product-badge">Seasonal</div>
                                <div class="product-image mango-image"><img src="https://images.unsplash.com/photo-1553279467-fcd0e64c7b8b?w=300&h=300&fit=crop&crop=center" alt="Mango"></div>
                                <h3 class="product-name">Mango</h3>
                                <p class="product-price">रू 250 <span class="price-unit">per kg</span></p>
                                <div class="product-actions" onclick="event.stopPropagation()">
                                    <i class="far fa-heart wishlist-icon"></i>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn minus-btn">-</button>
                                        <input type="number" class="quantity-display" value="1" min="1" max="99">
                                        <button class="quantity-btn plus-btn">+</button>
                                    </div>
                                    <button class="add-to-cart-btn" data-product="Mango" data-price="250">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-card" data-category="fruit" onclick="openProductDetail('Grapes')">
                                <div class="product-image grapes-image"><img src="https://images.unsplash.com/photo-1537640538966-79f369143f8f?w=300&h=300&fit=crop&crop=center" alt="Grapes"></div>
                                <h3 class="product-name">Grapes</h3>
                                <p class="product-price">रू 300 <span class="price-unit">per kg</span></p>
                                <div class="product-actions" onclick="event.stopPropagation()">
                                    <i class="far fa-heart wishlist-icon"></i>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn minus-btn">-</button>
                                        <input type="number" class="quantity-display" value="1" min="1" max="99">
                                        <button class="quantity-btn plus-btn">+</button>
                                    </div>
                                    <button class="add-to-cart-btn" data-product="Grapes" data-price="300">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other category sections would follow similar pattern -->
                    <!-- Vegetables, Meat, Dairy, Bakery, etc. -->
                </main>
            </div>
        </div>
    </div>

    <script>
        // Category Pills Navigation
        function showMoreCategories() {
            const moreCategories = document.querySelector('.more-categories');
            const btn = document.querySelector('.more-categories-btn');
            
            if (moreCategories.classList.contains('show')) {
                moreCategories.classList.remove('show');
                btn.innerHTML = 'More <i class="fas fa-chevron-down"></i>';
            } else {
                moreCategories.classList.add('show');
                btn.innerHTML = 'Less <i class="fas fa-chevron-up"></i>';
            }
        }

        // Category Pills Functionality
        document.querySelectorAll('.pill[data-target]').forEach(pill => {
            pill.addEventListener('click', function() {
                // Remove active class from all pills
                document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
                // Add active class to clicked pill
                this.classList.add('active');
                
                // Hide all category blocks
                document.querySelectorAll('.category-block').forEach(block => {
                    block.style.display = 'none';
                });
                
                // Show target category block
                const targetId = this.getAttribute('data-target');
                const targetBlock = document.getElementById(targetId);
                if (targetBlock) {
                    targetBlock.style.display = 'block';
                }
            });
        });

        function openProductDetail(productName) {
            window.location.href = `product-detail.php?product=${productName}`;
        }

        function viewMoreItems(category) {
            window.location.href = `category.php?category=${category}&view=all`;
        }
    </script>

<?php
// Include footer template
include_once '../template/footer.php';
?>

<?php
// Set page variables
$page_title = 'Product Details - DOKO';
$current_page = 'product';
$additional_css = ['css/product-detail.css'];
$additional_js = ['product-detail.js'];

// Get product from URL parameter (in real implementation, this would come from database)
$product_name = isset($_GET['product']) ? $_GET['product'] : 'Apple';

// Sample product data (in real implementation, this would come from database)
$products = [
    'Apple' => [
        'name' => 'Fresh Red Apples',
        'price' => 150,
        'unit' => 'per kg',
        'image' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=600&h=600&fit=crop',
        'gallery' => [
            'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=600&h=600&fit=crop',
            'https://images.unsplash.com/photo-1567306226416-28f0efdc88ce?w=600&h=600&fit=crop',
            'https://images.unsplash.com/photo-1590005354167-6da97870c757?w=600&h=600&fit=crop'
        ],
        'description' => 'Fresh, crispy red apples sourced directly from local orchards. Perfect for snacking, baking, or adding to your daily nutrition.',
        'category' => 'Fruits',
        'rating' => 4.5,
        'availability' => 'In Stock',
        'features' => ['Organic', 'Fresh', 'Locally Sourced', 'High Quality']
    ],
    'Banana' => [
        'name' => 'Fresh Bananas',
        'price' => 140,
        'unit' => 'per dozen',
        'image' => 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=600&h=600&fit=crop',
        'gallery' => [
            'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=600&h=600&fit=crop',
            'https://images.unsplash.com/photo-1574484284002-952d92456975?w=600&h=600&fit=crop'
        ],
        'description' => 'Sweet and nutritious bananas perfect for smoothies, desserts, or as a healthy snack.',
        'category' => 'Fruits',
        'rating' => 4.3,
        'availability' => 'In Stock',
        'features' => ['Rich in Potassium', 'Energy Boost', 'Natural Sweetness']
    ]
];

$product = isset($products[$product_name]) ? $products[$product_name] : $products['Apple'];

// Include header template
include_once '../template/header.php';
?>

    <div class="product-detail-container">
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <a href="category.php">Shop</a>
                <span>/</span>
                <a href="category.php?category=<?php echo strtolower($product['category']); ?>"><?php echo $product['category']; ?></a>
                <span>/</span>
                <span><?php echo $product['name']; ?></span>
            </nav>
            
            <div class="product-detail-content">
                <!-- Product Images -->
                <div class="product-images">
                    <div class="main-image">
                        <img id="main-product-image" src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                    <div class="image-gallery">
                        <?php foreach ($product['gallery'] as $index => $image): ?>
                            <img src="<?php echo $image; ?>" alt="<?php echo $product['name']; ?>" 
                                 onclick="changeMainImage('<?php echo $image; ?>')" 
                                 class="gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>">
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="product-info">
                    <h1 class="product-title"><?php echo $product['name']; ?></h1>
                    
                    <div class="product-rating">
                        <div class="stars">
                            <?php 
                            $fullStars = floor($product['rating']);
                            $hasHalfStar = $product['rating'] - $fullStars >= 0.5;
                            
                            for ($i = 0; $i < $fullStars; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                            
                            <?php if ($hasHalfStar): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php endif; ?>
                            
                            <?php for ($i = 0; $i < (5 - ceil($product['rating'])); $i++): ?>
                                <i class="far fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-text"><?php echo $product['rating']; ?> stars</span>
                    </div>
                    
                    <div class="product-price">
                        <span class="current-price">रू <?php echo $product['price']; ?></span>
                        <span class="price-unit"><?php echo $product['unit']; ?></span>
                    </div>
                    
                    <div class="product-availability">
                        <span class="availability-status in-stock">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $product['availability']; ?>
                        </span>
                    </div>
                    
                    <div class="product-features">
                        <?php foreach ($product['features'] as $feature): ?>
                            <span class="feature-badge"><?php echo $feature; ?></span>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="product-description">
                        <p><?php echo $product['description']; ?></p>
                    </div>
                    
                    <div class="product-actions">
                        <div class="quantity-selector">
                            <label>Quantity:</label>
                            <div class="quantity-controls">
                                <button type="button" onclick="decreaseQuantity()">-</button>
                                <input type="number" id="product-quantity" value="1" min="1" max="99">
                                <button type="button" onclick="increaseQuantity()">+</button>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button class="add-to-cart-btn" onclick="addToCart()">
                                <i class="fas fa-shopping-cart"></i>
                                Add to Cart
                            </button>
                            <button class="wishlist-btn" onclick="toggleWishlist()">
                                <i class="far fa-heart"></i>
                                Add to Wishlist
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Tabs -->
            <div class="product-tabs">
                <div class="tab-navigation">
                    <button class="tab-btn active" onclick="showTab('description')">Description</button>
                    <button class="tab-btn" onclick="showTab('nutrition')">Nutrition Info</button>
                    <button class="tab-btn" onclick="showTab('shipping')">Shipping Info</button>
                </div>
                
                <div class="tab-content">
                    <div id="description-tab" class="tab-pane active">
                        <h3>Product Description</h3>
                        <p><?php echo $product['description']; ?></p>
                        <ul>
                            <li>Category: <?php echo $product['category']; ?></li>
                            <li>Unit: <?php echo $product['unit']; ?></li>
                            <li>Availability: <?php echo $product['availability']; ?></li>
                        </ul>
                    </div>
                    
                    <div id="nutrition-tab" class="tab-pane">
                        <h3>Nutrition Information</h3>
                        <div class="nutrition-info">
                            <table>
                                <tr><td>Energy</td><td>52 kcal per 100g</td></tr>
                                <tr><td>Carbohydrates</td><td>14g</td></tr>
                                <tr><td>Dietary Fiber</td><td>2.4g</td></tr>
                                <tr><td>Sugars</td><td>10g</td></tr>
                                <tr><td>Fat</td><td>0.2g</td></tr>
                                <tr><td>Protein</td><td>0.3g</td></tr>
                                <tr><td>Vitamin C</td><td>4.6mg</td></tr>
                                <tr><td>Potassium</td><td>107mg</td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <div id="shipping-tab" class="tab-pane">
                        <h3>Shipping Information</h3>
                        <ul>
                            <li><strong>Free Delivery:</strong> On orders over रू 1000</li>
                            <li><strong>Delivery Time:</strong> 2-4 hours (same day)</li>
                            <li><strong>Delivery Area:</strong> Kathmandu Valley</li>
                            <li><strong>Fresh Guarantee:</strong> 100% fresh products or money back</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const productData = <?php echo json_encode($product); ?>;
        
        function changeMainImage(imageSrc) {
            document.getElementById('main-product-image').src = imageSrc;
            
            // Update active thumbnail
            document.querySelectorAll('.gallery-thumb').forEach(thumb => {
                thumb.classList.remove('active');
            });
            event.target.classList.add('active');
        }
        
        function increaseQuantity() {
            const quantityInput = document.getElementById('product-quantity');
            quantityInput.value = parseInt(quantityInput.value) + 1;
        }
        
        function decreaseQuantity() {
            const quantityInput = document.getElementById('product-quantity');
            if (parseInt(quantityInput.value) > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
            }
        }
        
        function addToCart() {
            const quantity = parseInt(document.getElementById('product-quantity').value);
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            
            const existingItem = cart.find(item => item.name === productData.name);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    name: productData.name,
                    price: productData.price,
                    image: productData.image,
                    quantity: quantity
                });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            
            // Show success message
            showNotification('Added to cart successfully!');
            
            // Update cart count if function exists
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
        }
        
        function toggleWishlist() {
            const wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
            const existingIndex = wishlist.findIndex(item => item.name === productData.name);
            
            if (existingIndex > -1) {
                wishlist.splice(existingIndex, 1);
                showNotification('Removed from wishlist');
            } else {
                wishlist.push({
                    name: productData.name,
                    price: productData.price,
                    image: productData.image
                });
                showNotification('Added to wishlist');
            }
            
            localStorage.setItem('wishlist', JSON.stringify(wishlist));
            
            // Update wishlist count if function exists
            if (typeof updateWishlistCount === 'function') {
                updateWishlistCount();
            }
        }
        
        function showTab(tabName) {
            // Hide all tab panes
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab pane
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
        
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification success';
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #27ae60;
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                z-index: 1000;
                font-weight: 500;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 3000);
        }
    </script>

<?php
// Include footer template
include_once '../template/footer.php';
?>

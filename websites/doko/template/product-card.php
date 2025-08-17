<?php
/**
 * Enhanced Product Card Component Template
 * Usage: 
 * $product = ['id' => 1, 'name' => 'Product Name', ...];
 * include 'template/product-card.php';
 */

if (!isset($product) || !is_array($product)) {
    return;
}

// Map different field name variations to standard format
$productId = $product['id'] ?? $product['product_id'] ?? 0;
$productName = $product['name'] ?? $product['product_name'] ?? 'Unknown Product';
$productPrice = $product['price'] ?? 0;
$productOriginalPrice = $product['original_price'] ?? null;
$productImage = $product['image'] ?? $product['image_url'] ?? '/images/default-product.jpg';
$productCategory = $product['category'] ?? $product['category_name'] ?? 'General';
$productDescription = $product['short_description'] ?? $product['description'] ?? '';
$stockQuantity = $product['stock_quantity'] ?? 0;
$productUnit = $product['unit'] ?? 'piece';
$status = $product['status'] ?? 'active';
$isInactive = strtolower($status) !== 'active';
$inStock = !$isInactive && ($product['in_stock'] ?? ($stockQuantity > 0));

// Set default values for missing fields
$product = array_merge([
    'id' => $productId,
    'name' => $productName,
    'price' => $productPrice,
    'original_price' => $productOriginalPrice,
    'image' => $productImage,
    'category' => $productCategory,
    'short_description' => $productDescription,
    'stock_quantity' => $stockQuantity,
    'unit' => $productUnit,
    'rating' => $product['rating'] ?? 0,
    'in_stock' => $inStock,
    'discount_percentage' => 0,
    'status' => $status,
    'is_inactive' => $isInactive
], $product);

// Calculate discount percentage if original price exists
if ($product['original_price'] && $product['original_price'] > $product['price']) {
    $product['discount_percentage'] = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
}
?>

<div class="product-card" data-product-id="<?php echo $product['id']; ?>">
    <div class="product-image ratio-4x3">
        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="product-image-link">
            <?php
            // Resolve external curated image first
            require_once __DIR__ . '/image-service.php';
            $resolvedExternal = getProductImage($product['name']);
            $img = trim((string)($product['image'] ?? ''));
            $isExternalOriginal = preg_match('~^https?://~i', $img);
            // Prefer curated external if local image is default or missing
            if ($img === '' || stripos($img, 'default') !== false || !$isExternalOriginal) {
                $img = $resolvedExternal ?: $img;
            }
            $isExternal = preg_match('~^https?://~i', $img);
            if(!$isExternal && (strpos($img,'uploads/')===0 || strpos($img,'/uploads/')===0)) {
                // keep relative local paths; leave as-is
            }
            if($img === '' ){ $img = '/images/default-product.jpg'; }
            $lowQualityPlaceholder = '/images/default-product.jpg';
            ?>
            <img src="<?php echo htmlspecialchars($lowQualityPlaceholder); ?>"
                 data-src="<?php echo htmlspecialchars($img); ?>"
                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                 loading="lazy"
                 class="product-img lazy-blur<?php echo $isExternal ? ' external-img' : ''; ?>"
                 data-fallback="/images/default-product.jpg"
                 onerror="if(!this.dataset.errored){this.dataset.errored=1;this.src=this.getAttribute('data-fallback'); this.classList.remove('loading');}else{this.src='/images/default-product.jpg';}"
                 decoding="async" />
        </a>
        
        <?php if ($product['discount_percentage'] > 0): ?>
        <div class="product-badge sale-badge">
            -<?php echo $product['discount_percentage']; ?>%
        </div>
        <?php endif; ?>
        
        <?php if (!$product['in_stock']): ?>
        <div class="product-badge out-of-stock-badge">
            <?php echo $product['is_inactive'] ? 'Unavailable' : 'Out of Stock'; ?>
        </div>
        <?php endif; ?>
        
        <div class="product-actions">
            <button class="btn-icon btn-wishlist" onclick="toggleWishlist(<?php echo $product['id']; ?>)" title="Add to Wishlist">
                <i class="fas fa-heart"></i>
            </button>
            <button class="btn-icon btn-quick-view" onclick="quickView(<?php echo $product['id']; ?>)" title="Quick View">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>
    
    <div class="product-info">
        <div class="product-category">
            <?php echo htmlspecialchars($product['category']); ?>
        </div>
        
        <h3 class="product-name">
            <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                <?php echo htmlspecialchars($product['name']); ?>
            </a>
        </h3>
        
        <?php if (!empty($product['short_description'])): ?>
        <p class="product-description">
            <?php echo htmlspecialchars($product['short_description']); ?>
        </p>
        <?php endif; ?>
        
        <?php if ($product['rating'] > 0): ?>
        <div class="product-rating">
            <div class="stars">
                <?php
                $rating = $product['rating'];
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
            <span class="rating-text">(<?php echo number_format($rating, 1); ?>)</span>
        </div>
        <?php endif; ?>
        
        <div class="product-price">
            <span class="current-price">Rs. <?php echo number_format($product['price'], 2); ?></span>
            <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                <span class="original-price">Rs. <?php echo number_format($product['original_price'], 2); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="stock-info">
            <?php if ($product['in_stock']): ?>
                <span class="in-stock">
                    <i class="fas fa-check-circle"></i> In Stock
                    <?php if ($product['stock_quantity'] > 0): ?>
                        (<?php echo $product['stock_quantity']; ?> <?php echo $product['unit']; ?>)
                    <?php endif; ?>
                </span>
            <?php else: ?>
                <span class="out-of-stock">
                    <i class="fas fa-times-circle"></i> Out of Stock
                </span>
            <?php endif; ?>
        </div>
        
        <?php if ($product['in_stock']): ?>
        <div class="cart-controls">
            <div class="quantity-selector-mini">
                <button type="button" class="qty-btn-mini minus" onclick="changeQuantity(<?php echo $product['id']; ?>, -1)">-</button>
                <input type="number" id="qty-<?php echo $product['id']; ?>" name="qty-<?php echo $product['id']; ?>" class="qty-input-mini" value="1" min="1" max="<?php echo $product['stock_quantity'] ?? 99; ?>" autocomplete="off">
                <button type="button" class="qty-btn-mini plus" onclick="changeQuantity(<?php echo $product['id']; ?>, 1)">+</button>
            </div>
        <button class="btn btn-primary btn-block add-to-cart"
            onclick="addToCartWithQuantity(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>')"
            data-product-id="<?php echo $product['id']; ?>"
            data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
            data-product-price="<?php echo $product['price']; ?>">
                <i class="fas fa-shopping-cart"></i>
                Add to Cart
            </button>
        </div>
        <?php else: ?>
        <button class="btn btn-secondary btn-block" disabled>
            <i class="fas fa-ban"></i>
            <?php echo $product['is_inactive'] ? 'Unavailable' : 'Out of Stock'; ?>
        </button>
        <?php endif; ?>
    </div>
</div>

<style>
.product-card {
    background: var(--white);
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
    position: relative;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

/* Aspect Ratio Wrapper */
.product-image {
    position: relative;
    overflow: hidden;
}
.product-image.ratio-4x3 { aspect-ratio: 4 / 3; }
@supports not (aspect-ratio: 4 / 3) { /* legacy fallback */
    .product-image.ratio-4x3 { height: 0; padding-bottom: 75%; }
    .product-image.ratio-4x3 > a, .product-image.ratio-4x3 img { position: absolute; inset: 0; }
}

.product-image-link {
    display: block;
    width: 100%;
    height: 100%;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition), filter .6s ease;
    display: block;
}

/* Blur-up Lazy Loading */
.product-image img.lazy-blur { filter: blur(20px); transform: scale(1.03); }
.product-image img.lazy-blur.loaded { filter: blur(0); transform: scale(1); }

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 0.25rem 0.5rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    z-index: 2;
}

.sale-badge {
    background: var(--accent-color);
    color: white;
}

.out-of-stock-badge {
    background: #dc3545;
    color: white;
}

.product-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    opacity: 0;
    transition: var(--transition);
}

.product-card:hover .product-actions {
    opacity: 1;
}

.btn-icon {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: none;
    background: white;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.btn-icon:hover {
    background: var(--primary-color);
    color: white;
    transform: scale(1.1);
}

.btn-wishlist.active {
    background: var(--accent-color);
    color: white;
}

.product-info {
    padding: 1.5rem;
}

.product-category {
    font-size: 0.8rem;
    color: var(--light-text);
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}

.product-name {
    margin-bottom: 0.75rem;
}

.product-name a {
    color: var(--dark-text);
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    transition: var(--transition);
}

.product-name a:hover {
    color: var(--primary-color);
}

.product-description {
    font-size: 0.85rem;
    color: var(--light-text);
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
}

.stock-info {
    margin-bottom: 1rem;
    font-size: 0.8rem;
}

.in-stock {
    color: #28a745;
    font-weight: 600;
}

.out-of-stock {
    color: #dc3545;
    font-weight: 600;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.stars {
    display: flex;
    gap: 0.1rem;
}

.stars i {
    font-size: 0.8rem;
    color: #ffc107;
}

.rating-text {
    font-size: 0.8rem;
    color: var(--light-text);
}

.product-price {
    margin-bottom: 1rem; /* fixed potential earlier typo 1re */
}

.current-price {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary-color);
}

.original-price {
    font-size: 0.9rem;
    color: var(--light-text);
    text-decoration: line-through;
    margin-left: 0.5rem;
}

.add-to-cart {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.add-to-cart:hover {
    background: var(--secondary-color);
}

.btn-secondary {
    background: #6c757d;
    color: white;
    cursor: not-allowed;
}

/* Cart Controls */
.cart-controls {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.quantity-selector-mini {
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--light-gray);
    border-radius: var(--border-radius);
    overflow: hidden;
    background: white;
}

.qty-btn-mini {
    background: var(--light-gray);
    border: none;
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    transition: var(--transition);
    min-width: 35px;
}

.qty-btn-mini:hover {
    background: var(--primary);
    color: white;
}

.qty-input-mini {
    border: none;
    padding: 0.5rem;
    text-align: center;
    width: 50px;
    font-size: 0.9rem;
    background: white;
}

.qty-input-mini:focus {
    outline: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    /* Aspect ratio handles height automatically */
    
    .product-info {
        padding: 1rem;
    }
    
    .product-actions {
        opacity: 1;
    }
}
</style>
<script>
// Progressive / lazy load observer (idempotent)
(function(){
    if(window.__DOKO_LAZY_INIT) return; window.__DOKO_LAZY_INIT = true;
    const supportsObserver = 'IntersectionObserver' in window;
    const images = [];
    document.addEventListener('DOMContentLoaded', init);
    function init(){
        document.querySelectorAll('img.lazy-blur[data-src]').forEach(img=>{ if(!img.dataset.lazyBound){ img.dataset.lazyBound=1; images.push(img);} });
        if(!supportsObserver){ images.forEach(loadImage); return; }
        const io = new IntersectionObserver((entries)=>{
            entries.forEach(entry=>{ if(entry.isIntersecting){ loadImage(entry.target); io.unobserve(entry.target);} });
        },{rootMargin:'200px 0px'});
        images.forEach(i=>io.observe(i));
    }
    function loadImage(img){
        const src = img.getAttribute('data-src'); if(!src) return;
        const high = new Image();
        high.onload = function(){ img.src = src; img.classList.add('loaded'); };
        high.onerror = function(){ img.dispatchEvent(new Event('error')); };
        high.src = src;
    }
})();
</script>

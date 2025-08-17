<?php
/**
 * Simple Image Service for DOKO
 * Generates placeholder images with proper names
 */

// Generate a local image URL (use local default when no specific image found)
function getImageUrl($category, $name, $width = 600, $height = 600) {
    // We no longer rely on external image services. Return the local default product image.
    return '/uploads/default-product.jpg';
}

// Function to get product image with multiple fallbacks
function getProductImagePath($productName, $productId = null) {
    global $productImages; // curated map
    $key = strtolower(trim(preg_replace('/[^a-z0-9\s-]/','', $productName)));
    $key = preg_replace('/\s+/', '-', $key);
    if(isset($productImages[$key])) return $productImages[$key];
    // Local file scan (only if running with filesystem access)
    $candidates = [];
    if($key){
        $candidates[] = "uploads/products/{$key}.jpg";
        $candidates[] = "uploads/products/{$key}.png";
        $candidates[] = "images/products/{$key}.jpg";
        $candidates[] = "images/products/{$key}.png";
    }
    if($productId){
        $candidates[] = "uploads/products/product-{$productId}.jpg";
        $candidates[] = "uploads/products/product-{$productId}.png";
    }
    foreach($candidates as $rel){
        $abs = __DIR__.'/../public/'.ltrim($rel,'/');
        if(is_file($abs)) return '/'.$rel; // serve existing local file
    }
    // fallback to a dynamic external image query for product name (disabled by default)
    return getImageUrl('product',$productName,800,800);
}

// Category image function
function getCategoryImagePath($categoryName, $categoryId = null) {
    global $categoryImages;
    if($categoryId && isset($categoryImages[$categoryId])) return $categoryImages[$categoryId];
    $clean = strtolower(trim(preg_replace('/[^a-z0-9\s-]/','', $categoryName)));
    $clean = preg_replace('/\s+/','-', $clean);
    $candidates = [
        "uploads/categories/{$clean}.jpg",
        "uploads/categories/{$clean}.png",
        "images/categories/{$clean}.jpg",
        "images/categories/{$clean}.png"
    ];
    foreach($candidates as $rel){
        $abs = __DIR__.'/../public/'.ltrim($rel,'/');
        if(is_file($abs)) return '/'.$rel;
    }
    return getImageUrl('category',$categoryName,600,600);
}

// Product image mappings for consistent images
// Curated stable image mappings for common products (prefer local files)
$productImages = [
    // Exact database product names first
    'fresh red apples' => '/images/Fresh Red Apples.jpg',
    'bananas (ripe)' => '/images/Banana.jpg',
    'bananas-ripe' => '/images/Banana.jpg',
    'tomatoes (local)' => '/images/Tomatoes (Local).jpg',
    'tomatoes-local' => '/images/Tomatoes (Local).jpg',
    'potatoes (washed)' => '/images/Potatoes (Washed).jpg',
    'potatoes-washed' => '/images/Potatoes (Washed).jpg',
    'spinach bundle' => '/images/Spinach Bundle.jpg',
    'organic milk 1l' => '/images/Organic Milk 1L.jpg',
    'plain yogurt 500g' => '/images/Plain Yogurt 500g.jpg',
    'cheddar cheese 250g' => '/images/CheddarCheese.jpg',
    'whole wheat bread' => '/images/Whole Wheat Bread.jpg',
    'brown bread loaf' => '/images/BrownBreadLoaf.jpg',
    'classic burger buns 6pc' => '/images/Classic Burger Buns 6pc.jpg',
    'orange juice 1l' => '/images/Orange Juice 1L.jpg',
    'green tea box' => '/images/Green Tea Box.jpg',
    'instant coffee 100g' => '/images/Instant Coffee 100g.jpg',
    'fresh chicken 1kg' => '/images/Fresh Chicken 1kg.jpg',
    'rohu fish 1kg' => '/images/Rohu Fish 1kg.jpg',
    'basmati rice 5kg' => '/images/BasmatiRice.jpg',
    'red lentils (masoor) 1kg' => '/images/Red Lentils (Masoor) 1kg.jpg',
    'red lentils-masoor-1kg' => '/images/Red Lentils (Masoor) 1kg.jpg',
    'chickpeas 1kg' => '/images/Chickpeas 1kg.jpg',
    'turmeric powder 200g' => '/images/Turmeric Powder 200g.jpg',
    'cumin seeds 200g' => '/images/Cumin Seeds 200g.jpg',
    'coriander powder 200g' => '/images/Coriander Powder 200g.jpg',
    'salted roasted peanuts 200g' => '/images/Salted Roasted Peanuts 200g.jpg',
    'masala chips family pack' => '/images/Masala Chips Family Pack.jpg',
    
    // Fruits
    'banana'        => '/images/Banana.jpg',
    'bananas'       => '/images/Banana.jpg',
    'apple'         => '/images/Fresh Red Apples.jpg',
    'apples'        => '/images/Fresh Red Apples.jpg',
    'red-apples'    => '/images/Fresh Red Apples.jpg',
    'fresh-apples'  => '/images/Fresh Red Apples.jpg',
    'fruits'        => '/images/Fresh-fruits.jpg',
    'fresh-fruits'  => '/images/Fresh-fruits.jpg',
    
    // Vegetables
    'tomatoes'      => '/images/Tomatoes (Local).jpg',
    'tomato'        => '/images/Tomatoes (Local).jpg',
    'local-tomatoes'=> '/images/Tomatoes (Local).jpg',
    'potatoes'      => '/images/Potatoes (Washed).jpg',
    'potato'        => '/images/Potatoes (Washed).jpg',
    'washed-potatoes'=> '/images/Potatoes (Washed).jpg',
    'spinach'       => '/images/Spinach Bundle.jpg',
    'palungo'       => '/images/Spinach Bundle.jpg',
    'spinach-bundle'=> '/images/Spinach Bundle.jpg',
    'vegetables'    => '/images/Fresh-vegetables.jpg',
    'fresh-vegetables' => '/images/Fresh-vegetables.jpg',
    
    // Dairy Products
    'milk'          => '/images/Organic Milk 1L.jpg',
    'fresh-milk'    => '/images/Organic Milk 1L.jpg',
    'organic-milk'  => '/images/Organic Milk 1L.jpg',
    'yogurt'        => '/images/Plain Yogurt 500g.jpg',
    'jujudhau'      => '/images/Plain Yogurt 500g.jpg',
    'plain-yogurt'  => '/images/Plain Yogurt 500g.jpg',
    'cheese'        => '/images/CheddarCheese.jpg',
    'cheddar-cheese'=> '/images/CheddarCheese.jpg',
    'dairy'         => '/images/FreshDairyproduct.jpg',
    'dairy-products'=> '/images/FreshDairyproduct.jpg',
    'fresh-dairy'   => '/images/FreshDairyproduct.jpg',
    
    // Grains & Pulses
    'rice'          => '/images/BasmatiRice.jpg',
    'basmati-rice'  => '/images/BasmatiRice.jpg',
    'basmati'       => '/images/BasmatiRice.jpg',
    'red-lentils'   => '/images/Red Lentils (Masoor) 1kg.jpg',
    'lentils'       => '/images/Red Lentils (Masoor) 1kg.jpg',
    'masoor'        => '/images/Red Lentils (Masoor) 1kg.jpg',
    'masoor-dal'    => '/images/Red Lentils (Masoor) 1kg.jpg',
    'chickpeas'     => '/images/Chickpeas 1kg.jpg',
    'grains'        => '/images/Grains & Pulses.jpg',
    'pulses'        => '/images/Grains & Pulses.jpg',
    'grains-pulses' => '/images/Grains & Pulses.jpg',
    
    // Spices & Herbs
    'turmeric'      => '/images/Turmeric Powder 200g.jpg',
    'turmeric-powder'=> '/images/Turmeric Powder 200g.jpg',
    'cumin'         => '/images/Cumin Seeds 200g.jpg',
    'cumin-seeds'   => '/images/Cumin Seeds 200g.jpg',
    'coriander'     => '/images/Coriander Powder 200g.jpg',
    'coriander-powder'=> '/images/Coriander Powder 200g.jpg',
    'spices'        => '/images/Spices&Herbs.jpg',
    'herbs'         => '/images/Spices&Herbs.jpg',
    'spices-herbs'  => '/images/Spices&Herbs.jpg',
    'masala'        => '/images/Spices&Herbs.jpg',
    'garam-masala'  => '/images/Spices&Herbs.jpg',
    
    // Meat & Seafood
    'chicken'       => '/images/Fresh Chicken 1kg.jpg',
    'fresh-chicken' => '/images/Fresh Chicken 1kg.jpg',
    'fish'          => '/images/Rohu Fish 1kg.jpg',
    'rohu-fish'     => '/images/Rohu Fish 1kg.jpg',
    'rohu'          => '/images/Rohu Fish 1kg.jpg',
    'seafood'       => '/images/Rohu Fish 1kg.jpg',
    'meat'          => '/images/Fresh Chicken 1kg.jpg',
    
    // Bakery Items
    'bread'         => '/images/BrownBreadLoaf.jpg',
    'brown-bread'   => '/images/BrownBreadLoaf.jpg',
    'bread-loaf'    => '/images/BrownBreadLoaf.jpg',
    'whole-wheat-bread' => '/images/Whole Wheat Bread.jpg',
    'wheat-bread'   => '/images/Whole Wheat Bread.jpg',
    'burger-buns'   => '/images/Classic Burger Buns 6pc.jpg',
    'buns'          => '/images/Classic Burger Buns 6pc.jpg',
    'classic-buns'  => '/images/Classic Burger Buns 6pc.jpg',
    'bakery'        => '/images/BrownBreadLoaf.jpg',
    
    // Beverages
    'orange-juice'  => '/images/Orange Juice 1L.jpg',
    'juice'         => '/images/Orange Juice 1L.jpg',
    'instant-coffee'=> '/images/Instant Coffee 100g.jpg',
    'coffee'        => '/images/Instant Coffee 100g.jpg',
    'green-tea'     => '/images/Green Tea Box.jpg',
    'tea'           => '/images/Green Tea Box.jpg',
    
    // Snacks
    'chips'         => '/images/Masala Chips Family Pack.jpg',
    'masala-chips'  => '/images/Masala Chips Family Pack.jpg',
    'family-chips'  => '/images/Masala Chips Family Pack.jpg',
    'peanuts'       => '/images/Salted Roasted Peanuts 200g.jpg',
    'roasted-peanuts'=> '/images/Salted Roasted Peanuts 200g.jpg',
    'salted-peanuts'=> '/images/Salted Roasted Peanuts 200g.jpg',
    'snacks'        => '/images/Snacks&Beverages.jpg',
    'snacks-beverages' => '/images/Snacks&Beverages.jpg',
    'beverages'     => '/images/Snacks&Beverages.jpg',
    
    // Generic category fallbacks
    'frozen'        => '/images/Fresh-vegetables.jpg',
    'frozen-foods'  => '/images/Fresh-vegetables.jpg',
    'organic'       => '/images/Fresh-vegetables.jpg',
    'premium'       => '/images/Fresh-fruits.jpg',
];

// Expanded curated mappings for products previously pointing to remote images
// Prefer local image files under /images or fallback to the uploads default
$productImages['red-lentils'] = '/images/Red Lentils (Masoor) 1kg.jpg';
$productImages['masoor-dal'] = '/images/Red Lentils (Masoor) 1kg.jpg';
$productImages['red-lentils-masoor'] = '/images/Red Lentils (Masoor) 1kg.jpg';
$productImages['organic-eggs'] = '/uploads/default-product.jpg';
$productImages['green-vegetables'] = '/images/Fresh-vegetables.jpg';
// Ensure specific keys used by cleaning logic are present
$productImages['fresh-milk'] = '/images/Organic Milk 1L.jpg';

// Wrap curated product URLs via proxy if enabled (only absolute external URLs)
// If image proxy is enabled, wrap any absolute http(s) external URLs; local paths are untouched
if(defined('IMAGE_PROXY_ENABLED') && IMAGE_PROXY_ENABLED){
    foreach($productImages as $k=>$v){
        if(str_starts_with($v,'http://') || str_starts_with($v,'https://')){
            $productImages[$k] = '/api/image-proxy.php?url='.rawurlencode($v);
        }
    }
}

// Category image mappings
$categoryImages = [
    // Use local images from public/images for categories
    1 => '/images/Fresh-vegetables.jpg',    // Fresh Vegetables
    2 => '/images/Fresh-fruits.jpg',        // Fresh Fruits
    3 => '/images/FreshDairyproduct.jpg',   // Dairy Products
    4 => '/images/Grains & Pulses.jpg',     // Grains & Pulses
    5 => '/images/Spices&Herbs.jpg',        // Spices & Herbs
    6 => '/images/Snacks&Beverages.jpg',    // Snacks & Beverages
];

if(defined('IMAGE_PROXY_ENABLED') && IMAGE_PROXY_ENABLED){
    foreach($categoryImages as $k=>$v){
        if(str_starts_with($v,'http://') || str_starts_with($v,'https://')){
            $categoryImages[$k] = '/api/image-proxy.php?url='.rawurlencode($v);
        }
    }
}

// Function to get product image by product name
function getProductImage($productName) {
    global $productImages;
    
    // First, try exact match with the original name (case-insensitive)
    $exactKey = strtolower(trim($productName));
    if(isset($productImages[$exactKey])) {
        return $productImages[$exactKey];
    }
    
    // Try with parentheses replaced by dashes
    $dashKey = str_replace(['(', ')', ' '], ['-', '-', '-'], $exactKey);
    $dashKey = preg_replace('/-+/', '-', $dashKey); // collapse multiple dashes
    $dashKey = trim($dashKey, '-'); // remove leading/trailing dashes
    if(isset($productImages[$dashKey])) {
        return $productImages[$dashKey];
    }
    
    // Continue with the existing processing logic
    // Lowercase & remove parentheses
    $key = strtolower($productName);
    $key = str_replace(['(',')'], ' ', $key);
    // Remove measurement tokens (kg, g, ml, l) ONLY at word boundaries
    $key = preg_replace('/\b(\d+(?:\.\d+)?)(kg|g|ml|l)\b/u',' ', $key); // quantities like 1kg, 500g
    $key = preg_replace('/\b(kg|g|ml|l)\b/u',' ', $key);
    // Remove numbers left over
    $key = preg_replace('/\d+/',' ', $key);
    // Remove brand/noise words
    $noise = ['fresh','ddc','royal','everest','mountain','bhatbhateni','organic','premium'];
    $parts = preg_split('/\s+/', trim($key));
    $parts = array_filter($parts, function($p) use ($noise){ return $p !== '' && !in_array($p, $noise, true); });
    $key = implode('-', $parts); // use hyphen separator
    // Normalize plural basic (very light)
    if(substr($key,-1)==='s' && strlen($key)>3){ $singular = substr($key,0,-1); if(isset($productImages[$singular])) $key=$singular; }
    // Map common variations
    $mappings = [
        'banana' => 'bananas',
        'bananas' => 'bananas',
        'apple' => 'apples',
        'apples' => 'apples',
        'mango' => 'mangoes',
        'mangoes' => 'mangoes',
        'orange' => 'oranges',
        'oranges' => 'oranges',
        'palungo' => 'spinach',
        'spinach' => 'spinach',
        'garammasala' => 'garam-masala',
        'masala' => 'garam-masala',
        'jujudhau' => 'yogurt',
        'curd' => 'yogurt',
        'dahi' => 'yogurt',
        'milk' => 'fresh-milk',
        'onion' => 'onions',
        'onions' => 'onions',
        'potato' => 'potatoes',
        'potatoes' => 'potatoes',
        'tomato' => 'tomatoes',
        'tomatoes' => 'tomatoes',
        'cabbage' => 'cabbage',
        'cauliflower' => 'cauliflower',
        'broccoli' => 'broccoli',
        'carrot' => 'carrots',
        'carrots' => 'carrots',
        'lentil' => 'lentils',
        'lentils' => 'lentils',
        'dal' => 'lentils',
        'rice' => 'rice-grains',
        'flour' => 'wheat-flour',
        'atta' => 'wheat-flour',
        'sugar' => 'sugar-crystals',
        'salt' => 'sea-salt',
        'oil' => 'cooking-oil',
        'butter' => 'butter',
        'cheese' => 'cheese',
        'egg' => 'eggs',
        'eggs' => 'eggs',
        'chili' => 'red-chillies',
        'chilli' => 'red-chillies',
        'pepper' => 'black-pepper',
        'spice' => 'spices',
        'spices' => 'spices',
        'juice' => 'orange-juice'
    ];
    if(isset($mappings[$key])) $key = $mappings[$key];
    if(isset($productImages[$key])) return $productImages[$key];
    // Fallback dynamic query with cleaned words (disabled by default). Restrict random queries to grocery domain.
    $query = preg_replace('/[^a-z0-9\s]/',' ', $key);
    $query = trim($query) ?: 'fresh vegetables';
    $query .= ' grocery food';
    $dyn = getImageUrl('product', $query, 800, 800);
    // Do NOT proxy dynamic external random sources; return direct (if enabled)
    return $dyn;
}

// Unified display resolver: prefer explicit usable image else mapped external
if (!function_exists('resolve_display_product_image')) {
    function resolve_display_product_image(?string $raw, string $name): string {
        if ($raw) {
            $lower = strtolower($raw);
            // If already proxied or external and not a default placeholder, keep it
            if ((str_starts_with($lower,'/api/image-proxy.php') || str_starts_with($lower,'http')) && !str_contains($lower,'default-product')) {
                return $raw;
            }
        }
        return getProductImage($name);
    }
}

// Function to get category image
function getCategoryImage($categoryId) {
    global $categoryImages;
    if(isset($categoryImages[$categoryId])) return $categoryImages[$categoryId];
    $dyn = getImageUrl('category','grocery food',600,600);
    return $dyn;
}

// Export functions for use in templates
if (!function_exists('product_image')) {
    function product_image($productName, $width = 300, $height = 300) {
        return getProductImage($productName);
    }
}

if (!function_exists('category_image')) {
    function category_image($categoryId, $width = 300, $height = 300) {
        return getCategoryImage($categoryId);
    }
}
?>

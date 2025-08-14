<?php
/**
 * Simple Image Service for DOKO
 * Generates placeholder images with proper names
 */

// Generate an external image URL (Unsplash static query as last resort)
function getImageUrl($category, $name, $width = 600, $height = 600) {
    $clean = strtolower(trim(preg_replace('/[^a-z0-9\s-]/i','', $name)));
    $clean = preg_replace('/\s+/','+', $clean);
    // Unsplash source (random per request) fallback
    return "https://source.unsplash.com/{$width}x{$height}/?grocery,$category,$clean";
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
    // fallback to dynamic unsplash query for product name
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
// Curated stable Unsplash image IDs (static photos) for common products
$productImages = [
    'tomatoes'      => 'https://images.unsplash.com/photo-1567306226416-28f0efdc88ce?auto=format&fit=crop&w=800&q=70',
    'banana'        => 'https://images.unsplash.com/photo-1574226516831-e1dff420e37d?auto=format&fit=crop&w=800&q=70',
    'bananas'       => 'https://images.unsplash.com/photo-1574226516831-e1dff420e37d?auto=format&fit=crop&w=800&q=70',
    'milk'          => 'https://images.unsplash.com/photo-1585238342028-4cbc9f5dc3f3?auto=format&fit=crop&w=800&q=70',
    'rice'          => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=800&q=70',
    'apples'        => 'https://images.unsplash.com/photo-1567303316750-b7a0c0f7b411?auto=format&fit=crop&w=800&q=70',
    'apple'         => 'https://images.unsplash.com/photo-1567303316750-b7a0c0f7b411?auto=format&fit=crop&w=800&q=70',
    'spinach'       => 'https://images.unsplash.com/photo-1584270354949-c26b0d5b2d41?auto=format&fit=crop&w=800&q=70',
    'palungo'       => 'https://images.unsplash.com/photo-1584270354949-c26b0d5b2d41?auto=format&fit=crop&w=800&q=70',
    'garam-masala'  => 'https://images.unsplash.com/photo-1608111283392-83e50946e9a4?auto=format&fit=crop&w=800&q=70',
    'masala'        => 'https://images.unsplash.com/photo-1608111283392-83e50946e9a4?auto=format&fit=crop&w=800&q=70',
    'yogurt'        => 'https://images.unsplash.com/photo-1584556812952-905ffd0c611a?auto=format&fit=crop&w=800&q=70',
    'jujudhau'      => 'https://images.unsplash.com/photo-1584556812952-905ffd0c611a?auto=format&fit=crop&w=800&q=70',
    'onions'        => 'https://images.unsplash.com/photo-1603031599551-eea48f937111?auto=format&fit=crop&w=800&q=70',
    'onion'         => 'https://images.unsplash.com/photo-1603031599551-eea48f937111?auto=format&fit=crop&w=800&q=70',
    'orange-juice'  => 'https://images.unsplash.com/photo-1586201375761-83865001e31b?auto=format&fit=crop&w=800&q=70',
    'juice'         => 'https://images.unsplash.com/photo-1586201375761-83865001e31b?auto=format&fit=crop&w=800&q=70',
    'bread'         => 'https://images.unsplash.com/photo-1608198093002-ad4e005484b2?auto=format&fit=crop&w=800&q=70',
    'honey'         => 'https://images.unsplash.com/photo-1506617420156-8e4536971650?auto=format&fit=crop&w=800&q=70',
    // Newly added extended curated mappings
    'red-apples'    => 'https://images.unsplash.com/photo-1567303316750-b7a0c0f7b411?auto=format&fit=crop&w=800&q=70',
    'instant-coffee'=> 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&q=70',
    'coffee'        => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&q=70',
    'chicken'       => 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?auto=format&fit=crop&w=800&q=70',
    'rohu-fish'     => 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?auto=format&fit=crop&w=800&q=70',
    'fish'          => 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?auto=format&fit=crop&w=800&q=70',
    'basmati-rice'  => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=800&q=70',
    'red-lentils'   => 'https://images.unsplash.com/photo-1605478371310-a9f1e96b4a3b?auto=format&fit=crop&w=800&q=70',
    'lentils'       => 'https://images.unsplash.com/photo-1605478371310-a9f1e96b4a3b?auto=format&fit=crop&w=800&q=70',
    'masoor'        => 'https://images.unsplash.com/photo-1605478371310-a9f1e96b4a3b?auto=format&fit=crop&w=800&q=70',
    'chickpeas'     => 'https://images.unsplash.com/photo-1584270354949-c26b0d5b2d41?auto=format&fit=crop&w=800&q=70',
    'turmeric-powder'=>'https://images.unsplash.com/photo-1627308597744-1046f9e2d135?auto=format&fit=crop&w=800&q=70',
    'turmeric'      => 'https://images.unsplash.com/photo-1627308597744-1046f9e2d135?auto=format&fit=crop&w=800&q=70',
];

// Expanded curated mappings for products causing errors
// Override earlier local placeholder overrides with stable remote images (or keep if local file exists later)
$productImages['red-lentils'] = 'https://images.unsplash.com/photo-1605478371310-a9f1e96b4a3b?auto=format&fit=crop&w=800&q=70';
$productImages['masoor-dal'] = 'https://images.unsplash.com/photo-1605478371310-a9f1e96b4a3b?auto=format&fit=crop&w=800&q=70';
$productImages['red-lentils-masoor'] = 'https://images.unsplash.com/photo-1605478371310-a9f1e96b4a3b?auto=format&fit=crop&w=800&q=70';
$productImages['organic-eggs'] = 'https://images.unsplash.com/photo-1584556812952-905ffd0c611a?auto=format&fit=crop&w=800&q=70';
$productImages['green-vegetables'] = 'https://images.unsplash.com/photo-1582515073490-dc84f0f7e827?auto=format&fit=crop&w=800&q=70';
// Ensure specific keys used by cleaning logic are present
$productImages['fresh-milk'] = 'https://images.unsplash.com/photo-1585238342028-4cbc9f5dc3f3?auto=format&fit=crop&w=800&q=70';

// Wrap curated product URLs via proxy if enabled (only static images.unsplash.com)
if(defined('IMAGE_PROXY_ENABLED') && IMAGE_PROXY_ENABLED){
    foreach($productImages as $k=>$v){
        if(str_starts_with($v,'https://images.unsplash.com/')){
            $productImages[$k] = '/api/image-proxy.php?url='.rawurlencode($v);
        }
    }
}

// Category image mappings
$categoryImages = [
    // Curated local images chosen for closer relevance per category
    1 => '/images/sydney-rae-t4XYbj1q_Cc-unsplash.jpg',             // Fresh Vegetables (mixed fresh produce)
    2 => '/images/marcos-paulo-prado-0py70yxumAk-unsplash.jpg',     // Fresh Fruits
    3 => '/images/nathan-dumlao-bRdRUUtbxO0-unsplash.jpg',          // Dairy Products (milk/latte context)
    4 => '/images/micheile-henderson-3TgIneA4xjM-unsplash.jpg',     // Grains & Pulses
    5 => '/images/kimberly-fowler-_L0jF0tt2kE-unsplash.jpg',        // Spices & Herbs
    6 => '/images/sean-bernstein-BdrrunAzTjQ-unsplash.jpg',         // Snacks & Beverages (packaged goods)
];

if(defined('IMAGE_PROXY_ENABLED') && IMAGE_PROXY_ENABLED){
    foreach($categoryImages as $k=>$v){
        if(str_starts_with($v,'https://images.unsplash.com/')){
            $categoryImages[$k] = '/api/image-proxy.php?url='.rawurlencode($v);
        }
    }
}

// Function to get product image by product name
function getProductImage($productName) {
    global $productImages;
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
    // Fallback dynamic query with cleaned words (replace hyphens with commas for broader Unsplash match)
    // Restrict random queries to grocery domain to avoid unrelated people images
    $query = preg_replace('/[^a-z0-9\s]/',' ', $key);
    $query = trim($query) ?: 'fresh vegetables';
    $query .= ' grocery food';
    $dyn = getImageUrl('product', $query, 800, 800);
    // Do NOT proxy dynamic source.unsplash.com (frequent 302 & random); return direct
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

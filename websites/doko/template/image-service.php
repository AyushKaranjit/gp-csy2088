<?php
/**
 * Simple Image Service for DOKO
 * Generates placeholder images with proper names
 */

// Function to generate image URL from API services
function getImageUrl($category, $name, $width = 300, $height = 300) {
    // Clean the name for URL
    $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $name));
    $cleanName = str_replace(' ', '+', $cleanName);
    
    // Use local placeholder service or fallback images to avoid CORS issues
    // You can replace these with actual product images in uploads/products/
    
    return '/uploads/default-product.jpg'; // Use local default image to avoid CORS
    
    /* Alternative: Use a different image service that doesn't have CORS issues
    switch($category) {
        case 'vegetables':
            return "https://picsum.photos/{$width}/{$height}/?random&vegetable";
        case 'fruits':
            return "https://picsum.photos/{$width}/{$height}/?random&fruit";
        case 'dairy':
            return "https://picsum.photos/{$width}/{$height}/?random&dairy";
        case 'grains':
            return "https://picsum.photos/{$width}/{$height}/?random&grain";
        case 'spices':
            return "https://picsum.photos/{$width}/{$height}/?random&spice";
        case 'beverages':
            return "https://picsum.photos/{$width}/{$height}/?random&beverage";
        default:
            return "https://picsum.photos/{$width}/{$height}/?random";
    }
    */
}

// Product image mappings for consistent images
$productImages = [
    'tomatoes' => 'uploads/default-product.jpg',
    'bananas' => 'uploads/default-product.jpg',
    'milk' => 'uploads/default-product.jpg',
    'rice' => 'uploads/default-product.jpg',
    'apples' => 'uploads/default-product.jpg',
    'spinach' => 'uploads/default-product.jpg',
    'garam-masala' => 'uploads/default-product.jpg',
    'yogurt' => 'uploads/default-product.jpg',
    'onions' => 'uploads/default-product.jpg',
    'orange-juice' => 'uploads/default-product.jpg',
    'bread' => 'uploads/default-product.jpg',
    'honey' => 'uploads/default-product.jpg',
];

// Category image mappings
$categoryImages = [
    1 => 'uploads/default-product.jpg',
    2 => 'uploads/default-product.jpg',
    3 => 'uploads/default-product.jpg',
    4 => 'uploads/default-product.jpg',
    5 => 'uploads/default-product.jpg',
    6 => 'uploads/default-product.jpg',
];

// Function to get product image by product name
function getProductImage($productName) {
    global $productImages;
    
    $key = strtolower(str_replace([' ', '(', ')', 'kg', 'l', 'ml', 'g'], '', $productName));
    $key = trim(preg_replace('/\d+/', '', $key)); // Remove numbers
    $key = str_replace(['fresh', 'ddc', 'royal', 'everest', 'mountain', 'bhatbhateni'], '', $key);
    $key = trim($key);
    
    // Map common variations
    $mappings = [
        'tomatoes' => 'tomatoes',
        'banana' => 'bananas',
        'milk' => 'milk',
        'rice' => 'rice',
        'apples' => 'apples',
        'apple' => 'apples',
        'palungo' => 'spinach',
        'spinach' => 'spinach',
        'garammasala' => 'garam-masala',
        'masala' => 'garam-masala',
        'jujudhau' => 'yogurt',
        'yogurt' => 'yogurt',
        'onions' => 'onions',
        'onion' => 'onions',
        'juice' => 'orange-juice',
        'bread' => 'bread',
        'honey' => 'honey',
    ];
    
    $mapped = $mappings[$key] ?? $key;
    return $productImages[$mapped] ?? "uploads/default-product.jpg";
}

// Function to get category image
function getCategoryImage($categoryId) {
    global $categoryImages;
    return $categoryImages[$categoryId] ?? "https://source.unsplash.com/300x300/?grocery+food";
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

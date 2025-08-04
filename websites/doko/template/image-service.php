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
    
    // Different image services based on category
    switch($category) {
        case 'vegetables':
            return "https://source.unsplash.com/{$width}x{$height}/?{$cleanName}+vegetable+fresh";
        case 'fruits':
            return "https://source.unsplash.com/{$width}x{$height}/?{$cleanName}+fruit+fresh";
        case 'dairy':
            return "https://source.unsplash.com/{$width}x{$height}/?{$cleanName}+dairy+product";
        case 'grains':
            return "https://source.unsplash.com/{$width}x{$height}/?{$cleanName}+grain+rice+wheat";
        case 'spices':
            return "https://source.unsplash.com/{$width}x{$height}/?{$cleanName}+spice+herb";
        case 'beverages':
            return "https://source.unsplash.com/{$width}x{$height}/?{$cleanName}+drink+beverage";
        default:
            return "https://source.unsplash.com/{$width}x{$height}/?{$cleanName}+food+grocery";
    }
}

// Product image mappings for consistent images
$productImages = [
    'tomatoes' => getImageUrl('vegetables', 'tomatoes'),
    'bananas' => getImageUrl('fruits', 'bananas'),
    'milk' => getImageUrl('dairy', 'milk'),
    'rice' => getImageUrl('grains', 'basmati rice'),
    'apples' => getImageUrl('fruits', 'green apples'),
    'spinach' => getImageUrl('vegetables', 'spinach leaves'),
    'garam-masala' => getImageUrl('spices', 'garam masala spices'),
    'yogurt' => getImageUrl('dairy', 'yogurt'),
    'onions' => getImageUrl('vegetables', 'red onions'),
    'orange-juice' => getImageUrl('beverages', 'orange juice'),
    'bread' => getImageUrl('grains', 'brown bread'),
    'honey' => getImageUrl('beverages', 'honey jar'),
];

// Category image mappings
$categoryImages = [
    1 => getImageUrl('vegetables', 'fresh vegetables'),
    2 => getImageUrl('fruits', 'fresh fruits'),
    3 => getImageUrl('dairy', 'dairy products'),
    4 => getImageUrl('grains', 'grains pulses'),
    5 => getImageUrl('spices', 'spices herbs'),
    6 => getImageUrl('beverages', 'snacks beverages'),
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
    return $productImages[$mapped] ?? "https://source.unsplash.com/300x300/?{$key}+food+grocery";
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

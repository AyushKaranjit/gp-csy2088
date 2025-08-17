<?php
// Simple API returning curated external grocery hero images.
// No authentication required; cache-friendly.
header('Content-Type: application/json');
header('Cache-Control: public, max-age=3000'); // 30 min

// Use structured objects so frontend can show alt text / categories
// Prefer local hero images stored under /images/hero/ for offline operation
$images = [
    [ 'url' => '/images/hero/produce.jpg', 'alt' => 'Fresh seasonal fruits and vegetables on a wooden table', 'category' => 'produce' ],
    [ 'url' => '/images/hero/vegetables.jpg', 'alt' => 'Organic colorful bell peppers and leafy greens', 'category' => 'vegetables' ],
    [ 'url' => '/images/hero/fruits.jpg', 'alt' => 'Assorted fresh citrus fruits sliced and whole', 'category' => 'fruits' ],
    [ 'url' => '/images/hero/dairy.jpg', 'alt' => 'Milk eggs butter and cottage cheese dairy selection', 'category' => 'dairy' ],
    [ 'url' => '/images/hero/bakery.jpg', 'alt' => 'Artisan breads and rolls in rustic basket', 'category' => 'bakery' ],
    [ 'url' => '/images/hero/grains.jpg', 'alt' => 'Selection of whole grains rice and quinoa bowls', 'category' => 'grains' ],
    [ 'url' => '/images/hero/spices.jpg', 'alt' => 'Assorted colorful spices and herbs in bowls', 'category' => 'spices' ],
    [ 'url' => '/images/hero/protein.jpg', 'alt' => 'Lean proteins chicken fillets and fresh herbs', 'category' => 'protein' ],
    [ 'url' => '/images/hero/seafood.jpg', 'alt' => 'Fresh seafood assortment salmon and shellfish on ice', 'category' => 'seafood' ],
    [ 'url' => '/images/hero/beverages.jpg', 'alt' => 'Refreshing breakfast scene with juice and bread', 'category' => 'beverages' ],
    [ 'url' => '/images/hero/snacks.jpg', 'alt' => 'Healthy mixed nuts and dried fruits snack bowls', 'category' => 'snacks' ],
    [ 'url' => '/images/hero/pantry.jpg', 'alt' => 'Olive oil tomatoes garlic and cooking essentials', 'category' => 'pantry' ],
];

echo json_encode([
    'success' => true,
    'count' => count($images),
    'images' => $images,
    'mode' => 'scroll'
]);

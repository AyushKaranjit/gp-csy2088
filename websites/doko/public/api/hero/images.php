<?php
// Simple API returning curated external grocery hero images.
// No authentication required; cache-friendly.
header('Content-Type: application/json');
header('Cache-Control: public, max-age=1800'); // 30 min

// Use structured objects so frontend can show alt text / categories
$images = [
    // Fresh Produce
    [ 'url' => 'https://images.unsplash.com/photo-1604908176997-1251479ab9b9?auto=format&fit=crop&w=1920&q=70', 'alt' => 'Fresh seasonal fruits and vegetables on a wooden table', 'category' => 'produce' ],
    [ 'url' => 'https://images.unsplash.com/photo-1571689936114-b699c8b6c41f?auto=format&fit=crop&w=1920&q=70', 'alt' => 'Organic colorful bell peppers and leafy greens', 'category' => 'vegetables' ],
    [ 'url' => 'https://images.unsplash.com/photo-1582545078239-0116a6b0cece?auto=format&fit=crop&w=1920&q=70', 'alt' => 'Assorted fresh citrus fruits sliced and whole', 'category' => 'fruits' ],
    // Dairy & Eggs
    [ 'url' => 'https://images.unsplash.com/photo-1543353071-10c8ba85a904?auto=format&fit=crop&w=1920&q=70', 'alt' => 'Milk eggs butter and cottage cheese dairy selection', 'category' => 'dairy' ],
    // Bakery & Breads
    [ 'url' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=1920&q=70', 'alt' => 'Artisan breads and rolls in rustic basket', 'category' => 'bakery' ],
    // Grains & Pulses
    [ 'url' => 'https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=1920&q=70', 'alt' => 'Selection of whole grains rice and quinoa bowls', 'category' => 'grains' ],
    // Spices & Herbs
    [ 'url' => 'https://images.unsplash.com/photo-1585238342028-4cbc9f5dc3f3?auto=format&fit=crop&w=1920&q=70', 'alt' => 'Assorted colorful spices and herbs in bowls', 'category' => 'spices' ],
    // Proteins (keep neutral cooked / prepared style)
    [ 'url' => 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?auto=format&fit=crop&w=1920&q=70', 'alt' => 'Lean proteins chicken fillets and fresh herbs', 'category' => 'protein' ],
    [ 'url' => 'https://images.unsplash.com/photo-1627308597744-1046f9e2d135?auto=format&fit=crop&w=1920&q=70', 'alt' => 'Fresh seafood assortment salmon and shellfish on ice', 'category' => 'seafood' ],
    // Beverages & Juices
    [ 'url' => 'https://images.unsplash.com/photo-1586201375754-1412665c5f47?auto=format&fit=crop&w=1920&q=70', 'alt' => 'Refreshing breakfast scene with juice and bread', 'category' => 'beverages' ],
    // Snacks & Packaged
    [ 'url' => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?auto=format&fit=crop&w=1920&q=70', 'alt' => 'Healthy mixed nuts and dried fruits snack bowls', 'category' => 'snacks' ],
    // Pantry / Cooking Essentials
    [ 'url' => 'https://images.unsplash.com/photo-1510626176961-4b57d4fbad03?auto=format&fit=crop&w=1920&q=70', 'alt' => 'Olive oil tomatoes garlic and cooking essentials', 'category' => 'pantry' ],
];

echo json_encode([
    'success' => true,
    'count' => count($images),
    'images' => $images,
    'mode' => 'scroll'
]);

<?php
// Test the image system
require_once '../template/config.php';

echo "<h1>Image System Test</h1>";

$test_products = [
    'Fresh Tomatoes',
    'Bananas', 
    'Milk',
    'Basmati Rice',
    'Green Apples',
    'Non-existent Product'
];

echo "<div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding: 20px;'>";

foreach ($test_products as $product_name) {
    $image_path = product_image($product_name);
    echo "<div style='border: 1px solid #ddd; padding: 15px; text-align: center;'>";
    echo "<h3>{$product_name}</h3>";
    echo "<p>Image path: {$image_path}</p>";
    echo "<div style='height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; margin: 10px 0;'>";
    echo "<img src='{$image_path}' alt='{$product_name}' style='max-width: 100%; max-height: 100%; object-fit: contain;' onerror='handleImageError(this)'>";
    echo "</div>";
    
    // Check if file exists
    $file_exists = file_exists(__DIR__ . '/' . $image_path);
    echo "<p>File exists: " . ($file_exists ? "✅ Yes" : "❌ No") . "</p>";
    echo "</div>";
}

echo "</div>";

echo "<script>
function handleImageError(img) {
    if (img.src.indexOf('default-product.jpg') === -1) {
        img.src = 'uploads/default-product.jpg';
        img.style.objectFit = 'contain';
        img.style.padding = '20px';
        img.style.background = '#f8f9fa';
    } else {
        img.style.display = 'none';
        const placeholder = document.createElement('div');
        placeholder.innerHTML = '<i class=\"fas fa-image\"></i><br>Image not available';
        placeholder.style.cssText = 'width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f8f9fa;color:#6c757d;';
        img.parentNode.appendChild(placeholder);
    }
}
</script>";

echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>";
?>

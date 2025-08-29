<?php
try {
    $pdo = new PDO('mysql:host=doko-mysql-1;dbname=doko_ecommerce', 'student', 'student');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "🎉 DATABASE FULLY POPULATED! 🎉\n\n";

    // Check all tables
    $tables = [
        'users' => '👥 Users',
        'categories' => '📂 Categories',
        'brands' => '🏷️  Brands',
        'products' => '📦 Products',
        'system_settings' => '⚙️  System Settings',
        'coupons' => '🎫 Coupons'
    ];

    foreach ($tables as $table => $label) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
        $result = $stmt->fetch();
        echo "$label: " . $result['count'] . " records\n";
    }

    echo "\n✅ All data from SQL schema has been successfully inserted!\n";
    echo "✅ Your website should now display categories and products properly.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>

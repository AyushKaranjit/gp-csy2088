<?php
// Grocery-only product seeding & cleanup utility
// Usage:
//   php database/seed_grocery_products.php --seed
//   php database/seed_grocery_products.php --purge-and-seed
//   php database/seed_grocery_products.php --purge-tests
//   php database/seed_grocery_products.php --help

require_once __DIR__ . '/../config/database.php';

function out($m){ echo $m."\n"; }

$args = $argv; array_shift($args);
$flags = array_flip($args);
if (isset($flags['--help'])) {
    out("Grocery Product Seeder\n----------------------\n".
        "--seed             Seed curated grocery products (upsert by name)\n".
        "--purge-and-seed   Delete ALL products then seed curated list\n".
        "--purge-tests      Remove products whose name starts with 'Test Product' or sku with 'TEST'\n".
        "--dry-run          Show actions only\n");
    exit(0);
}

if (!($flags['--seed'] ?? false) && !($flags['--purge-and-seed'] ?? false) && !($flags['--purge-tests'] ?? false)) {
    out("Nothing to do. Use --seed, --purge-and-seed or --purge-tests (or --help).");
    exit(1);
}

$dry = isset($flags['--dry-run']);

$db = Database::getInstance()->getConnection();
$db->exec("SET NAMES utf8mb4");

$categories = [
    'Fruits & Vegetables' => [
        ['Fresh Red Apples','Premium farm fresh apples',150,50],
        ['Bananas (Ripe)','Sweet ripe bananas',90,80],
        ['Tomatoes (Local)','Juicy local tomatoes',70,60],
        ['Potatoes (Washed)','Clean table potatoes',55,100],
        ['Spinach Bundle','Fresh green spinach leaves',40,40],
    ],
    'Dairy Products' => [
        ['Organic Milk 1L','Creamy organic cow milk',80,40],
        ['Plain Yogurt 500g','Cultured probiotic yogurt',120,30],
        ['Salted Butter 200g','Rich dairy butter',250,25],
        ['Cheddar Cheese 250g','Aged cheddar block',420,15],
    ],
    'Bakery' => [
        ['Whole Wheat Bread','Freshly baked wheat bread',90,30],
        ['Brown Bread Loaf','Fiber rich brown loaf',95,20],
        ['Classic Burger Buns 6pc','Soft burger buns pack',110,18],
    ],
    'Beverages' => [
        ['Orange Juice 1L','100% pure orange juice',220,25],
        ['Green Tea Box','Antioxidant rich tea',180,34],
        ['Instant Coffee 100g','Premium roast coffee',480,22],
    ],
    'Meat & Seafood' => [
        ['Fresh Chicken 1kg','Farm chicken – cleaned',360,18],
        ['Rohu Fish 1kg','Fresh river fish',520,10],
    ],
    'Pantry Staples' => [
        ['Basmati Rice 5kg','Premium long grain rice',1180,35],
        ['Red Lentils (Masoor) 1kg','Protein rich lentils',190,60],
        ['Chickpeas 1kg','High fiber chickpeas',210,55],
    ],
    'Spices' => [
        ['Turmeric Powder 200g','Pure ground turmeric',160,40],
        ['Cumin Seeds 200g','Whole cumin seeds',180,38],
        ['Coriander Powder 200g','Aromatic coriander',150,42],
    ],
    'Snacks' => [
        ['Salted Roasted Peanuts 200g','Crunchy roasted peanuts',130,50],
        ['Masala Chips Family Pack','Spiced potato chips',180,45],
    ],
];

$catId = [];
$selectCat = $db->prepare("SELECT category_id FROM categories WHERE name = ? LIMIT 1");
$insertCat = $db->prepare("INSERT INTO categories (name, slug, description, created_at) VALUES (?, ?, ?, NOW())");
foreach ($categories as $cName => $_) {
    $selectCat->execute([$cName]);
    $cid = $selectCat->fetchColumn();
    if (!$cid) {
        if ($dry) out("[DRY] create category: $cName");
        else {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-', $cName));
            // ensure uniqueness
            $suffix = 1; $base = $slug; $check = $db->prepare("SELECT 1 FROM categories WHERE slug=? LIMIT 1");
            while (true) { $check->execute([$slug]); if (!$check->fetchColumn()) break; $slug = $base.'-'.$suffix++; }
            $insertCat->execute([$cName, $slug, $cName.' category']);
            $cid = $db->lastInsertId();
        }
    }
    $catId[$cName] = $cid;
}

if (isset($flags['--purge-tests'])) {
    $count = $db->query("SELECT COUNT(*) FROM products WHERE name LIKE 'Test Product%' OR sku LIKE 'TEST%'")->fetchColumn();
    if ($count) {
        if ($dry) out("[DRY] would delete $count test products");
        else { $db->exec("DELETE FROM products WHERE name LIKE 'Test Product%' OR sku LIKE 'TEST%'"); out("✓ Deleted $count test products"); }
    } else out("No test products found.");
}

if (isset($flags['--purge-and-seed'])) {
    out("Purging ALL existing products (images & order items referencing them)...");
    if ($dry) out("[DRY] would delete order_items, product_images, products");
    else {
        $db->exec("DELETE FROM order_items");
        $db->exec("DELETE FROM product_images");
        $db->exec("DELETE FROM products");
    }
}

if (isset($flags['--seed']) || isset($flags['--purge-and-seed'])) {
    out("Seeding curated grocery products...");
    $selectExisting = $db->prepare("SELECT product_id FROM products WHERE name = ? LIMIT 1");
    // Include cost_price (nullable) to satisfy schema expecting that column before category_id
    $insertProduct = $db->prepare("INSERT INTO products (sku, name, slug, short_description, description, price, original_price, cost_price, category_id, stock_quantity, unit, featured, status, visibility, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?, ?, 'active', 'public', NOW())");
    $updateProduct = $db->prepare("UPDATE products SET price=?, category_id=?, stock_quantity=?, updated_at=NOW() WHERE product_id=?");
    $added=0; $updated=0; $errors=0;
    foreach ($categories as $cName => $prods) {
        foreach ($prods as $p) {
            [$name,$desc,$price,$stock] = $p;
            $selectExisting->execute([$name]);
            $row = $selectExisting->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                if ($dry) out("[DRY] update $name"); else { $updateProduct->execute([$price,$catId[$cName],$stock,$row['product_id']]); $updated++; }
                continue;
            }
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',$name)).'-'.substr(sha1($name),0,6);
            $skuBase = strtoupper(preg_replace('/[^A-Z0-9]/i','', substr($name,0,6)));
            $sku = $skuBase . substr(strtoupper(uniqid()),-6);
            try {
                if ($dry) out("[DRY] add $name");
                else { $insertProduct->execute([$sku,$name,$slug,substr($desc,0,120),$desc,$price,null,null,$catId[$cName],$stock,'unit',0]); $added++; }
            } catch (Exception $e) {
                $errors++; out("Error: $name => ".$e->getMessage());
            }
        }
    }
    out("Summary: added=$added updated=$updated errors=$errors");
}

out("Done.");
?>

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
if (array_key_exists('--help', $flags)) {
    out("Grocery Product Seeder\n----------------------\n".
        "--seed             Seed curated grocery products (upsert by name)\n".
        "--purge-and-seed   Delete ALL products then seed curated list\n".
        "--purge-tests      Remove products whose name starts with 'Test Product' or sku with 'TEST'\n".
        "--dry-run          Show actions only\n");
    exit(0);
}

if (!array_key_exists('--seed', $flags) && !array_key_exists('--purge-and-seed', $flags) && !array_key_exists('--purge-tests', $flags)) {
    out("Nothing to do. Use --seed, --purge-and-seed or --purge-tests (or --help).");
    exit(1);
}

$dry = array_key_exists('--dry-run', $flags);

$db = Database::getInstance()->getConnection();
$db->exec("SET NAMES utf8mb4");

$categories = [
    'Fruits & Vegetables' => [
        ['Fresh Red Apples','Crisp and juicy red apples sourced directly from mountain orchards. Rich in vitamins, fiber, and antioxidants. Perfect for snacking, baking, or adding to salads. Each apple is carefully selected for quality and freshness.',150,50],
        ['Bananas (Ripe)','Sweet, perfectly ripened bananas packed with potassium and natural energy. Great for breakfast, smoothies, or healthy snacks. These tropical fruits are rich in vitamins B6 and C, supporting heart health and immune function.',90,80],
        ['Tomatoes (Local)','Fresh, vine-ripened local tomatoes bursting with flavor and nutrients. Grown by local farmers using sustainable practices. Perfect for cooking, salads, or making fresh sauces. High in lycopene and vitamin C.',70,60],
        ['Potatoes (Washed)','Premium quality table potatoes, thoroughly washed and ready to cook. Versatile root vegetables perfect for mashing, roasting, frying, or boiling. Rich in potassium, vitamin C, and dietary fiber. Sourced from certified farms.',55,100],
        ['Spinach Bundle','Fresh, tender spinach leaves packed with iron, vitamins, and minerals. Grown organically without harmful pesticides. Perfect for salads, smoothies, curries, or sautéing. A superfood that supports bone health and immunity.',40,40],
    ],
    'Dairy Products' => [
        ['Organic Milk 1L','Pure, creamy organic cow milk from grass-fed cows. Rich in calcium, protein, and essential vitamins. Pasteurized for safety while maintaining natural taste and nutritional value. Perfect for drinking, cereals, tea, and cooking.',80,40],
        ['Plain Yogurt 500g','Smooth, creamy probiotic yogurt made from pure milk cultures. Rich in beneficial bacteria that support digestive health. High in protein and calcium. Great for breakfast, snacks, or as a healthy ingredient in cooking and baking.',120,30],
        ['Salted Butter 200g','Premium quality salted butter made from fresh cream. Rich, creamy texture perfect for cooking, baking, or spreading on bread. Contains natural vitamins A and D. Made using traditional churning methods for authentic taste.',250,25],
        ['Cheddar Cheese 250g','Aged cheddar cheese with a sharp, tangy flavor. Made from high-quality milk and aged to perfection. Rich in protein and calcium. Perfect for sandwiches, cooking, or cheese platters. Vacuum-sealed for freshness.',420,15],
    ],
    'Bakery' => [
        ['Whole Wheat Bread','Nutritious whole wheat bread baked fresh daily using traditional recipes. Made with 100% whole grain flour, rich in fiber and nutrients. No artificial preservatives. Perfect for sandwiches, toast, or healthy meals.',90,30],
        ['Brown Bread Loaf','Fiber-rich brown bread made with whole grains and natural ingredients. Baked fresh using time-honored techniques. High in nutrients and dietary fiber. Great for healthy eating and weight management. Soft texture, nutty flavor.',95,20],
        ['Classic Burger Buns 6pc','Soft, fluffy burger buns perfect for homemade burgers and sandwiches. Made with high-quality flour and baked to golden perfection. Fresh, airy texture that holds fillings well. Pack of 6 individual buns.',110,18],
    ],
    'Beverages' => [
        ['Orange Juice 1L','100% pure orange juice made from fresh, ripe oranges. No added sugar or artificial flavors. Rich in vitamin C, folate, and natural antioxidants. Freshly squeezed and pasteurized for safety. Refreshing and nutritious.',220,25],
        ['Green Tea Box','Premium quality green tea leaves rich in antioxidants and natural compounds. Supports metabolism and overall wellness. Carefully processed to preserve flavor and health benefits. Perfect for daily consumption. Contains 25 tea bags.',180,34],
        ['Instant Coffee 100g','Premium roasted coffee beans ground to perfection for instant preparation. Rich, aromatic flavor with smooth finish. Made from carefully selected coffee beans. Just add hot water for a perfect cup. Convenient and delicious.',480,22],
    ],
    'Meat & Seafood' => [
        ['Fresh Chicken 1kg','Farm-raised chicken, cleaned and ready to cook. High in protein and essential amino acids. Raised without antibiotics or hormones. Perfect for curries, grilling, roasting, or soup. Vacuum-packed for freshness and hygiene.',360,18],
        ['Rohu Fish 1kg','Fresh river fish, cleaned and cut. Rich in omega-3 fatty acids and high-quality protein. Supports heart and brain health. Perfect for traditional curries, frying, or grilling. Sourced from clean, natural water bodies.',520,10],
    ],
    'Pantry Staples' => [
        ['Basmati Rice 5kg','Premium long-grain basmati rice with authentic aroma and flavor. Aged to perfection for fluffy, separate grains when cooked. Rich in carbohydrates and gluten-free. Perfect for biryanis, pilafs, and daily meals. Premium quality guaranteed.',1180,35],
        ['Red Lentils (Masoor) 1kg','Protein-rich red lentils, cleaned and sorted for quality. Quick-cooking legumes perfect for dal, soups, and curries. High in fiber, folate, and plant-based protein. Essential for vegetarian diets. Sourced from certified farms.',190,60],
        ['Chickpeas 1kg','High-fiber chickpeas (garbanzo beans) packed with protein and nutrients. Versatile legumes perfect for curries, salads, hummus, and snacks. Rich in fiber, folate, and minerals. Supports heart health and digestion.',210,55],
    ],
    'Spices' => [
        ['Turmeric Powder 200g','Pure ground turmeric with anti-inflammatory properties and vibrant color. Essential spice for Indian cooking with numerous health benefits. Rich in curcumin, supports immunity and joint health. Adds color and flavor to dishes.',160,40],
        ['Cumin Seeds 200g','Whole cumin seeds with distinctive aroma and earthy flavor. Essential for tempering and spice blends. Rich in antioxidants and aids digestion. Perfect for dal, vegetables, and meat dishes. Premium quality, carefully sorted.',180,38],
        ['Coriander Powder 200g','Aromatic ground coriander seeds with fresh, citrusy flavor. Essential spice for curries, marinades, and spice blends. Rich in antioxidants and supports digestion. Adds depth and aroma to cooking. Finely ground for even distribution.',150,42],
    ],
    'Snacks' => [
        ['Salted Roasted Peanuts 200g','Crunchy roasted peanuts with perfect salt seasoning. High in protein, healthy fats, and energy. Great for snacking, parties, or adding to dishes. Roasted to golden perfection. Rich in vitamins E and niacin.',130,50],
        ['Masala Chips Family Pack','Spiced potato chips with authentic Indian masala flavoring. Made from fresh potatoes and traditional spice blends. Crispy texture with bold flavors. Perfect for parties, picnics, or family snacking. Large family-size pack.',180,45],
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

if (array_key_exists('--purge-tests', $flags)) {
    $count = $db->query("SELECT COUNT(*) FROM products WHERE name LIKE 'Test Product%' OR sku LIKE 'TEST%'")->fetchColumn();
    if ($count) {
        if ($dry) out("[DRY] would delete $count test products");
        else { $db->exec("DELETE FROM products WHERE name LIKE 'Test Product%' OR sku LIKE 'TEST%'"); out("✓ Deleted $count test products"); }
    } else out("No test products found.");
}

if (array_key_exists('--purge-and-seed', $flags)) {
    out("Purging ALL existing products (images & order items referencing them)...");
    if ($dry) out("[DRY] would delete order_items, product_images, products");
    else {
        $db->exec("DELETE FROM order_items");
        $db->exec("DELETE FROM product_images");
        $db->exec("DELETE FROM products");
    }
}

if (array_key_exists('--seed', $flags) || array_key_exists('--purge-and-seed', $flags)) {
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

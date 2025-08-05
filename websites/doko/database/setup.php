<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOKO Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #17a2b8; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 10px 5px 10px 0; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 DOKO Database Setup</h1>
        
        <?php
        if (isset($_GET['setup'])) {
            echo "<h2>Setting up database...</h2>";
            
            try {
                // Database configuration
                $host = 'localhost';
                $username = 'root';
                $password = '';
                $database = 'doko_grocery_new';
                
                // Connect to MySQL without database first
                $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                
                echo "<div class='success'>✓ Connected to MySQL server</div>";
                
                // Create database
                $pdo->exec("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                echo "<div class='success'>✓ Database '$database' created/verified</div>";
                
                // Select database
                $pdo->exec("USE $database");
                
                // Check if tables exist
                $result = $pdo->query("SHOW TABLES");
                $tables = $result->fetchAll(PDO::FETCH_COLUMN);
                
                if (empty($tables)) {
                    echo "<div class='info'>No tables found. Creating database schema...</div>";
                    
                    // Read schema file
                    $schemaFile = __DIR__ . '/doko_schema.sql';
                    if (!file_exists($schemaFile)) {
                        throw new Exception("Schema file not found: $schemaFile");
                    }
                    
                    $schema = file_get_contents($schemaFile);
                    $statements = array_filter(array_map('trim', explode(';', $schema)));
                    
                    foreach ($statements as $statement) {
                        if (!empty($statement) && !preg_match('/^(--|\/\*|\*|DROP DATABASE|CREATE DATABASE|USE)/', $statement)) {
                            try {
                                $pdo->exec($statement);
                            } catch (PDOException $e) {
                                if (strpos($e->getMessage(), 'already exists') === false) {
                                    echo "<div class='error'>Warning: " . htmlspecialchars($e->getMessage()) . "</div>";
                                }
                            }
                        }
                    }
                    echo "<div class='success'>✓ Database schema created</div>";
                } else {
                    echo "<div class='info'>Found " . count($tables) . " tables in database</div>";
                }
                
                // Check for users
                $result = $pdo->query("SELECT COUNT(*) as count FROM users");
                $userCount = $result->fetch()['count'];
                
                if ($userCount == 0) {
                    echo "<div class='info'>No users found. Adding sample data...</div>";
                    
                    // Add sample users
                    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
                    $userPassword = password_hash('user123', PASSWORD_DEFAULT);
                    
                    $pdo->exec("INSERT INTO users (username, email, password, first_name, last_name, role, status, email_verified) VALUES 
                        ('admin', 'admin@doko.com', '$adminPassword', 'Admin', 'User', 'admin', 'active', 1),
                        ('testuser', 'user@doko.com', '$userPassword', 'Test', 'User', 'customer', 'active', 1)");
                    
                    // Add categories
                    $pdo->exec("INSERT INTO categories (name, slug, description, sort_order, is_featured) VALUES 
                        ('Fruits & Vegetables', 'fruits-vegetables', 'Fresh fruits and vegetables', 1, 1),
                        ('Dairy & Eggs', 'dairy-eggs', 'Milk, cheese, eggs and dairy products', 2, 1),
                        ('Meat & Seafood', 'meat-seafood', 'Fresh meat and seafood', 3, 0),
                        ('Bakery', 'bakery', 'Fresh baked goods and bread', 4, 1),
                        ('Beverages', 'beverages', 'Drinks, juices, and beverages', 5, 0)");
                    
                    // Add brands
                    $pdo->exec("INSERT INTO brands (name, slug, description) VALUES 
                        ('DOKO Fresh', 'doko-fresh', 'Our premium fresh produce brand'),
                        ('Local Farm', 'local-farm', 'Locally sourced products'),
                        ('Organic Plus', 'organic-plus', 'Certified organic products')");
                    
                    // Add sample products
                    $pdo->exec("INSERT INTO products (sku, name, slug, short_description, description, price, original_price, category_id, brand_id, stock_quantity, unit, weight, weight_unit, featured, status) VALUES 
                        ('APPLE001', 'Fresh Red Apples', 'fresh-red-apples', 'Crispy and sweet red apples', 'Fresh, crispy red apples perfect for snacking or cooking. Rich in vitamins and fiber.', 250.00, 300.00, 1, 2, 100, 'kg', 1.0, 'kg', 1, 'active'),
                        ('MILK001', 'Fresh Milk 1L', 'fresh-milk-1l', 'Pure cow milk', 'Fresh, pure cow milk packed with nutrients. Perfect for drinking or cooking.', 120.00, 120.00, 2, 1, 50, 'liter', 1.0, 'liter', 1, 'active'),
                        ('BREAD001', 'Whole Wheat Bread', 'whole-wheat-bread', 'Healthy whole wheat bread', 'Freshly baked whole wheat bread made with premium ingredients.', 80.00, 90.00, 4, 1, 30, 'piece', 0.5, 'kg', 0, 'active'),
                        ('JUICE001', 'Orange Juice 500ml', 'orange-juice-500ml', 'Fresh orange juice', 'Freshly squeezed orange juice packed with vitamin C.', 150.00, 180.00, 5, 1, 25, 'bottle', 0.5, 'liter', 0, 'active')");
                    
                    echo "<div class='success'>✓ Sample data added</div>";
                } else {
                    echo "<div class='info'>Found $userCount users in database</div>";
                }
                
                // Show summary
                echo "<div class='success'>";
                echo "<h3>🎉 Database Setup Complete!</h3>";
                echo "<strong>Login Credentials:</strong><br>";
                echo "Admin: admin@doko.com / admin123<br>";
                echo "User: user@doko.com / user123<br>";
                echo "</div>";
                
                echo "<a href='../public/index.php' class='btn'>Go to Website</a>";
                echo "<a href='../public/admin.php' class='btn'>Go to Admin</a>";
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } else {
            ?>
            <p>This tool will set up your DOKO grocery database with sample data.</p>
            
            <div class="info">
                <strong>What this will do:</strong><br>
                • Create the database 'doko_grocery_new'<br>
                • Create all required tables<br>
                • Add sample products and categories<br>
                • Create admin and test user accounts
            </div>
            
            <a href="?setup=1" class="btn">🚀 Setup Database</a>
            <?php
        }
        ?>
    </div>
</body>
</html>

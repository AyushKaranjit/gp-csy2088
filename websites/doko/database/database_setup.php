<?php
/**
 * Simple DOKO Database Setup Script
 * Creates database and basic tables with sample data
 */

echo "🐳 DOKO Database Setup (Docker Version)\n";
echo "=====================================\n";

// Safety: require explicit confirmation when running from CLI
if (php_sapi_name() === 'cli') {
    $args = $argv; array_shift($args);
    if (!in_array('--confirm', $args, true)) {
        fwrite(STDOUT, "This script will DROP and CREATE the 'doko_ecommerce' database.\n");
        fwrite(STDOUT, "To proceed, re-run with the --confirm flag: php database_setup.php --confirm\n");
        exit(2);
    }
}
try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=mysql;charset=utf8mb4", 'student', 'student', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✓ Connected to MySQL server\n";
    
    // Drop and create database
    $pdo->exec("DROP DATABASE IF EXISTS doko_ecommerce");
    $pdo->exec("CREATE DATABASE doko_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE doko_ecommerce");
    
    echo "✓ Database created: doko_ecommerce\n";
    
    // Create essential tables
    $tables = [
        // Users table
        "CREATE TABLE users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'user') DEFAULT 'user',
            phone VARCHAR(20),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Categories table
        "CREATE TABLE categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            image VARCHAR(255),
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Products table
        "CREATE TABLE products (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            sale_price DECIMAL(10,2),
            stock_quantity INT DEFAULT 0,
            category_id INT,
            image VARCHAR(255),
            status ENUM('active', 'inactive') DEFAULT 'active',
            featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )",
        
        // Orders table
        "CREATE TABLE orders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            payment_method VARCHAR(50),
            payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
            shipping_address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )",
        
        // Order items table
        "CREATE TABLE order_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id),
            FOREIGN KEY (product_id) REFERENCES products(id)
        )",
        
        // Cart table
        "CREATE TABLE cart (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (product_id) REFERENCES products(id)
        )",
        
        // Product reviews table
        "CREATE TABLE product_reviews (
            id INT PRIMARY KEY AUTO_INCREMENT,
            product_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            comment TEXT,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )",
        
        // Settings table
        "CREATE TABLE settings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )"
    ];
    
    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }
    
    echo "✓ Created 8 essential tables\n";
    
    // Insert sample data
    echo "📊 Inserting sample data...\n";
    
    // Sample users
    $pdo->exec("INSERT INTO users (name, email, password, role, phone, address) VALUES 
        ('Admin User', 'admin@doko.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin', '9800000000', 'Admin Office, Kathmandu'),
        ('Test User', 'user@doko.com', '" . password_hash('user123', PASSWORD_DEFAULT) . "', 'user', '9800000001', 'Test Address, Kathmandu')");
    
    // Sample categories
    $pdo->exec("INSERT INTO categories (name, description) VALUES 
        ('Fruits & Vegetables', 'Fresh fruits and vegetables'),
        ('Dairy Products', 'Milk, cheese, yogurt and dairy items'),
        ('Meat & Seafood', 'Fresh meat and seafood'),
        ('Bakery', 'Bread, cakes and bakery items'),
        ('Beverages', 'Drinks and beverages')");
    
    // Sample products
    $pdo->exec("INSERT INTO products (name, description, price, sale_price, stock_quantity, category_id, featured) VALUES 
        ('Fresh Red Apples', 'Premium quality red apples from local farms', 150.00, 120.00, 50, 1, TRUE),
        ('Organic Milk 1L', 'Fresh organic milk from local dairy', 80.00, NULL, 30, 2, TRUE),
        ('White Bread Loaf', 'Freshly baked white bread', 45.00, NULL, 25, 4, FALSE),
        ('Orange Juice 500ml', 'Fresh squeezed orange juice', 120.00, 100.00, 40, 5, TRUE),
        ('Fresh Chicken 1kg', 'Farm fresh chicken', 350.00, 320.00, 15, 3, FALSE)");
    
    // Sample settings
    $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES 
        ('site_name', 'DOKO Grocery'),
        ('site_email', 'contact@doko.com'),
        ('currency', 'NPR'),
        ('tax_rate', '13'),
        ('free_shipping_limit', '1000')");
    
    echo "✓ Sample users added (admin@doko.com/admin123, user@doko.com/user123)\n";
    echo "✓ Sample categories added (5 categories)\n";
    echo "✓ Sample products added (5 products)\n";
    echo "✓ Site settings configured\n";
    
    echo "\n🎉 Database setup completed successfully!\n";
    echo "=====================================\n";
    echo "🌐 Access your site: http://localhost\n";
    echo "👨‍💼 Admin panel: http://localhost/admin.php\n";
    echo "🗄️ phpMyAdmin: http://localhost:8081\n";
    echo "\n🔑 Login credentials:\n";
    echo "   Admin: admin@doko.com / admin123\n";
    echo "   User:  user@doko.com / user123\n";
    echo "   DB:    student / student\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

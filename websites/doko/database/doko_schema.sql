-- DOKO Grocery E-commerce Database Schema
-- CSY2088 Project - Complete Database Structure

-- Create database
CREATE DATABASE IF NOT EXISTS doko_grocery;
USE doko_grocery;

-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    category_id INT,
    stock INT DEFAULT 0,
    unit VARCHAR(20) DEFAULT 'piece',
    weight VARCHAR(20),
    image_url VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    nutritional_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

-- Cart table
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Orders table
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_charge DECIMAL(10,2) DEFAULT 0,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'cash_on_delivery',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    delivery_address TEXT NOT NULL,
    delivery_date DATE,
    notes TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Wishlist table
CREATE TABLE wishlist (
    wishlist_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Product reviews table
CREATE TABLE product_reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    user_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Coupons table
CREATE TABLE coupons (
    coupon_id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    minimum_amount DECIMAL(10,2) DEFAULT 0,
    maximum_discount DECIMAL(10,2),
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    expires_at DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample categories
INSERT INTO categories (name, description, image_url) VALUES
('Fresh Vegetables', 'Fresh and organic vegetables delivered to your door', 'images/categories/vegetables.jpg'),
('Fresh Fruits', 'Seasonal fresh fruits rich in vitamins and minerals', 'images/categories/fruits.jpg'),
('Dairy Products', 'Fresh milk, cheese, yogurt and dairy essentials', 'images/categories/dairy.jpg'),
('Grains & Pulses', 'Premium quality rice, lentils and grains', 'images/categories/grains.jpg'),
('Spices & Herbs', 'Authentic Nepali spices and fresh herbs', 'images/categories/spices.jpg'),
('Snacks & Beverages', 'Healthy snacks and refreshing beverages', 'images/categories/snacks.jpg');

-- Insert sample products
INSERT INTO products (name, description, price, original_price, category_id, stock, unit, weight, image_url, featured) VALUES
-- Vegetables
('Fresh Tomatoes', 'Red ripe tomatoes perfect for cooking', 80.00, 90.00, 1, 100, 'kg', '1kg', 'images/products/tomatoes.jpg', TRUE),
('Green Spinach', 'Fresh spinach leaves rich in iron', 60.00, 70.00, 1, 80, 'bunch', '250g', 'images/products/spinach.jpg', FALSE),
('Organic Carrots', 'Sweet and crunchy organic carrots', 120.00, 130.00, 1, 60, 'kg', '1kg', 'images/products/carrots.jpg', TRUE),
('Red Onions', 'Fresh red onions for everyday cooking', 100.00, 110.00, 1, 150, 'kg', '1kg', 'images/products/onions.jpg', FALSE),
('Potatoes', 'High quality potatoes for all your needs', 70.00, 80.00, 1, 200, 'kg', '1kg', 'images/products/potatoes.jpg', TRUE),

-- Fruits
('Fresh Apples', 'Crispy red apples imported from Kashmir', 280.00, 300.00, 2, 50, 'kg', '1kg', 'images/products/apples.jpg', TRUE),
('Ripe Bananas', 'Sweet yellow bananas perfect for breakfast', 150.00, 160.00, 2, 80, 'dozen', '12 pieces', 'images/products/bananas.jpg', FALSE),
('Juicy Oranges', 'Vitamin C rich oranges from Sindhuli', 200.00, 220.00, 2, 70, 'kg', '1kg', 'images/products/oranges.jpg', TRUE),
('Sweet Mangoes', 'Delicious mangoes from Dharan', 350.00, 380.00, 2, 30, 'kg', '1kg', 'images/products/mangoes.jpg', TRUE),
('Green Grapes', 'Sweet and juicy green grapes', 400.00, 450.00, 2, 25, 'kg', '1kg', 'images/products/grapes.jpg', FALSE),

-- Dairy
('Fresh Milk', 'Pure cow milk delivered fresh daily', 80.00, 85.00, 3, 100, 'liter', '1L', 'images/products/milk.jpg', TRUE),
('Greek Yogurt', 'Thick and creamy Greek style yogurt', 180.00, 200.00, 3, 40, 'cup', '200g', 'images/products/yogurt.jpg', FALSE),
('Mozzarella Cheese', 'Premium mozzarella cheese for pizzas', 450.00, 500.00, 3, 20, 'pack', '200g', 'images/products/cheese.jpg', TRUE),
('Fresh Butter', 'Creamy butter made from pure cream', 320.00, 350.00, 3, 30, 'pack', '200g', 'images/products/butter.jpg', FALSE),

-- Grains & Pulses
('Basmati Rice', 'Premium aged basmati rice', 180.00, 200.00, 4, 80, 'kg', '1kg', 'images/products/rice.jpg', TRUE),
('Red Lentils', 'High protein red lentils (Rahar dal)', 160.00, 180.00, 4, 60, 'kg', '1kg', 'images/products/lentils.jpg', FALSE),
('Chickpeas', 'Premium quality chickpeas (Chana)', 140.00, 160.00, 4, 50, 'kg', '1kg', 'images/products/chickpeas.jpg', TRUE),

-- Spices
('Turmeric Powder', 'Pure turmeric powder for health and cooking', 120.00, 140.00, 5, 40, 'pack', '100g', 'images/products/turmeric.jpg', TRUE),
('Garam Masala', 'Authentic Nepali garam masala blend', 200.00, 220.00, 5, 30, 'pack', '50g', 'images/products/garam-masala.jpg', FALSE),
('Cumin Seeds', 'Aromatic cumin seeds (Jeera)', 180.00, 200.00, 5, 35, 'pack', '100g', 'images/products/cumin.jpg', TRUE),

-- Snacks & Beverages
('Green Tea', 'Premium Himalayan green tea', 250.00, 280.00, 6, 50, 'pack', '100g', 'images/products/green-tea.jpg', TRUE),
('Digestive Biscuits', 'Healthy digestive biscuits for tea time', 120.00, 140.00, 6, 60, 'pack', '200g', 'images/products/biscuits.jpg', FALSE),
('Mixed Nuts', 'Premium mixed nuts for healthy snacking', 800.00, 900.00, 6, 25, 'pack', '250g', 'images/products/nuts.jpg', TRUE);

-- Insert admin user
INSERT INTO users (name, email, password, phone, address, role, email_verified) VALUES
('Admin User', 'admin@doko.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9841234567', 'Kathmandu, Nepal', 'admin', TRUE);

-- Insert sample coupons
INSERT INTO coupons (code, discount_type, discount_value, minimum_amount, maximum_discount, usage_limit, expires_at, is_active) VALUES
('WELCOME10', 'percentage', 10.00, 500.00, 100.00, 100, '2025-12-31 23:59:59', TRUE),
('SAVE50', 'fixed', 50.00, 200.00, NULL, 50, '2025-12-31 23:59:59', TRUE),
('FIRSTORDER', 'percentage', 15.00, 300.00, 150.00, NULL, '2025-12-31 23:59:59', TRUE);

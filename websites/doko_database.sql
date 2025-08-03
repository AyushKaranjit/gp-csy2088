-- DOKO E-Commerce Database Creation Script
-- Created: July 28, 2025
-- Description: Complete database schema for DOKO e-commerce platform

-- Create Database
CREATE DATABASE IF NOT EXISTS doko_ecommerce 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE doko_ecommerce;

-- ============================================================================
-- TABLE CREATION
-- ============================================================================

-- 1. Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    user_type ENUM('customer', 'admin') DEFAULT 'customer',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_user_type (user_type),
    INDEX idx_status (status)
);

-- 2. Categories Table
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    parent_category_id INT NULL,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    INDEX idx_parent_category (parent_category_id),
    INDEX idx_active (is_active),
    INDEX idx_sort_order (sort_order)
);

-- 3. Products Table
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    sku VARCHAR(100) UNIQUE NOT NULL,
    category_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    cost_price DECIMAL(10,2),
    stock_quantity INT NOT NULL DEFAULT 0,
    min_stock_level INT DEFAULT 5,
    weight DECIMAL(8,3),
    dimensions VARCHAR(50),
    brand VARCHAR(100),
    status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
    is_featured BOOLEAN DEFAULT FALSE,
    rating DECIMAL(3,2) DEFAULT 0.00 CHECK (rating >= 0 AND rating <= 5),
    total_reviews INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT,
    INDEX idx_category (category_id),
    INDEX idx_sku (sku),
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_price (price),
    INDEX idx_rating (rating),
    INDEX idx_stock (stock_quantity)
);

-- 4. Product Images Table
CREATE TABLE product_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(200),
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_primary (is_primary),
    INDEX idx_sort_order (sort_order)
);

-- 5. Customer Addresses Table
CREATE TABLE customer_addresses (
    address_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('billing', 'shipping', 'both') DEFAULT 'both',
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    company VARCHAR(100),
    address_line1 VARCHAR(200) NOT NULL,
    address_line2 VARCHAR(200),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'Nepal',
    phone VARCHAR(20),
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_default (is_default),
    INDEX idx_type (type)
);

-- 6. Orders Table
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method ENUM('cash_on_delivery', 'card', 'bank_transfer', 'digital_wallet') NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'NPR',
    billing_address_id INT,
    shipping_address_id INT,
    notes TEXT,
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (billing_address_id) REFERENCES customer_addresses(address_id) ON DELETE SET NULL,
    FOREIGN KEY (shipping_address_id) REFERENCES customer_addresses(address_id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_created_at (created_at),
    INDEX idx_payment_method (payment_method)
);

-- 7. Order Items Table
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
);

-- 8. Shopping Cart Table
CREATE TABLE shopping_cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1 CHECK (quantity > 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user (user_id),
    INDEX idx_updated_at (updated_at)
);

-- 9. Wishlist Table
CREATE TABLE wishlist (
    wishlist_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user (user_id),
    INDEX idx_created_at (created_at)
);

-- 10. Product Reviews Table
CREATE TABLE product_reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200),
    review_text TEXT,
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_user (user_id),
    INDEX idx_rating (rating),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- 11. Coupons Table
CREATE TABLE coupons (
    coupon_id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('fixed', 'percentage') NOT NULL,
    value DECIMAL(10,2) NOT NULL CHECK (value > 0),
    minimum_amount DECIMAL(10,2) DEFAULT 0.00,
    maximum_discount DECIMAL(10,2),
    usage_limit INT,
    used_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    start_date TIMESTAMP NOT NULL,
    end_date TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active (is_active),
    INDEX idx_dates (start_date, end_date),
    CHECK (end_date > start_date)
);

-- 12. User Sessions Table
CREATE TABLE user_sessions (
    session_id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at),
    INDEX idx_active (is_active)
);

-- 13. Inventory Logs Table
CREATE TABLE inventory_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    change_type ENUM('sale', 'restock', 'adjustment', 'damage', 'return') NOT NULL,
    quantity_change INT NOT NULL,
    old_quantity INT NOT NULL,
    new_quantity INT NOT NULL,
    notes TEXT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_date (created_at),
    INDEX idx_change_type (change_type)
);

-- ============================================================================
-- SAMPLE DATA INSERTION
-- ============================================================================

-- Insert Categories
INSERT INTO categories (name, description, image_url, sort_order) VALUES
('Fruits', 'Fresh and organic fruits direct from farms', '/images/categories/fruits.jpg', 1),
('Vegetables', 'Fresh vegetables and leafy greens', '/images/categories/vegetables.jpg', 2),
('Dairy', 'Fresh milk, cheese, yogurt and dairy products', '/images/categories/dairy.jpg', 3),
('Meat & Poultry', 'Fresh meat, chicken and seafood', '/images/categories/meat.jpg', 4),
('Bakery', 'Fresh bread, cakes and baked goods', '/images/categories/bakery.jpg', 5),
('Beverages', 'Juices, soft drinks and beverages', '/images/categories/beverages.jpg', 6),
('Pantry', 'Rice, grains, spices and cooking essentials', '/images/categories/pantry.jpg', 7),
('Snacks', 'Chips, biscuits and ready-to-eat snacks', '/images/categories/snacks.jpg', 8);

-- Insert Sample Users
INSERT INTO users (username, email, password_hash, first_name, last_name, user_type, email_verified) VALUES
('admin', 'admin@doko.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', TRUE),
('john_doe', 'john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'customer', TRUE),
('jane_smith', 'jane.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', 'customer', TRUE),
('mike_johnson', 'mike.johnson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike', 'Johnson', 'customer', FALSE);

-- Insert Sample Products
INSERT INTO products (name, description, short_description, sku, category_id, price, cost_price, stock_quantity, min_stock_level, brand, is_featured) VALUES
('Fresh Red Apples', 'Crisp and sweet red apples, perfect for snacking or cooking. Rich in vitamins and fiber.', 'Crisp and sweet red apples', 'FRUIT-001', 1, 399.00, 250.00, 156, 20, 'Farm Fresh', TRUE),
('Organic Bananas', 'Premium organic yellow bananas, naturally ripened and full of potassium and energy.', 'Premium organic yellow bananas', 'FRUIT-002', 1, 249.00, 150.00, 89, 15, 'Organic Valley', TRUE),
('Fresh Whole Milk', 'Pure and fresh whole milk, rich in calcium and protein. Perfect for daily nutrition.', 'Pure and fresh whole milk 1L', 'DAIRY-001', 3, 499.00, 350.00, 0, 10, 'Dairy Best', FALSE),
('Brown Bread Loaf', 'Healthy whole wheat brown bread, soft and nutritious. Perfect for sandwiches.', 'Healthy whole wheat brown bread', 'BAKERY-001', 5, 89.00, 45.00, 67, 10, 'Golden Bakery', FALSE),
('Fresh Spinach', 'Organic fresh spinach leaves, rich in iron and vitamins. Great for salads and cooking.', 'Organic fresh spinach leaves', 'VEG-001', 2, 125.00, 75.00, 234, 25, 'Green Fields', FALSE);

-- Insert Product Images
INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order) VALUES
(1, '/images/products/apples-main.jpg', 'Fresh Red Apples', TRUE, 1),
(1, '/images/products/apples-2.jpg', 'Red Apples Close-up', FALSE, 2),
(2, '/images/products/bananas-main.jpg', 'Organic Bananas', TRUE, 1),
(2, '/images/products/bananas-bunch.jpg', 'Banana Bunch', FALSE, 2),
(3, '/images/products/milk-bottle.jpg', 'Whole Milk Bottle', TRUE, 1),
(4, '/images/products/brown-bread.jpg', 'Brown Bread Loaf', TRUE, 1),
(5, '/images/products/spinach-leaves.jpg', 'Fresh Spinach', TRUE, 1);

-- Insert Sample Customer Addresses
INSERT INTO customer_addresses (user_id, type, first_name, last_name, address_line1, city, state, postal_code, phone, is_default) VALUES
(2, 'both', 'John', 'Doe', 'Thamel Street, House No. 123', 'Kathmandu', 'Bagmati', '44600', '+977-9841234567', TRUE),
(3, 'both', 'Jane', 'Smith', 'Patan Durbar Square, Building 45', 'Lalitpur', 'Bagmati', '44700', '+977-9851234568', TRUE),
(4, 'shipping', 'Mike', 'Johnson', 'Boudha Stupa Road, Apartment 12', 'Kathmandu', 'Bagmati', '44600', '+977-9861234569', TRUE);

-- Insert Sample Orders
INSERT INTO orders (order_number, user_id, status, payment_status, payment_method, subtotal, tax_amount, shipping_cost, total_amount, billing_address_id, shipping_address_id) VALUES
('ORD-001', 2, 'processing', 'paid', 'cash_on_delivery', 2850.00, 142.50, 100.00, 3092.50, 1, 1),
('ORD-002', 3, 'shipped', 'paid', 'card', 4200.00, 210.00, 150.00, 4560.00, 2, 2),
('ORD-003', 4, 'delivered', 'paid', 'digital_wallet', 1650.00, 82.50, 100.00, 1832.50, 3, 3);

-- Insert Order Items
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(1, 1, 3, 399.00, 1197.00),
(1, 2, 5, 249.00, 1245.00),
(1, 4, 2, 89.00, 178.00),
(1, 5, 1, 125.00, 125.00),
(2, 1, 8, 399.00, 3192.00),
(2, 3, 2, 499.00, 998.00),
(3, 2, 4, 249.00, 996.00),
(3, 4, 3, 89.00, 267.00),
(3, 5, 2, 125.00, 250.00);

-- Insert Sample Coupons
INSERT INTO coupons (code, name, description, type, value, minimum_amount, usage_limit, start_date, end_date) VALUES
('WELCOME10', 'Welcome Discount', '10% discount for new customers', 'percentage', 10.00, 500.00, 100, '2025-01-01 00:00:00', '2025-12-31 23:59:59'),
('SAVE50', 'Fixed Discount', 'Rs. 50 off on orders above Rs. 1000', 'fixed', 50.00, 1000.00, 200, '2025-01-01 00:00:00', '2025-06-30 23:59:59'),
('BIGDEAL', 'Big Deal Discount', '15% off on orders above Rs. 2000', 'percentage', 15.00, 2000.00, 50, '2025-01-01 00:00:00', '2025-03-31 23:59:59');

-- Insert Sample Shopping Cart Items
INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES
(2, 1, 2),
(2, 3, 1),
(3, 2, 3),
(3, 4, 1),
(4, 5, 2);

-- Insert Sample Wishlist Items
INSERT INTO wishlist (user_id, product_id) VALUES
(2, 3),
(2, 5),
(3, 1),
(3, 4),
(4, 1),
(4, 2);

-- Insert Sample Product Reviews
INSERT INTO product_reviews (product_id, user_id, order_id, rating, title, review_text, is_verified_purchase, status) VALUES
(1, 2, 1, 5, 'Excellent Quality!', 'These apples are incredibly fresh and crispy. Great value for money!', TRUE, 'approved'),
(2, 3, 2, 4, 'Good bananas', 'Fresh and sweet bananas, though slightly expensive.', TRUE, 'approved'),
(4, 4, 3, 5, 'Perfect bread', 'Soft and healthy brown bread. My family loves it!', TRUE, 'approved'),
(5, 4, 3, 4, 'Fresh spinach', 'Very fresh spinach leaves, perfect for cooking.', TRUE, 'approved');

-- Insert Sample Inventory Logs
INSERT INTO inventory_logs (product_id, change_type, quantity_change, old_quantity, new_quantity, notes, user_id) VALUES
(1, 'restock', 50, 106, 156, 'Weekly restock from supplier', 1),
(2, 'restock', 40, 49, 89, 'Fresh batch received', 1),
(3, 'sale', -2, 2, 0, 'Sold via order ORD-002', NULL),
(4, 'restock', 30, 37, 67, 'Bakery delivery', 1),
(5, 'restock', 100, 134, 234, 'Large shipment from farm', 1);

-- ============================================================================
-- CREATE VIEWS FOR COMMON QUERIES
-- ============================================================================

-- View for Product Catalog with Category Information
CREATE VIEW product_catalog AS
SELECT 
    p.product_id,
    p.name AS product_name,
    p.description,
    p.short_description,
    p.sku,
    p.price,
    p.stock_quantity,
    p.rating,
    p.total_reviews,
    p.is_featured,
    p.status,
    c.name AS category_name,
    c.category_id,
    pi.image_url AS primary_image
FROM products p
LEFT JOIN categories c ON p.category_id = c.category_id
LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = TRUE
WHERE p.status = 'active' AND c.is_active = TRUE;

-- View for Order Summary
CREATE VIEW order_summary AS
SELECT 
    o.order_id,
    o.order_number,
    o.status,
    o.payment_status,
    o.total_amount,
    o.created_at,
    u.first_name,
    u.last_name,
    u.email,
    COUNT(oi.order_item_id) AS total_items
FROM orders o
LEFT JOIN users u ON o.user_id = u.user_id
LEFT JOIN order_items oi ON o.order_id = oi.order_id
GROUP BY o.order_id;

-- View for Low Stock Products
CREATE VIEW low_stock_products AS
SELECT 
    p.product_id,
    p.name,
    p.sku,
    p.stock_quantity,
    p.min_stock_level,
    c.name AS category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.category_id
WHERE p.stock_quantity <= p.min_stock_level 
AND p.status = 'active';

-- ============================================================================
-- CREATE TRIGGERS FOR BUSINESS LOGIC
-- ============================================================================

-- Trigger to update product rating when review is added/updated
DELIMITER //
CREATE TRIGGER update_product_rating 
AFTER INSERT ON product_reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET 
        rating = (
            SELECT AVG(rating) 
            FROM product_reviews 
            WHERE product_id = NEW.product_id 
            AND status = 'approved'
        ),
        total_reviews = (
            SELECT COUNT(*) 
            FROM product_reviews 
            WHERE product_id = NEW.product_id 
            AND status = 'approved'
        )
    WHERE product_id = NEW.product_id;
END //
DELIMITER ;

-- Trigger to log inventory changes when stock is updated
DELIMITER //
CREATE TRIGGER log_inventory_change 
AFTER UPDATE ON products
FOR EACH ROW
BEGIN
    IF OLD.stock_quantity != NEW.stock_quantity THEN
        INSERT INTO inventory_logs (
            product_id, 
            change_type, 
            quantity_change, 
            old_quantity, 
            new_quantity, 
            notes
        ) VALUES (
            NEW.product_id,
            'adjustment',
            NEW.stock_quantity - OLD.stock_quantity,
            OLD.stock_quantity,
            NEW.stock_quantity,
            'Stock updated manually'
        );
    END IF;
END //
DELIMITER ;

-- ============================================================================
-- CREATE STORED PROCEDURES
-- ============================================================================

-- Procedure to get customer order history
DELIMITER //
CREATE PROCEDURE GetCustomerOrderHistory(IN customer_id INT)
BEGIN
    SELECT 
        o.order_number,
        o.status,
        o.total_amount,
        o.created_at,
        COUNT(oi.order_item_id) as total_items
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.user_id = customer_id
    GROUP BY o.order_id
    ORDER BY o.created_at DESC;
END //
DELIMITER ;

-- Procedure to get sales analytics
DELIMITER //
CREATE PROCEDURE GetSalesAnalytics(IN start_date DATE, IN end_date DATE)
BEGIN
    SELECT 
        DATE(o.created_at) as order_date,
        COUNT(o.order_id) as total_orders,
        SUM(o.total_amount) as total_revenue,
        AVG(o.total_amount) as average_order_value
    FROM orders o
    WHERE DATE(o.created_at) BETWEEN start_date AND end_date
    AND o.status != 'cancelled'
    GROUP BY DATE(o.created_at)
    ORDER BY order_date;
END //
DELIMITER ;

-- ============================================================================
-- INDEXES FOR PERFORMANCE OPTIMIZATION
-- ============================================================================

-- Additional composite indexes for common queries
CREATE INDEX idx_products_category_status ON products(category_id, status);
CREATE INDEX idx_products_featured_status ON products(is_featured, status);
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
CREATE INDEX idx_orders_date_status ON orders(created_at, status);
CREATE INDEX idx_order_items_product_date ON order_items(product_id, created_at);

-- Full-text search indexes
CREATE FULLTEXT INDEX idx_products_search ON products(name, description, short_description);
CREATE FULLTEXT INDEX idx_categories_search ON categories(name, description);

-- ============================================================================
-- SECURITY AND MAINTENANCE
-- ============================================================================

-- Create database user for application
-- CREATE USER 'doko_app'@'localhost' IDENTIFIED BY 'secure_password_here';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON doko_ecommerce.* TO 'doko_app'@'localhost';
-- FLUSH PRIVILEGES;

-- Note: Remember to:
-- 1. Change default passwords
-- 2. Set up regular backups
-- 3. Configure proper SSL certificates
-- 4. Set up monitoring and logging
-- 5. Regular security updates

SELECT 'Database setup completed successfully!' AS Status;

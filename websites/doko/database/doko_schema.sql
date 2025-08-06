-- DOKO Grocery E-commerce Database Schema (Redesigned)
-- CSY2088 Project - Complete New Database Structure
-- Created: August 5, 2025

-- Drop existing database and create new one
DROP DATABASE IF EXISTS doko_ecommerce;
CREATE DATABASE doko_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE doko_ecommerce;

-- ============================================================================
-- USER MANAGEMENT TABLES
-- ============================================================================

-- Users table with enhanced security and profile features
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    profile_image VARCHAR(255),
    role ENUM('customer', 'admin', 'manager', 'vendor') DEFAULT 'customer',
    status ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'pending',
    email_verified BOOLEAN DEFAULT FALSE,
    phone_verified BOOLEAN DEFAULT FALSE,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_role (role)
);

-- User addresses table for multiple delivery addresses
CREATE TABLE user_addresses (
    address_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    address_type ENUM('home', 'work', 'other') DEFAULT 'home',
    address_label VARCHAR(50),
    street_address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(50) DEFAULT 'Nepal',
    landmark TEXT,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_default (is_default)
);

-- User sessions for security tracking
CREATE TABLE user_sessions (
    session_id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_info VARCHAR(255),
    location VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);

-- ============================================================================
-- PRODUCT MANAGEMENT TABLES
-- ============================================================================

-- Enhanced categories table with hierarchy support
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    parent_id INT NULL,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    icon VARCHAR(100),
    sort_order INT DEFAULT 0,
    meta_title VARCHAR(160),
    meta_description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    INDEX idx_parent_id (parent_id),
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
);

-- Brands table for product brands
CREATE TABLE brands (
    brand_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    logo_url VARCHAR(255),
    website VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
);

-- Enhanced products table
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    sku VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    short_description TEXT,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    cost_price DECIMAL(10,2),
    category_id INT,
    brand_id INT,
    stock_quantity INT DEFAULT 0,
    min_stock_level INT DEFAULT 5,
    unit VARCHAR(20) DEFAULT 'piece',
    weight DECIMAL(8,2),
    weight_unit ENUM('kg', 'g', 'lb', 'oz') DEFAULT 'g',
    dimensions VARCHAR(50),
    barcode VARCHAR(100),
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'out_of_stock', 'discontinued') DEFAULT 'active',
    visibility ENUM('public', 'hidden', 'password_protected') DEFAULT 'public',
    nutritional_info JSON,
    ingredients TEXT,
    allergen_info TEXT,
    storage_instructions TEXT,
    expiry_days INT,
    tax_rate DECIMAL(5,2) DEFAULT 0.00,
    meta_title VARCHAR(160),
    meta_description TEXT,
    sort_order INT DEFAULT 0,
    total_sales INT DEFAULT 0,
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    FOREIGN KEY (brand_id) REFERENCES brands(brand_id) ON DELETE SET NULL,
    INDEX idx_sku (sku),
    INDEX idx_slug (slug),
    INDEX idx_category_id (category_id),
    INDEX idx_brand_id (brand_id),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_price (price),
    INDEX idx_stock_quantity (stock_quantity),
    FULLTEXT idx_search (name, short_description, description)
);

-- Product images table
CREATE TABLE product_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_is_primary (is_primary)
);

-- Product variants table (for different sizes, colors, etc.)
CREATE TABLE product_variants (
    variant_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    sku VARCHAR(50) UNIQUE NOT NULL,
    variant_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    stock_quantity INT DEFAULT 0,
    attributes JSON, -- Store variant attributes like size, color, etc.
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_sku (sku)
);

-- ============================================================================
-- SHOPPING & ORDER MANAGEMENT TABLES
-- ============================================================================

-- Enhanced cart table with session support
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    session_id VARCHAR(128) NULL,
    product_id INT NOT NULL,
    variant_id INT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_product_id (product_id),
    UNIQUE KEY unique_cart_item (user_id, session_id, product_id, variant_id)
);

-- Enhanced orders table
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'partially_refunded') DEFAULT 'pending',
    
    -- Order totals
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    shipping_fee DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    
    -- Shipping information
    shipping_address JSON NOT NULL,
    billing_address JSON,
    shipping_method VARCHAR(50),
    tracking_number VARCHAR(100),
    
    -- Payment information
    payment_method ENUM('cash_on_delivery', 'esewa', 'khalti', 'imepay', 'fonepay', 'bank_transfer') DEFAULT 'cash_on_delivery',
    payment_reference VARCHAR(100),
    
    -- Delivery information
    delivery_date DATE,
    delivery_time_slot VARCHAR(50),
    delivery_instructions TEXT,
    
    -- Order notes and tracking
    notes TEXT,
    admin_notes TEXT,
    
    -- Timestamps
    ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_at TIMESTAMP NULL,
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_order_number (order_number),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_ordered_at (ordered_at)
);

-- Enhanced order items table
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT NULL,
    product_name VARCHAR(150) NOT NULL,
    product_sku VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    product_snapshot JSON, -- Store product details at time of order
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT,
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
);

-- ============================================================================
-- CUSTOMER ENGAGEMENT TABLES
-- ============================================================================

-- Enhanced wishlist table
CREATE TABLE wishlist (
    wishlist_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id),
    INDEX idx_user_id (user_id)
);

-- Enhanced reviews table
CREATE TABLE product_reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(150),
    review TEXT,
    images JSON, -- Store review images
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    helpful_votes INT DEFAULT 0,
    total_votes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL,
    INDEX idx_product_id (product_id),
    INDEX idx_user_id (user_id),
    INDEX idx_rating (rating),
    INDEX idx_status (status)
);

-- Review helpfulness tracking
CREATE TABLE review_votes (
    vote_id INT PRIMARY KEY AUTO_INCREMENT,
    review_id INT NOT NULL,
    user_id INT NOT NULL,
    is_helpful BOOLEAN NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (review_id) REFERENCES product_reviews(review_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (review_id, user_id)
);

-- ============================================================================
-- MARKETING & PROMOTIONS TABLES
-- ============================================================================

-- Enhanced coupons table
CREATE TABLE coupons (
    coupon_id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('fixed', 'percentage', 'free_shipping') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0.00,
    max_discount_amount DECIMAL(10,2) NULL,
    usage_limit INT NULL,
    usage_limit_per_user INT DEFAULT 1,
    used_count INT DEFAULT 0,
    applicable_to ENUM('all', 'categories', 'products', 'users') DEFAULT 'all',
    applicable_ids JSON, -- Store category/product/user IDs
    starts_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_code (code),
    INDEX idx_is_active (is_active),
    INDEX idx_expires_at (expires_at)
);

-- Coupon usage tracking
CREATE TABLE coupon_usage (
    usage_id INT PRIMARY KEY AUTO_INCREMENT,
    coupon_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (coupon_id) REFERENCES coupons(coupon_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    INDEX idx_coupon_id (coupon_id),
    INDEX idx_user_id (user_id)
);

-- ============================================================================
-- NOTIFICATION & COMMUNICATION TABLES
-- ============================================================================

-- Notifications table
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('order_update', 'promotion', 'system', 'review_reminder', 'low_stock', 'wishlist_sale') NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    data JSON, -- Additional notification data
    is_read BOOLEAN DEFAULT FALSE,
    sent_via ENUM('web', 'email', 'sms', 'push') DEFAULT 'web',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_type (type)
);

-- Newsletter subscriptions
CREATE TABLE newsletter_subscriptions (
    subscription_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    user_id INT NULL,
    status ENUM('active', 'unsubscribed', 'bounced') DEFAULT 'active',
    preferences JSON, -- Store subscription preferences
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- ============================================================================
-- INVENTORY & STOCK MANAGEMENT TABLES
-- ============================================================================

-- Stock movements tracking
CREATE TABLE stock_movements (
    movement_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    variant_id INT NULL,
    movement_type ENUM('purchase', 'sale', 'adjustment', 'return', 'damage', 'expired') NOT NULL,
    quantity_change INT NOT NULL, -- Positive for increase, negative for decrease
    quantity_before INT NOT NULL,
    quantity_after INT NOT NULL,
    unit_cost DECIMAL(10,2),
    reference_type ENUM('order', 'adjustment', 'return', 'manual') NOT NULL,
    reference_id INT NULL,
    notes TEXT,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_product_id (product_id),
    INDEX idx_movement_type (movement_type),
    INDEX idx_created_at (created_at)
);

-- ============================================================================
-- ANALYTICS & REPORTING TABLES
-- ============================================================================

-- Product view tracking
CREATE TABLE product_views (
    view_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NULL,
    session_id VARCHAR(128) NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referrer VARCHAR(255),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_product_id (product_id),
    INDEX idx_user_id (user_id),
    INDEX idx_viewed_at (viewed_at)
);

-- Search queries tracking
CREATE TABLE search_queries (
    query_id INT PRIMARY KEY AUTO_INCREMENT,
    query_text VARCHAR(255) NOT NULL,
    user_id INT NULL,
    session_id VARCHAR(128) NULL,
    results_count INT DEFAULT 0,
    clicked_product_id INT NULL,
    ip_address VARCHAR(45),
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (clicked_product_id) REFERENCES products(product_id) ON DELETE SET NULL,
    INDEX idx_query_text (query_text),
    INDEX idx_user_id (user_id),
    INDEX idx_searched_at (searched_at)
);

-- ============================================================================
-- SYSTEM CONFIGURATION TABLES
-- ============================================================================

-- System settings
CREATE TABLE system_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE, -- Whether setting can be accessed by frontend
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key),
    INDEX idx_is_public (is_public)
);

-- Activity logs for admin actions
CREATE TABLE activity_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NULL,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity_type (entity_type),
    INDEX idx_created_at (created_at)
);

-- ============================================================================
-- SAMPLE DATA INSERTION
-- ============================================================================

-- Insert default admin user
INSERT INTO users (username, email, password, first_name, last_name, role, status, email_verified) VALUES
('admin', 'admin@doko.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 'active', TRUE);

-- Insert sample categories
INSERT INTO categories (name, slug, description, sort_order, is_featured) VALUES
('Fruits & Vegetables', 'fruits-vegetables', 'Fresh fruits and vegetables', 1, TRUE),
('Dairy & Eggs', 'dairy-eggs', 'Milk, cheese, yogurt, and eggs', 2, TRUE),
('Meat & Seafood', 'meat-seafood', 'Fresh meat and seafood', 3, FALSE),
('Bakery', 'bakery', 'Bread, cakes, and baked goods', 4, TRUE),
('Pantry Staples', 'pantry-staples', 'Rice, flour, oil, and cooking essentials', 5, TRUE),
('Snacks & Beverages', 'snacks-beverages', 'Snacks, drinks, and beverages', 6, TRUE),
('Frozen Foods', 'frozen-foods', 'Frozen vegetables, meat, and ready meals', 7, FALSE),
('Personal Care', 'personal-care', 'Health and beauty products', 8, FALSE),
('Household Items', 'household-items', 'Cleaning supplies and household essentials', 9, FALSE),
('Baby Products', 'baby-products', 'Baby food, diapers, and care products', 10, FALSE);

-- Insert sample brands
INSERT INTO brands (name, slug, description) VALUES
('Fresh Farm', 'fresh-farm', 'Local fresh produce brand'),
('Dairy Best', 'dairy-best', 'Premium dairy products'),
('Organic Valley', 'organic-valley', 'Certified organic products'),
('Bakery Express', 'bakery-express', 'Fresh baked goods daily'),
('Kitchen King', 'kitchen-king', 'Quality cooking essentials');

-- Insert sample products
INSERT INTO products (sku, name, slug, short_description, description, price, original_price, category_id, brand_id, stock_quantity, unit, weight, weight_unit, featured, status) VALUES
('APPLE001', 'Fresh Red Apples', 'fresh-red-apples', 'Crispy and sweet red apples', 'Premium quality red apples sourced from local farms. Rich in vitamins and perfect for snacking.', 180.00, 200.00, 1, 1, 50, 'kg', 1.00, 'kg', TRUE, 'active'),
('MILK001', 'Fresh Milk 1L', 'fresh-milk-1l', 'Pure and fresh cow milk', 'Farm fresh cow milk, pasteurized and packed with nutrients. Perfect for daily consumption.', 85.00, 90.00, 2, 2, 30, 'liter', 1.00, 'kg', TRUE, 'active'),
('RICE001', 'Basmati Rice 5kg', 'basmati-rice-5kg', 'Premium basmati rice', 'Long grain basmati rice with authentic aroma and taste. Perfect for special occasions.', 650.00, 700.00, 5, 5, 25, 'bag', 5.00, 'kg', TRUE, 'active'),
('BREAD001', 'Whole Wheat Bread', 'whole-wheat-bread', 'Healthy whole wheat bread', 'Soft and nutritious whole wheat bread, perfect for sandwiches and toast.', 45.00, 50.00, 4, 4, 40, 'loaf', 400.00, 'g', FALSE, 'active'),
('BANANA001', 'Fresh Bananas', 'fresh-bananas', 'Sweet and ripe bananas', 'Rich in potassium and natural sweetness, perfect for smoothies and snacking.', 120.00, 130.00, 1, 1, 60, 'dozen', 1.50, 'kg', TRUE, 'active');

-- Insert sample system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('site_name', 'DOKO Grocery', 'string', 'Website name', TRUE),
('site_description', 'Fresh groceries delivered to your door', 'string', 'Website description', TRUE),
('currency', 'NPR', 'string', 'Default currency', TRUE),
('tax_rate', '13.00', 'number', 'Default tax rate percentage', FALSE),
('min_order_amount', '500.00', 'number', 'Minimum order amount for checkout', TRUE),
('free_shipping_threshold', '2000.00', 'number', 'Minimum amount for free shipping', TRUE),
('delivery_fee', '100.00', 'number', 'Standard delivery fee', TRUE),
('max_cart_items', '50', 'number', 'Maximum items allowed in cart', FALSE),
('session_timeout', '3600', 'number', 'Session timeout in seconds', FALSE),
('maintenance_mode', 'false', 'boolean', 'Enable maintenance mode', FALSE);

-- Insert sample coupon
INSERT INTO coupons (code, name, description, type, value, min_order_amount, starts_at, expires_at) VALUES
('WELCOME10', 'Welcome Discount', 'Get 10% off on your first order', 'percentage', 10.00, 500.00, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
('FREESHIP', 'Free Shipping', 'Free shipping on orders above Rs. 1000', 'free_shipping', 100.00, 1000.00, NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY));

-- ============================================================================
-- CREATE VIEWS FOR COMMON QUERIES
-- ============================================================================

-- Product details view with category and brand info
CREATE VIEW product_details_view AS
SELECT 
    p.*,
    c.name as category_name,
    c.slug as category_slug,
    b.name as brand_name,
    b.slug as brand_slug,
    AVG(pr.rating) as average_rating,
    COUNT(pr.review_id) as review_count,
    CASE 
        WHEN p.stock_quantity <= p.min_stock_level THEN 'low'
        WHEN p.stock_quantity = 0 THEN 'out'
        ELSE 'in_stock'
    END as stock_status
FROM products p
LEFT JOIN categories c ON p.category_id = c.category_id
LEFT JOIN brands b ON p.brand_id = b.brand_id
LEFT JOIN product_reviews pr ON p.product_id = pr.product_id AND pr.status = 'approved'
GROUP BY p.product_id;

-- Order summary view
CREATE VIEW order_summary_view AS
SELECT 
    o.*,
    u.first_name,
    u.last_name,
    u.email,
    u.phone,
    COUNT(oi.order_item_id) as item_count,
    SUM(oi.quantity) as total_quantity
FROM orders o
JOIN users u ON o.user_id = u.user_id
LEFT JOIN order_items oi ON o.order_id = oi.order_id
GROUP BY o.order_id;

-- User statistics view
CREATE VIEW user_stats_view AS
SELECT 
    u.user_id,
    u.username,
    u.email,
    u.first_name,
    u.last_name,
    u.created_at,
    COUNT(DISTINCT o.order_id) as total_orders,
    COALESCE(SUM(o.total_amount), 0) as total_spent,
    COUNT(DISTINCT pr.review_id) as total_reviews,
    COUNT(DISTINCT w.wishlist_id) as wishlist_items
FROM users u
LEFT JOIN orders o ON u.user_id = o.user_id
LEFT JOIN product_reviews pr ON u.user_id = pr.user_id
LEFT JOIN wishlist w ON u.user_id = w.user_id
WHERE u.role = 'customer'
GROUP BY u.user_id;

-- ============================================================================
-- CREATE STORED PROCEDURES
-- ============================================================================

DELIMITER //

-- Procedure to update product stock
CREATE PROCEDURE UpdateProductStock(
    IN p_product_id INT,
    IN p_variant_id INT,
    IN p_quantity_change INT,
    IN p_movement_type ENUM('purchase', 'sale', 'adjustment', 'return', 'damage', 'expired'),
    IN p_reference_type ENUM('order', 'adjustment', 'return', 'manual'),
    IN p_reference_id INT,
    IN p_notes TEXT,
    IN p_created_by INT
)
BEGIN
    DECLARE current_stock INT DEFAULT 0;
    DECLARE new_stock INT DEFAULT 0;
    
    START TRANSACTION;
    
    -- Get current stock
    IF p_variant_id IS NULL THEN
        SELECT stock_quantity INTO current_stock FROM products WHERE product_id = p_product_id;
        SET new_stock = current_stock + p_quantity_change;
        UPDATE products SET stock_quantity = new_stock, updated_at = CURRENT_TIMESTAMP WHERE product_id = p_product_id;
    ELSE
        SELECT stock_quantity INTO current_stock FROM product_variants WHERE variant_id = p_variant_id;
        SET new_stock = current_stock + p_quantity_change;
        UPDATE product_variants SET stock_quantity = new_stock, updated_at = CURRENT_TIMESTAMP WHERE variant_id = p_variant_id;
    END IF;
    
    -- Insert stock movement record
    INSERT INTO stock_movements (
        product_id, variant_id, movement_type, quantity_change, 
        quantity_before, quantity_after, reference_type, reference_id, 
        notes, created_by
    ) VALUES (
        p_product_id, p_variant_id, p_movement_type, p_quantity_change,
        current_stock, new_stock, p_reference_type, p_reference_id,
        p_notes, p_created_by
    );
    
    COMMIT;
END //

-- Procedure to calculate order totals
CREATE PROCEDURE CalculateOrderTotals(
    IN p_order_id INT,
    OUT p_subtotal DECIMAL(10,2),
    OUT p_tax_amount DECIMAL(10,2),
    OUT p_total_amount DECIMAL(10,2)
)
BEGIN
    DECLARE tax_rate DECIMAL(5,2) DEFAULT 0;
    
    -- Get tax rate from settings
    SELECT CAST(setting_value AS DECIMAL(5,2)) INTO tax_rate 
    FROM system_settings 
    WHERE setting_key = 'tax_rate';
    
    -- Calculate subtotal
    SELECT SUM(total_price) INTO p_subtotal
    FROM order_items
    WHERE order_id = p_order_id;
    
    -- Calculate tax
    SET p_tax_amount = (p_subtotal * tax_rate) / 100;
    
    -- Calculate total (will be updated with shipping and discounts later)
    SET p_total_amount = p_subtotal + p_tax_amount;
    
END //

DELIMITER ;

-- ============================================================================
-- CREATE TRIGGERS
-- ============================================================================

DELIMITER //

-- Trigger to update product view count
CREATE TRIGGER update_product_view_count
AFTER INSERT ON product_views
FOR EACH ROW
BEGIN
    UPDATE products 
    SET view_count = view_count + 1 
    WHERE product_id = NEW.product_id;
END //

-- Trigger to update coupon usage count
CREATE TRIGGER update_coupon_usage_count
AFTER INSERT ON coupon_usage
FOR EACH ROW
BEGIN
    UPDATE coupons 
    SET used_count = used_count + 1 
    WHERE coupon_id = NEW.coupon_id;
END //

-- Trigger to update product sales count when order is completed
CREATE TRIGGER update_product_sales_count
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.status != 'delivered' AND NEW.status = 'delivered' THEN
        UPDATE products p
        INNER JOIN order_items oi ON p.product_id = oi.product_id
        SET p.total_sales = p.total_sales + oi.quantity
        WHERE oi.order_id = NEW.order_id;
    END IF;
END //

DELIMITER ;

-- ============================================================================
-- CREATE INDEXES FOR PERFORMANCE
-- ============================================================================

-- Additional indexes for better performance
CREATE INDEX idx_products_featured_active ON products(featured, status);
CREATE INDEX idx_products_category_status ON products(category_id, status);
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
CREATE INDEX idx_order_items_product ON order_items(product_id, order_id);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX idx_product_reviews_product_status ON product_reviews(product_id, status);

-- ============================================================================
-- FINAL NOTES
-- ============================================================================

/*
This new database schema includes:

1. Enhanced user management with security features
2. Comprehensive product management with variants and inventory tracking
3. Advanced order management with detailed tracking
4. Customer engagement features (reviews, wishlist, notifications)
5. Marketing tools (coupons, newsletters)
6. Analytics and reporting capabilities
7. System configuration and activity logging
8. Optimized performance with proper indexing
9. Data integrity with foreign key constraints
10. Useful views and stored procedures for common operations

Key improvements over the old schema:
- Better security and user management
- Product variants support
- Comprehensive inventory tracking
- Enhanced order management
- Advanced analytics capabilities
- Better performance optimization
- More flexible and scalable design

To migrate from the old database:
1. Export data from old tables
2. Run this new schema
3. Import and transform data to fit new structure
4. Update application code to use new table structure
*/

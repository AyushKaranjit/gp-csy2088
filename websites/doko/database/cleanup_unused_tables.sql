-- DOKO Database Cleanup Script
-- Remove unused tables and optimize schema
-- Created: August 17, 2025

USE doko_ecommerce;

-- ============================================================================
-- DROP UNUSED TABLES
-- ============================================================================

-- Tables that are completely unused
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS review_votes;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS coupon_usage;
DROP TABLE IF EXISTS newsletter_subscriptions;
DROP TABLE IF EXISTS product_views;
DROP TABLE IF EXISTS search_queries;

-- ============================================================================
-- OPTIMIZE REMAINING TABLES
-- ============================================================================

-- Add missing indexes for better performance
ALTER TABLE products ADD INDEX idx_view_count (view_count);
ALTER TABLE products ADD INDEX idx_total_sales (total_sales);

-- Add view tracking to products table (since product_views table was removed)
ALTER TABLE products ADD daily_views INT DEFAULT 0;
ALTER TABLE products ADD weekly_views INT DEFAULT 0;
ALTER TABLE products ADD monthly_views INT DEFAULT 0;

-- Add search tracking directly to a simple table
CREATE TABLE search_stats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    query VARCHAR(255) NOT NULL,
    results_count INT DEFAULT 0,
    search_date DATE DEFAULT (CURRENT_DATE),
    search_count INT DEFAULT 1,
    UNIQUE KEY unique_query_date (query, search_date)
);

-- ============================================================================
-- ADD MISSING FEATURES FOR EXISTING TABLES
-- ============================================================================

-- Ensure brands table has proper indexes
ALTER TABLE brands ADD INDEX idx_name (name);

-- Add more user tracking
ALTER TABLE users ADD last_activity TIMESTAMP NULL;
ALTER TABLE users ADD total_orders INT DEFAULT 0;
ALTER TABLE users ADD total_spent DECIMAL(10,2) DEFAULT 0.00;

-- Improve cart table with timestamps
ALTER TABLE cart ADD updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Improve wishlist with notes
ALTER TABLE wishlist ADD notes TEXT;
ALTER TABLE wishlist ADD priority ENUM('low', 'medium', 'high') DEFAULT 'medium';

-- Add product rating cache (since review_votes was removed)
ALTER TABLE products ADD avg_rating DECIMAL(3,2) DEFAULT 0.00;
ALTER TABLE products ADD total_reviews INT DEFAULT 0;

DELIMITER //

-- Trigger to update product rating when reviews are added/updated
CREATE TRIGGER update_product_rating AFTER INSERT ON product_reviews
FOR EACH ROW
BEGIN
    UPDATE products SET 
        avg_rating = (SELECT AVG(rating) FROM product_reviews WHERE product_id = NEW.product_id AND status = 'approved'),
        total_reviews = (SELECT COUNT(*) FROM product_reviews WHERE product_id = NEW.product_id AND status = 'approved')
    WHERE product_id = NEW.product_id;
END//

CREATE TRIGGER update_product_rating_update AFTER UPDATE ON product_reviews
FOR EACH ROW
BEGIN
    UPDATE products SET 
        avg_rating = (SELECT AVG(rating) FROM product_reviews WHERE product_id = NEW.product_id AND status = 'approved'),
        total_reviews = (SELECT COUNT(*) FROM product_reviews WHERE product_id = NEW.product_id AND status = 'approved')
    WHERE product_id = NEW.product_id;
END//

-- Trigger to update user stats when orders are created
CREATE TRIGGER update_user_stats AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    UPDATE users SET 
        total_orders = total_orders + 1,
        total_spent = total_spent + NEW.total_amount,
        last_activity = NOW()
    WHERE user_id = NEW.user_id;
END//

DELIMITER ;

-- ============================================================================
-- CLEAN UP OLD DATA
-- ============================================================================

-- Remove any orphaned data
DELETE FROM product_images WHERE product_id NOT IN (SELECT product_id FROM products);
DELETE FROM cart WHERE product_id NOT IN (SELECT product_id FROM products);
DELETE FROM wishlist WHERE product_id NOT IN (SELECT product_id FROM products);
DELETE FROM order_items WHERE product_id NOT IN (SELECT product_id FROM products);
DELETE FROM product_reviews WHERE product_id NOT IN (SELECT product_id FROM products);

-- Update existing product ratings
UPDATE products p SET 
    avg_rating = (SELECT COALESCE(AVG(rating), 0) FROM product_reviews WHERE product_id = p.product_id AND status = 'approved'),
    total_reviews = (SELECT COUNT(*) FROM product_reviews WHERE product_id = p.product_id AND status = 'approved');

-- Update user stats
UPDATE users u SET 
    total_orders = (SELECT COUNT(*) FROM orders WHERE user_id = u.user_id),
    total_spent = (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = u.user_id);

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================

-- Check remaining tables
SELECT TABLE_NAME, TABLE_ROWS, DATA_LENGTH 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'doko_ecommerce' 
ORDER BY TABLE_NAME;

-- Check for any foreign key issues
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM 
    information_schema.KEY_COLUMN_USAGE
WHERE 
    REFERENCED_TABLE_SCHEMA = 'doko_ecommerce'
    AND REFERENCED_TABLE_NAME IS NOT NULL;

SELECT 'Database cleanup completed successfully' as STATUS;

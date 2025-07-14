
CREATE DATABASE grocery_db;
GO
USE grocery_db;
GO

CREATE TABLE users (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role VARCHAR(10) NOT NULL DEFAULT 'customer',
    created_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT chk_role CHECK (role IN ('customer', 'admin'))
);

CREATE TABLE categories (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at DATETIME DEFAULT GETDATE()
);

CREATE TABLE products (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price NUMERIC(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    category_id INT,
    image VARCHAR(255),
    status VARCHAR(10) NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT chk_status CHECK (status IN ('active', 'inactive')),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE cart (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE orders (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT,
    total_amount NUMERIC(10,2) NOT NULL,
    status VARCHAR(10) NOT NULL DEFAULT 'pending',
    shipping_address TEXT,
    created_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT chk_order_status CHECK (status IN ('pending', 'processing', 'shipped', 'delivered', 'cancelled')),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id INT IDENTITY(1,1) PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price NUMERIC(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@graduation.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin'),
('ayush', 'ayush.2024105@nami.edu.np', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ayush Karanjit', 'customer');

INSERT INTO categories (name, description) VALUES
('Fruits', 'Fresh fruits and vegetables'),
('Vegetables', 'Fresh vegetables and greens'),
('Dairy', 'Milk, cheese, and dairy products'),
('Beverages', 'Drinks and beverages'),
('Snacks', 'Snacks and quick bites');

INSERT INTO products (name, description, price, stock_quantity, category_id) VALUES
('Fresh Apples', 'Red delicious apples', 150.00, 50, 1),
('Bananas', 'Fresh yellow bananas', 80.00, 100, 1),
('Milk', 'Fresh dairy milk 1L', 120.00, 30, 3),
('Tomatoes', 'Fresh red tomatoes', 60.00, 40, 2),
('Coca Cola', 'Soft drink 500ml', 45.00, 60, 4),
('Potato Chips', 'Crispy potato chips', 25.00, 80, 5);
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

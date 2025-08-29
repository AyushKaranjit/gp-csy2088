# DOKO E-Commerce Website

**Team Name:** Graduation

A modern e-commerce platform built with PHP, MySQL, and JavaScript for educational purposes by Team Graduation.

## Academic Integrity Statement

This website was developed as part of an academic project for educational purposes. All code was written by the student developers from Team Graduation to demonstrate web development skills and understanding of e-commerce systems. This project serves as a portfolio piece to showcase programming abilities and is not intended for commercial use.

**Team:** Graduation
**Author:** Team Graduation - Student Developers
**Date:** 2025
**Purpose:** Educational Project

## 📁 Project Structure

```
websites/doko/
├── public/                 # Web-accessible files
│   ├── api-*.php          # Direct API endpoints
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   ├── images/            # Static images
│   ├── uploads/           # User uploaded files
│   ├── *.php              # Main pages (index, login, products, etc.)
│   └── .htaccess          # Apache configuration
├── config/                # Configuration files
│   └── database.php       # Database connection
├── database/              # Database setup
│   ├── doko_schema.sql    # Database schema
│   └── setup.php          # Database initialization
├── src/                   # Backend source code
│   └── Models/            # Data models
└── template/              # Reusable templates
    ├── header.php         # Page header
    ├── footer.php         # Page footer
    └── config.php         # Template configuration
```

## 🚀 Features

- **User Authentication**: Register, login, logout
- **Product Catalog**: Browse products by categories
- **Shopping Cart**: Add, remove, update cart items
- **Wishlist**: Save favorite products
- **Responsive Design**: Mobile-friendly interface
- **API-driven**: Clean REST API architecture

## 🛠️ Main Pages

| File | Purpose |
|------|---------|
| `index.php` | Homepage |
| `products.php` | Product listing |
| `product-detail.php` | Individual product page |
| `cart.php` | Shopping cart |
| `wishlist.php` | User wishlist |
| `login.php` | User login |
| `register.php` | User registration |
| `checkout.php` | Order checkout |

## 🔌 API Endpoints

### Authentication
- `POST /api-auth-login.php` - User login
- `POST /api-auth-register.php` - User registration
- `POST /api-auth-logout.php` - User logout
- `GET /api-auth-profile.php` - Get user profile

### Products
- `GET /api-products-list.php` - List all products
- `GET /api-product-detail.php?id={id}` - Get product details
- `GET /api-products-featured.php` - Get featured products
- `GET /api-products-search.php?q={query}` - Search products

### Categories
- `GET /api-categories-list.php` - List all categories
- `GET /api-categories-detail.php?id={id}` - Get category details

### Shopping Cart
- `GET /api/cart/get.php` (or legacy `api-cart-get.php`) - Get cart contents
- `POST /api/cart/add.php` (preferred; legacy alias `cart-add.php`) - Add item to cart
- `PUT /api/cart/update.php` (legacy `api-cart-update.php`) - Update cart item
- `DELETE /api/cart/remove.php` (legacy `api-cart-remove.php`) - Remove cart item
- `DELETE /api/cart/clear.php` (legacy `api-cart-clear.php`) - Clear entire cart
* Cart (canonical): `add.php`, `update.php`, `remove.php`, `clear.php`, `get.php` under `/api/cart/` (all legacy wrappers removed)
* Wishlist: unified `/api/wishlist/wishlist.php` (GET list, POST add/toggle, DELETE remove). All former legacy variants have been removed after migration.

## 💾 Database

The application uses MySQL with the following main tables:
- `users` - User accounts
- `products` - Product catalog
- `categories` - Product categories
- `cart_items` - Shopping cart data
- `orders` - Order history

## 🎨 Frontend

- **CSS Framework**: Custom responsive design
- **JavaScript**: Vanilla JS with modular architecture
- **Icons**: Font Awesome
- **API Client**: Custom AJAX wrapper in `js/main.js`

## 🔧 Development

1. Start Docker containers: `docker-compose up -d`
2. Access the site at `http://localhost`
3. PhpMyAdmin available at `http://localhost:8080`

## 📝 Notes

- All API endpoints return JSON responses
- CORS headers are properly configured
- Error handling is implemented throughout
- File uploads are stored in `uploads/` directory
- Session management for user authentication

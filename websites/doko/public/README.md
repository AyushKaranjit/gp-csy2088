# DOKO - Online Grocery Store

A complete e-commerce platform for grocery shopping built with PHP, MySQL, and JavaScript.

## Project Team
- **Ayush Karanjit** - Project Lead & Backend Developer
- **Utsab Nepal** - Frontend Developer
- **Anuskar Shrestha** - Database Designer
- **Sandhya Thapa** - UI/UX Designer
- **Jesina Maharjan** - Quality Assurance

## Quick Start (Static Version)

🚀 **The website now works without a server!** Just open `index.html` in your browser to see the static version with sample products.

## To Run with Full PHP Backend

If you want to test the full PHP functionality (user authentication, dynamic products, etc.), you need to run a local server:

### Option 1: Using PHP Built-in Server (Recommended)
1. Open PowerShell/Command Prompt
2. Navigate to the project directory:
   ```
   cd "c:\Users\DEII\Downloads\gp-csy2088\websites\doko\public"
   ```
3. Start the PHP server:
   ```
   php -S localhost:8000
   ```
4. Open browser and go to: `http://localhost:8000`

### Option 2: Using XAMPP/WAMP
1. Install XAMPP or WAMP
2. Copy the `doko` folder to `htdocs` (XAMPP) or `www` (WAMP)
3. Start Apache and MySQL services
4. Go to: `http://localhost/doko/public`

## Features Working in Static Mode:
- ✅ Responsive design and navigation
- ✅ Product display (sample data)
- ✅ Cart functionality (localStorage)
- ✅ Wishlist functionality (localStorage)
- ✅ Search interface
- ✅ All page navigation
- ✅ Notifications

## Features Requiring PHP Server:
- 🔄 User authentication
- 🔄 Database integration
- 🔄 Dynamic product loading
- 🔄 Order processing
- 🔄 Real-time features

## Browser Compatibility:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Features
- ✅ User Authentication (Login/Register)
- ✅ Product Catalog with Categories
- ✅ Shopping Cart & Wishlist
- ✅ Order Management
- ✅ Payment Processing (COD, Cards, Bank Transfer)
- ✅ Admin Dashboard
- ✅ Responsive Design

## Technology Stack
- **Backend**: PHP (MVC Architecture)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Custom CSS with modern design
- **Icons**: Font Awesome

## Installation
1. Set up PHP server environment
2. Import `doko_database.sql` into MySQL
3. Configure database connection in `config/database.php`
4. Access the application via web browser

## Project Structure
```
doko/
├── config/          # Configuration files
├── src/             # PHP source code (MVC)
├── public/          # Public web files
└── templates/       # View templates
```

## Live Demo
https://ayushkaranjit.github.io/gp-csy2088-brief/index.html

## License
Educational Project - CSY2088 Module

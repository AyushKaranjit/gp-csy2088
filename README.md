# 🛒 DOKO - Online Grocery E-commerce Platform

A modern, responsive e-commerce website for grocery shopping built with PHP, featuring professional design inspired by platforms like Kirana and Daraz.

## 🌟 Features

### Customer Features
- **Modern UI/UX**: Professional orange and blue color scheme with gradient designs
- **Product Catalog**: Browse fresh groceries with high-quality images
- **Smart Search**: Find products quickly with real-time search
- **Shopping Cart**: Add, remove, and manage items before checkout
- **User Authentication**: Secure registration and login system
- **Checkout Process**: Complete order flow with multiple payment options
- **Order Tracking**: Track order status from placement to delivery
- **Mobile Responsive**: Optimized for all devices

### Admin Features
- **Dashboard**: Comprehensive overview with sales statistics
- **Product Management**: Add, edit, and manage product inventory
- **Order Management**: View and process customer orders
- **Customer Management**: Manage user accounts and customer data
- **Settings**: Configure store settings and preferences

### Technical Features
- **Security**: CSRF protection, input validation, XSS prevention
- **Image Service**: Dynamic product images via Unsplash API
- **Session Management**: Secure user sessions and cart persistence
- **Flash Messages**: User feedback system
- **SEO Optimized**: Meta tags, breadcrumbs, semantic HTML

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Quick Start

1. **Clone/Download** the project to your web server directory
2. **Database Setup**:
   ```sql
   -- Import the database schema
   mysql -u username -p < database/doko_schema.sql
   ```
3. **Configuration**:
   - Update `config/database.php` with your database credentials
   - Verify `template/config.php` settings
4. **Permissions**:
   ```bash
   chmod 755 public/
   chmod 644 public/*.php
   ```
5. **Access** the website:
   - Homepage: `http://yourdomain.com/public/`
   - Admin Panel: `http://yourdomain.com/public/admin.php`
   - System Test: `http://yourdomain.com/public/test.php`

### Admin Access
- **Username**: `admin`
- **Password**: `doko123`

## 📁 Project Structure

```
doko/
├── config/
│   └── database.php          # Database configuration
├── database/
│   └── doko_schema.sql       # Complete database schema
├── public/                   # Web-accessible files
│   ├── index.php            # Homepage
│   ├── products.php         # Product catalog
│   ├── cart.php             # Shopping cart
│   ├── login.php            # User login
│   ├── register.php         # User registration
│   ├── checkout.php         # Order checkout
│   ├── order-confirmation.php # Order confirmation
│   ├── about.php            # About us page
│   ├── contact.php          # Contact form
│   ├── offers.php           # Special offers
│   ├── admin.php            # Admin dashboard
│   ├── test.php             # System test page
│   ├── css/
│   │   └── style.css        # Main stylesheet
│   ├── js/
│   │   ├── main.js          # Core JavaScript
│   │   └── mobile-nav.js    # Mobile navigation
│   └── images/              # Static images
├── src/                     # Application logic
│   ├── Controllers/
│   │   └── api/            # API endpoints
│   ├── Core/               # Core functionality
│   └── Models/             # Data models
└── template/               # Reusable components
    ├── config.php          # Global configuration
    ├── header.php          # Site header
    ├── footer.php          # Site footer
    ├── breadcrumb.php      # Navigation breadcrumbs
    ├── product-card.php    # Product display component
    └── image-service.php   # Dynamic image service
```

## 🎨 Design System

### Color Palette
- **Primary**: #ff6b35 (Orange) - CTA buttons, highlights
- **Accent**: #2563eb (Blue) - Links, secondary elements
- **Success**: #22c55e (Green) - Success messages, positive actions
- **Error**: #ef4444 (Red) - Error messages, warnings
- **Dark Text**: #1f2937 - Primary text color
- **Light Text**: #6b7280 - Secondary text color

### Typography
- **Font Family**: System fonts (Arial, Helvetica, sans-serif)
- **Font Weights**: 400 (normal), 600 (semi-bold), 700 (bold)
- **Responsive Scaling**: Fluid typography for all screen sizes

### Components
- **Buttons**: Gradient backgrounds with hover animations
- **Cards**: Soft shadows with rounded corners
- **Navigation**: Modern hamburger menu for mobile
- **Forms**: Clean inputs with focus states
- **Badges**: Rounded status indicators

## 🔧 Development

### Key Technologies
- **Backend**: PHP 8.x
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Database**: MySQL 8.0
- **Styling**: CSS Grid, Flexbox, CSS Variables
- **Icons**: Font Awesome 6.0
- **Images**: Unsplash API integration

### Code Organization
- **MVC Pattern**: Separation of concerns
- **Component-Based**: Reusable template components
- **Security First**: Input validation and sanitization
- **Mobile First**: Responsive design approach

### Configuration Functions
```php
// Site configuration
site_name()              // Returns "DOKO"
site_url()               // Returns base URL
page_title($title)       // Formats page titles

// Security functions
generate_csrf_token()    // CSRF protection
verify_csrf_token($token) // Token validation
clean_output($string)    // XSS prevention

// Image functions
product_image($name)     // Dynamic product images
category_image($name)    // Category images

// User functions
set_flash_message($type, $message) // User notifications
display_flash_message() // Show notifications
```

## 📱 Pages Overview

### Public Pages
1. **Homepage** (`index.php`)
   - Hero section with search
   - Featured categories
   - Popular products
   - Special offers

2. **Products** (`products.php`)
   - Product grid with filters
   - Category navigation
   - Search functionality
   - Product details

3. **Shopping Cart** (`cart.php`)
   - Item management
   - Quantity updates
   - Price calculations
   - Checkout button

4. **Checkout** (`checkout.php`)
   - Customer information
   - Delivery options
   - Payment methods
   - Order summary

5. **Authentication**
   - Login page with validation
   - Registration with email verification
   - Password security

### Admin Panel
- **Dashboard**: Sales overview and statistics
- **Products**: CRUD operations for inventory
- **Orders**: Order processing and status updates
- **Customers**: User management
- **Settings**: Store configuration

## 🛡️ Security Features

- **CSRF Protection**: All forms protected with tokens
- **Input Validation**: Server-side validation for all inputs
- **XSS Prevention**: Output sanitization
- **SQL Injection Prevention**: Prepared statements
- **Session Security**: Secure session handling
- **Password Security**: Hashed password storage

## 📊 Testing

Run the system test page (`test.php`) to verify:
- ✅ Configuration setup
- ✅ Image service functionality
- ✅ Security features
- ✅ Session management
- ✅ File availability
- ✅ Template integration

## 🚀 Deployment

### Production Checklist
- [ ] Update database credentials
- [ ] Enable HTTPS
- [ ] Configure error reporting
- [ ] Set up backups
- [ ] Optimize images
- [ ] Enable caching
- [ ] Test all functionality

### Performance Optimization
- Minify CSS and JavaScript
- Optimize images
- Enable gzip compression
- Use CDN for static assets
- Database indexing

## 🔮 Future Enhancements

### Planned Features
- [ ] Real-time order tracking
- [ ] Push notifications
- [ ] Advanced search filters
- [ ] Customer reviews and ratings
- [ ] Wishlist functionality
- [ ] Inventory alerts
- [ ] Multi-language support
- [ ] Mobile app API

### Technical Improvements
- [ ] Implement caching layer
- [ ] Add unit tests
- [ ] API documentation
- [ ] Performance monitoring
- [ ] Automated deployments

## 🤝 Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature-name`
3. Commit changes: `git commit -am 'Add feature'`
4. Push to branch: `git push origin feature-name`
5. Submit pull request

### Development Guidelines
- Follow PSR-12 coding standards
- Write descriptive commit messages
- Test all functionality before submission
- Update documentation for new features

## 📞 Support

For technical support or questions:
- **Email**: support@doko.com.np
- **Phone**: +977-1-4567890
- **Documentation**: Check project files and comments

## 📄 License

This project is developed for educational purposes as part of CSY2088 coursework.

---

**Built with ❤️ by DOKO Team**  
*Nepal's Premier Online Grocery Platform*

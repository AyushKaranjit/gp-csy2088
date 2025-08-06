# DOKO E-commerce Project - CSY2088 Group Project

## Project Overview

**DOKO** is a comprehensive e-commerce grocery application developed for the CSY2088 Group Project module. The application provides a complete online shopping experience with features for customers, managers, and administrators.

### Faculty Information
- **Faculty**: Faculty of Art, Science and Technology - Field of Computing
- **Module**: CSY2088 | Group Project – BSc (Hons) Computer Science
- **Level**: Level 5
- **Credits**: 20
- **Assessment**: AS1 (100%)
- **Submission Date**: 23:59, 5th September 2025

---

## 🎯 Project Objectives

The DOKO e-commerce platform aims to:
1. Provide a user-friendly online grocery shopping experience
2. Implement secure user authentication and authorization
3. Enable comprehensive product and inventory management
4. Support multiple user roles (Customer, Manager, Admin)
5. Ensure responsive design for all devices
6. Implement secure payment processing workflows
7. Provide comprehensive testing and quality assurance

---

## 🏗️ System Architecture

### Technology Stack
- **Backend**: PHP 8.1+
- **Database**: MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Server**: Apache/Nginx
- **Development Environment**: XAMPP/WAMP/Docker

### Design Patterns
- **MVC Architecture**: Model-View-Controller separation
- **Singleton Pattern**: Database connection management
- **Repository Pattern**: Data access layer abstraction
- **Factory Pattern**: Object creation management

---

## 📋 Features Implementation

### ✅ Completed Features

#### 1. User Management System
- **User Registration**: Complete signup process with validation
- **User Authentication**: Secure login/logout functionality
- **Role Management**: Customer, Manager, Admin roles
- **Profile Management**: User profile updates and management
- **Session Security**: Secure session handling and timeout

#### 2. Product Management
- **Product Catalog**: Complete product listing with categories
- **Product CRUD**: Create, Read, Update, Delete operations
- **Inventory Management**: Stock tracking and low-stock alerts
- **Product Search**: Advanced search with filters
- **Product Images**: Image upload and management

#### 3. Shopping Cart System
- **Add to Cart**: Products can be added to shopping cart
- **Cart Management**: Update quantities, remove items
- **Session Cart**: Guest user cart functionality
- **Cart Persistence**: User cart saved across sessions

#### 4. Order Management
- **Order Creation**: Complete checkout process
- **Order Tracking**: Order status updates and tracking
- **Order History**: Customer order history viewing
- **Admin Order Management**: Full order lifecycle management

#### 5. Category Management
- **Category CRUD**: Complete category management
- **Hierarchical Categories**: Parent-child category relationships
- **Category Filtering**: Product filtering by categories

#### 6. Admin Dashboard
- **Dashboard Analytics**: Sales and inventory statistics
- **User Management**: Admin user management interface
- **Product Management**: Admin product management
- **Order Management**: Admin order processing

### 🚧 Features Coming Soon

#### 1. Payment Integration
- **eSewa Integration**: Nepal's popular payment gateway
- **Khalti Integration**: Alternative payment method
- **Cash on Delivery**: Traditional payment option
- **Payment Status Tracking**: Complete payment lifecycle

#### 2. Enhanced User Features
- **Wishlist Management**: Save favorite products
- **Product Reviews**: Customer product reviews and ratings
- **Advanced Search**: Full-text search with autocomplete
- **Email Notifications**: Order confirmations and updates

#### 3. Inventory & Supply Chain
- **Supplier Management**: Supplier relationship management
- **Purchase Orders**: Automated inventory replenishment
- **Stock Alerts**: Automated low-stock notifications
- **Inventory Reports**: Comprehensive inventory analytics

#### 4. Marketing Features
- **Coupon System**: Discount codes and promotions
- **Newsletter**: Email marketing campaigns
- **Product Recommendations**: AI-powered suggestions
- **Loyalty Program**: Customer reward system

#### 5. Advanced Analytics
- **Sales Analytics**: Detailed sales reporting
- **Customer Analytics**: Customer behavior analysis
- **Inventory Analytics**: Stock movement analysis
- **Financial Reports**: Revenue and profit analysis

#### 6. Mobile & API Features
- **REST API**: Complete API for mobile apps
- **API Documentation**: Comprehensive API docs
- **Mobile Responsiveness**: Enhanced mobile experience
- **Push Notifications**: Real-time notifications

---

## 🗄️ Database Design

### Core Tables
1. **users** - User account information
2. **categories** - Product categories
3. **products** - Product information
4. **cart** - Shopping cart items
5. **orders** - Customer orders
6. **order_items** - Order line items
7. **product_images** - Product image management
8. **user_addresses** - Customer delivery addresses
9. **product_reviews** - Customer reviews
10. **coupons** - Discount management

### Key Relationships
- Users have many Orders
- Orders have many Order Items
- Products belong to Categories
- Products have many Images
- Users have many Addresses
- Products have many Reviews

---

## 🧪 Testing Implementation

### Unit Tests ✅
- **Authentication Functions**: Login, registration, password validation
- **Product Functions**: CRUD operations, search functionality
- **Cart Functions**: Add, remove, update cart items
- **Database Functions**: Connection, queries, transactions
- **Validation Functions**: Email, password, input validation

### Integration Tests ✅
- **User Journey**: Registration → Login → Shopping → Checkout
- **Admin Workflow**: Login → Manage Products → Process Orders
- **Shopping Flow**: Browse → Add to Cart → Checkout → Order
- **API Integration**: Frontend ↔ Backend communication

### Database Tests ✅
- **CRUD Operations**: Create, Read, Update, Delete functionality
- **Transaction Integrity**: Rollback and commit operations
- **Constraint Validation**: Foreign key and data constraints
- **Performance Tests**: Query optimization and indexing

### Security Tests ✅
- **SQL Injection Protection**: Prepared statements validation
- **Authentication Security**: Session management and timeout
- **Access Control**: Role-based permissions
- **Input Sanitization**: XSS and injection prevention

### System Tests ✅
- **Cross-browser Testing**: Chrome, Firefox, Safari compatibility
- **Responsive Design**: Mobile, tablet, desktop layouts
- **Performance Testing**: Load time and optimization
- **Error Handling**: Graceful error management

---

## 📊 Test Results Summary

| Test Category | Total Tests | Passed | Failed | Success Rate |
|---------------|-------------|--------|--------|--------------|
| Unit Tests | 8 | 8 | 0 | 100% |
| Integration Tests | 4 | 4 | 0 | 100% |
| Database Tests | 6 | 6 | 0 | 100% |
| Security Tests | 5 | 5 | 0 | 100% |
| System Tests | 4 | 4 | 0 | 100% |
| **TOTAL** | **27** | **27** | **0** | **100%** |

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Composer (for dependencies)

### Installation Steps
1. **Clone/Download** the project files
2. **Configure Database** - Update `config/database.php`
3. **Run Database Setup** - Execute `database/setup.php`
4. **Configure Web Server** - Point to `public/` directory
5. **Set Permissions** - Ensure proper file permissions
6. **Run Tests** - Execute `testing.php` to verify setup

### Default Login Credentials
- **Admin**: admin@doko.com / admin123
- **Customer**: user@doko.com / user123

---

## 📁 Project Structure

```
DOKO/
├── websites/doko/
│   ├── config/
│   │   └── database.php          # Database configuration
│   ├── database/
│   │   ├── doko_schema.sql       # Complete database schema
│   │   ├── setup.php             # Web-based setup
│   │   └── setup-cli.php         # Command-line setup
│   ├── public/                   # Web-accessible files
│   │   ├── index.php             # Homepage
│   │   ├── admin.php             # Admin dashboard
│   │   ├── login.php             # Login page
│   │   ├── register.php          # Registration page
│   │   ├── products.php          # Product catalog
│   │   ├── cart.php              # Shopping cart
│   │   ├── checkout.php          # Checkout process
│   │   ├── api/                  # API endpoints
│   │   │   ├── auth-login.php    # Authentication API
│   │   │   ├── auth-register.php # Registration API
│   │   │   ├── products.php      # Products API
│   │   │   ├── cart.php          # Cart API
│   │   │   ├── orders.php        # Orders API
│   │   │   └── categories.php    # Categories API
│   │   ├── css/
│   │   │   └── style.css         # Main stylesheet
│   │   ├── js/
│   │   │   └── main.js           # Main JavaScript
│   │   └── uploads/              # File uploads
│   ├── src/
│   │   ├── Controllers/
│   │   │   └── AuthController.php # Authentication logic
│   │   └── Models/
│   │       ├── User.php          # User model
│   │       └── NewUser.php       # Enhanced user model
│   ├── template/
│   │   ├── config.php            # Template configuration
│   │   ├── header.php            # Page header
│   │   ├── footer.php            # Page footer
│   │   └── product-card.php      # Product display component
│   └── testing.php               # Comprehensive test suite
```

---

## 🔐 Security Implementation

### Authentication & Authorization
- **Password Hashing**: bcrypt with salt
- **Session Management**: Secure session handling
- **Role-based Access**: Customer/Manager/Admin roles
- **CSRF Protection**: Cross-site request forgery prevention

### Data Security
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization and output encoding
- **Input Validation**: Server-side validation
- **File Upload Security**: Type and size restrictions

### Privacy & Compliance
- **Data Encryption**: Sensitive data protection
- **Privacy Policy**: GDPR compliance considerations
- **Audit Logging**: Activity tracking
- **Secure Headers**: Security-focused HTTP headers

---

## 📈 Performance Optimization

### Database Optimization
- **Indexing**: Strategic database indexing
- **Query Optimization**: Efficient SQL queries
- **Connection Pooling**: Database connection management
- **Caching**: Result caching where appropriate

### Frontend Optimization
- **Minification**: CSS and JS compression
- **Image Optimization**: Compressed images
- **Lazy Loading**: Progressive content loading
- **CDN Ready**: Content delivery network support

---

## 🤝 Team Collaboration

### Development Process
- **Version Control**: Git-based development workflow
- **Code Reviews**: Peer review process
- **Testing Strategy**: Continuous testing approach
- **Documentation**: Comprehensive code documentation

### Quality Assurance
- **Code Standards**: PSR coding standards
- **Error Handling**: Comprehensive error management
- **Logging**: Application logging and monitoring
- **Performance Monitoring**: System performance tracking

---

## 📚 Learning Outcomes Achieved

### Technical Skills
- **PHP Development**: Advanced PHP programming
- **Database Design**: MySQL schema design and optimization
- **Web Technologies**: HTML5, CSS3, JavaScript ES6+
- **API Development**: RESTful API design and implementation
- **Testing**: Unit, integration, and system testing

### Project Management
- **Requirements Analysis**: Stakeholder requirement gathering
- **System Design**: Architecture and database design
- **Implementation Planning**: Feature prioritization and delivery
- **Quality Assurance**: Testing and validation strategies
- **Documentation**: Technical and user documentation

### Collaboration Skills
- **Team Communication**: Regular team meetings and updates
- **Code Collaboration**: Version control and code reviews
- **Problem Solving**: Issue identification and resolution
- **Time Management**: Meeting deadlines and milestones

---

## 🔮 Future Enhancements

### Short-term (Next 3 months)
1. Complete payment gateway integration
2. Implement wishlist and reviews system
3. Add email notification system
4. Enhance mobile responsiveness
5. Implement inventory analytics

### Medium-term (3-6 months)
1. Develop mobile API
2. Add recommendation engine
3. Implement loyalty program
4. Add multi-vendor support
5. Enhance security features

### Long-term (6+ months)
1. AI-powered personalization
2. Advanced analytics dashboard
3. Multi-language support
4. Integration with delivery services
5. Machine learning recommendations

---

## 📞 Support & Contact

For technical support or questions about this project:

- **Project Repository**: [GitHub Repository Link]
- **Documentation**: Available in `/docs` folder
- **Issue Tracking**: GitHub Issues
- **Team Contact**: [Team Email/Contact Information]

---

## 📄 License & Attribution

This project is developed as part of the CSY2088 Group Project coursework at the University of Northampton. All code and documentation are original work created specifically for this assessment.

**Academic Integrity Statement**: This work represents original development by the project team and adheres to the University's Academic Integrity Policy. All external resources, libraries, and references have been properly documented.

---

*Last Updated: August 6, 2025*
*Version: 1.0.0*
*Status: Production Ready with Future Enhancements Planned*

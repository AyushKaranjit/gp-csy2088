# DOKO Manager Dashboard

This document describes the Manager user role and dashboard functionality added to the DOKO Grocery E-commerce system.

## Overview

The Manager role provides an intermediate level of access between Customer and Admin users. Managers can view and manage orders and products but have limited access compared to full administrators.

## Features

### Manager Dashboard
- **Order Statistics**: View pending, processing, and completed orders
- **Product Statistics**: Track total products, low stock items, and out of stock products  
- **Quick Actions**: Fast access to common management tasks
- **Recent Orders**: Overview of latest customer orders
- **Top Selling Products**: Monthly sales performance tracking

### Order Management
- **View All Orders**: Paginated list with filtering by status, customer, and date
- **Order Details**: Complete order information including customer details and items
- **Status Updates**: Change order status (pending, processing, shipped, delivered, cancelled)
- **Search & Filter**: Find orders by customer name, email, or order ID
- **Print Orders**: Generate printable order summaries

### Product Management
- **Product Catalog**: View all products with images, pricing, and stock levels
- **Add New Products**: Create new product listings with categories and descriptions
- **Edit Products**: Update existing product information (when edit.php is created)
- **Stock Monitoring**: Track inventory levels and identify low stock items
- **Status Management**: Activate, deactivate, or mark products as out of stock
- **Category Filtering**: Filter products by category, status, and stock level

## User Access Levels

### Manager Permissions
- ✅ View and manage orders
- ✅ View and manage products
- ✅ Update order statuses
- ✅ Add new products
- ✅ Edit existing products
- ✅ View sales statistics
- ❌ Manage users
- ❌ Access admin settings
- ❌ View financial reports (admin only)

### Admin Permissions (for comparison)
- ✅ All Manager permissions
- ✅ Manage users and roles
- ✅ Access system settings
- ✅ View detailed financial reports
- ✅ Manage categories
- ✅ System configuration

## File Structure

```
public/
├── manager.php                     # Manager entry point (redirects to dashboard)
├── manager/
    ├── index.php                   # Manager main index (redirects to dashboard)
    ├── dashboard/
    │   └── index.php              # Main manager dashboard
    ├── orders/
    │   ├── index.php              # Orders listing and management
    │   ├── view.php               # Individual order details
    │   └── print.php              # Print order (to be created)
    ├── products/
    │   ├── index.php              # Products listing and management
    │   ├── add.php                # Add new product form
    │   └── edit.php               # Edit product form (to be created)
    └── shared/
        ├── header.php             # Manager navigation and header
        └── footer.php             # Manager footer
```

## Authentication Updates

### AuthController.php
Added new methods:
- `isManager()` - Check if user has manager role
- `hasManagerAccess()` - Check if user has manager or admin access
- Updated login redirect to include manager.php

### Login Flow
1. User logs in with manager credentials
2. System checks role and redirects to manager.php
3. manager.php redirects to manager/dashboard/
4. Manager dashboard loads with role-appropriate permissions

## Database Integration

### User Roles
The system supports these user roles in the `users` table:
- `customer` - Regular customers
- `manager` - Store managers (new)
- `admin` - System administrators
- `vendor` - Product vendors (existing but not implemented)

### Manager User Creation
Run the seed script to create a default manager user:
```bash
php database/seed_manager_user.php
```

Default manager credentials:
- Email: manager@doko.com
- Password: manager123

## Security Features

- **Role-based Access**: Only managers and admins can access manager pages
- **Session Management**: Proper session handling with role verification
- **Input Validation**: Form validation and SQL injection prevention
- **CSRF Protection**: Consider adding CSRF tokens for enhanced security

## Responsive Design

The manager interface is fully responsive and optimized for:
- Desktop computers (primary use case)
- Tablets (landscape and portrait)
- Mobile phones (basic functionality)

## Future Enhancements

### Planned Features
1. **Product Image Upload**: Allow managers to upload product images
2. **Bulk Product Actions**: Edit multiple products simultaneously
3. **Order Notes**: Add internal notes to orders
4. **Inventory Alerts**: Email notifications for low stock
5. **Sales Reports**: Basic sales reporting for managers
6. **Customer Communication**: Send order status updates to customers

### Technical Improvements
1. **AJAX Updates**: Real-time status updates without page refresh
2. **Export Functions**: Export orders and products to CSV/Excel
3. **Advanced Search**: More sophisticated filtering options
4. **Audit Log**: Track manager actions for accountability
5. **API Integration**: REST API for mobile manager app

## Usage Guide

### For Store Managers

1. **Login**: Use your manager credentials to access the system
2. **Dashboard**: Review daily statistics and recent activity
3. **Orders**: 
   - Check pending orders that need processing
   - Update order statuses as items are prepared and shipped
   - View customer details and order history
4. **Products**:
   - Monitor stock levels and reorder when necessary
   - Add new products as inventory arrives
   - Update product information and pricing
   - Deactivate discontinued items

### For System Administrators

1. **Manager Setup**: Create manager users through admin panel or database
2. **Permissions**: Managers inherit appropriate permissions automatically
3. **Monitoring**: Admin retains full access to all manager functions
4. **Support**: Assist managers with training and troubleshooting

## Troubleshooting

### Common Issues

1. **Access Denied**: Ensure user role is set to 'manager' or 'admin'
2. **Dashboard Not Loading**: Check database connection and table existence
3. **Orders Not Updating**: Verify order status values match database constraints
4. **Products Not Saving**: Check required fields and category existence

### Error Logging
Errors are logged to PHP error log. Check:
- Database connection issues
- Missing required fields
- Permission problems
- Session management errors

## Integration Notes

The manager functionality integrates seamlessly with existing DOKO features:
- Uses existing database schema (products, orders, users, categories)
- Maintains compatibility with customer and admin interfaces
- Follows established coding patterns and security practices
- Responsive design matches existing UI/UX standards

## Support

For technical support or feature requests:
1. Check error logs for specific issues
2. Verify database schema and permissions
3. Test with different user roles
4. Review authentication flow
5. Contact system administrator if needed

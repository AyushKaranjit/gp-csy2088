# API Folder Structure

## Organized API Endpoints

### Admin API (`/api/admin/`)
- **Dashboard**: `/api/admin/dashboard/`
- **Products**: `/api/admin/products/` 
- **Users**: `/api/admin/users/`
- **Orders**: `/api/admin/orders/`

### Public API Endpoints

#### Authentication (`/api/auth/`)
- Login, logout, registration endpoints

#### Products (`/api/products/`)
- `product-detail.php` - Get single product details
- `products-featured.php` - Get featured products
- `products-search.php` - Search products
- `inventory-list.php` - Get inventory list
- `stock-update.php` - Update product stock
- `product-image-upload.php` - Upload product images

#### Categories (`/api/categories/`)
- `categories.php` - Get all categories
- `categories-list.php` - Get categories list
- `categories-detail.php` - Get category details

#### Cart (`/api/cart/`)
- `cart-add-simple.php` - Add items to cart
- `cart-add-working.php` - Working cart implementation
- `cart-add-simple-backup.php` - Backup cart functionality

#### Orders (`/api/orders/`)
- `orders.php` - Order management
- `orders-list.php` - Get orders list

#### Users (`/api/users/`)
- `users-list.php` - Get users list
- `customer-orders.php` - Get customer orders

#### Wishlist (`/api/wishlist/`)
- `wishlist.php` - Wishlist management
- `wishlist-toggle.php` - Toggle wishlist items
- `wishlist-get.php` - Get wishlist items
- `wishlist-simple.php` - Simple wishlist functionality
- `wishlist-backup.php` - Backup wishlist

#### Uploads (`/api/uploads/`)
- `image-upload.php` - General image upload functionality

## Path Configuration

All files have been updated to use correct relative paths:
- Config files: `../../config/` or `../../../config/` depending on depth
- Templates: `../../template/` or `../../../template/` depending on depth  
- Uploads: `../../uploads/` or `../../../uploads/` depending on depth
- CSS/JS: Handled dynamically by admin-header.php template

## Admin Header Template

The `admin-header.php` template automatically detects the current directory structure and sets appropriate paths for:
- CSS files
- JavaScript files  
- Image files
- Navigation links

Supports both `/admin/` and `/api/admin/` folder structures.

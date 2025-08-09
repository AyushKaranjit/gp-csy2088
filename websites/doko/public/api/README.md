# API Folder Structure

## Organized API Endpoints

### Admin API (`/api/admin/`)
- Dashboard UI: `/api/admin/dashboard/`
- Metrics JSON: `admin/metrics/summary.php`
- Legacy (deprecated JSON listings):
	- `admin/admin-users.php` (use `/api/users/users-list.php`)
	- `admin/admin-products.php` (use `/api/products/list.php`)
	- `admin/admin-orders.php` (use `/api/orders/orders-list.php`)

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
- `get.php` (canonical cart fetch)
- `add.php` (add line)
- `update.php` (update quantity)
- `remove.php` (remove line)
- `clear.php` (clear cart)
- Removed all deprecated experimental and wrapper variants (`cart-add-*`, `cart-*.php` duplicates) to reduce clutter.

#### Orders (`/api/orders/`)
- `orders-list.php` - Paginated list
- `order-detail.php` - Single order
- `update-status.php` - Admin status update
- `cancel-order.php` - Cancel order
- `orders.php` - DEPRECATED (returns 410)

#### Users (`/api/users/`)
- `auth-login.php` / `logout.php` / `register.php`
- `status.php` (session status)
- `profile.php` (get profile)
- `auth-profile.php` (update profile)
- `change-password.php`
- `addresses.php`
- `customer-orders.php`
- `users-list.php` (admin advanced list + metrics)
- Deprecated wrappers: `auth-login|auth-logout|auth-register|auth-status` (kept temporarily)

#### Wishlist (`/api/wishlist/`)
- `wishlist.php` - Unified wishlist API (GET list, POST add/toggle, DELETE remove)
- Legacy stubs removed (toggle/get/add/remove/simple/backup) after migration window.

#### Uploads (`/api/uploads/`)
- `image-upload.php` - General image upload functionality

## Standardization & Deprecations

All active endpoints share `_bootstrap.php` and `ApiResponse` for consistency.

Deprecation Strategy:
- Deprecated endpoints returned HTTP 410 during migration window and are now removed (wishlist legacy set, experimental cart variants).
- All cart wrappers removed after test migration.
- Future removals pending: deprecated admin * listings, wrapper auth-* endpoints after confirmation.

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

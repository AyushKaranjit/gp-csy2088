<?php
/**
// Check if we're in a subdirectory (like api/ or admin/)
if (strpos($current_dir, '/api/admin') !== false) {
    // Handle api/admin folder structure: /api/admin/dashboard/, /api/admin/products/, etc.
    $css_path = '../../../css/';
    $js_path = '../../../js/';
    $images_path = '../../../images/';
} elseif (strpos($current_dir, '/api') !== false) {
    // Handle regular api folder structure  
    $css_path = '../css/';
    $js_path = '../js/';
    $images_path = '../images/';
} elseif (strpos($current_dir, '/admin') !== false) {
    // Handle admin folder structure: /admin/dashboard/, /admin/products/, etc.
    $css_path = '../../css/';
    $js_path = '../../js/';
    $images_path = '../../images/';
}ader Template
 * Shared navigation and styling for all admin pages
 */

// Determine the correct paths based on current location
$current_dir = dirname($_SERVER['SCRIPT_NAME']);
$css_path = 'css/';
$js_path = 'js/';
$images_path = 'images/';

// Check if we're in a subdirectory (like api/ or admin/)
if (strpos($current_dir, '/api') !== false) {
    $css_path = '../css/';
    $js_path = '../js/';
    $images_path = '../images/';
} elseif (strpos($current_dir, '/admin') !== false) {
    // Handle admin folder structure: /admin/dashboard/, /admin/products/, etc.
    $css_path = '../../css/';
    $js_path = '../../js/';
    $images_path = '../../images/';
}

// Determine current admin page for navigation highlighting
$current_admin_page = 'dashboard';
if (strpos($_SERVER['SCRIPT_NAME'], '/products/') !== false || strpos($_SERVER['SCRIPT_NAME'], 'admin-products.php') !== false) {
    $current_admin_page = 'products';
} elseif (strpos($_SERVER['SCRIPT_NAME'], '/users/') !== false || strpos($_SERVER['SCRIPT_NAME'], 'admin-users.php') !== false) {
    $current_admin_page = 'users';
} elseif (strpos($_SERVER['SCRIPT_NAME'], '/orders/') !== false || strpos($_SERVER['SCRIPT_NAME'], 'admin-orders.php') !== false) {
    $current_admin_page = 'orders';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Dashboard | DOKO'; ?></title>
    <meta name="description" content="DOKO Admin Panel - Manage your online grocery store efficiently">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $images_path; ?>favicon.ico">
    
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome and Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Admin Styles -->
    <style>
        /* Prevent any flash of home page content */
        body {
            visibility: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #f8fafc !important;
            font-family: 'Inter', sans-serif !important;
        }
        
        body.admin-ready {
            visibility: visible !important;
        }
        
        /* Hide ALL regular header elements completely */
        .header,
        .navbar,
        .header-main,
        .header-actions,
        .header-action,
        .navigation,
        .logo,
        .logo-container,
        .main-nav,
        .header-content,
        .search-container,
        .user-actions,
        .breadcrumb,
        .nav-container {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            overflow: hidden !important;
            position: absolute !important;
            left: -9999px !important;
        }
        
        /* CSS Custom Properties - Professional Admin Theme */
        :root {
            --primary-color: #1e293b;
            --primary-light: #334155;
            --primary-dark: #0f172a;
            --primary-gradient: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            --accent-color: #3b82f6;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
            --info-color: #3b82f6;
            --background-color: #f8fafc;
            --background-secondary: #f1f5f9;
            --card-background: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --border-light: #f1f5f9;
            --hover-color: #f8fafc;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --radius-2xl: 20px;
            --transition-fast: all 0.15s ease;
            --transition-base: all 0.2s ease;
            --transition-slow: all 0.3s ease;
        }
        
        /* Admin Navigation Bar */
        .admin-navbar {
            background: var(--primary-color);
            box-shadow: var(--shadow-lg);
            position: sticky;
            top: 0;
            z-index: 1000;
            margin: 0;
            padding: 0;
        }
        
        .admin-navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }
        
        /* Admin Logo with DOKO Traditional Basket */
        .admin-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            transition: var(--transition-fast);
        }
        
        .admin-logo:hover {
            color: #e8f5e8;
            transform: translateX(2px);
        }
        
        .admin-logo-icon {
            position: relative;
            width: 40px;
            height: 40px;
        }
        
        /* Traditional Basket Design */
        .doko-basket {
            width: 100%;
            height: 100%;
            position: relative;
        }
        
        .basket-body {
            width: 32px;
            height: 24px;
            background: linear-gradient(45deg, 
                rgba(255,255,255,0.9) 25%, 
                transparent 25%, 
                transparent 50%, 
                rgba(255,255,255,0.9) 50%, 
                rgba(255,255,255,0.9) 75%, 
                transparent 75%);
            background-size: 6px 6px;
            border: 2px solid rgba(255,255,255,0.95);
            border-radius: 0 0 8px 8px;
            position: relative;
            margin: 6px auto 0;
        }
        
        .basket-handles {
            position: absolute;
            top: 6px;
            width: 100%;
            height: 12px;
        }
        
        .basket-handle {
            width: 8px;
            height: 12px;
            border: 2px solid rgba(255,255,255,0.95);
            border-bottom: none;
            border-radius: 6px 6px 0 0;
            position: absolute;
            top: 0;
        }
        
        .basket-handle.left {
            left: 4px;
        }
        
        .basket-handle.right {
            right: 4px;
        }
        
        .admin-logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }
        
        .logo-main-text {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        
        .logo-sub-text {
            font-size: 0.75rem;
            font-weight: 400;
            opacity: 0.9;
            margin-top: -2px;
        }
        
        /* Navigation Links */
        .admin-nav-links {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            border-radius: var(--radius-md);
            transition: var(--transition-fast);
            position: relative;
            white-space: nowrap;
        }
        
        .admin-nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.8);
            transform: translateX(-50%);
            transition: var(--transition-fast);
        }
        
        .admin-nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateY(-1px);
        }
        
        .admin-nav-link:hover::before {
            width: 80%;
        }
        
        .admin-nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            font-weight: 600;
        }
        
        .admin-nav-link.active::before {
            width: 80%;
        }
        
        .admin-nav-link i {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .admin-navbar .container {
                flex-wrap: wrap;
                height: auto;
                padding: 1rem;
            }
            
            .admin-nav-links {
                flex-wrap: wrap;
                gap: 0.25rem;
                margin-top: 1rem;
                width: 100%;
            }
            
            .admin-nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
                flex: 1;
                justify-content: center;
                min-width: 120px;
            }
            
            .logo-sub-text {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .admin-logo-text {
                display: none;
            }
            
            .admin-nav-link span {
                display: none;
            }
            
            .admin-nav-link {
                min-width: 50px;
                justify-content: center;
            }
        }
        
        /* Admin Container */
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: calc(100vh - 70px);
        }
        
        /* Page loading indicator */
        .page-loader {
            position: fixed;
            top: 70px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary-light);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 999;
        }
        
        .page-loader.loading {
            animation: loading 1s ease-in-out infinite;
        }
        
        @keyframes loading {
            0% { transform: translateX(-100%); }
            50% { transform: translateX(0); }
            100% { transform: translateX(100%); }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader" id="pageLoader"></div>
    
    <!-- Admin Navigation Bar -->
    <nav class="admin-navbar">
        <div class="container">
            <!-- Admin Logo -->
            <?php 
            // Determine the correct admin dashboard path
            $admin_dashboard_path = '';
            if (strpos($current_dir, '/admin/') !== false) {
                // We're in an admin subfolder, go back to dashboard
                $admin_dashboard_path = '../dashboard/';
            } elseif ($current_dir === '/api') {
                // Legacy API structure
                $admin_dashboard_path = '../admin.php';
            } else {
                // Root level
                $admin_dashboard_path = 'admin/dashboard/';
            }
            ?>
            <a href="<?php echo $admin_dashboard_path; ?>" class="admin-logo">
                <div class="admin-logo-icon">
                    <div class="doko-basket">
                        <div class="basket-handles">
                            <div class="basket-handle left"></div>
                            <div class="basket-handle right"></div>
                        </div>
                        <div class="basket-body"></div>
                    </div>
                </div>
                <div class="admin-logo-text">
                    <span class="logo-main-text">DOKO</span>
                    <span class="logo-sub-text">Admin Panel</span>
                </div>
            </a>
            
            <!-- Navigation Links -->
            <div class="admin-nav-links">
                <?php
                // Determine paths for navigation links
                $dashboard_path = '';
                $products_path = '';
                $users_path = '';
                $orders_path = '';
                
                if (strpos($current_dir, '/api/admin/') !== false) {
                    // We're in an api/admin subfolder  
                    $dashboard_path = '../dashboard/';
                    $products_path = '../products/';
                    $users_path = '../users/';
                    $orders_path = '../orders/';
                } elseif (strpos($current_dir, '/admin/') !== false) {
                    // We're in an admin subfolder
                    $dashboard_path = '../dashboard/';
                    $products_path = '../products/';
                    $users_path = '../users/';
                    $orders_path = '../orders/';
                } elseif ($current_dir === '/api') {
                    // Legacy API structure
                    $dashboard_path = '../admin.php';
                    $products_path = 'admin-products.php';
                    $users_path = 'admin-users.php';
                    $orders_path = 'admin-orders.php';
                } else {
                    // Root level
                    $dashboard_path = 'admin/dashboard/';
                    $products_path = 'admin/products/';
                    $users_path = 'admin/users/';
                    $orders_path = 'admin/orders/';
                }
                ?>
                <a href="<?php echo $dashboard_path; ?>" 
                   class="admin-nav-link <?php echo $current_admin_page === 'dashboard' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo $products_path; ?>" 
                   class="admin-nav-link <?php echo $current_admin_page === 'products' ? 'active' : ''; ?>">
                    <i class="bi bi-box"></i>
                    <span>Products</span>
                </a>
                <a href="<?php echo $users_path; ?>" 
                   class="admin-nav-link <?php echo $current_admin_page === 'users' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
                <a href="<?php echo $orders_path; ?>" 
                   class="admin-nav-link <?php echo $current_admin_page === 'orders' ? 'active' : ''; ?>">
                    <i class="bi bi-cart-check"></i>
                    <span>Orders</span>
                </a>
                <a href="<?php echo $current_dir === '/api' ? '../logout.php' : 'logout.php'; ?>" class="admin-nav-link">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <script>
        // Eliminate any flash of regular content and ensure smooth loading
        document.addEventListener('DOMContentLoaded', function() {
            // Show page loader briefly
            const loader = document.getElementById('pageLoader');
            loader.classList.add('loading');
            
            // Hide any remaining regular header elements
            const elementsToHide = [
                '.header', '.navbar', '.header-main', '.header-actions', 
                '.header-action', '.navigation', '.logo', '.logo-container', 
                '.main-nav', '.header-content', '.search-container', 
                '.user-actions', '.breadcrumb', '.nav-container'
            ];
            
            elementsToHide.forEach(selector => {
                const elements = document.querySelectorAll(selector);
                elements.forEach(el => {
                    el.style.display = 'none';
                    el.style.visibility = 'hidden';
                    el.style.height = '0';
                    el.style.overflow = 'hidden';
                    el.style.position = 'absolute';
                    el.style.left = '-9999px';
                });
            });
            
            // Show admin content after brief delay
            setTimeout(() => {
                document.body.classList.add('admin-ready');
                loader.classList.remove('loading');
            }, 100);
        });
        
        // Enhanced navigation with loading states and smooth transitions
        document.querySelectorAll('.admin-nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                // Don't prevent logout
                if (this.href.includes('logout.php')) {
                    return;
                }
                
                // Show loading state for other navigation
                const loader = document.getElementById('pageLoader');
                loader.classList.add('loading');
                
                // Add clicked state
                this.style.background = 'rgba(255, 255, 255, 0.2)';
            });
        });
        
        // Add smooth page transition effect
        window.addEventListener('beforeunload', function() {
            document.body.style.opacity = '0.8';
            document.body.style.transition = 'opacity 0.2s ease';
        });
        
        // Continuous monitoring to prevent any flash content
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            // Hide any regular header elements that might be added dynamically
                            const elementsToHide = [
                                '.header', '.navbar', '.header-main', '.header-actions', 
                                '.header-action', '.navigation', '.logo', '.logo-container', 
                                '.main-nav', '.header-content', '.search-container', 
                                '.user-actions', '.breadcrumb', '.nav-container'
                            ];
                            
                            elementsToHide.forEach(selector => {
                                const elements = node.querySelectorAll && node.querySelectorAll(selector);
                                if (elements) {
                                    elements.forEach(el => {
                                        el.style.display = 'none !important';
                                        el.style.visibility = 'hidden !important';
                                        el.style.height = '0 !important';
                                        el.style.overflow = 'hidden !important';
                                        el.style.position = 'absolute !important';
                                        el.style.left = '-9999px !important';
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Enhanced navigation with loading states
        document.querySelectorAll('.admin-nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                // Don't prevent logout
                if (this.href.includes('logout.php')) {
                    return;
                }
                
                // Show loading state for other navigation
                const loader = document.getElementById('pageLoader');
                loader.classList.add('loading');
                
                // Add clicked state
                this.style.background = 'rgba(255, 255, 255, 0.2)';
            });
        });
    </script>

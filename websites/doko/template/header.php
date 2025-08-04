<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'DOKO | Nepal\'s Premier Online Grocery Store'; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Fresh groceries delivered to your doorstep across Nepal. Quality products, competitive prices, and reliable service.'; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <!-- Main Header -->
        <div class="header-main">
            <div class="container">
                <div class="header-content">
                    <!-- Professional DOKO Logo -->
                    <div class="logo">
                        <a href="index.php" style="display: flex; align-items: center; gap: 15px; text-decoration: none;">
                            <div class="logo-icon doko-basket-icon">
                                <div class="basket-container">
                                    <div class="basket-body">
                                        <div class="weave-pattern"></div>
                                        <div class="weave-pattern"></div>
                                        <div class="weave-pattern"></div>
                                    </div>
                                    <div class="basket-handles">
                                        <div class="handle-left"></div>
                                        <div class="handle-right"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="logo-text">
                                <span class="logo-main">DOKO</span>
                                <span class="logo-tagline">Traditional Basket</span>
                            </div>
                        </a>
                    </div>

                    <!-- Enhanced Search Bar -->
                    <div class="search-container">
                        <button class="search-btn" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                        <input type="text" id="search-box" class="search-box" placeholder="Search for fresh vegetables, fruits, dairy products...">
                    </div>

                    <!-- Header Actions -->
                    <div class="header-actions">
                        <!-- Account -->
                        <div class="header-action login-link">
                            <a href="login.php">
                                <i class="fas fa-user"></i>
                                <span>Login</span>
                            </a>
                        </div>
                        
                        <div class="header-action logout-link" style="display: none;">
                            <i class="fas fa-user-circle"></i>
                            <span class="user-name">User</span>
                        </div>

                        <!-- Wishlist -->
                        <div class="header-action">
                            <a href="wishlist.php">
                                <i class="fas fa-heart"></i>
                                <span>Wishlist</span>
                            </a>
                        </div>

                        <!-- Cart -->
                        <div class="header-action">
                            <a href="cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Cart</span>
                                <span class="cart-count">0</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="navbar">
            <div class="container">
                <div class="nav-container">
                    <ul class="nav-list" id="nav-list">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link <?php echo ($current_page == 'home') ? 'active' : ''; ?>">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="products.php" class="nav-link <?php echo ($current_page == 'categories' || $current_page == 'products') ? 'active' : ''; ?>">
                                <i class="fas fa-th-large"></i> Categories
                            </a>
                            <div class="dropdown-menu">
                                <a href="products.php?category=1" class="dropdown-item">🥬 Fresh Vegetables</a>
                                <a href="products.php?category=2" class="dropdown-item">🍎 Fresh Fruits</a>
                                <a href="products.php?category=3" class="dropdown-item">🥛 Dairy Products</a>
                                <a href="products.php?category=4" class="dropdown-item">🌾 Grains & Pulses</a>
                                <a href="products.php?category=5" class="dropdown-item">🌿 Spices & Herbs</a>
                                <a href="products.php?category=6" class="dropdown-item">🍪 Snacks & Beverages</a>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a href="offers.php" class="nav-link <?php echo ($current_page == 'offers') ? 'active' : ''; ?>">
                                <i class="fas fa-tag"></i> Offers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="about.php" class="nav-link <?php echo ($current_page == 'about') ? 'active' : ''; ?>">
                                <i class="fas fa-info-circle"></i> About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="contact.php" class="nav-link <?php echo ($current_page == 'contact') ? 'active' : ''; ?>">
                                <i class="fas fa-phone"></i> Contact
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Mobile Navigation Script -->
    <script src="js/mobile-nav.js"></script>

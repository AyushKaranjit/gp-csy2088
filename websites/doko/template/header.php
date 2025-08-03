<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'DOKO - Professional E-Commerce Platform'; ?></title>
    <!-- Base CSS Files -->
    <link rel="stylesheet" href="css/test.css">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/header.css">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="https://img.icons8.com/fluency/48/shopping-bag.png" type="image/png">
</head>
<body>
    <!-- Top Banner -->
    <div class="top-banner">
        <div class="container">
            <div class="banner-content">
                <span class="banner-text">🛒 Fresh Groceries Flash Sale - Up to 50% Off on Fresh Produce & Daily Essentials!</span>
                <button class="banner-close" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <!-- Top Bar -->
        <div class="header-top">
            <div class="container">
                <div class="header-top-left">
                    <span><i class="fas fa-phone"></i> +977 9812345678</span>
                    <span><i class="fas fa-envelope"></i> support@doko.com</span>
                </div>
                <div class="header-top-right">
                    <a href="track-order.php" class="header-link">Track Order</a>
                    <a href="#" class="header-link">Help Center</a>
                    <a href="login.php" class="header-link">Login</a>
                    <a href="signup.php" class="header-link">Sign Up</a>
                    <div class="language-selector">
                        <i class="fas fa-globe"></i>
                        <select>
                            <option>English</option>
                            <option>नेपाली</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Header -->
        <div class="header-main">
            <div class="container">
                <!-- Logo -->
                <div class="nav-brand">
                    <img src="https://img.icons8.com/fluency/48/shopping-bag.png" alt="DOKO" class="logo-img">
                    <div class="brand-text">
                        <span class="logo-text">DOKO</span>
                        <span class="logo-tagline">Fresh Market</span>
                    </div>
                </div>
                
                <!-- Search Bar -->
                <div class="search-container">
                    <div class="search-bar">
                        <input type="text" placeholder="Search for fresh fruits, vegetables, dairy, meat..." class="search-input" id="mainSearch">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="search-suggestions" id="searchSuggestions" style="display: none;">
                        <!-- Search suggestions will appear here -->
                    </div>
                </div>
                
                <!-- Header Actions -->
                <div class="header-actions">
                    <div class="action-item">
                        <a href="login.php" class="action-link">
                            <i class="fas fa-user"></i>
                            <span>Login</span>
                        </a>
                    </div>
                    <div class="action-item">
                        <a href="wishlist.php" class="action-link">
                            <i class="fas fa-heart"></i>
                            <span>Wishlist</span>
                            <span class="badge">0</span>
                        </a>
                    </div>
                    <div class="action-item">
                        <a href="cart.php" class="action-link cart-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Cart</span>
                            <span class="badge cart-count">0</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation Bar -->
        <nav class="main-nav">
            <div class="container">
                <div class="nav-content">
                    <!-- Navigation Links -->
                    <div class="nav-links">
                        <a href="index.php" class="nav-link <?php echo (isset($current_page) && $current_page == 'home') ? 'active' : ''; ?>">Home</a>
                        <a href="category.php" class="nav-link <?php echo (isset($current_page) && $current_page == 'shop') ? 'active' : ''; ?>">Shop</a>
                        <a href="track-order.php" class="nav-link <?php echo (isset($current_page) && $current_page == 'track-order') ? 'active' : ''; ?>">Track Order</a>
                        <a href="about.php" class="nav-link <?php echo (isset($current_page) && $current_page == 'about') ? 'active' : ''; ?>">About</a>
                        <a href="admin-dashboard.php" class="nav-link <?php echo (isset($current_page) && $current_page == 'admin') ? 'active' : ''; ?>">Admin</a>
                    </div>
                    
                    <!-- Delivery Info -->
                    <div class="delivery-info">
                        <i class="fas fa-truck"></i>
                        <span>Free Delivery on Orders Over रू 1000</span>
                    </div>
                </div>
            </div>
        </nav>
    </header>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php 
        // Generate CSRF token if session is active and function exists
        if (isset($_SESSION) && function_exists('generate_csrf_token')) {
            echo generate_csrf_token();
        } elseif (isset($_SESSION)) {
            // Fallback CSRF token generation
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            echo $_SESSION['csrf_token'];
        }
    ?>">
    <title><?php echo isset($page_title) ? $page_title : 'DOKO | Nepal\'s Premier Online Grocery Store'; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Fresh groceries delivered to your doorstep across Nepal. Quality products, competitive prices, and reliable service.'; ?>">
    
    <?php
    // Determine the correct paths based on current location
    $current_dir = dirname($_SERVER['SCRIPT_NAME']);
    $css_path = 'css/';
    $js_path = 'js/';
    $images_path = 'images/';
    
    // Check if we're in a subdirectory (like api/, admin/*, or manager/*)
    if (strpos($current_dir, '/api') !== false) {
        $css_path = '../css/';
        $js_path = '../js/';
        $images_path = '../images/';
    } elseif (strpos($current_dir, '/admin') !== false) {
        // Admin pages nested inside /admin/{section}/
        $css_path = '../../css/';
        $js_path = '../../js/';
        $images_path = '../../images/';
    } elseif (strpos($current_dir, '/manager') !== false) {
        // Manager pages nested inside /manager/{section}/
        $css_path = '../../css/';
        $js_path = '../../js/';
        $images_path = '../../images/';
    }
    ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $images_path; ?>favicon.ico">
    
    <!-- Preconnect to Google Fonts for better performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?php echo $css_path; ?>style.css">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if (!empty($ADMIN_UI)): // Extra icon set for admin pages ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <?php endif; ?>
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Immediate Image Error Handler Fallback -->
    <script>
    // Expose a consistent absolute API base for scripts (works under nested routes)
    window.API_BASE = '/api/';
    // Fallback handleImageError function - only define if not already provided
    if (!window.handleImageError) window.handleImageError = function(img) {
        if(!img||!img.src) return;
        if(!window.__imageErrorCache) window.__imageErrorCache=new Set();
        if(!window.__imageErrorCache.has(img.src)){
            console.debug('[ImageError]', img.src);
            window.__imageErrorCache.add(img.src);
        }
        if (!/default-product\.jpg$/i.test(img.src)) {
            img.src = '/images/default-product.jpg';
            img.style.objectFit='contain';img.style.padding='20px';img.style.background='#f8f9fa';
            return;
        }
        img.style.display='none';
        if(img.parentNode && !img.parentNode.__placeholderAdded){
            const placeholder=document.createElement('div');
            placeholder.className='image-placeholder';
            placeholder.innerHTML='<i class="fas fa-image"></i><br><span>Image not available</span>';
            placeholder.style.cssText='width:100%;height:100%;display:flex!important;flex-direction:column;align-items:center;justify-content:center;background:#f8f9fa!important;color:#6c757d!important;font-size:0.8rem;text-align:center;border-radius:8px;min-height:200px;';
            img.parentNode.insertBefore(placeholder, img.nextSibling);
            img.parentNode.__placeholderAdded=true;
        }
    };
    </script>
    <script>
    // Expose auth state early (set after PHP auth resolution later in body too if needed)
    window.DOKO_LOGGED_IN = <?php echo isset($_SESSION['user_id']) ? 'true':'false'; ?>;
    </script>
</head>
<body<?php echo !empty($ADMIN_UI) ? ' class="admin-context"' : ''; ?>>
    <!-- Header (suppressed for admin context) -->
    <?php if (empty($ADMIN_UI)): ?>
    <header class="header">
        <!-- Main Header -->
        <div class="header-main">
            <div class="container">
                <div class="header-content">
                    <!-- Professional DOKO Logo -->
                    <div class="logo" style="gap:10px;align-items:center;">
                        <a href="index.php" class="doko-logo-link" aria-label="DOKO Nepali Traditional Basket Home" style="align-items:center;display:flex;">
                            <div class="logo-icon doko-basket-icon" aria-hidden="true">
                                <svg class="doko-svg" viewBox="0 0 64 64" role="img" focusable="false">
                                    <defs>
                                        <linearGradient id="dokoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#8B4513"/>
                                            <stop offset="55%" stop-color="#A0522D"/>
                                            <stop offset="100%" stop-color="#D17A2D"/>
                                        </linearGradient>
                                        <pattern id="weavePattern" width="6" height="6" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                                            <rect x="0" y="0" width="6" height="6" fill="#c27d46"/>
                                            <rect x="0" y="0" width="6" height="3" fill="#b36a33"/>
                                            <rect x="0" y="3" width="6" height="1" fill="#d58d54"/>
                                        </pattern>
                                    </defs>
                                    <path d="M8 10 L56 10 L50 46 Q32 54 14 46 Z" fill="url(#weavePattern)" stroke="#5c3311" stroke-width="2" stroke-linejoin="round"/>
                                    <path d="M8 10 L56 10" stroke="url(#dokoGrad)" stroke-width="6" stroke-linecap="round"/>
                                    <path d="M14 18 L50 18 M12 26 L52 26 M16 34 L48 34" stroke="#a45d2a" stroke-width="2" stroke-linecap="round" opacity="0.55"/>
                                </svg>
                            </div>
                            <div class="logo-text">
                                <span class="logo-main">DOKO</span>
                                <span class="logo-tagline">Traditional Basket</span>
                            </div>
                        </a>
                    </div>

                    <!-- Enhanced Search Bar -->
                    <div class="search-container">
                        <form action="products.php" method="GET" class="search-form" id="search-form">
                            <button class="search-btn" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            <input type="text" id="search-box" name="search" class="search-box" 
                                   placeholder="Search for fresh vegetables, fruits, dairy products..."
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </form>
                    </div>

                    <!-- Header Actions -->
                    <div class="header-actions">
                        <?php 
                        // Check if we have the AuthController available
                        $auth = null;
                        $isLoggedIn = false;
                        $currentUser = null;
                        
                        // Session should already be started by the page
                        // Don't start session here to avoid headers already sent errors
                        
                        // Try to create AuthController if classes are available
                        if (file_exists(__DIR__ . '/../src/Controllers/AuthController.php')) {
                            try {
                                require_once __DIR__ . '/../src/Controllers/AuthController.php';
                                $auth = new AuthController();
                                $isLoggedIn = $auth->isLoggedIn();
                                if ($isLoggedIn) {
                                    $currentUser = $auth->getCurrentUser();
                                }
                            } catch (Exception $e) {
                                // Fallback to session check
                                $isLoggedIn = isset($_SESSION['user_id']);
                                if ($isLoggedIn) {
                                    $currentUser = [
                                        'first_name' => $_SESSION['first_name'] ?? 'User',
                                        'role' => $_SESSION['role'] ?? 'customer'
                                    ];
                                }
                            }
                        } else {
                            // Fallback to session check
                            $isLoggedIn = isset($_SESSION['user_id']);
                            if ($isLoggedIn) {
                                $currentUser = [
                                    'first_name' => $_SESSION['first_name'] ?? 'User',
                                    'role' => $_SESSION['role'] ?? 'customer'
                                ];
                            }
                        }
                        ?>
                        
                        <?php if (!$isLoggedIn): ?>
                            <!-- Account Login -->
                            <div class="header-action login-link">
                                <a href="login.php">
                                    <i class="fas fa-user"></i>
                                    <span>Login</span>
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Logged in user dropdown -->
                            <div class="header-action user-dropdown">
                                <div class="user-info" onclick="toggleUserDropdown()">
                                    <?php if (!empty($currentUser['profile_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($currentUser['profile_image']); ?>" alt="Avatar" style="width:28px;height:28px;border-radius:50%;object-fit:cover;" onerror="this.onerror=null;this.src='/images/default-avatar.png'"> 
                                    <?php else: ?>
                                        <i class="fas fa-user-circle"></i>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($currentUser['first_name']); ?></span>
                                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                                </div>
                                <div class="user-dropdown-menu">
                                    <div class="dropdown-user-info">
                                        <?php if (!empty($currentUser['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($currentUser['profile_image']); ?>" alt="Profile" class="dropdown-avatar" onerror="this.onerror=null;this.src='/images/default-avatar.png'"> 
                                        <?php else: ?>
                                            <div class="dropdown-avatar-placeholder">
                                                <i class="fas fa-user-circle"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="dropdown-user-details">
                                            <span class="dropdown-user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . ($currentUser['last_name'] ?? '')); ?></span>
                                            <span class="dropdown-user-email"><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></span>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a href="profile.php" class="dropdown-item">
                                        <i class="fas fa-user-edit"></i> My Profile
                                    </a>
                                    <?php if (isset($currentUser['role']) && $currentUser['role'] === 'admin'): ?>
                                        <a href="admin.php" class="dropdown-item">
                                            <i class="fas fa-cogs"></i> Admin Panel
                                        </a>
                                    <?php else: ?>
                                        <a href="profile.php#order-history" class="dropdown-item">
                                            <i class="fas fa-shopping-bag"></i> My Orders
                                        </a>
                                    <?php endif; ?>
                                    <div class="dropdown-divider"></div>
                                    <a href="logout.php" class="dropdown-item logout-item" onclick="return confirm('Are you sure you want to logout?')">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </div>
                            
                        <?php endif; ?>

                        <!-- Wishlist -->
                        <div class="header-action">
                            <a href="wishlist.php">
                                <i class="fas fa-heart"></i>
                                <span>Wishlist</span>
                                <span class="wishlist-count" id="wishlist-count">0</span>
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
                            <a href="index.php" class="nav-link <?php echo (isset($current_page) && $current_page == 'home') ? 'active' : ''; ?>">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="products.php" class="nav-link <?php echo (isset($current_page) && ($current_page == 'categories' || $current_page == 'products')) ? 'active' : ''; ?>">
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
                            <a href="offers.php" class="nav-link <?php echo (isset($current_page) && $current_page == 'offers') ? 'active' : ''; ?>">
                                <i class="fas fa-tag"></i> Offers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="about.php" class="nav-link <?php echo (isset($current_page) && $current_page == 'about') ? 'active' : ''; ?>">
                                <i class="fas fa-info-circle"></i> About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="contact.php" class="nav-link <?php echo (isset($current_page) && $current_page == 'contact') ? 'active' : ''; ?>">
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
    <!-- Move user dropdown outside nav for proper z-index and click handling -->
    <!-- ...existing code... -->
    </header>
    <?php endif; ?>
    
    <script>
    // Profile dropdown toggle
    document.addEventListener('DOMContentLoaded', function() {
        var userDropdown = document.querySelector('.user-dropdown');
        var userInfo = document.querySelector('.user-info');
        if (userDropdown && userInfo) {
            userInfo.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userDropdown.contains(e.target)) {
                    userDropdown.classList.remove('active');
                }
            });
        }
        
        // Enhanced Search Functionality
        var searchForm = document.getElementById('search-form');
        var searchBox = document.getElementById('search-box');
        var searchContainer = document.querySelector('.search-container');
        
        if (searchForm && searchBox) {
            // Create search suggestions dropdown
            var suggestionsContainer = document.createElement('div');
            suggestionsContainer.className = 'search-suggestions';
            suggestionsContainer.style.cssText = `
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid #ddd;
                border-top: none;
                border-radius: 0 0 8px 8px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                max-height: 300px;
                overflow-y: auto;
                z-index: 1000;
                display: none;
            `;
            searchContainer.appendChild(suggestionsContainer);
            
            var searchTimeout;
            var currentSuggestionIndex = -1;
            
            // Handle form submission
            searchForm.addEventListener('submit', function(e) {
                var searchTerm = searchBox.value.trim();
                if (searchTerm === '') {
                    e.preventDefault();
                    searchBox.focus();
                    return false;
                }
                
                // Update the form action to handle different page contexts
                var currentLocation = window.location.pathname;
                var searchUrl = '/products.php';
                
                // If we're in a nested directory, adjust the path
                if (currentLocation.includes('/admin/') || currentLocation.includes('/manager/') || currentLocation.includes('/api/')) {
                    searchUrl = '../../products.php';
                } else if (currentLocation.includes('/')) {
                    // Check depth and adjust accordingly
                    var depth = (currentLocation.match(/\//g) || []).length;
                    if (depth > 1) {
                        searchUrl = '../products.php';
                    }
                }
                
                searchForm.action = searchUrl;
                hideSuggestions();
            });
            
            // Live search suggestions
            searchBox.addEventListener('input', function(e) {
                var query = e.target.value.trim();
                
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                
                if (query.length < 2) {
                    hideSuggestions();
                    return;
                }
                
                searchTimeout = setTimeout(function() {
                    fetchSuggestions(query);
                }, 300);
            });
            
            // Handle keyboard navigation
            searchBox.addEventListener('keydown', function(e) {
                var suggestions = suggestionsContainer.querySelectorAll('.suggestion-item');
                
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        currentSuggestionIndex = Math.min(currentSuggestionIndex + 1, suggestions.length - 1);
                        updateSuggestionSelection(suggestions);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        currentSuggestionIndex = Math.max(currentSuggestionIndex - 1, -1);
                        updateSuggestionSelection(suggestions);
                        break;
                    case 'Enter':
                        if (currentSuggestionIndex >= 0 && suggestions[currentSuggestionIndex]) {
                            e.preventDefault();
                            suggestions[currentSuggestionIndex].click();
                        }
                        break;
                    case 'Escape':
                        hideSuggestions();
                        break;
                }
            });
            
            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchContainer.contains(e.target)) {
                    hideSuggestions();
                }
            });
            
            function fetchSuggestions(query) {
                fetch('/api/products/products-search.php?q=' + encodeURIComponent(query) + '&limit=5')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data && data.data.length > 0) {
                            showSuggestions(data.data, query);
                        } else {
                            hideSuggestions();
                        }
                    })
                    .catch(error => {
                        console.debug('Search suggestions error:', error);
                        hideSuggestions();
                    });
            }
            
            function showSuggestions(products, query) {
                currentSuggestionIndex = -1;
                suggestionsContainer.innerHTML = '';
                
                products.forEach(function(product, index) {
                    var suggestionItem = document.createElement('div');
                    suggestionItem.className = 'suggestion-item';
                    suggestionItem.style.cssText = `
                        padding: 10px 15px;
                        cursor: pointer;
                        border-bottom: 1px solid #f0f0f0;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        transition: background-color 0.2s;
                    `;
                    
                    var productImage = product.primary_image || '/images/default-product.jpg';
                    var productName = product.name || 'Unknown Product';
                    var productPrice = product.price ? 'Rs. ' + parseFloat(product.price).toFixed(2) : '';
                    
                    suggestionItem.innerHTML = `
                        <img src="${productImage}" alt="${productName}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                        <div style="flex: 1;">
                            <div style="font-weight: 500; color: #333;">${highlightQuery(productName, query)}</div>
                            ${productPrice ? '<div style="font-size: 0.9rem; color: #666;">' + productPrice + '</div>' : ''}
                        </div>
                    `;
                    
                    suggestionItem.addEventListener('click', function() {
                        searchBox.value = productName;
                        hideSuggestions();
                        searchForm.submit();
                    });
                    
                    suggestionItem.addEventListener('mouseenter', function() {
                        currentSuggestionIndex = index;
                        updateSuggestionSelection(suggestionsContainer.querySelectorAll('.suggestion-item'));
                    });
                    
                    suggestionsContainer.appendChild(suggestionItem);
                });
                
                suggestionsContainer.style.display = 'block';
            }
            
            function hideSuggestions() {
                suggestionsContainer.style.display = 'none';
                currentSuggestionIndex = -1;
            }
            
            function updateSuggestionSelection(suggestions) {
                suggestions.forEach(function(item, index) {
                    if (index === currentSuggestionIndex) {
                        item.style.backgroundColor = '#f8f9fa';
                        searchBox.value = item.querySelector('div div').textContent;
                    } else {
                        item.style.backgroundColor = '';
                    }
                });
            }
            
            function highlightQuery(text, query) {
                if (!query) return text;
                var regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                return text.replace(regex, '<strong style="color: #FF6B35;">$1</strong>');
            }
            
            // Auto-focus search on '/' key press (popular shortcut)
            document.addEventListener('keydown', function(e) {
                if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                    var activeElement = document.activeElement;
                    if (!activeElement || (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA')) {
                        e.preventDefault();
                        searchBox.focus();
                        searchBox.select();
                    }
                }
            });
        }
    });

    // Cart count update function
    function updateCartCount() {
        fetch('/api/cart/get.php')
            .then(response => response.json())
            .then(data => {
                if (data && data.success) {
                    const cartCountElement = document.querySelector('.cart-count');
                    if (cartCountElement) {
                        cartCountElement.textContent = data.total_items || 0;
                    }
                    
                    // If we're on the cart page, trigger a cart refresh
                    if (window.location.pathname.includes('cart.php') && typeof CartModule !== 'undefined' && CartModule.refresh) {
                        CartModule.refresh();
                    }
                } else {
                    // Handle guest cart count
                    try {
                        const guestCart = JSON.parse(localStorage.getItem(window.GUEST_CART_KEY || 'doko_guest_cart_v1') || '[]');
                        const totalItems = guestCart.reduce((sum, item) => sum + (item.quantity || 0), 0);
                        const cartCountElement = document.querySelector('.cart-count');
                        if (cartCountElement) {
                            cartCountElement.textContent = totalItems;
                        }
                    } catch(e) {
                        console.error('Error updating guest cart count:', e);
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart count:', error);
                // Fallback to guest cart count
                try {
                    const guestCart = JSON.parse(localStorage.getItem(window.GUEST_CART_KEY || 'doko_guest_cart_v1') || '[]');
                    const totalItems = guestCart.reduce((sum, item) => sum + (item.quantity || 0), 0);
                    const cartCountElement = document.querySelector('.cart-count');
                    if (cartCountElement) {
                        cartCountElement.textContent = totalItems;
                    }
                } catch(e) {
                    console.error('Error with fallback cart count:', e);
                }
            });
    }

    // Wishlist count update function
    function updateWishlistCount() {
        fetch('/api/wishlist/wishlist.php')
            .then(response => response.json())
            .then(data => {
                if (data && data.success) {
                    const wishlistCountElement = document.querySelector('.wishlist-count');
                    if (wishlistCountElement) {
                        wishlistCountElement.textContent = data.count || 0;
                    }
                }
            })
            .catch(error => {
                console.error('Error updating wishlist count:', error);
            });
    }

    // Update counts on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateCartCount();
        updateWishlistCount();
    });

    // Toggle user dropdown function
    function toggleUserDropdown() {
        const dropdown = document.querySelector('.user-dropdown');
        if (dropdown) {
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const userInfo = document.querySelector('.user-info');
        const dropdown = document.querySelector('.user-dropdown');
        if (dropdown && userInfo && !userInfo.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
    </script>

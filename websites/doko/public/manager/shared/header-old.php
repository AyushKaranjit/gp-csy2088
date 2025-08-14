<?php
/**
 * Manager Header Template
 * DOKO Grocery E-commerce Manager Panel
 */

if (!isset($page_title)) {
    $page_title = 'Manager Panel | DOKO';
}

if (!isset($current_page)) {
    $current_page = 'manager';
}

// Include main site header template
$ADMIN_UI = true; // suppress storefront header
$additional_css = $additional_css ?? [];
$additional_css[] = '/css/admin.css';
include '../../../template/header.php';

// Manager Navigation Function
$script = $_SERVER['SCRIPT_NAME'] ?? '';
function doko_manager_active(string $section, string $script): string {
    return (strpos($script, "/manager/$section/") !== false) ? 'active' : '';
}
?>

<!-- Manager Navigation -->
<nav class="admin-subnav">
    <div class="container">
        <div class="admin-brand"><a href="/manager/dashboard/" class="admin-logo-link">DOKO <span>Manager</span></a></div>
        <ul class="admin-subnav-list">
            <li><a class="<?php echo doko_manager_active('dashboard', $script); ?>" href="/manager/dashboard/">Dashboard</a></li>
            <li><a class="<?php echo doko_manager_active('orders', $script); ?>" href="/manager/orders/">Orders <span id="manager-pending-count" class="badge-pill" style="display:none;">0</span></a></li>
            <li><a class="<?php echo doko_manager_active('products', $script); ?>" href="/manager/products/">Products</a></li>
            <li class="admin-subnav-spacer"></li>
            <li class="admin-subnav-right"><a href="/" class="return-site">← Storefront</a></li>
            <li class="admin-subnav-right"><a href="/logout.php" class="logout-link" onclick="return confirm('Logout from manager panel?');">Logout</a></li>
        </ul>
    </div>
</nav>

<!-- Badge Scripts -->
<script>
// Fetch pending orders count for badge
(function(){
  async function fetchPendingCount(){
    try {
      const res = await fetch('/api/orders/stats.php', {credentials:'same-origin'});
      const data = await res.json();
      if(data && typeof data.pending_orders === 'number' && data.pending_orders > 0){
        const el = document.getElementById('manager-pending-count');
        if(el){ el.textContent = data.pending_orders; el.style.display='inline-block'; }
      }
    } catch(e) { /* silent */ }
  }
  if(document.readyState==='complete' || document.readyState==='interactive') setTimeout(fetchPendingCount, 300); else document.addEventListener('DOMContentLoaded',()=>setTimeout(fetchPendingCount,300));
})();
</script>

<style>
/* Manager-specific badge styles */
.badge-pill{background:#e11d48;color:#fff;border-radius:999px;padding:2px 6px;font-size:11px;font-weight:600;vertical-align:middle;margin-left:4px;line-height:1;}
</style>

<div class="admin-container">
        }

        .nav-link i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        .user-menu {
            position: relative;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .user-dropdown:hover {
            background-color: rgba(255,255,255,0.2);
        }

        .user-info {
            margin-right: 10px;
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-role {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            min-width: 200px;
            display: none;
            z-index: 1000;
            margin-top: 10px;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            text-decoration: none;
            color: #333;
        }

        .dropdown-item i {
            margin-right: 10px;
            width: 16px;
        }

        /* Main Content */
        .main-content {
            min-height: calc(100vh - 80px);
            padding: 20px 0;
        }

        /* Breadcrumb */
        .breadcrumb-container {
            background: white;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }

        .breadcrumb {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
        }

        .breadcrumb-item {
            color: #666;
            text-decoration: none;
            margin-right: 10px;
        }

        .breadcrumb-item:hover {
            color: #007bff;
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: #333;
            font-weight: 500;
        }

        .breadcrumb-separator {
            margin: 0 10px;
            color: #ccc;
        }

        /* Mobile Navigation */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                flex-direction: column;
                padding: 20px;
                transition: left 0.3s;
                z-index: 999;
            }

            .nav-menu.show {
                left: 0;
            }

            .nav-item {
                margin: 5px 0;
                width: 100%;
            }

            .nav-link {
                width: 100%;
                justify-content: flex-start;
            }

            .mobile-menu-btn {
                display: block;
            }

            .user-dropdown {
                padding: 5px 10px;
            }

            .user-info {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="manager-nav">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-store"></i>
                <h1>DOKO Manager</h1>
            </div>
            
            <ul class="nav-menu" id="navMenu">
                <li class="nav-item">
                    <a href="../dashboard/" class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../orders/" class="nav-link <?php echo ($current_page == 'orders') ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../products/" class="nav-link <?php echo ($current_page == 'products') ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i>
                        Products
                    </a>
                </li>
                <?php if ($auth->isAdmin()): ?>
                <li class="nav-item">
                    <a href="../../admin/" class="nav-link">
                        <i class="fas fa-user-shield"></i>
                        Admin Panel
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <div class="user-menu">
                <div class="user-dropdown" onclick="toggleDropdown()">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
                        <div class="user-role"><?php echo ucfirst($currentUser['role']); ?></div>
                    </div>
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <i class="fas fa-chevron-down" style="margin-left: 8px; font-size: 0.8rem;"></i>
                </div>
                
                <div class="dropdown-menu" id="userDropdown">
                    <a href="../../profile.php" class="dropdown-item">
                        <i class="fas fa-user-edit"></i>
                        Profile Settings
                    </a>
                    <a href="../../index.php" class="dropdown-item">
                        <i class="fas fa-store"></i>
                        View Store
                    </a>
                    <a href="../../logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>

            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <div class="main-content">
        <?php if (isset($show_breadcrumb) && $show_breadcrumb !== false): ?>
        <div class="breadcrumb-container">
            <nav class="breadcrumb">
                <a href="../dashboard/" class="breadcrumb-item">Dashboard</a>
                <?php if (isset($breadcrumb_items) && is_array($breadcrumb_items)): ?>
                    <?php foreach ($breadcrumb_items as $item): ?>
                        <span class="breadcrumb-separator">></span>
                        <?php if (isset($item['url'])): ?>
                            <a href="<?php echo $item['url']; ?>" class="breadcrumb-item"><?php echo $item['title']; ?></a>
                        <?php else: ?>
                            <span class="breadcrumb-item active"><?php echo $item['title']; ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        function toggleMobileMenu() {
            const navMenu = document.getElementById('navMenu');
            navMenu.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            const dropdown = document.getElementById('userDropdown');
            const userDropdown = document.querySelector('.user-dropdown');
            if (!userDropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Close mobile menu when clicking outside
        window.addEventListener('click', function(e) {
            const navMenu = document.getElementById('navMenu');
            const mobileBtn = document.querySelector('.mobile-menu-btn');
            if (!navMenu.contains(e.target) && !mobileBtn.contains(e.target)) {
                navMenu.classList.remove('show');
            }
        });
    </script>

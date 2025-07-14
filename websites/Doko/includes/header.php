<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Graduation Grocery Store'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <h1><i class="fas fa-shopping-basket"></i> Graduation Grocery</h1>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="products.php" class="nav-link">Products</a>
                <?php if (isLoggedIn()): ?>
                    <a href="cart.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <span class="cart-count" id="cartCount">0</span>
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/dashboard.php" class="nav-link">Admin</a>
                    <?php endif; ?>
                    <a href="profile.php" class="nav-link">Profile</a>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

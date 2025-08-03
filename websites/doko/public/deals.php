<?php
// Page Configuration
$page_title = 'DOKO - Fresh Deals | Daily Offers & Discounts';
$current_page = 'deals';
$additional_css = [
    'css/homepage.css',
    'css/grocery-theme.css',
    'css/category.css'
];
$additional_js = [
    'js/deals.js'
];

// Include header template
include_once '../template/header.php';
?>

<!-- Main Content -->
<main class="deals-page">
    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1 class="main-title">Fresh Deals & Offers</h1>
            <p class="main-subtitle">Unbeatable prices on premium quality groceries</p>
        </div>
    </section>

    <!-- Flash Sale Timer -->
    <section class="flash-sale-section">
        <div class="container">
            <div class="flash-sale-header">
                <div class="flash-sale-title">
                    <i class="fas fa-bolt"></i>
                    <h2>Flash Sale</h2>
                    <span class="sale-badge">UP TO 70% OFF</span>
                </div>
                <div class="flash-sale-timer">
                    <span>Ends in:</span>
                    <div class="timer">
                        <div class="time-unit">
                            <span class="time-value" id="hours">12</span>
                            <span class="time-label">Hours</span>
                        </div>
                        <div class="time-unit">
                            <span class="time-value" id="minutes">34</span>
                            <span class="time-label">Minutes</span>
                        </div>
                        <div class="time-unit">
                            <span class="time-value" id="seconds">56</span>
                            <span class="time-label">Seconds</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Deal Categories -->
    <section class="deals-categories">
        <div class="container">
            <div class="deals-nav">
                <button class="deal-nav-btn active" data-category="all">All Deals</button>
                <button class="deal-nav-btn" data-category="fruits">Fruits</button>
                <button class="deal-nav-btn" data-category="vegetables">Vegetables</button>
                <button class="deal-nav-btn" data-category="dairy">Dairy</button>
                <button class="deal-nav-btn" data-category="meat">Meat & Seafood</button>
                <button class="deal-nav-btn" data-category="pantry">Pantry</button>
            </div>

            <!-- Today's Best Deals -->
            <div class="deals-grid">
                <!-- Deal items will be loaded here dynamically -->
            </div>
        </div>
    </section>

    <!-- Special Offers Banner -->
    <section class="special-offers-banner">
        <div class="container">
            <div class="offers-banner-grid">
                <div class="banner-item main-banner">
                    <div class="banner-content">
                        <span class="banner-badge">Limited Time</span>
                        <h3>Mega Grocery Sale</h3>
                        <p>Up to 60% off on all categories</p>
                        <a href="category.php" class="banner-btn">Shop Now</a>
                    </div>
                </div>
                <div class="banner-item">
                    <div class="banner-content">
                        <span class="banner-badge">Weekend Special</span>
                        <h3>Fresh Fruits Bundle</h3>
                        <p>Buy 2 Get 1 Free</p>
                        <a href="category.php?cat=fruits" class="banner-btn">View Offers</a>
                    </div>
                </div>
                <div class="banner-item">
                    <div class="banner-content">
                        <span class="banner-badge">New Customer</span>
                        <h3>Welcome Bonus</h3>
                        <p>Extra 20% off first order</p>
                        <a href="signup.php" class="banner-btn">Sign Up</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
// Flash sale timer
function updateTimer() {
    const now = new Date().getTime();
    const endTime = now + (12 * 60 * 60 * 1000) + (34 * 60 * 1000) + (56 * 1000); // 12h 34m 56s from now
    
    setInterval(function() {
        const now = new Date().getTime();
        const distance = endTime - now;
        
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
        document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
        document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
        
        if (distance < 0) {
            document.querySelector('.flash-sale-timer').innerHTML = '<span class="expired">Deal Expired</span>';
        }
    }, 1000);
}

// Deal category navigation
document.querySelectorAll('.deal-nav-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.deal-nav-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const category = this.getAttribute('data-category');
        filterDealsByCategory(category);
    });
});

function filterDealsByCategory(category) {
    console.log('Filtering deals by category:', category);
    // Implementation would go here
}

// Initialize timer
updateTimer();
</script>

<?php
// Include footer template
include_once '../template/footer.php';
?>

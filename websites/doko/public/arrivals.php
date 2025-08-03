<?php
// Page Configuration
$page_title = 'DOKO - New Arrivals | Latest Fresh Products';
$current_page = 'arrivals';
$additional_css = [
    'css/homepage.css',
    'css/grocery-theme.css',
    'css/category.css'
];
$additional_js = [
    'js/arrivals.js'
];

// Include header template
include_once '../template/header.php';
?>

<!-- Main Content -->
<main class="arrivals-page">
    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1 class="main-title">New Arrivals</h1>
            <p class="main-subtitle">Fresh products just arrived at DOKO marketplace</p>
        </div>
    </section>

    <!-- Filter Bar -->
    <section class="arrivals-filter">
        <div class="container">
            <div class="filter-bar">
                <div class="filter-group">
                    <span class="filter-label">Filter by:</span>
                    <button class="filter-btn active" data-filter="all">All Items</button>
                    <button class="filter-btn" data-filter="today">Today</button>
                    <button class="filter-btn" data-filter="week">This Week</button>
                    <button class="filter-btn" data-filter="month">This Month</button>
                </div>
                <div class="sort-group">
                    <span class="sort-label">Sort by:</span>
                    <select class="sort-select">
                        <option value="newest">Newest First</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="popular">Most Popular</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <!-- New Arrivals Grid -->
    <section class="arrivals-grid">
        <div class="container">
            <div class="arrivals-categories">
                <!-- Fresh Fruits -->
                <div class="arrival-category">
                    <div class="category-header">
                        <h2 class="category-title">Fresh Fruits</h2>
                        <span class="new-badge">5 New Items</span>
                    </div>
                    <div class="products-grid">
                        <!-- Product cards will be loaded here -->
                    </div>
                </div>

                <!-- Vegetables -->
                <div class="arrival-category">
                    <div class="category-header">
                        <h2 class="category-title">Fresh Vegetables</h2>
                        <span class="new-badge">8 New Items</span>
                    </div>
                    <div class="products-grid">
                        <!-- Product cards will be loaded here -->
                    </div>
                </div>

                <!-- Dairy Products -->
                <div class="arrival-category">
                    <div class="category-header">
                        <h2 class="category-title">Dairy Products</h2>
                        <span class="new-badge">3 New Items</span>
                    </div>
                    <div class="products-grid">
                        <!-- Product cards will be loaded here -->
                    </div>
                </div>

                <!-- Meat & Seafood -->
                <div class="arrival-category">
                    <div class="category-header">
                        <h2 class="category-title">Meat & Seafood</h2>
                        <span class="new-badge">4 New Items</span>
                    </div>
                    <div class="products-grid">
                        <!-- Product cards will be loaded here -->
                    </div>
                </div>

                <!-- Pantry Essentials -->
                <div class="arrival-category">
                    <div class="category-header">
                        <h2 class="category-title">Pantry Essentials</h2>
                        <span class="new-badge">12 New Items</span>
                    </div>
                    <div class="products-grid">
                        <!-- Product cards will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Signup -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h3>Stay Updated</h3>
                    <p>Get notified about new arrivals and exclusive deals</p>
                </div>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter your email" class="newsletter-input" required>
                    <button type="submit" class="newsletter-btn">Subscribe</button>
                </form>
            </div>
        </div>
    </section>
</main>

<script>
// Filter functionality
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.getAttribute('data-filter');
        filterArrivalsByTime(filter);
    });
});

function filterArrivalsByTime(timeFilter) {
    console.log('Filtering arrivals by time:', timeFilter);
    // Implementation would go here
}

// Sort functionality
document.querySelector('.sort-select').addEventListener('change', function() {
    const sortBy = this.value;
    sortArrivals(sortBy);
});

function sortArrivals(sortBy) {
    console.log('Sorting arrivals by:', sortBy);
    // Implementation would go here
}

// Newsletter form
document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('.newsletter-input').value;
    console.log('Newsletter signup:', email);
    // Implementation would go here
    alert('Thank you for subscribing to our newsletter!');
    this.reset();
});
</script>

<?php
// Include footer template
include_once '../template/footer.php';
?>

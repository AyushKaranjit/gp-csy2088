<?php
// Start session and include configuration
session_start();
require_once __DIR__ . '/../template/config.php';

// Page-specific variables
$page_title = page_title('Shipping Information');
$page_description = 'Learn about DOKO\'s shipping and delivery options. Fast, reliable delivery for fresh groceries across Nepal.';
$current_page = 'shipping';

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Shipping Info', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php include __DIR__ . '/../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <!-- Shipping Hero Section -->
    <section class="shipping-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Shipping & Delivery</h1>
                <p>Fast, reliable delivery for fresh groceries across Nepal</p>
            </div>
        </div>
    </section>

    <!-- Shipping Overview Section -->
    <section class="section">
        <div class="container">
            <div class="shipping-overview">
                <h2>Delivery Options</h2>
                <p>At DOKO, we understand that fresh groceries need to reach you quickly and safely. That's why we offer multiple delivery options to suit your needs.</p>

                <div class="delivery-options">
                    <div class="option-card">
                        <div class="option-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3>Standard Delivery</h3>
                        <p>Delivery within 2-4 hours in Kathmandu Valley</p>
                        <div class="option-price">Rs. 100</div>
                    </div>

                    <div class="option-card featured">
                        <div class="option-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3>Express Delivery</h3>
                        <p>Delivery within 1-2 hours in Kathmandu Valley</p>
                        <div class="option-price">Rs. 150</div>
                    </div>

                    <div class="option-card">
                        <div class="option-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Scheduled Delivery</h3>
                        <p>Choose your preferred delivery time slot</p>
                        <div class="option-price">Rs. 120</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Free Shipping Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="free-shipping">
                <div class="free-shipping-content">
                    <h2>Free Shipping</h2>
                    <p>Enjoy free shipping on orders above Rs. 1,000 within Kathmandu Valley!</p>
                    <div class="free-shipping-threshold">
                        <div class="threshold-amount">Rs. 1,000</div>
                        <p>Minimum order value for free shipping</p>
                    </div>
                </div>
                <div class="free-shipping-image">
                    <i class="fas fa-truck-moving"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Delivery Areas Section -->
    <section class="section">
        <div class="container">
            <div class="delivery-areas">
                <h2>Delivery Areas</h2>

                <div class="areas-grid">
                    <div class="area-card primary">
                        <h3>Kathmandu Valley</h3>
                        <p>Fast delivery within 1-4 hours</p>
                        <ul>
                            <li>Kathmandu</li>
                            <li>Lalitpur</li>
                            <li>Bhaktapur</li>
                        </ul>
                    </div>

                    <div class="area-card">
                        <h3>Pokhara</h3>
                        <p>Delivery within 24-48 hours</p>
                        <div class="area-note">Additional charges may apply</div>
                    </div>

                    <div class="area-card">
                        <h3>Other Cities</h3>
                        <p>Delivery within 2-5 days</p>
                        <div class="area-note">Contact us for availability</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Special Handling Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="special-handling">
                <h2>Special Care for Fresh Products</h2>
                <p>We take extra care with temperature-sensitive and perishable items to ensure they reach you in perfect condition.</p>

                <div class="handling-features">
                    <div class="feature-item">
                        <i class="fas fa-temperature-low"></i>
                        <h4>Cold Chain Delivery</h4>
                        <p>Dairy products, meats, and frozen items are delivered in insulated packaging with ice packs.</p>
                    </div>

                    <div class="feature-item">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Quality Guarantee</h4>
                        <p>All products are inspected before delivery. Report any quality issues within 24 hours.</p>
                    </div>

                    <div class="feature-item">
                        <i class="fas fa-hand-holding-heart"></i>
                        <h4>Careful Packaging</h4>
                        <p>Fragile items are carefully packaged to prevent damage during transit.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delivery Tracking Section -->
    <section class="section">
        <div class="container">
            <div class="tracking-info">
                <h2>Order Tracking</h2>
                <p>Stay updated on your order status with our real-time tracking system.</p>

                <div class="tracking-steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <h4>Order Confirmed</h4>
                        <p>Your order has been received and is being prepared.</p>
                    </div>

                    <div class="step">
                        <div class="step-number">2</div>
                        <h4>Order Packed</h4>
                        <p>Your items are carefully packed and ready for delivery.</p>
                    </div>

                    <div class="step">
                        <div class="step-number">3</div>
                        <h4>Out for Delivery</h4>
                        <p>Your order is on its way to your location.</p>
                    </div>

                    <div class="step">
                        <div class="step-number">4</div>
                        <h4>Delivered</h4>
                        <p>Your order has been successfully delivered.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="contact-shipping">
                <h2>Questions About Shipping?</h2>
                <p>Our customer service team is here to help with any shipping-related questions.</p>

                <div class="contact-options">
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <h4>Call Us</h4>
                        <p>+977-9851234567</p>
                    </div>

                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <h4>Email Us</h4>
                        <p>care@doko.com.np</p>
                    </div>

                    <div class="contact-item">
                        <i class="fas fa-comments"></i>
                        <h4>Live Chat</h4>
                        <p>Available 8 AM - 8 PM</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
/* Shipping Hero */
.shipping-hero {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    color: white;
    padding: 60px 0;
    text-align: center;
}

.shipping-hero .hero-content h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.shipping-hero .hero-content p {
    font-size: 1.2rem;
    opacity: 0.9;
}

/* Delivery Options */
.delivery-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.option-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.option-card:hover {
    transform: translateY(-5px);
}

.option-card.featured {
    border: 2px solid #4CAF50;
    position: relative;
}

.option-card.featured::before {
    content: "Most Popular";
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: #4CAF50;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.option-icon {
    font-size: 3rem;
    color: #4CAF50;
    margin-bottom: 1rem;
}

.option-card h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #333;
}

.option-card p {
    color: #666;
    margin-bottom: 1.5rem;
}

.option-price {
    font-size: 1.8rem;
    font-weight: 700;
    color: #4CAF50;
}

/* Free Shipping */
.free-shipping {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
    align-items: center;
}

.free-shipping-content h2 {
    color: #4CAF50;
    margin-bottom: 1rem;
}

.free-shipping-threshold {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-top: 2rem;
}

.threshold-amount {
    font-size: 2.5rem;
    font-weight: 700;
    color: #4CAF50;
    margin-bottom: 0.5rem;
}

.free-shipping-image {
    text-align: center;
    font-size: 8rem;
    color: #4CAF50;
    opacity: 0.7;
}

/* Delivery Areas */
.areas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.area-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.area-card.primary {
    border: 2px solid #4CAF50;
}

.area-card h3 {
    color: #333;
    margin-bottom: 1rem;
}

.area-card ul {
    list-style: none;
    padding: 0;
}

.area-card li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.area-card li:last-child {
    border-bottom: none;
}

.area-note {
    background: #fff3cd;
    color: #856404;
    padding: 0.5rem;
    border-radius: 6px;
    margin-top: 1rem;
    font-size: 0.9rem;
}

/* Special Handling */
.handling-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.feature-item {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.feature-item i {
    font-size: 3rem;
    color: #4CAF50;
    margin-bottom: 1rem;
}

.feature-item h4 {
    color: #333;
    margin-bottom: 1rem;
}

/* Tracking Steps */
.tracking-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.step {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.step-number {
    width: 50px;
    height: 50px;
    background: #4CAF50;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 auto 1rem;
}

.step h4 {
    color: #333;
    margin-bottom: 1rem;
}

/* Contact Options */
.contact-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.contact-item {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.contact-item i {
    font-size: 2.5rem;
    color: #4CAF50;
    margin-bottom: 1rem;
}

.contact-item h4 {
    color: #333;
    margin-bottom: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .shipping-hero .hero-content h1 {
        font-size: 2rem;
    }

    .delivery-options,
    .areas-grid,
    .handling-features,
    .tracking-steps,
    .contact-options {
        grid-template-columns: 1fr;
    }

    .free-shipping {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .free-shipping-image {
        font-size: 5rem;
    }
}

@media (max-width: 480px) {
    .option-card,
    .area-card,
    .feature-item,
    .step,
    .contact-item {
        padding: 1.5rem;
    }

    .option-icon,
    .feature-item i {
        font-size: 2.5rem;
    }
}
</style>

<?php
// Include footer
include_footer([], "document.addEventListener('DOMContentLoaded',function(){const f=document.querySelector('.newsletter-form-element');if(f){f.addEventListener('submit',function(e){e.preventDefault();const email=this.querySelector('input[name=email]').value.trim();if(!email)return;fetch('api/newsletter.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email})}).then(r=>r.json()).then(d=>{alert(d.success?'Thank you for subscribing!':'Error: '+d.message);if(d.success) this.reset();}).catch(err=>{console.error('Newsletter error',err);alert('An error occurred.');});});}});");
?>

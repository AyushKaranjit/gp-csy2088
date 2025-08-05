<?php
// Start session and include configuration
session_start();
require_once '../template/config.php';

// Page-specific variables
$page_title = page_title('Shipping Information');
$page_description = 'Learn about DOKO\'s shipping and delivery options. Fast, reliable delivery across Kathmandu Valley.';
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
<?php include '../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <!-- Shipping Hero Section -->
    <section class="shipping-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Shipping & Delivery</h1>
                <p>Fast, reliable delivery of fresh groceries to your doorstep</p>
            </div>
        </div>
    </section>

    <!-- Delivery Areas Section -->
    <section class="section">
        <div class="container">
            <div class="delivery-overview">
                <h2>Delivery Areas</h2>
                <p>We currently deliver across Kathmandu Valley with plans to expand nationwide</p>
                
                <div class="delivery-areas">
                    <div class="area-card active">
                        <div class="area-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>Kathmandu</h3>
                        <p>All areas within Kathmandu city</p>
                        <ul>
                            <li>Thamel</li>
                            <li>New Baneshwor</li>
                            <li>Putalisadak</li>
                            <li>Baluwatar</li>
                            <li>And more...</li>
                        </ul>
                    </div>
                    
                    <div class="area-card active">
                        <div class="area-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>Lalitpur</h3>
                        <p>Complete Lalitpur district coverage</p>
                        <ul>
                            <li>Patan</li>
                            <li>Jawalakhel</li>
                            <li>Pulchowk</li>
                            <li>Sanepa</li>
                            <li>And more...</li>
                        </ul>
                    </div>
                    
                    <div class="area-card active">
                        <div class="area-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>Bhaktapur</h3>
                        <p>Historic city and surrounding areas</p>
                        <ul>
                            <li>Bhaktapur Durbar</li>
                            <li>Suryabinayak</li>
                            <li>Madhyapur</li>
                            <li>Changunarayan</li>
                            <li>And more...</li>
                        </ul>
                    </div>
                    
                    <div class="area-card coming-soon">
                        <div class="area-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Other Cities</h3>
                        <p>Coming Soon</p>
                        <ul>
                            <li>Pokhara</li>
                            <li>Chitwan</li>
                            <li>Butwal</li>
                            <li>Dharan</li>
                            <li>More cities...</li>
                        </ul>
                        <div class="coming-soon-badge">Coming Soon</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delivery Options Section -->
    <section class="section delivery-options-section">
        <div class="container">
            <h2 class="section-title">Delivery Options</h2>
            <div class="delivery-options">
                <div class="delivery-option">
                    <div class="option-header">
                        <div class="option-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="option-info">
                            <h3>Standard Delivery</h3>
                            <p class="delivery-time">1-2 Business Days</p>
                        </div>
                        <div class="option-price">
                            <span class="price">Rs. 100</span>
                        </div>
                    </div>
                    <div class="option-details">
                        <ul>
                            <li>Free for orders above Rs. 2,000</li>
                            <li>Delivery between 9 AM - 6 PM</li>
                            <li>Order tracking available</li>
                            <li>SMS and email notifications</li>
                        </ul>
                    </div>
                </div>
                
                <div class="delivery-option featured">
                    <div class="popular-badge">Most Popular</div>
                    <div class="option-header">
                        <div class="option-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="option-info">
                            <h3>Express Delivery</h3>
                            <p class="delivery-time">Same Day / Next Day</p>
                        </div>
                        <div class="option-price">
                            <span class="price">Rs. 200</span>
                        </div>
                    </div>
                    <div class="option-details">
                        <ul>
                            <li>Same-day delivery for orders before 12 PM</li>
                            <li>Next-day delivery for orders after 12 PM</li>
                            <li>Priority handling</li>
                            <li>Real-time tracking</li>
                        </ul>
                    </div>
                </div>
                
                <div class="delivery-option">
                    <div class="option-header">
                        <div class="option-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="option-info">
                            <h3>Scheduled Delivery</h3>
                            <p class="delivery-time">Choose Your Time</p>
                        </div>
                        <div class="option-price">
                            <span class="price">Rs. 150</span>
                        </div>
                    </div>
                    <div class="option-details">
                        <ul>
                            <li>Choose your preferred date and time</li>
                            <li>Available up to 7 days in advance</li>
                            <li>Morning (9-12) or Afternoon (2-6) slots</li>
                            <li>Perfect for planning ahead</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delivery Process -->
    <section class="section process-section">
        <div class="container">
            <h2 class="section-title">How Delivery Works</h2>
            <div class="process-steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Order Placed</h3>
                        <p>Your order is received and confirmed</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Preparing</h3>
                        <p>Fresh items are picked and packed carefully</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Out for Delivery</h3>
                        <p>Your order is on the way with our delivery partner</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Delivered</h3>
                        <p>Fresh groceries delivered to your doorstep</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delivery Info -->
    <section class="section info-section">
        <div class="container">
            <div class="info-grid">
                <div class="info-card">
                    <h3>Delivery Guidelines</h3>
                    <ul>
                        <li>Someone must be available to receive the order</li>
                        <li>Please provide accurate address and phone number</li>
                        <li>Check items immediately upon delivery</li>
                        <li>Report any issues within 24 hours</li>
                        <li>Keep your phone accessible for delivery updates</li>
                    </ul>
                </div>
                
                <div class="info-card">
                    <h3>What We Deliver</h3>
                    <ul>
                        <li>Fresh vegetables and fruits</li>
                        <li>Dairy products and eggs</li>
                        <li>Meat and seafood</li>
                        <li>Pantry staples and spices</li>
                        <li>Household essentials</li>
                    </ul>
                </div>
                
                <div class="info-card">
                    <h3>Packaging</h3>
                    <ul>
                        <li>Eco-friendly packaging materials</li>
                        <li>Temperature-controlled bags for perishables</li>
                        <li>Separate packaging for different item types</li>
                        <li>Secure packaging to prevent damage</li>
                        <li>Minimal plastic use for environmental care</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section faq-section">
        <div class="container">
            <h2 class="section-title">Shipping FAQ</h2>
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question" data-faq="1">
                        <span>Do you deliver on weekends?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-1">
                        <p>Yes, we deliver 7 days a week including weekends. However, delivery times may vary on holidays.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" data-faq="2">
                        <span>Can I track my delivery?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-2">
                        <p>Yes, you'll receive tracking information via SMS and email once your order is out for delivery.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" data-faq="3">
                        <span>What if I'm not home during delivery?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-3">
                        <p>Our delivery partner will contact you. You can reschedule delivery for the same day or next day at no extra cost.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" data-faq="4">
                        <span>Is there a minimum order amount for delivery?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-4">
                        <p>Yes, the minimum order amount is Rs. 500. Orders above Rs. 2,000 qualify for free standard delivery.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
.shipping-hero {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: var(--white);
    padding: 4rem 0;
    text-align: center;
}

.shipping-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.shipping-hero p {
    font-size: 1.1rem;
    opacity: 0.9;
}

.delivery-areas {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.area-card {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    text-align: center;
    position: relative;
    transition: var(--transition);
}

.area-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.area-card.active {
    border: 2px solid var(--primary-color);
}

.area-card.coming-soon {
    opacity: 0.7;
    border: 2px solid #ddd;
}

.area-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.area-icon i {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.coming-soon .area-icon {
    background: #f8f9fa;
}

.coming-soon .area-icon i {
    color: #6c757d;
}

.area-card h3 {
    margin-bottom: 0.5rem;
    color: var(--dark-text);
}

.area-card ul {
    list-style: none;
    padding: 0;
    margin-top: 1rem;
    text-align: left;
}

.area-card li {
    padding: 0.25rem 0;
    color: var(--light-text);
    font-size: 0.9rem;
}

.coming-soon-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #ffc107;
    color: #000;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
}

.delivery-options-section {
    background: var(--light-bg);
}

.delivery-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.delivery-option {
    background: var(--white);
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    position: relative;
    transition: var(--transition);
}

.delivery-option:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
}

.delivery-option.featured {
    border: 2px solid var(--primary-color);
}

.popular-badge {
    position: absolute;
    top: 0;
    right: 0;
    background: var(--primary-color);
    color: var(--white);
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    clip-path: polygon(0 0, 100% 0, 100% 100%, 15% 100%);
}

.option-header {
    display: flex;
    align-items: center;
    padding: 2rem;
    gap: 1rem;
}

.option-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.option-icon i {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.option-info {
    flex: 1;
}

.option-info h3 {
    margin-bottom: 0.5rem;
    color: var(--dark-text);
}

.delivery-time {
    color: var(--light-text);
    font-size: 0.9rem;
}

.option-price {
    text-align: right;
}

.price {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-color);
}

.option-details {
    padding: 0 2rem 2rem;
    border-top: 1px solid #eee;
    padding-top: 1rem;
}

.option-details ul {
    list-style: none;
    padding: 0;
}

.option-details li {
    padding: 0.5rem 0;
    position: relative;
    padding-left: 1.5rem;
    color: var(--light-text);
    font-size: 0.9rem;
}

.option-details li:before {
    content: '✓';
    position: absolute;
    left: 0;
    color: var(--primary-color);
    font-weight: 600;
}

.process-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.step {
    text-align: center;
}

.step-number {
    width: 60px;
    height: 60px;
    background: var(--primary-color);
    color: var(--white);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 auto 1rem;
}

.step h3 {
    margin-bottom: 0.5rem;
    color: var(--dark-text);
}

.info-section {
    background: var(--light-bg);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.info-card {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.info-card h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-color);
}

.info-card ul {
    list-style: none;
    padding: 0;
}

.info-card li {
    padding: 0.5rem 0;
    position: relative;
    padding-left: 1.5rem;
    color: var(--dark-text);
}

.info-card li:before {
    content: '•';
    position: absolute;
    left: 0;
    color: var(--primary-color);
    font-weight: 600;
}

.faq-list {
    max-width: 800px;
    margin: 3rem auto 0;
}

.faq-item {
    background: var(--white);
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.faq-question {
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    font-weight: 600;
    color: var(--dark-text);
    transition: var(--transition);
}

.faq-question:hover {
    background: var(--light-bg);
}

.faq-question i {
    transition: transform 0.3s ease;
}

.faq-question.active i {
    transform: rotate(180deg);
}

.faq-answer {
    padding: 0 1.5rem;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, padding 0.3s ease;
}

.faq-answer.active {
    max-height: 200px;
    padding: 1.5rem;
}

@media (max-width: 768px) {
    .shipping-hero h1 {
        font-size: 2rem;
    }
    
    .delivery-areas {
        grid-template-columns: 1fr;
    }
    
    .delivery-options {
        grid-template-columns: 1fr;
    }
    
    .process-steps {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .process-steps {
        grid-template-columns: 1fr;
    }
    
    .option-header {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // FAQ functionality
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', function() {
            const faqIndex = this.dataset.faq;
            const answer = document.getElementById(`faq-${faqIndex}`);
            
            // Close all other FAQ items
            document.querySelectorAll('.faq-question').forEach(q => {
                if (q !== this) {
                    q.classList.remove('active');
                }
            });
            
            document.querySelectorAll('.faq-answer').forEach(a => {
                if (a !== answer) {
                    a.classList.remove('active');
                }
            });
            
            // Toggle current FAQ item
            this.classList.toggle('active');
            answer.classList.toggle('active');
        });
    });
});
</script>

<?php
// Include footer
include_footer();
?>

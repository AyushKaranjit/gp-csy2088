<?php
// Start session and include configuration
session_start();
require_once __DIR__ . '/../template/config.php';

// Page-specific variables
$page_title = page_title('Help Center');
$page_description = 'Get help and support for your DOKO shopping experience. Find answers to common questions and contact our support team.';
$current_page = 'help';

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Help Center', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php include __DIR__ . '/../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <!-- Help Hero Section -->
    <section class="help-hero">
        <div class="container">
            <div class="hero-content">
                <h1>How can we help you?</h1>
                <p>Find answers to your questions and get the support you need</p>
                <div class="help-search">
                    <input type="text" placeholder="Search for help topics..." class="help-search-input">
                    <button class="btn btn-primary">Search</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Help Categories -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Popular Help Topics</h2>
            <div class="help-categories">
                <div class="help-category">
                    <div class="help-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Orders & Delivery</h3>
                    <p>Track orders, delivery info, and order issues</p>
                    <a href="#orders" class="btn btn-outline">Learn More</a>
                </div>
                <div class="help-category">
                    <div class="help-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3>Payment & Billing</h3>
                    <p>Payment methods, refunds, and billing questions</p>
                    <a href="#payment" class="btn btn-outline">Learn More</a>
                </div>
                <div class="help-category">
                    <div class="help-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h3>Account & Profile</h3>
                    <p>Manage your account, password, and preferences</p>
                    <a href="#account" class="btn btn-outline">Learn More</a>
                </div>
                <div class="help-category">
                    <div class="help-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <h3>Returns & Refunds</h3>
                    <p>Return policy, refund process, and exchanges</p>
                    <a href="#returns" class="btn btn-outline">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section faq-section">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="faq-grid">
                <div class="faq-column">
                    <h3>Ordering & Shopping</h3>
                    <div class="faq-item">
                        <div class="faq-question" data-faq="1">
                            <span>How do I place an order?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer" id="faq-1">
                            <p>Browse our products, add items to your cart, and proceed to checkout. You'll need to provide delivery information and choose a payment method.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" data-faq="2">
                            <span>What is the minimum order amount?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer" id="faq-2">
                            <p>The minimum order amount is Rs. 500. Orders above Rs. 2000 qualify for free delivery.</p>
                        </div>
                    </div>
                </div>
                
                <div class="faq-column">
                    <h3>Delivery & Shipping</h3>
                    <div class="faq-item">
                        <div class="faq-question" data-faq="3">
                            <span>What are your delivery areas?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer" id="faq-3">
                            <p>We deliver across Kathmandu Valley including Kathmandu, Lalitpur, and Bhaktapur. We're expanding to other cities soon.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" data-faq="4">
                            <span>How long does delivery take?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer" id="faq-4">
                            <p>Standard delivery takes 1-2 business days. Express delivery is available for same-day or next-day delivery.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Support -->
    <section class="section contact-support">
        <div class="container">
            <div class="support-card">
                <h2>Still need help?</h2>
                <p>Our customer support team is here to assist you</p>
                <div class="support-options">
                    <div class="support-option">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Call Us</h4>
                            <p>+977-9851234567</p>
                            <small>Mon-Fri: 9AM-6PM</small>
                        </div>
                    </div>
                    <div class="support-option">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email Us</h4>
                            <p>support@doko.com.np</p>
                            <small>Response within 24 hours</small>
                        </div>
                    </div>
                    <div class="support-option">
                        <i class="fas fa-comments"></i>
                        <div>
                            <h4>Live Chat</h4>
                            <p>Chat with our team</p>
                            <small>Available now</small>
                        </div>
                    </div>
                </div>
                <a href="contact.php" class="btn btn-primary">Contact Support</a>
            </div>
        </div>
    </section>
</main>

<style>
.help-hero {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: var(--white);
    padding: 4rem 0;
    text-align: center;
}

.help-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.help-hero p {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.help-search {
    display: flex;
    max-width: 500px;
    margin: 0 auto;
    gap: 1rem;
}

.help-search-input {
    flex: 1;
    padding: 1rem;
    border: none;
    border-radius: var(--border-radius);
    font-size: 1rem;
}

.help-categories {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.help-category {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    text-align: center;
    transition: var(--transition);
}

.help-category:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.help-icon {
    width: 80px;
    height: 80px;
    background: var(--primary-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.help-icon i {
    font-size: 2rem;
    color: var(--primary-color);
}

.help-category h3 {
    margin-bottom: 1rem;
    color: var(--dark-text);
}

.help-category p {
    color: var(--light-text);
    margin-bottom: 1.5rem;
}

.faq-section {
    background: var(--light-bg);
}

.faq-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 3rem;
    margin-top: 3rem;
}

.faq-column h3 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-color);
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

.contact-support {
    background: var(--gradient-primary);
    color: var(--white);
}

.support-card {
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.support-card h2 {
    margin-bottom: 1rem;
}

.support-card p {
    font-size: 1.1rem;
    margin-bottom: 3rem;
    opacity: 0.9;
}

.support-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.support-option {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.support-option i {
    font-size: 2rem;
    opacity: 0.8;
}

.support-option h4 {
    margin-bottom: 0.5rem;
}

.support-option p {
    margin: 0;
    font-weight: 600;
}

.support-option small {
    opacity: 0.8;
}

@media (max-width: 768px) {
    .help-hero h1 {
        font-size: 2rem;
    }
    
    .help-search {
        flex-direction: column;
    }
    
    .faq-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .support-options {
        grid-template-columns: 1fr;
        text-align: left;
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

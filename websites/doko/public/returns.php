<?php
// Start session and include configuration
session_start();
require_once '../template/config.php';

// Page-specific variables
$page_title = page_title('Returns & Refunds');
$page_description = 'Learn about DOKO\'s return and refund policy. Easy returns for fresh groceries and quality guarantee.';
$current_page = 'returns';

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Returns & Refunds', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php include '../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <!-- Returns Hero Section -->
    <section class="returns-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Returns & Refunds</h1>
                <p>We stand behind our products with a satisfaction guarantee</p>
            </div>
        </div>
    </section>

    <!-- Return Policy Section -->
    <section class="section">
        <div class="container">
            <div class="policy-content">
                <div class="policy-overview">
                    <h2>Our Return Policy</h2>
                    <p>At DOKO, we want you to be completely satisfied with your purchase. If you're not happy with any item, we offer easy returns and refunds.</p>
                    
                    <div class="policy-highlights">
                        <div class="highlight-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>24-Hour Return Window</h4>
                                <p>Report quality issues within 24 hours of delivery</p>
                            </div>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <h4>Quality Guarantee</h4>
                                <p>Fresh products guaranteed or your money back</p>
                            </div>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-undo"></i>
                            <div>
                                <h4>Easy Process</h4>
                                <p>Simple online return process with quick refunds</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="return-categories">
                    <h2>What Can Be Returned?</h2>
                    
                    <div class="category-grid">
                        <div class="category-card returnable">
                            <div class="category-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3>Returnable Items</h3>
                            <ul>
                                <li>Damaged or spoiled fresh produce</li>
                                <li>Expired or near-expiry products</li>
                                <li>Wrong items delivered</li>
                                <li>Missing items from your order</li>
                                <li>Packaged goods with quality issues</li>
                            </ul>
                        </div>
                        
                        <div class="category-card non-returnable">
                            <div class="category-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <h3>Non-Returnable Items</h3>
                            <ul>
                                <li>Items consumed or used</li>
                                <li>Products without quality issues</li>
                                <li>Items returned after 24 hours</li>
                                <li>Custom or special orders</li>
                                <li>Items damaged by customer</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Return Process Section -->
    <section class="section process-section">
        <div class="container">
            <h2 class="section-title">How to Return Items</h2>
            <div class="process-steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Report the Issue</h3>
                        <p>Contact us within 24 hours of delivery through phone, email, or our website</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Provide Details</h3>
                        <p>Share your order number and photos of the damaged or incorrect items</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Get Approval</h3>
                        <p>Our team will review and approve your return request</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Receive Refund</h3>
                        <p>Get your refund processed within 2-3 business days</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Refund Information -->
    <section class="section refund-section">
        <div class="container">
            <div class="refund-grid">
                <div class="refund-info">
                    <h2>Refund Information</h2>
                    <div class="refund-details">
                        <div class="refund-item">
                            <h4>Refund Timeline</h4>
                            <p>Approved refunds are processed within 2-3 business days</p>
                        </div>
                        <div class="refund-item">
                            <h4>Refund Method</h4>
                            <p>Refunds are issued to your original payment method</p>
                        </div>
                        <div class="refund-item">
                            <h4>Partial Refunds</h4>
                            <p>Available for partial damage or missing items</p>
                        </div>
                        <div class="refund-item">
                            <h4>Store Credit</h4>
                            <p>Option to receive store credit for future purchases</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-card">
                    <h3>Need to Return Something?</h3>
                    <p>Contact our customer service team</p>
                    <div class="contact-options">
                        <div class="contact-option">
                            <i class="fas fa-phone"></i>
                            <span>+977-9851234567</span>
                        </div>
                        <div class="contact-option">
                            <i class="fas fa-envelope"></i>
                            <span>returns@doko.com.np</span>
                        </div>
                        <div class="contact-option">
                            <i class="fas fa-clock"></i>
                            <span>Mon-Fri: 9AM-6PM</span>
                        </div>
                    </div>
                    <a href="contact.php" class="btn btn-primary">Contact Support</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section faq-section">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question" data-faq="1">
                        <span>What if I receive damaged items?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-1">
                        <p>Contact us immediately with photos of the damaged items. We'll process a full refund or replacement at no extra cost.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" data-faq="2">
                        <span>Can I return fresh produce?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-2">
                        <p>Yes, if the fresh produce is damaged, spoiled, or not meeting quality standards upon delivery.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" data-faq="3">
                        <span>How long do refunds take?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-3">
                        <p>Approved refunds are processed within 2-3 business days and will appear in your original payment method.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question" data-faq="4">
                        <span>What if I ordered the wrong item?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-4">
                        <p>Customer errors are generally non-returnable, but we'll work with you on a case-by-case basis for unopened items.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
.returns-hero {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: var(--white);
    padding: 4rem 0;
    text-align: center;
}

.returns-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.returns-hero p {
    font-size: 1.1rem;
    opacity: 0.9;
}

.policy-overview {
    margin-bottom: 3rem;
}

.policy-overview h2 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.policy-highlights {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.highlight-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--light-bg);
    border-radius: var(--border-radius);
}

.highlight-item i {
    font-size: 2rem;
    color: var(--primary-color);
}

.highlight-item h4 {
    margin-bottom: 0.5rem;
    color: var(--dark-text);
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.category-card {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.category-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.returnable .category-icon {
    background: #d4edda;
}

.returnable .category-icon i {
    color: #28a745;
    font-size: 1.5rem;
}

.non-returnable .category-icon {
    background: #f8d7da;
}

.non-returnable .category-icon i {
    color: #dc3545;
    font-size: 1.5rem;
}

.category-card h3 {
    margin-bottom: 1rem;
    color: var(--dark-text);
}

.category-card ul {
    list-style: none;
    padding: 0;
}

.category-card li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
    position: relative;
    padding-left: 1.5rem;
}

.category-card li:before {
    content: '•';
    position: absolute;
    left: 0;
    color: var(--primary-color);
}

.process-section {
    background: var(--light-bg);
}

.process-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.step {
    text-align: center;
    position: relative;
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
    margin-bottom: 1rem;
    color: var(--dark-text);
}

.refund-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
    margin-top: 2rem;
}

.refund-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.refund-item h4 {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.contact-card {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    text-align: center;
    height: fit-content;
}

.contact-card h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.contact-options {
    margin: 2rem 0;
}

.contact-option {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem 0;
    justify-content: center;
}

.contact-option i {
    color: var(--primary-color);
    width: 20px;
}

.faq-section {
    background: var(--light-bg);
}

.faq-list {
    max-width: 800px;
    margin: 0 auto;
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
    .returns-hero h1 {
        font-size: 2rem;
    }
    
    .category-grid {
        grid-template-columns: 1fr;
    }
    
    .process-steps {
        grid-template-columns: 1fr;
    }
    
    .refund-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .refund-details {
        grid-template-columns: 1fr;
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

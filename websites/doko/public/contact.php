<?php
// Start session and include configuration
session_start();
require_once __DIR__ . '/../template/config.php';

// Handle form submission (default to GET when REQUEST_METHOD not set, e.g., CLI smoke tests)
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($requestMethod === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    $errors = [];
    
    // Validate CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = 'Invalid security token. Please try again.';
    }
    
    // Validate required fields
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required.';
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        // In a real application, you would:
        // 1. Save the message to database
        // 2. Send email notification to admin
        // 3. Send confirmation email to user
        
        // For now, we'll just set a success message
        set_flash_message('success', 'Thank you for your message! We\'ll get back to you within 24 hours.');
        redirect('contact.php');
    } else {
        set_flash_message('error', implode('<br>', $errors));
    }
}

// Page-specific variables
$page_title = page_title('Contact Us');
$page_description = 'Get in touch with DOKO for any questions, feedback, or support. We\'re here to help with your grocery shopping needs.';
$current_page = 'contact';

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Contact Us', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php include __DIR__ . '/../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <!-- Contact Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Contact Us</h1>
                <p>We'd love to hear from you! Get in touch with our team for any questions, feedback, or support.</p>
            </div>
        </div>
    </section>

    <!-- Contact Information Section -->
    <section class="section">
        <div class="container">
            <div class="contact-info-grid">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Visit Our Store</h3>
                    <p>New Baneshwor, Kathmandu<br>Near Everest Bank<br>Nepal 44600</p>
                    <a href="https://maps.google.com" target="_blank" class="contact-link">
                        <i class="fas fa-external-link-alt"></i>
                        View on Map
                    </a>
                </div>

                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h3>Call Us</h3>
                    <p>
                        <a href="tel:+977-01-4567890">01-4567890</a><br>
                        <a href="tel:+977-9851234567">+977-9851234567</a>
                    </p>
                    <span class="contact-hours">Daily: 6:00 AM - 10:00 PM</span>
                </div>

                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email Us</h3>
                    <p>
                        <a href="mailto:care@doko.com.np">care@doko.com.np</a><br>
                        <a href="mailto:orders@doko.com.np">orders@doko.com.np</a>
                    </p>
                    <span class="contact-hours">Quick response guaranteed</span>
                </div>

                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Delivery Hours</h3>
                    <p>
                        Daily Delivery<br>
                        6:00 AM - 10:00 PM
                    </p>
                    <span class="contact-hours">Same day delivery available</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="contact-form-section">
                <div class="contact-form-content">
                    <div class="contact-form-text">
                        <h2>Send Us a Message</h2>
                        <p>Have a question, suggestion, or need help with your order? Fill out the form below and we'll get back to you as soon as possible.</p>
                        
                        <div class="contact-features">
                            <div class="feature">
                                <i class="fas fa-check-circle"></i>
                                <span>Quick response within 24 hours</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-check-circle"></i>
                                <span>Dedicated customer support team</span>
                            </div>
                            <div class="feature">
                                <i class="fas fa-check-circle"></i>
                                <span>Multiple ways to reach us</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-form-wrapper">
                        <!-- Display flash messages -->
                        <?php display_flash_message(); ?>
                        
                        <form class="contact-form" method="POST" action="contact.php">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Full Name *</label>
                                    <input type="text" id="name" name="name" required autocomplete="name" 
                                           value="<?php echo isset($_POST['name']) ? clean_output($_POST['name']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" required autocomplete="email"
                                           value="<?php echo isset($_POST['email']) ? clean_output($_POST['email']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" autocomplete="tel"
                                           value="<?php echo isset($_POST['phone']) ? clean_output($_POST['phone']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="subject">Subject *</label>
                                    <select id="subject" name="subject" required>
                                        <option value="">Select a subject</option>
                                        <option value="general" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'general') ? 'selected' : ''; ?>>General Inquiry</option>
                                        <option value="order" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'order') ? 'selected' : ''; ?>>Order Support</option>
                                        <option value="delivery" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'delivery') ? 'selected' : ''; ?>>Delivery Issue</option>
                                        <option value="product" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'product') ? 'selected' : ''; ?>>Product Question</option>
                                        <option value="complaint" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'complaint') ? 'selected' : ''; ?>>Complaint</option>
                                        <option value="suggestion" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'suggestion') ? 'selected' : ''; ?>>Suggestion</option>
                                        <option value="partnership" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'partnership') ? 'selected' : ''; ?>>Partnership</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Message *</label>
                                <textarea id="message" name="message" rows="6" required 
                                          placeholder="Please describe your inquiry in detail..."><?php echo isset($_POST['message']) ? clean_output($_POST['message']) : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i>
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Find quick answers to common questions</p>
            </div>
            
            <div class="faq-grid">
                <?php
                $faqs = [
                    [
                        'question' => 'What are your delivery hours?',
                        'answer' => 'We deliver 7 days a week from 7:00 AM to 9:00 PM. Same-day delivery is available for orders placed before 2:00 PM.'
                    ],
                    [
                        'question' => 'Do you offer free delivery?',
                        'answer' => 'Yes! We offer free delivery on all orders above Rs. 1000 within Kathmandu Valley. For orders below Rs. 1000, a delivery charge of Rs. 50 applies.'
                    ],
                    [
                        'question' => 'How can I track my order?',
                        'answer' => 'Once your order is confirmed, you\'ll receive an SMS with tracking details. You can also track your order by logging into your account on our website.'
                    ],
                    [
                        'question' => 'What if I receive damaged products?',
                        'answer' => 'We guarantee product quality. If you receive damaged or unsatisfactory products, contact us immediately for a replacement or full refund.'
                    ],
                    [
                        'question' => 'Can I modify or cancel my order?',
                        'answer' => 'You can modify or cancel your order within 30 minutes of placing it. After that, please contact our customer support for assistance.'
                    ],
                    [
                        'question' => 'Do you accept cash on delivery?',
                        'answer' => 'Yes, we accept cash on delivery as well as online payments through various methods including mobile banking and digital wallets.'
                    ]
                ];

                foreach ($faqs as $index => $faq): ?>
                <div class="faq-item">
                    <div class="faq-question" data-faq="<?php echo $index; ?>">
                        <h3><?php echo clean_output($faq['question']); ?></h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-<?php echo $index; ?>">
                        <p><?php echo clean_output($faq['answer']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="faq-footer">
                <p>Still have questions? <a href="#contact-form">Contact us directly</a> and we'll be happy to help!</p>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="section map-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Find Us</h2>
                <p class="section-subtitle">Visit our store or find us on the map</p>
            </div>
            
            <div class="map-container">
                <div class="map-wrapper" id="map-wrapper" data-map-lat="27.7148" data-map-lng="85.3123">
                    <img 
                        src="/images/kathmandu-map.jpg" 
                        alt="Kathmandu area placeholder while live map loads" 
                        class="map-static-img" 
                        width="1200" height="675" 
                        decoding="async" 
                        loading="lazy"
                        onload="this.classList.add('loaded');"
                        onerror="this.onerror=null;this.src='/images/kathmandu-map.svg';this.classList.add('loaded');" />
                    <div class="map-overlay-lite" aria-label="Map quick actions">
                        <a href="https://www.google.com/maps/place/Thamel,+Kathmandu" target="_blank" rel="noopener" class="btn btn-outline btn-sm" title="Open in Google Maps">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                    <noscript>
                        <div class="map-noscript">
                            <p>View our location on <a href="https://www.google.com/maps/place/Thamel,+Kathmandu" target="_blank" rel="noopener">Google Maps</a>.</p>
                        </div>
                    </noscript>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
.contact-hero {
    background: linear-gradient(135deg, rgba(44, 85, 48, 0.9), rgba(62, 123, 62, 0.9)),
                url('public/images/contact/hero-bg.jpg') center/cover no-repeat;
    padding: 4rem 0;
    color: white;
    text-align: center;
}

.contact-hero h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: white;
}

.contact-hero p {
    font-size: 1.2rem;
    max-width: 600px;
    margin: 0 auto;
    color: rgba(255,255,255,0.9);
}

.contact-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.contact-card {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    text-align: center;
    transition: var(--transition);
}

.contact-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.contact-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.contact-icon i {
    font-size: 1.5rem;
    color: white;
}

.contact-card h3 {
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.contact-card p {
    margin-bottom: 1rem;
    line-height: 1.6;
}

.contact-card a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.contact-card a:hover {
    color: var(--accent-color);
}

.contact-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.contact-hours {
    font-size: 0.9rem;
    color: var(--light-text);
    font-style: italic;
}

.contact-form-section {
    max-width: 1200px;
    margin: 0 auto;
}

.contact-form-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: start;
}

.contact-form-text h2 {
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.contact-form-text p {
    margin-bottom: 2rem;
    line-height: 1.8;
    color: var(--dark-text);
}

.contact-features {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.contact-features .feature {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.contact-features i {
    color: var(--primary-color);
    font-size: 1.1rem;
}

.contact-form-wrapper {
    background: var(--white);
    padding: 2.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.contact-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--dark-text);
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    font-size: 1rem;
    transition: var(--transition);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(44, 85, 48, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.alert {
    padding: 1rem;
    border-radius: var(--border-radius);
    margin-bottom: 1.5rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-dismissible {
    position: relative;
    padding-right: 3rem;
}

.close {
    position: absolute;
    top: 0.5rem;
    right: 1rem;
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: inherit;
}

.faq-grid {
    margin-top: 3rem;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

.faq-item {
    border: 1px solid #eee;
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
    overflow: hidden;
}

.faq-question {
    padding: 1.5rem;
    background: var(--white);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: var(--transition);
}

.faq-question:hover {
    background: var(--light-bg);
}

.faq-question h3 {
    margin: 0;
    color: var(--dark-text);
    font-size: 1.1rem;
}

.faq-question i {
    color: var(--primary-color);
    transition: var(--transition);
}

.faq-question.active i {
    transform: rotate(180deg);
}

.faq-answer {
    padding: 0 1.5rem;
    background: var(--light-bg);
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.faq-answer.active {
    max-height: 200px;
    padding: 1.5rem;
}

.faq-footer {
    text-align: center;
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid #eee;
}

.faq-footer a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.map-section {
    background: var(--light-bg);
}

.map-container {
    margin-top: 2rem;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    position: relative;
}

.map-wrapper {
    position: relative;
    width: 100%;
    height: 0;
    padding-bottom: 56.25%; /* 16:9 */
    background: #e2e8f0 url('/images/kathmandu-map.jpg') center/cover no-repeat;
}

.map-static-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.9) contrast(1.05);
    opacity: 0;
    transition: opacity .6s ease;
}
.map-static-img.loaded { opacity: 1; }
.map-wrapper iframe.google-map-bg {position:absolute;inset:0;width:100%;height:100%;border:0;opacity:0;transition:opacity .6s ease;}
.map-wrapper iframe.google-map-bg.visible{opacity:1;}

.map-wrapper iframe {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%; border: 0;
}

.map-overlay-lite {
    position: absolute;
    top: .75rem;
    right: .75rem;
    display: flex;
    gap: .5rem;
    background: rgba(0,0,0,0.35);
    padding: .5rem;
    border-radius: .5rem;
}
.map-overlay-lite .btn { padding: .4rem .6rem; }

.map-noscript { padding: 1rem; background:#fff; font-size:.9rem; }

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .contact-hero h1 {
        font-size: 2rem;
    }
    
    .contact-info-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .contact-form-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .contact-form-wrapper {
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .contact-hero {
        padding: 2rem 0;
    }
    
    .contact-hero h1 {
        font-size: 1.8rem;
    }
    
    .contact-card {
        padding: 1.5rem;
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
    
    // Close alert functionality
    document.querySelectorAll('.close').forEach(button => {
        button.addEventListener('click', function() {
            this.parentElement.style.display = 'none';
        });
    });
    
    // Form validation
    const form = document.querySelector('.contact-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    }
    // Lazy load Google Map
    const wrapper = document.getElementById('map-wrapper');
    function injectMap(){
        if(!wrapper || wrapper.dataset.mapLoaded) return;
        const iframe=document.createElement('iframe');
        iframe.className='google-map-bg';
        iframe.loading='lazy';
        iframe.referrerPolicy='no-referrer-when-downgrade';
        iframe.allowFullscreen=true;
        iframe.src='https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3532.258318116201!2d85.30860447524144!3d27.70903142514662!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39eb19d7d8b3db01%3A0x4ddf5b8e1b1c2b4d!2sThamel%2C%20Kathmandu!5e0!3m2!1sen!2snp!4v1691400000000';
        iframe.addEventListener('load',()=>{ iframe.classList.add('visible'); });
        wrapper.appendChild(iframe);
        wrapper.dataset.mapLoaded='1';
    }
    if('IntersectionObserver' in window && wrapper){
        const io=new IntersectionObserver(entries=>{entries.forEach(en=>{if(en.isIntersecting){injectMap();io.disconnect();}});},{rootMargin:'0px 0px 200px 0px'});
        io.observe(wrapper);
    } else {
        // Fallback: inject after slight delay
        setTimeout(injectMap,1200);
    }
});
</script>

<?php
// Include footer
include_footer();
?>

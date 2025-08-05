<?php
// Start session and include configuration
session_start();
require_once '../template/config.php';

// Page-specific variables
$page_title = page_title('Privacy Policy');
$page_description = 'DOKO\'s commitment to protecting your privacy and personal information. Learn how we collect, use, and protect your data.';
$current_page = 'privacy';

// Breadcrumb items
$breadcrumb_items = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Privacy Policy', 'url' => '']
];

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Breadcrumb -->
<?php include '../template/breadcrumb.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <!-- Privacy Hero Section -->
    <section class="privacy-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Privacy Policy</h1>
                <p>Your privacy is important to us. Learn how we protect your personal information.</p>
                <div class="last-updated">Last updated: August 5, 2025</div>
            </div>
        </div>
    </section>

    <!-- Privacy Content -->
    <section class="section">
        <div class="container">
            <div class="privacy-content">
                <div class="privacy-nav">
                    <h3>Quick Navigation</h3>
                    <ul>
                        <li><a href="#information-collection">Information We Collect</a></li>
                        <li><a href="#information-use">How We Use Information</a></li>
                        <li><a href="#information-sharing">Information Sharing</a></li>
                        <li><a href="#data-security">Data Security</a></li>
                        <li><a href="#cookies">Cookies & Tracking</a></li>
                        <li><a href="#your-rights">Your Rights</a></li>
                        <li><a href="#contact-us">Contact Us</a></li>
                    </ul>
                </div>

                <div class="privacy-main">
                    <!-- Introduction -->
                    <div class="privacy-section">
                        <h2>Introduction</h2>
                        <p>At DOKO ("we," "our," or "us"), we are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services.</p>
                        <p>By using our website and services, you consent to the collection and use of your information in accordance with this Privacy Policy.</p>
                    </div>

                    <!-- Information We Collect -->
                    <div class="privacy-section" id="information-collection">
                        <h2>Information We Collect</h2>
                        
                        <h3>Personal Information</h3>
                        <p>We may collect the following personal information:</p>
                        <ul>
                            <li><strong>Account Information:</strong> Name, email address, phone number, password</li>
                            <li><strong>Profile Information:</strong> Date of birth, gender, profile picture</li>
                            <li><strong>Address Information:</strong> Delivery addresses, billing addresses</li>
                            <li><strong>Payment Information:</strong> Payment method details (processed securely)</li>
                            <li><strong>Order Information:</strong> Purchase history, order preferences</li>
                        </ul>

                        <h3>Automatically Collected Information</h3>
                        <ul>
                            <li><strong>Device Information:</strong> IP address, browser type, operating system</li>
                            <li><strong>Usage Information:</strong> Pages visited, time spent, click patterns</li>
                            <li><strong>Location Information:</strong> General location based on IP address</li>
                            <li><strong>Cookies and Tracking:</strong> Website preferences, shopping cart contents</li>
                        </ul>
                    </div>

                    <!-- How We Use Information -->
                    <div class="privacy-section" id="information-use">
                        <h2>How We Use Your Information</h2>
                        <p>We use your information for the following purposes:</p>
                        
                        <div class="use-categories">
                            <div class="use-category">
                                <h4>Service Provision</h4>
                                <ul>
                                    <li>Process and fulfill your orders</li>
                                    <li>Manage your account and profile</li>
                                    <li>Provide customer support</li>
                                    <li>Process payments and refunds</li>
                                </ul>
                            </div>
                            
                            <div class="use-category">
                                <h4>Communication</h4>
                                <ul>
                                    <li>Send order confirmations and updates</li>
                                    <li>Notify you about promotions and offers</li>
                                    <li>Respond to your inquiries</li>
                                    <li>Send important account information</li>
                                </ul>
                            </div>
                            
                            <div class="use-category">
                                <h4>Improvement & Analytics</h4>
                                <ul>
                                    <li>Analyze website usage and performance</li>
                                    <li>Improve our products and services</li>
                                    <li>Personalize your shopping experience</li>
                                    <li>Conduct research and analytics</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Information Sharing -->
                    <div class="privacy-section" id="information-sharing">
                        <h2>Information Sharing and Disclosure</h2>
                        <p>We do not sell, trade, or rent your personal information to third parties. We may share your information in the following circumstances:</p>
                        
                        <div class="sharing-item">
                            <h4>Service Providers</h4>
                            <p>We may share information with trusted third-party service providers who assist us in:</p>
                            <ul>
                                <li>Payment processing</li>
                                <li>Order fulfillment and delivery</li>
                                <li>Customer support</li>
                                <li>Marketing and analytics</li>
                            </ul>
                        </div>
                        
                        <div class="sharing-item">
                            <h4>Legal Requirements</h4>
                            <p>We may disclose your information if required by law or to:</p>
                            <ul>
                                <li>Comply with legal processes</li>
                                <li>Protect our rights and property</li>
                                <li>Ensure user safety</li>
                                <li>Prevent fraud or illegal activities</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Data Security -->
                    <div class="privacy-section" id="data-security">
                        <h2>Data Security</h2>
                        <p>We implement appropriate security measures to protect your personal information:</p>
                        
                        <div class="security-measures">
                            <div class="security-item">
                                <i class="fas fa-shield-alt"></i>
                                <div>
                                    <h4>Encryption</h4>
                                    <p>All sensitive data is encrypted in transit and at rest</p>
                                </div>
                            </div>
                            
                            <div class="security-item">
                                <i class="fas fa-lock"></i>
                                <div>
                                    <h4>Secure Storage</h4>
                                    <p>Data is stored on secure servers with restricted access</p>
                                </div>
                            </div>
                            
                            <div class="security-item">
                                <i class="fas fa-user-shield"></i>
                                <div>
                                    <h4>Access Control</h4>
                                    <p>Limited access to personal information on a need-to-know basis</p>
                                </div>
                            </div>
                            
                            <div class="security-item">
                                <i class="fas fa-eye"></i>
                                <div>
                                    <h4>Regular Monitoring</h4>
                                    <p>Continuous monitoring for security vulnerabilities</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cookies -->
                    <div class="privacy-section" id="cookies">
                        <h2>Cookies and Tracking Technologies</h2>
                        <p>We use cookies and similar technologies to enhance your experience:</p>
                        
                        <div class="cookie-types">
                            <div class="cookie-type">
                                <h4>Essential Cookies</h4>
                                <p>Required for basic website functionality, including:</p>
                                <ul>
                                    <li>User authentication</li>
                                    <li>Shopping cart functionality</li>
                                    <li>Security features</li>
                                </ul>
                            </div>
                            
                            <div class="cookie-type">
                                <h4>Performance Cookies</h4>
                                <p>Help us understand how you use our website:</p>
                                <ul>
                                    <li>Page views and navigation</li>
                                    <li>Error tracking</li>
                                    <li>Site performance analysis</li>
                                </ul>
                            </div>
                            
                            <div class="cookie-type">
                                <h4>Marketing Cookies</h4>
                                <p>Used for personalized advertising:</p>
                                <ul>
                                    <li>Targeted advertisements</li>
                                    <li>Social media integration</li>
                                    <li>Email marketing</li>
                                </ul>
                            </div>
                        </div>
                        
                        <p>You can control cookies through your browser settings, but disabling certain cookies may affect website functionality.</p>
                    </div>

                    <!-- Your Rights -->
                    <div class="privacy-section" id="your-rights">
                        <h2>Your Privacy Rights</h2>
                        <p>You have the following rights regarding your personal information:</p>
                        
                        <div class="rights-grid">
                            <div class="right-item">
                                <h4>Access</h4>
                                <p>Request a copy of your personal information</p>
                            </div>
                            
                            <div class="right-item">
                                <h4>Correction</h4>
                                <p>Update or correct inaccurate information</p>
                            </div>
                            
                            <div class="right-item">
                                <h4>Deletion</h4>
                                <p>Request deletion of your personal information</p>
                            </div>
                            
                            <div class="right-item">
                                <h4>Portability</h4>
                                <p>Receive your data in a portable format</p>
                            </div>
                            
                            <div class="right-item">
                                <h4>Opt-out</h4>
                                <p>Unsubscribe from marketing communications</p>
                            </div>
                            
                            <div class="right-item">
                                <h4>Restriction</h4>
                                <p>Limit how we process your information</p>
                            </div>
                        </div>
                        
                        <p>To exercise these rights, please contact us using the information provided below.</p>
                    </div>

                    <!-- Contact -->
                    <div class="privacy-section" id="contact-us">
                        <h2>Contact Us</h2>
                        <p>If you have questions about this Privacy Policy or our privacy practices, please contact us:</p>
                        
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <h4>Email</h4>
                                    <p>privacy@doko.com.np</p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <h4>Phone</h4>
                                    <p>+977-9851234567</p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <h4>Address</h4>
                                    <p>New Baneshwor, Kathmandu<br>Nepal</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Changes to Policy -->
                    <div class="privacy-section">
                        <h2>Changes to This Privacy Policy</h2>
                        <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date.</p>
                        <p>We encourage you to review this Privacy Policy periodically for any changes. Changes to this Privacy Policy are effective when they are posted on this page.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
.privacy-hero {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: var(--white);
    padding: 4rem 0;
    text-align: center;
}

.privacy-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.privacy-hero p {
    font-size: 1.1rem;
    margin-bottom: 1rem;
    opacity: 0.9;
}

.last-updated {
    background: rgba(255,255,255,0.2);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    display: inline-block;
}

.privacy-content {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 3rem;
    margin-top: 3rem;
}

.privacy-nav {
    position: sticky;
    top: 2rem;
    height: fit-content;
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.privacy-nav h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-color);
}

.privacy-nav ul {
    list-style: none;
    padding: 0;
}

.privacy-nav li {
    margin-bottom: 0.5rem;
}

.privacy-nav a {
    color: var(--dark-text);
    text-decoration: none;
    transition: var(--transition);
    font-size: 0.9rem;
    display: block;
    padding: 0.5rem 0;
}

.privacy-nav a:hover {
    color: var(--primary-color);
    padding-left: 0.5rem;
}

.privacy-main {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.privacy-section {
    margin-bottom: 3rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
}

.privacy-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.privacy-section h2 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.privacy-section h3 {
    color: var(--dark-text);
    margin: 1.5rem 0 1rem;
}

.privacy-section h4 {
    color: var(--dark-text);
    margin: 1rem 0 0.5rem;
}

.privacy-section p {
    line-height: 1.6;
    margin-bottom: 1rem;
}

.privacy-section ul {
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

.privacy-section li {
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

.use-categories {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.use-category {
    background: var(--light-bg);
    padding: 1.5rem;
    border-radius: var(--border-radius);
}

.use-category h4 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    margin-top: 0;
}

.sharing-item {
    background: var(--light-bg);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    margin-bottom: 1.5rem;
}

.sharing-item h4 {
    color: var(--primary-color);
    margin-top: 0;
}

.security-measures {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.security-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--light-bg);
    border-radius: var(--border-radius);
}

.security-item i {
    font-size: 2rem;
    color: var(--primary-color);
}

.security-item h4 {
    margin: 0 0 0.5rem 0;
    color: var(--dark-text);
}

.security-item p {
    margin: 0;
    color: var(--light-text);
}

.cookie-types {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin: 2rem 0;
}

.cookie-type {
    background: var(--light-bg);
    padding: 1.5rem;
    border-radius: var(--border-radius);
}

.cookie-type h4 {
    color: var(--primary-color);
    margin-top: 0;
}

.rights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.right-item {
    text-align: center;
    padding: 1.5rem;
    background: var(--light-bg);
    border-radius: var(--border-radius);
}

.right-item h4 {
    color: var(--primary-color);
    margin: 0 0 0.5rem 0;
}

.right-item p {
    margin: 0;
    color: var(--light-text);
    font-size: 0.9rem;
}

.contact-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--light-bg);
    border-radius: var(--border-radius);
}

.contact-item i {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.contact-item h4 {
    margin: 0 0 0.5rem 0;
    color: var(--dark-text);
}

.contact-item p {
    margin: 0;
    color: var(--light-text);
}

@media (max-width: 768px) {
    .privacy-hero h1 {
        font-size: 2rem;
    }
    
    .privacy-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .privacy-nav {
        position: static;
    }
    
    .use-categories,
    .security-measures,
    .cookie-types {
        grid-template-columns: 1fr;
    }
    
    .rights-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .contact-info {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .rights-grid {
        grid-template-columns: 1fr;
    }
    
    .privacy-main {
        padding: 1.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for navigation links
    document.querySelectorAll('.privacy-nav a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Highlight current section in navigation
    const sections = document.querySelectorAll('.privacy-section[id]');
    const navLinks = document.querySelectorAll('.privacy-nav a');
    
    function highlightNavigation() {
        let current = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            if (window.pageYOffset >= sectionTop) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    }
    
    window.addEventListener('scroll', highlightNavigation);
});
</script>

<style>
.privacy-nav a.active {
    color: var(--primary-color);
    font-weight: 600;
    padding-left: 0.5rem;
}
</style>

<?php
// Include footer
include_footer();
?>

<?php
// Set page variables
$page_title = 'About Us - DOKO Fresh Market';
$current_page = 'about';
$additional_css = ['css/about.css'];

// Include header template
include_once '../template/header.php';
?>

    <!-- About Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="about-hero-content">
                <h1 class="about-title">About DOKO</h1>
                <p class="about-subtitle">Nepal's Freshest Grocery Marketplace</p>
                <p class="about-description">
                    DOKO is Nepal's premier online grocery platform, dedicated to bringing the freshest produce, 
                    dairy products, and daily essentials directly to your doorstep in Kathmandu and surrounding areas.
                </p>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="our-story">
        <div class="container">
            <div class="story-content">
                <div class="story-text">
                    <h2>Our Story</h2>
                    <p>
                        Founded in 2024, DOKO was born from a simple idea: making fresh, quality groceries accessible 
                        to every household in Nepal. We recognized the challenges people face in finding time to shop 
                        for fresh produce while maintaining their busy lifestyles.
                    </p>
                    <p>
                        Starting with partnerships with local farmers and trusted suppliers, we've built a network 
                        that ensures the freshest products reach our customers' tables. Our commitment to quality, 
                        freshness, and convenience has made us the preferred choice for thousands of families.
                    </p>
                </div>
                <div class="story-image">
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=600&h=400&fit=crop" alt="Fresh Groceries">
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values Section -->
    <section class="our-values">
        <div class="container">
            <h2 class="section-title">Our Values</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>Freshness First</h3>
                    <p>We source directly from farms and ensure products reach you at peak freshness.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Quality Assurance</h3>
                    <p>Every product undergoes strict quality checks before reaching your doorstep.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Timely Delivery</h3>
                    <p>Fast and reliable delivery service across Kathmandu valley.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Customer Care</h3>
                    <p>Dedicated support team to ensure your shopping experience is perfect.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="our-team">
        <div class="container">
            <h2 class="section-title">Meet Our Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-image">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=300&fit=crop&crop=face" alt="Team Member">
                    </div>
                    <h4>Rajesh Sharma</h4>
                    <p class="member-role">Founder & CEO</p>
                    <p>Passionate about bringing fresh groceries to every Nepali household.</p>
                </div>
                <div class="team-member">
                    <div class="member-image">
                        <img src="https://images.unsplash.com/photo-1494790108755-2616b612b372?w=300&h=300&fit=crop&crop=face" alt="Team Member">
                    </div>
                    <h4>Sita Patel</h4>
                    <p class="member-role">Operations Manager</p>
                    <p>Ensures smooth operations and quality control across all processes.</p>
                </div>
                <div class="team-member">
                    <div class="member-image">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop&crop=face" alt="Team Member">
                    </div>
                    <h4>Ram Thapa</h4>
                    <p class="member-role">Technology Lead</p>
                    <p>Develops and maintains our platform for seamless user experience.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-content">
                <div class="contact-info">
                    <h2>Get in Touch</h2>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Phone</h4>
                            <p>+977 9812345678</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email</h4>
                            <p>support@doko.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Address</h4>
                            <p>Kathmandu, Nepal</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form">
                    <h3>Send us a Message</h3>
                    <form>
                        <div class="form-group">
                            <input type="text" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <textarea placeholder="Your Message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

<?php
// Include footer template
include_once '../template/footer.php';
?>

<?php
// Start session and include configuration
session_start();
require_once '../template/config.php';

// Page-specific variables
$page_title = page_title('About Us');
$page_description = 'Learn about DOKO\'s mission to deliver fresh, quality groceries across Nepal. Discover our story, values, and commitment to excellence.';
$current_page = 'about';

// Include header
include_header($page_title, $page_description, $current_page);
?>

<!-- Hero Section -->
<section class="about-hero">
    <div class="container">
        <div class="hero-content">
            <h1>About DOKO</h1>
            <p>Nepal's premier online grocery store committed to delivering fresh, quality products to your doorstep with convenience and care.</p>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="main-content">
    <!-- Our Story Section -->
    <section class="section">
        <div class="container">
            <div class="story-content">
                <div class="story-text">
                    <h2>Our Story</h2>
                    <p>Founded in 2019, DOKO emerged from a vision to transform grocery shopping in Nepal. Starting as a small family business in Kathmandu, we understood the challenges faced by busy families in accessing fresh, quality groceries.</p>
                    
                    <p>Our founders, inspired by successful e-commerce models like Daraz and Kirana, combined with deep understanding of Nepali consumer needs, created DOKO to bridge the gap between traditional markets and modern convenience.</p>
                    
                    <p>Today, we proudly serve customers across Kathmandu Valley and major cities, working directly with local farmers and suppliers to ensure authentic, fresh products reach your table while supporting Nepal's agricultural economy.</p>
                    
                    <div class="story-stats">
                        <div class="stat-item">
                            <h3>25,000+</h3>
                            <p>Satisfied Customers</p>
                        </div>
                        <div class="stat-item">
                            <h3>1,200+</h3>
                            <p>Quality Products</p>
                        </div>
                        <div class="stat-item">
                            <h3>150+</h3>
                            <p>Local Suppliers</p>
                        </div>
                        <div class="stat-item">
                            <h3>15+</h3>
                            <p>Cities Served</p>
                        </div>
                    </div>
                </div>
                
                <div class="story-image">
                    <img src="public/images/about/our-story.jpg" alt="DOKO Story" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="mission-vision">
                <div class="mission-card card">
                    <div class="card-body">
                        <div class="icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3>Our Mission</h3>
                        <p>To make quality groceries accessible to every Nepali household through innovative e-commerce solutions. We bridge traditional markets with modern convenience, supporting local agriculture while delivering freshness and value to our customers' doorsteps.</p>
                    </div>
                </div>
                
                <div class="vision-card card">
                    <div class="card-body">
                        <div class="icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3>Our Vision</h3>
                        <p>To become Nepal's most trusted digital grocery platform, creating a seamless ecosystem that connects consumers with quality products while empowering local farmers and suppliers. We envision a future where every Nepali family enjoys convenient access to fresh, authentic groceries.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Our Values</h2>
                <p class="section-subtitle">The principles that guide everything we do</p>
            </div>
            
            <div class="values-grid">
                <?php
                $values = [
                    [
                        'icon' => 'fas fa-leaf',
                        'title' => 'Freshness',
                        'description' => 'We source directly from farms and maintain cold chain delivery to ensure maximum freshness and nutritional value in every product.'
                    ],
                    [
                        'icon' => 'fas fa-shield-alt',
                        'title' => 'Quality',
                        'description' => 'Every product undergoes strict quality checks. We guarantee 100% satisfaction with our quality assurance and easy return policy.'
                    ],
                    [
                        'icon' => 'fas fa-handshake',
                        'title' => 'Trust',
                        'description' => 'Building lasting relationships with customers through transparency, reliability, and consistent service excellence is our priority.'
                    ],
                    [
                        'icon' => 'fas fa-users',
                        'title' => 'Community',
                        'description' => 'Supporting local farmers, creating employment opportunities, and contributing to community development across Nepal.'
                    ],
                    [
                        'icon' => 'fas fa-rocket',
                        'title' => 'Innovation',
                        'description' => 'Continuously improving our platform, delivery systems, and customer experience through technology and feedback.'
                    ],
                    [
                        'icon' => 'fas fa-heart',
                        'title' => 'Care',
                        'description' => 'Treating every customer like family and ensuring their health and satisfaction through personalized service and attention.'
                    ]
                ];

                foreach ($values as $value): ?>
                <div class="value-item">
                    <div class="value-icon">
                        <i class="<?php echo $value['icon']; ?>"></i>
                    </div>
                    <h3><?php echo clean_output($value['title']); ?></h3>
                    <p><?php echo clean_output($value['description']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Meet Our Development Team</h2>
                <p class="section-subtitle">The talented students behind DOKO's development - CSY2088 Project Team</p>
            </div>
            
            <div class="team-grid">
                <?php
                $team_members = [
                    [
                        'id' => '24814107',
                        'name' => 'Utsab Thami Magar',
                        'role' => 'Developer',
                        'bio' => 'Full-stack developer specializing in PHP and JavaScript, contributing to DOKO\'s e-commerce platform development.',
                        'image' => 'uploads/default-product.jpg',
                        'email' => '',
                        'social' => [
                            'linkedin' => '#',
                            'github' => '#'
                        ]
                    ],
                    [
                        'id' => '24812606',
                        'name' => 'Anuskar Sigdel',
                        'role' => 'Developer',
                        'bio' => 'Backend developer focused on database optimization and API development for seamless user experiences.',
                        'image' => 'uploads/default-product.jpg',
                        'email' => '',
                        'social' => [
                            'linkedin' => '#',
                            'github' => '#'
                        ]
                    ],
                    [
                        'id' => '24812931',
                        'name' => 'Ayush Karanjit',
                        'role' => 'Lead Developer',
                        'bio' => 'Project lead and full-stack developer driving the technical vision and implementation of DOKO\'s grocery platform.',
                        'image' => 'uploads/default-product.jpg',
                        'email' => 'aayush.2024105@nami.edu.np',
                        'social' => [
                            'linkedin' => '#',
                            'github' => '#'
                        ]
                    ],
                    [
                        'id' => '24812945',
                        'name' => 'Sandhaya Kumari',
                        'role' => 'Developer',
                        'bio' => 'Frontend developer creating intuitive user interfaces and enhancing customer experience across the platform.',
                        'image' => 'uploads/default-product.jpg',
                        'email' => '',
                        'social' => [
                            'linkedin' => '#',
                            'github' => '#'
                        ]
                    ],
                    [
                        'id' => '24812923',
                        'name' => 'Jesina Bastola',
                        'role' => 'Developer',
                        'bio' => 'Quality assurance specialist and developer ensuring robust functionality and smooth user experiences.',
                        'image' => 'uploads/default-product.jpg',
                        'email' => '',
                        'social' => [
                            'linkedin' => '#',
                            'github' => '#'
                        ]
                    ]
                ];

                foreach ($team_members as $member): ?>
                <div class="team-member">
                    <div class="member-image">
                        <img src="<?php echo $member['image']; ?>" alt="<?php echo clean_output($member['name'] . ' - ' . $member['role']); ?>" loading="lazy" onerror="handleImageError(this)">
                    </div>
                    <div class="member-info">
                        <h3><?php echo clean_output($member['name']); ?></h3>
                        <p class="member-role"><?php echo clean_output($member['role']); ?></p>
                        <p class="member-id">Student ID: <?php echo clean_output($member['id']); ?></p>
                        <?php if (!empty($member['email'])): ?>
                            <p class="member-email"><i class="fas fa-envelope"></i> <?php echo clean_output($member['email']); ?></p>
                        <?php endif; ?>
                        <p class="member-bio"><?php echo clean_output($member['bio']); ?></p>
                        <div class="member-social">
                            <?php foreach ($member['social'] as $platform => $url): ?>
                                <a href="<?php echo $url; ?>" aria-label="<?php echo ucfirst($platform); ?>">
                                    <i class="fab fa-<?php echo $platform; ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Why Choose DOKO?</h2>
                <p class="section-subtitle">What makes us Nepal's preferred grocery delivery service</p>
            </div>
            
            <div class="benefits-grid">
                <?php
                $benefits = [
                    [
                        'icon' => 'fas fa-truck',
                        'title' => 'Fast Delivery',
                        'description' => 'Same-day delivery available. Order before 2 PM and get your groceries delivered within hours.'
                    ],
                    [
                        'icon' => 'fas fa-money-bill-wave',
                        'title' => 'Best Prices',
                        'description' => 'Competitive pricing with regular discounts and offers. Save more on bulk purchases and seasonal deals.'
                    ],
                    [
                        'icon' => 'fas fa-mobile-alt',
                        'title' => 'Easy Ordering',
                        'description' => 'User-friendly website and mobile app make shopping convenient anytime, anywhere.'
                    ],
                    [
                        'icon' => 'fas fa-undo-alt',
                        'title' => 'Easy Returns',
                        'description' => 'Not satisfied? We offer hassle-free returns and refunds with our customer satisfaction guarantee.'
                    ],
                    [
                        'icon' => 'fas fa-headset',
                        'title' => '24/7 Support',
                        'description' => 'Round-the-clock customer service ready to help with any questions or concerns.'
                    ],
                    [
                        'icon' => 'fas fa-certificate',
                        'title' => 'Certified Quality',
                        'description' => 'All products meet strict quality standards and safety certifications for your peace of mind.'
                    ]
                ];

                foreach ($benefits as $benefit): ?>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="<?php echo $benefit['icon']; ?>"></i>
                    </div>
                    <div>
                        <h3><?php echo clean_output($benefit['title']); ?></h3>
                        <p><?php echo clean_output($benefit['description']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Sustainability Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="sustainability-content">
                <div class="sustainability-text">
                    <h2>Our Commitment to Sustainability</h2>
                    <p>At DOKO, we believe in responsible business practices that protect our environment and support sustainable development in Nepal.</p>
                    
                    <div class="sustainability-features">
                        <?php
                        $sustainability_features = [
                            [
                                'icon' => 'fas fa-seedling',
                                'title' => 'Supporting Local Farmers',
                                'description' => 'Direct partnerships with local farmers ensure fair prices and promote sustainable farming practices.'
                            ],
                            [
                                'icon' => 'fas fa-recycle',
                                'title' => 'Eco-Friendly Packaging',
                                'description' => 'Biodegradable and recyclable packaging materials to minimize environmental impact.'
                            ],
                            [
                                'icon' => 'fas fa-route',
                                'title' => 'Optimized Delivery',
                                'description' => 'Smart route planning and electric vehicles to reduce carbon footprint and delivery emissions.'
                            ],
                            [
                                'icon' => 'fas fa-heart',
                                'title' => 'Community Impact',
                                'description' => 'Contributing to local communities through employment, education, and social development programs.'
                            ]
                        ];

                        foreach ($sustainability_features as $feature): ?>
                        <div class="feature">
                            <i class="<?php echo $feature['icon']; ?>"></i>
                            <div>
                                <h4><?php echo clean_output($feature['title']); ?></h4>
                                <p><?php echo clean_output($feature['description']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="sustainability-image">
                    <img src="public/images/about/sustainability.jpg" alt="Sustainability" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="section cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Experience Fresh Grocery Delivery?</h2>
                <p>Join thousands of satisfied customers who trust DOKO for their daily grocery needs. Start shopping today!</p>
                <div class="cta-actions">
                    <a href="products.php" class="btn btn-accent btn-lg">Start Shopping</a>
                    <a href="contact.php" class="btn btn-outline btn-lg">Contact Us</a>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Page-specific CSS -->
<style>
    .about-hero {
        background: linear-gradient(135deg, rgba(44, 85, 48, 0.9), rgba(62, 123, 62, 0.9)),
                    url('public/images/about/hero-bg.jpg') center/cover no-repeat;
        padding: 4rem 0;
        color: white;
        text-align: center;
    }

    .about-hero h1 {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: white;
    }

    .about-hero p {
        font-size: 1.2rem;
        max-width: 600px;
        margin: 0 auto;
        color: rgba(255,255,255,0.9);
    }

    .story-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        align-items: center;
    }

    .story-text h2 {
        margin-bottom: 1.5rem;
        color: var(--primary-color);
    }

    .story-text p {
        margin-bottom: 1.5rem;
        line-height: 1.8;
        color: var(--dark-text);
    }

    .story-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        margin-top: 3rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-item h3 {
        font-size: 2.5rem;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .stat-item p {
        color: var(--light-text);
        font-weight: 500;
    }

    .story-image img {
        width: 100%;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }

    .mission-vision {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
    }

    .mission-card,
    .vision-card {
        text-align: center;
    }

    .mission-card .icon,
    .vision-card .icon {
        width: 80px;
        height: 80px;
        background: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
    }

    .mission-card .icon i,
    .vision-card .icon i {
        font-size: 2rem;
        color: white;
    }

    .mission-card h3,
    .vision-card h3 {
        margin-bottom: 1rem;
        color: var(--primary-color);
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 3rem;
    }

    .value-item {
        text-align: center;
        padding: 2rem;
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .value-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .value-icon {
        width: 60px;
        height: 60px;
        background: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }

    .value-icon i {
        font-size: 1.5rem;
        color: white;
    }

    .value-item h3 {
        margin-bottom: 1rem;
        color: var(--primary-color);
    }

    .value-item p {
        color: var(--dark-text);
        line-height: 1.6;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 3rem;
        margin-top: 3rem;
    }

    .team-member {
        background: var(--white);
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .team-member:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .member-image {
        height: 250px;
        overflow: hidden;
    }

    .member-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .team-member:hover .member-image img {
        transform: scale(1.05);
    }

    .member-info {
        padding: 2rem;
        text-align: center;
    }

    .member-info h3 {
        margin-bottom: 0.5rem;
        color: var(--primary-color);
    }

    .member-role {
        color: var(--accent-color);
        font-weight: 500;
        margin-bottom: 1rem;
    }

    .member-bio {
        color: var(--dark-text);
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .member-social {
        display: flex;
        justify-content: center;
        gap: 1rem;
    }

    .member-social a {
        width: 40px;
        height: 40px;
        background: var(--light-bg);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        transition: var(--transition);
    }

    .member-social a:hover {
        background: var(--primary-color);
        color: white;
    }

    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 3rem;
    }

    .benefit-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 2rem;
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }

    .benefit-icon {
        width: 50px;
        height: 50px;
        background: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .benefit-icon i {
        font-size: 1.2rem;
        color: white;
    }

    .benefit-item h3 {
        margin-bottom: 0.5rem;
        color: var(--primary-color);
    }

    .benefit-item p {
        color: var(--dark-text);
        line-height: 1.6;
    }

    .sustainability-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        align-items: center;
    }

    .sustainability-text h2 {
        margin-bottom: 1.5rem;
        color: var(--primary-color);
    }

    .sustainability-text > p {
        margin-bottom: 2rem;
        font-size: 1.1rem;
        color: var(--dark-text);
    }

    .sustainability-features {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .sustainability-features .feature {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .sustainability-features .feature i {
        font-size: 1.5rem;
        color: var(--primary-color);
        margin-top: 0.25rem;
    }

    .sustainability-features .feature h4 {
        margin-bottom: 0.5rem;
        color: var(--primary-color);
    }

    .sustainability-features .feature p {
        color: var(--dark-text);
        line-height: 1.6;
    }

    .sustainability-image img {
        width: 100%;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }

    .cta-section {
        background: var(--primary-color);
        color: white;
        text-align: center;
    }

    .cta-content h2 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: white;
    }

    .cta-content p {
        font-size: 1.1rem;
        margin-bottom: 2rem;
        color: rgba(255,255,255,0.9);
    }

    .cta-actions {
        display: flex;
        justify-content: center;
        gap: 1rem;
    }

    .cta-actions .btn-outline {
        border-color: white;
        color: white;
    }

    .cta-actions .btn-outline:hover {
        background: white;
        color: var(--primary-color);
    }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .about-hero h1 {
            font-size: 2rem;
        }

        .story-content,
        .mission-vision,
        .sustainability-content {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .story-stats {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .stat-item h3 {
            font-size: 2rem;
        }

        .values-grid,
        .benefits-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .team-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .cta-actions {
            flex-direction: column;
            align-items: center;
        }
    }

    @media (max-width: 480px) {
        .about-hero {
            padding: 2rem 0;
        }

        .about-hero h1 {
            font-size: 1.8rem;
        }

        .story-stats {
            grid-template-columns: 1fr;
        }

        .benefit-item {
            flex-direction: column;
            text-align: center;
        }

        .sustainability-features .feature {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<?php
// Include footer
include_footer();
?>

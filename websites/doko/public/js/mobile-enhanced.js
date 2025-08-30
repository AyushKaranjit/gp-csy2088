/**
 * DOKO E-Commerce Website - Enhanced Mobile Navigation
 * Comprehensive mobile navigation and interaction handler
 *
 * Author: Team Graduation
 * Version: 2.0 - Enhanced Mobile
 * Date: 2025
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Enhanced Mobile Navigation
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const navList = document.getElementById('nav-list');
    const body = document.body;
    
    if (mobileMenuToggle && navList) {
        // Toggle mobile menu
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isActive = navList.classList.contains('active');
            
            if (isActive) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
        
        // Open mobile menu
        function openMobileMenu() {
            navList.classList.add('active');
            mobileMenuToggle.classList.add('active');
            body.style.overflow = 'hidden'; // Prevent background scrolling
            
            // Change icon to close (X)
            const icon = mobileMenuToggle.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-times';
            }
            
            // Add overlay
            if (!document.querySelector('.mobile-menu-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'mobile-menu-overlay';
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.5);
                    z-index: 999;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                `;
                body.appendChild(overlay);
                
                // Fade in overlay
                setTimeout(() => {
                    overlay.style.opacity = '1';
                }, 10);
                
                // Close menu when clicking overlay
                overlay.addEventListener('click', closeMobileMenu);
            }
        }
        
        // Close mobile menu
        function closeMobileMenu() {
            navList.classList.remove('active');
            mobileMenuToggle.classList.remove('active');
            body.style.overflow = ''; // Restore scrolling
            
            // Change icon back to hamburger
            const icon = mobileMenuToggle.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-bars';
            }
            
            // Remove overlay
            const overlay = document.querySelector('.mobile-menu-overlay');
            if (overlay) {
                overlay.style.opacity = '0';
                setTimeout(() => {
                    if (overlay.parentNode) {
                        overlay.parentNode.removeChild(overlay);
                    }
                }, 300);
            }
        }
        
        // Close menu when clicking nav links
        const navLinks = navList.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                closeMobileMenu();
            });
        });
        
        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && navList.classList.contains('active')) {
                closeMobileMenu();
            }
        });
        
        // Close menu on window resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && navList.classList.contains('active')) {
                closeMobileMenu();
            }
        });
    }
    
    // Enhanced Touch Interactions
    if ('ontouchstart' in window) {
        
        // Add touch feedback to buttons
        const touchElements = document.querySelectorAll('.btn, .product-card, .category-card, .nav-link');
        
        touchElements.forEach(element => {
            element.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
                this.style.transition = 'transform 0.1s ease';
            });
            
            element.addEventListener('touchend', function() {
                this.style.transform = '';
                this.style.transition = '';
            });
            
            element.addEventListener('touchcancel', function() {
                this.style.transform = '';
                this.style.transition = '';
            });
        });
        
        // Swipe gestures for image galleries
        const imageContainers = document.querySelectorAll('.product-images, .hero-with-slider');
        
        imageContainers.forEach(container => {
            let startX = 0;
            let startY = 0;
            let isSwipe = false;
            
            container.addEventListener('touchstart', function(e) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                isSwipe = false;
            });
            
            container.addEventListener('touchmove', function(e) {
                if (!startX || !startY) return;
                
                const diffX = e.touches[0].clientX - startX;
                const diffY = e.touches[0].clientY - startY;
                
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                    isSwipe = true;
                    e.preventDefault(); // Prevent scrolling when swiping
                }
            });
            
            container.addEventListener('touchend', function(e) {
                if (!isSwipe) return;
                
                const diffX = e.changedTouches[0].clientX - startX;
                
                // Trigger swipe events
                if (diffX > 50) {
                    // Swipe right
                    const event = new CustomEvent('swiperight');
                    container.dispatchEvent(event);
                } else if (diffX < -50) {
                    // Swipe left
                    const event = new CustomEvent('swipeleft');
                    container.dispatchEvent(event);
                }
                
                startX = 0;
                startY = 0;
                isSwipe = false;
            });
        });
    }
    
    // Enhanced Form Handling for Mobile
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            // Prevent zoom on iOS when focusing inputs
            if (input.type !== 'file') {
                const currentFontSize = window.getComputedStyle(input).fontSize;
                if (parseFloat(currentFontSize) < 16) {
                    input.style.fontSize = '16px';
                }
            }
            
            // Enhanced validation feedback
            input.addEventListener('blur', function() {
                const isValid = this.checkValidity();
                const parent = this.closest('.form-group');
                
                if (parent) {
                    parent.classList.remove('has-error', 'has-success');
                    
                    if (this.value) {
                        parent.classList.add(isValid ? 'has-success' : 'has-error');
                    }
                }
            });
            
            // Clear validation on focus
            input.addEventListener('focus', function() {
                const parent = this.closest('.form-group');
                if (parent) {
                    parent.classList.remove('has-error');
                }
            });
        });
    });
    
    // Enhanced Cart Quantity Controls for Mobile
    const quantityControls = document.querySelectorAll('.quantity-controls');
    quantityControls.forEach(control => {
        const minusBtn = control.querySelector('.quantity-minus');
        const plusBtn = control.querySelector('.quantity-plus');
        const input = control.querySelector('.quantity-input');
        
        if (minusBtn && plusBtn && input) {
            // Add touch feedback
            [minusBtn, plusBtn].forEach(btn => {
                btn.addEventListener('touchstart', function() {
                    this.style.background = 'var(--primary-color)';
                    this.style.color = 'white';
                });
                
                btn.addEventListener('touchend', function() {
                    this.style.background = '';
                    this.style.color = '';
                });
            });
            
            // Rapid increment/decrement on hold
            let holdTimeout;
            let holdInterval;
            
            function startHold(btn, increment) {
                holdTimeout = setTimeout(() => {
                    holdInterval = setInterval(() => {
                        btn.click();
                    }, 100);
                }, 500);
            }
            
            function stopHold() {
                clearTimeout(holdTimeout);
                clearInterval(holdInterval);
            }
            
            plusBtn.addEventListener('touchstart', () => startHold(plusBtn, true));
            minusBtn.addEventListener('touchstart', () => startHold(minusBtn, false));
            
            [plusBtn, minusBtn].forEach(btn => {
                btn.addEventListener('touchend', stopHold);
                btn.addEventListener('touchcancel', stopHold);
            });
        }
    });
    
    // Enhanced Search for Mobile
    const searchInput = document.querySelector('.search-input');
    const searchForm = document.querySelector('.search-form');
    
    if (searchInput && searchForm) {
        // Auto-complete/suggestions (simplified)
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            if (this.value.length >= 2) {
                searchTimeout = setTimeout(() => {
                    // Here you could implement search suggestions
                    console.log('Search suggestions for:', this.value);
                }, 300);
            }
        });
        
        // Submit on enter for mobile
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchForm.submit();
            }
        });
    }
    
    // Enhanced Modal/Dropdown Handling for Mobile
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close other dropdowns
                dropdowns.forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.classList.remove('active');
                    }
                });
                
                dropdown.classList.toggle('active');
            });
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    });
    
    // Enhanced Image Loading for Mobile
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.dataset.src || img.src;
                    
                    if (src && src !== img.src) {
                        img.src = src;
                        img.classList.add('loaded');
                    }
                    
                    imageObserver.unobserve(img);
                }
            });
        });
        
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => imageObserver.observe(img));
    }
    
    // Enhanced Scroll Behavior for Mobile
    let lastScrollTop = 0;
    const header = document.querySelector('.header');
    
    if (header && window.innerWidth <= 768) {
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling down - hide header
                header.style.transform = 'translateY(-100%)';
            } else {
                // Scrolling up - show header
                header.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        });
    }
    
    // Enhanced Smooth Scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerHeight = header ? header.offsetHeight : 0;
                const targetPosition = target.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Performance optimizations for mobile
    if (window.innerWidth <= 768) {
        // Reduce animation on low-end devices
        const isLowEndDevice = navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 2;
        
        if (isLowEndDevice) {
            document.documentElement.style.setProperty('--transition', '0.1s ease');
        }
        
        // Optimize scroll events
        let ticking = false;
        
        function updateScrollElements() {
            // Your scroll-based animations here
            ticking = false;
        }
        
        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(updateScrollElements);
                ticking = true;
            }
        });
    }
    
    // Console log for debugging
    console.log('DOKO Enhanced Mobile Navigation initialized');
});

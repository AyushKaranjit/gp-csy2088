// Admin Dashboard JavaScript

// Navigation functionality
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link');
    const contentSections = document.querySelectorAll('.content-section');
    const pageTitle = document.getElementById('page-title');
    const pageSubtitle = document.getElementById('page-subtitle');
    
    // Section titles and subtitles
    const sectionData = {
        'overview': {
            title: 'Dashboard Overview',
            subtitle: 'Welcome to DOKO Admin Panel'
        },
        'products': {
            title: 'Products Management',
            subtitle: 'Manage your store inventory'
        },
        'add-product': {
            title: 'Add New Product',
            subtitle: 'Add a new product to your store'
        },
        'categories': {
            title: 'Categories Management',
            subtitle: 'Organize your product categories'
        },
        'orders': {
            title: 'Orders Management',
            subtitle: 'Track and manage customer orders'
        },
        'pending-orders': {
            title: 'Pending Orders',
            subtitle: 'Orders waiting for processing'
        },
        'customers': {
            title: 'Customer Management',
            subtitle: 'Manage customer accounts'
        },
        'admins': {
            title: 'Admin Users',
            subtitle: 'Manage admin access and permissions'
        },
        'settings': {
            title: 'Site Settings',
            subtitle: 'Configure your store settings'
        },
        'reports': {
            title: 'Reports & Analytics',
            subtitle: 'View store performance data'
        }
    };
    
    // Handle navigation clicks
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const section = this.dataset.section;
            
            // Update active nav link
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Update content sections
            contentSections.forEach(s => s.classList.remove('active'));
            const targetSection = document.getElementById(section + '-section');
            if (targetSection) {
                targetSection.classList.add('active');
            }
            
            // Update page title and subtitle
            if (sectionData[section]) {
                pageTitle.textContent = sectionData[section].title;
                pageSubtitle.textContent = sectionData[section].subtitle;
            }
        });
    });
    
    // Filter buttons functionality
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Add filtering logic here
            console.log('Filtering by:', this.textContent);
        });
    });
    
    // Table action buttons
    const editBtns = document.querySelectorAll('.btn-edit');
    const deleteBtns = document.querySelectorAll('.btn-delete');
    const viewBtns = document.querySelectorAll('.btn-view');
    
    editBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            showMessage('Edit functionality coming soon!', 'info');
        });
    });
    
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (confirm('Are you sure you want to delete this item?')) {
                showMessage('Delete functionality coming soon!', 'info');
            }
        });
    });
    
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            showMessage('View functionality coming soon!', 'info');
        });
    });
    
    // Add new product button
    const addProductBtn = document.querySelector('.btn-primary');
    if (addProductBtn) {
        addProductBtn.addEventListener('click', function() {
            showMessage('Add product form coming soon!', 'info');
        });
    }
    
    // Search functionality
    const searchInput = document.querySelector('.admin-search input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            // Add search logic here
            console.log('Searching for:', searchTerm);
        });
    }
    
    // Mobile sidebar toggle
    const sidebarToggle = document.createElement('button');
    sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
    sidebarToggle.className = 'sidebar-toggle';
    sidebarToggle.style.cssText = `
        display: none;
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.75rem;
        border-radius: 6px;
        cursor: pointer;
        position: fixed;
        top: 200px;
        left: 1rem;
        z-index: 200;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    `;
    
    document.body.appendChild(sidebarToggle);
    
    sidebarToggle.addEventListener('click', function() {
        const sidebar = document.querySelector('.admin-sidebar');
        sidebar.classList.toggle('open');
    });
    
    // Show/hide mobile toggle based on screen size
    function updateMobileToggle() {
        if (window.innerWidth <= 768) {
            sidebarToggle.style.display = 'block';
        } else {
            sidebarToggle.style.display = 'none';
        }
    }
    
    window.addEventListener('resize', updateMobileToggle);
    updateMobileToggle();
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            const sidebar = document.querySelector('.admin-sidebar');
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });
});

// Sample data management
const sampleData = {
    products: [
        {
            id: 1,
            name: 'Fresh Red Apples',
            category: 'Fruits',
            price: 4.99,
            stock: 50,
            status: 'active',
            image: 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=40&h=40&fit=crop'
        },
        {
            id: 2,
            name: 'Organic Bananas',
            category: 'Fruits',
            price: 3.49,
            stock: 75,
            status: 'active',
            image: 'https://images.unsplash.com/photo-1550258987-190a2d41a8ba?w=40&h=40&fit=crop'
        }
    ],
    orders: [
        {
            id: '#12345',
            customer: 'John Doe',
            date: '2024-01-15',
            amount: 45.99,
            status: 'pending'
        },
        {
            id: '#12346',
            customer: 'Jane Smith',
            date: '2024-01-16',
            amount: 67.50,
            status: 'processing'
        }
    ]
};

// Utility functions
function showMessage(message, type = 'info') {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.admin-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `admin-message admin-message-${type}`;
    messageEl.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        <span>${message}</span>
        <button class="close-message"><i class="fas fa-times"></i></button>
    `;
    
    // Add styles
    messageEl.style.cssText = `
        position: fixed;
        top: 200px;
        right: 2rem;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
        border-radius: 8px;
        padding: 1rem 1.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 500;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    `;
    
    // Add animation keyframes
    if (!document.querySelector('#admin-animations')) {
        const style = document.createElement('style');
        style.id = 'admin-animations';
        style.textContent = `
            @keyframes slideIn {
                from { opacity: 0; transform: translateX(100%); }
                to { opacity: 1; transform: translateX(0); }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(messageEl);
    
    // Close button functionality
    const closeBtn = messageEl.querySelector('.close-message');
    closeBtn.style.cssText = `
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
        padding: 0;
        margin-left: auto;
        opacity: 0.7;
    `;
    
    closeBtn.addEventListener('click', () => {
        messageEl.remove();
    });
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (messageEl.parentNode) {
            messageEl.remove();
        }
    }, 5000);
}

// Real-time stats updates (simulated)
function updateStats() {
    const statValues = document.querySelectorAll('.stat-info h3');
    
    // Simulate random small changes in stats
    statValues.forEach((stat, index) => {
        const currentValue = parseInt(stat.textContent.replace(/[^0-9]/g, ''));
        const change = Math.floor(Math.random() * 3) - 1; // -1, 0, or 1
        const newValue = Math.max(0, currentValue + change);
        
        if (index === 3) { // Revenue stat
            stat.textContent = '$' + newValue.toLocaleString();
        } else {
            stat.textContent = newValue.toString();
        }
    });
}

// Update stats every 30 seconds
setInterval(updateStats, 30000);

// Export for potential use in other scripts
window.AdminDashboard = {
    showMessage,
    sampleData,
    updateStats
};

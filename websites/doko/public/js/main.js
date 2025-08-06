/**
 * DOKO E-commerce - Enhanced Dashboard JavaScript
 * Comprehensive functionality for all user roles
 * CSY2088 Project
 */

// ========== COMPREHENSIVE DASHBOARD FUNCTIONALITY ==========

// Initialize Dashboard on Page Load
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    initializeModals();
    initializeTables();
    initializeNotifications();
    initializeCart();
    initializeForms();
});

// Dashboard Navigation System
function initializeDashboard() {
    const navLinks = document.querySelectorAll('.dashboard-nav .nav-link');
    const sections = document.querySelectorAll('.dashboard-section');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetSection = this.dataset.section;
            if (!targetSection) return;
            
            // Update active nav link
            navLinks.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            
            // Update active section
            sections.forEach(section => section.classList.remove('active'));
            const target = document.getElementById(targetSection);
            if (target) {
                target.classList.add('active');
                
                // Load section data if needed
                loadSectionData(targetSection);
            }
        });
    });
    
    // Auto-activate first section
    if (navLinks.length > 0) {
        navLinks[0].click();
    }
}

// Load Section Data Based on Section Type
function loadSectionData(sectionId) {
    switch(sectionId) {
        case 'admin-orders':
        case 'orders':
            loadOrders();
            break;
        case 'admin-users':
        case 'users':
            loadUsers();
            break;
        case 'admin-products':
        case 'products':
            loadProducts();
            break;
        case 'manager-inventory':
        case 'inventory':
            loadInventoryData();
            break;
        case 'customer-orders':
        case 'orders-section':
            loadCustomerOrders();
            break;
        default:
            console.log('No data loader for section:', sectionId);
    }
}

// Orders Management (Admin)
function loadOrders() {
    showLoading('orders-table');
    
    fetch('api/orders-list.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOrders(data.orders);
                updateOrderStats(data.stats);
            } else {
                showError('Failed to load orders: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error loading orders:', error);
            showError('Failed to load orders');
        })
        .finally(() => {
            hideLoading('orders-table');
        });
}

function displayOrders(orders) {
    const tbody = document.querySelector('#orders-table tbody');
    if (!tbody) return;
    
    if (!orders || orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center empty-state">No orders found</td></tr>';
        return;
    }
    
    tbody.innerHTML = orders.map(order => `
        <tr>
            <td>#${order.id}</td>
            <td>${order.customer_name || 'N/A'}</td>
            <td>₹${parseFloat(order.total_amount).toFixed(2)}</td>
            <td><span class="status-badge ${order.status.toLowerCase()}">${order.status}</span></td>
            <td>${formatDate(order.created_at)}</td>
            <td>
                <div class="btn-group">
                    <button class="btn-icon view" onclick="viewOrder(${order.id})" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon edit" onclick="editOrderStatus(${order.id}, '${order.status}')" title="Edit Status">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function updateOrderStats(stats) {
    if (!stats) return;
    
    updateStatCard('total-orders', stats.total || 0);
    updateStatCard('pending-orders', stats.pending || 0);
    updateStatCard('total-revenue', '₹' + (stats.revenue || 0));
}

function updateStatCard(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value;
    }
}

// Users Management (Admin)
function loadUsers() {
    showLoading('users-table');
    
    fetch('api/users-list.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayUsers(data.users);
            } else {
                showError('Failed to load users: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            showError('Failed to load users');
        })
        .finally(() => {
            hideLoading('users-table');
        });
}

function displayUsers(users) {
    const tbody = document.querySelector('#users-table tbody');
    if (!tbody) return;
    
    if (!users || users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center empty-state">No users found</td></tr>';
        return;
    }
    
    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${user.id}</td>
            <td>${user.username}</td>
            <td>${user.email}</td>
            <td><span class="status-badge ${user.role.toLowerCase()}">${user.role}</span></td>
            <td><span class="status-badge ${user.status.toLowerCase()}">${user.status}</span></td>
            <td>
                <div class="btn-group">
                    <button class="btn-icon edit" onclick="editUser(${user.id})" title="Edit User">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon delete" onclick="deleteUser(${user.id})" title="Delete User">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Products Management
function loadProducts() {
    showLoading('products-table');
    
    fetch('api/products-list.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayProducts(data.products);
            } else {
                showError('Failed to load products: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error loading products:', error);
            showError('Failed to load products');
        })
        .finally(() => {
            hideLoading('products-table');
        });
}

function displayProducts(products) {
    const tbody = document.querySelector('#products-table tbody');
    if (!tbody) return;
    
    if (!products || products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center empty-state">No products found</td></tr>';
        return;
    }
    
    tbody.innerHTML = products.map(product => `
        <tr>
            <td>
                <div class="product-info">
                    <img src="${product.image || 'uploads/placeholder.jpg'}" alt="${product.name}" class="product-image">
                    <div>
                        <strong>${product.name}</strong>
                        <small class="category-tag">${product.category}</small>
                    </div>
                </div>
            </td>
            <td>₹${parseFloat(product.price).toFixed(2)}</td>
            <td>
                <span class="stock-indicator ${product.stock < 10 ? 'low' : (product.stock > 50 ? 'high' : 'medium')}">
                    ${product.stock}
                </span>
            </td>
            <td><span class="status-badge ${product.status.toLowerCase()}">${product.status}</span></td>
            <td>
                <div class="btn-group">
                    <button class="btn-icon view" onclick="viewProduct(${product.id})" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon edit" onclick="editProduct(${product.id})" title="Edit Product">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon delete" onclick="deleteProduct(${product.id})" title="Delete Product">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Inventory Management (Manager)
function loadInventoryData() {
    showLoading('inventory-list');
    
    fetch('api/inventory-list.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayInventory(data.inventory);
                updateInventoryStats(data.stats);
            } else {
                showError('Failed to load inventory: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error loading inventory:', error);
            showError('Failed to load inventory');
        })
        .finally(() => {
            hideLoading('inventory-list');
        });
}

function displayInventory(inventory) {
    const container = document.getElementById('inventory-list');
    if (!container) return;
    
    if (!inventory || inventory.length === 0) {
        container.innerHTML = '<div class="empty-state">No inventory items found</div>';
        return;
    }
    
    container.innerHTML = inventory.map(item => {
        let stockClass = 'inventory-item';
        if (item.stock <= 0) stockClass += ' out-of-stock';
        else if (item.stock < 10) stockClass += ' low-stock';
        
        return `
            <div class="${stockClass}">
                <div class="item-info">
                    <h4>${item.name}</h4>
                    <p>Stock: ${item.stock} | Price: ₹${parseFloat(item.price).toFixed(2)}</p>
                </div>
                <div class="item-actions">
                    <button class="btn-icon edit" onclick="updateStock(${item.id}, ${item.stock})" title="Update Stock">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function updateInventoryStats(stats) {
    if (!stats) return;
    
    updateStatCard('total-products-inv', stats.total || 0);
    updateStatCard('low-stock-count', stats.lowStock || 0);
    updateStatCard('out-of-stock-count', stats.outOfStock || 0);
}

// Customer Orders
function loadCustomerOrders() {
    showLoading('customer-orders-table');
    
    fetch('api/customer-orders.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCustomerOrders(data.orders);
            } else {
                showError('Failed to load orders: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error loading customer orders:', error);
            showError('Failed to load orders');
        })
        .finally(() => {
            hideLoading('customer-orders-table');
        });
}

function displayCustomerOrders(orders) {
    const tbody = document.querySelector('#customer-orders-table tbody');
    if (!tbody) return;
    
    if (!orders || orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center empty-state">No orders found</td></tr>';
        return;
    }
    
    tbody.innerHTML = orders.map(order => `
        <tr>
            <td>#${order.id}</td>
            <td>₹${parseFloat(order.total_amount).toFixed(2)}</td>
            <td><span class="status-badge ${order.status.toLowerCase()}">${order.status}</span></td>
            <td>${formatDate(order.created_at)}</td>
            <td>
                <button class="btn-icon view" onclick="viewOrderDetails(${order.id})" title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Cart Functionality
function initializeCart() {
    updateCartCount();
    
    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productPrice = this.dataset.productPrice;
            
            addToCart(productId, 1, productName); // Fixed parameter order: (id, quantity, name)
        });
    });
}

function addToCart(productId, quantity = 1, productName = 'Product') {
    // Log the parameters for debugging
    console.log('addToCart called with parameters:', {
        productId: productId,
        productIdType: typeof productId,
        quantity: quantity,
        quantityType: typeof quantity,
        productName: productName,
        productNameType: typeof productName
    });
    
    // Ensure quantity is a number
    const parsedQuantity = parseInt(quantity);
    const parsedProductId = parseInt(productId);
    
    console.log('Parsed values:', {
        parsedProductId: parsedProductId,
        parsedQuantity: parsedQuantity,
        isValidProductId: !isNaN(parsedProductId) && parsedProductId > 0,
        isValidQuantity: !isNaN(parsedQuantity) && parsedQuantity > 0
    });
    
    if (isNaN(parsedProductId) || parsedProductId <= 0) {
        console.error('Invalid product ID:', productId);
        showNotification('Invalid product ID', 'error');
        return;
    }
    
    if (isNaN(parsedQuantity) || parsedQuantity <= 0) {
        console.error('Invalid quantity:', quantity);
        showNotification('Invalid quantity', 'error');
        return;
    }
    
    const data = {
        product_id: parsedProductId,
        quantity: parsedQuantity
    };
    
    console.log('Sending to API:', data);
    
    fetch('api/cart-add-working.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            showNotification(`${productName} added to cart!`, 'success');
            updateCartCount();
        } else {
            if (data.message && data.message.includes('log in')) {
                showNotification('Please log in to add items to cart', 'warning');
                setTimeout(() => {
                    window.location.href = '/login.php?redirect=' + encodeURIComponent(window.location.pathname);
                }, 1500);
            } else {
                showNotification(data.message || 'Failed to add to cart', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Cart error:', error);
        showNotification('Network error: Failed to add to cart', 'error');
    });
}

function updateCartCount() {
    fetch('api/cart-get.php', {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    const totalItems = data.items ? data.items.reduce((sum, item) => sum + parseInt(item.quantity), 0) : 0;
                    cartCount.textContent = totalItems;
                }
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
}

// Form Handling
function initializeForms() {
    // Profile update forms
    const profileForms = document.querySelectorAll('.profile-form');
    profileForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitProfileForm(this);
        });
    });
    
    // Password change forms
    const passwordForms = document.querySelectorAll('.password-form');
    passwordForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitPasswordForm(this);
        });
    });
}

function submitProfileForm(form) {
    const formData = new FormData(form);
    
    fetch('api/profile-update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Profile updated successfully!', 'success');
        } else {
            showNotification(data.message || 'Failed to update profile', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to update profile', 'error');
    });
}

function submitPasswordForm(form) {
    const formData = new FormData(form);
    
    if (formData.get('new_password') !== formData.get('confirm_password')) {
        showNotification('Passwords do not match', 'error');
        return;
    }
    
    fetch('api/password-update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Password updated successfully!', 'success');
            form.reset();
        } else {
            showNotification(data.message || 'Failed to update password', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to update password', 'error');
    });
}

// Modal Management
function initializeModals() {
    // Close modals on background click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });
    
    // Close modals on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModals = document.querySelectorAll('.modal.active');
            activeModals.forEach(modal => closeModal(modal.id));
        }
    });
    
    // Close button functionality
    const closeButtons = document.querySelectorAll('.modal .close, .modal .close-btn');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        // Reset form if exists
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        }
    }
}

// Table Enhancements
function initializeTables() {
    // Add sorting functionality
    const sortableHeaders = document.querySelectorAll('.data-table th[data-sort]');
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const column = this.dataset.sort;
            const table = this.closest('table');
            sortTable(table, column);
        });
    });
}

function sortTable(table, column) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const sorted = rows.sort((a, b) => {
        const aVal = a.querySelector(`td[data-${column}]`)?.textContent || '';
        const bVal = b.querySelector(`td[data-${column}]`)?.textContent || '';
        return aVal.localeCompare(bVal, undefined, { numeric: true });
    });
    
    tbody.innerHTML = '';
    sorted.forEach(row => tbody.appendChild(row));
}

// Notification System
function initializeNotifications() {
    // Auto-hide notifications after 5 seconds
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        setTimeout(() => {
            notification.remove();
        }, 5000);
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button class="close-btn" onclick="this.parentElement.remove()">×</button>
    `;
    
    // Add notification styles if not present
    if (!document.querySelector('.notification-styles')) {
        const style = document.createElement('style');
        style.className = 'notification-styles';
        style.innerHTML = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                display: flex;
                align-items: center;
                gap: 1rem;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideIn 0.3s ease-out;
            }
            .notification.info { background: #3b82f6; }
            .notification.success { background: #10b981; }
            .notification.error { background: #ef4444; }
            .notification.warning { background: #f59e0b; }
            .notification .close-btn {
                background: none;
                border: none;
                color: white;
                font-size: 1.2rem;
                cursor: pointer;
                padding: 0;
            }
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideIn 0.3s ease-out reverse';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Utility Functions
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    }
}

function hideLoading(elementId) {
    // Loading will be replaced by actual content
}

function showError(message) {
    showNotification(message, 'error');
}

function showSuccess(message) {
    showNotification(message, 'success');
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Specific Action Functions
function viewOrder(orderId) {
    console.log('View order:', orderId);
    // Open order details modal or redirect
    openModal('order-details-modal');
}

function editOrderStatus(orderId, currentStatus) {
    const newStatus = prompt(`Change order status for Order #${orderId}:`, currentStatus);
    if (newStatus && newStatus !== currentStatus) {
        updateOrderStatus(orderId, newStatus);
    }
}

function updateOrderStatus(orderId, newStatus) {
    fetch('api/order-update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_id: orderId,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Order status updated successfully');
            loadOrders(); // Reload orders
        } else {
            showError(data.message || 'Failed to update order status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to update order status');
    });
}

function editUser(userId) {
    console.log('Edit user:', userId);
    // Implementation for editing user
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch(`api/user-delete.php?id=${userId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('User deleted successfully');
                loadUsers(); // Reload users
            } else {
                showError(data.message || 'Failed to delete user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to delete user');
        });
    }
}

function viewProduct(productId) {
    console.log('View product:', productId);
    // Implementation for viewing product
}

function editProduct(productId) {
    console.log('Edit product:', productId);
    // Implementation for editing product
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        fetch(`api/product-delete.php?id=${productId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Product deleted successfully');
                loadProducts(); // Reload products
            } else {
                showError(data.message || 'Failed to delete product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to delete product');
        });
    }
}

function updateStock(productId, currentStock) {
    const newStock = prompt(`Update stock for product ${productId}:`, currentStock);
    if (newStock !== null && !isNaN(newStock)) {
        fetch('api/stock-update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                stock: parseInt(newStock)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Stock updated successfully');
                loadInventoryData(); // Reload inventory
            } else {
                showError(data.message || 'Failed to update stock');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to update stock');
        });
    }
}

function viewOrderDetails(orderId) {
    console.log('View order details:', orderId);
    // Implementation for viewing customer order details
}

// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navList = document.querySelector('.nav-list');
    
    if (mobileToggle && navList) {
        mobileToggle.addEventListener('click', function() {
            navList.classList.toggle('active');
            this.classList.toggle('active');
        });
    }
});

// Search functionality
function initializeSearch() {
    const searchInput = document.getElementById('search-input');
    const searchButton = document.getElementById('search-button');
    
    if (searchInput && searchButton) {
        searchButton.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
}

function performSearch() {
    const searchInput = document.getElementById('search-input');
    const query = searchInput.value.trim();
    
    if (query.length > 0) {
        window.location.href = `products.php?search=${encodeURIComponent(query)}`;
    }
}

// Missing functions for product pages
function toggleUserDropdown() {
    const dropdown = document.querySelector('.user-dropdown-menu');
    if (dropdown) {
        dropdown.classList.toggle('active');
    }
}

function quickView(productId) {
    // Redirect to product detail page for now
    window.location.href = `product-detail.php?id=${productId}`;
}

// Note: toggleWishlist function moved to end of file to avoid conflicts

function updateWishlistCount() {
    fetch('api/wishlist.php', {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                const wishlistCount = document.getElementById('wishlist-count');
                if (wishlistCount) {
                    wishlistCount.textContent = data.count || 0;
                }
            }
        } catch (e) {
            console.error('Invalid JSON response for wishlist:', text);
            // Set count to 0 if there's an error
            const wishlistCount = document.getElementById('wishlist-count');
            if (wishlistCount) {
                wishlistCount.textContent = '0';
            }
        }
    })
    .catch(error => {
        console.error('Error updating wishlist count:', error);
        // Set count to 0 if there's an error
        const wishlistCount = document.getElementById('wishlist-count');
        if (wishlistCount) {
            wishlistCount.textContent = '0';
        }
    });
}

// Add error handling for JavaScript errors
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    console.error('  at', e.filename + ':' + e.lineno + ':' + e.colno);
});

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    initializeModals();
    initializeTables();
    initializeNotifications();
    initializeCart();
    initializeForms();
    initializeSearch();
    updateWishlistCount();
});

// Initialize search on page load
document.addEventListener('DOMContentLoaded', initializeSearch);

// ========== USER DROPDOWN FUNCTIONALITY ==========

function toggleUserDropdown() {
    const dropdown = document.getElementById('user-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target) && !e.target.closest('.user-menu')) {
                dropdown.classList.remove('show');
            }
        }, { once: true });
    }
}

// ========== NOTIFICATION SYSTEM ==========

function showNotification(message, type = 'info', duration = 5000) {
    // Remove any existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        max-width: 400px;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        font-family: 'Inter', sans-serif;
    `;
    
    // Set colors based on type
    const colors = {
        success: { bg: '#10b981', text: '#ffffff' },
        error: { bg: '#ef4444', text: '#ffffff' },
        warning: { bg: '#f59e0b', text: '#ffffff' },
        info: { bg: '#3b82f6', text: '#ffffff' }
    };
    
    const color = colors[type] || colors.info;
    notification.style.backgroundColor = color.bg;
    notification.style.color = color.text;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto remove after duration
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }
    }, duration);
}

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-triangle',
        warning: 'exclamation-circle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// ========== PROFILE FUNCTIONALITY ==========

// Handle JavaScript errors gracefully
window.addEventListener('error', function(event) {
    console.error('JavaScript Error:', event.error);
});

// ========== MISSING PRODUCT FUNCTIONS ==========

/**
 * Quick view product modal
 */
function quickView(productId) {
    console.log('Quick view for product:', productId);
    
    fetch(`/api/product-detail.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.product) {
                showQuickViewModal(data.product);
            } else {
                showNotification('Failed to load product details', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading product details:', error);
            showNotification('Failed to load product details', 'error');
        });
}

/**
 * Show quick view modal
 */
function showQuickViewModal(product) {
    const modal = document.createElement('div');
    modal.className = 'modal quick-view-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>${product.name}</h3>
                <button class="close-btn" onclick="closeQuickView()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="product-quick-view">
                    <div class="product-image">
                        <img src="${product.image || '/uploads/products/default.svg'}" alt="${product.name}">
                    </div>
                    <div class="product-details">
                        <p class="price">Rs. ${product.price}</p>
                        <p class="description">${product.description || 'No description available'}</p>
                        <div class="product-actions">
                            <button class="btn btn-primary" onclick="addToCartFromQuickView(${product.product_id})">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                            <button class="btn btn-outline" onclick="toggleWishlist(${product.product_id})">
                                <i class="fas fa-heart"></i> Add to Wishlist
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

/**
 * Close quick view modal
 */
function closeQuickView() {
    const modal = document.querySelector('.quick-view-modal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

/**
 * Add to cart from quick view
 */
function addToCartFromQuickView(productId) {
    addToCart(productId);
    closeQuickView();
}

// Note: toggleWishlist function moved to end of file to avoid conflicts

/**
 * Update cart count with better error handling
 */
function updateCartCount() {
    fetch('api/cart-get.php', {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        } else {
            // If not logged in or error, just set to 0
            return { success: true, items: [] };
        }
    })
    .then(data => {
        if (data.success) {
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                const totalItems = data.items ? data.items.reduce((sum, item) => sum + parseInt(item.quantity), 0) : 0;
                cartCount.textContent = totalItems;
            }
        }
    })
    .catch(error => {
        console.error('Error updating cart count:', error);
        // Silently fail for cart count updates
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            cartCount.textContent = '0';
        }
    });
}

// ========== INITIALIZATION ==========

// Global error handler to prevent JavaScript errors from breaking the page
window.onerror = function(msg, url, lineNo, columnNo, error) {
    console.error('Global error:', {
        message: msg,
        source: url,
        line: lineNo,
        column: columnNo,
        error: error
    });
    return false; // Allow default error handling
};

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing DOKO E-commerce...');
    
    try {
        initializeDashboard();
        initializeModals();
        initializeTables();
        initializeNotifications();
        initializeCart();
        initializeForms();
        initializeSearch();
        
        console.log('DOKO E-commerce initialized successfully');
    } catch (error) {
        console.error('Error during initialization:', error);
    }
});

// Export functions for global access
window.quickView = quickView;
window.toggleWishlist = toggleWishlist;
window.addToCart = addToCart;
window.updateCartCount = updateCartCount;
window.toggleUserDropdown = toggleUserDropdown;
window.showNotification = showNotification;

function showChangePasswordModal() {
    const modal = document.getElementById('change-password-modal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeChangePasswordModal() {
    const modal = document.getElementById('change-password-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// ========== UTILITY FUNCTIONS ==========

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-NP', {
        style: 'currency',
        currency: 'NPR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// ========== ERROR HANDLING ==========

window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
});

// ========== PAGE LOAD OPTIMIZATION ==========

// Preload critical resources
document.addEventListener('DOMContentLoaded', function() {
    // Preload cart data if user is logged in
    if (document.querySelector('.user-menu')) {
        updateCartCount();
    }
});


// Quick View Function
function quickView(productId) {
    // Open product in modal or new tab
    window.open(`product-detail.php?id=${productId}`, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
}

// Authentication Functions
async function isLoggedIn() {
    try {
        const response = await fetch('api/auth-status.php', {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const result = await response.json();
        return result.success && result.logged_in;
    } catch (error) {
        console.error('Error checking authentication status:', error);
        return false;
    }
}

// Make isLoggedIn available globally immediately
window.isLoggedIn = isLoggedIn;

function showAuthModal(type = 'login') {
    // Redirect to login page with current page as redirect parameter
    const currentPage = encodeURIComponent(window.location.pathname + window.location.search);
    window.location.href = `login.php?redirect=${currentPage}`;
}

// Make showAuthModal available globally immediately
window.showAuthModal = showAuthModal;

// Toggle Wishlist Function
async function toggleWishlist(productId) {
    // Check if user is logged in
    const loggedIn = await isLoggedIn();
    if (!loggedIn) {
        showNotification('Please login to use wishlist', 'info');
        showAuthModal('login');
        return;
    }
    
    // Show loading state
    const wishlistBtn = document.querySelector(`[onclick="toggleWishlist(${productId})"] i`);
    if (wishlistBtn) {
        wishlistBtn.className = 'fa fa-spinner fa-spin';
    }
    
    fetch(`api/wishlist.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ 
            action: 'toggle',
            product_id: productId 
        })
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Update wishlist button icon
            if (wishlistBtn) {
                wishlistBtn.className = data.in_wishlist ? 'fa fa-heart' : 'fa fa-heart-o';
            }
            
            // Update wishlist count in header
            updateWishlistCount();
        } else {
            showNotification(data.message || 'Failed to update wishlist', 'error');
            // Restore original icon
            if (wishlistBtn) {
                wishlistBtn.className = 'fa fa-heart-o';
            }
        }
    })
    .catch(error => {
        console.error('Wishlist error:', error);
        showNotification('Failed to update wishlist', 'error');
        
        // Restore original icon
        if (wishlistBtn) {
            wishlistBtn.className = 'fa fa-heart-o';
        }
    });
}

// Make toggleWishlist available globally immediately
window.toggleWishlist = toggleWishlist;

// Create default product image
function getDefaultProductImage() {
    return '/uploads/default-product.jpg'; // Use the correct path
}

// Image error handler
function handleImageError(img) {
    console.log('Image load error for:', img.src);
    const defaultImg = getDefaultProductImage();
    if (img.src !== defaultImg) {
        img.src = defaultImg;
        img.onerror = null; // Prevent infinite loop
    }
}

// Global error handler for JavaScript errors
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    // You can send this to your error tracking service
});

// Add to window for global access
window.quickView = quickView;
window.toggleWishlist = toggleWishlist;
window.handleImageError = handleImageError;
window.isLoggedIn = isLoggedIn;
window.showAuthModal = showAuthModal;

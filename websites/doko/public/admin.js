// Admin Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize admin dashboard
    initTabNavigation();
    initModal();
    initProductForm();
    initTableActions();
    initFilters();
    
    // Load initial data
    loadDashboardData();
});

// Tab Navigation
function initTabNavigation() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.dataset.tab;
            
            // Remove active class from all tabs and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            button.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
}

// Modal Management
function initModal() {
    const modal = document.getElementById('addProductModal');
    const addProductBtn = document.getElementById('addProductBtn');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.querySelector('.cancel-btn');
    
    // Open modal
    addProductBtn.addEventListener('click', () => {
        modal.classList.add('active');
    });
    
    // Close modal functions
    function closeModal() {
        modal.classList.remove('active');
        resetProductForm();
    }
    
    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    
    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
}

// Product Form Management
function initProductForm() {
    const productForm = document.querySelector('.product-form');
    
    productForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const formData = new FormData(productForm);
        const productData = {
            name: formData.get('productName'),
            category: formData.get('productCategory'),
            price: parseFloat(formData.get('productPrice')),
            stock: parseInt(formData.get('productStock')),
            sku: formData.get('productSKU'),
            description: formData.get('productDescription'),
            image: formData.get('productImage')
        };
        
        // Validate form data
        if (validateProductData(productData)) {
            addProduct(productData);
            document.getElementById('addProductModal').classList.remove('active');
            resetProductForm();
            showNotification('Product added successfully!', 'success');
        }
    });
}

function validateProductData(data) {
    if (!data.name || !data.category || !data.price || !data.stock || !data.sku) {
        showNotification('Please fill in all required fields', 'error');
        return false;
    }
    
    if (data.price <= 0) {
        showNotification('Price must be greater than 0', 'error');
        return false;
    }
    
    if (data.stock < 0) {
        showNotification('Stock cannot be negative', 'error');
        return false;
    }
    
    return true;
}

function resetProductForm() {
    document.querySelector('.product-form').reset();
}

// Table Actions
function initTableActions() {
    // Product table actions
    const productsTable = document.getElementById('productsTableBody');
    
    if (productsTable) {
        productsTable.addEventListener('click', (e) => {
            const target = e.target.closest('.action-btn');
            if (!target) return;
            
            const row = target.closest('tr');
            const productId = row.cells[0].textContent;
            
            if (target.classList.contains('edit')) {
                editProduct(productId, row);
            } else if (target.classList.contains('delete')) {
                deleteProduct(productId, row);
            }
        });
    }
    
    // Orders table actions
    const ordersTable = document.querySelector('#orders .admin-table tbody');
    if (ordersTable) {
        ordersTable.addEventListener('click', (e) => {
            const target = e.target.closest('.action-btn');
            if (!target) return;
            
            const row = target.closest('tr');
            const orderId = row.cells[0].textContent;
            
            if (target.classList.contains('view')) {
                viewOrder(orderId);
            } else if (target.classList.contains('edit')) {
                editOrder(orderId, row);
            }
        });
    }
    
    // Customers table actions
    const customersTable = document.querySelector('#customers .admin-table tbody');
    if (customersTable) {
        customersTable.addEventListener('click', (e) => {
            const target = e.target.closest('.action-btn');
            if (!target) return;
            
            const row = target.closest('tr');
            const customerId = row.cells[0].textContent;
            
            if (target.classList.contains('view')) {
                viewCustomer(customerId);
            } else if (target.classList.contains('edit')) {
                editCustomer(customerId, row);
            }
        });
    }
}

// Product Management Functions
function addProduct(productData) {
    const tableBody = document.getElementById('productsTableBody');
    const newId = '#' + String(tableBody.children.length + 1).padStart(3, '0');
    
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${newId}</td>
        <td><img src="https://via.placeholder.com/50" alt="Product" class="product-thumb"></td>
        <td>${productData.name}</td>
        <td>${productData.category}</td>
        <td>$${productData.price.toFixed(2)}</td>
        <td>${productData.stock}</td>
        <td><span class="status active">Active</span></td>
        <td class="table-actions">
            <button class="action-btn edit"><i class="fas fa-edit"></i></button>
            <button class="action-btn delete"><i class="fas fa-trash"></i></button>
        </td>
    `;
    
    tableBody.appendChild(row);
    updateStats();
}

function editProduct(productId, row) {
    const productName = row.cells[2].textContent;
    const category = row.cells[3].textContent;
    const price = row.cells[4].textContent.replace('$', '');
    const stock = row.cells[5].textContent;
    
    // Fill form with existing data
    document.getElementById('productName').value = productName;
    document.getElementById('productCategory').value = category.toLowerCase();
    document.getElementById('productPrice').value = price;
    document.getElementById('productStock').value = stock;
    
    // Open modal for editing
    document.getElementById('addProductModal').classList.add('active');
    
    showNotification(`Editing product: ${productName}`, 'info');
}

function deleteProduct(productId, row) {
    const productName = row.cells[2].textContent;
    
    if (confirm(`Are you sure you want to delete "${productName}"?`)) {
        row.remove();
        updateStats();
        showNotification('Product deleted successfully!', 'success');
    }
}

// Order Management Functions
function viewOrder(orderId) {
    showNotification(`Viewing order details for ${orderId}`, 'info');
    // In a real application, this would open a detailed order view
}

function editOrder(orderId, row) {
    const currentStatus = row.querySelector('.status').textContent;
    const statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    
    const newStatus = prompt(`Change order status for ${orderId}:\nCurrent: ${currentStatus}\nOptions: ${statuses.join(', ')}`);
    
    if (newStatus && statuses.includes(newStatus)) {
        const statusElement = row.querySelector('.status');
        statusElement.textContent = newStatus;
        statusElement.className = `status ${newStatus.toLowerCase()}`;
        showNotification(`Order ${orderId} status updated to ${newStatus}`, 'success');
    }
}

// Customer Management Functions
function viewCustomer(customerId) {
    showNotification(`Viewing customer details for ${customerId}`, 'info');
    // In a real application, this would open a detailed customer view
}

function editCustomer(customerId, row) {
    const customerName = row.cells[1].textContent;
    showNotification(`Editing customer: ${customerName}`, 'info');
    // In a real application, this would open a customer edit form
}

// Filters and Search
function initFilters() {
    // Order status filter
    const orderFilter = document.querySelector('#orders .filter-select');
    if (orderFilter) {
        orderFilter.addEventListener('change', (e) => {
            filterOrders(e.target.value);
        });
    }
    
    // Customer search
    const customerSearch = document.querySelector('#customers .search-input');
    if (customerSearch) {
        customerSearch.addEventListener('input', (e) => {
            searchCustomers(e.target.value);
        });
    }
}

function filterOrders(status) {
    const orderRows = document.querySelectorAll('#orders .admin-table tbody tr');
    
    orderRows.forEach(row => {
        const orderStatus = row.querySelector('.status').textContent;
        
        if (status === 'All Orders' || orderStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    showNotification(`Filtered orders by: ${status}`, 'info');
}

function searchCustomers(searchTerm) {
    const customerRows = document.querySelectorAll('#customers .admin-table tbody tr');
    
    customerRows.forEach(row => {
        const customerName = row.cells[1].textContent.toLowerCase();
        const customerEmail = row.cells[2].textContent.toLowerCase();
        const searchTermLower = searchTerm.toLowerCase();
        
        if (customerName.includes(searchTermLower) || customerEmail.includes(searchTermLower)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Dashboard Data Management
function loadDashboardData() {
    // Simulate loading dashboard data
    updateStats();
    loadRecentOrders();
    loadTopProducts();
}

function updateStats() {
    // In a real application, this would fetch data from an API
    const totalProducts = document.querySelectorAll('#productsTableBody tr').length;
    const totalOrders = document.querySelectorAll('#orders .admin-table tbody tr').length;
    const totalCustomers = document.querySelectorAll('#customers .admin-table tbody tr').length;
    
    // Update stats display (you could make this dynamic based on actual data)
    console.log(`Updated stats: ${totalProducts} products, ${totalOrders} orders, ${totalCustomers} customers`);
}

function loadRecentOrders() {
    // In a real application, this would fetch recent orders from an API
    console.log('Loading recent orders...');
}

function loadTopProducts() {
    // In a real application, this would fetch top-selling products from an API
    console.log('Loading top products...');
}

// Notification System
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.admin-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `admin-notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">&times;</button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${getNotificationColor(type)};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 1001;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        animation: slideInRight 0.3s ease;
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
    
    // Close button functionality
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.remove();
    });
}

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function getNotificationColor(type) {
    const colors = {
        success: '#16a34a',
        error: '#dc2626',
        warning: '#d97706',
        info: '#3b82f6'
    };
    return colors[type] || '#3b82f6';
}

// Analytics Functions
function generateReport() {
    const startDate = document.querySelector('.date-input:first-of-type').value;
    const endDate = document.querySelector('.date-input:last-of-type').value;
    
    if (!startDate || !endDate) {
        showNotification('Please select both start and end dates', 'error');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        showNotification('Start date cannot be after end date', 'error');
        return;
    }
    
    showNotification(`Generating report from ${startDate} to ${endDate}`, 'info');
    
    // In a real application, this would generate and download a report
    setTimeout(() => {
        showNotification('Report generated successfully!', 'success');
    }, 2000);
}

// Initialize report generation
document.addEventListener('DOMContentLoaded', function() {
    const generateReportBtn = document.querySelector('.date-range .admin-btn');
    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', generateReport);
    }
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        margin-left: 15px;
    }
    
    .notification-close:hover {
        opacity: 0.7;
    }
`;
document.head.appendChild(style);

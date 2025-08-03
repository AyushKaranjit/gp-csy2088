// DOKO - Track Order Functionality

// Sample order data (in a real application, this would come from a database)
const sampleOrders = {
    'DOKO-2024-001234': {
        orderNumber: 'DOKO-2024-001234',
        phoneNumber: '9812345678',
        orderDate: '2024-08-03',
        status: 'out-for-delivery',
        estimatedDelivery: 'Today, 3:00 PM - 5:00 PM',
        deliveryAddress: 'Kathmandu, Baneshwor, Ward 32, Street 15',
        deliveryPartner: 'DOKO Express',
        contactNumber: '+977 9856743210',
        items: [
            {
                name: 'Fresh Red Apples',
                quantity: 2,
                unit: 'kg',
                price: 300,
                image: 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=50&h=50&fit=crop'
            },
            {
                name: 'Fresh Bananas',
                quantity: 1,
                unit: 'dozen',
                price: 140,
                image: 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=50&h=50&fit=crop'
            },
            {
                name: 'Dairy Milk',
                quantity: 2,
                unit: 'liter',
                price: 160,
                image: 'https://images.unsplash.com/photo-1481070555726-e2fe8357725c?w=50&h=50&fit=crop'
            }
        ],
        subtotal: 600,
        deliveryFee: 50,
        total: 650
    },
    'DOKO-2024-001235': {
        orderNumber: 'DOKO-2024-001235',
        phoneNumber: '9823456789',
        orderDate: '2024-08-04',
        status: 'delivered',
        estimatedDelivery: 'Delivered on Aug 4, 2024 at 2:30 PM',
        deliveryAddress: 'Lalitpur, Patan, Ward 12, Mangal Bazaar',
        deliveryPartner: 'DOKO Express',
        contactNumber: '+977 9856743210',
        items: [
            {
                name: 'Mixed Vegetables Pack',
                quantity: 1,
                unit: 'pack',
                price: 450,
                image: 'https://images.unsplash.com/photo-1518843875459-f738682238a6?w=50&h=50&fit=crop'
            },
            {
                name: 'Basmati Rice',
                quantity: 1,
                unit: '5kg',
                price: 900,
                image: 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=50&h=50&fit=crop'
            }
        ],
        subtotal: 1350,
        deliveryFee: 0,
        total: 1350
    }
};

// DOM elements
const trackOrderForm = document.getElementById('trackOrderForm');
const orderStatusSection = document.getElementById('orderStatusSection');
const noOrderSection = document.getElementById('noOrderSection');

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    if (trackOrderForm) {
        trackOrderForm.addEventListener('submit', handleTrackOrder);
    }
});

// Handle track order form submission
function handleTrackOrder(e) {
    e.preventDefault();
    
    const orderNumber = document.getElementById('orderNumber').value.trim().toUpperCase();
    const phoneNumber = document.getElementById('phoneNumber').value.trim();
    
    // Find order in sample data
    const order = sampleOrders[orderNumber];
    
    if (order && order.phoneNumber === phoneNumber) {
        displayOrderStatus(order);
    } else {
        displayNoOrderFound();
    }
}

// Display order status
function displayOrderStatus(order) {
    // Hide search and no-order sections
    document.querySelector('.order-search-section').style.display = 'none';
    noOrderSection.style.display = 'none';
    
    // Show order status section
    orderStatusSection.style.display = 'block';
    
    // Update order header
    document.getElementById('displayOrderNumber').textContent = order.orderNumber;
    document.getElementById('orderDate').textContent = formatDate(order.orderDate);
    
    // Update status badge
    const statusBadge = document.getElementById('currentStatus');
    statusBadge.textContent = formatStatus(order.status);
    statusBadge.className = `status-badge ${order.status}`;
    
    // Update progress steps
    updateProgressSteps(order.status);
    
    // Update delivery details
    document.getElementById('estimatedDelivery').textContent = order.estimatedDelivery;
    document.getElementById('deliveryAddress').textContent = order.deliveryAddress;
    document.getElementById('deliveryPartner').textContent = order.deliveryPartner;
    document.getElementById('contactNumber').textContent = order.contactNumber;
    
    // Update order items
    displayOrderItems(order.items);
    
    // Update order summary
    document.getElementById('subtotal').textContent = `रू ${order.subtotal}`;
    document.getElementById('deliveryFee').textContent = order.deliveryFee === 0 ? 'Free' : `रू ${order.deliveryFee}`;
    document.getElementById('totalAmount').textContent = `रू ${order.total}`;
    
    // Scroll to order status
    orderStatusSection.scrollIntoView({ behavior: 'smooth' });
}

// Display no order found message
function displayNoOrderFound() {
    document.querySelector('.order-search-section').style.display = 'none';
    orderStatusSection.style.display = 'none';
    noOrderSection.style.display = 'block';
    
    // Scroll to no order section
    noOrderSection.scrollIntoView({ behavior: 'smooth' });
}

// Update progress steps based on order status
function updateProgressSteps(status) {
    const steps = ['confirmed', 'preparing', 'outForDelivery', 'delivered'];
    const currentStepIndex = getStatusIndex(status);
    
    steps.forEach((step, index) => {
        const stepElement = document.getElementById(`step-${step}`);
        stepElement.classList.remove('completed', 'active');
        
        if (index < currentStepIndex) {
            stepElement.classList.add('completed');
        } else if (index === currentStepIndex) {
            stepElement.classList.add('active');
        }
    });
}

// Get status index for progress tracking
function getStatusIndex(status) {
    const statusMap = {
        'confirmed': 0,
        'preparing': 1,
        'out-for-delivery': 2,
        'delivered': 3
    };
    return statusMap[status] || 0;
}

// Display order items
function displayOrderItems(items) {
    const itemsList = document.getElementById('orderItemsList');
    itemsList.innerHTML = '';
    
    items.forEach(item => {
        const itemElement = document.createElement('div');
        itemElement.className = 'order-item';
        itemElement.innerHTML = `
            <div class="item-info">
                <div class="item-image">
                    <img src="${item.image}" alt="${item.name}">
                </div>
                <div class="item-details">
                    <h5>${item.name}</h5>
                    <p>Quantity: ${item.quantity} ${item.unit}</p>
                </div>
            </div>
            <div class="item-price">रू ${item.price}</div>
        `;
        itemsList.appendChild(itemElement);
    });
}

// Format date for display
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Format status for display
function formatStatus(status) {
    const statusMap = {
        'confirmed': 'Order Confirmed',
        'preparing': 'Preparing Order',
        'out-for-delivery': 'Out for Delivery',
        'delivered': 'Delivered'
    };
    return statusMap[status] || status;
}

// Reset search function
function resetSearch() {
    // Show search section
    document.querySelector('.order-search-section').style.display = 'block';
    
    // Hide other sections
    orderStatusSection.style.display = 'none';
    noOrderSection.style.display = 'none';
    
    // Clear form
    document.getElementById('trackOrderForm').reset();
    
    // Scroll to top
    document.querySelector('.track-order-header').scrollIntoView({ behavior: 'smooth' });
}

// Add some sample order numbers for testing
console.log('Sample Order Numbers for Testing:');
console.log('DOKO-2024-001234 (Phone: 9812345678) - Out for Delivery');
console.log('DOKO-2024-001235 (Phone: 9823456789) - Delivered');

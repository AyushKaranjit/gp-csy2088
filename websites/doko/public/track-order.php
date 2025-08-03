<?php
// Set page variables
$page_title = 'Track Your Order - DOKO Fresh Market';
$current_page = 'track-order';
$additional_css = ['css/track-order.css'];
$additional_js = ['track-order.js'];

// Include header template
include_once '../template/header.php';
?>

    <div class="track-order-container">
        <div class="container">
            <div class="track-order-header">
                <h1>Track Your Order</h1>
                <p>Enter your order details to track your delivery status</p>
            </div>
            
            <div class="track-order-content">
                <!-- Order Search Section -->
                <div class="order-search-section">
                    <div class="search-card">
                        <h2>Find Your Order</h2>
                        <form id="trackOrderForm" class="track-form">
                            <div class="form-group">
                                <label for="orderNumber">Order Number</label>
                                <input type="text" id="orderNumber" name="orderNumber" placeholder="e.g., DOKO-2024-001234" required>
                            </div>
                            <div class="form-group">
                                <label for="phoneNumber">Phone Number</label>
                                <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="9812345678" required>
                            </div>
                            <button type="submit" class="track-btn">
                                <i class="fas fa-search"></i>
                                Track Order
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Order Status Section (Initially Hidden) -->
                <div id="orderStatusSection" class="order-status-section" style="display: none;">
                    <div class="status-card">
                        <div class="order-header">
                            <div class="order-number">
                                <h3>Order #<span id="displayOrderNumber"></span></h3>
                                <span class="order-date">Placed on <span id="orderDate"></span></span>
                            </div>
                            <div class="order-status">
                                <span id="currentStatus" class="status-badge"></span>
                            </div>
                        </div>
                        
                        <!-- Progress Tracking -->
                        <div class="progress-tracking">
                            <div class="progress-step completed" id="step-confirmed">
                                <div class="step-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="step-content">
                                    <h4>Order Confirmed</h4>
                                    <p>Your order has been received and confirmed</p>
                                </div>
                            </div>
                            
                            <div class="progress-step" id="step-preparing">
                                <div class="step-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="step-content">
                                    <h4>Preparing Order</h4>
                                    <p>We're carefully preparing your fresh groceries</p>
                                </div>
                            </div>
                            
                            <div class="progress-step" id="step-outForDelivery">
                                <div class="step-icon">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <div class="step-content">
                                    <h4>Out for Delivery</h4>
                                    <p>Your order is on the way to your location</p>
                                </div>
                            </div>
                            
                            <div class="progress-step" id="step-delivered">
                                <div class="step-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="step-content">
                                    <h4>Delivered</h4>
                                    <p>Order successfully delivered to your address</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Delivery Details -->
                        <div class="delivery-details">
                            <div class="delivery-info">
                                <h4>Delivery Information</h4>
                                <div class="info-row">
                                    <span class="label">Estimated Delivery:</span>
                                    <span id="estimatedDelivery" class="value"></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Delivery Address:</span>
                                    <span id="deliveryAddress" class="value"></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Delivery Partner:</span>
                                    <span id="deliveryPartner" class="value"></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Contact Number:</span>
                                    <span id="contactNumber" class="value"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Items -->
                        <div class="order-items">
                            <h4>Order Items</h4>
                            <div id="orderItemsList" class="items-list">
                                <!-- Items will be populated by JavaScript -->
                            </div>
                        </div>
                        
                        <!-- Order Summary -->
                        <div class="order-summary">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="subtotal"></span>
                            </div>
                            <div class="summary-row">
                                <span>Delivery Fee:</span>
                                <span id="deliveryFee"></span>
                            </div>
                            <div class="summary-row total">
                                <span>Total:</span>
                                <span id="totalAmount"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- No Order Found Section -->
                <div id="noOrderSection" class="no-order-section" style="display: none;">
                    <div class="no-order-card">
                        <i class="fas fa-search"></i>
                        <h3>Order Not Found</h3>
                        <p>We couldn't find an order with the provided details. Please check your order number and phone number.</p>
                        <button onclick="resetSearch()" class="search-again-btn">Search Again</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
// Include footer template
include_once '../template/footer.php';
?>

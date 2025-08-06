<?php
/**
 * Email System Demo
 * Example usage of the DOKO Email Notification System
 */

require_once __DIR__ . '/src/Services/EmailNotificationSystem.php';

// Initialize email system
$emailConfig = [
    'smtp_username' => 'your-email@gmail.com',  // Set your SMTP username
    'smtp_password' => 'your-app-password',     // Set your app password
    'from_email' => 'noreply@doko.com',
    'from_name' => 'DOKO E-commerce'
];

$emailSystem = new EmailNotificationSystem($emailConfig);

// Example 1: Send Welcome Email
$userData = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'username' => 'johndoe',
    'email' => 'john.doe@example.com'
];

echo "<h2>Email System Demo</h2>";
echo "<h3>1. Welcome Email</h3>";
if ($emailSystem->sendWelcomeEmail($userData)) {
    echo "<p style='color: green;'>✅ Welcome email sent successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to send welcome email (mail function may not be configured)</p>";
}

// Example 2: Send Order Confirmation
$orderData = [
    'order_id' => '12345',
    'created_at' => date('Y-m-d H:i:s'),
    'customer_name' => 'John Doe',
    'status' => 'confirmed',
    'total_amount' => '1,250.00',
    'payment_method' => 'Cash on Delivery',
    'payment_status' => 'Pending',
    'shipping_address' => 'Kathmandu, Nepal',
    'items' => [
        [
            'product_name' => 'Fresh Apples',
            'quantity' => 2,
            'price' => '150.00',
            'total' => '300.00'
        ],
        [
            'product_name' => 'Orange Juice',
            'quantity' => 3,
            'price' => '200.00',
            'total' => '600.00'
        ]
    ]
];

echo "<h3>2. Order Confirmation Email</h3>";
if ($emailSystem->sendOrderConfirmation($orderData, 'john.doe@example.com')) {
    echo "<p style='color: green;'>✅ Order confirmation email sent successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to send order confirmation (mail function may not be configured)</p>";
}

// Example 3: Send Order Status Update
echo "<h3>3. Order Status Update Email</h3>";
if ($emailSystem->sendOrderStatusUpdate($orderData, 'john.doe@example.com', 'shipped')) {
    echo "<p style='color: green;'>✅ Order status update email sent successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to send status update (mail function may not be configured)</p>";
}

// Example 4: Send Low Stock Alert
$productData = [
    'product_id' => 101,
    'name' => 'Fresh Apples',
    'sku' => 'APP-001',
    'stock_quantity' => 5,
    'low_stock_threshold' => 10,
    'category_name' => 'Fruits'
];

$adminEmails = ['admin@doko.com', 'manager@doko.com'];

echo "<h3>4. Low Stock Alert Email</h3>";
$results = $emailSystem->sendLowStockAlert($productData, $adminEmails);
$successCount = count(array_filter($results));
echo "<p style='color: " . ($successCount > 0 ? 'green' : 'red') . ";'>";
echo $successCount > 0 ? "✅" : "❌";
echo " Low stock alert sent to {$successCount}/" . count($adminEmails) . " administrators</p>";

// Example 5: Send Password Reset
echo "<h3>5. Password Reset Email</h3>";
$resetToken = bin2hex(random_bytes(32));
if ($emailSystem->sendPasswordReset('john.doe@example.com', $resetToken)) {
    echo "<p style='color: green;'>✅ Password reset email sent successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to send password reset email (mail function may not be configured)</p>";
}

echo "<hr>";
echo "<h3>Email System Features:</h3>";
echo "<ul>";
echo "<li>✅ Professional HTML email templates</li>";
echo "<li>✅ Order confirmation and status updates</li>";
echo "<li>✅ Welcome emails for new users</li>";
echo "<li>✅ Low stock alerts for administrators</li>";
echo "<li>✅ Password reset functionality</li>";
echo "<li>✅ Fallback to PHP mail() function</li>";
echo "<li>✅ Support for SMTP configuration (PHPMailer ready)</li>";
echo "<li>✅ Proper error handling and logging</li>";
echo "</ul>";

echo "<h3>Configuration Notes:</h3>";
echo "<p><strong>For production use:</strong></p>";
echo "<ul>";
echo "<li>Install PHPMailer via Composer: <code>composer require phpmailer/phpmailer</code></li>";
echo "<li>Configure SMTP settings in the email system initialization</li>";
echo "<li>Set up proper email server or use services like Gmail, SendGrid, etc.</li>";
echo "<li>Enable PHP mail() function on your server if using fallback</li>";
echo "</ul>";

echo "<p><em>Note: In development/testing environment, emails may not actually send but the system will log the attempts.</em></p>";
?>

<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    h2 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
    h3 { color: #4CAF50; margin-top: 30px; }
    code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
    ul { line-height: 1.6; }
    hr { margin: 30px 0; border: none; border-top: 1px solid #ddd; }
</style>

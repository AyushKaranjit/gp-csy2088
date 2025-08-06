<?php
/**
 * Email Notification System
 * Handles all email communications for the DOKO e-commerce system
 */

class EmailNotificationSystem {
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_username;
    private $smtp_password;
    private $from_email;
    private $from_name = 'DOKO E-commerce';
    
    public function __construct($config = []) {
        $this->smtp_username = $config['smtp_username'] ?? '';
        $this->smtp_password = $config['smtp_password'] ?? '';
        $this->from_email = $config['from_email'] ?? 'noreply@doko.com';
        $this->from_name = $config['from_name'] ?? 'DOKO E-commerce';
    }
    
    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation($order_data, $customer_email) {
        $subject = "Order Confirmation - Order #{$order_data['order_id']}";
        
        $body = $this->getOrderConfirmationTemplate($order_data);
        
        return $this->sendEmail($customer_email, $subject, $body);
    }
    
    /**
     * Send order status update email
     */
    public function sendOrderStatusUpdate($order_data, $customer_email, $new_status) {
        $subject = "Order Update - Order #{$order_data['order_id']} - " . ucfirst($new_status);
        
        $body = $this->getOrderStatusUpdateTemplate($order_data, $new_status);
        
        return $this->sendEmail($customer_email, $subject, $body);
    }
    
    /**
     * Send welcome email to new users
     */
    public function sendWelcomeEmail($user_data) {
        $subject = "Welcome to DOKO E-commerce!";
        
        $body = $this->getWelcomeTemplate($user_data);
        
        return $this->sendEmail($user_data['email'], $subject, $body);
    }
    
    /**
     * Send low stock alert to admins
     */
    public function sendLowStockAlert($product_data, $admin_emails) {
        $subject = "Low Stock Alert - {$product_data['name']}";
        
        $body = $this->getLowStockTemplate($product_data);
        
        $results = [];
        foreach ($admin_emails as $email) {
            $results[$email] = $this->sendEmail($email, $subject, $body);
        }
        
        return $results;
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordReset($user_email, $reset_token) {
        $subject = "Password Reset Request - DOKO E-commerce";
        
        $body = $this->getPasswordResetTemplate($reset_token);
        
        return $this->sendEmail($user_email, $subject, $body);
    }
    
    /**
     * Core email sending function
     */
    private function sendEmail($to_email, $subject, $body) {
        try {
            // For now, use built-in mail function (can be enhanced with PHPMailer later)
            return $this->sendWithBuiltInMail($to_email, $subject, $body);
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using built-in mail function
     */
    private function sendWithBuiltInMail($to_email, $subject, $body) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->from_name . ' <' . $this->from_email . '>',
            'Reply-To: ' . $this->from_email,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        return mail($to_email, $subject, $body, implode("\r\n", $headers));
    }
    
    /**
     * Order confirmation email template
     */
    private function getOrderConfirmationTemplate($order_data) {
        $items_html = '';
        foreach ($order_data['items'] as $item) {
            $items_html .= "
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$item['product_name']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$item['quantity']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>रू {$item['price']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>रू {$item['total']}</td>
                </tr>
            ";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4CAF50; color: white; padding: 30px; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; }
                .order-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th { background: #4CAF50; color: white; padding: 12px; text-align: left; }
                td { padding: 10px; border-bottom: 1px solid #eee; }
                .total { font-weight: bold; font-size: 1.2em; }
                .footer { text-align: center; padding: 20px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Order Confirmation</h1>
                    <p>Thank you for your order!</p>
                </div>
                
                <div class='content'>
                    <h2>Order Details</h2>
                    <div class='order-details'>
                        <p><strong>Order Number:</strong> #{$order_data['order_id']}</p>
                        <p><strong>Order Date:</strong> {$order_data['created_at']}</p>
                        <p><strong>Customer:</strong> {$order_data['customer_name']}</p>
                        <p><strong>Status:</strong> {$order_data['status']}</p>
                    </div>
                    
                    <h3>Items Ordered</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$items_html}
                        </tbody>
                        <tfoot>
                            <tr class='total'>
                                <td colspan='3'>Grand Total:</td>
                                <td style='text-align: right;'>रू {$order_data['total_amount']}</td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class='order-details'>
                        <h3>Shipping Information</h3>
                        <p>{$order_data['shipping_address']}</p>
                        
                        <h3>Payment Information</h3>
                        <p><strong>Method:</strong> {$order_data['payment_method']}</p>
                        <p><strong>Status:</strong> {$order_data['payment_status']}</p>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>Thank you for shopping with DOKO E-commerce!</p>
                    <p>If you have any questions, please contact us at support@doko.com</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Order status update template
     */
    private function getOrderStatusUpdateTemplate($order_data, $new_status) {
        $status_messages = [
            'confirmed' => 'Your order has been confirmed and is being prepared.',
            'processing' => 'Your order is currently being processed.',
            'shipped' => 'Great news! Your order has been shipped and is on its way.',
            'delivered' => 'Your order has been delivered successfully.',
            'cancelled' => 'Your order has been cancelled as requested.',
        ];
        
        $message = $status_messages[$new_status] ?? 'Your order status has been updated.';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2196F3; color: white; padding: 30px; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; }
                .status-update { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #2196F3; }
                .footer { text-align: center; padding: 20px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Order Status Update</h1>
                    <p>Order #{$order_data['order_id']}</p>
                </div>
                
                <div class='content'>
                    <div class='status-update'>
                        <h2>Status: " . ucfirst($new_status) . "</h2>
                        <p>{$message}</p>
                        
                        <p><strong>Order Details:</strong></p>
                        <ul>
                            <li>Order Number: #{$order_data['order_id']}</li>
                            <li>Order Date: {$order_data['created_at']}</li>
                            <li>Total Amount: रू {$order_data['total_amount']}</li>
                        </ul>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>Thank you for choosing DOKO E-commerce!</p>
                    <p>Track your order or contact support at support@doko.com</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Welcome email template
     */
    private function getWelcomeTemplate($user_data) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; }
                .welcome-box { background: white; padding: 25px; border-radius: 8px; margin: 20px 0; }
                .cta-button { background: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to DOKO E-commerce!</h1>
                    <p>Your account has been created successfully</p>
                </div>
                
                <div class='content'>
                    <div class='welcome-box'>
                        <h2>Hello {$user_data['first_name']}!</h2>
                        <p>Thank you for joining DOKO E-commerce. We're excited to have you as part of our community!</p>
                        
                        <h3>What you can do now:</h3>
                        <ul>
                            <li>Browse our extensive product catalog</li>
                            <li>Add items to your wishlist</li>
                            <li>Enjoy secure and fast checkout</li>
                            <li>Track your orders in real-time</li>
                            <li>Manage your profile and preferences</li>
                        </ul>
                        
                        <p>Your account details:</p>
                        <ul>
                            <li><strong>Username:</strong> {$user_data['username']}</li>
                            <li><strong>Email:</strong> {$user_data['email']}</li>
                            <li><strong>Registration Date:</strong> " . date('Y-m-d H:i:s') . "</li>
                        </ul>
                        
                        <a href='http://localhost/doko/public/products.php' class='cta-button'>Start Shopping</a>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>If you have any questions, feel free to contact us at support@doko.com</p>
                    <p>Welcome to the DOKO family!</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Low stock alert template
     */
    private function getLowStockTemplate($product_data) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #ff9800; color: white; padding: 30px; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; }
                .alert-box { background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .product-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>⚠️ Low Stock Alert</h1>
                    <p>Inventory Management Notification</p>
                </div>
                
                <div class='content'>
                    <div class='alert-box'>
                        <h2>Stock Level Warning</h2>
                        <p>The following product is running low on stock and may require immediate attention:</p>
                    </div>
                    
                    <div class='product-details'>
                        <h3>{$product_data['name']}</h3>
                        <ul>
                            <li><strong>Product ID:</strong> {$product_data['product_id']}</li>
                            <li><strong>SKU:</strong> {$product_data['sku']}</li>
                            <li><strong>Current Stock:</strong> {$product_data['stock_quantity']} units</li>
                            <li><strong>Low Stock Threshold:</strong> {$product_data['low_stock_threshold']} units</li>
                            <li><strong>Category:</strong> {$product_data['category_name']}</li>
                        </ul>
                        
                        <p><strong>Recommendation:</strong> Consider restocking this item to avoid stockouts.</p>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>This is an automated alert from the DOKO Inventory Management System</p>
                    <p>Login to the admin panel to manage stock levels</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Password reset template
     */
    private function getPasswordResetTemplate($reset_token) {
        $reset_link = "http://localhost/doko/public/reset-password.php?token=" . $reset_token;
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f44336; color: white; padding: 30px; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; }
                .reset-box { background: white; padding: 25px; border-radius: 8px; margin: 20px 0; }
                .reset-button { background: #f44336; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                .security-note { background: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Password Reset Request</h1>
                    <p>DOKO E-commerce Account Security</p>
                </div>
                
                <div class='content'>
                    <div class='reset-box'>
                        <h2>Reset Your Password</h2>
                        <p>We received a request to reset your password. If you made this request, click the button below to reset your password:</p>
                        
                        <a href='{$reset_link}' class='reset-button'>Reset Password</a>
                        
                        <p>Or copy and paste this link into your browser:</p>
                        <p style='word-break: break-all; background: #f5f5f5; padding: 10px; border-radius: 3px;'>{$reset_link}</p>
                        
                        <div class='security-note'>
                            <h4>Security Notice:</h4>
                            <ul>
                                <li>This link will expire in 1 hour</li>
                                <li>If you didn't request this reset, please ignore this email</li>
                                <li>Never share this link with anyone</li>
                                <li>Your password will remain unchanged until you create a new one</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>This is an automated security email from DOKO E-commerce</p>
                    <p>If you need help, contact support@doko.com</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>

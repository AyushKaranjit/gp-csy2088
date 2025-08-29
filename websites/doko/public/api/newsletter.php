<?php
/**
 * Newsletter Subscription API Endpoint
 * Handles newsletter signup from homepage and other sources
 */
require __DIR__ . '/_bootstrap.php';
use Doko\Http\ApiResponse;

require_method('POST');

try {
    $input = json_input();
    if (!$input) { ApiResponse::error('Invalid JSON data', 400); }

    // Enhanced email validation
    if (!isset($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        ApiResponse::error('Valid email address is required', 400);
    }

    $email = trim(strtolower($input['email']));
    
    // Additional email validation
    if (strlen($email) > 100) {
        ApiResponse::error('Email address is too long', 400);
    }
    
    // Check for disposable email domains (basic check)
    $disposableDomains = ['10minutemail.com', 'guerrillamail.com', 'mailinator.com'];
    $domain = substr(strrchr($email, "@"), 1);
    if (in_array($domain, $disposableDomains)) {
        ApiResponse::error('Disposable email addresses are not allowed', 400);
    }

    // Get database connection
    $db = Database::getInstance();

    // Check if email already exists
    $stmt = $db->execute("SELECT subscription_id, status FROM newsletter_subscriptions WHERE email = ?", [$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['status'] === 'active') {
            ApiResponse::error('This email is already subscribed to our newsletter', 409);
        } elseif ($existing['status'] === 'unsubscribed') {
            // Reactivate subscription
            $stmt = $db->execute("
                UPDATE newsletter_subscriptions
                SET status = 'active', subscribed_at = NOW(), unsubscribed_at = NULL
                WHERE subscription_id = ?
            ", [$existing['subscription_id']]);
            ApiResponse::success(['message' => 'Newsletter subscription reactivated successfully']);
        } else {
            ApiResponse::error('This email cannot be subscribed due to previous issues', 400);
        }
    } else {
        // Check if user exists (for registered users)
        $userId = null;
        $stmt = $db->execute("SELECT user_id FROM users WHERE email = ?", [$email]);
        $user = $stmt->fetch();
        if ($user) {
            $userId = $user['user_id'];
        }

        // Insert new subscription
        $stmt = $db->execute("
            INSERT INTO newsletter_subscriptions (email, user_id, status, preferences, subscribed_at)
            VALUES (?, ?, 'active', '{}', NOW())
        ", [$email, $userId]);

        ApiResponse::success([
            'message' => 'Successfully subscribed to newsletter',
            'subscription_id' => $db->lastInsertId()
        ]);
    }

} catch (Throwable $e) {
    error_log('Newsletter subscription error: ' . $e->getMessage());
    ApiResponse::error('Failed to process newsletter subscription', 500, ['exception' => $e->getMessage()]);
}
?>

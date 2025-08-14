<?php
/**
 * Add OAuth columns to users table
 * Migration script for Google and Facebook OAuth integration
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    echo "Adding OAuth columns to users table...\n";
    
    // Check if columns already exist
    $checkGoogle = $db->query("SHOW COLUMNS FROM users LIKE 'google_id'")->rowCount();
    $checkFacebook = $db->query("SHOW COLUMNS FROM users LIKE 'facebook_id'")->rowCount();
    
    if ($checkGoogle == 0) {
        echo "Adding google_id column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE");
        echo "✓ Added google_id column\n";
    } else {
        echo "✓ google_id column already exists\n";
    }
    
    if ($checkFacebook == 0) {
        echo "Adding facebook_id column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN facebook_id VARCHAR(255) NULL UNIQUE");
        echo "✓ Added facebook_id column\n";
    } else {
        echo "✓ facebook_id column already exists\n";
    }
    
    // Add indexes for better performance
    try {
        $db->exec("CREATE INDEX idx_users_google_id ON users(google_id)");
        echo "✓ Added index for google_id\n";
    } catch (Exception $e) {
        echo "⚠ Index for google_id may already exist\n";
    }
    
    try {
        $db->exec("CREATE INDEX idx_users_facebook_id ON users(facebook_id)");
        echo "✓ Added index for facebook_id\n";
    } catch (Exception $e) {
        echo "⚠ Index for facebook_id may already exist\n";
    }
    
    echo "\nOAuth database migration completed successfully!\n";
    echo "You can now use Google and Facebook OAuth login.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

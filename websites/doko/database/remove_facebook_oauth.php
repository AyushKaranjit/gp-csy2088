<?php
/**
 * Remove Facebook OAuth columns from users table
 * Run this script to remove Facebook login functionality
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    echo "Removing Facebook OAuth functionality...\n";
    
    // Check if facebook_id column exists
    $checkColumn = $db->query("SHOW COLUMNS FROM users LIKE 'facebook_id'");
    if ($checkColumn->rowCount() > 0) {
        // Remove facebook_id column
        $db->exec("ALTER TABLE users DROP COLUMN facebook_id");
        echo "✅ Removed facebook_id column from users table\n";
    } else {
        echo "ℹ️  facebook_id column not found in users table\n";
    }
    
    // Update any users who had Facebook as their only login method
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE google_id IS NULL AND password IS NULL");
    $orphanedUsers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($orphanedUsers > 0) {
        echo "⚠️  Warning: Found {$orphanedUsers} users with no login method after Facebook removal\n";
        echo "   These users will need to reset their passwords to login\n";
    }
    
    echo "✅ Facebook OAuth removal completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error removing Facebook OAuth: " . $e->getMessage() . "\n";
    exit(1);
}
?>

<?php
/**
 * Seed Manager User
 * DOKO Grocery E-commerce - Add a default manager user
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    echo "Creating manager user...\n";
    
    // Check if manager user already exists
    $checkQuery = "SELECT user_id FROM users WHERE email = 'manager@doko.com' LIMIT 1";
    $stmt = $db->query($checkQuery);
    
    if ($stmt->rowCount() > 0) {
        echo "Manager user already exists!\n";
        exit;
    }
    
    // Create manager user
    $password = password_hash('manager123', PASSWORD_DEFAULT);
    
    $insertQuery = "INSERT INTO users (
        username, 
        email, 
        password, 
        first_name, 
        last_name, 
        phone, 
        role, 
        status, 
        email_verified,
        created_at
    ) VALUES (
        'manager', 
        'manager@doko.com', 
        :password, 
        'Store', 
        'Manager', 
        '+1234567890', 
        'manager', 
        'active', 
        1,
        NOW()
    )";
    
    $stmt = $db->prepare($insertQuery);
    $stmt->bindParam(':password', $password);
    
    if ($stmt->execute()) {
        echo "Manager user created successfully!\n";
        echo "Email: manager@doko.com\n";
        echo "Password: manager123\n";
        echo "Please change the password after first login.\n";
    } else {
        echo "Failed to create manager user.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

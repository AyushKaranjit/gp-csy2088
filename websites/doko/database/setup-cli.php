<?php
/**
 * Command-line database setup script
 */

require_once __DIR__ . '/../config/database.php';

try {
    // Get database connection (this will create the database if it doesn't exist)
    $db = Database::getInstance();
    echo "✅ Database connection established\n";
    
    // Read and execute the schema file
    $schemaFile = __DIR__ . '/doko_schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: " . $schemaFile);
    }
    
    $sql = file_get_contents($schemaFile);
    
    // Replace database name from doko_grocery_new to doko_ecommerce
    $sql = str_replace('doko_grocery_new', 'doko_ecommerce', $sql);
    
    // Split the SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $db->execute($statement);
            $success++;
            
            // Show which table was created
            if (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE\s+`?(\w+)`?/i', $statement, $matches);
                if ($matches) {
                    echo "✅ Created table: " . $matches[1] . "\n";
                }
            }
        } catch (Exception $e) {
            $errors++;
            echo "❌ Error executing statement: " . $e->getMessage() . "\n";
            echo "Statement: " . substr($statement, 0, 100) . "...\n";
        }
    }
    
    echo "\n🎉 Database setup completed!\n";
    echo "✅ Successful statements: $success\n";
    echo "❌ Errors: $errors\n";
    
    // Verify tables were created
    $result = $db->execute("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    echo "\n📋 Created tables (" . count($tables) . "):\n";
    foreach ($tables as $table) {
        echo "  • $table\n";
    }
    
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>

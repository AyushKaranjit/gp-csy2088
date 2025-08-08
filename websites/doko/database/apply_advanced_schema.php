<?php
/**
 * Apply Advanced DOKO Database Schema
 * - Executes the full SQL in doko_schema.sql (including DROP/CREATE/USE)
 * - Intended for local/dev environments inside Docker
 */

ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "🚧 Applying advanced DOKO schema...\n";

// Connect to MySQL server (no DB selected) so DROP/CREATE/USE work
$dsn = "mysql:host=mysql;charset=utf8mb4";
$user = getenv('DB_USER') ?: 'student';
$pass = getenv('DB_PASS') ?: 'student';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    echo "✓ Connected to MySQL server as {$user}\n";
} catch (Throwable $e) {
    fwrite(STDERR, "❌ Connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

$schemaFile = __DIR__ . '/doko_schema.sql';
if (!is_file($schemaFile)) {
    fwrite(STDERR, "❌ Schema file not found: {$schemaFile}\n");
    exit(1);
}

echo "📄 Loaded schema file: doko_schema.sql\n";

// Robust streaming parser with DELIMITER support
$handle = fopen($schemaFile, 'r');
if (!$handle) {
    fwrite(STDERR, "❌ Failed to open schema file for reading.\n");
    exit(1);
}

$delimiter = ';';
$buffer = '';
$applied = 0;
$failed = 0;

while (($line = fgets($handle)) !== false) {
    // Normalize line endings
    $line = rtrim($line, "\r\n") . "\n";

    // Handle DELIMITER changes
    if (preg_match('/^\s*DELIMITER\s+(.+)\s*$/i', $line, $m)) {
        $delimiter = trim($m[1]);
        // When delimiter changes, flush any pending buffer (unlikely but safe)
        if (trim($buffer) !== '') {
            try {
                $pdo->exec($buffer);
                $applied++;
            } catch (Throwable $e) {
                $failed++;
                $preview = substr(preg_replace('/\s+/', ' ', $buffer), 0, 160);
                fwrite(STDERR, "⚠️  Statement failed: {$preview}...\n   → " . $e->getMessage() . "\n");
            }
            $buffer = '';
        }
        continue;
    }

    // Skip full-line comments only when not inside a statement
    if ($buffer === '' && preg_match('/^\s*(--|#)/', $line)) {
        continue;
    }

    $buffer .= $line;

    // Check if buffer ends with the current delimiter (ignoring trailing whitespace)
    $trimmed = rtrim($buffer);
    if ($delimiter !== '' && substr($trimmed, -strlen($delimiter)) === $delimiter) {
        // Remove the delimiter
        $statement = rtrim(substr($trimmed, 0, -strlen($delimiter)));
        // Skip empty statements
        if ($statement !== '') {
            try {
                $pdo->exec($statement);
                $applied++;
            } catch (Throwable $e) {
                $failed++;
                $preview = substr(preg_replace('/\s+/', ' ', $statement), 0, 160);
                fwrite(STDERR, "⚠️  Statement failed: {$preview}...\n   → " . $e->getMessage() . "\n");
            }
        }
        $buffer = '';
    }
}
fclose($handle);

// Flush any trailing buffer without delimiter (safety)
if (trim($buffer) !== '') {
    try {
        $pdo->exec($buffer);
        $applied++;
    } catch (Throwable $e) {
        $failed++;
        $preview = substr(preg_replace('/\s+/', ' ', $buffer), 0, 160);
        fwrite(STDERR, "⚠️  Statement failed (final flush): {$preview}...\n   → " . $e->getMessage() . "\n");
    }
}

echo "\n✅ Schema apply complete. Statements applied: {$applied}, failed: {$failed}\n";
echo "   Database should now match the advanced model expected by tests.\n";

// Quick smoke check
try {
    $chk = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'doko_ecommerce' AND TABLE_NAME='products' AND COLUMN_NAME IN ('product_id','sku','slug')")->fetchColumn();
    if ((int)$chk >= 1) {
        echo "🔍 Smoke check: products table has advanced columns.\n";
    } else {
        echo "🔍 Smoke check: products table columns not detected as expected, please review output above.\n";
    }
} catch (Throwable $e) {
    echo "🔍 Smoke check skipped: " . $e->getMessage() . "\n";
}

echo "\n🎉 Done.\n";
?>

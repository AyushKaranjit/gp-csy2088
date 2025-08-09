<?php
/**
 * Seed Default Users Script
 * ------------------------------------------------------------
 * Creates (or recreates) one admin user and one customer user
 * with securely hashed passwords.
 *
 * Usage (from project root):
 *   php websites/doko/database/seed_default_users.php
 *
 * Optional flags:
 *   --purge         Delete ALL existing users before inserting seeds
 *   --force-update  Update existing seed user rows (matching emails) instead of skipping
 *
 * Environment overrides (set before running) e.g.:
 *   set ADMIN_EMAIL=admin@example.com
 *   set ADMIN_PASS=Str0ngPass!
 *   set CUSTOMER_EMAIL=customer@example.com
 *   set CUSTOMER_PASS=Customer123!
 *
 * Safety: By default only touches rows with the target seed emails.
 * Use --purge cautiously in non-production environments.
 */

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

require_once __DIR__ . '/../config/database.php';

$args = $argv; array_shift($args);
$flags = ['purge' => false, 'force-update' => false, 'purge-non-seed' => false];
foreach ($args as $a) {
    if ($a === '--purge') { $flags['purge'] = true; }
    if ($a === '--force-update') { $flags['force-update'] = true; }
    if ($a === '--purge-non-seed') { $flags['purge-non-seed'] = true; }
}

$adminEmail    = getenv('ADMIN_EMAIL') ?: 'admin@doko.com';
$adminPassword = getenv('ADMIN_PASS') ?: 'admin123';
$adminUsername = getenv('ADMIN_USERNAME') ?: 'admin';

$customerEmail    = getenv('CUSTOMER_EMAIL') ?: 'customer@doko.com';
$customerPassword = getenv('CUSTOMER_PASS') ?: 'customer123';
$customerUsername = getenv('CUSTOMER_USERNAME') ?: 'customer';

try {
    $db = Database::getInstance()->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->query('SELECT 1 FROM users LIMIT 1');

    $seedEmails = [$adminEmail, $customerEmail];

    if ($flags['purge'] || $flags['purge-non-seed']) {
        if ($flags['purge']) {
            fwrite(STDOUT, "[INFO] Full purge requested. Removing dependent rows then all users...\\n");
        } else {
            fwrite(STDOUT, "[INFO] Purging users NOT in seed list (" . implode(', ', $seedEmails) . ")...\\n");
        }
        try { $db->exec('SET FOREIGN_KEY_CHECKS=0'); } catch (Throwable $e) {}
        // Delete dependent rows referencing users to be removed
        $targetCondition = $flags['purge'] ? '' : 'WHERE user_id NOT IN (SELECT user_id FROM users WHERE email IN ('.implode(',', array_map(fn($e)=>$db->quote($e), $seedEmails)).'))';
        // Order items via orders
        $ordersToClean = [];
        if ($flags['purge']) {
            $orderStmt = $db->query('SELECT order_id FROM orders');
        } else {
            $orderStmt = $db->query('SELECT o.order_id FROM orders o JOIN users u ON o.user_id = u.user_id WHERE u.email NOT IN ('.implode(',', array_map(fn($e)=>$db->quote($e), $seedEmails)).')');
        }
        $ordersToClean = $orderStmt ? $orderStmt->fetchAll(PDO::FETCH_COLUMN) : [];
        if ($ordersToClean) {
            $chunks = array_chunk($ordersToClean, 500);
            foreach ($chunks as $ch) {
                $db->exec('DELETE FROM order_items WHERE order_id IN ('.implode(',', array_map('intval',$ch)).')');
            }
        }
        if ($flags['purge']) {
            $db->exec('DELETE FROM orders');
            $db->exec('DELETE FROM cart');
            $db->exec('DELETE FROM wishlist');
            $db->exec('DELETE FROM users');
        } else {
            $db->exec('DELETE FROM orders WHERE user_id NOT IN (SELECT user_id FROM users WHERE email IN ('.implode(',', array_map(fn($e)=>$db->quote($e), $seedEmails)).'))');
            $db->exec('DELETE FROM cart WHERE user_id NOT IN (SELECT user_id FROM users WHERE email IN ('.implode(',', array_map(fn($e)=>$db->quote($e), $seedEmails)).'))');
            $db->exec('DELETE FROM wishlist WHERE user_id NOT IN (SELECT user_id FROM users WHERE email IN ('.implode(',', array_map(fn($e)=>$db->quote($e), $seedEmails)).'))');
            $db->exec('DELETE FROM users WHERE email NOT IN ('.implode(',', array_map(fn($e)=>$db->quote($e), $seedEmails)).')');
        }
        try { $db->exec('SET FOREIGN_KEY_CHECKS=1'); } catch (Throwable $e) {}
    }

    $seedUsers = [
        [ 'email' => $adminEmail, 'password' => $adminPassword, 'username' => $adminUsername, 'role' => 'admin', 'first_name' => 'Site', 'last_name' => 'Admin' ],
        [ 'email' => $customerEmail, 'password' => $customerPassword, 'username' => $customerUsername, 'role' => 'customer', 'first_name' => 'Default', 'last_name' => 'Customer' ],
    ];

    $inserted = 0; $updated = 0; $skipped = 0;

    foreach ($seedUsers as $u) {
        $stmt = $db->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$u['email']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        $hashed = password_hash($u['password'], PASSWORD_DEFAULT);
        if ($existing) {
            if ($flags['force-update']) {
                $upd = $db->prepare('UPDATE users SET username=?, password=?, role=?, status="active", first_name=?, last_name=? WHERE user_id=?');
                $upd->execute([$u['username'], $hashed, $u['role'], $u['first_name'], $u['last_name'], $existing['user_id']]);
                $updated++; fwrite(STDOUT, "[UPDATED] {$u['email']}\n");
            } else { $skipped++; fwrite(STDOUT, "[SKIP]    {$u['email']} (exists)\n"); }
            continue;
        }
        // Ensure unique username (avoid collision from earlier data)
        $baseUsername = $u['username'];
        $username = $baseUsername;
        $checkUser = $db->prepare('SELECT 1 FROM users WHERE username = ? LIMIT 1');
        $suffix = 1;
        while (true) {
            $checkUser->execute([$username]);
            if (!$checkUser->fetchColumn()) break;
            $username = $baseUsername . $suffix;
            $suffix++;
        }
        $ins = $db->prepare('INSERT INTO users (username, email, password, first_name, last_name, phone, role, status, created_at) VALUES (?,?,?,?,?,?,?,"active",NOW())');
        $ins->execute([$username, $u['email'], $hashed, $u['first_name'], $u['last_name'], null, $u['role']]);
        $inserted++; fwrite(STDOUT, "[CREATED] {$u['email']} ({$u['role']})\n");
    }

    fwrite(STDOUT, "\nSummary:\n  Created: $inserted\n  Updated: $updated\n  Skipped: $skipped\n\nAdmin Login -> $adminEmail / (configured password)\nCustomer Login -> $customerEmail / (configured password)\nDone.\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "[ERROR] ".$e->getMessage()."\n");
    exit(1);
}

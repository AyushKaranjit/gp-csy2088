<?php
/**
 * Admin Users API (Test Compatibility Stub)
 * Returns minimal user list when admin authenticated
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/Controllers/AuthController.php';

$auth = new AuthController();
if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    $db = Database::getInstance();
    $stmt = $db->execute('SELECT user_id, username, email, role, status, created_at FROM users LIMIT 25');
    $users = $stmt->fetchAll();
    echo json_encode(['success' => true, 'users' => $users]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load users']);
}

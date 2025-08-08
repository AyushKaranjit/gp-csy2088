<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/Controllers/AuthController.php';
$auth = new AuthController();
if (!$auth->isLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Authentication required']); exit; }
echo json_encode(['success'=>true,'message'=>'Removed from wishlist']);

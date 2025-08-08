<?php
header('Content-Type: application/json');
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../src/Controllers/AuthController.php';
require_once __DIR__ . '/../../../config/database.php';
$auth = new AuthController();
if (!$auth->isLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Authentication required']); exit; }
if (!$auth->isAdmin()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Access denied']); exit; }
try { $db = Database::getInstance();
  $st = $db->execute('SELECT product_id,name,price,stock_quantity,status FROM products ORDER BY product_id DESC LIMIT 10');
  $products = $st->fetchAll();
  echo json_encode(['success'=>true,'products'=>$products]);
} catch(Exception $e){ http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to load products']); }
?>

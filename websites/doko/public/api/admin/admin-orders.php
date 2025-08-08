<?php
header('Content-Type: application/json');
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../src/Controllers/AuthController.php';
require_once __DIR__ . '/../../../config/database.php';
$auth = new AuthController();
if (!$auth->isLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Authentication required']); exit; }
if (!$auth->isAdmin()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Access denied']); exit; }
try { $db = Database::getInstance();
  $st = $db->execute('SELECT order_id,order_number,user_id,status,total_amount,ordered_at FROM orders ORDER BY order_id DESC LIMIT 10');
  $orders = $st->fetchAll();
  echo json_encode(['success'=>true,'orders'=>$orders]);
} catch(Exception $e){ http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to load orders']); }
?>

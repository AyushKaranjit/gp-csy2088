<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/Controllers/AuthController.php';
try {
  $db = Database::getInstance();
  $auth = new AuthController();
  if(!$auth->isLoggedIn()){ http_response_code(401); echo json_encode(['success'=>false,'message'=>'Authentication required']); exit; }
  $id = isset($_GET['id'])?(int)$_GET['id']:0; if($id<=0){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid order id']); exit; }
  $user = $auth->getCurrentUser();
  $order = $db->execute('SELECT * FROM orders WHERE order_id=?',[$id])->fetch();
  if(!$order){ http_response_code(404); echo json_encode(['success'=>false,'message'=>'Order not found']); exit; }
  if(!$auth->isAdmin() && (int)$order['user_id'] !== (int)$user['user_id']){ http_response_code(403); echo json_encode(['success'=>false,'message'=>'Access denied']); exit; }
  $items = $db->execute('SELECT oi.order_item_id, oi.product_id, oi.quantity, oi.unit_price as price, oi.total_price as total, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id=p.product_id WHERE oi.order_id=?',[$id])->fetchAll();
  echo json_encode(['success'=>true,'order'=>$order,'items'=>$items]);
} catch(Exception $e){ http_response_code(500); echo json_encode(['success'=>false,'message'=>'Server error']); }
?>

<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/Controllers/AuthController.php';
try {
  $db = Database::getInstance();
  $auth = new AuthController();
  if(!$auth->isLoggedIn() || !$auth->isAdmin()){ http_response_code(403); echo json_encode(['success'=>false,'message'=>'Admin access required']); exit; }
  $input = json_decode(file_get_contents('php://input'), true); if(!$input){ $input = $_POST; }
  $orderId = (int)($input['order_id'] ?? 0); $status = $input['status'] ?? '';
  if($orderId<=0 || $status===''){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Order ID and status required']); exit; }
  $validStatuses = ['pending','confirmed','processing','shipped','delivered','cancelled','refunded'];
  if(!in_array($status,$validStatuses)){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid status']); exit; }
  $order = $db->execute('SELECT order_id FROM orders WHERE order_id=?',[$orderId])->fetch();
  if(!$order){ http_response_code(404); echo json_encode(['success'=>false,'message'=>'Order not found']); exit; }
  $db->execute('UPDATE orders SET status=?, updated_at=NOW() WHERE order_id=?',[$status,$orderId]);
  echo json_encode(['success'=>true,'message'=>'Order status updated successfully','status'=>$status]);
} catch(Exception $e){ http_response_code(500); echo json_encode(['success'=>false,'message'=>'Server error']); }
?>

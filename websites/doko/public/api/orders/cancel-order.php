<?php
// Cancel Order Endpoint (deduplicated)
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/Controllers/AuthController.php';
if (session_status()===PHP_SESSION_NONE) session_start();
try {
  $db = Database::getInstance();
  $auth = new AuthController();
  if(!$auth->isLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Authentication required']); exit; }
  $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
  $orderId = (int)($input['order_id'] ?? 0);
  if($orderId<=0){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Order ID required']); exit; }
  $user = $auth->getCurrentUser();
  $order = $db->execute('SELECT order_id,user_id,status FROM orders WHERE order_id=?',[$orderId])->fetch();
  if(!$order){ http_response_code(404); echo json_encode(['success'=>false,'message'=>'Order not found']); exit; }
  if(!$auth->isAdmin() && (int)$order['user_id'] !== (int)$user['user_id']) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Access denied']); exit; }
  // Tests expect generic "cannot be cancelled" phrase for disallowed statuses (e.g. shipped, delivered, cancelled already)
  if(!in_array($order['status'], ['pending','confirmed'])) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Order cannot be cancelled']); exit; }
  $db->execute('UPDATE orders SET status="cancelled", updated_at=NOW() WHERE order_id=?',[$orderId]);
  echo json_encode(['success'=>true,'message'=>'Order cancelled successfully']);
} catch(Exception $e){ http_response_code(500); echo json_encode(['success'=>false,'message'=>'Server error']); }
?>

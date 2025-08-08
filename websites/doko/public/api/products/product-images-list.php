<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';
require_once '../../../src/Controllers/AuthController.php';
session_start();
$auth = new AuthController();
if (!$auth->isAdmin()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }
$productId = (int)($_GET['product_id'] ?? 0);
if ($productId<=0) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'product_id required']); exit; }
try {
  $pdo = Database::getInstance()->getConnection();
  $stmt = $pdo->prepare('SELECT image_id, image_url, is_primary, sort_order, created_at FROM product_images WHERE product_id=? ORDER BY is_primary DESC, sort_order ASC, image_id DESC');
  $stmt->execute([$productId]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['success'=>true,'images'=>$rows]);
} catch (Exception $e) { http_response_code(500); echo json_encode(['success'=>false,'message'=>$e->getMessage()]); }
?>

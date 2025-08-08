<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';
require_once '../../../src/Controllers/AuthController.php';
session_start();
$auth = new AuthController();
if (!$auth->isAdmin()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'DELETE') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }
parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
$imageId = (int)($qs['image_id'] ?? 0);
if ($imageId<=0) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'image_id required']); exit; }
try {
  $pdo = Database::getInstance()->getConnection();
  $pdo->beginTransaction();
  $row = $pdo->prepare('SELECT product_id, image_url, is_primary FROM product_images WHERE image_id=? FOR UPDATE');
  $row->execute([$imageId]);
  $img = $row->fetch(PDO::FETCH_ASSOC);
  if (!$img) { throw new Exception('Image not found'); }
  $pdo->prepare('DELETE FROM product_images WHERE image_id=?')->execute([$imageId]);
  if (str_starts_with($img['image_url'],'/uploads/')) {
      $abs = realpath(__DIR__ . '/../../..') . $img['image_url'];
      if ($abs && is_file($abs)) @unlink($abs);
  }
  if ((int)$img['is_primary'] === 1) {
      $next = $pdo->prepare('SELECT image_id, image_url FROM product_images WHERE product_id=? ORDER BY image_id DESC LIMIT 1');
      $next->execute([$img['product_id']]);
      $n = $next->fetch(PDO::FETCH_ASSOC);
      if ($n) {
          $pdo->prepare('UPDATE product_images SET is_primary=1 WHERE image_id=?')->execute([$n['image_id']]);
          $pdo->prepare('UPDATE products SET image_url=? WHERE product_id=?')->execute([$n['image_url'],$img['product_id']]);
      } else {
          $col = $pdo->query("SHOW COLUMNS FROM products LIKE 'image_url'")->fetch();
          if ($col) { $pdo->prepare('UPDATE products SET image_url=NULL WHERE product_id=?')->execute([$img['product_id']]); }
      }
  }
  $pdo->commit();
  echo json_encode(['success'=>true,'message'=>'Image deleted']);
} catch (Exception $e) { if ($pdo->inTransaction()) $pdo->rollBack(); http_response_code(400); echo json_encode(['success'=>false,'message'=>$e->getMessage()]); }
?>

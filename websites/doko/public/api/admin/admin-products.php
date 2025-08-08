<?php
/**
 * Admin Products (Legacy Compatibility Endpoint)
 * Prefer using /api/products/list.php with admin context for richer data.
 */
require_once __DIR__ . '/../_bootstrap.php';

use Doko\Http\ApiResponse;

require_method('GET');
ensure_session();
$auth = auth_controller();
if (!$auth->isLoggedIn()) { ApiResponse::error('Authentication required', 401); exit; }
if (!$auth->isAdmin()) { ApiResponse::error('Access denied', 403); exit; }

$page = int_param('page', 1, 1);
$perPage = int_param('per_page', 10, 1, 100);
$offset = ($page - 1) * $perPage;

try {
  $database = db();
  $pdo = $database->getConnection();
  $total = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
  $stmt = $pdo->prepare("SELECT product_id, name, price, stock_quantity, status FROM products ORDER BY product_id DESC LIMIT :limit OFFSET :offset");
  $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

  ApiResponse::success([
    'products' => $products,
    'pagination' => [
      'current_page' => $page,
      'per_page' => $perPage,
      'total' => $total,
      'total_pages' => (int)ceil($total / $perPage)
    ],
    'meta' => [
      'deprecated' => true,
      'replacement' => '/api/products/list.php'
    ]
  ]);
} catch (Throwable $e) {
  ApiResponse::error('Failed to load products', 500, ['error' => $e->getMessage()]);
}

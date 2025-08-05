<?php
/**
 * Bulk Operations API - Admin Only
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../config/database.php';

try {
    // Check if user is admin
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Admin access required'
        ]);
        exit;
    }
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['operation'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Operation type is required'
        ]);
        exit;
    }
    
    switch ($input['operation']) {
        case 'bulk_update_product_status':
            bulkUpdateProductStatus($conn, $input);
            break;
        case 'bulk_update_product_prices':
            bulkUpdateProductPrices($conn, $input);
            break;
        case 'bulk_update_stock':
            bulkUpdateStock($conn, $input);
            break;
        case 'bulk_delete_products':
            bulkDeleteProducts($conn, $input);
            break;
        case 'bulk_update_categories':
            bulkUpdateCategories($conn, $input);
            break;
        case 'bulk_export_data':
            bulkExportData($conn, $input);
            break;
        case 'bulk_import_products':
            bulkImportProducts($conn, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid operation']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

function bulkUpdateProductStatus($conn, $input) {
    try {
        if (!isset($input['product_ids']) || !isset($input['status'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Product IDs and status are required'
            ]);
            return;
        }
        
        $productIds = $input['product_ids'];
        $status = $input['status'];
        $allowedStatuses = ['active', 'inactive', 'draft'];
        
        if (!in_array($status, $allowedStatuses)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid status. Allowed: ' . implode(', ', $allowedStatuses)
            ]);
            return;
        }
        
        $conn->beginTransaction();
        
        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        $query = "UPDATE products SET status = ? WHERE product_id IN ($placeholders)";
        
        $stmt = $conn->prepare($query);
        $params = array_merge([$status], $productIds);
        
        if ($stmt->execute($params)) {
            $affectedRows = $stmt->rowCount();
            
            // Log activity for each product
            foreach ($productIds as $productId) {
                logActivity($_SESSION['user_id'], 'bulk_update', 'product', $productId, null, ['status' => $status]);
            }
            
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => "Successfully updated {$affectedRows} products",
                'affected_rows' => $affectedRows
            ]);
        } else {
            $conn->rollback();
            throw new Exception('Failed to update product statuses');
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error updating product statuses: ' . $e->getMessage()
        ]);
    }
}

function bulkUpdateProductPrices($conn, $input) {
    try {
        if (!isset($input['updates']) || !is_array($input['updates'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Price updates array is required'
            ]);
            return;
        }
        
        $conn->beginTransaction();
        $successCount = 0;
        $errors = [];
        
        foreach ($input['updates'] as $update) {
            if (!isset($update['product_id']) || !isset($update['price'])) {
                $errors[] = "Missing product_id or price in update";
                continue;
            }
            
            $productId = (int)$update['product_id'];
            $price = (float)$update['price'];
            $originalPrice = isset($update['original_price']) ? (float)$update['original_price'] : null;
            
            if ($price <= 0) {
                $errors[] = "Invalid price for product ID {$productId}";
                continue;
            }
            
            $query = "UPDATE products SET price = :price";
            $params = [':price' => $price, ':product_id' => $productId];
            
            if ($originalPrice !== null) {
                $query .= ", original_price = :original_price";
                $params[':original_price'] = $originalPrice;
            }
            
            $query .= " WHERE product_id = :product_id";
            
            $stmt = $conn->prepare($query);
            
            if ($stmt->execute($params)) {
                $successCount++;
                logActivity($_SESSION['user_id'], 'bulk_update', 'product', $productId, null, ['price' => $price, 'original_price' => $originalPrice]);
            } else {
                $errors[] = "Failed to update price for product ID {$productId}";
            }
        }
        
        if ($successCount > 0) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => "Successfully updated prices for {$successCount} products",
                'updated_products' => $successCount,
                'errors' => $errors
            ]);
        } else {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'No products were updated',
                'errors' => $errors
            ]);
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error updating prices: ' . $e->getMessage()
        ]);
    }
}

function bulkUpdateStock($conn, $input) {
    try {
        if (!isset($input['updates']) || !is_array($input['updates'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Stock updates array is required'
            ]);
            return;
        }
        
        $conn->beginTransaction();
        $successCount = 0;
        $errors = [];
        
        foreach ($input['updates'] as $update) {
            if (!isset($update['product_id']) || !isset($update['stock_quantity'])) {
                $errors[] = "Missing product_id or stock_quantity in update";
                continue;
            }
            
            $productId = (int)$update['product_id'];
            $stockQuantity = (int)$update['stock_quantity'];
            $operation = $update['operation'] ?? 'set'; // 'set', 'add', 'subtract'
            
            if ($stockQuantity < 0 && $operation === 'set') {
                $errors[] = "Invalid stock quantity for product ID {$productId}";
                continue;
            }
            
            // Get current stock
            $currentStockQuery = "SELECT stock_quantity FROM products WHERE product_id = :product_id";
            $currentStockStmt = $conn->prepare($currentStockQuery);
            $currentStockStmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
            $currentStockStmt->execute();
            $currentStock = $currentStockStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$currentStock) {
                $errors[] = "Product ID {$productId} not found";
                continue;
            }
            
            $newStock = $currentStock['stock_quantity'];
            
            switch ($operation) {
                case 'add':
                    $newStock += $stockQuantity;
                    break;
                case 'subtract':
                    $newStock -= $stockQuantity;
                    if ($newStock < 0) $newStock = 0;
                    break;
                case 'set':
                default:
                    $newStock = $stockQuantity;
                    break;
            }
            
            $updateQuery = "UPDATE products SET stock_quantity = :stock_quantity WHERE product_id = :product_id";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bindValue(':stock_quantity', $newStock, PDO::PARAM_INT);
            $updateStmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
            
            if ($updateStmt->execute()) {
                // Record stock movement
                $movementQuery = "INSERT INTO stock_movements 
                                 (product_id, movement_type, quantity_change, quantity_before, quantity_after, reference_type, notes, created_by) 
                                 VALUES (:product_id, :movement_type, :quantity_change, :quantity_before, :quantity_after, :reference_type, :notes, :created_by)";
                
                $movementStmt = $conn->prepare($movementQuery);
                $movementStmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
                $movementStmt->bindValue(':movement_type', 'adjustment');
                $movementStmt->bindValue(':quantity_change', $newStock - $currentStock['stock_quantity'], PDO::PARAM_INT);
                $movementStmt->bindValue(':quantity_before', $currentStock['stock_quantity'], PDO::PARAM_INT);
                $movementStmt->bindValue(':quantity_after', $newStock, PDO::PARAM_INT);
                $movementStmt->bindValue(':reference_type', 'manual');
                $movementStmt->bindValue(':notes', "Bulk stock update via admin panel");
                $movementStmt->bindValue(':created_by', $_SESSION['user_id'], PDO::PARAM_INT);
                $movementStmt->execute();
                
                $successCount++;
                logActivity($_SESSION['user_id'], 'bulk_update', 'product', $productId, null, ['stock_quantity' => $newStock, 'operation' => $operation]);
            } else {
                $errors[] = "Failed to update stock for product ID {$productId}";
            }
        }
        
        if ($successCount > 0) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => "Successfully updated stock for {$successCount} products",
                'updated_products' => $successCount,
                'errors' => $errors
            ]);
        } else {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'No products were updated',
                'errors' => $errors
            ]);
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error updating stock: ' . $e->getMessage()
        ]);
    }
}

function bulkDeleteProducts($conn, $input) {
    try {
        if (!isset($input['product_ids']) || !is_array($input['product_ids'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Product IDs array is required'
            ]);
            return;
        }
        
        $productIds = $input['product_ids'];
        $softDelete = $input['soft_delete'] ?? true; // Default to soft delete
        
        $conn->beginTransaction();
        
        if ($softDelete) {
            // Soft delete - mark as inactive
            $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
            $query = "UPDATE products SET status = 'inactive' WHERE product_id IN ($placeholders)";
            
            $stmt = $conn->prepare($query);
            
            if ($stmt->execute($productIds)) {
                $affectedRows = $stmt->rowCount();
                
                // Log activity
                foreach ($productIds as $productId) {
                    logActivity($_SESSION['user_id'], 'soft_delete', 'product', $productId);
                }
                
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => "Successfully soft deleted {$affectedRows} products",
                    'affected_rows' => $affectedRows
                ]);
            } else {
                $conn->rollback();
                throw new Exception('Failed to soft delete products');
            }
        } else {
            // Hard delete - check for dependencies first
            $checkQuery = "SELECT product_id FROM order_items WHERE product_id IN (" . str_repeat('?,', count($productIds) - 1) . "?) LIMIT 1";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->execute($productIds);
            
            if ($checkStmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot permanently delete products that have been ordered. Use soft delete instead.'
                ]);
                return;
            }
            
            // Hard delete
            $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
            $query = "DELETE FROM products WHERE product_id IN ($placeholders)";
            
            $stmt = $conn->prepare($query);
            
            if ($stmt->execute($productIds)) {
                $affectedRows = $stmt->rowCount();
                
                // Log activity
                foreach ($productIds as $productId) {
                    logActivity($_SESSION['user_id'], 'hard_delete', 'product', $productId);
                }
                
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => "Successfully deleted {$affectedRows} products",
                    'affected_rows' => $affectedRows
                ]);
            } else {
                $conn->rollback();
                throw new Exception('Failed to delete products');
            }
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting products: ' . $e->getMessage()
        ]);
    }
}

function bulkUpdateCategories($conn, $input) {
    try {
        if (!isset($input['category_id']) || !isset($input['product_ids'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Category ID and Product IDs are required'
            ]);
            return;
        }
        
        $categoryId = (int)$input['category_id'];
        $productIds = $input['product_ids'];
        
        // Verify category exists
        $categoryQuery = "SELECT category_id FROM categories WHERE category_id = :category_id AND is_active = TRUE";
        $categoryStmt = $conn->prepare($categoryQuery);
        $categoryStmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $categoryStmt->execute();
        
        if ($categoryStmt->rowCount() === 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid or inactive category'
            ]);
            return;
        }
        
        $conn->beginTransaction();
        
        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        $query = "UPDATE products SET category_id = ? WHERE product_id IN ($placeholders)";
        
        $stmt = $conn->prepare($query);
        $params = array_merge([$categoryId], $productIds);
        
        if ($stmt->execute($params)) {
            $affectedRows = $stmt->rowCount();
            
            // Log activity
            foreach ($productIds as $productId) {
                logActivity($_SESSION['user_id'], 'bulk_update', 'product', $productId, null, ['category_id' => $categoryId]);
            }
            
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => "Successfully updated category for {$affectedRows} products",
                'affected_rows' => $affectedRows
            ]);
        } else {
            $conn->rollback();
            throw new Exception('Failed to update product categories');
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error updating categories: ' . $e->getMessage()
        ]);
    }
}

function bulkExportData($conn, $input) {
    try {
        $exportType = $input['export_type'] ?? 'products';
        $format = $input['format'] ?? 'csv'; // csv, json, excel
        
        switch ($exportType) {
            case 'products':
                $query = "SELECT 
                             p.*,
                             c.name as category_name
                          FROM products p
                          LEFT JOIN categories c ON p.category_id = c.category_id
                          WHERE p.status = 'active'
                          ORDER BY p.name";
                break;
            case 'orders':
                $dateFrom = $input['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
                $dateTo = $input['date_to'] ?? date('Y-m-d');
                
                $query = "SELECT 
                             o.*,
                             u.username,
                             u.email
                          FROM orders o
                          JOIN users u ON o.user_id = u.user_id
                          WHERE DATE(o.ordered_at) BETWEEN :date_from AND :date_to
                          ORDER BY o.ordered_at DESC";
                break;
            case 'customers':
                $query = "SELECT 
                             u.*,
                             COUNT(o.order_id) as total_orders,
                             COALESCE(SUM(o.total_amount), 0) as total_spent
                          FROM users u
                          LEFT JOIN orders o ON u.user_id = o.user_id AND o.status != 'cancelled'
                          WHERE u.role = 'customer'
                          GROUP BY u.user_id
                          ORDER BY total_spent DESC";
                break;
            default:
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid export type'
                ]);
                return;
        }
        
        $stmt = $conn->prepare($query);
        
        if ($exportType === 'orders' && isset($dateFrom) && isset($dateTo)) {
            $stmt->bindValue(':date_from', $dateFrom);
            $stmt->bindValue(':date_to', $dateTo);
        }
        
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Generate export data based on format
        $filename = $exportType . '_export_' . date('Y-m-d_H-i-s');
        
        switch ($format) {
            case 'json':
                $exportData = json_encode($data, JSON_PRETTY_PRINT);
                $contentType = 'application/json';
                $filename .= '.json';
                break;
            case 'csv':
            default:
                $exportData = arrayToCsv($data);
                $contentType = 'text/csv';
                $filename .= '.csv';
                break;
        }
        
        // Log export activity
        logActivity($_SESSION['user_id'], 'export', $exportType, null, null, ['format' => $format, 'records' => count($data)]);
        
        echo json_encode([
            'success' => true,
            'message' => "Export generated successfully",
            'data' => base64_encode($exportData),
            'filename' => $filename,
            'content_type' => $contentType,
            'record_count' => count($data)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error generating export: ' . $e->getMessage()
        ]);
    }
}

function arrayToCsv($data) {
    if (empty($data)) {
        return '';
    }
    
    $output = fopen('php://temp', 'r+');
    
    // Add headers
    fputcsv($output, array_keys($data[0]));
    
    // Add data rows
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

function bulkImportProducts($conn, $input) {
    // This would handle CSV/Excel import of products
    // Implementation would depend on the specific import format
    echo json_encode([
        'success' => false,
        'message' => 'Bulk import functionality coming soon'
    ]);
}
?>

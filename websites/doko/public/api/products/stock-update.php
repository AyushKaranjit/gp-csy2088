<?php
/**
 * Stock Update API
 * Handles inventory stock level updates with validation and logging
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';
require_once '../../src/Controllers/AuthController.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $db = Database::getInstance();
    $auth = new AuthController();
    
    // Start session to check authentication
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is authenticated and has proper permissions
    if (!$auth->isLoggedIn() || (!$auth->isAdmin())) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access. Admin role required.'
        ]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'] ?? 'customer';
    
    // Only allow PUT and POST methods
    if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed. Use PUT or POST.'
        ]);
        exit;
    }
    
    // Get request data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Also check for POST data (form submission)
    if (empty($data) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $_POST;
    }
    
    if (empty($data)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No data provided'
        ]);
        exit;
    }
    
    // Validate required fields for single product update
    if (isset($data['product_id']) && isset($data['stock_quantity'])) {
        $updates = [[
            'product_id' => $data['product_id'],
            'stock_quantity' => $data['stock_quantity'],
            'low_stock_threshold' => $data['low_stock_threshold'] ?? null,
            'reason' => $data['reason'] ?? 'Manual update',
            'notes' => $data['notes'] ?? ''
        ]];
    }
    // Handle bulk updates
    elseif (isset($data['updates']) && is_array($data['updates'])) {
        $updates = $data['updates'];
    }
    // Handle CSV import format
    elseif (isset($data['csv_data']) && is_array($data['csv_data'])) {
        $updates = [];
        foreach ($data['csv_data'] as $row) {
            if (isset($row['product_id'], $row['stock_quantity'])) {
                $updates[] = [
                    'product_id' => $row['product_id'],
                    'stock_quantity' => $row['stock_quantity'],
                    'low_stock_threshold' => $row['low_stock_threshold'] ?? null,
                    'reason' => $row['reason'] ?? 'CSV import',
                    'notes' => $row['notes'] ?? ''
                ];
            }
        }
    }
    else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid data format. Provide product_id and stock_quantity, or updates array.'
        ]);
        exit;
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No valid updates provided'
        ]);
        exit;
    }
    
    $successful_updates = [];
    $failed_updates = [];
    $warnings = [];
    
    // Begin transaction for atomicity
    $db->beginTransaction();
    
    try {
        foreach ($updates as $update) {
            $product_id = intval($update['product_id']);
            $new_stock = intval($update['stock_quantity']);
            $low_threshold = isset($update['low_stock_threshold']) ? intval($update['low_stock_threshold']) : null;
            $reason = $update['reason'] ?? 'Manual update';
            $notes = $update['notes'] ?? '';
            
            // Validate product ID
            if ($product_id <= 0) {
                $failed_updates[] = [
                    'product_id' => $product_id,
                    'error' => 'Invalid product ID'
                ];
                continue;
            }
            
            // Validate stock quantity (allow 0 for out of stock)
            if ($new_stock < 0) {
                $failed_updates[] = [
                    'product_id' => $product_id,
                    'error' => 'Stock quantity cannot be negative'
                ];
                continue;
            }
            
            // Check if product exists and get current data
            $product_query = "SELECT product_id, name, sku, stock_quantity, low_stock_threshold FROM products WHERE product_id = ?";
            $product_stmt = $db->execute($product_query, [$product_id]);
            $product = $product_stmt->fetch();
            
            if (!$product) {
                $failed_updates[] = [
                    'product_id' => $product_id,
                    'error' => 'Product not found'
                ];
                continue;
            }
            
            $old_stock = $product['stock_quantity'];
            $old_threshold = $product['low_stock_threshold'];
            
            // Prepare update data
            $update_fields = ['stock_quantity = ?'];
            $update_params = [$new_stock];
            
            if ($low_threshold !== null) {
                $update_fields[] = 'low_stock_threshold = ?';
                $update_params[] = $low_threshold;
            } else {
                $low_threshold = $old_threshold; // Keep existing threshold for logging
            }
            
            $update_fields[] = 'updated_at = NOW()';
            $update_params[] = $product_id;
            
            // Update the product
            $update_query = "UPDATE products SET " . implode(', ', $update_fields) . " WHERE product_id = ?";
            $update_stmt = $db->execute($update_query, $update_params);
            
            if ($update_stmt->rowCount() > 0) {
                // Log the stock change
                $log_query = "INSERT INTO stock_movements (product_id, user_id, movement_type, quantity_change, old_quantity, new_quantity, reason, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $quantity_change = $new_stock - $old_stock;
                $movement_type = $quantity_change > 0 ? 'stock_in' : ($quantity_change < 0 ? 'stock_out' : 'adjustment');
                
                // Create stock_movements table if it doesn't exist
                try {
                    $db->execute($log_query, [$product_id, $user_id, $movement_type, $quantity_change, $old_stock, $new_stock, $reason, $notes]);
                } catch (Exception $e) {
                    // If stock_movements table doesn't exist, create it
                    $create_table_query = "
                        CREATE TABLE IF NOT EXISTS stock_movements (
                            movement_id INT AUTO_INCREMENT PRIMARY KEY,
                            product_id INT NOT NULL,
                            user_id INT NOT NULL,
                            movement_type ENUM('stock_in', 'stock_out', 'adjustment', 'sale', 'return', 'damaged', 'expired') NOT NULL,
                            quantity_change INT NOT NULL,
                            old_quantity INT NOT NULL,
                            new_quantity INT NOT NULL,
                            reason VARCHAR(255),
                            notes TEXT,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
                            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
                        )
                    ";
                    $db->execute($create_table_query);
                    
                    // Retry the log insert
                    $db->execute($log_query, [$product_id, $user_id, $movement_type, $quantity_change, $old_stock, $new_stock, $reason, $notes]);
                }
                
                $successful_updates[] = [
                    'product_id' => $product_id,
                    'product_name' => $product['name'],
                    'sku' => $product['sku'],
                    'old_stock' => $old_stock,
                    'new_stock' => $new_stock,
                    'change' => $quantity_change,
                    'low_threshold' => $low_threshold,
                    'reason' => $reason
                ];
                
                // Add warnings for stock levels
                if ($new_stock <= 0) {
                    $warnings[] = "Product '{$product['name']}' is now out of stock";
                } elseif ($low_threshold && $new_stock <= $low_threshold) {
                    $warnings[] = "Product '{$product['name']}' is below low stock threshold ($low_threshold)";
                }
                
                // Check for large stock changes
                if ($old_stock > 0 && abs($quantity_change) > ($old_stock * 0.5)) {
                    $warnings[] = "Large stock change detected for '{$product['name']}': {$quantity_change} units";
                }
                
            } else {
                $failed_updates[] = [
                    'product_id' => $product_id,
                    'error' => 'Failed to update product stock'
                ];
            }
        }
        
        // Commit transaction
        $db->commit();
        
        // Prepare response
        $total_attempted = count($updates);
        $successful_count = count($successful_updates);
        $failed_count = count($failed_updates);
        
        $response = [
            'success' => $failed_count === 0,
            'message' => $successful_count > 0 
                ? "Successfully updated {$successful_count} of {$total_attempted} products"
                : "No products were updated",
            'data' => [
                'successful_updates' => $successful_updates,
                'failed_updates' => $failed_updates,
                'warnings' => $warnings,
                'summary' => [
                    'total_attempted' => $total_attempted,
                    'successful' => $successful_count,
                    'failed' => $failed_count
                ]
            ],
            'timestamp' => date('c')
        ];
        
        // Set appropriate HTTP status code
        if ($failed_count === 0) {
            http_response_code(200);
        } elseif ($successful_count > 0) {
            http_response_code(207); // Multi-status
        } else {
            http_response_code(400);
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred while updating stock',
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
    
    // Log error for debugging
    error_log("Stock Update API Error: " . $e->getMessage());
}
?>

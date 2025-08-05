<?php
/**
 * Image Management API
 * DOKO Grocery E-commerce - Admin Only
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../Controllers/AuthController.php';
require_once '../../../config/database.php';

try {
    // Check authentication
    $auth = new AuthController();
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    
    // Check if user is admin
    if (!$auth->isAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        exit;
    }
    
    $database = Database::getInstance();
    $conn = $database->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetImages($conn);
            break;
        case 'PUT':
            handleUpdateImage($conn);
            break;
        case 'DELETE':
            handleDeleteImage($conn);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Image management error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

function handleGetImages($conn) {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
    $type = isset($_GET['type']) ? $_GET['type'] : null;
    $entityId = isset($_GET['entity_id']) ? (int)$_GET['entity_id'] : null;
    
    $offset = ($page - 1) * $limit;
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if ($type) {
        $whereConditions[] = "upload_type = :type";
        $params[':type'] = $type;
    }
    
    if ($entityId) {
        $whereConditions[] = "entity_id = :entity_id";
        $params[':entity_id'] = $entityId;
    }
    
    $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $query = "SELECT i.*, u.username as uploaded_by_name 
              FROM uploaded_images i
              LEFT JOIN users u ON i.uploaded_by = u.user_id
              {$whereClause}
              ORDER BY i.created_at DESC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $images = $stmt->fetchAll();
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM uploaded_images i {$whereClause}";
    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    
    // Format image data
    $formattedImages = array_map(function($img) {
        return [
            'image_id' => (int)$img['image_id'],
            'filename' => $img['filename'],
            'original_name' => $img['original_name'],
            'web_path' => $img['web_path'],
            'file_size' => (int)$img['file_size'],
            'file_size_formatted' => formatFileSize($img['file_size']),
            'mime_type' => $img['mime_type'],
            'upload_type' => $img['upload_type'],
            'entity_id' => $img['entity_id'] ? (int)$img['entity_id'] : null,
            'alt_text' => $img['alt_text'],
            'is_primary' => (bool)$img['is_primary'],
            'uploaded_by' => $img['uploaded_by_name'],
            'created_at' => $img['created_at']
        ];
    }, $images);
    
    echo json_encode([
        'success' => true,
        'data' => $formattedImages,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_items' => (int)$total,
            'items_per_page' => $limit
        ]
    ]);
}

function handleUpdateImage($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['image_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Image ID is required']);
        return;
    }
    
    $imageId = (int)$input['image_id'];
    $altText = $input['alt_text'] ?? '';
    $isPrimary = isset($input['is_primary']) ? (bool)$input['is_primary'] : false;
    
    // Update image
    $query = "UPDATE uploaded_images 
              SET alt_text = :alt_text, is_primary = :is_primary, updated_at = NOW()
              WHERE image_id = :image_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':alt_text', $altText);
    $stmt->bindParam(':is_primary', $isPrimary);
    $stmt->bindParam(':image_id', $imageId);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Image not found']);
        return;
    }
    
    // If setting as primary, update other images of same type/entity
    if ($isPrimary) {
        $getImageQuery = "SELECT upload_type, entity_id FROM uploaded_images WHERE image_id = :image_id";
        $getStmt = $conn->prepare($getImageQuery);
        $getStmt->bindParam(':image_id', $imageId);
        $getStmt->execute();
        $image = $getStmt->fetch();
        
        if ($image && $image['entity_id']) {
            $updateQuery = "UPDATE uploaded_images 
                           SET is_primary = FALSE 
                           WHERE entity_id = :entity_id 
                           AND upload_type = :upload_type 
                           AND image_id != :image_id";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bindParam(':entity_id', $image['entity_id']);
            $updateStmt->bindParam(':upload_type', $image['upload_type']);
            $updateStmt->bindParam(':image_id', $imageId);
            $updateStmt->execute();
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Image updated successfully'
    ]);
}

function handleDeleteImage($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['image_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Image ID is required']);
        return;
    }
    
    $imageId = (int)$input['image_id'];
    
    // Get image info before deletion
    $getQuery = "SELECT file_path, web_path FROM uploaded_images WHERE image_id = :image_id";
    $getStmt = $conn->prepare($getQuery);
    $getStmt->bindParam(':image_id', $imageId);
    $getStmt->execute();
    
    if ($getStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Image not found']);
        return;
    }
    
    $image = $getStmt->fetch();
    
    // Delete from database
    $deleteQuery = "DELETE FROM uploaded_images WHERE image_id = :image_id";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bindParam(':image_id', $imageId);
    $deleteStmt->execute();
    
    // Delete physical file
    if (file_exists($image['file_path'])) {
        unlink($image['file_path']);
        
        // Delete resized versions if they exist
        $pathInfo = pathinfo($image['file_path']);
        $sizesToDelete = ['thumbnail', 'medium', 'large'];
        
        foreach ($sizesToDelete as $size) {
            $resizedFile = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $size . '.' . $pathInfo['extension'];
            if (file_exists($resizedFile)) {
                unlink($resizedFile);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Image deleted successfully'
    ]);
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>

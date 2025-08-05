<?php
/**
 * Image Upload API
 * DOKO Grocery E-commerce - Admin Only
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once '../../../Controllers/AuthController.php';
require_once '../../../config/database.php';

try {
    // Check authentication
    $auth = new AuthController();
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required'
        ]);
        exit;
    }
    
    // Check if user is admin
    if (!$auth->isAdmin()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Admin access required'
        ]);
        exit;
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No file uploaded or upload error occurred'
        ]);
        exit;
    }
    
    $file = $_FILES['image'];
    $uploadType = $_POST['type'] ?? 'product'; // product, category, brand, etc.
    $entityId = isset($_POST['entity_id']) ? (int)$_POST['entity_id'] : null;
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'
        ]);
        exit;
    }
    
    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'File size too large. Maximum 5MB allowed.'
        ]);
        exit;
    }
    
    // Create upload directory structure
    $uploadDir = '../../../public/uploads/' . $uploadType . '/';
    $monthDir = date('Y/m/');
    $fullUploadDir = $uploadDir . $monthDir;
    
    if (!file_exists($fullUploadDir)) {
        if (!mkdir($fullUploadDir, 0755, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid($uploadType . '_') . '_' . time() . '.' . $extension;
    $filePath = $fullUploadDir . $filename;
    $webPath = 'uploads/' . $uploadType . '/' . $monthDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    // Resize image if needed
    $resizedPath = resizeImage($filePath, $uploadType);
    if ($resizedPath) {
        $webPath = str_replace('../../../public/', '', $resizedPath);
    }
    
    // Save to database
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    // Insert image record
    $query = "INSERT INTO uploaded_images (filename, original_name, file_path, web_path, file_size, mime_type, upload_type, entity_id, uploaded_by, created_at) 
              VALUES (:filename, :original_name, :file_path, :web_path, :file_size, :mime_type, :upload_type, :entity_id, :uploaded_by, NOW())";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':filename', $filename);
    $stmt->bindParam(':original_name', $file['name']);
    $stmt->bindParam(':file_path', $filePath);
    $stmt->bindParam(':web_path', $webPath);
    $stmt->bindParam(':file_size', $file['size']);
    $stmt->bindParam(':mime_type', $file['type']);
    $stmt->bindParam(':upload_type', $uploadType);
    $stmt->bindParam(':entity_id', $entityId);
    $stmt->bindParam(':uploaded_by', $_SESSION['user_id']);
    $stmt->execute();
    
    $imageId = $conn->lastInsertId();
    
    // If this is a product image, also add to product_images table
    if ($uploadType === 'product' && $entityId) {
        $isPrimary = isset($_POST['is_primary']) ? (bool)$_POST['is_primary'] : false;
        
        // If this is set as primary, unset others
        if ($isPrimary) {
            $updateQuery = "UPDATE product_images SET is_primary = FALSE WHERE product_id = :product_id";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bindParam(':product_id', $entityId);
            $updateStmt->execute();
        }
        
        $productImageQuery = "INSERT INTO product_images (product_id, image_url, alt_text, is_primary) 
                             VALUES (:product_id, :image_url, :alt_text, :is_primary)";
        $productImageStmt = $conn->prepare($productImageQuery);
        $productImageStmt->bindParam(':product_id', $entityId);
        $productImageStmt->bindParam(':image_url', $webPath);
        $productImageStmt->bindParam(':alt_text', $_POST['alt_text'] ?? '');
        $productImageStmt->bindParam(':is_primary', $isPrimary);
        $productImageStmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully',
        'data' => [
            'image_id' => $imageId,
            'filename' => $filename,
            'web_path' => $webPath,
            'full_url' => '/websites/doko/public/' . $webPath,
            'file_size' => $file['size'],
            'upload_type' => $uploadType
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Image upload error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Upload failed: ' . $e->getMessage()
    ]);
}

/**
 * Resize image based on upload type
 */
function resizeImage($filePath, $type) {
    $maxWidths = [
        'product' => 800,
        'category' => 400,
        'brand' => 300,
        'banner' => 1200
    ];
    
    $maxWidth = $maxWidths[$type] ?? 800;
    $maxHeight = $maxWidth;
    
    // Get image info
    $imageInfo = getimagesize($filePath);
    if (!$imageInfo) return false;
    
    list($width, $height, $imageType) = $imageInfo;
    
    // Don't resize if image is already smaller
    if ($width <= $maxWidth && $height <= $maxHeight) {
        return $filePath;
    }
    
    // Calculate new dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);
    
    // Create image resource
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($filePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($filePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($filePath);
            break;
        default:
            return false;
    }
    
    // Create new image
    $destination = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
    }
    
    // Resize
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save resized image
    $resizedPath = dirname($filePath) . '/resized_' . basename($filePath);
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            imagejpeg($destination, $resizedPath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($destination, $resizedPath, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($destination, $resizedPath);
            break;
    }
    
    // Clean up
    imagedestroy($source);
    imagedestroy($destination);
    
    // Replace original with resized
    unlink($filePath);
    rename($resizedPath, $filePath);
    
    return $filePath;
}
?>

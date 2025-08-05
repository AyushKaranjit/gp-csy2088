<?php
/**
 * Custom Image Upload API
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
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    
    // Check if user is admin
    if (!$auth->isAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
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
    $uploadType = $_POST['type'] ?? 'product'; // product, category, brand, user
    $entityId = isset($_POST['entity_id']) ? (int)$_POST['entity_id'] : null;
    $altText = $_POST['alt_text'] ?? '';
    $isPrimary = isset($_POST['is_primary']) ? (bool)$_POST['is_primary'] : false;
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'
        ]);
        exit;
    }
    
    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'File size too large. Maximum 5MB allowed.'
        ]);
        exit;
    }
    
    // Create upload directory structure
    $uploadDir = __DIR__ . '/../../../../public/uploads/' . $uploadType . '/';
    $monthDir = date('Y/m/');
    $fullUploadDir = $uploadDir . $monthDir;
    
    if (!file_exists($fullUploadDir)) {
        mkdir($fullUploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid($uploadType . '_') . '_' . time() . '.' . $extension;
    $filePath = $fullUploadDir . $filename;
    $webPath = 'uploads/' . $uploadType . '/' . $monthDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to save uploaded file'
        ]);
        exit;
    }
    
    // Create different sizes for products
    $sizes = [];
    if ($uploadType === 'product') {
        $sizes = createProductImageSizes($filePath, $fullUploadDir, $filename);
    }
    
    // Save to database
    $database = Database::getInstance();
    $conn = $database->getConnection();
    
    session_start();
    $uploadedBy = $_SESSION['user_id'];
    
    // Insert image record
    $query = "INSERT INTO uploaded_images (
        filename, original_name, file_path, web_path, file_size, mime_type, 
        upload_type, entity_id, alt_text, is_primary, uploaded_by, created_at
    ) VALUES (
        :filename, :original_name, :file_path, :web_path, :file_size, :mime_type, 
        :upload_type, :entity_id, :alt_text, :is_primary, :uploaded_by, NOW()
    )";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':filename', $filename);
    $stmt->bindParam(':original_name', $file['name']);
    $stmt->bindParam(':file_path', $filePath);
    $stmt->bindParam(':web_path', $webPath);
    $stmt->bindParam(':file_size', $file['size']);
    $stmt->bindParam(':mime_type', $mimeType);
    $stmt->bindParam(':upload_type', $uploadType);
    $stmt->bindParam(':entity_id', $entityId);
    $stmt->bindParam(':alt_text', $altText);
    $stmt->bindParam(':is_primary', $isPrimary);
    $stmt->bindParam(':uploaded_by', $uploadedBy);
    $stmt->execute();
    
    $imageId = $conn->lastInsertId();
    
    // If this is a primary image, update other images to non-primary
    if ($isPrimary && $entityId) {
        $updateQuery = "UPDATE uploaded_images 
                       SET is_primary = FALSE 
                       WHERE entity_id = :entity_id 
                       AND upload_type = :upload_type 
                       AND image_id != :image_id";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':entity_id', $entityId);
        $updateStmt->bindParam(':upload_type', $uploadType);
        $updateStmt->bindParam(':image_id', $imageId);
        $updateStmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully',
        'data' => [
            'image_id' => $imageId,
            'filename' => $filename,
            'web_path' => $webPath,
            'file_size' => $file['size'],
            'mime_type' => $mimeType,
            'sizes' => $sizes
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Image upload error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during image upload: ' . $e->getMessage()
    ]);
}

/**
 * Create different sizes for product images
 */
function createProductImageSizes($originalPath, $uploadDir, $filename) {
    $sizes = [
        'thumbnail' => [150, 150],
        'medium' => [300, 300],
        'large' => [600, 600]
    ];
    
    $createdSizes = [];
    $pathInfo = pathinfo($filename);
    
    foreach ($sizes as $sizeName => $dimensions) {
        $newFilename = $pathInfo['filename'] . '_' . $sizeName . '.' . $pathInfo['extension'];
        $newPath = $uploadDir . $newFilename;
        
        if (resizeImage($originalPath, $newPath, $dimensions[0], $dimensions[1])) {
            $createdSizes[$sizeName] = [
                'filename' => $newFilename,
                'width' => $dimensions[0],
                'height' => $dimensions[1]
            ];
        }
    }
    
    return $createdSizes;
}

/**
 * Resize image to specified dimensions
 */
function resizeImage($sourcePath, $destPath, $maxWidth, $maxHeight) {
    try {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) return false;
        
        list($origWidth, $origHeight, $imageType) = $imageInfo;
        
        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
        $newWidth = round($origWidth * $ratio);
        $newHeight = round($origHeight * $ratio);
        
        // Create source image
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }
        
        if (!$source) return false;
        
        // Create destination image
        $destination = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize image
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        
        // Save image
        $result = false;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($destination, $destPath, 90);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($destination, $destPath, 9);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($destination, $destPath);
                break;
            case IMAGETYPE_WEBP:
                $result = imagewebp($destination, $destPath, 90);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($destination);
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Image resize error: " . $e->getMessage());
        return false;
    }
}
?>

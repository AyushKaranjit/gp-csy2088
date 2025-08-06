<?php
/**
 * Image Upload API for Admin
 * Upload and store product images
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();

try {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../src/Controllers/AuthController.php';

    // Verify admin authentication
    $auth = new AuthController();
    if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handleImageUpload();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        handleImageRetrieve();
    }

} catch (Exception $e) {
    error_log("Image Upload API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

function handleImageUpload() {
    try {
        if (!isset($_FILES['image'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No image uploaded']);
            return;
        }

        $file = $_FILES['image'];
        
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only images are allowed.']);
            return;
        }

        // Check file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB.']);
            return;
        }

        // Create uploads directory if it doesn't exist
        $uploadsDir = __DIR__ . '/../../public/uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . uniqid() . '.' . $extension;
        $filepath = $uploadsDir . '/' . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Store in database
            $db = Database::getInstance()->getConnection();
            $query = "INSERT INTO product_images (filename, original_name, file_type, file_size, created_at) 
                      VALUES (?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($query);
            $stmt->execute([$filename, $file['name'], $file['type'], $file['size']]);
            
            $imageId = $db->lastInsertId();
            $imageUrl = 'uploads/' . $filename;

            echo json_encode([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'id' => $imageId,
                    'filename' => $filename,
                    'url' => $imageUrl,
                    'original_name' => $file['name']
                ]
            ]);
        } else {
            throw new Exception('Failed to move uploaded file');
        }

    } catch (Exception $e) {
        error_log("Image upload error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
    }
}

function handleImageRetrieve() {
    try {
        $db = Database::getInstance()->getConnection();
        
        if (isset($_GET['id'])) {
            // Get specific image
            $imageId = (int)$_GET['id'];
            $query = "SELECT * FROM product_images WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$imageId]);
            $image = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($image) {
                $image['url'] = 'uploads/' . $image['filename'];
                echo json_encode(['success' => true, 'data' => $image]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Image not found']);
            }
        } else {
            // Get all images with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = ($page - 1) * $limit;

            $countQuery = "SELECT COUNT(*) as total FROM product_images";
            $stmt = $db->prepare($countQuery);
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $query = "SELECT * FROM product_images ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$limit, $offset]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Add URL to each image
            foreach ($images as &$image) {
                $image['url'] = 'uploads/' . $image['filename'];
            }

            echo json_encode([
                'success' => true,
                'data' => $images,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_records' => $total,
                    'per_page' => $limit
                ]
            ]);
        }

    } catch (Exception $e) {
        error_log("Image retrieve error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to retrieve images']);
    }
}
?>

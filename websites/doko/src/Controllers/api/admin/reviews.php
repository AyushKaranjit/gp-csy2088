<?php
/**
 * Reviews Management API - Admin Only
 * DOKO Grocery E-commerce
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, DELETE');
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
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetReviews($conn);
            break;
        case 'PUT':
            handleUpdateReview($conn);
            break;
        case 'DELETE':
            handleDeleteReview($conn);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

function handleGetReviews($conn) {
    try {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
        $offset = ($page - 1) * $limit;
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';
        $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
        
        $query = "SELECT 
                     pr.*,
                     p.name as product_name,
                     p.image_url as product_image,
                     u.username,
                     u.email as user_email,
                     o.order_number
                  FROM product_reviews pr
                  JOIN products p ON pr.product_id = p.product_id
                  JOIN users u ON pr.user_id = u.user_id
                  LEFT JOIN orders o ON pr.order_id = o.order_id
                  WHERE 1=1";
        
        $params = [];
        
        if ($status !== 'all') {
            $query .= " AND pr.status = :status";
            $params[':status'] = $status;
        }
        
        if ($productId) {
            $query .= " AND pr.product_id = :product_id";
            $params[':product_id'] = $productId;
        }
        
        $query .= " ORDER BY pr.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM product_reviews pr WHERE 1=1";
        if ($status !== 'all') {
            $countQuery .= " AND pr.status = :status";
        }
        if ($productId) {
            $countQuery .= " AND pr.product_id = :product_id";
        }
        
        $countStmt = $conn->prepare($countQuery);
        if ($status !== 'all') {
            $countStmt->bindValue(':status', $status);
        }
        if ($productId) {
            $countStmt->bindValue(':product_id', $productId);
        }
        $countStmt->execute();
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get review statistics
        $statsQuery = "SELECT 
                          COUNT(*) as total_reviews,
                          COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_reviews,
                          COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_reviews,
                          COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_reviews,
                          AVG(rating) as average_rating
                       FROM product_reviews";
        $statsStmt = $conn->prepare($statsQuery);
        $statsStmt->execute();
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $reviews,
            'stats' => $stats,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int)$totalCount,
                'pages' => ceil($totalCount / $limit)
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching reviews: ' . $e->getMessage()
        ]);
    }
}

function handleUpdateReview($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['review_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Review ID is required'
            ]);
            return;
        }
        
        $reviewId = (int)$input['review_id'];
        
        // Check if review exists
        $checkQuery = "SELECT * FROM product_reviews WHERE review_id = :review_id";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
        $checkStmt->execute();
        
        $existingReview = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$existingReview) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Review not found'
            ]);
            return;
        }
        
        // Build update query dynamically
        $updateFields = [];
        $params = [':review_id' => $reviewId];
        
        if (isset($input['status'])) {
            $allowedStatuses = ['pending', 'approved', 'rejected'];
            if (!in_array($input['status'], $allowedStatuses)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid status. Allowed: ' . implode(', ', $allowedStatuses)
                ]);
                return;
            }
            $updateFields[] = "status = :status";
            $params[':status'] = $input['status'];
        }
        
        if (isset($input['admin_response'])) {
            $updateFields[] = "admin_response = :admin_response";
            $params[':admin_response'] = $input['admin_response'];
        }
        
        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'No fields to update'
            ]);
            return;
        }
        
        $query = "UPDATE product_reviews SET " . implode(', ', $updateFields) . " WHERE review_id = :review_id";
        
        $stmt = $conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if ($stmt->execute()) {
            // Log activity
            logActivity($_SESSION['user_id'], 'update', 'review', $reviewId, $existingReview, $input);
            
            // If approved, update product rating cache
            if (isset($input['status']) && $input['status'] === 'approved') {
                updateProductRatingCache($conn, $existingReview['product_id']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Review updated successfully'
            ]);
        } else {
            throw new Exception('Failed to update review');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error updating review: ' . $e->getMessage()
        ]);
    }
}

function handleDeleteReview($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['review_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Review ID is required'
            ]);
            return;
        }
        
        $reviewId = (int)$input['review_id'];
        
        // Get review details before deletion
        $reviewQuery = "SELECT * FROM product_reviews WHERE review_id = :review_id";
        $reviewStmt = $conn->prepare($reviewQuery);
        $reviewStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
        $reviewStmt->execute();
        
        $review = $reviewStmt->fetch(PDO::FETCH_ASSOC);
        if (!$review) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Review not found'
            ]);
            return;
        }
        
        // Delete review
        $deleteQuery = "DELETE FROM product_reviews WHERE review_id = :review_id";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
        
        if ($deleteStmt->execute()) {
            // Log activity
            logActivity($_SESSION['user_id'], 'delete', 'review', $reviewId, $review);
            
            // Update product rating cache
            updateProductRatingCache($conn, $review['product_id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);
        } else {
            throw new Exception('Failed to delete review');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting review: ' . $e->getMessage()
        ]);
    }
}

function updateProductRatingCache($conn, $productId) {
    try {
        $ratingQuery = "SELECT 
                           AVG(rating) as avg_rating,
                           COUNT(*) as review_count
                        FROM product_reviews 
                        WHERE product_id = :product_id AND status = 'approved'";
        
        $ratingStmt = $conn->prepare($ratingQuery);
        $ratingStmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $ratingStmt->execute();
        
        $ratingData = $ratingStmt->fetch(PDO::FETCH_ASSOC);
        
        $updateQuery = "UPDATE products 
                       SET avg_rating = :avg_rating, 
                           review_count = :review_count 
                       WHERE product_id = :product_id";
        
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindValue(':avg_rating', $ratingData['avg_rating'] ?: 0);
        $updateStmt->bindValue(':review_count', $ratingData['review_count'] ?: 0);
        $updateStmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $updateStmt->execute();
        
    } catch (Exception $e) {
        // Log error but don't fail the main operation
        error_log("Failed to update product rating cache: " . $e->getMessage());
    }
}
?>

<?php
/** Cart Add API (refactored) */
require_once __DIR__ . '/../_bootstrap.php';
use Doko\Http\ApiResponse;
require_method('POST');

try {
    // Get JSON input (tests send JSON). Fallback to POST form fields if JSON empty
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (!$input) {
        $input = $_POST; // fallback
    }
    if (!is_array($input)) { $input = []; }
    
    if (!isset($input['product_id']) || !isset($input['quantity'])) { ApiResponse::error('Product ID and quantity are required', 400); return; }
    
    $product_id = (int)$input['product_id'];
    $quantity = (int)$input['quantity'];
    
    if ($quantity <= 0) { ApiResponse::error('Quantity must be greater than 0', 400); return; }
    
    // Check if user is logged in
    $auth = new AuthController();
    $isLoggedIn = $auth->isLoggedIn();
    if (!$isLoggedIn) { ApiResponse::error('Authentication required', 401, ['is_logged_in' => false]); return; }
    
    $user = $auth->getCurrentUser();
    if (!$user || !isset($user['user_id'])) { ApiResponse::error('User not found', 401); return; }
    
    $user_id = $user['user_id'];
    
    // Get database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if product exists
    $query = "SELECT product_id, name, price, stock_quantity FROM products WHERE product_id = ? AND status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) { ApiResponse::error('Product not found', 404); return; }
    
    // Explicit out of stock check
    if ($product['stock_quantity'] <= 0) { ApiResponse::error('Product is out of stock', 400); return; }
    // Check stock availability against requested quantity
    if ($product['stock_quantity'] < $quantity) { ApiResponse::error('Insufficient stock available', 400); return; }
    
    // Check if item already exists in cart
    $query = "SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing cart item
        $new_quantity = $existing['quantity'] + $quantity;
        $query = "UPDATE cart SET quantity = ?, created_at = NOW() WHERE cart_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$new_quantity, $existing['cart_id']]);
    } else {
    // Add new cart item (schema requires price column)
    $query = "INSERT INTO cart (user_id, product_id, quantity, price, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id, $product_id, $quantity, $product['price']]);
    }
    
    // Calculate current cart total for response convenience
    $totalQuery = "SELECT SUM(c.quantity * p.price) as total FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?";
    $totalStmt = $conn->prepare($totalQuery);
    $totalStmt->execute([$user_id]);
    $total = (float) ($totalStmt->fetchColumn() ?? 0);

    ApiResponse::success([
        'message' => 'Product added to cart successfully',
        'total' => $total,
        'is_logged_in' => true
    ]);
    
} catch (Exception $e) {
    error_log("Add to cart API error: " . $e->getMessage());
    ApiResponse::error('An error occurred while adding to cart', 500);
}
?>

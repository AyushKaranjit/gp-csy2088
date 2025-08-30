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
    
    // Auth (use shared helper for consistency / session reuse)
    $auth = auth_controller();
    $isLoggedIn = $auth->isLoggedIn();
    if (!$isLoggedIn) { ApiResponse::error('Authentication required', 401, ['is_logged_in' => false]); return; }
    
    $user = $auth->getCurrentUser();
    if (!$user || !isset($user['user_id'])) { ApiResponse::error('User not found', 401); return; }
    
    $user_id = $user['user_id'];
    
    // Get database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Detect schema variants
    $productsPk = schema_products_pk();
    $cartPk = schema_cart_pk();
    $cartHasPrice = schema_cart_has_price();
    
    // Fetch product with image (primary attempt enforcing active status)
    $query = "SELECT p.{$productsPk} AS product_id, p.name, p.price, p.stock_quantity, p.status,
                     COALESCE(pi.image_url, '') AS product_image
              FROM products p
              LEFT JOIN product_images pi ON p.{$productsPk} = pi.product_id AND pi.is_primary = 1
              WHERE p.{$productsPk} = ? AND p.status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fallback: if not found try without status filter (could be inactive / debugging) but do NOT allow adding if inactive
    if (!$product) {
        $fallbackStmt = $conn->prepare("SELECT {$productsPk} AS product_id, name, price, stock_quantity, status FROM products WHERE {$productsPk} = ? LIMIT 1");
        $fallbackStmt->execute([$product_id]);
        $productFallback = $fallbackStmt->fetch(PDO::FETCH_ASSOC);
        if ($productFallback) {
            ApiResponse::error('Product not available (inactive)', 404, [
                'product_id'=>$product_id,
                'status'=>$productFallback['status'],
                'reason'=>'inactive'
            ]);
            return;
        }
        ApiResponse::error('Product not found', 404, [
            'product_id'=>$product_id,
            'reason'=>'not_found',
            'hint'=>'Ensure product exists in products table and is active; UI demo data may not be seeded into DB.'
        ]);
        return;
    }
    
    // Explicit out of stock check
    if ($product['stock_quantity'] <= 0) { ApiResponse::error('Product is out of stock', 400, ['reason'=>'out_of_stock','stock'=>$product['stock_quantity']]); return; }
    if ($product['stock_quantity'] < $quantity) { ApiResponse::error('Insufficient stock available', 400, ['reason'=>'insufficient_stock','stock'=>$product['stock_quantity']]); return; }
    
    // Check if item already exists in cart
    $query = "SELECT {$cartPk} AS cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing cart item
        $new_quantity = $existing['quantity'] + $quantity;
        $query = "UPDATE cart SET quantity = ? WHERE {$cartPk} = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$new_quantity, $existing['cart_id']]);
        $finalQuantity = $new_quantity;
    } else {
        // Add new cart item (handle optional price column)
        if ($cartHasPrice) {
            $query = "INSERT INTO cart (user_id, product_id, quantity, price, created_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->execute([$user_id, $product_id, $quantity, $product['price']]);
        } else {
            $query = "INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->execute([$user_id, $product_id, $quantity]);
        }
        $finalQuantity = $quantity;
    }
    
    // Calculate current cart total for response convenience
    $totalQuery = "SELECT SUM(c.quantity * p.price) as total FROM cart c JOIN products p ON c.product_id = p.{$productsPk} WHERE c.user_id = ?";
    $totalStmt = $conn->prepare($totalQuery);
    $totalStmt->execute([$user_id]);
    $total = (float) ($totalStmt->fetchColumn() ?? 0);

    // Determine image path for response
    $image_url = '/images/default-product.jpg';
    $candidate = $product['product_image'] ?? '';
    if ($candidate) {
        if (preg_match('#^https?://#', $candidate)) {
            $image_url = $candidate; // external
        } elseif (preg_match('#^/images/#', $candidate)) {
            $image_url = $candidate; // already has /images/ prefix
        } elseif (file_exists(__DIR__ . '/uploads/' . $candidate)) {
            $image_url = '/uploads/' . $candidate;
        } elseif (file_exists(__DIR__ . '/images/' . $candidate)) {
            $image_url = '/images/' . $candidate;
        }
    }
    // Fallback to slug-based image
    if ($image_url === '/images/default-product.jpg' && !empty($product['slug'])) {
        $slug_candidate = $product['slug'] . '.jpg';
        if (file_exists(__DIR__ . '/images/' . $slug_candidate)) {
            $image_url = '/images/' . $slug_candidate;
        }
    }

    ApiResponse::success([
        'message' => 'Product added to cart successfully',
        'total' => $total,
        'item' => [
            'product_id' => $product['product_id'],
            'name' => $product['name'],
            'price' => (float)$product['price'],
            'quantity' => $finalQuantity,
            'image' => $image_url
        ],
        'is_logged_in' => true,
        'reason' => 'added'
    ]);
    
} catch (Exception $e) {
    error_log("Add to cart API error: " . $e->getMessage());
    ApiResponse::error('An error occurred while adding to cart', 500, [ 'exception' => $e->getMessage() ]);
}
?>

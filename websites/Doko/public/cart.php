<?php
session_start();
// Shopping Cart Page
require_once '../functions/loadTemplate.php';
echo loadTemplate('cart.html.php', '');
// src/cart.php
require_once '../config/database.php';
require_once '../config/auth.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

// Handle cart operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $product_id = (int)$_POST['product_id'];
                $quantity = (int)$_POST['quantity'];
                
                // Check if item already in cart
                $query = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
                $stmt = $db->prepare($query);
                $stmt->bind_param('ii', $_SESSION['user_id'], $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($existing = $result->fetch_assoc()) {
                    // Update quantity
                    $new_quantity = $existing['quantity'] + $quantity;
                    $query = "UPDATE cart SET quantity = ? WHERE id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->bind_param('ii', $new_quantity, $existing['id']);
                    $stmt->execute();
                } else {
                    // Add new item
                    $query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
                    $stmt = $db->prepare($query);
                    $stmt->bind_param('iii', $_SESSION['user_id'], $product_id, $quantity);
                    $stmt->execute();
                }
                break;
                
            case 'update':
                $cart_id = (int)$_POST['cart_id'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity > 0) {
                    $query = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->bind_param('iii', $quantity, $cart_id, $_SESSION['user_id']);
                    $stmt->execute();
                } else {
                    $query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->bind_param('ii', $cart_id, $_SESSION['user_id']);
                    $stmt->execute();
                }
                break;
                
            case 'remove':
                $cart_id = (int)$_POST['cart_id'];
                $query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
                $stmt = $db->prepare($query);
                $stmt->bind_param('ii', $cart_id, $_SESSION['user_id']);
                $stmt->execute();
                break;
        }
    }
    
    header('Location: cart.php');
    exit();
}

// Get cart items
$query = "SELECT c.*, p.name, p.price, p.stock_quantity 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$pageTitle = 'Shopping Cart - Graduation Grocery Store';
include '../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container">
            <h1 class="section-title">Your Shopping Cart</h1>
            
            <?php if (empty($cart_items)): ?>
                <div style="text-align: center; padding: 3rem;">
                    <i class="fas fa-shopping-cart" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <h3>Your cart is empty</h3>
                    <p>Browse products and add items to your cart.</p>
                    <a href="products.php" class="btn btn-primary">Shop Now</a>
                </div>
            <?php else: ?>
                <div class="cart-list">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <h3 class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div class="cart-item-price">Rs. <?php echo number_format($item['price'], 2); ?></div>
                                <p>Stock: <?php echo $item['stock_quantity']; ?> items</p>
                            </div>
                            <form method="POST" style="display: flex; align-items: center; gap: 1rem;">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn" data-action="decrease" data-product-id="<?php echo $item['product_id']; ?>">-</button>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input" data-product-id="<?php echo $item['product_id']; ?>">
                                    <button type="button" class="quantity-btn" data-action="increase" data-product-id="<?php echo $item['product_id']; ?>">+</button>
                                </div>
                                <button type="submit" name="action" value="update" class="btn btn-primary">Update</button>
                                <button type="submit" name="action" value="remove" class="btn" style="background-color: #dc3545; color: white;">Remove</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="text-align: right; margin-top: 2rem;">
                    <h3>Total: <span id="cartTotal">Rs. <?php echo number_format($total, 2); ?></span></h3>
                    <a href="checkout.php" class="btn btn-primary" style="margin-top: 1rem;">Proceed to Checkout</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

<?php
session_start();
// src/checkout.php
require_once '../config/database.php';
require_once '../config/auth.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

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

$error = '';
$success = '';

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if (empty($address) || empty($phone)) {
        $error = 'Please provide delivery address and phone number.';
    } elseif (empty($cart_items)) {
        $error = 'Your cart is empty.';
    } else {
        // Insert order
        $query = "INSERT INTO orders (user_id, address, phone, total, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($query);
        $stmt->bind_param('issd', $_SESSION['user_id'], $address, $phone, $total);
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            // Insert order items
            foreach ($cart_items as $item) {
                $query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt_item = $db->prepare($query);
                $stmt_item->bind_param('iiid', $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt_item->execute();
            }
            // Clear cart
            $query = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $success = 'Order placed successfully!';
        } else {
            $error = 'Failed to place order. Please try again.';
        }
    }
}

$pageTitle = 'Checkout - Graduation Grocery Store';
include '../includes/header.php';
?>

<main>
    <section class="section">
        <div class="container">
            <h1 class="section-title">Checkout</h1>
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (empty($success)): ?>
            <form method="POST" class="form-container" style="max-width: 600px; margin: 0 auto;">
                <h2 style="text-align: center; margin-bottom: 2rem; color: #333;">Delivery Details</h2>
                <div class="form-group">
                    <label for="address">Delivery Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                </div>
                <h2 style="text-align: center; margin: 2rem 0 1rem; color: #333;">Order Summary</h2>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
                    <?php if (empty($cart_items)): ?>
                        <p>Your cart is empty.</p>
                    <?php else: ?>
                        <ul style="list-style: none; padding: 0;">
                            <?php foreach ($cart_items as $item): ?>
                                <li style="margin-bottom: 1rem;">
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong> x <?php echo $item['quantity']; ?>
                                    <span style="float: right;">Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <hr>
                        <p style="text-align: right; font-size: 1.2rem;"><strong>Total: Rs. <?php echo number_format($total, 2); ?></strong></p>
                    <?php endif; ?>
                </div>
                <button type="submit" name="checkout" class="btn btn-primary" style="width: 100%;">Place Order</button>
            </form>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

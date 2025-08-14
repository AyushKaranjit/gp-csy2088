<?php
/**
 * Manager Edit Product
 * DOKO Grocery E-commerce Manager Panel
 */

require_once '../../../config/database.php';
require_once '../../../src/Controllers/AuthController.php';

// Check authentication
$auth = new AuthController();
if (!$auth->hasManagerAccess()) {
    header('Location: ../../login.php?error=unauthorized');
    exit;
}

$currentUser = $auth->getCurrentUser();
$page_title = 'Edit Product | DOKO Manager';
$current_page = 'products';
$show_breadcrumb = true;

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: index.php?error=invalid_product');
    exit;
}

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    // Get product details
    $productQuery = "SELECT p.*, c.name as category_name FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.category_id 
                     WHERE p.product_id = ?";
    $stmt = $db->prepare($productQuery);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: index.php?error=product_not_found');
        exit;
    }
    
    $breadcrumb_items = [
        ['title' => 'Products', 'url' => '../products/'],
        ['title' => 'Edit: ' . $product['name']]
    ];
    
} catch (Exception $e) {
    error_log("Error fetching product: " . $e->getMessage());
    header('Location: index.php?error=database_error');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['name', 'price', 'category_id', 'stock_quantity'];
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        if (empty($errors)) {
            // Update product
            $updateQuery = "UPDATE products SET 
                name = ?, 
                description = ?, 
                price = ?, 
                category_id = ?, 
                stock_quantity = ?, 
                sku = ?, 
                weight = ?, 
                status = ?,
                updated_at = NOW()
                WHERE product_id = ?";
            
            $stmt = $db->prepare($updateQuery);
            $result = $stmt->execute([
                $_POST['name'],
                $_POST['description'] ?? '',
                (float)$_POST['price'],
                (int)$_POST['category_id'],
                (int)$_POST['stock_quantity'],
                $_POST['sku'] ?? '',
                $_POST['weight'] ?? '',
                $_POST['status'] ?? 'active',
                $product_id
            ]);
            
            if ($result) {
                $success_message = "Product updated successfully!";
                
                // Refresh product data
                $stmt = $db->prepare($productQuery);
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $errors[] = "Failed to update product. Please try again.";
            }
        }
        
    } catch (Exception $e) {
        error_log("Error updating product: " . $e->getMessage());
        $errors[] = "Database error occurred. Please try again.";
    }
}

// Get categories for dropdown
try {
    $categoriesQuery = "SELECT id, category_name FROM categories WHERE status = 'active' ORDER BY category_name";
    $stmt = $db->query($categoriesQuery);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}

include_once '../shared/header.php';
?>

<div class="manager-content">
    <div class="page-header">
        <h1><i class="fas fa-edit"></i> Edit Product</h1>
        <div class="page-actions">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" class="manager-form">
            <div class="form-sections">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name" class="required">Product Name</label>
                            <input type="text" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" 
                                   required maxlength="255">
                        </div>
                        
                        <div class="form-group">
                            <label for="sku">SKU</label>
                            <input type="text" id="sku" name="sku" 
                                   value="<?php echo htmlspecialchars($product['sku'] ?? ''); ?>" 
                                   maxlength="100">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Pricing & Category -->
                <div class="form-section">
                    <h3><i class="fas fa-tag"></i> Pricing & Category</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price" class="required">Price (NPR)</label>
                            <input type="number" id="price" name="price" 
                                   value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" 
                                   step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id" class="required">Category</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Inventory & Details -->
                <div class="form-section">
                    <h3><i class="fas fa-boxes"></i> Inventory & Details</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="stock_quantity" class="required">Stock Quantity</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" 
                                   value="<?php echo htmlspecialchars($product['stock_quantity'] ?? ''); ?>" 
                                   min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="brand">Brand</label>
                            <input type="text" id="brand" name="brand" 
                                   value="<?php echo htmlspecialchars($product['brand'] ?? ''); ?>" 
                                   maxlength="100">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="weight">Weight</label>
                            <input type="text" id="weight" name="weight" 
                                   value="<?php echo htmlspecialchars($product['weight'] ?? ''); ?>" 
                                   placeholder="e.g. 500g, 1kg" maxlength="50">
                        </div>
                        
                        <div class="form-group">
                            <label for="dimensions">Dimensions</label>
                            <input type="text" id="dimensions" name="dimensions" 
                                   value="<?php echo htmlspecialchars($product['dimensions'] ?? ''); ?>" 
                                   placeholder="e.g. 10x5x3 cm" maxlength="100">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="active" <?php echo ($product['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($product['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                <option value="discontinued" <?php echo ($product['status'] === 'discontinued') ? 'selected' : ''; ?>>Discontinued</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Product
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.manager-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e0e0e0;
}

.page-header h1 {
    margin: 0;
    color: #333;
    font-size: 1.8rem;
}

.page-header h1 i {
    color: #007bff;
    margin-right: 0.5rem;
}

.page-actions .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border: 1px solid transparent;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.alert ul {
    margin: 0.5rem 0 0 1rem;
    padding: 0;
}

.form-container {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-sections {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.form-section {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 1.5rem;
}

.form-section h3 {
    margin: 0 0 1.5rem 0;
    color: #333;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section h3 i {
    color: #007bff;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.form-group label.required::after {
    content: ' *';
    color: #dc3545;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e0e0e0;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #545b62;
}

@media (max-width: 768px) {
    .manager-content {
        padding: 1rem;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<?php include_once '../shared/footer.php'; ?>

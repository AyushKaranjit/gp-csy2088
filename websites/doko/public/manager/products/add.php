<?php
/**
 * Manager Add Product
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
$page_title = 'Add New Product | DOKO Manager';
$current_page = 'products';
$show_breadcrumb = true;
$breadcrumb_items = [
    ['title' => 'Products', 'url' => '../products/'],
    ['title' => 'Add New Product']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = Database::getInstance();
        $db = $database->getConnection();
        
        // Validate required fields
        $required_fields = ['name', 'price', 'category_id', 'stock_quantity'];
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        if (empty($errors)) {
            // Insert product
            $insertQuery = "INSERT INTO products (
                name, 
                description, 
                price, 
                category_id, 
                stock_quantity, 
                sku, 
                status, 
                created_at, 
                updated_at
            ) VALUES (
                :name, 
                :description, 
                :price, 
                :category_id, 
                :stock_quantity, 
                :sku, 
                'active', 
                NOW(), 
                NOW()
            )";
            
            $stmt = $db->prepare($insertQuery);
            $stmt->bindParam(':name', $_POST['name']);
            $stmt->bindParam(':description', $_POST['description']);
            $stmt->bindParam(':price', $_POST['price']);
            $stmt->bindParam(':category_id', $_POST['category_id']);
            $stmt->bindParam(':stock_quantity', $_POST['stock_quantity']);
            $stmt->bindParam(':sku', $_POST['sku']);
            
            if ($stmt->execute()) {
                $success_message = "Product added successfully!";
                // Clear form data
                $_POST = [];
            } else {
                $error_message = "Failed to add product. Please try again.";
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
        
    } catch (Exception $e) {
        $error_message = "Error adding product: " . $e->getMessage();
    }
}

// Get categories
try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    $categoriesQuery = "SELECT category_id, category_name FROM categories ORDER BY category_name";
    $stmt = $db->query($categoriesQuery);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $categories = [];
    error_log("Categories Error: " . $e->getMessage());
}

include_once '../shared/header.php';
?>

<div class="add-product-container">
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="form-section">
        <div class="form-header">
            <h1><i class="fas fa-plus"></i> Add New Product</h1>
            <p>Fill in the details below to add a new product to your inventory.</p>
        </div>

        <form method="POST" class="product-form">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="name">Product Name <span class="required">*</span></label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                           required 
                           placeholder="Enter product name">
                </div>

                <div class="form-group">
                    <label for="sku">SKU</label>
                    <input type="text" 
                           id="sku" 
                           name="sku" 
                           value="<?php echo htmlspecialchars($_POST['sku'] ?? ''); ?>" 
                           placeholder="Product SKU (optional)">
                </div>

                <div class="form-group">
                    <label for="category_id">Category <span class="required">*</span></label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Price <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-prefix">$</span>
                        <input type="number" 
                               id="price" 
                               name="price" 
                               value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                               step="0.01" 
                               min="0" 
                               required 
                               placeholder="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <label for="stock_quantity">Stock Quantity <span class="required">*</span></label>
                    <input type="number" 
                           id="stock_quantity" 
                           name="stock_quantity" 
                           value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? ''); ?>" 
                           min="0" 
                           required 
                           placeholder="0">
                </div>

                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" 
                              name="description" 
                              rows="4" 
                              placeholder="Enter product description..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Add Product
                </button>
                <a href="../products/" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.add-product-container {
    padding: 20px;
    max-width: 800px;
    margin: 0 auto;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.alert i {
    margin-right: 10px;
    font-size: 1.2rem;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.form-section {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-header {
    margin-bottom: 30px;
    text-align: center;
}

.form-header h1 {
    margin: 0 0 10px 0;
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-header h1 i {
    margin-right: 10px;
}

.form-header p {
    margin: 0;
    color: #666;
    font-size: 1.1rem;
}

.product-form {
    max-width: 100%;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
    font-size: 1rem;
}

.required {
    color: #dc3545;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.input-group {
    position: relative;
    display: flex;
}

.input-prefix {
    background-color: #f8f9fa;
    border: 1px solid #ddd;
    border-right: none;
    border-radius: 5px 0 0 5px;
    padding: 12px 15px;
    font-weight: 500;
    color: #666;
    display: flex;
    align-items: center;
}

.input-group input {
    border-radius: 0 5px 5px 0;
    border-left: none;
}

.input-group input:focus {
    border-left: 1px solid #007bff;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn-submit,
.btn-cancel {
    padding: 12px 30px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s;
    display: inline-flex;
    align-items: center;
    font-size: 1rem;
}

.btn-submit {
    background-color: #28a745;
    color: white;
}

.btn-submit:hover {
    background-color: #1e7e34;
}

.btn-cancel {
    background-color: #6c757d;
    color: white;
}

.btn-cancel:hover {
    background-color: #545b62;
    text-decoration: none;
    color: white;
}

.btn-submit i,
.btn-cancel i {
    margin-right: 8px;
}

/* Form validation styles */
.form-group input:invalid,
.form-group select:invalid {
    border-color: #dc3545;
}

.form-group input:valid,
.form-group select:valid {
    border-color: #28a745;
}

/* Responsive design */
@media (max-width: 768px) {
    .add-product-container {
        padding: 10px;
    }
    
    .form-section {
        padding: 20px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .form-group.full-width {
        grid-column: 1;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-submit,
    .btn-cancel {
        width: 100%;
        justify-content: center;
    }
}

/* Loading state */
.btn-submit:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}

.btn-submit:disabled i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
// Form validation and enhancement
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.product-form');
    const submitBtn = document.querySelector('.btn-submit');
    
    // Auto-generate SKU based on product name (optional)
    const productNameInput = document.getElementById('name');
    const skuInput = document.getElementById('sku');
    
    productNameInput.addEventListener('input', function() {
        if (!skuInput.value) {
            // Generate simple SKU from product name
            const sku = this.value
                .toUpperCase()
                .replace(/[^A-Z0-9]/g, '')
                .substring(0, 10);
            if (sku) {
                skuInput.value = sku + Math.floor(Math.random() * 1000);
            }
        }
    });
    
    // Form submission loading state
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner"></i> Adding Product...';
    });
    
    // Real-time price formatting
    const priceInput = document.getElementById('price');
    priceInput.addEventListener('input', function() {
        let value = this.value;
        if (value && !isNaN(value)) {
            this.style.color = '#28a745';
        } else {
            this.style.color = '#dc3545';
        }
    });
});
</script>

<?php include_once '../shared/footer.php'; ?>

<?php
/**
 * Admin Products Management API
 * Handles all product-related admin operations
 */

require_once '../../../config/database.php';
require_once '../../../src/Controllers/AuthController.php';

// Check authentication
$auth = new AuthController();
if (!$auth->isAdmin()) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['api'])) {
        // Redirect to login for web requests
        header('Location: ../../login.php');
        exit;
    } else {
        // Return JSON error for API requests
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized access']);
        exit;
    }
}

// Handle API requests
if (isset($_GET['api']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
    handleApiRequest();
    exit;
}

// Handle web page request
$currentUser = $auth->getCurrentUser();
$page_title = 'Product Management | Admin';
$current_page = 'admin';

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    // Get products with category information
    $query = "
        SELECT p.*, c.name as category_name, pi.image_url AS primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        ORDER BY p.created_at DESC
    ";
    $stmt = $db->query($query);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get categories for dropdown
    $categoryQuery = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
    $stmt = $db->query($categoryQuery);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = "Error loading products: " . $e->getMessage();
    $products = [];
    $categories = [];
}

include '../../../template/admin-header.php';
?>

<!-- Immediate visibility fix -->
<script>
document.documentElement.className += ' admin-ready';
if (document.body) document.body.className += ' admin-ready';
</script>

<style>
/* Prevent flash of unstyled content */
body:not(.admin-ready) .products-container {
    opacity: 0;
    transition: opacity 0.3s ease;
}

body.admin-ready .products-container {
    opacity: 1;
}

/* Product Management Specific Styles */
.products-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.products-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.products-header h1 {
    margin: 0;
    font-size: 2.5rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.products-header p {
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.btn-add-product {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 1rem 2rem;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 12px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.btn-add-product:hover {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.products-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    max-width: 400px;
}

.search-box input {
    width: 100%;
    padding: 0.875rem 1.25rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.search-box input:focus {
    border-color: #16a34a;
    outline: none;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.filter-select {
    padding: 0.875rem 1.25rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.filter-select:focus {
    border-color: #16a34a;
    outline: none;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
}

.product-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid #f1f5f9;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.product-image {
    width: 100%;
    height: 220px;
    object-fit: cover;
    background: #f8fafc;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.product-info {
    padding: 1.5rem;
}

.product-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.5rem 0;
    line-height: 1.3;
}

.product-category {
    color: #64748b;
    font-size: 0.875rem;
    margin-bottom: 1rem;
    font-weight: 500;
}

.product-price {
    font-size: 1.5rem;
    font-weight: 800;
    color: #16a34a;
    margin-bottom: 1rem;
}

.product-stock {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.stock-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.stock-in-stock { 
    background: #d1fae5; 
    color: #065f46; 
    border: 1px solid #34d399;
}
.stock-low-stock { 
    background: #fef3c7; 
    color: #92400e; 
    border: 1px solid #fcd34d;
}
.stock-out-of-stock { 
    background: #fee2e2; 
    color: #991b1b; 
    border: 1px solid #fca5a5;
}

.product-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-action {
    flex: 1;
    padding: 0.75rem;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-edit {
    background: #3b82f6;
    color: white;
}

.btn-edit:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.btn-delete {
    background: #ef4444;
    color: white;
}

.btn-delete:hover {
    background: #dc2626;
    transform: translateY(-1px);
}

.btn-toggle-status {
    background: #f59e0b;
    color: white;
}

.btn-toggle-status:hover {
    background: #d97706;
    transform: translateY(-1px);
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f5f9;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #64748b;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.modal-close:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.75rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #16a34a;
    outline: none;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
    font-family: inherit;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 2px solid #f1f5f9;
}

.btn-cancel, .btn-save {
    padding: 0.875rem 1.75rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.btn-cancel {
    background: #f1f5f9;
    color: #64748b;
}

.btn-cancel:hover {
    background: #e2e8f0;
    color: #475569;
}

.btn-save {
    background: #16a34a;
    color: white;
}

.btn-save:hover {
    background: #15803d;
    transform: translateY(-1px);
}

/* Alert Styles */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #34d399;
}

.alert-danger {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

/* Responsive Design */
@media (max-width: 768px) {
    .products-header {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
        padding: 2rem 1.5rem;
    }
    
    .products-header h1 {
        font-size: 2rem;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .products-actions {
        flex-direction: column;
    }
    
    .search-box {
        max-width: none;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .product-actions {
        flex-wrap: wrap;
    }
    
    .btn-action {
        min-width: 120px;
    }
}

@media (max-width: 480px) {
    .products-container {
        padding: 1rem 0.5rem;
    }
    
    .modal-content {
        padding: 1.5rem;
        width: 95%;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-cancel, .btn-save {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="products-container">
    <!-- Products Header -->
    <div class="products-header">
        <div>
            <h1><i class="fas fa-box"></i> Product Management</h1>
            <p>Manage your product catalog</p>
        </div>
        <a href="#" class="btn-add-product" onclick="openAddProductModal()">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Products Actions -->
    <div class="products-actions">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search products..." onkeyup="filterProducts()">
        </div>
        <select class="filter-select" id="categoryFilter" onchange="filterProducts()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select class="filter-select" id="statusFilter" onchange="filterProducts()">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <!-- Products Grid -->
    <div class="products-grid" id="productsGrid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card" data-category="<?php echo $product['category_id']; ?>" data-status="<?php echo $product['status']; ?>">
                    <?php
                        $imgFile = !empty($product['primary_image']) ? $product['primary_image'] : ($product['image_url'] ?? ''); // fallback legacy
                        if (!empty($imgFile) && preg_match('#^https?://#i', $imgFile)) {
                            $imgPath = $imgFile; // absolute external
                        } elseif (!empty($imgFile)) {
                            $imgPath = '../../uploads/' . $imgFile;
                        } else {
                            $imgPath = '../../uploads/default-product.jpg';
                        }
                    ?>
                    <img src="<?php echo htmlspecialchars($imgPath); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image"
                         onerror="this.src='../../uploads/default-product.jpg'">
                    
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
                        <div class="product-price">NPR <?php echo number_format($product['price'], 0); ?></div>
                        
                        <div class="product-stock">
                            <span class="stock-badge <?php 
                                if ($product['stock_quantity'] <= 0) echo 'stock-out-of-stock';
                                elseif ($product['stock_quantity'] <= 10) echo 'stock-low-stock';
                                else echo 'stock-in-stock';
                            ?>">
                                <?php 
                                    if ($product['stock_quantity'] <= 0) echo 'Out of Stock';
                                    elseif ($product['stock_quantity'] <= 10) echo 'Low Stock (' . $product['stock_quantity'] . ')';
                                    else echo 'In Stock (' . $product['stock_quantity'] . ')';
                                ?>
                            </span>
                        </div>
                        
                        <div class="product-actions">
                            <button class="btn-action btn-edit" onclick="editProduct(<?php echo $product['product_id']; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-action btn-toggle-status" onclick="toggleProductStatus(<?php echo $product['product_id']; ?>, '<?php echo $product['status']; ?>')">
                                <i class="fas fa-toggle-<?php echo $product['status'] === 'active' ? 'on' : 'off'; ?>"></i>
                                <?php echo $product['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteProduct(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-box-open"></i>
                <h3>No Products Found</h3>
                <p>Start by adding your first product to the catalog.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal" id="productModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Product</h3>
            <button class="modal-close" onclick="closeProductModal()">&times;</button>
        </div>
        
        <form id="productForm" onsubmit="saveProduct(event)">
            <input type="hidden" id="productId" name="product_id">
            
            <div class="form-group">
                <label for="productName">Product Name *</label>
                <input type="text" id="productName" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="productCategory">Category</label>
                <select id="productCategory" name="category_id">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="productDescription">Description</label>
                <textarea id="productDescription" name="description" placeholder="Product description..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="productPrice">Price (NPR) *</label>
                <input type="number" id="productPrice" name="price" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="productStock">Stock Quantity *</label>
                <input type="number" id="productStock" name="stock_quantity" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="productImage">Product Image</label>
                <input type="file" id="productImage" name="image" accept="image/*">
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeProductModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
let allProducts = <?php echo json_encode($products); ?>;

console.log('All products loaded:', allProducts);

function filterProducts() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    console.log('Filtering products:', { searchTerm, categoryFilter, statusFilter });
    
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        const productName = card.querySelector('.product-name').textContent.toLowerCase();
        const productCategory = card.dataset.category;
        const productStatus = card.dataset.status;
        
        const matchesSearch = productName.includes(searchTerm);
        const matchesCategory = !categoryFilter || productCategory === categoryFilter;
        const matchesStatus = !statusFilter || productStatus === statusFilter;
        
        if (matchesSearch && matchesCategory && matchesStatus) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function openAddProductModal() {
    document.getElementById('modalTitle').textContent = 'Add Product';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('productModal').classList.add('active');
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('active');
}

function editProduct(productId) {
    console.log('Editing product with ID:', productId);
    console.log('Available products:', allProducts);
    
    const product = allProducts.find(p => p.product_id == productId);
    if (!product) {
        console.error('Product not found with ID:', productId);
        alert('Product not found! Available products: ' + allProducts.length);
        return;
    }
    
    console.log('Found product:', product);
    
    try {
        // Set modal title
        document.getElementById('modalTitle').textContent = 'Edit Product';
        
        // Populate form fields with error checking
        const elements = {
            'productId': product.product_id,
            'productName': product.name || '',
            'productCategory': product.category_id || '',
            'productDescription': product.description || '',
            'productPrice': product.price || '',
            'productStock': product.stock_quantity || ''
        };
        
        for (const [elementId, value] of Object.entries(elements)) {
            const element = document.getElementById(elementId);
            if (element) {
                element.value = value;
                console.log(`Set ${elementId} to:`, value);
            } else {
                console.error(`Element ${elementId} not found`);
            }
        }
        
        // Show modal
        const modal = document.getElementById('productModal');
        if (modal) {
            modal.classList.add('active');
            console.log('Modal should now be visible');
        } else {
            console.error('Modal element not found');
        }
        
    } catch (error) {
        console.error('Error in editProduct:', error);
        alert('Error opening edit form: ' + error.message);
    }
}

function saveProduct(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('productForm'));
    formData.append('action', document.getElementById('productId').value ? 'update' : 'add');
    formData.append('api', '1');
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product saved successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error saving product: ' + error.message);
    });
}

function toggleProductStatus(productId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    
    if (!confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this product?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('product_id', productId);
    formData.append('status', newStatus);
    formData.append('api', '1');
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function deleteProduct(productId, productName) {
    if (!confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('product_id', productId);
    formData.append('api', '1');
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

// Close modal when clicking outside
document.getElementById('productModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeProductModal();
    }
});
</script>

<?php
function handleApiRequest() {
    header('Content-Type: application/json');
    
    try {
        $database = Database::getInstance();
        $db = $database->getConnection();
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'add':
                handleAddProduct($db);
                break;
                
            case 'update':
                handleUpdateProduct($db);
                break;
                
            case 'delete':
                handleDeleteProduct($db);
                break;
                
            case 'toggle_status':
                handleToggleStatus($db);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleAddProduct($db) {
    $name = $_POST['name'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock_quantity = $_POST['stock_quantity'] ?? 0;

    if (empty($name) || $price <= 0) {
        throw new Exception('Name and valid price are required');
    }

    // Basic required fields for enhanced schema (provide placeholders where necessary)
    $sku = 'SKU-' . strtoupper(uniqid());
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name))) . '-' . substr(md5(uniqid()), 0, 6);

    // Insert into products WITHOUT legacy image_url column (not present in current schema)
    $query = "INSERT INTO products (sku, name, slug, description, price, category_id, stock_quantity, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
    $stmt = $db->prepare($query);
    $stmt->execute([$sku, $name, $slug, $description, $price, $category_id ?: null, $stock_quantity]);
    $product_id = $db->lastInsertId();

    // Handle primary image upload into product_images table if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_filename = handleImageUpload($_FILES['image']);
        $imgStmt = $db->prepare("INSERT INTO product_images (product_id, image_url, is_primary, sort_order) VALUES (?, ?, 1, 0)");
        $imgStmt->execute([$product_id, $image_filename]);
    }

    echo json_encode(['success' => true, 'message' => 'Product added successfully', 'product_id' => $product_id]);
}

function handleUpdateProduct($db) {
    $product_id = $_POST['product_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock_quantity = $_POST['stock_quantity'] ?? 0;

    if (!$product_id || empty($name) || $price <= 0) {
        throw new Exception('Product ID, name and valid price are required');
    }

    $query = "UPDATE products SET name=?, category_id=?, description=?, price=?, stock_quantity=?, updated_at=NOW() WHERE product_id=?";
    $params = [$name, $category_id ?: null, $description, $price, $stock_quantity, $product_id];
    $stmt = $db->prepare($query);
    $stmt->execute($params);

    // If a new image uploaded, insert into product_images and set it primary, demote previous primary
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_filename = handleImageUpload($_FILES['image']);
        // Demote existing primary
        $db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ? AND is_primary = 1")->execute([$product_id]);
        // Insert new
        $db->prepare("INSERT INTO product_images (product_id, image_url, is_primary, sort_order) VALUES (?, ?, 1, 0)")
           ->execute([$product_id, $image_filename]);
    }

    echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
}

function handleDeleteProduct($db) {
    $product_id = $_POST['product_id'] ?? 0;
    
    if (!$product_id) {
        throw new Exception('Product ID is required');
    }
    
    $query = "DELETE FROM products WHERE product_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$product_id]);
    
    echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
}

function handleToggleStatus($db) {
    $product_id = $_POST['product_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if (!$product_id || !in_array($status, ['active', 'inactive'])) {
        throw new Exception('Valid product ID and status are required');
    }
    
    $query = "UPDATE products SET status = ?, updated_at = NOW() WHERE product_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$status, $product_id]);
    
    echo json_encode(['success' => true, 'message' => 'Product status updated successfully']);
}

function handleImageUpload($file) {
    $uploadDir = '../../uploads/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid image type. Only JPEG, PNG, GIF, and WebP are allowed.');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('Image size too large. Maximum 5MB allowed.');
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'product_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to upload image');
    }
    
    return $filename;
}

// Prevent flash of unstyled content - Initialize early
echo '<script>
// Set admin-ready class immediately to prevent flickering
document.documentElement.className += " admin-ready";
if (document.body) {
    document.body.className += " admin-ready";
}
document.addEventListener("DOMContentLoaded", function() {
    document.body.classList.add("admin-ready");
    // Ensure visibility
    document.body.style.visibility = "visible";
});
// Also set when script loads
setTimeout(function() {
    if (document.body) {
        document.body.classList.add("admin-ready");
        document.body.style.visibility = "visible";
    }
}, 1);
</script>';

// Add Bootstrap Icons to head section
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">';

?>
<?php include '../../../template/footer.php'; ?>

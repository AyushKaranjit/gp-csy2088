<?php
/**
 * Manager Products - View and Manage Products
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
$page_title = 'Manage Products | DOKO Manager';
$current_page = 'products';
$show_breadcrumb = true;
$breadcrumb_items = [
    ['title' => 'Products']
];

// Handle filters
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$stock_filter = isset($_GET['stock']) ? $_GET['stock'] : '';

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    if ($_POST['action'] === 'update_status' && isset($_POST['product_id']) && isset($_POST['new_status'])) {
        try {
            $product_id = (int)$_POST['product_id'];
            $new_status = $_POST['new_status'];
            
            // Validate status
            $valid_statuses = ['active', 'inactive', 'out_of_stock'];
            if (in_array($new_status, $valid_statuses)) {
                $updateQuery = "UPDATE products SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE product_id = :product_id";
                $stmt = $db->prepare($updateQuery);
                $stmt->bindParam(':status', $new_status);
                $stmt->bindParam(':product_id', $product_id);
                
                if ($stmt->execute()) {
                    $success_message = "Product status updated successfully";
                } else {
                    $error_message = "Failed to update product status";
                }
            } else {
                $error_message = "Invalid product status";
            }
        } catch (Exception $e) {
            $error_message = "Error updating product: " . $e->getMessage();
        }
    }
    
    // Handle product update
    if ($_POST['action'] === 'update_product' && isset($_POST['product_id'])) {
        header('Content-Type: application/json');
        
        try {
            $product_id = (int)$_POST['product_id'];
            $name = trim($_POST['name']);
            $category_id = (int)$_POST['category_id'];
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $stock_quantity = (int)$_POST['stock_quantity'];
            $sku = trim($_POST['sku']);
            $status = $_POST['status'];
            
            // Validate inputs
            if (empty($name) || empty($sku) || $price <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please fill all required fields with valid values']);
                exit;
            }
            
            // Validate status
            $valid_statuses = ['active', 'inactive', 'out_of_stock'];
            if (!in_array($status, $valid_statuses)) {
                echo json_encode(['success' => false, 'message' => 'Invalid product status']);
                exit;
            }
            
            // Check if SKU exists for other products
            $checkSkuQuery = "SELECT product_id FROM products WHERE sku = :sku AND product_id != :product_id";
            $checkStmt = $db->prepare($checkSkuQuery);
            $checkStmt->bindParam(':sku', $sku);
            $checkStmt->bindParam(':product_id', $product_id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'SKU already exists for another product']);
                exit;
            }
            
            // Update product
            $updateQuery = "UPDATE products SET 
                           name = :name, 
                           category_id = :category_id, 
                           description = :description, 
                           price = :price, 
                           stock_quantity = :stock_quantity, 
                           sku = :sku, 
                           status = :status, 
                           updated_at = CURRENT_TIMESTAMP 
                           WHERE product_id = :product_id";
            
            $stmt = $db->prepare($updateQuery);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock_quantity', $stock_quantity);
            $stmt->bindParam(':sku', $sku);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':product_id', $product_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update product']);
            }
            exit;
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $e->getMessage()]);
            exit;
        }
    }
}

// Get products data
try {
    $database = Database::getInstance();
    $db = $database->getConnection();

    // Build query with filters
    $whereConditions = [];
    $params = [];
    
    if (!empty($category_filter)) {
        $whereConditions[] = "p.category_id = :category";
        $params[':category'] = $category_filter;
    }
    
    if (!empty($status_filter)) {
        $whereConditions[] = "p.status = :status";
        $params[':status'] = $status_filter;
    }
    
    if (!empty($search_query)) {
        $whereConditions[] = "(p.name LIKE :search OR p.sku LIKE :search OR p.description LIKE :search)";
        $params[':search'] = "%$search_query%";
    }
    
    if (!empty($stock_filter)) {
        switch ($stock_filter) {
            case 'low':
                $whereConditions[] = "p.stock_quantity < 10";
                break;
            case 'out':
                $whereConditions[] = "p.stock_quantity = 0";
                break;
            case 'in_stock':
                $whereConditions[] = "p.stock_quantity > 0";
                break;
        }
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get products with pagination
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    $productsQuery = "SELECT p.*, c.name as category_name,
                             COALESCE(pi.image_url, '/images/default-product.jpg') AS primary_image,
                             COALESCE(SUM(oi.quantity), 0) as total_sold
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.category_id 
                      LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                      LEFT JOIN order_items oi ON p.product_id = oi.product_id
                      LEFT JOIN orders o ON oi.order_id = o.order_id AND o.status IN ('completed', 'delivered')
                      $whereClause
                      GROUP BY p.product_id
                      ORDER BY p.created_at DESC 
                      LIMIT $per_page OFFSET $offset";
    
    $stmt = $db->prepare($productsQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    $countQuery = "SELECT COUNT(DISTINCT p.product_id) as total
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.category_id 
                   $whereClause";
    
    $stmt = $db->prepare($countQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_products / $per_page);

    // Get categories for filter
    $categoriesQuery = "SELECT category_id, name as category_name FROM categories ORDER BY name";
    $stmt = $db->query($categoriesQuery);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get product statistics
    $statsQuery = "SELECT 
                       COUNT(*) as total_products,
                       SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_products,
                       SUM(CASE WHEN stock_quantity < 10 THEN 1 ELSE 0 END) as low_stock,
                       SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock
                   FROM products";
    $stmt = $db->query($statsQuery);
    $product_stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Products Error: " . $e->getMessage());
    $products = [];
    $categories = [];
    $product_stats = [];
    $total_products = 0;
    $total_pages = 1;
}

include_once '../shared/header.php';
?>

<div class="products-container">
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Product Statistics -->
    <div class="product-stats">
        <h2>Product Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-content">
                    <h3><?php echo number_format($product_stats['total_products'] ?? 0); ?></h3>
                    <p>Total Products</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <h3><?php echo number_format($product_stats['active_products'] ?? 0); ?></h3>
                    <p>Active Products</p>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-content">
                    <h3><?php echo number_format($product_stats['low_stock'] ?? 0); ?></h3>
                    <p>Low Stock Items</p>
                </div>
            </div>
            <div class="stat-card error">
                <div class="stat-content">
                    <h3><?php echo number_format($product_stats['out_of_stock'] ?? 0); ?></h3>
                    <p>Out of Stock</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="filters-section">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label for="category">Category:</label>
                <select name="category" id="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>" 
                                <?php echo $category_filter == $category['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="out_of_stock" <?php echo $status_filter === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="stock">Stock Level:</label>
                <select name="stock" id="stock">
                    <option value="">All Stock Levels</option>
                    <option value="in_stock" <?php echo $stock_filter === 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                    <option value="low" <?php echo $stock_filter === 'low' ? 'selected' : ''; ?>>Low Stock (&lt; 10)</option>
                    <option value="out" <?php echo $stock_filter === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="search">Search Products:</label>
                <input type="text" name="search" id="search" placeholder="Product name, SKU, description..." value="<?php echo htmlspecialchars($search_query); ?>">
            </div>
            
            <div class="filter-group">
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i>
                    Apply Filters
                </button>
                <a href="?" class="btn-clear">
                    <i class="fas fa-times"></i>
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Products Section -->
    <div class="products-section">
        <div class="section-header">
            <h2>Products Management</h2>
            <div class="header-actions">
                <div class="results-info">
                    Showing <?php echo count($products); ?> of <?php echo number_format($total_products); ?> products
                </div>
                <a href="add.php" class="btn-add-product">
                    <i class="fas fa-plus"></i>
                    Add New Product
                </a>
            </div>
        </div>

        <div class="table-container">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Total Sold</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="product-image">
                                    <img src="<?php echo htmlspecialchars($product['primary_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-thumbnail"
                                         onerror="this.src='/images/default-product.jpg'">
                                </td>
                                <td class="product-info">
                                    <div class="product-details">
                                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 60)) . '...'; ?></p>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                <td class="price">Rs. <?php echo number_format($product['price'], 2); ?></td>
                                <td class="stock <?php echo $product['stock_quantity'] < 10 ? 'low-stock' : ''; ?>">
                                    <?php echo $product['stock_quantity']; ?>
                                    <?php if ($product['stock_quantity'] < 10): ?>
                                        <i class="fas fa-exclamation-triangle" title="Low Stock"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo number_format($product['total_sold']); ?></td>
                                <td>
                                    <form method="POST" class="status-form" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <select name="new_status" class="status-select" onchange="this.form.submit()">
                                            <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            <option value="out_of_stock" <?php echo $product['status'] === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="actions">
                                    <button type="button" class="btn-edit" title="Edit Product" onclick="editProduct(<?php echo $product['product_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="../../product-detail.php?id=<?php echo $product['product_id']; ?>" class="btn-view" title="View Product" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn-delete" title="Delete Product" onclick="deleteProduct(<?php echo $product['product_id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="no-data">
                                <?php if (!empty($search_query) || !empty($category_filter) || !empty($status_filter) || !empty($stock_filter)): ?>
                                    No products found matching your criteria.
                                <?php else: ?>
                                    No products found. <a href="add.php">Add your first product</a>.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($category_filter) ? '&category=' . $category_filter : ''; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($stock_filter) ? '&stock=' . $stock_filter : ''; ?>" class="page-btn">
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($category_filter) ? '&category=' . $category_filter : ''; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($stock_filter) ? '&stock=' . $stock_filter : ''; ?>" 
                       class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($category_filter) ? '&category=' . $category_filter : ''; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($stock_filter) ? '&stock=' . $stock_filter : ''; ?>" class="page-btn">
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal" id="productModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Edit Product</h3>
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
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="productDescription">Description</label>
                <textarea id="productDescription" name="description" placeholder="Product description..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="productPrice">Price (Rs.) *</label>
                <input type="number" id="productPrice" name="price" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="productStock">Stock Quantity *</label>
                <input type="number" id="productStock" name="stock_quantity" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="productSku">SKU</label>
                <input type="text" id="productSku" name="sku">
            </div>
            
            <div class="form-group">
                <label for="productStatus">Status</label>
                <select id="productStatus" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeProductModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Product</button>
            </div>
        </form>
    </div>
</div>

<style>
.products-container {
    padding: 20px;
    max-width: 1400px;
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

.product-stats {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.product-stats h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    border-left: 4px solid #4CAF50;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-card.warning {
    border-left-color: #FF9800;
}

.stat-card.error {
    border-left-color: #f44336;
}

.stat-content h3 {
    margin: 0;
    font-size: 2rem;
    font-weight: bold;
    color: #333;
}

.stat-content p {
    margin: 5px 0 0 0;
    color: #666;
    font-weight: 500;
}

.filters-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.filter-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.filter-group select,
.filter-group input {
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.filter-group select:focus,
.filter-group input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.btn-filter,
.btn-clear {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s;
    display: inline-flex;
    align-items: center;
    margin-right: 10px;
}

.btn-filter {
    background-color: #007bff;
    color: white;
}

.btn-filter:hover {
    background-color: #0056b3;
}

.btn-clear {
    background-color: #6c757d;
    color: white;
}

.btn-clear:hover {
    background-color: #545b62;
    text-decoration: none;
    color: white;
}

.btn-filter i,
.btn-clear i {
    margin-right: 8px;
}

.products-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h2 {
    margin: 0;
    color: #333;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 20px;
}

.results-info {
    color: #666;
    font-size: 0.9rem;
}

.btn-add-product {
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    transition: background-color 0.3s;
}

.btn-add-product:hover {
    background-color: #1e7e34;
    text-decoration: none;
    color: white;
}

.btn-add-product i {
    margin-right: 8px;
}

.table-container {
    overflow-x: auto;
    margin-bottom: 20px;
}

.products-table {
    width: 100%;
    border-collapse: collapse;
}

.products-table th,
.products-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.products-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
    position: sticky;
    top: 0;
}

.products-table tr:hover {
    background-color: #f8f9fa;
}

.product-info {
    display: flex;
    align-items: center;
    min-width: 300px;
}

.product-image {
    width: 80px;
    text-align: center;
}

.product-thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    margin-right: 15px;
}

.no-image {
    width: 60px;
    height: 60px;
    background-color: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: #ccc;
    font-size: 1.5rem;
}

.product-details h4 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 1rem;
}

.product-details p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.price {
    font-weight: 600;
    color: #28a745;
    font-size: 1.1rem;
}

.stock {
    font-weight: 600;
    color: #333;
}

.stock.low-stock {
    color: #dc3545;
}

.stock i {
    margin-left: 5px;
    color: #dc3545;
}

.status-select {
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
    cursor: pointer;
}

.status-select:focus {
    outline: none;
    border-color: #007bff;
}

.actions {
    display: flex;
    gap: 5px;
}

.btn-edit,
.btn-view,
.btn-delete {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 4px;
    text-decoration: none;
    color: white;
    border: none;
    cursor: pointer;
    transition: opacity 0.3s;
}

.btn-edit {
    background-color: #007bff;
}

.btn-view {
    background-color: #28a745;
}

.btn-delete {
    background-color: #dc3545;
}

.btn-edit:hover,
.btn-view:hover,
.btn-delete:hover {
    opacity: 0.8;
    text-decoration: none;
    color: white;
}

.no-data {
    text-align: center;
    color: #666;
    font-style: italic;
    padding: 40px;
}

.no-data a {
    color: #007bff;
    text-decoration: none;
}

.no-data a:hover {
    text-decoration: underline;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
}

.page-btn {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
    display: flex;
    align-items: center;
}

.page-btn:hover {
    background-color: #007bff;
    color: white;
    text-decoration: none;
}

.page-btn.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.page-btn i {
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .products-container {
        padding: 10px;
    }
    
    .filter-form {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .header-actions {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        width: 100%;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .products-table {
        font-size: 0.9rem;
    }
    
    .products-table th,
    .products-table td {
        padding: 8px;
    }
    
    .product-info {
        min-width: 250px;
    }
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 1;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 0;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
    transform: scale(0.7);
    transition: transform 0.3s ease;
}

.modal.active .modal-content {
    transform: scale(1);
}

.modal-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.2s;
}

.modal-close:hover {
    background-color: rgba(255,255,255,0.1);
}

.modal form {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.btn-cancel,
.btn-save {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-cancel {
    background: #f3f4f6;
    color: #374151;
}

.btn-cancel:hover {
    background: #e5e7eb;
}

.btn-save {
    background: #3b82f6;
    color: white;
}

.btn-save:hover {
    background: #2563eb;
}
</style>

<script>
let allProducts = <?php echo json_encode($products); ?>;

function editProduct(productId) {
    console.log('Editing product with ID:', productId);
    
    const product = allProducts.find(p => p.product_id == productId);
    if (!product) {
        console.error('Product not found with ID:', productId);
        alert('Product not found!');
        return;
    }
    
    console.log('Found product:', product);
    
    try {
        // Set modal title
        document.getElementById('modalTitle').textContent = 'Edit Product';
        
        // Populate form fields
        document.getElementById('productId').value = product.product_id || '';
        document.getElementById('productName').value = product.name || '';
        document.getElementById('productCategory').value = product.category_id || '';
        document.getElementById('productDescription').value = product.description || '';
        document.getElementById('productPrice').value = product.price || '';
        document.getElementById('productStock').value = product.stock_quantity || '';
        document.getElementById('productSku').value = product.sku || '';
        document.getElementById('productStatus').value = product.status || 'active';
        
        // Show modal
        const modal = document.getElementById('productModal');
        modal.classList.add('active');
        
    } catch (error) {
        console.error('Error in editProduct:', error);
        alert('Error opening edit form: ' + error.message);
    }
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('active');
}

function saveProduct(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('productForm'));
    formData.append('action', 'update_product');
    
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update product'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating product: ' + error.message);
    });
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        // Create a form to submit the delete action
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_product">
            <input type="hidden" name="product_id" value="${productId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include_once '../shared/footer.php'; ?>

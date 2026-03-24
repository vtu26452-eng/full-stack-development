<?php
session_start();
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Fetch categories for dropdown
$cat_query = "SELECT id, name FROM categories ORDER BY name";
$cat_stmt = $db->query($cat_query);
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch suppliers for dropdown
$sup_query = "SELECT id, name FROM suppliers ORDER BY name";
$sup_stmt = $db->query($sup_query);
$suppliers = $sup_stmt->fetchAll(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sku = $_POST['sku'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $supplier_id = $_POST['supplier_id'];
    $unit_price = $_POST['unit_price'];
    $selling_price = $_POST['selling_price'];
    $reorder_level = $_POST['reorder_level'];
    $current_stock = $_POST['current_stock'];

    $query = "INSERT INTO products (sku, name, description, category_id, supplier_id, 
              unit_price, selling_price, reorder_level, current_stock) 
              VALUES (:sku, :name, :description, :category_id, :supplier_id, 
              :unit_price, :selling_price, :reorder_level, :current_stock)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':sku', $sku);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':supplier_id', $supplier_id);
    $stmt->bindParam(':unit_price', $unit_price);
    $stmt->bindParam(':selling_price', $selling_price);
    $stmt->bindParam(':reorder_level', $reorder_level);
    $stmt->bindParam(':current_stock', $current_stock);

    if($stmt->execute()) {
        // Record initial stock movement
        $product_id = $db->lastInsertId();
        $movement_query = "INSERT INTO stock_movements (product_id, quantity, type, reference_no, notes) 
                          VALUES (:product_id, :quantity, 'IN', 'INITIAL', 'Initial stock')";
        $movement_stmt = $db->prepare($movement_query);
        $movement_stmt->bindParam(':product_id', $product_id);
        $movement_stmt->bindParam(':quantity', $current_stock);
        $movement_stmt->execute();

        header("Location: products.php?msg=added");
        exit();
    }
}

include '../includes/header.php';
?>

<div class="form-container">
    <h1 class="page-title">Add New Product</h1>
    
    <form method="POST" class="product-form" id="productForm">
        <div class="form-grid">
            <div class="form-group">
                <label for="sku">SKU *</label>
                <input type="text" id="sku" name="sku" required 
                       pattern="[A-Za-z0-9-]+" 
                       title="Only letters, numbers, and hyphens allowed">
            </div>

            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group full-width">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="supplier_id">Supplier *</label>
                <select id="supplier_id" name="supplier_id" required>
                    <option value="">Select Supplier</option>
                    <?php foreach($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['id']; ?>">
                            <?php echo htmlspecialchars($supplier['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="unit_price">Unit Price ($) *</label>
                <input type="number" id="unit_price" name="unit_price" 
                       step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="selling_price">Selling Price ($) *</label>
                <input type="number" id="selling_price" name="selling_price" 
                       step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="reorder_level">Reorder Level *</label>
                <input type="number" id="reorder_level" name="reorder_level" 
                       min="0" value="10" required>
            </div>

            <div class="form-group">
                <label for="current_stock">Initial Stock *</label>
                <input type="number" id="current_stock" name="current_stock" 
                       min="0" value="0" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Product</button>
            <a href="products.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
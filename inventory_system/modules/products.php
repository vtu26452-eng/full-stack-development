<?php
session_start();
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Handle delete request
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM products WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    if($stmt->execute()) {
        header("Location: products.php?msg=deleted");
        exit();
    }
}

// Fetch all products
$query = "SELECT p.*, c.name as category_name, s.name as supplier_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN suppliers s ON p.supplier_id = s.id 
          ORDER BY p.id DESC";
$stmt = $db->query($query);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="products-management">
    <div class="header-actions">
        <h1 class="page-title">Products Management</h1>
        <a href="add_product.php" class="btn btn-primary">➕ Add New Product</a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <?php 
                if($_GET['msg'] == 'added') echo "Product added successfully!";
                if($_GET['msg'] == 'updated') echo "Product updated successfully!";
                if($_GET['msg'] == 'deleted') echo "Product deleted successfully!";
            ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="data-table" id="productsTable">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Unit Price</th>
                    <th>Selling Price</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['sku']); ?></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($product['supplier_name']); ?></td>
                    <td>$<?php echo number_format($product['unit_price'], 2); ?></td>
                    <td>$<?php echo number_format($product['selling_price'], 2); ?></td>
                    <td><?php echo $product['current_stock']; ?></td>
                    <td>
                        <?php if($product['current_stock'] <= $product['reorder_level']): ?>
                            <span class="badge badge-danger">Low Stock</span>
                        <?php else: ?>
                            <span class="badge badge-success">In Stock</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-edit">✏️</a>
                        <a href="?delete=<?php echo $product['id']; ?>" 
                           class="btn-delete" 
                           onclick="return confirm('Are you sure?')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#productsTable').DataTable({
        pageLength: 10,
        order: [[0, 'desc']]
    });
});
</script>

<?php include '../includes/footer.php'; ?>
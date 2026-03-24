<?php
session_start();
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Fetch products for dropdown
$product_query = "SELECT id, name, sku, current_stock FROM products ORDER BY name";
$product_stmt = $db->query($product_query);
$products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $reference_no = $_POST['reference_no'];
    $notes = $_POST['notes'];

    // Start transaction
    $db->beginTransaction();

    try {
        // Update product stock
        $update_query = "UPDATE products SET current_stock = current_stock + :quantity WHERE id = :product_id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':quantity', $quantity);
        $update_stmt->bindParam(':product_id', $product_id);
        $update_stmt->execute();

        // Record stock movement
        $movement_query = "INSERT INTO stock_movements (product_id, quantity, type, reference_no, notes) 
                          VALUES (:product_id, :quantity, 'IN', :reference_no, :notes)";
        $movement_stmt = $db->prepare($movement_query);
        $movement_stmt->bindParam(':product_id', $product_id);
        $movement_stmt->bindParam(':quantity', $quantity);
        $movement_stmt->bindParam(':reference_no', $reference_no);
        $movement_stmt->bindParam(':notes', $notes);
        $movement_stmt->execute();

        $db->commit();
        $success = "Stock added successfully!";
    } catch(Exception $e) {
        $db->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="form-container">
    <h1 class="page-title">Stock In</h1>
    
    <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="stock-form" id="stockInForm">
        <div class="form-grid">
            <div class="form-group full-width">
                <label for="product_id">Select Product *</label>
                <select id="product_id" name="product_id" required class="form-control">
                    <option value="">-- Choose a product --</option>
                    <?php foreach($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?> 
                            (<?php echo $product['sku']; ?>) - Current Stock: <?php echo $product['current_stock']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity *</label>
                <input type="number" id="quantity" name="quantity" 
                       min="1" required class="form-control">
            </div>

            <div class="form-group">
                <label for="reference_no">Reference Number</label>
                <input type="text" id="reference_no" name="reference_no" 
                       placeholder="PO-2024-001" class="form-control">
            </div>

            <div class="form-group full-width">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="3" class="form-control"
                          placeholder="Additional information..."></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Stock</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
            <a href="products.php" class="btn btn-info">View Products</a>
        </div>
    </form>

    <!-- Recent Stock In Movements -->
    <div class="recent-movements" style="margin-top: 40px;">
        <h2>Recent Stock In Movements</h2>
        <?php
        $recent_query = "SELECT sm.*, p.name as product_name, p.sku 
                         FROM stock_movements sm 
                         JOIN products p ON sm.product_id = p.id 
                         WHERE sm.type = 'IN' 
                         ORDER BY sm.movement_date DESC 
                         LIMIT 10";
        $recent_stmt = $db->query($recent_query);
        $recent_movements = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if(count($recent_movements) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product (SKU)</th>
                    <th>Quantity</th>
                    <th>Reference</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recent_movements as $movement): ?>
                <tr>
                    <td><?php echo date('Y-m-d H:i', strtotime($movement['movement_date'])); ?></td>
                    <td><?php echo htmlspecialchars($movement['product_name']); ?> (<?php echo $movement['sku']; ?>)</td>
                    <td class="text-success">+<?php echo $movement['quantity']; ?></td>
                    <td><?php echo htmlspecialchars($movement['reference_no']); ?></td>
                    <td><?php echo htmlspecialchars($movement['notes']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="alert alert-info">No stock in movements yet.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.text-success { color: #28a745; font-weight: bold; }
.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.btn-info {
    background: #17a2b8;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 4px;
    display: inline-block;
}
.btn-info:hover {
    background: #138496;
}
</style>

<?php include '../includes/footer.php'; ?>
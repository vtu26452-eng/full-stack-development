<?php
session_start();
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Handle Add Category
if(isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    if(!empty($name)) {
        $query = "INSERT INTO categories (name, description) VALUES (:name, :description)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        
        if($stmt->execute()) {
            $success = "Category added successfully!";
        } else {
            $error = "Error adding category.";
        }
    } else {
        $error = "Category name is required.";
    }
}

// Handle Edit Category
if(isset($_POST['edit_category'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    $query = "UPDATE categories SET name = :name, description = :description WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    
    if($stmt->execute()) {
        $success = "Category updated successfully!";
    } else {
        $error = "Error updating category.";
    }
}

// Handle Delete Category
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if category has products
    $check_query = "SELECT COUNT(*) as count FROM products WHERE category_id = :id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':id', $id);
    $check_stmt->execute();
    $product_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if($product_count == 0) {
        $query = "DELETE FROM categories WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            $success = "Category deleted successfully!";
        } else {
            $error = "Error deleting category.";
        }
    } else {
        $error = "Cannot delete category. It has $product_count product(s) associated.";
    }
}

// Get category for editing
$edit_category = null;
if(isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM categories WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all categories with product counts
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count 
          FROM categories c 
          ORDER BY c.id DESC";
$stmt = $db->query($query);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<style>
/* Categories page specific styles */
.categories-container {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-title {
    font-size: 28px;
    color: #333;
    margin: 0;
}

/* Form Styles */
.category-form {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    margin-bottom: 30px;
    border: 1px solid #f0f0f0;
}

.category-form h2 {
    font-size: 18px;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.form-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.form-group {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500;
    font-size: 14px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #f0f0f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-actions {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

.btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

/* Table Styles */
.categories-table-container {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f0f0;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.table-header h2 {
    font-size: 18px;
    color: #333;
    margin: 0;
}

.search-box {
    padding: 10px 15px;
    border: 2px solid #f0f0f0;
    border-radius: 8px;
    width: 250px;
    font-size: 14px;
}

.categories-table {
    width: 100%;
    border-collapse: collapse;
}

.categories-table th {
    text-align: left;
    padding: 15px;
    background: #f8f9fa;
    color: #555;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.categories-table td {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
    color: #666;
}

.categories-table tr:hover td {
    background: #f8f9fa;
}

.category-name {
    font-weight: 600;
    color: #333;
    font-size: 16px;
}

.category-description {
    color: #666;
    font-size: 13px;
    max-width: 300px;
}

.product-count {
    display: inline-block;
    padding: 5px 10px;
    background: #e9ecef;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    color: #495057;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-icon {
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    color: white;
    transition: all 0.3s;
}

.btn-icon.edit {
    background: #28a745;
}

.btn-icon.edit:hover {
    background: #218838;
}

.btn-icon.delete {
    background: #dc3545;
}

.btn-icon.delete:hover {
    background: #c82333;
}

/* Alert Messages */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: inherit;
    opacity: 0.5;
}

.alert-close:hover {
    opacity: 1;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #999;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    display: block;
}

.empty-state p {
    font-size: 16px;
    margin-bottom: 20px;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}

.modal-content {
    background: white;
    width: 90%;
    max-width: 500px;
    margin: 50px auto;
    border-radius: 15px;
    padding: 30px;
    position: relative;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h3 {
    font-size: 20px;
    color: #333;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
}

.modal-body {
    margin-bottom: 20px;
}

.modal-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

/* Responsive */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        margin-top: 10px;
    }
    
    .table-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .search-box {
        width: 100%;
    }
    
    .categories-table {
        display: block;
        overflow-x: auto;
    }
}
</style>

<div class="categories-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">📑 Categories Management</h1>
    </div>

    <!-- Alert Messages -->
    <?php if(isset($success)): ?>
        <div class="alert alert-success" id="successAlert">
            <span><?php echo $success; ?></span>
            <button class="alert-close" onclick="document.getElementById('successAlert').style.display='none'">×</button>
        </div>
    <?php endif; ?>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger" id="errorAlert">
            <span><?php echo $error; ?></span>
            <button class="alert-close" onclick="document.getElementById('errorAlert').style.display='none'">×</button>
        </div>
    <?php endif; ?>

    <!-- Add/Edit Category Form -->
    <div class="category-form">
        <h2><?php echo $edit_category ? '✏️ Edit Category' : '➕ Add New Category'; ?></h2>
        
        <form method="POST" action="categories.php">
            <?php if($edit_category): ?>
                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
            <?php endif; ?>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" 
                           value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>"
                           placeholder="e.g., Electronics, Furniture, Clothing" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" 
                              rows="1" 
                              placeholder="Brief description of this category..."><?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-actions">
                    <?php if($edit_category): ?>
                        <button type="submit" name="edit_category" class="btn btn-primary">Update Category</button>
                        <a href="categories.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Categories List -->
    <div class="categories-table-container">
        <div class="table-header">
            <h2>📋 All Categories</h2>
            <input type="text" id="searchInput" class="search-box" 
                   placeholder="🔍 Search categories..." onkeyup="searchCategories()">
        </div>

        <?php if(count($categories) > 0): ?>
            <div class="table-responsive">
                <table class="categories-table" id="categoriesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $category): ?>
                        <tr>
                            <td>#<?php echo $category['id']; ?></td>
                            <td>
                                <span class="category-name"><?php echo htmlspecialchars($category['name']); ?></span>
                            </td>
                            <td>
                                <span class="category-description">
                                    <?php echo htmlspecialchars($category['description'] ?: '—'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="product-count">
                                    <?php echo $category['product_count']; ?> products
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($category['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?php echo $category['id']; ?>" class="btn-icon edit" title="Edit category">✏️</a>
                                    <button onclick="confirmDelete(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', <?php echo $category['product_count']; ?>)" class="btn-icon delete" title="Delete category">🗑️</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i>📭</i>
                <p>No categories found. Add your first category above!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <div class="modal-body">
            <p id="deleteMessage"></p>
            <p id="productWarning" style="color: #dc3545; display: none;"></p>
        </div>
        <div class="modal-footer">
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            <button onclick="closeModal()" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>

<script>
// Search functionality
function searchCategories() {
    let input = document.getElementById('searchInput').value.toUpperCase();
    let table = document.getElementById('categoriesTable');
    
    if(!table) return;
    
    let rows = table.getElementsByTagName('tr');
    
    for(let i = 1; i < rows.length; i++) {
        let nameCell = rows[i].getElementsByTagName('td')[1];
        let descCell = rows[i].getElementsByTagName('td')[2];
        
        if(nameCell || descCell) {
            let nameText = nameCell ? nameCell.textContent.toUpperCase() : '';
            let descText = descCell ? descCell.textContent.toUpperCase() : '';
            
            if(nameText.indexOf(input) > -1 || descText.indexOf(input) > -1) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }
}

// Delete confirmation modal
function confirmDelete(id, name, productCount) {
    let modal = document.getElementById('deleteModal');
    let message = document.getElementById('deleteMessage');
    let warning = document.getElementById('productWarning');
    let confirmBtn = document.getElementById('confirmDeleteBtn');
    
    message.textContent = `Are you sure you want to delete category "${name}"?`;
    
    if(productCount > 0) {
        warning.style.display = 'block';
        warning.textContent = `⚠️ Warning: This category has ${productCount} product(s). You cannot delete it until all products are reassigned.`;
        confirmBtn.style.display = 'none';
    } else {
        warning.style.display = 'none';
        confirmBtn.style.display = 'inline-block';
        confirmBtn.href = `?delete=${id}`;
    }
    
    modal.style.display = 'block';
}

function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    let alerts = document.getElementsByClassName('alert');
    for(let alert of alerts) {
        alert.style.display = 'none';
    }
}, 5000);

// Close modal when clicking outside
window.onclick = function(event) {
    let modal = document.getElementById('deleteModal');
    if(event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
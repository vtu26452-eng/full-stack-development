<?php
session_start();
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Handle Add Supplier
if(isset($_POST['add_supplier'])) {
    $name = trim($_POST['name']);
    $contact_person = trim($_POST['contact_person']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if(!empty($name)) {
        $query = "INSERT INTO suppliers (name, contact_person, email, phone, address) 
                  VALUES (:name, :contact_person, :email, :phone, :address)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':contact_person', $contact_person);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        
        if($stmt->execute()) {
            $success = "Supplier added successfully!";
        } else {
            $error = "Error adding supplier.";
        }
    } else {
        $error = "Supplier name is required.";
    }
}

// Handle Edit Supplier
if(isset($_POST['edit_supplier'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $contact_person = trim($_POST['contact_person']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    $query = "UPDATE suppliers SET 
              name = :name, 
              contact_person = :contact_person, 
              email = :email, 
              phone = :phone, 
              address = :address 
              WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':contact_person', $contact_person);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    
    if($stmt->execute()) {
        $success = "Supplier updated successfully!";
    } else {
        $error = "Error updating supplier.";
    }
}

// Handle Delete Supplier
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if supplier has products
    $check_query = "SELECT COUNT(*) as count FROM products WHERE supplier_id = :id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':id', $id);
    $check_stmt->execute();
    $product_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if($product_count == 0) {
        $query = "DELETE FROM suppliers WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            $success = "Supplier deleted successfully!";
        } else {
            $error = "Error deleting supplier.";
        }
    } else {
        $error = "Cannot delete supplier. They have $product_count product(s) associated.";
    }
}

// Get supplier for editing
$edit_supplier = null;
if(isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM suppliers WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit_supplier = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all suppliers with product counts and stats
$query = "SELECT s.*, 
          COUNT(p.id) as product_count,
          SUM(p.current_stock) as total_stock,
          SUM(p.current_stock * p.unit_price) as inventory_value,
          MAX(p.created_at) as last_shipment
          FROM suppliers s
          LEFT JOIN products p ON s.id = p.supplier_id
          GROUP BY s.id
          ORDER BY s.name ASC";
$stmt = $db->query($query);
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get overall supplier statistics
$stats_query = "SELECT 
                COUNT(*) as total_suppliers,
                SUM(CASE WHEN (SELECT COUNT(*) FROM products WHERE supplier_id = suppliers.id) > 0 THEN 1 ELSE 0 END) as active_suppliers,
                (SELECT COUNT(*) FROM products) as total_products,
                (SELECT COUNT(DISTINCT supplier_id) FROM products WHERE supplier_id IS NOT NULL) as suppliers_with_products
                FROM suppliers";
$stats_stmt = $db->query($stats_query);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<style>
/* Suppliers page specific styles */
.suppliers-container {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.page-title {
    font-size: 28px;
    color: #333;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-content h3 {
    font-size: 13px;
    color: #666;
    margin-bottom: 5px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-content .number {
    font-size: 24px;
    font-weight: 700;
    color: #333;
}

.stat-content .small {
    font-size: 12px;
    color: #999;
}

/* Form Styles */
.supplier-form {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    margin-bottom: 30px;
    border: 1px solid #f0f0f0;
}

.supplier-form h2 {
    font-size: 18px;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.form-group {
    margin-bottom: 0;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500;
    font-size: 14px;
}

.form-group label i {
    color: #667eea;
    margin-right: 5px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #f0f0f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
    font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input.error {
    border-color: #dc3545;
}

.error-message {
    color: #dc3545;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
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
    display: inline-flex;
    align-items: center;
    gap: 8px;
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

/* Suppliers Grid */
.suppliers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.supplier-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    overflow: hidden;
    transition: all 0.3s;
    border: 1px solid #f0f0f0;
    position: relative;
}

.supplier-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.supplier-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    position: relative;
}

.supplier-header h3 {
    font-size: 18px;
    margin: 0 0 5px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.supplier-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255,255,255,0.2);
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    backdrop-filter: blur(5px);
}

.supplier-body {
    padding: 20px;
}

.supplier-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #666;
    font-size: 14px;
}

.info-row i {
    width: 20px;
    color: #667eea;
    font-size: 16px;
}

.info-row .label {
    font-weight: 500;
    color: #999;
    width: 80px;
}

.info-row .value {
    color: #333;
    flex: 1;
}

.supplier-stats {
    display: flex;
    justify-content: space-around;
    margin: 15px 0;
    padding: 15px 0;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #333;
}

.stat-label {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}

.supplier-footer {
    display: flex;
    gap: 10px;
    padding: 15px 20px;
    background: #f8f9fa;
    border-top: 1px solid #f0f0f0;
}

.btn-icon {
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 13px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s;
}

.btn-icon.edit {
    background: #28a745;
    color: white;
    flex: 1;
}

.btn-icon.edit:hover {
    background: #218838;
}

.btn-icon.delete {
    background: #dc3545;
    color: white;
    flex: 1;
}

.btn-icon.delete:hover {
    background: #c82333;
}

.btn-icon.view {
    background: #17a2b8;
    color: white;
    flex: 1;
}

.btn-icon.view:hover {
    background: #138496;
}

/* Alert Messages */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
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

.alert-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
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

/* Search and Filter */
.search-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.search-box {
    flex: 1;
    min-width: 250px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}

.search-box input {
    width: 100%;
    padding: 12px 15px 12px 40px;
    border: 2px solid #f0f0f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
}

.filter-buttons {
    display: flex;
    gap: 10px;
}

.filter-btn {
    padding: 10px 20px;
    border: 2px solid #f0f0f0;
    border-radius: 8px;
    background: white;
    color: #666;
    cursor: pointer;
    transition: all 0.3s;
}

.filter-btn.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.filter-btn:hover {
    border-color: #667eea;
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
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    width: 90%;
    max-width: 500px;
    margin: 50px auto;
    border-radius: 15px;
    overflow: hidden;
    animation: slideUp 0.3s;
}

@keyframes slideUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    opacity: 0.8;
}

.modal-close:hover {
    opacity: 1;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    background: #f8f9fa;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
    grid-column: 1 / -1;
}

.empty-state i {
    font-size: 60px;
    color: #ddd;
    margin-bottom: 20px;
    display: block;
}

.empty-state h3 {
    font-size: 20px;
    color: #333;
    margin-bottom: 10px;
}

.empty-state p {
    color: #999;
    margin-bottom: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .suppliers-grid {
        grid-template-columns: 1fr;
    }
    
    .search-section {
        flex-direction: column;
    }
    
    .filter-buttons {
        width: 100%;
        justify-content: stretch;
    }
    
    .filter-btn {
        flex: 1;
    }
}
</style>

<div class="suppliers-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <span>🏢</span> Suppliers Management
        </h1>
        <button class="btn btn-primary" onclick="document.getElementById('addForm').scrollIntoView({behavior: 'smooth'})">
            ➕ Add New Supplier
        </button>
    </div>

    <!-- Alert Messages -->
    <?php if(isset($success)): ?>
        <div class="alert alert-success" id="successAlert">
            <span>✅ <?php echo $success; ?></span>
            <button class="alert-close" onclick="document.getElementById('successAlert').style.display='none'">×</button>
        </div>
    <?php endif; ?>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger" id="errorAlert">
            <span>❌ <?php echo $error; ?></span>
            <button class="alert-close" onclick="document.getElementById('errorAlert').style.display='none'">×</button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🏢</div>
            <div class="stat-content">
                <h3>Total Suppliers</h3>
                <div class="number"><?php echo $stats['total_suppliers']; ?></div>
                <div class="small">Registered suppliers</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <h3>Active Suppliers</h3>
                <div class="number"><?php echo $stats['active_suppliers']; ?></div>
                <div class="small">With products</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <h3>Total Products</h3>
                <div class="number"><?php echo $stats['total_products']; ?></div>
                <div class="small">Across all suppliers</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3>Supplier Coverage</h3>
                <div class="number"><?php echo round(($stats['suppliers_with_products'] / max($stats['total_suppliers'], 1)) * 100); ?>%</div>
                <div class="small">Suppliers with products</div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Supplier Form -->
    <div id="addForm" class="supplier-form">
        <h2>
            <?php if($edit_supplier): ?>
                <span>✏️</span> Edit Supplier
            <?php else: ?>
                <span>➕</span> Add New Supplier
            <?php endif; ?>
        </h2>
        
        <form method="POST" action="suppliers.php" onsubmit="return validateForm()">
            <?php if($edit_supplier): ?>
                <input type="hidden" name="id" value="<?php echo $edit_supplier['id']; ?>">
            <?php endif; ?>
            
            <div class="form-grid">
                <div class="form-group">
                    <label><i>🏢</i> Company Name *</label>
                    <input type="text" name="name" id="name" 
                           value="<?php echo $edit_supplier ? htmlspecialchars($edit_supplier['name']) : ''; ?>" 
                           placeholder="e.g., Tech Distributors Inc." required>
                </div>
                
                <div class="form-group">
                    <label><i>👤</i> Contact Person</label>
                    <input type="text" name="contact_person" id="contact_person" 
                           value="<?php echo $edit_supplier ? htmlspecialchars($edit_supplier['contact_person']) : ''; ?>" 
                           placeholder="e.g., John Smith">
                </div>
                
                <div class="form-group">
                    <label><i>📧</i> Email Address</label>
                    <input type="email" name="email" id="email" 
                           value="<?php echo $edit_supplier ? htmlspecialchars($edit_supplier['email']) : ''; ?>" 
                           placeholder="contact@company.com">
                </div>
                
                <div class="form-group">
                    <label><i>📞</i> Phone Number</label>
                    <input type="tel" name="phone" id="phone" 
                           value="<?php echo $edit_supplier ? htmlspecialchars($edit_supplier['phone']) : ''; ?>" 
                           placeholder="+1 234 567 8900">
                </div>
                
                <div class="form-group full-width">
                    <label><i>📍</i> Address</label>
                    <textarea name="address" id="address" rows="2" 
                              placeholder="Street address, city, country"><?php echo $edit_supplier ? htmlspecialchars($edit_supplier['address']) : ''; ?></textarea>
                </div>
            </div>
            
            <div class="form-actions">
                <?php if($edit_supplier): ?>
                    <button type="submit" name="edit_supplier" class="btn btn-primary">
                        <span>✏️</span> Update Supplier
                    </button>
                    <a href="suppliers.php" class="btn btn-secondary">
                        <span>↩️</span> Cancel
                    </a>
                <?php else: ?>
                    <button type="submit" name="add_supplier" class="btn btn-primary">
                        <span>➕</span> Add Supplier
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <span>🔄</span> Reset
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Search and Filter -->
    <div class="search-section">
        <div class="search-box">
            <i>🔍</i>
            <input type="text" id="searchInput" placeholder="Search suppliers by name, contact, email..." onkeyup="filterSuppliers()">
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" onclick="filterByStatus('all')">All</button>
            <button class="filter-btn" onclick="filterByStatus('active')">Active</button>
            <button class="filter-btn" onclick="filterByStatus('inactive')">Inactive</button>
        </div>
    </div>

    <!-- Suppliers Grid -->
    <?php if(count($suppliers) > 0): ?>
        <div class="suppliers-grid" id="suppliersGrid">
            <?php foreach($suppliers as $supplier): ?>
                <?php 
                $is_active = $supplier['product_count'] > 0;
                $status_class = $is_active ? 'active' : 'inactive';
                $status_text = $is_active ? 'Active' : 'Inactive';
                ?>
                <div class="supplier-card" data-status="<?php echo $status_class; ?>" data-name="<?php echo strtolower($supplier['name']); ?>" data-contact="<?php echo strtolower($supplier['contact_person']); ?>" data-email="<?php echo strtolower($supplier['email']); ?>">
                    <div class="supplier-header">
                        <h3>
                            <span>🏢</span> 
                            <?php echo htmlspecialchars($supplier['name']); ?>
                        </h3>
                        <div class="supplier-badge <?php echo $status_class; ?>">
                            <?php echo $status_text; ?>
                        </div>
                    </div>
                    
                    <div class="supplier-body">
                        <div class="supplier-info">
                            <?php if(!empty($supplier['contact_person'])): ?>
                            <div class="info-row">
                                <i>👤</i>
                                <span class="label">Contact:</span>
                                <span class="value"><?php echo htmlspecialchars($supplier['contact_person']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($supplier['email'])): ?>
                            <div class="info-row">
                                <i>📧</i>
                                <span class="label">Email:</span>
                                <span class="value">
                                    <a href="mailto:<?php echo htmlspecialchars($supplier['email']); ?>">
                                        <?php echo htmlspecialchars($supplier['email']); ?>
                                    </a>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($supplier['phone'])): ?>
                            <div class="info-row">
                                <i>📞</i>
                                <span class="label">Phone:</span>
                                <span class="value">
                                    <a href="tel:<?php echo htmlspecialchars($supplier['phone']); ?>">
                                        <?php echo htmlspecialchars($supplier['phone']); ?>
                                    </a>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($supplier['address'])): ?>
                            <div class="info-row">
                                <i>📍</i>
                                <span class="label">Address:</span>
                                <span class="value"><?php echo htmlspecialchars($supplier['address']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="supplier-stats">
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $supplier['product_count']; ?></div>
                                <div class="stat-label">Products</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $supplier['total_stock'] ?: 0; ?></div>
                                <div class="stat-label">Total Stock</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">$<?php echo number_format($supplier['inventory_value'] ?: 0, 0); ?></div>
                                <div class="stat-label">Inventory Value</div>
                            </div>
                        </div>
                        
                        <?php if($supplier['last_shipment']): ?>
                        <div style="font-size: 12px; color: #999; text-align: center; margin-top: 10px;">
                            Last shipment: <?php echo date('d M Y', strtotime($supplier['last_shipment'])); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="supplier-footer">
                        <a href="?edit=<?php echo $supplier['id']; ?>" class="btn-icon edit">
                            <span>✏️</span> Edit
                        </a>
                        <a href="products.php?supplier=<?php echo $supplier['id']; ?>" class="btn-icon view">
                            <span>👁️</span> View Products
                        </a>
                        <button onclick="confirmDelete(<?php echo $supplier['id']; ?>, '<?php echo htmlspecialchars($supplier['name']); ?>', <?php echo $supplier['product_count']; ?>)" class="btn-icon delete">
                            <span>🗑️</span> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i>🏢</i>
            <h3>No Suppliers Found</h3>
            <p>Get started by adding your first supplier using the form above.</p>
            <button class="btn btn-primary" onclick="document.getElementById('addForm').scrollIntoView({behavior: 'smooth'})">
                ➕ Add Your First Supplier
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <div class="modal-body">
            <p id="deleteMessage" style="font-size: 16px; margin-bottom: 15px;"></p>
            <div id="productWarning" style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; display: none;">
                <strong>⚠️ Warning:</strong> <span id="warningText"></span>
            </div>
        </div>
        <div class="modal-footer">
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete Supplier</a>
            <button onclick="closeModal()" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>

<script>
// Form validation
function validateForm() {
    let name = document.getElementById('name').value.trim();
    let email = document.getElementById('email').value;
    let phone = document.getElementById('phone').value;
    let isValid = true;
    
    if(name === '') {
        alert('Supplier name is required');
        isValid = false;
    }
    
    if(email && !isValidEmail(email)) {
        alert('Please enter a valid email address');
        isValid = false;
    }
    
    return isValid;
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Search and filter functionality
function filterSuppliers() {
    let searchTerm = document.getElementById('searchInput').value.toLowerCase();
    let suppliers = document.querySelectorAll('.supplier-card');
    let activeFilter = document.querySelector('.filter-btn.active').textContent.toLowerCase();
    
    suppliers.forEach(supplier => {
        let name = supplier.getAttribute('data-name');
        let contact = supplier.getAttribute('data-contact');
        let email = supplier.getAttribute('data-email');
        let status = supplier.getAttribute('data-status');
        
        let matchesSearch = name.includes(searchTerm) || 
                           (contact && contact.includes(searchTerm)) || 
                           (email && email.includes(searchTerm));
        
        let matchesFilter = activeFilter === 'all' || 
                           (activeFilter === 'active' && status === 'active') ||
                           (activeFilter === 'inactive' && status === 'inactive');
        
        if(matchesSearch && matchesFilter) {
            supplier.style.display = '';
        } else {
            supplier.style.display = 'none';
        }
    });
}

function filterByStatus(status) {
    let buttons = document.querySelectorAll('.filter-btn');
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if(btn.textContent.toLowerCase() === status) {
            btn.classList.add('active');
        }
    });
    filterSuppliers();
}

// Delete confirmation modal
function confirmDelete(id, name, productCount) {
    let modal = document.getElementById('deleteModal');
    let message = document.getElementById('deleteMessage');
    let warning = document.getElementById('productWarning');
    let warningText = document.getElementById('warningText');
    let confirmBtn = document.getElementById('confirmDeleteBtn');
    
    message.textContent = `Are you sure you want to delete supplier "${name}"?`;
    
    if(productCount > 0) {
        warning.style.display = 'block';
        warningText.textContent = `This supplier has ${productCount} product(s). You cannot delete them until all products are reassigned to another supplier.`;
        confirmBtn.style.pointerEvents = 'none';
        confirmBtn.style.opacity = '0.5';
    } else {
        warning.style.display = 'none';
        confirmBtn.style.pointerEvents = 'auto';
        confirmBtn.style.opacity = '1';
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
        alert.style.opacity = '0';
        setTimeout(() => {
            alert.style.display = 'none';
        }, 300);
    }
}, 5000);

// Close modal when clicking outside
window.onclick = function(event) {
    let modal = document.getElementById('deleteModal');
    if(event.target == modal) {
        modal.style.display = 'none';
    }
}

// Format phone number as user types
document.getElementById('phone')?.addEventListener('input', function(e) {
    let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
    e.target.value = !x[2] ? x[1]
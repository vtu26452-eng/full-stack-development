<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<nav class="sidebar">
    <div class="sidebar-header">
        <h3>Inventory System</h3>
    </div>
    <ul class="nav-menu">
        <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="modules/products.php">Products</a></li>
        <li><a href="modules/categories.php">Categories</a></li>
        <li><a href="modules/suppliers.php">Suppliers</a></li>
        <li><a href="modules/stock_in.php">Stock In</a></li>
        <li><a href="modules/stock_out.php">Stock Out</a></li>
        <li><a href="modules/reports.php">Reports</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>
<main class="main-content">
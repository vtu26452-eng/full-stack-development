<?php
session_start();
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get date range from request (default: last 30 days)
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-30 days'));

if(isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

// 1. Inventory Summary Statistics
$stats = [];

// Total inventory value
$query = "SELECT 
            SUM(current_stock * unit_price) as total_cost_value,
            SUM(current_stock * selling_price) as total_selling_value,
            COUNT(*) as total_products,
            SUM(current_stock) as total_units
          FROM products";
$stmt = $db->query($query);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Low stock count
$query = "SELECT COUNT(*) as low_stock FROM products WHERE current_stock <= reorder_level";
$stmt = $db->query($query);
$stats['low_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['low_stock'];

// Out of stock count
$query = "SELECT COUNT(*) as out_of_stock FROM products WHERE current_stock = 0";
$stmt = $db->query($query);
$stats['out_of_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['out_of_stock'];

// 2. Stock Movement Summary
$query = "SELECT 
            SUM(CASE WHEN type = 'IN' THEN quantity ELSE 0 END) as total_in,
            SUM(CASE WHEN type = 'OUT' THEN quantity ELSE 0 END) as total_out,
            COUNT(CASE WHEN type = 'IN' THEN 1 END) as in_count,
            COUNT(CASE WHEN type = 'OUT' THEN 1 END) as out_count
          FROM stock_movements
          WHERE DATE(movement_date) BETWEEN :start_date AND :end_date";
$stmt = $db->prepare($query);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$movement_summary = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Top Moving Products (by quantity)
$query = "SELECT 
            p.id,
            p.name,
            p.sku,
            p.current_stock,
            p.reorder_level,
            SUM(CASE WHEN sm.type = 'OUT' THEN sm.quantity ELSE 0 END) as total_sold,
            SUM(CASE WHEN sm.type = 'IN' THEN sm.quantity ELSE 0 END) as total_received
          FROM products p
          LEFT JOIN stock_movements sm ON p.id = sm.product_id
          WHERE DATE(sm.movement_date) BETWEEN :start_date AND :end_date OR sm.movement_date IS NULL
          GROUP BY p.id
          ORDER BY total_sold DESC
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Category-wise Stock Distribution
$query = "SELECT 
            c.name as category_name,
            COUNT(p.id) as product_count,
            SUM(p.current_stock) as total_stock,
            SUM(p.current_stock * p.selling_price) as total_value
          FROM categories c
          LEFT JOIN products p ON c.id = p.category_id
          GROUP BY c.id
          ORDER BY total_value DESC";
$stmt = $db->query($query);
$category_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Daily Movement Trend
$query = "SELECT 
            DATE(movement_date) as date,
            SUM(CASE WHEN type = 'IN' THEN quantity ELSE 0 END) as in_quantity,
            SUM(CASE WHEN type = 'OUT' THEN quantity ELSE 0 END) as out_quantity
          FROM stock_movements
          WHERE DATE(movement_date) BETWEEN :start_date AND :end_date
          GROUP BY DATE(movement_date)
          ORDER BY date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$daily_trend = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Supplier Performance
$query = "SELECT 
            s.name as supplier_name,
            COUNT(DISTINCT p.id) as products_supplied,
            SUM(p.current_stock) as total_stock,
            SUM(p.current_stock * p.unit_price) as inventory_value
          FROM suppliers s
          LEFT JOIN products p ON s.id = p.supplier_id
          GROUP BY s.id
          ORDER BY inventory_value DESC";
$stmt = $db->query($query);
$supplier_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<style>
/* Reports page specific styles */
.reports-container {
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
}

/* Date Filter */
.date-filter {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.date-filter label {
    font-weight: 500;
    color: #555;
}

.date-filter input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.date-filter button {
    padding: 8px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s;
}

.date-filter button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

/* Summary Cards */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.3s;
}

.summary-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.card-icon {
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

.card-content h3 {
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}

.card-content .number {
    font-size: 24px;
    font-weight: 700;
    color: #333;
}

.card-content .small {
    font-size: 12px;
    color: #999;
}

/* Report Sections */
.report-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.section-header h2 {
    font-size: 18px;
    color: #333;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.export-btn {
    padding: 8px 15px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s;
}

.export-btn:hover {
    background: #218838;
}

/* Report Grid */
.report-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

/* Tables */
.table-responsive {
    overflow-x: auto;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.report-table th {
    text-align: left;
    padding: 12px;
    background: #f8f9fa;
    color: #555;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.report-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    color: #666;
}

.report-table tr:hover td {
    background: #f8f9fa;
}

.text-right {
    text-align: right;
}

.text-success {
    color: #28a745;
    font-weight: 600;
}

.text-danger {
    color: #dc3545;
    font-weight: 600;
}

.text-warning {
    color: #ffc107;
    font-weight: 600;
}

/* Charts Container */
.charts-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.chart-box {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.chart-box h3 {
    font-size: 16px;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f0;
}

/* KPI Indicators */
.kpi-indicator {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.kpi-good {
    background: #d4edda;
    color: #155724;
}

.kpi-warning {
    background: #fff3cd;
    color: #856404;
}

.kpi-danger {
    background: #f8d7da;
    color: #721c24;
}

/* Progress Bar */
.progress-bar {
    width: 100%;
    height: 8px;
    background: #f0f0f0;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 5px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    border-radius: 4px;
}

/* Responsive */
@media (max-width: 768px) {
    .charts-container {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .date-filter {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<div class="reports-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">📊 Inventory Reports</h1>
        <div class="date-range">
            <span><?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?></span>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="date-filter">
        <form method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div>
                <label for="start_date">From:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div>
                <label for="end_date">To:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <button type="submit">Apply Filter</button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="card-icon">💰</div>
            <div class="card-content">
                <h3>Inventory Value (Cost)</h3>
                <div class="number">$<?php echo number_format($stats['total_cost_value'] ?? 0, 2); ?></div>
                <div class="small">Selling: $<?php echo number_format($stats['total_selling_value'] ?? 0, 2); ?></div>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon">📦</div>
            <div class="card-content">
                <h3>Total Products</h3>
                <div class="number"><?php echo $stats['total_products'] ?? 0; ?></div>
                <div class="small"><?php echo $stats['total_units'] ?? 0; ?> total units</div>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon">⚠️</div>
            <div class="card-content">
                <h3>Stock Status</h3>
                <div class="number"><?php echo $stats['low_stock'] ?? 0; ?> Low</div>
                <div class="small"><?php echo $stats['out_of_stock'] ?? 0; ?> Out of Stock</div>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon">📊</div>
            <div class="card-content">
                <h3>Movement (Selected Period)</h3>
                <div class="number"><?php echo $movement_summary['total_in'] ?? 0; ?> IN</div>
                <div class="small"><?php echo $movement_summary['total_out'] ?? 0; ?> OUT</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-container">
        <div class="chart-box">
            <h3>📈 Stock Movement Trend</h3>
            <canvas id="movementChart" style="width:100%; max-height:300px;"></canvas>
        </div>
        
        <div class="chart-box">
            <h3>🥧 Category Distribution</h3>
            <canvas id="categoryChart" style="width:100%; max-height:300px;"></canvas>
        </div>
    </div>

    <!-- Top Moving Products -->
    <div class="report-section">
        <div class="section-header">
            <h2>🔥 Top Moving Products</h2>
            <button class="export-btn" onclick="exportTableToCSV('top-products.csv')">
                📥 Export CSV
            </button>
        </div>
        <div class="table-responsive">
            <table class="report-table" id="topProductsTable">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                        <th class="text-right">Sold (Selected Period)</th>
                        <th class="text-right">Received</th>
                        <th>Turnover Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($top_products as $product): ?>
                        <?php 
                        $turnover = $product['total_sold'] > 0 ? 
                            round(($product['total_sold'] / max($product['current_stock'], 1)) * 100, 1) : 0;
                        $status_class = $product['current_stock'] <= $product['reorder_level'] ? 'kpi-warning' : 'kpi-good';
                        $status_text = $product['current_stock'] <= $product['reorder_level'] ? 'Low Stock' : 'In Stock';
                        if($product['current_stock'] == 0) {
                            $status_class = 'kpi-danger';
                            $status_text = 'Out of Stock';
                        }
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($product['sku']); ?></strong></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo $product['current_stock']; ?></td>
                            <td><span class="kpi-indicator <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                            <td class="text-right"><?php echo $product['total_sold'] ?? 0; ?></td>
                            <td class="text-right"><?php echo $product['total_received'] ?? 0; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span><?php echo $turnover; ?>%</span>
                                    <div class="progress-bar" style="width: 100px;">
                                        <div class="progress-fill" style="width: <?php echo min($turnover, 100); ?>%;"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category & Supplier Grid -->
    <div class="report-grid">
        <!-- Category Statistics -->
        <div class="report-section">
            <div class="section-header">
                <h2>📑 Category Analysis</h2>
            </div>
            <div class="table-responsive">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Products</th>
                            <th class="text-right">Stock</th>
                            <th class="text-right">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($category_stats as $cat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cat['category_name'] ?: 'Uncategorized'); ?></td>
                            <td><?php echo $cat['product_count']; ?></td>
                            <td class="text-right"><?php echo $cat['total_stock']; ?></td>
                            <td class="text-right">$<?php echo number_format($cat['total_value'] ?? 0, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Supplier Performance -->
        <div class="report-section">
            <div class="section-header">
                <h2>🏢 Supplier Performance</h2>
            </div>
            <div class="table-responsive">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Products</th>
                            <th class="text-right">Total Stock</th>
                            <th class="text-right">Inventory Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($supplier_stats as $sup): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sup['supplier_name'] ?: 'No Supplier'); ?></td>
                            <td><?php echo $sup['products_supplied']; ?></td>
                            <td class="text-right"><?php echo $sup['total_stock']; ?></td>
                            <td class="text-right">$<?php echo number_format($sup['inventory_value'] ?? 0, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Daily Movement Details -->
    <div class="report-section">
        <div class="section-header">
            <h2>📅 Daily Movement Log</h2>
        </div>
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-right">Stock In</th>
                        <th class="text-right">Stock Out</th>
                        <th class="text-right">Net Change</th>
                        <th>Transactions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_in = 0;
                    $total_out = 0;
                    foreach($daily_trend as $day): 
                        $total_in += $day['in_quantity'];
                        $total_out += $day['out_quantity'];
                        $net_change = $day['in_quantity'] - $day['out_quantity'];
                        $net_class = $net_change > 0 ? 'text-success' : ($net_change < 0 ? 'text-danger' : '');
                    ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($day['date'])); ?></td>
                        <td class="text-right text-success">+<?php echo $day['in_quantity']; ?></td>
                        <td class="text-right text-danger">-<?php echo $day['out_quantity']; ?></td>
                        <td class="text-right <?php echo $net_class; ?>">
                            <?php echo ($net_change > 0 ? '+' : '') . $net_change; ?>
                        </td>
                        <td>
                            <?php 
                            $transactions = 0;
                            if($day['in_quantity'] > 0) $transactions++;
                            if($day['out_quantity'] > 0) $transactions++;
                            echo $transactions . ' movement(s)';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot style="background: #f8f9fa; font-weight: bold;">
                    <tr>
                        <td>Total</td>
                        <td class="text-right text-success">+<?php echo $total_in; ?></td>
                        <td class="text-right text-danger">-<?php echo $total_out; ?></td>
                        <td class="text-right"><?php echo ($total_in - $total_out); ?></td>
                        <td><?php echo count($daily_trend); ?> days</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Movement Trend Chart
const movementCtx = document.getElementById('movementChart').getContext('2d');
new Chart(movementCtx, {
    type: 'line',
    data: {
        labels: [<?php 
            $dates = array_reverse($daily_trend);
            foreach($dates as $day) {
                echo "'" . date('d M', strtotime($day['date'])) . "',";
            }
        ?>],
        datasets: [{
            label: 'Stock In',
            data: [<?php 
                foreach($dates as $day) {
                    echo $day['in_quantity'] . ",";
                }
            ?>],
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4
        }, {
            label: 'Stock Out',
            data: [<?php 
                foreach($dates as $day) {
                    echo $day['out_quantity'] . ",";
                }
            ?>],
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});

// Category Distribution Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: [<?php 
            foreach($category_stats as $cat) {
                echo "'" . ($cat['category_name'] ?: 'Uncategorized') . "',";
            }
        ?>],
        datasets: [{
            data: [<?php 
                foreach($category_stats as $cat) {
                    echo ($cat['total_value'] ?? 0) . ",";
                }
            ?>],
            backgroundColor: [
                '#667eea',
                '#764ba2',
                '#f093fb',
                '#f5576c',
                '#4facfe',
                '#00f2fe'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Export to CSV function
function exportTableToCSV(filename) {
    const table = document.getElementById('topProductsTable');
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    for(let row of rows) {
        const cells = row.querySelectorAll('th, td');
        const rowData = [];
        for(let cell of cells) {
            // Remove HTML tags and get clean text
            let text = cell.textContent.trim();
            rowData.push('"' + text.replace(/"/g, '""') + '"');
        }
        csv.push(rowData.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

// Print report function
function printReport() {
    window.print();
}

// Auto-refresh data every 5 minutes (optional)
setTimeout(function() {
    location.reload();
}, 300000); // 5 minutes
</script>

<?php include '../includes/footer.php'; ?>
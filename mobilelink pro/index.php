<?php
// index.php
session_start();
include 'db_connect.php';

// --- DATA FETCHING ---

// 1. KPI: Today's Sales
$today = date("Y-m-d");
$sales_today_result = $conn->query("SELECT SUM(TotalAmount) as total FROM Sales WHERE DATE(SaleDate) = '$today'");
$sales_today = $sales_today_result->fetch_assoc()['total'] ?? 0;

// 2. KPI: Transactions Today
$transactions_today_result = $conn->query("SELECT COUNT(SaleID) as count FROM Sales WHERE DATE(SaleDate) = '$today'");
$transactions_today = $transactions_today_result->fetch_assoc()['count'] ?? 0;

// 3. KPI: Total Products in Stock
$total_products_result = $conn->query("SELECT SUM(StockQuantity) as total_stock FROM Products");
$total_products = $total_products_result->fetch_assoc()['total_stock'] ?? 0;

// 4. KPI: Low Stock Alerts
$low_stock_threshold = 5;
$low_stock_result = $conn->query("SELECT COUNT(ProductID) as low_stock_count FROM Products WHERE StockQuantity < $low_stock_threshold");
$low_stock_count = $low_stock_result->fetch_assoc()['low_stock_count'] ?? 0;

// 5. Recent Transactions (Last 5)
$recent_transactions_query = "
    SELECT s.SaleID, c.FirstName, c.LastName, s.TotalAmount, s.SaleDate
    FROM Sales s
    JOIN Customers c ON s.CustomerID = c.CustomerID
    ORDER BY s.SaleDate DESC
    LIMIT 5";
$recent_transactions_result = $conn->query($recent_transactions_query);

// 6. Low Stock Items
$low_stock_items_query = "
    SELECT Brand, Model, StockQuantity
    FROM Products
    WHERE StockQuantity < $low_stock_threshold
    ORDER BY StockQuantity ASC";
$low_stock_items_result = $conn->query($low_stock_items_query);


// 7. Sales Chart Data (Last 7 days)
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_name = date('D', strtotime($date));
    $result = $conn->query("SELECT SUM(TotalAmount) as daily_total FROM Sales WHERE DATE(SaleDate) = '$date'");
    $row = $result->fetch_assoc();
    $chart_data['labels'][] = $day_name;
    $chart_data['data'][] = $row['daily_total'] ?? 0;
}
$chart_data_json = json_encode($chart_data);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MobileLink Pro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-header">MobileLink Pro</div>
               <nav>
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="inventory.php"><i class="fas fa-box-open"></i> Inventory</a>
                <a href="new_sale.php"><i class="fas fa-cash-register"></i> Sales</a>
                <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
                <a href="#"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Dashboard</h1>
                <div class="user-info">Welcome, Admin!</div>
            </header>

            <section class="kpi-cards">
                <div class="card">
                    <div class="card-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="card-info">
                        <p>Today's Sales</p>
                        <h3>K<?php echo number_format($sales_today, 2); ?></h3>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="card-info">
                        <p>Transactions Today</p>
                        <h3><?php echo $transactions_today; ?></h3>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-boxes-stacked"></i></div>
                    <div class="card-info">
                        <p>Total Products in Stock</p>
                        <h3><?php echo $total_products; ?></h3>
                    </div>
                </div>
                <div class="card alert">
                    <div class="card-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="card-info">
                        <p>Low Stock Alerts</p>
                        <h3><?php echo $low_stock_count; ?></h3>
                    </div>
                </div>
            </section>

            <section class="dashboard-main">
                <div class="chart-container">
                    <h3>Weekly Sales Trend (ZMW)</h3>
                    <canvas id="salesChart"></canvas>
                </div>
                <div class="recent-activity">
                    <h3>Recent Transactions</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_transactions_result->num_rows > 0): ?>
                                <?php while($row = $recent_transactions_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
                                        <td>K<?php echo number_format($row['TotalAmount'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="2">No transactions today.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            
            <section class="low-stock-section">
                <h3>Low Stock Items</h3>
                 <table>
                        <thead>
                            <tr>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Qty Left</th>
                            </tr>
                        </thead>
                        <tbody>
                             <?php if ($low_stock_items_result->num_rows > 0): ?>
                                <?php while($row = $low_stock_items_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['Brand']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Model']); ?></td>
                                        <td><span class="stock-alert"><?php echo $row['StockQuantity']; ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3">No items are low on stock.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
            </section>
        </main>
    </div>

    <script>
        // JavaScript for Sales Chart using Chart.js
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesData = JSON.parse('<?php echo $chart_data_json; ?>');

        new Chart(ctx, {
            type: 'line', // You can change this to 'bar'
            data: {
                labels: salesData.labels,
                datasets: [{
                    label: 'Sales (ZMW)',
                    data: salesData.data,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>
<?php
// inventory.php
session_start();
// if (!isset($_SESSION['UserID'])) {
//     header('Location: login.php'); // Uncomment this when you create the login system
//     exit();
// }
include 'db_connect.php';

// Fetch all products from the database
$sql = "SELECT ProductID, Brand, Model, IMEI, StockQuantity, SellingPrice FROM Products ORDER BY Brand, Model";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - MobileLink Pro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-header">MobileLink Pro</div>
            <nav>
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="inventory.php"><i class="fas fa-box-open"></i> Inventory</a>
                <a href="new_sale.php" class="active"><i class="fas fa-cash-register"></i> Sales</a>
                <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
                <a href="#"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
            
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Inventory Management</h1>
                <a href="add_product.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Add New Phone
                </a>
            </header>

            <section class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>IMEI</th>
                            <th>Stock Qty</th>
                            <th>Price (ZMW)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['Brand']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Model']); ?></td>
                                    <td><?php echo htmlspecialchars($row['IMEI']); ?></td>
                                    <td><?php echo $row['StockQuantity']; ?></td>
                                    <td><?php echo number_format($row['SellingPrice'], 2); ?></td>
                                    <td class="actions">
                                        <a href="edit_product.php?id=<?php echo $row['ProductID']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="delete_product.php?id=<?php echo $row['ProductID']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fas fa-trash"></i> Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No products found in inventory.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
    <script src="api_interactions.js"></script>
</body>
</html>
<?php $conn->close(); ?>
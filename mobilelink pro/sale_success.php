<?php
// sale_success.php
session_start();
include 'db_connect.php';

$sale_id = isset($_GET['sale_id']) ? (int)$_GET['sale_id'] : 0;
if ($sale_id == 0) {
    header('Location: new_sale.php');
    exit();
}

// Fetch sale details to display
$sql = "
    SELECT s.SaleID, s.SaleDate, s.TotalAmount, s.PaymentMethod, c.FirstName, c.LastName
    FROM Sales s
    JOIN Customers c ON s.CustomerID = c.CustomerID
    WHERE s.SaleID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$result = $stmt->get_result();
$sale = $result->fetch_assoc();

// Fetch items sold in this transaction
$items_sql = "
    SELECT p.Brand, p.Model, si.Quantity, si.UnitPrice
    FROM Sales_Items si
    JOIN Products p ON si.ProductID = p.ProductID
    WHERE si.SaleID = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $sale_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sale Successful - MobileLink Pro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .invoice-container { background-color: #fff; padding: 2rem; margin: 0 auto; max-width: 800px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .success-message { text-align: center; color: #10B981; margin-bottom: 2rem; }
        .invoice-header { display: flex; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem; }
        .invoice-details { text-align: right; }
        .invoice-table { width: 100%; margin-top: 2rem; }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar"> </aside>
        <main class="main-content">
            <div class="invoice-container">
                <div class="success-message">
                    <i class="fas fa-check-circle fa-3x"></i>
                    <h2>Sale Completed Successfully!</h2>
                </div>
                <div class="invoice-header">
                    <div>
                        <h3>Invoice</h3>
                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($sale['FirstName'] . ' ' . $sale['LastName']); ?></p>
                    </div>
                    <div class="invoice-details">
                        <p><strong>Sale ID:</strong> #<?php echo $sale['SaleID']; ?></p>
                        <p><strong>Date:</strong> <?php echo date('d M Y, h:i A', strtotime($sale['SaleDate'])); ?></p>
                    </div>
                </div>
                <table class="invoice-table">
                    <thead><tr><th>Item</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php while($item = $items_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['Brand'] . ' ' . $item['Model']); ?></td>
                            <td><?php echo $item['Quantity']; ?></td>
                            <td><?php echo number_format($item['UnitPrice'], 2); ?></td>
                            <td><?php echo number_format($item['UnitPrice'] * $item['Quantity'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; border-top: 2px solid #333;">
                            <td colspan="3" style="text-align: right;">Grand Total:</td>
                            <td>ZMW <?php echo number_format($sale['TotalAmount'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
                 <a href="new_sale.php" class="btn-primary" style="margin-top: 2rem; display: inline-block;">Start New Sale</a>
            </div>
        </main>
    </div>
</body>
</html>
<?php
$stmt->close();
$items_stmt->close();
$conn->close();
?>
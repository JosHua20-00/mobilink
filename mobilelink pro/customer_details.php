<?php
// customer_details.php
session_start();
include 'db_connect.php';

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($customer_id == 0) {
    header('Location: customers.php');
    exit();
}

// Fetch customer details
$stmt = $conn->prepare("SELECT * FROM Customers WHERE CustomerID = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $customer = $result->fetch_assoc();
} else {
    echo "Customer not found."; exit;
}
$stmt->close();

// Fetch customer's sales history
$sales_sql = "SELECT SaleID, SaleDate, TotalAmount, PaymentMethod FROM Sales WHERE CustomerID = ? ORDER BY SaleDate DESC";
$sales_stmt = $conn->prepare($sales_sql);
$sales_stmt->bind_param("i", $customer_id);
$sales_stmt->execute();
$sales_result = $sales_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Details - MobileLink Pro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .customer-card { background-color: #fff; padding: 2rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .customer-card h2 { margin-bottom: 1rem; }
        .customer-card p { margin-bottom: 0.5rem; color: #4B5563; }
        .customer-card strong { color: var(--secondary-color); }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar"> </aside>
        <main class="main-content">
            <header class="header">
                <h1>Customer Details</h1>
                <a href="customers.php" class="btn-primary"><i class="fas fa-arrow-left"></i> Back to Customers</a>
            </header>

            <section class="customer-card">
                <h2><?php echo htmlspecialchars($customer['FirstName'] . ' ' . $customer['LastName']); ?></h2>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['PhoneNumber']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['Email'] ?? 'N/A'); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($customer['Address'] ?? 'N/A'); ?></p>
            </section>

            <section class="content-table">
                <h3>Purchase History</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Sale ID</th>
                            <th>Date</th>
                            <th>Payment Method</th>
                            <th>Total Amount (ZMW)</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php if ($sales_result->num_rows > 0): ?>
                            <?php while($row = $sales_result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['SaleID']; ?></td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($row['SaleDate'])); ?></td>
                                    <td><?php echo $row['PaymentMethod']; ?></td>
                                    <td><?php echo number_format($row['TotalAmount'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">This customer has no purchase history.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
<?php
$sales_stmt->close();
$conn->close();
?>
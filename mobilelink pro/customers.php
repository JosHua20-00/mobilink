<?php
// customers.php
session_start();
include 'db_connect.php';

// Fetch all customers
$sql = "SELECT CustomerID, FirstName, LastName, PhoneNumber, Email FROM Customers ORDER BY LastName, FirstName";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - MobileLink Pro</title>
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
                <h1>Customer Management</h1>
                <a href="add_customer.php" class="btn-primary">
                    <i class="fas fa-user-plus"></i> Add New Customer
                </a>
            </header>

            <section class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone Number</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['PhoneNumber']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                    <td class="actions">
                                        <a href="customer_details.php?id=<?php echo $row['CustomerID']; ?>" class="btn-view"><i class="fas fa-eye"></i> View</a>
                                        <a href="edit_customer.php?id=<?php echo $row['CustomerID']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No customers found.</td>
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
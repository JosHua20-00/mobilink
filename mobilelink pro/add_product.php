<?php
// add_product.php
session_start();
include 'db_connect.php';

$error_message = '';
$success_message = '';

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $brand = $conn->real_escape_string($_POST['brand']);
    $model = $conn->real_escape_string($_POST['model']);
    $imei = $conn->real_escape_string($_POST['imei']);
    $storage = (int)$_POST['storage'];
    $color = $conn->real_escape_string($_POST['color']);
    $purchase_price = (float)$_POST['purchase_price'];
    $selling_price = (float)$_POST['selling_price'];
    $quantity = (int)$_POST['quantity'];
    $supplier = $conn->real_escape_string($_POST['supplier']);

    // Basic Validation
    if (empty($brand) || empty($model) || empty($imei) || empty($selling_price)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO Products (Brand, Model, IMEI, StorageGB, Color, PurchasePrice, SellingPrice, StockQuantity, Supplier) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssisddis", $brand, $model, $imei, $storage, $color, $purchase_price, $selling_price, $quantity, $supplier);

        if ($stmt->execute()) {
            header("Location: inventory.php?status=success_add");
            exit();
        } else {
            // Check for duplicate IMEI
            if ($conn->errno == 1062) {
                $error_message = "Error: A product with this IMEI already exists.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - MobileLink Pro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Add specific styles for forms if needed */
        .form-container { background-color: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .form-group input { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #D1D5DB; }
        .form-actions { display: flex; gap: 1rem; }
        .btn-submit { background-color: var(--primary-color); color: #fff; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; }
        .btn-cancel { background-color: #6B7280; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 5px; }
        .message { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .error { background-color: #FEE2E2; color: #B91C1C; }
    </style>
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
            <header class="header"><h1>Add New Product</h1></header>

            <div class="form-container">
                <?php if ($error_message): ?>
                    <div class="message error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form action="add_product.php" method="POST">
                    <div class="form-group"><label for="brand">Brand</label><input type="text" id="brand" name="brand" required></div>
                    <div class="form-group"><label for="model">Model</label><input type="text" id="model" name="model" required></div>
                    <div class="form-group"><label for="imei">IMEI</label><input type="text" id="imei" name="imei" required></div>
                    <div class="form-group"><label for="storage">Storage (GB)</label><input type="number" id="storage" name="storage"></div>
                    <div class="form-group"><label for="color">Color</label><input type="text" id="color" name="color"></div>
                    <div class="form-group"><label for="purchase_price">Purchase Price (ZMW)</label><input type="number" step="0.01" id="purchase_price" name="purchase_price"></div>
                    <div class="form-group"><label for="selling_price">Selling Price (ZMW)</label><input type="number" step="0.01" id="selling_price" name="selling_price" required></div>
                    <div class="form-group"><label for="quantity">Stock Quantity</label><input type="number" id="quantity" name="quantity" value="1" required></div>
                    <div class="form-group"><label for="supplier">Supplier</label><input type="text" id="supplier" name="supplier"></div>
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Add Product</button>
                        <a href="inventory.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
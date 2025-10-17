<?php
// edit_product.php
session_start();
include 'db_connect.php';

$error_message = '';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id == 0) {
    header('Location: inventory.php');
    exit();
}

// Handle form submission for update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pid = (int)$_POST['product_id'];
    $brand = $conn->real_escape_string($_POST['brand']);
    $model = $conn->real_escape_string($_POST['model']);
    // ... get all other fields like in add_product.php
    $selling_price = (float)$_POST['selling_price'];
    $quantity = (int)$_POST['quantity'];

    $stmt = $conn->prepare("UPDATE Products SET Brand=?, Model=?, SellingPrice=?, StockQuantity=? WHERE ProductID=?");
    // Bind all parameters here
    $stmt->bind_param("ssdis", $brand, $model, $selling_price, $quantity, $pid);

    if ($stmt->execute()) {
        header("Location: inventory.php?status=success_update");
        exit();
    } else {
        $error_message = "Error updating record: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch the existing product data
$stmt = $conn->prepare("SELECT * FROM Products WHERE ProductID = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $product = $result->fetch_assoc();
} else {
    echo "Product not found.";
    exit;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - MobileLink Pro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
     <style> /* Re-using styles from add_product.php */ .form-container { background-color: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); } .form-group { margin-bottom: 1.5rem; } .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; } .form-group input { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #D1D5DB; } .form-actions { display: flex; gap: 1rem; } .btn-submit { background-color: var(--primary-color); color: #fff; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; } .btn-cancel { background-color: #6B7280; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 5px; } .message { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; } .error { background-color: #FEE2E2; color: #B91C1C; } </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-header">MobileLink Pro</div>
             <nav>
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="inventory.php" class="active"><i class="fas fa-box-open"></i> Inventory</a>
                <a href="#"><i class="fas fa-cash-register"></i> Sales</a>
                <a href="#"><i class="fas fa-users"></i> Customers</a>
                <a href="#"><i class="fas fa-chart-bar"></i> Reports</a>
            </nav>
        </aside>
        <main class="main-content">
            <header class="header"><h1>Edit Product: <?php echo htmlspecialchars($product['Brand'] . ' ' . $product['Model']); ?></h1></header>
            <div class="form-container">
                 <form action="edit_product.php?id=<?php echo $product_id; ?>" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                    <div class="form-group">
                        <label for="brand">Brand</label>
                        <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($product['Brand']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="model">Model</label>
                        <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($product['Model']); ?>" required>
                    </div>
                     <div class="form-group">
                        <label for="selling_price">Selling Price (ZMW)</label>
                        <input type="number" step="0.01" id="selling_price" name="selling_price" value="<?php echo $product['SellingPrice']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Stock Quantity</label>
                        <input type="number" id="quantity" name="quantity" value="<?php echo $product['StockQuantity']; ?>" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Update Product</button>
                        <a href="inventory.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
<?php
// process_sale.php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = (int)$_POST['customer_id'];
    $total_amount = (float)$_POST['total_amount'];
    $payment_method = $_POST['payment_method'];
    $products = $_POST['products']; // This is an array of JSON strings
    // Assume UserID 1 for now. Replace with $_SESSION['UserID'] when login is ready.
    $user_id = 1;

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Step 1: Insert into Sales table
        $stmt_sales = $conn->prepare("INSERT INTO Sales (CustomerID, UserID, TotalAmount, PaymentMethod) VALUES (?, ?, ?, ?)");
        $stmt_sales->bind_param("iids", $customer_id, $user_id, $total_amount, $payment_method);
        $stmt_sales->execute();
        $sale_id = $stmt_sales->insert_id; // Get the ID of the new sale
        $stmt_sales->close();

        // Step 2: Prepare statements for Sales_Items and Products update
        $stmt_items = $conn->prepare("INSERT INTO Sales_Items (SaleID, ProductID, Quantity, UnitPrice) VALUES (?, ?, ?, ?)");
        $stmt_stock = $conn->prepare("UPDATE Products SET StockQuantity = StockQuantity - ? WHERE ProductID = ?");

        // Step 3: Loop through products, insert into Sales_Items, and update stock
        foreach ($products as $product_json) {
            $product = json_decode($product_json);
            
            $product_id = (int)$product->id;
            $quantity = (int)$product->quantity;
            $unit_price = (float)$product->price;

            // Insert into Sales_Items
            $stmt_items->bind_param("iiid", $sale_id, $product_id, $quantity, $unit_price);
            $stmt_items->execute();

            // Update product stock
            $stmt_stock->bind_param("ii", $quantity, $product_id);
            $stmt_stock->execute();
        }

        $stmt_items->close();
        $stmt_stock->close();

        // If all queries were successful, commit the transaction
        $conn->commit();
        
        // Redirect to a success page with the new Sale ID
        header("Location: sale_success.php?sale_id=" . $sale_id);
        exit();

    } catch (mysqli_sql_exception $exception) {
        // If any query failed, roll back the transaction
        $conn->rollback();
        die("Transaction failed: " . $exception->getMessage());
    }
}

$conn->close();
?>
<?php
// delete_product.php
session_start();
include 'db_connect.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id > 0) {
    // Use a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM Products WHERE ProductID = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        // Redirect back to the inventory page with a success message
        header("Location: inventory.php?status=success_delete");
        exit();
    } else {
        // Handle error
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
} else {
    // Redirect if no valid ID is provided
    header("Location: inventory.php");
    exit();
}

$conn->close();
?>
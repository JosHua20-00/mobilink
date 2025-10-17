<?php
// fetch_products.php
session_start();
if (!isset($_SESSION['UserID'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');
include 'db_connect.php';

// Check if $conn is the correct variable name (MySQLi)
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$sql = "SELECT ProductID, Brand, Model, SellingPrice, StockQuantity FROM Products WHERE StockQuantity > 0 ORDER BY Brand, Model";
$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode($products);
$conn->close();
?>
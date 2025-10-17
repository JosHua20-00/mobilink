<?php
// fetch_customers.php
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

$sql = "SELECT CustomerID, FirstName, LastName, PhoneNumber FROM Customers ORDER BY FirstName, LastName";
$result = $conn->query($sql);

$customers = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

echo json_encode($customers);
$conn->close();
?>
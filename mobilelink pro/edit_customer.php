<?php
// edit_customer.php
session_start();
include 'db_connect.php';

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($customer_id == 0) {
    header('Location: customers.php');
    exit();
}

// Handle form submission for update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cid = (int)$_POST['customer_id'];
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    
    $stmt = $conn->prepare("UPDATE Customers SET FirstName=?, LastName=?, PhoneNumber=?, Email=?, Address=? WHERE CustomerID=?");
    $stmt->bind_param("sssssi", $firstname, $lastname, $phone, $email, $address, $cid);
    
    if ($stmt->execute()) {
        header("Location: customers.php?status=success_update");
        exit();
    } else {
        $error_message = "Error updating record: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch existing customer data
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
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Customer - MobileLink Pro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style> .form-container { background-color: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); } .form-group { margin-bottom: 1.5rem; } .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; } .form-group input, .form-group textarea { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #D1D5DB; font-family: 'Poppins', sans-serif; } .form-actions { display: flex; gap: 1rem; } .btn-submit { background-color: var(--primary-color); color: #fff; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; } .btn-cancel { background-color: #6B7280; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 5px; } </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar"> </aside>
        <main class="main-content">
            <header class="header"><h1>Edit Customer</h1></header>
            <div class="form-container">
                <form action="edit_customer.php?id=<?php echo $customer_id; ?>" method="POST">
                    <input type="hidden" name="customer_id" value="<?php echo $customer['CustomerID']; ?>">
                    <div class="form-group"><label for="firstname">First Name</label><input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($customer['FirstName']); ?>" required></div>
                    <div class="form-group"><label for="lastname">Last Name</label><input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($customer['LastName']); ?>" required></div>
                    <div class="form-group"><label for="phone">Phone Number</label><input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['PhoneNumber']); ?>" required></div>
                    <div class="form-group"><label for="email">Email Address</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['Email']); ?>"></div>
                    <div class="form-group"><label for="address">Address</label><textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($customer['Address']); ?></textarea></div>
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Update Customer</button>
                        <a href="customers.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
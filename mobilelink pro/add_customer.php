<?php
// add_customer.php
session_start();
include 'db_connect.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);

    if (empty($firstname) || empty($lastname) || empty($phone)) {
        $error_message = "First Name, Last Name, and Phone Number are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO Customers (FirstName, LastName, PhoneNumber, Email, Address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $firstname, $lastname, $phone, $email, $address);
        
        if ($stmt->execute()) {
            header("Location: customers.php?status=success_add");
            exit();
        } else {
            if ($conn->errno == 1062) {
                $error_message = "Error: A customer with this phone number or email already exists.";
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
    <title>Add Customer - MobileLink Pro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style> .form-container { background-color: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); } .form-group { margin-bottom: 1.5rem; } .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; } .form-group input, .form-group textarea { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #D1D5DB; font-family: 'Poppins', sans-serif; } .form-actions { display: flex; gap: 1rem; } .btn-submit { background-color: var(--primary-color); color: #fff; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; } .btn-cancel { background-color: #6B7280; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 5px; } .message { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; } .error { background-color: #FEE2E2; color: #B91C1C; } </style>
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
            <header class="header"><h1>Add New Customer</h1></header>

            <div class="form-container">
                 <?php if ($error_message): ?>
                    <div class="message error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form action="add_customer.php" method="POST">
                    <div class="form-group"><label for="firstname">First Name</label><input type="text" id="firstname" name="firstname" required></div>
                    <div class="form-group"><label for="lastname">Last Name</label><input type="text" id="lastname" name="lastname" required></div>
                    <div class="form-group"><label for="phone">Phone Number</label><input type="tel" id="phone" name="phone" required></div>
                    <div class="form-group"><label for="email">Email Address</label><input type="email" id="email" name="email"></div>
                    <div class="form-group"><label for="address">Address</label><textarea id="address" name="address" rows="3"></textarea></div>
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Add Customer</button>
                        <a href="customers.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
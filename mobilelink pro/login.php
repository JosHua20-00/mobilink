<?php
// login.php
session_start();

// If user is already logged in, redirect to the dashboard
if (isset($_SESSION['UserID'])) {
    header('Location: index.php');
    exit();
}

include 'db_connect.php';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT UserID, Username, PasswordHash, FullName, Role FROM Users WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify the password against the stored hash
        if (password_verify($password, $user['PasswordHash'])) {
            // Password is correct, start the session
            session_regenerate_id(true); // Security measure
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['Username'] = $user['Username'];
            $_SESSION['FullName'] = $user['FullName'];
            
            header('Location: index.php');
            exit();
        }
    }
    // If we get here, login failed
    $error_message = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - MobileLink Pro</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background-color: var(--background-color); }
        .login-container { background-color: #fff; padding: 2rem 3rem; border-radius: 10px; box-shadow: 0 10px 15px rgba(0,0,0,0.1); text-align: center; width: 100%; max-width: 400px; }
        .login-container h1 { margin-bottom: 1.5rem; color: var(--secondary-color); }
        .form-group { text-align: left; margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .btn-login { width: 100%; padding: 12px; background-color: var(--primary-color); color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; }
        .error-message { color: var(--alert-color); background-color: #FEE2E2; padding: 10px; border-radius: 5px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>MobileLink Pro</h1>
        <p>Please sign in to continue</p>
        <br>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Sign In</button>
        </form>
    </div>
</body>
</html>
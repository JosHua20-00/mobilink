<?php
// db_connect.php

$servername = "localhost";
$username = "root"; // Your database username
$password = "";     // Your database password
$dbname = "mobilelink_db"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set timezone to match your location (e.g., Africa/Lusaka for Zambia)
date_default_timezone_set('Africa/Lusaka');
?>
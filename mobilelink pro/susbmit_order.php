<?php
// submit_order.php
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["message" => "Invalid order data."]);
    exit;
}

$name = htmlspecialchars($data['name']);
$phone = htmlspecialchars($data['phone']);
$email = htmlspecialchars($data['email']);
$items = $data['items'];

if (empty($name) || empty($phone) || empty($items)) {
    echo json_encode(["message" => "Missing required fields."]);
    exit;
}

// You can later save this to database, send email, or notify admin.
echo json_encode(["message" => "Thank you, $name! Your order has been received."]);
?>

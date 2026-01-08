<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = new mysqli("localhost", "root", "", "food-app");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB error"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid JSON"]);
    exit;
}

$cart_id = (int)($data["cart_id"] ?? 0);
$quantity = (int)($data["quantity"] ?? 1);

if ($cart_id <= 0 || $quantity <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid cart ID or quantity"]);
    exit;
}

$sql = "UPDATE cart SET quantity = $quantity WHERE id = $cart_id";

if ($conn->query($sql)) {
    echo json_encode(["success" => true, "message" => "Cart updated"]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed"]);
}
$conn->close();

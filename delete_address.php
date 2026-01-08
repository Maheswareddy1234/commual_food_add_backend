<?php
error_reporting(0);
ini_set('display_errors', 0);
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

$address_id = (int)($data["address_id"] ?? 0);

if ($address_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid address ID"]);
    exit;
}

$sql = "DELETE FROM addresses WHERE id = $address_id";

if ($conn->query($sql)) {
    echo json_encode(["success" => true, "message" => "Address deleted"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to delete"]);
}
$conn->close();

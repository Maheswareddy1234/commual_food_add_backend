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

$customer_id = (int)($data["customer_id"] ?? 0);
$label = $conn->real_escape_string($data["label"] ?? "");
$full_address = $conn->real_escape_string($data["full_address"] ?? "");
$landmark = $conn->real_escape_string($data["landmark"] ?? "");
$pincode = $conn->real_escape_string($data["pincode"] ?? "");
$is_default = isset($data["is_default"]) && $data["is_default"] ? 1 : 0;

if ($customer_id <= 0 || empty($label) || empty($full_address)) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

// Create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    label VARCHAR(50) NOT NULL,
    full_address TEXT NOT NULL,
    landmark VARCHAR(255),
    pincode VARCHAR(10),
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// If setting as default, unset other defaults first
if ($is_default) {
    $conn->query("UPDATE addresses SET is_default = 0 WHERE customer_id = $customer_id");
}

$sql = "INSERT INTO addresses (customer_id, label, full_address, landmark, pincode, is_default) 
        VALUES ($customer_id, '$label', '$full_address', '$landmark', '$pincode', $is_default)";

if ($conn->query($sql)) {
    echo json_encode([
        "success" => true, 
        "message" => "Address saved",
        "address_id" => $conn->insert_id
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to save address"]);
}
$conn->close();

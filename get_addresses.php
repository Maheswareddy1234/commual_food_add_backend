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

$customer_id = isset($_GET["customer_id"]) ? (int)$_GET["customer_id"] : 0;

if ($customer_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid customer ID"]);
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

$sql = "SELECT * FROM addresses WHERE customer_id = $customer_id ORDER BY is_default DESC, created_at DESC";
$result = $conn->query($sql);

$addresses = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $addresses[] = [
            "id" => (int)$row["id"],
            "customer_id" => (int)$row["customer_id"],
            "label" => $row["label"],
            "full_address" => $row["full_address"],
            "landmark" => $row["landmark"],
            "pincode" => $row["pincode"],
            "is_default" => (bool)$row["is_default"]
        ];
    }
}

echo json_encode([
    "success" => true,
    "addresses" => $addresses
]);
$conn->close();

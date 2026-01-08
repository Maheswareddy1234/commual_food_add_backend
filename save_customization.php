<?php
header("Content-Type: application/json");
require_once "db.php";

/* Read JSON input */
$data = json_decode(file_get_contents("php://input"), true);

if ($data === null) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON input"
    ]);
    exit;
}

/* Required fields */
$required = ["customer_id", "dish_id"];
foreach ($required as $field) {
    if (!isset($data[$field]) || trim((string)$data[$field]) === "") {
        echo json_encode([
            "success" => false,
            "message" => "$field is required"
        ]);
        exit;
    }
}

$customer_id = (int)$data["customer_id"];
$dish_id = (int)$data["dish_id"];

/* Optional customization fields */
$spice_level  = $conn->real_escape_string($data["spice_level"] ?? "");
$oil_level    = $conn->real_escape_string($data["oil_level"] ?? "");
$salt_level   = $conn->real_escape_string($data["salt_level"] ?? "");
$sweetness    = $conn->real_escape_string($data["sweetness"] ?? "");
$portion_size = $conn->real_escape_string($data["portion_size"] ?? "");
$instructions = $conn->real_escape_string($data["instructions"] ?? "");

/* Save customization */
$sql = "INSERT INTO dish_customizations
(customer_id, dish_id, spice_level, oil_level, salt_level, sweetness, portion_size, instructions)
VALUES
('$customer_id','$dish_id','$spice_level','$oil_level','$salt_level','$sweetness','$portion_size','$instructions')";

if ($conn->query($sql)) {
    echo json_encode([
        "success" => true,
        "message" => "Customization saved successfully",
        "customization_id" => $conn->insert_id
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to save customization"
    ]);
}
?>

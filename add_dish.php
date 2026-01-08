<?php
header("Content-Type: application/json");
require_once "db.php";

/* 1. Read JSON input */
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

/* 2. Check JSON validity */
if ($data === null) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid or empty JSON input"
    ]);
    exit;
}

/* 3. Required fields check */
$required = [
    "chef_id",
    "dish_name",
    "price",
    "preparation_time",
    "category",
    "food_type"
];

foreach ($required as $field) {
    if (!isset($data[$field]) || trim((string)$data[$field]) === "") {
        echo json_encode([
            "success" => false,
            "message" => "$field is required"
        ]);
        exit;
    }
}

/* 4. Sanitize inputs */
$chef_id = (int) $data["chef_id"];
$dish_name = $conn->real_escape_string($data["dish_name"]);
$description = $conn->real_escape_string($data["description"] ?? "");
$price = (float) $data["price"];
$preparation_time = (int) $data["preparation_time"];
$category = $conn->real_escape_string($data["category"]);
$food_type = $conn->real_escape_string($data["food_type"]);
$health_tags = $conn->real_escape_string($data["health_tags"] ?? "");
$image = $conn->real_escape_string($data["image"] ?? "");

/* 5. Insert query */
$sql = "INSERT INTO dishes 
(chef_id, dish_name, description, price, preparation_time, category, food_type, health_tags, image)
VALUES
('$chef_id', '$dish_name', '$description', '$price', '$preparation_time', '$category', '$food_type', '$health_tags', '$image')";

/* 6. Execute */
if ($conn->query($sql)) {
    echo json_encode([
        "success" => true,
        "message" => "Dish added successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Database error"
    ]);
}
?>

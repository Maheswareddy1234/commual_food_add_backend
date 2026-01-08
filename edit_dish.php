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
$required = [
    "dish_id",
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

/* Sanitize inputs */
$dish_id = (int) $data["dish_id"];
$dish_name = $conn->real_escape_string($data["dish_name"]);
$description = $conn->real_escape_string($data["description"] ?? "");
$price = (float) $data["price"];
$preparation_time = (int) $data["preparation_time"];
$category = $conn->real_escape_string($data["category"]);
$food_type = $conn->real_escape_string($data["food_type"]);
$health_tags = $conn->real_escape_string($data["health_tags"] ?? "");
$image = $conn->real_escape_string($data["image"] ?? "");

/* Availability (optional) */
$is_available_sql = "";
if (isset($data["is_available"]) && in_array((int)$data["is_available"], [0,1], true)) {
    $is_available = (int) $data["is_available"];
    $is_available_sql = ", is_available = '$is_available'";
}

/* Update query */
$sql = "UPDATE dishes SET
            dish_name = '$dish_name',
            description = '$description',
            price = '$price',
            preparation_time = '$preparation_time',
            category = '$category',
            food_type = '$food_type',
            health_tags = '$health_tags',
            image = '$image'
            $is_available_sql
        WHERE id = '$dish_id'";

/* Execute */
if ($conn->query($sql)) {
    echo json_encode([
        "success" => true,
        "message" => "Dish updated successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update dish"
    ]);
}
?>

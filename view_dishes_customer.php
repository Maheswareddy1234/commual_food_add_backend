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

/* Optional filters */
$chef_id   = isset($data["chef_id"]) ? (int)$data["chef_id"] : 0;
$category  = isset($data["category"]) ? $conn->real_escape_string($data["category"]) : "";
$food_type = isset($data["food_type"]) ? $conn->real_escape_string($data["food_type"]) : "";

/* Base query – only AVAILABLE dishes for customers */
$sql = "SELECT 
            id AS dish_id,
            chef_id,
            dish_name,
            description,
            price,
            preparation_time,
            category,
            food_type,
            health_tags,
            image
        FROM dishes
        WHERE is_available = 1";

/* Apply filters */
if ($chef_id > 0) {
    $sql .= " AND chef_id = $chef_id";
}

if ($category !== "") {
    $sql .= " AND category = '$category'";
}

if ($food_type !== "") {
    $sql .= " AND food_type = '$food_type'";
}

$sql .= " ORDER BY dish_name ASC";

$result = $conn->query($sql);

$dishes = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        /* Customization types sent to customer */
        $dishes[] = [
            "dish_id" => $row["dish_id"],
            "chef_id" => $row["chef_id"],
            "dish_name" => $row["dish_name"],
            "description" => $row["description"],
            "price" => $row["price"],
            "category" => $row["category"],
            "image" => $row["image"],
            "customization" => [
                "food_type" => $row["food_type"],          // Veg / Non-Veg
                "health_tags" => $row["health_tags"],      // Diabetic, Low Salt, etc.
                "preparation_time_minutes" => $row["preparation_time"]
            ]
        ];
    }
}

/* Response */
echo json_encode([
    "success" => true,
    "count" => count($dishes),
    "data" => $dishes
]);
?>

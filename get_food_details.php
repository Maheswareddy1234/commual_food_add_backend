<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
$food_id = $data["food_id"] ?? 0;

$sql = "SELECT * FROM foods WHERE id = $food_id LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    echo json_encode([
        "success" => true,
        "data" => [
            "id" => $row["id"],
            "name" => $row["name"],
            "price" => $row["price"],
            "rating" => $row["rating"],
            "reviews" => $row["reviews"],
            "time" => $row["cook_time"],
            "calories" => $row["calories"],
            "description" => $row["description"],
            "protein" => $row["protein"],
            "carbs" => $row["carbs"],
            "fat" => $row["fat"],
            "fiber" => $row["fiber"],
            "tags" => explode(",", $row["tags"]),
            "ingredients" => explode(",", $row["ingredients"]),
            "image" => $row["image"]
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Food not found"
    ]);
}

<?php
header("Content-Type: application/json");
require_once "db.php";

/*
Optional:
- view all dishes
- OR view dishes by chef_id using ?chef_id=1
*/

$chef_id = isset($_GET["chef_id"]) ? (int) $_GET["chef_id"] : 0;

// Build query
if ($chef_id > 0) {
    $sql = "SELECT 
                id,
                chef_id,
                dish_name,
                description,
                price,
                preparation_time,
                category,
                food_type,
                health_tags,
                image,
                created_at
            FROM dishes
            WHERE chef_id = $chef_id
            ORDER BY id DESC";
} else {
    $sql = "SELECT 
                id,
                chef_id,
                dish_name,
                description,
                price,
                preparation_time,
                category,
                food_type,
                health_tags,
                image,
                created_at
            FROM dishes
            ORDER BY id DESC";
}

$result = $conn->query($sql);

$dishes = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dishes[] = $row;
    }
}

// Response
echo json_encode([
    "success" => true,
    "count" => count($dishes),
    "data" => $dishes
]);
?>

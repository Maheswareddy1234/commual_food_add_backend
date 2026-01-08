<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data["dish_id"]) || !isset($data["chef_id"]) || !isset($data["is_available"])) {
    echo json_encode([
        "success" => false,
        "message" => "dish_id, chef_id, and is_available are required"
    ]);
    exit;
}

$dish_id = (int)$data["dish_id"];
$chef_id = (int)$data["chef_id"];
$is_available = (bool)$data["is_available"] ? 1 : 0;

// Verify dish belongs to this chef
$check_sql = "SELECT id FROM dishes WHERE id = ? AND chef_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $dish_id, $chef_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Dish not found or not owned by this chef"
    ]);
    exit;
}

// Update availability
$update_sql = "UPDATE dishes SET is_available = ? WHERE id = ? AND chef_id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("iii", $is_available, $dish_id, $chef_id);

if ($update_stmt->execute()) {
    $status = $is_available ? "available" : "unavailable";
    echo json_encode([
        "success" => true,
        "message" => "Dish marked as $status"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update dish availability"
    ]);
}

$check_stmt->close();
$update_stmt->close();
$conn->close();
?>

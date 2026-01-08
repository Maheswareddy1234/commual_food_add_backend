<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data["dish_id"]) || !isset($data["chef_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "dish_id and chef_id are required"
    ]);
    exit;
}

$dish_id = (int)$data["dish_id"];
$chef_id = (int)$data["chef_id"];

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

// Delete dish
$delete_sql = "DELETE FROM dishes WHERE id = ? AND chef_id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("ii", $dish_id, $chef_id);

if ($delete_stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Dish deleted successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to delete dish"
    ]);
}

$check_stmt->close();
$delete_stmt->close();
$conn->close();
?>

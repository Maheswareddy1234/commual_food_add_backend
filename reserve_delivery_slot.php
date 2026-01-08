<?php
header("Content-Type: application/json");
require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["slot_id"]) || !isset($data["chef_id"])) {
    echo json_encode(["success"=>false,"message"=>"slot_id and chef_id required"]);
    exit;
}

$slot_id = (int)$data["slot_id"];
$chef_id = (int)$data["chef_id"];

$sql = "UPDATE delivery_slots
        SET status = 'reserved'
        WHERE id = '$slot_id'
        AND chef_id = '$chef_id'
        AND status = 'available'";

if ($conn->query($sql) && $conn->affected_rows > 0) {
    echo json_encode([
        "success" => true,
        "message" => "Slot reserved successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Slot not available"
    ]);
}
?>

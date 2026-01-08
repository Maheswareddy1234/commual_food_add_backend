<?php
header("Content-Type: application/json");
require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["chef_id"]) || !isset($data["slot_day"])) {
    echo json_encode(["success"=>false,"message"=>"chef_id and slot_day required"]);
    exit;
}

$chef_id = (int)$data["chef_id"];
$slot_day = $conn->real_escape_string($data["slot_day"]);

$sql = "SELECT id, slot_time, status
        FROM delivery_slots
        WHERE chef_id = '$chef_id'
        AND slot_day = '$slot_day'
        ORDER BY slot_time ASC";

$result = $conn->query($sql);
$slots = [];

while ($row = $result->fetch_assoc()) {
    $slots[] = [
        "slot_id" => $row["id"],
        "time" => date("h:i A", strtotime($row["slot_time"])),
        "status" => ucfirst($row["status"])
    ];
}

echo json_encode([
    "success" => true,
    "data" => $slots
]);
?>

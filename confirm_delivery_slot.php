<?php
header("Content-Type: application/json");
require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$required = ["order_id","customer_id","chef_id","slot_id","slot_day","slot_time"];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        echo json_encode(["success"=>false,"message"=>"$field is required"]);
        exit;
    }
}

$order_id = (int)$data["order_id"];
$customer_id = (int)$data["customer_id"];
$chef_id = (int)$data["chef_id"];
$slot_id = (int)$data["slot_id"];
$slot_day = $conn->real_escape_string($data["slot_day"]);
$slot_time = $conn->real_escape_string($data["slot_time"]);

$conn->begin_transaction();

try {
    /* Confirm slot */
    $update = "UPDATE delivery_slots
               SET status = 'booked'
               WHERE id = '$slot_id'
               AND chef_id = '$chef_id'
               AND status = 'reserved'";

    if (!$conn->query($update) || $conn->affected_rows === 0) {
        throw new Exception("Slot not reserved or already booked");
    }

    /* Save booking */
    $insert = "INSERT INTO order_delivery_slots
        (order_id, customer_id, chef_id, slot_day, slot_time)
        VALUES
        ('$order_id','$customer_id','$chef_id','$slot_day','$slot_time')";

    if (!$conn->query($insert)) {
        throw new Exception("Failed to save delivery slot");
    }

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Delivery slot booked successfully"
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>

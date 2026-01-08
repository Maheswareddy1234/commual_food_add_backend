<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = new mysqli("localhost", "root", "", "food-app");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB error"]);
    exit;
}

$customer_id = isset($_GET["customer_id"]) ? (int)$_GET["customer_id"] : 0;

if ($customer_id <= 0) {
    echo json_encode(["success" => false, "has_subscription" => false]);
    exit;
}

// Check for active subscription
$sql = "SELECT * FROM subscriptions WHERE customer_id = $customer_id AND status = 'active' AND end_date >= CURDATE() ORDER BY end_date DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $sub = $result->fetch_assoc();
    echo json_encode([
        "success" => true,
        "has_subscription" => true,
        "subscription" => [
            "id" => (int)$sub["id"],
            "plan_name" => $sub["plan_name"],
            "start_date" => $sub["start_date"],
            "end_date" => $sub["end_date"],
            "discount_percent" => (int)$sub["discount_percent"]
        ]
    ]);
} else {
    echo json_encode([
        "success" => true,
        "has_subscription" => false
    ]);
}
$conn->close();

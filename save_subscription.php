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

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid JSON"]);
    exit;
}

$customer_id = (int)($data["customer_id"] ?? 0);
$plan_name = $conn->real_escape_string($data["plan_name"] ?? "Weekly Plan");
$amount = (float)($data["amount"] ?? 699);
$payment_id = $conn->real_escape_string($data["payment_id"] ?? "DEMO_" . time());

if ($customer_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid customer ID"]);
    exit;
}

// Create subscriptions table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    plan_name VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_id VARCHAR(100),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    discount_percent INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Check if customer already has active subscription
$check = $conn->query("SELECT id FROM subscriptions WHERE customer_id = $customer_id AND status = 'active' AND end_date >= CURDATE()");
if ($check && $check->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "You already have an active subscription"]);
    exit;
}

// Calculate dates (7 days for weekly plan)
$start_date = date('Y-m-d');
$end_date = date('Y-m-d', strtotime('+7 days'));

$sql = "INSERT INTO subscriptions (customer_id, plan_name, amount, payment_id, start_date, end_date, status, discount_percent) 
        VALUES ($customer_id, '$plan_name', $amount, '$payment_id', '$start_date', '$end_date', 'active', 10)";

if ($conn->query($sql)) {
    echo json_encode([
        "success" => true, 
        "message" => "Subscription activated!",
        "subscription_id" => $conn->insert_id,
        "end_date" => $end_date,
        "discount_percent" => 10
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to save subscription"]);
}
$conn->close();

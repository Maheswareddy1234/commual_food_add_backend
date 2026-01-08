<?php
// Test script to verify database connection and order placement
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

$response = [];

// Test 1: Database connection
$conn = new mysqli("localhost", "root", "", "food-app");
if ($conn->connect_error) {
    $response["db_connection"] = "FAILED: " . $conn->connect_error;
    echo json_encode($response);
    exit;
}
$response["db_connection"] = "OK";

// Test 2: Check if tables exist
$tables = ["orders", "order_items", "cart", "dishes", "customers"];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    $response["table_$table"] = ($result && $result->num_rows > 0) ? "EXISTS" : "MISSING";
}

// Test 3: Count cart items
$result = $conn->query("SELECT customer_id, COUNT(*) as count FROM cart GROUP BY customer_id");
$response["cart_summary"] = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $response["cart_summary"][] = $row;
    }
}

// Test 4: Check customers table
$result = $conn->query("SELECT id, full_name FROM customers LIMIT 5");
$response["customers"] = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $response["customers"][] = $row;
    }
}

// Test 5: Check orders count
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
if ($result) {
    $response["orders_count"] = $result->fetch_assoc()['count'];
}

$conn->close();

echo json_encode($response, JSON_PRETTY_PRINT);
?>

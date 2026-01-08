<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$conn = new mysqli("localhost", "root", "", "food-app");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection error"]);
    exit;
}

$customer_id = (int)($_GET["customer_id"] ?? 0);
$status_filter = $_GET["status"] ?? "ongoing"; // ongoing or completed

if ($customer_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid customer ID"]);
    exit;
}

// Create orders table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    delivery_date VARCHAR(50),
    delivery_time VARCHAR(50),
    status ENUM('pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create order_items table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    dish_id INT NOT NULL,
    dish_name VARCHAR(100),
    quantity INT DEFAULT 1,
    price DECIMAL(10,2),
    customization TEXT
)");

// Build status filter
if ($status_filter === "ongoing") {
    $status_clause = "o.status IN ('pending', 'confirmed', 'preparing', 'out_for_delivery')";
} else {
    $status_clause = "o.status IN ('delivered', 'cancelled')";
}

// Get orders with first item details
$sql = "SELECT 
    o.id as order_id,
    o.order_number,
    o.total,
    o.delivery_date,
    o.delivery_time,
    o.status,
    o.created_at,
    oi.dish_name,
    oi.dish_id,
    oi.quantity,
    d.image as dish_image,
    d.chef_id,
    c.name as chef_name,
    c.phone as chef_phone
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN dishes d ON oi.dish_id = d.id
LEFT JOIN chefs c ON d.chef_id = c.id
WHERE o.customer_id = $customer_id 
AND $status_clause
GROUP BY o.id
ORDER BY o.created_at DESC";

$result = $conn->query($sql);

$orders = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = [
            "order_id" => (int)$row['order_id'],
            "order_number" => $row['order_number'],
            "dish_name" => $row['dish_name'] ?? "Unknown Dish",
            "dish_image" => $row['dish_image'] ?? "",
            "chef_id" => (int)($row['chef_id'] ?? 0),
            "chef_name" => $row['chef_name'] ?? "Unknown Chef",
            "chef_phone" => $row['chef_phone'] ?? "",
            "delivery_date" => $row['delivery_date'] ?? "",
            "delivery_time" => $row['delivery_time'] ?? "",
            "order_date" => date('M d, h:i A', strtotime($row['created_at'])),
            "total" => (float)$row['total'],
            "status" => $row['status'],
            "quantity" => (int)($row['quantity'] ?? 1)
        ];
    }
}

echo json_encode([
    "success" => true,
    "orders" => $orders
]);

$conn->close();

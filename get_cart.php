<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = new mysqli("localhost", "root", "", "food-app");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB error: " . $conn->connect_error]);
    exit;
}

$customer_id = isset($_GET["customer_id"]) ? (int)$_GET["customer_id"] : 0;

if ($customer_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid customer ID"]);
    exit;
}

// Check if cart table exists, if not create it
$conn->query("CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    dish_id INT NOT NULL,
    quantity INT DEFAULT 1,
    customization TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Get cart items with dish details
$sql = "SELECT c.id, c.dish_id, c.quantity, c.customization, 
               d.dish_name, d.price, d.image, d.food_type 
        FROM cart c 
        LEFT JOIN dishes d ON c.dish_id = d.id 
        WHERE c.customer_id = $customer_id 
        ORDER BY c.created_at DESC";

$result = $conn->query($sql);
$items = [];
$subtotal = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customization = isset($row["customization"]) ? json_decode($row["customization"], true) : [];
        if (!is_array($customization)) $customization = [];
        
        $price = (float)($row["price"] ?? 0);
        $quantity = (int)($row["quantity"] ?? 1);
        $item_total = $price * $quantity;
        $subtotal += $item_total;
        
        $items[] = [
            "cart_id" => (int)$row["id"],
            "dish_id" => (int)$row["dish_id"],
            "dish_name" => $row["dish_name"] ?? "Unknown Dish",
            "price" => $price,
            "quantity" => $quantity,
            "image" => $row["image"] ?? "",
            "food_type" => $row["food_type"] ?? "veg",
            "portion" => isset($customization["portion"]) ? $customization["portion"] : "Regular",
            "spice_level" => isset($customization["spice_level"]) ? $customization["spice_level"] : "Medium",
            "oil_level" => isset($customization["oil_level"]) ? $customization["oil_level"] : "Medium",
            "salt_level" => isset($customization["salt_level"]) ? $customization["salt_level"] : "Normal",
            "item_total" => $item_total
        ];
    }
}

echo json_encode([
    "success" => true,
    "items" => $items,
    "subtotal" => $subtotal,
    "total" => $subtotal
]);
$conn->close();

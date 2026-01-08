<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once "db.php";

/* Read JSON input */
$data = json_decode(file_get_contents("php://input"), true);

if ($data === null) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON input"
    ]);
    exit;
}

/* Validate customer_id */
if (!isset($data["customer_id"]) || trim((string)$data["customer_id"]) === "") {
    echo json_encode([
        "success" => false,
        "message" => "customer_id is required"
    ]);
    exit;
}

$customer_id = (int) $data["customer_id"];

/* Fetch cart items with dish and chef details */
$sql = "SELECT 
            c.id AS cart_id,
            c.quantity,
            c.portion,
            c.spice_level,
            c.oil_level,
            c.salt_level,
            d.id AS dish_id,
            d.dish_name,
            d.price,
            d.image,
            d.is_available,
            COALESCE(u.full_name, 'Home Chef') AS chef_name
        FROM cart c
        JOIN dishes d ON c.dish_id = d.id
        LEFT JOIN users u ON d.chef_id = u.id
        WHERE c.customer_id = '$customer_id'";

$result = $conn->query($sql);

$items = [];
$total_amount = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $item_total = $row["price"] * $row["quantity"];
        $total_amount += $item_total;
        
        $items[] = [
            "cart_id" => (int) $row["cart_id"],
            "dish_id" => (int) $row["dish_id"],
            "dish_name" => $row["dish_name"],
            "chef_name" => $row["chef_name"],
            "price" => (float) $row["price"],
            "quantity" => (int) $row["quantity"],
            "image" => $row["image"],
            "is_available" => (bool) $row["is_available"],
            "portion" => $row["portion"] ?? "Regular",
            "spice_level" => $row["spice_level"] ?? "Medium",
            "oil_level" => $row["oil_level"] ?? "Medium",
            "salt_level" => $row["salt_level"] ?? "Normal",
            "item_total" => $item_total
        ];
    }
}

/* Response */
echo json_encode([
    "success" => true,
    "count" => count($items),
    "total_amount" => $total_amount,
    "delivery_fee" => 20,
    "data" => $items
]);
?>


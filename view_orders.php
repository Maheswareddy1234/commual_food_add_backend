<?php
header("Content-Type: application/json");
require_once "db.php";

/* Read JSON input */
$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data["user_type"]) ||
    !in_array($data["user_type"], ["customer","chef"]) ||
    !isset($data["user_id"])
) {
    echo json_encode([
        "success" => false,
        "message" => "user_type (customer/chef) and user_id are required"
    ]);
    exit;
}

$user_type = $data["user_type"];
$user_id = (int)$data["user_id"];

/* Build query */
$where = ($user_type === "customer")
    ? "o.customer_id = '$user_id'"
    : "o.chef_id = '$user_id'";

$sql = "SELECT 
            o.id AS order_id,
            o.total_amount,
            o.status,
            o.created_at,
            ods.slot_day,
            ods.slot_time
        FROM orders o
        LEFT JOIN order_delivery_slots ods ON o.id = ods.order_id
        WHERE $where
        ORDER BY o.created_at DESC";

$result = $conn->query($sql);

$orders = [];

while ($row = $result->fetch_assoc()) {

    /* Fetch items per order */
    $items_sql = "SELECT 
                    oi.quantity,
                    oi.price,
                    d.dish_name
                  FROM order_items oi
                  JOIN dishes d ON oi.dish_id = d.id
                  WHERE oi.order_id = '{$row["order_id"]}'";

    $items_result = $conn->query($items_sql);
    $items = [];

    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }

    $orders[] = [
        "order_id" => $row["order_id"],
        "total_amount" => $row["total_amount"],
        "status" => $row["status"],
        "order_date" => $row["created_at"],
        "delivery_slot" => [
            "day" => $row["slot_day"],
            "time" => $row["slot_time"]
        ],
        "items" => $items
    ];
}

echo json_encode([
    "success" => true,
    "count" => count($orders),
    "data" => $orders
]);
?>

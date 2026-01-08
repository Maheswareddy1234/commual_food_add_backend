<?php
// Start output buffering
ob_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Error handler
set_exception_handler(function($e) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    exit;
});

$conn = new mysqli("localhost", "root", "", "food-app");
if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "DB Connection failed"]);
    exit;
}

// Get order_id from query parameter
$order_id = isset($_GET["order_id"]) ? (int)$_GET["order_id"] : 0;
if ($order_id <= 0) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "order_id is required"]);
    exit;
}

// Get order details
$order_sql = "SELECT o.*, c.name as customer_name, c.phone as customer_phone 
              FROM orders o 
              LEFT JOIN customers c ON o.customer_id = c.id 
              WHERE o.id = $order_id";
$order_result = $conn->query($order_sql);

if (!$order_result || $order_result->num_rows == 0) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Order not found"]);
    exit;
}

$order = $order_result->fetch_assoc();

// Get customer address
$customer_id = (int)$order['customer_id'];
$delivery_address = "";
$addr_result = $conn->query("SELECT full_address, landmark, pincode FROM addresses WHERE customer_id = $customer_id AND is_default = 1 LIMIT 1");
if ($addr_result && $addr_result->num_rows > 0) {
    $addr = $addr_result->fetch_assoc();
    $delivery_address = $addr['full_address'];
    if (!empty($addr['landmark'])) {
        $delivery_address .= ", " . $addr['landmark'];
    }
    if (!empty($addr['pincode'])) {
        $delivery_address .= " - " . $addr['pincode'];
    }
}

// Get order items with customization
$items_sql = "SELECT oi.*, d.dish_name as name, d.price as unit_price
              FROM order_items oi
              LEFT JOIN dishes d ON oi.dish_id = d.id
              WHERE oi.order_id = $order_id";
$items_result = $conn->query($items_sql);

$items = [];
if ($items_result) {
    while ($item = $items_result->fetch_assoc()) {
        $customization = [];
        
        // Parse customization JSON
        if (!empty($item['customization']) && $item['customization'] != '{}') {
            $custom_json = json_decode($item['customization'], true);
            if ($custom_json) {
                if (isset($custom_json['spice_level'])) {
                    $customization[] = [
                        "icon" => "spice",
                        "label" => $custom_json['spice_level']
                    ];
                }
                if (isset($custom_json['oil_level'])) {
                    $customization[] = [
                        "icon" => "oil",
                        "label" => $custom_json['oil_level']
                    ];
                }
                if (isset($custom_json['salt_level'])) {
                    $customization[] = [
                        "icon" => "salt",
                        "label" => $custom_json['salt_level']
                    ];
                }
                if (isset($custom_json['instructions']) && !empty($custom_json['instructions'])) {
                    $customization[] = [
                        "icon" => "note",
                        "label" => $custom_json['instructions']
                    ];
                }
            }
        }
        
        $items[] = [
            "id" => (int)$item['id'],
            "dish_id" => (int)$item['dish_id'],
            "name" => $item['dish_name'] ?? $item['name'] ?? "Item",
            "quantity" => (int)$item['quantity'],
            "price" => (int)($item['price'] ?? $item['unit_price'] ?? 0),
            "customization" => $customization
        ];
    }
}

// Format dates
$order_date = "";
$order_time = "";
if (!empty($order['created_at'])) {
    $order_date = date('M d, Y', strtotime($order['created_at']));
    $order_time = date('h:i A', strtotime($order['created_at']));
}

$delivery_date = "";
if (!empty($order['delivery_date'])) {
    $date_ts = strtotime($order['delivery_date']);
    if ($date_ts) {
        $delivery_date = date('M d, Y', $date_ts);
    }
}

$conn->close();

ob_end_clean();
echo json_encode([
    "success" => true,
    "order" => [
        "id" => (int)$order['id'],
        "order_number" => $order['order_number'] ?? "#ORD-" . $order['id'],
        "status" => strtoupper($order['status'] ?? 'pending'),
        "customer_name" => $order['customer_name'] ?? "Customer",
        "customer_phone" => $order['customer_phone'] ?? "",
        "delivery_address" => $delivery_address,
        "delivery_date" => $delivery_date,
        "delivery_time" => $order['delivery_time'] ?? "",
        "order_date" => $order_date,
        "order_time" => $order_time,
        "subtotal" => (int)($order['subtotal'] ?? 0),
        "discount" => (int)($order['discount'] ?? 0),
        "total" => (int)($order['total'] ?? 0),
        "payment_method" => $order['payment_method'] ?? "cod",
        "items" => $items
    ]
]);
?>

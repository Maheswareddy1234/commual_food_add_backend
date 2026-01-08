<?php
// Start output buffering
ob_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Error handler
set_exception_handler(function($e) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage(), "orders" => [], "counts" => ["total" => 0, "pending" => 0, "confirmed" => 0, "preparing" => 0, "delivered" => 0, "cancelled" => 0]]);
    exit;
});

$conn = new mysqli("localhost", "root", "", "food-app");
if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "DB Connection failed", "orders" => [], "counts" => ["total" => 0, "pending" => 0, "confirmed" => 0, "preparing" => 0, "delivered" => 0, "cancelled" => 0]]);
    exit;
}

// Get chef_id from query parameter
$chef_id = isset($_GET["chef_id"]) ? (int)$_GET["chef_id"] : 0;
if ($chef_id <= 0) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "chef_id is required", "orders" => [], "counts" => ["total" => 0, "pending" => 0, "confirmed" => 0, "preparing" => 0, "delivered" => 0, "cancelled" => 0]]);
    exit;
}

$status = isset($_GET["status"]) ? strtolower($_GET["status"]) : null;
$limit = isset($_GET["limit"]) ? (int)$_GET["limit"] : 20;
$offset = isset($_GET["offset"]) ? (int)$_GET["offset"] : 0;

// First, check if tables exist
$tables_check = $conn->query("SHOW TABLES LIKE 'orders'");
if (!$tables_check || $tables_check->num_rows == 0) {
    ob_end_clean();
    echo json_encode(["success" => true, "message" => "No orders table", "orders" => [], "counts" => ["total" => 0, "pending" => 0, "confirmed" => 0, "preparing" => 0, "delivered" => 0, "cancelled" => 0]]);
    exit;
}

// Check if dishes table has chef_id column
$col_check = $conn->query("SHOW COLUMNS FROM dishes LIKE 'chef_id'");
$has_chef_id = ($col_check && $col_check->num_rows > 0);

// Build query based on whether chef_id exists in dishes
if ($has_chef_id) {
    $sql = "SELECT DISTINCT o.id, o.order_number, o.customer_id, o.subtotal, o.discount, o.total, 
            o.payment_method, o.delivery_date, o.delivery_time, o.status, o.created_at
            FROM orders o 
            INNER JOIN order_items oi ON o.id = oi.order_id
            INNER JOIN dishes d ON oi.dish_id = d.id
            WHERE d.chef_id = $chef_id";
} else {
    // If no chef_id column, show all orders
    $sql = "SELECT o.id, o.order_number, o.customer_id, o.subtotal, o.discount, o.total, 
            o.payment_method, o.delivery_date, o.delivery_time, o.status, o.created_at
            FROM orders o";
    $sql .= " WHERE 1=1";
}

if ($status && $status !== 'all') {
    $status = $conn->real_escape_string($status);
    $sql .= " AND o.status = '$status'";
}

$sql .= " ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

$orders = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $order_id = $row['id'];
        
        // Get customer name
        $customer_name = "Customer";
        $customer_id = (int)$row['customer_id'];
        if ($customer_id > 0) {
            $cust_result = $conn->query("SELECT name FROM customers WHERE id = $customer_id");
            if ($cust_result && $cust_result->num_rows > 0) {
                $customer_name = $cust_result->fetch_assoc()['name'];
            }
        }
        
        // Get delivery address (using correct column names: full_address, landmark)
        $delivery_address = "";
        $addr_result = $conn->query("SELECT full_address, landmark FROM addresses WHERE customer_id = $customer_id AND is_default = 1 LIMIT 1");
        if ($addr_result && $addr_result->num_rows > 0) {
            $addr = $addr_result->fetch_assoc();
            $delivery_address = $addr['full_address'];
            if (!empty($addr['landmark'])) {
                $delivery_address .= ", " . $addr['landmark'];
            }
        }
        
        // Get items for this order
        $items_sql = "SELECT dish_name, quantity, customization FROM order_items WHERE order_id = $order_id";
        $items_result = $conn->query($items_sql);
        
        $items_arr = [];
        $customization = "";
        if ($items_result) {
            while ($item = $items_result->fetch_assoc()) {
                $items_arr[] = $item['dish_name'] . " x" . $item['quantity'];
                if (!empty($item['customization'])) {
                    $customization = $item['customization'];
                }
            }
        }
        
        // Format delivery date nicely (e.g., "Dec 16, 2025")
        $formatted_delivery_date = "";
        if (!empty($row['delivery_date'])) {
            $date_ts = strtotime($row['delivery_date']);
            if ($date_ts) {
                $formatted_delivery_date = date('M d, Y', $date_ts);
            } else {
                $formatted_delivery_date = $row['delivery_date'];
            }
        }
        
        $orders[] = [
            "id" => (int)$row['id'],
            "customer_id" => $customer_id,
            "customer_name" => $customer_name,
            "items" => implode(", ", $items_arr),
            "total_amount" => (int)$row['total'],
            "status" => strtoupper($row['status']),
            "delivery_address" => $delivery_address,
            "delivery_date" => $formatted_delivery_date,
            "delivery_time" => $row['delivery_time'] ?? "",
            "customization" => $customization,
            "order_time" => date('h:i A', strtotime($row['created_at'])),
            "order_date" => date('Y-m-d', strtotime($row['created_at'])),
            "order_date_formatted" => date('M d, Y', strtotime($row['created_at']))
        ];
    }
}

// Get counts by status
$counts = [
    "total" => 0,
    "pending" => 0,
    "confirmed" => 0,
    "preparing" => 0,
    "delivered" => 0,
    "cancelled" => 0
];

if ($has_chef_id) {
    $count_sql = "SELECT 
        COUNT(DISTINCT o.id) as total,
        SUM(CASE WHEN o.status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN o.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN o.status = 'preparing' THEN 1 ELSE 0 END) as preparing,
        SUM(CASE WHEN o.status = 'delivered' THEN 1 ELSE 0 END) as delivered,
        SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
        FROM orders o 
        INNER JOIN order_items oi ON o.id = oi.order_id
        INNER JOIN dishes d ON oi.dish_id = d.id
        WHERE d.chef_id = $chef_id";
} else {
    $count_sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) as preparing,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
        FROM orders";
}

$count_result = $conn->query($count_sql);
if ($count_result) {
    $row = $count_result->fetch_assoc();
    $counts = [
        "total" => (int)($row['total'] ?? 0),
        "pending" => (int)($row['pending'] ?? 0),
        "confirmed" => (int)($row['confirmed'] ?? 0),
        "preparing" => (int)($row['preparing'] ?? 0),
        "delivered" => (int)($row['delivered'] ?? 0),
        "cancelled" => (int)($row['cancelled'] ?? 0)
    ];
}

$conn->close();

ob_end_clean();
echo json_encode([
    "success" => true,
    "orders" => $orders,
    "counts" => $counts,
    "debug" => [
        "chef_id" => $chef_id,
        "status_filter" => $status,
        "has_chef_id_column" => $has_chef_id,
        "query" => $sql
    ]
]);
?>

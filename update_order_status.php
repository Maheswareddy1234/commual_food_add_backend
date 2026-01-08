<?php
// Start output buffering
ob_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_clean();
    exit;
}

// Error handler
set_exception_handler(function($e) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    exit;
});

// Database connection
$conn = new mysqli("localhost", "root", "", "food-app");
if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "DB Connection failed"]);
    exit;
}

// Get POST data
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Validate required fields
if (!isset($data["order_id"]) || !isset($data["status"])) {
    ob_end_clean();
    echo json_encode([
        "success" => false,
        "message" => "order_id and status are required"
    ]);
    exit;
}

$order_id = (int)$data["order_id"];
$chef_id = isset($data["chef_id"]) ? (int)$data["chef_id"] : 0;
$new_status = strtolower($data["status"]);

// Validate status value
$valid_statuses = ["pending", "confirmed", "preparing", "delivered", "cancelled"];
if (!in_array($new_status, $valid_statuses)) {
    ob_end_clean();
    echo json_encode([
        "success" => false,
        "message" => "Invalid status. Must be one of: " . implode(", ", $valid_statuses)
    ]);
    exit;
}

// Verify order exists
$check_sql = "SELECT id, status FROM orders WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $order_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    ob_end_clean();
    echo json_encode([
        "success" => false,
        "message" => "Order not found"
    ]);
    exit;
}

$current_order = $check_result->fetch_assoc();
$current_status = strtolower($current_order["status"]);

// If chef_id is provided, verify the order contains dishes from this chef
if ($chef_id > 0) {
    // Check if dishes table has chef_id column
    $col_check = $conn->query("SHOW COLUMNS FROM dishes LIKE 'chef_id'");
    $has_chef_id = ($col_check && $col_check->num_rows > 0);
    
    if ($has_chef_id) {
        $verify_sql = "SELECT COUNT(*) as cnt FROM order_items oi 
                       JOIN dishes d ON oi.dish_id = d.id 
                       WHERE oi.order_id = ? AND d.chef_id = ?";
        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("ii", $order_id, $chef_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        $count = $verify_result->fetch_assoc()['cnt'];
        
        if ($count == 0) {
            ob_end_clean();
            echo json_encode([
                "success" => false,
                "message" => "Order doesn't contain dishes from this chef"
            ]);
            exit;
        }
    }
}

// Validate status transition (more lenient for flexibility)
$valid_transitions = [
    "pending" => ["confirmed", "cancelled"],
    "confirmed" => ["preparing", "cancelled"],
    "preparing" => ["delivered", "cancelled"],
    "delivered" => [],
    "cancelled" => []
];

// For flexibility, if current_status is not a known state, allow the update
if (isset($valid_transitions[$current_status])) {
    if (!empty($valid_transitions[$current_status]) && !in_array($new_status, $valid_transitions[$current_status])) {
        // Allow the transition anyway for now (can be made stricter later)
        // Uncomment below to enforce strict transitions
        /*
        ob_end_clean();
        echo json_encode([
            "success" => false,
            "message" => "Invalid status transition from $current_status to $new_status"
        ]);
        exit;
        */
    }
}

// Update order status
$update_sql = "UPDATE orders SET status = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("si", $new_status, $order_id);

if ($update_stmt->execute()) {
    // Get customer_id and order_number for notification
    $order_info = $conn->query("SELECT customer_id, order_number FROM orders WHERE id = $order_id");
    if ($order_info && $order_row = $order_info->fetch_assoc()) {
        $customer_id = $order_row['customer_id'];
        $order_number = $order_row['order_number'];
        
        // Create notification message based on status
        $status_messages = [
            'confirmed' => ['title' => '✅ Order Confirmed!', 'body' => "Your order $order_number has been confirmed by the chef."],
            'preparing' => ['title' => '👨‍🍳 Order Being Prepared', 'body' => "Your order $order_number is now being prepared."],
            'delivered' => ['title' => '🎉 Order Delivered!', 'body' => "Your order $order_number has been delivered. Enjoy your meal!"],
            'cancelled' => ['title' => '❌ Order Cancelled', 'body' => "Your order $order_number has been cancelled."]
        ];
        
        if (isset($status_messages[$new_status])) {
            $notif = $status_messages[$new_status];
            
            // Send FCM push notification
            require_once 'fcm_helper.php';
            notifyCustomer($conn, $customer_id, $notif['title'], $notif['body'], [
                'type' => 'order_update',
                'order_id' => strval($order_id),
                'order_number' => $order_number,
                'status' => $new_status
            ]);
        }
    }
    
    ob_end_clean();
    echo json_encode([
        "success" => true,
        "message" => "Order status updated to $new_status",
        "order_id" => $order_id,
        "new_status" => strtoupper($new_status)
    ]);
} else {
    ob_end_clean();
    echo json_encode([
        "success" => false,
        "message" => "Failed to update order status: " . $conn->error
    ]);
}

$conn->close();
?>
